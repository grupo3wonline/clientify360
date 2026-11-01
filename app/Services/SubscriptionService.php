<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\InvoiceData;
use App\Data\PlanData;
use App\Data\SubscriptionSummaryData;
use App\Exceptions\BillingUnavailableException;
use App\Models\Account;
use App\Models\Client;
use App\Models\LeadForm;
use App\Models\Media;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use App\Support\AccountCache;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Invoice;
use Laravel\Cashier\Subscription;
use Stripe\Exception\ExceptionInterface as StripeException;
use Stripe\Subscription as StripeSubscription;

/**
 * A assinatura é da CONTA (tenant), não do usuário. Os métodos recebem o
 * usuário por conveniência dos controllers e resolvem a conta dele como a
 * entidade Billable do Cashier.
 *
 * Not final on purpose: billing feature tests replace the Stripe-backed
 * methods with a Mockery partial mock (tests never call real Stripe).
 */
class SubscriptionService
{
    private const SUBSCRIPTION_NAME = 'default';

    private const INVOICES_TTL = 1800;

    private const SUMMARY_MAX_TTL = 1800;

    private const SUMMARY_MIN_TTL = 60;

    /** Rótulos usados nas mensagens de limite atingido. */
    public const RESOURCE_LABELS = [
        'clients' => 'clientes',
        'products' => 'produtos/serviços',
        'quotes_per_month' => 'orçamentos por mês',
        'lead_forms' => 'formulários de captação',
        'client_attachments' => 'anexos',
    ];

    /** Mensagens dos recursos exclusivos do plano Profissional. */
    public const FEATURE_MESSAGES = [
        'api' => 'Acesso à API disponível apenas no plano Profissional.',
        'export' => 'Exportação disponível apenas no plano Profissional.',
        'pdf' => 'PDF disponível apenas no plano Profissional.',
        'ai' => 'Recurso disponível apenas no plano Profissional.',
    ];

    public function __construct(
        private readonly AccountCache $cache,
    ) {}

    /** Resolve a conta (entidade Billable) a partir do usuário. */
    private function billable(User $user): ?Account
    {
        return $user->account;
    }

    public function currentPlan(User $user): PlanData
    {
        return PlanData::fromConfig($this->isProfessional($user) ? 'professional' : 'basic');
    }

    public function isProfessional(User $user): bool
    {
        $account = $this->billable($user);

        return $account !== null && $account->subscribed(self::SUBSCRIPTION_NAME);
    }

    public function hasFeature(User $user, string $feature): bool
    {
        return $this->currentPlan($user)->hasFeature($feature);
    }

    public function featureMessage(string $feature): string
    {
        return self::FEATURE_MESSAGES[$feature] ?? self::FEATURE_MESSAGES['ai'];
    }

    public function limitMessage(User $user, string $resource): string
    {
        $limit = $this->getLimit($user, $resource);
        $label = self::RESOURCE_LABELS[$resource] ?? $resource;
        $plan = $this->currentPlan($user)->name;

        return "Você atingiu o limite de {$limit} {$label} do plano {$plan}. Faça upgrade para o plano Profissional para liberar mais.";
    }

    /** Limite do plano para um recurso (-1 = ilimitado; recurso não configurado = ilimitado). */
    public function getLimit(User $user, string $resource): int
    {
        return $this->currentPlan($user)->limit($resource);
    }

    /**
     * Uso atual do recurso na conta. Os models têm escopo global por conta, mas
     * aqui filtramos explicitamente por account_id para funcionar também fora de
     * uma requisição autenticada (ex.: formulário público de captação).
     */
    public function getUsage(User $user, string $resource): int
    {
        $accountId = $user->account_id;

        return match ($resource) {
            'clients' => Client::withoutGlobalScope('owned')->where('account_id', $accountId)->count(),
            'products' => Product::withoutGlobalScope('owned')->where('account_id', $accountId)->count(),
            'lead_forms' => LeadForm::withoutGlobalScope('owned')->where('account_id', $accountId)->count(),
            'quotes_per_month' => Quote::withoutGlobalScope('owned')->withTrashed()
                ->where('account_id', $accountId)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'client_attachments' => Media::query()
                ->where('model_type', (new Client)->getMorphClass())
                ->whereIn('model_id', Client::withoutGlobalScope('owned')->withTrashed()
                    ->where('account_id', $accountId)->select('id'))
                ->where('collection_name', 'attachments')
                ->count(),
            default => 0,
        };
    }

