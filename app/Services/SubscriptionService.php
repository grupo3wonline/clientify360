<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

final class SubscriptionService
{
    private const PLAN_BASIC = 'basic';
    private const PLAN_PROFESSIONAL = 'professional';
    private const SUBSCRIPTION_NAME = 'default';

    public function getCurrentPlan(User $user): array
    {
        if ($user->subscribed(self::SUBSCRIPTION_NAME)) {
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
        return $user->subscribed(self::SUBSCRIPTION_NAME);
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
     * Uso atual do recurso. Os models têm escopo global por usuário, mas aqui
     * filtramos explicitamente por user_id para funcionar também fora de uma
     * requisição autenticada (ex.: formulário público de captação).
     */
    public function getUsage(User $user, string $resource): int
    {
        return match ($resource) {
            'clients' => \App\Models\Client::withoutGlobalScope('owned')->where('user_id', $user->id)->count(),
            'products' => \App\Models\Product::withoutGlobalScope('owned')->where('user_id', $user->id)->count(),
            'lead_forms' => \App\Models\LeadForm::withoutGlobalScope('owned')->where('user_id', $user->id)->count(),
            'quotes_per_month' => \App\Models\Quote::withoutGlobalScope('owned')->withTrashed()
                ->where('user_id', $user->id)
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
        $subscription = $user->subscription(self::SUBSCRIPTION_NAME);

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
        if (!$user->hasStripeId()) {
            return [];
        }

        try {
            $invoices = $user->invoices();

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
        $subscription = $user->subscription(self::SUBSCRIPTION_NAME);

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
        return $user->newSubscription(self::SUBSCRIPTION_NAME, $priceId)
            ->checkout([
                'success_url' => route('billing.index') . '?checkout=success',
                'cancel_url' => route('billing.index') . '?checkout=cancel',
            ])
            ->url;
    }

    public function cancelSubscription(User $user): void
    {
        $subscription = $user->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription !== null && !$subscription->canceled()) {
            $subscription->cancel();
        }
    }

    public function resumeSubscription(User $user): void
    {
        $subscription = $user->subscription(self::SUBSCRIPTION_NAME);

        if ($subscription !== null && $subscription->onGracePeriod()) {
            $subscription->resume();
        }
    }

    public function getBillingPortalUrl(User $user): string
    {
        return $user->billingPortalUrl(route('billing.index'));
    }

    /**
     * Sync subscription data from Stripe to local database.
     * Used when webhooks are unavailable (local dev) or as a fallback
     * after checkout redirect.
     */
    public function syncSubscriptionFromStripe(User $user): void
    {
        if (!$user->hasStripeId()) {
            return;
        }

        // If already subscribed locally, no need to sync
        if ($user->subscribed(self::SUBSCRIPTION_NAME)) {
            return;
        }

        try {
            $stripeCustomer = $user->asStripeCustomer(['subscriptions']);
            $subscriptions = $stripeCustomer->subscriptions->data ?? [];

            foreach ($subscriptions as $stripeSubscription) {
                if ($stripeSubscription->status === 'active' || $stripeSubscription->status === 'trialing') {
                    $user->subscriptions()->create([
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
