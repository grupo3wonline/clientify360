<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Client;
use App\Models\LeadForm;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Carbon\Carbon;

/**
 * A assinatura é da CONTA (tenant), não do usuário. Os métodos recebem o
 * usuário por conveniência dos controllers e resolvem a conta dele como a
 * entidade Billable do Cashier.
 */
final class SubscriptionService
{
    private const SUBSCRIPTION_NAME = 'default';

    /** Resolve a conta (entidade Billable) a partir do usuário. */
    private function billable(User $user): ?Account
    {
        return $user->account;
    }

    public function getCurrentPlan(User $user): array
    {
        $account = $this->billable($user);

        if ($account !== null && $account->subscribed(self::SUBSCRIPTION_NAME)) {
            return config('plans.professional');
        }

        return config('plans.basic');
    }

    public function getPlanSlug(User $user): string
    {
        return $this->getCurrentPlan($user)['slug'];
    }

    public function isProfessional(User $user): bool
    {
        $account = $this->billable($user);

        return $account !== null && $account->subscribed(self::SUBSCRIPTION_NAME);
    }

    public function isBasic(User $user): bool
    {
        return !$this->isProfessional($user);
    }

    public function canCreateClient(User $user, int $currentCount): bool
    {
        $plan = $this->getCurrentPlan($user);
        $limit = $plan['limits']['clients'];

        if ($limit === -1) {
            return true;
        }

        return $currentCount < $limit;
    }

    /** Rótulos usados nas mensagens de limite atingido. */
    public const RESOURCE_LABELS = [
        'clients' => 'clientes',
        'products' => 'produtos/serviços',
        'quotes_per_month' => 'orçamentos por mês',
        'lead_forms' => 'formulários de captação',
    ];

    /** Limite do plano para um recurso (-1 = ilimitado; recurso não configurado = ilimitado). */
    public function getLimit(User $user, string $resource): int
    {
        return (int) ($this->getCurrentPlan($user)['limits'][$resource] ?? -1);
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
            default => 0,
        };
    }

    public function canCreate(User $user, string $resource): bool
    {
        $limit = $this->getLimit($user, $resource);

        return $limit === -1 || $this->getUsage($user, $resource) < $limit;
    }

    public function getClientLimit(User $user): int
    {
        $plan = $this->getCurrentPlan($user);

        return $plan['limits']['clients'];
    }

    public function getRenewalDate(User $user): ?Carbon
    {
        $account = $this->billable($user);
        $subscription = $account?->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription === null) {
            return null;
        }

        return $subscription->asStripeSubscription()->current_period_end
            ? Carbon::createFromTimestamp($subscription->asStripeSubscription()->current_period_end)
            : null;
    }

    public function getDaysUntilRenewal(User $user): ?int
    {
        $renewalDate = $this->getRenewalDate($user);

        if ($renewalDate === null) {
            return null;
        }

        return (int) now()->diffInDays($renewalDate, false);
    }

    public function getPaymentHistory(User $user): array
    {
        $account = $this->billable($user);

        if ($account === null || !$account->hasStripeId()) {
            return [];
        }

        try {
            $invoices = $account->invoices();

            return $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'date' => Carbon::parse($invoice->date())->format('d/m/Y'),
                    'amount' => $invoice->total(),
                    'status' => $invoice->isPaid() ? 'Pago' : 'Pendente',
                    'pdf_url' => $invoice->asStripeInvoice()->invoice_pdf ?? null,
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getSubscriptionStatus(User $user): string
    {
        $account = $this->billable($user);
        $subscription = $account?->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription === null) {
            return 'free';
        }

        if ($subscription->onGracePeriod()) {
            return 'canceling';
        }

        if ($subscription->canceled()) {
            return 'canceled';
        }

        return 'active';
    }

    public function createCheckoutSession(User $user, string $priceId): string
    {
        $account = $this->requireAccount($user);

        return $account->newSubscription(self::SUBSCRIPTION_NAME, $priceId)
            ->checkout([
                'success_url' => route('billing.index').'?checkout=success',
                'cancel_url' => route('billing.index').'?checkout=cancel',
            ])
            ->url;
    }

    public function cancelSubscription(User $user): void
    {
        $subscription = $this->billable($user)?->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription !== null && !$subscription->canceled()) {
            $subscription->cancel();
        }
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
            // Silently fail — webhook will eventually sync
        }
    }
}