    public function canCreate(User $user, string $resource): bool
    {
        $limit = $this->getLimit($user, $resource);

        return $limit === -1 || $this->getUsage($user, $resource) < $limit;
    }

    /**
     * Uso x limite de cada recurso limitado (sem cache: muda a cada gravação).
     *
     * @return array<string, array{usage: int, limit: int, label: string}>
     */
    public function usageReport(User $user): array
    {
        $plan = $this->currentPlan($user);
        $report = [];

        foreach (self::RESOURCE_LABELS as $resource => $label) {
            $report[$resource] = [
                'usage' => $this->getUsage($user, $resource),
                'limit' => $plan->limit($resource),
                'label' => $label,
            ];
        }

        return $report;
    }

    /** Plano, situação e vigência da assinatura (cacheado; não cacheia o resultado degradado). */
    public function summary(User $user): SubscriptionSummaryData
    {
        $account = $this->billable($user);
        $local = $account?->subscription(self::SUBSCRIPTION_NAME);

        if ($account === null || $local === null) {
            return $this->buildSummary(null, null);
        }

        $cached = $this->cache->getBilling($account->id, 'summary');

        if (is_array($cached)) {
            return SubscriptionSummaryData::fromCache($cached);
        }

        $summary = $this->buildSummary($local, $this->fetchRemote($local, $account->id));

        if (!$summary->degraded) {
            $this->cache->putBilling($account->id, 'summary', $summary->toCache(), $this->summaryTtl($summary));
        }

        return $summary;
    }

    /** A cached summary never outlives the period it describes by more than 60 s. */
    public function summaryTtl(SubscriptionSummaryData $summary): int
    {
        $end = $summary->endsAt ?? $summary->periodEnd;

        if ($end === null) {
            return self::SUMMARY_MAX_TTL;
        }

        $seconds = $end->getTimestamp() - now()->getTimestamp();

        return min(self::SUMMARY_MAX_TTL, max(self::SUMMARY_MIN_TTL, $seconds));
    }

    /**
     * Pure builder (no network). $remote is null when Stripe was not queried
     * (ended subscription) or could not be reached (degraded).
     */
    public function buildSummary(?Subscription $local, ?StripeSubscription $remote): SubscriptionSummaryData
    {
        if ($local === null) {
            return new SubscriptionSummaryData(
                plan: PlanData::fromConfig('basic'),
                status: 'free',
                statusLabel: SubscriptionSummaryData::labelFor('free'),
                periodStart: null,
                periodEnd: null,
                cancelAtPeriodEnd: false,
                endsAt: null,
            );
        }

        $status = $this->statusOf($local);
        $start = $remote !== null ? self::periodTimestamp($remote, 'current_period_start') : null;
        $end = $remote !== null ? self::periodTimestamp($remote, 'current_period_end') : null;

        return new SubscriptionSummaryData(
            plan: PlanData::fromConfig($local->valid() ? 'professional' : 'basic'),
            status: $status,
            statusLabel: SubscriptionSummaryData::labelFor($status),
            periodStart: $start !== null ? CarbonImmutable::createFromTimestamp($start, config('app.timezone')) : null,
            periodEnd: $end !== null ? CarbonImmutable::createFromTimestamp($end, config('app.timezone')) : null,
            cancelAtPeriodEnd: $local->onGracePeriod(),
            endsAt: $local->ends_at !== null ? CarbonImmutable::instance($local->ends_at) : null,
            degraded: $remote === null && $status !== 'canceled',
        );
    }

    /**
     * Faturas da conta no Stripe (cacheadas).
     *
     * @return Collection<int, InvoiceData>
     *
     * @throws BillingUnavailableException
     */
    public function invoices(User $user): Collection
    {
        $account = $this->billable($user);

        if ($account === null || !$account->hasStripeId()) {
            return collect();
        }

        $rows = $this->cache->rememberBilling($account->id, 'invoices', self::INVOICES_TTL, function () use ($account): array {
            try {
                return $account->invoices(true)
                    ->map(fn (Invoice $invoice): array => InvoiceData::fromCashier($invoice)->toCache())
                    ->values()
                    ->all();
            } catch (StripeException $e) {
                throw new BillingUnavailableException('Stripe invoices fetch failed.', 0, $e);
            }
        });

        return collect($rows)->map(fn (array $row): InvoiceData => InvoiceData::fromCache($row))->values();
    }

