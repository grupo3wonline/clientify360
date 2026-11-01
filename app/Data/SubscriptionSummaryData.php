<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

/**
 * Stripe-derived subscription state shown on "Minha assinatura".
 * Usage counters are not part of it: the summary is cached, usage is not.
 */
final class SubscriptionSummaryData extends Data
{
    public const STATUS_LABELS = [
        'free' => 'Gratuito',
        'active' => 'Ativa',
        'canceling' => 'Cancelamento agendado',
        'past_due' => 'Pagamento pendente',
        'incomplete' => 'Pagamento pendente',
        'canceled' => 'Encerrada',
    ];

    public function __construct(
        public readonly PlanData $plan,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly ?CarbonImmutable $periodStart,
        public readonly ?CarbonImmutable $periodEnd,
        public readonly bool $cancelAtPeriodEnd,
        public readonly ?CarbonImmutable $endsAt,
        /** True when Stripe could not be reached and the period dates are unknown. */
        public readonly bool $degraded = false,
    ) {}

    /** @return array<string, mixed> Scalars only (the cache does not unserialize objects). */
    public function toCache(): array
    {
        return [
            'plan' => $this->plan->slug,
            'status' => $this->status,
            'period_start' => $this->periodStart?->getTimestamp(),
            'period_end' => $this->periodEnd?->getTimestamp(),
            'cancel_at_period_end' => $this->cancelAtPeriodEnd,
            'ends_at' => $this->endsAt?->getTimestamp(),
        ];
    }

    /** @param  array<string, mixed>  $row */
    public static function fromCache(array $row): self
    {
        $date = static fn (mixed $ts): ?CarbonImmutable => is_int($ts)
            ? CarbonImmutable::createFromTimestamp($ts, config('app.timezone'))
            : null;

        return new self(
            plan: PlanData::fromConfig((string) $row['plan']),
            status: (string) $row['status'],
            statusLabel: self::labelFor((string) $row['status']),
            periodStart: $date($row['period_start'] ?? null),
            periodEnd: $date($row['period_end'] ?? null),
            cancelAtPeriodEnd: (bool) ($row['cancel_at_period_end'] ?? false),
            endsAt: $date($row['ends_at'] ?? null),
        );
    }

    public static function labelFor(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? self::STATUS_LABELS['canceled'];
    }

    /** End of the current access/renewal period, if known. */
    public function countdownEnd(): ?CarbonImmutable
    {
        return $this->cancelAtPeriodEnd ? ($this->endsAt ?? $this->periodEnd) : $this->periodEnd;
    }
}
