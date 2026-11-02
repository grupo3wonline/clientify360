<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reminders about things that need the user's attention before they become a problem:
 * the password lifetime and the subscription renewal. One source for the notification
 * bell, the page banner and the panel, so the rule and the wording live in one place.
 *
 * Each alert is: key (stable, dismissible), type, level (warning|danger), title,
 * message, url and time (when it expires/renews).
 */
final class AccountAlertService
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
    ) {}

    /**
     * @return list<array{id: string, type: string, level: string, title: string, message: string, url: string, time: CarbonImmutable}>
     */
    public function for(User $user): array
    {
        return array_values(array_filter([
            $this->passwordAlert($user),
            $this->subscriptionAlert($user),
        ]));
    }

    /** @return array{id: string, type: string, level: string, title: string, message: string, url: string, time: CarbonImmutable}|null */
    public function passwordAlert(User $user): ?array
    {
        if (!$user->passwordExpiresSoon()) {
            return null;
        }

        $days = (int) $user->passwordDaysLeft();
        $when = match (true) {
            $days === 0 => 'expira hoje',
            $days === 1 => 'expira amanhã',
            default => "expira em {$days} dias",
        };

        return [
            // Day-based key: dismissing it hides it for today, and it comes back tomorrow with the new count.
            'id' => 'password-expiring-'.now()->toDateString(),
            'type' => 'password_expiring',
            'level' => $days <= 1 ? 'danger' : 'warning',
            'title' => 'Sua senha vai expirar',
            'message' => "Sua senha {$when} (em ".$user->passwordExpiresAt()->format('d/m/Y').'). Troque agora para não perder o acesso.',
            'url' => route('profile.edit').'#atualizar-senha',
            'time' => $user->passwordExpiresAt(),
        ];
    }

    /**
     * Renewal (or end of a canceled plan) within the warning window. Only for who can act on
     * billing (account.billing, i.e. the owner): the subscription belongs to the account.
     *
     * @return array{id: string, type: string, level: string, title: string, message: string, url: string, time: CarbonImmutable}|null
     */
    public function subscriptionAlert(User $user): ?array
    {
        if (!$user->can('account.billing')) {
            return null;
        }

        // This runs on every authenticated page (menu badge). A billing outage must never take the
        // page down: no alert is better than an error screen. SubscriptionService only catches
        // StripeException, so a network-level failure would otherwise reach the user.
        try {
            $summary = $this->subscriptions->summary($user);
        } catch (Throwable $e) {
            Log::warning('Subscription alert skipped: billing summary unavailable', ['exception' => $e::class]);

            return null;
        }

        // Free plan has nothing to renew; a degraded summary has no reliable dates.
        if ($summary->status === 'free' || $summary->degraded) {
            return null;
        }

        $end = $summary->countdownEnd();

        if ($end === null) {
            return null;
        }

        $days = (int) now()->startOfDay()->diffInDays($end->startOfDay(), false);

        if ($days < 0 || $days > (int) config('security.subscription_warning_days')) {
            return null;
        }

        $date = $end->format('d/m/Y');
        $when = match (true) {
            $days === 0 => 'hoje',
            $days === 1 => 'amanhã',
            default => "em {$days} dias",
        };

        if ($summary->cancelAtPeriodEnd) {
            $title = 'Sua assinatura vai encerrar';
            $message = "O plano {$summary->plan->name} encerra {$when} ({$date}). Retome a assinatura para manter os recursos.";
            $level = 'danger';
        } elseif (in_array($summary->status, ['past_due', 'incomplete'], true)) {
            $title = 'Pagamento pendente';
            $message = "Há um pagamento pendente e o período atual termina {$when} ({$date}). Regularize para não perder o plano {$summary->plan->name}.";
            $level = 'danger';
        } else {
            $title = 'Sua assinatura renova em breve';
            $message = "O plano {$summary->plan->name} renova {$when} ({$date}). Confira o cartão e os dados de cobrança.";
            $level = 'warning';
        }

        return [
            // The period end is part of the key: after the renewal the next cycle gets a new alert.
            'id' => 'subscription-renewal-'.$end->format('Ymd'),
            'type' => 'subscription_renewal',
            'level' => $level,
            'title' => $title,
            'message' => $message,
            'url' => route('billing.index'),
            'time' => $end,
        ];
    }
}