    public function forgetBillingCache(User $user): void
    {
        if ($user->account_id !== null) {
            $this->cache->forgetBilling($user->account_id);
        }
    }

    public function createCheckoutSession(User $user, string $priceId): string
    {
        $account = $this->requireAccount($user);

        return $account->newSubscription(self::SUBSCRIPTION_NAME, $priceId)
            ->checkout([
                'success_url' => route('billing.index').'?checkout=success',
                'cancel_url' => route('billing.index').'?checkout=cancel',
                'locale' => 'pt-BR',
                'allow_promotion_codes' => false,
            ])
            ->url;
    }

    /** Cancela ao fim do período pago. Retorna a data até quando o Profissional continua ativo. */
    public function cancelSubscription(User $user): ?CarbonInterface
    {
        $subscription = $this->billable($user)?->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription === null || $subscription->canceled()) {
            return $subscription?->ends_at;
        }

        return $subscription->cancel()->ends_at;
    }

    public function resumeSubscription(User $user): void
    {
        $subscription = $this->billable($user)?->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription !== null && $subscription->onGracePeriod()) {
            $subscription->resume();
        }
    }

    public function getBillingPortalUrl(User $user): string
    {
        return $this->requireAccount($user)->billingPortalUrl(route('billing.index'));
    }

    private function requireAccount(User $user): Account
    {
        $account = $this->billable($user);

        if ($account === null) {
            throw new \RuntimeException('Usuário sem conta associada.');
        }

        return $account;
    }

    /** A conta do usuário já é cliente no Stripe (tem assinatura/forma de pagamento)? */
    public function hasStripeCustomer(User $user): bool
    {
        $account = $this->billable($user);

        return $account !== null && $account->hasStripeId();
    }

    /**
     * Sync subscription data from Stripe to local database.
     * Used when webhooks are unavailable (local dev) or as a fallback
     * after checkout redirect.
     */
    public function syncSubscriptionFromStripe(User $user): void
    {
        $account = $this->billable($user);

        if ($account === null || !$account->hasStripeId()) {
            return;
        }

        // If already subscribed locally, no need to sync
        if ($account->subscribed(self::SUBSCRIPTION_NAME)) {
            return;
        }

        try {
            $stripeCustomer = $account->asStripeCustomer(['subscriptions']);
            $subscriptions = $stripeCustomer->subscriptions->data ?? [];

            foreach ($subscriptions as $stripeSubscription) {
                if ($stripeSubscription->status === 'active' || $stripeSubscription->status === 'trialing') {
                    $account->subscriptions()->create([
                        'type' => self::SUBSCRIPTION_NAME,
                        'stripe_id' => $stripeSubscription->id,
                        'stripe_status' => $stripeSubscription->status,
                        'stripe_price' => $stripeSubscription->items->data[0]->price->id ?? null,
                        'quantity' => $stripeSubscription->items->data[0]->quantity ?? 1,
                        'trial_ends_at' => $stripeSubscription->trial_end ? Carbon::createFromTimestamp($stripeSubscription->trial_end) : null,
                        'ends_at' => null,
                    ]);
                    break;
                }
            }
        } catch (\Exception $e) {
            // The webhook remains the source of truth.
            Log::warning('Stripe subscription sync failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function statusOf(Subscription $subscription): string
    {
        return match (true) {
            $subscription->onGracePeriod() => 'canceling',
            $subscription->ended(), $subscription->canceled() => 'canceled',
            $subscription->pastDue() => 'past_due',
            $subscription->incomplete() => 'incomplete',
            default => 'active',
        };
    }

    /** One Stripe call, no expand: items are returned by default. */
    private function fetchRemote(Subscription $local, string $accountId): ?StripeSubscription
    {
        if ($local->ended()) {
            return null;
        }

        try {
            return $local->asStripeSubscription();
        } catch (StripeException $e) {
            Log::warning('Stripe subscription fetch failed', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /** Period fields live on the subscription item since API "basil"; fall back to the subscription. */
    private static function periodTimestamp(StripeSubscription $remote, string $field): ?int
    {
        $item = isset($remote->items) ? ($remote->items->data[0] ?? null) : null;
        $value = $item !== null && isset($item->{$field}) ? $item->{$field} : null;

        if ($value === null && isset($remote->{$field})) {
            $value = $remote->{$field};
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
