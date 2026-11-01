<?php

declare(strict_types=1);

use App\Data\PlanData;
use App\Data\SubscriptionSummaryData;
use App\Services\SubscriptionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Subscription;
use Stripe\Subscription as StripeSubscription;

function localSubscription(array $attributes): Subscription
{
    return (new Subscription)->forceFill(array_merge(['type' => 'default', 'stripe_status' => 'active', 'ends_at' => null], $attributes));
}

function summaryEnding(?CarbonImmutable $periodEnd, ?CarbonImmutable $endsAt = null): SubscriptionSummaryData
{
    return new SubscriptionSummaryData(
        plan: PlanData::fromConfig('professional'),
        status: 'active',
        statusLabel: 'Ativa',
        periodStart: null,
        periodEnd: $periodEnd,
        cancelAtPeriodEnd: $endsAt !== null,
        endsAt: $endsAt,
    );
}

beforeEach(function (): void {
    $this->service = app(SubscriptionService::class);
});

afterEach(function (): void {
    Carbon::setTestNow();
    CarbonImmutable::setTestNow();
});

it('reads the period from the first subscription item (no network)', function (): void {
    $remote = StripeSubscription::constructFrom([
        'id' => 'sub_1',
        'status' => 'active',
        'items' => ['object' => 'list', 'data' => [[
            'id' => 'si_1',
            'current_period_start' => 1767225600, // 2026-01-01 00:00 UTC
            'current_period_end' => 1769904000,   // 2026-02-01 00:00 UTC
        ]]],
    ]);

    $summary = $this->service->buildSummary(localSubscription([]), $remote);

    expect($summary->plan->slug)->toBe('professional')
        ->and($summary->status)->toBe('active')
        ->and($summary->statusLabel)->toBe('Ativa')
        ->and($summary->periodStart?->getTimestamp())->toBe(1767225600)
        ->and($summary->periodEnd?->getTimestamp())->toBe(1769904000)
        ->and($summary->periodEnd?->timezoneName)->toBe('America/Sao_Paulo')
        ->and($summary->degraded)->toBeFalse();
});

it('falls back to subscription-level period fields', function (): void {
    $remote = StripeSubscription::constructFrom(['id' => 'sub_1', 'current_period_end' => 1769904000]);

    expect($this->service->buildSummary(localSubscription([]), $remote)->periodEnd?->getTimestamp())->toBe(1769904000);
});

it('labels every subscription status in pt-BR', function (array $attributes, string $status, string $label, string $plan): void {
    $summary = $this->service->buildSummary(localSubscription($attributes), StripeSubscription::constructFrom(['id' => 'sub_1']));

    expect($summary->status)->toBe($status)
        ->and($summary->statusLabel)->toBe($label)
        ->and($summary->plan->slug)->toBe($plan);
})->with([
    'active' => [[], 'active', 'Ativa', 'professional'],
    'canceling' => [['ends_at' => now()->addDays(5)], 'canceling', 'Cancelamento agendado', 'professional'],
    'past_due' => [['stripe_status' => 'past_due'], 'past_due', 'Pagamento pendente', 'basic'],
    'incomplete' => [['stripe_status' => 'incomplete'], 'incomplete', 'Pagamento pendente', 'basic'],
    'ended' => [['stripe_status' => 'canceled', 'ends_at' => now()->subDay()], 'canceled', 'Encerrada', 'basic'],
]);

it('returns the free summary without a subscription', function (): void {
    $summary = $this->service->buildSummary(null, null);

    expect($summary->status)->toBe('free')
        ->and($summary->statusLabel)->toBe('Gratuito')
        ->and($summary->plan->slug)->toBe('basic')
        ->and($summary->periodEnd)->toBeNull();
});

it('marks the summary degraded when Stripe could not be reached', function (): void {
    $summary = $this->service->buildSummary(localSubscription([]), null);

    expect($summary->degraded)->toBeTrue()
        ->and($summary->periodStart)->toBeNull()
        ->and($summary->periodEnd)->toBeNull();
});

it('round-trips through the cache as scalars only', function (): void {
    $summary = summaryEnding(CarbonImmutable::createFromTimestamp(1769904000));
    $row = $summary->toCache();

    foreach ($row as $value) {
        expect($value === null || is_scalar($value))->toBeTrue();
    }

    $restored = SubscriptionSummaryData::fromCache($row);

    expect($restored->periodEnd?->getTimestamp())->toBe(1769904000)
        ->and($restored->plan->slug)->toBe('professional')
        ->and($restored->statusLabel)->toBe('Ativa');
});

it('computes the summary TTL from the period end (AC-80)', function (?int $secondsAhead, int $ttl): void {
    $now = CarbonImmutable::parse('2026-03-01 12:00:00');
    Carbon::setTestNow($now);
    CarbonImmutable::setTestNow($now);

    $end = $secondsAhead === null ? null : $now->addSeconds($secondsAhead);

    expect($this->service->summaryTtl(summaryEnding($end)))->toBe($ttl);
})->with([
    '10 minutes' => [600, 600],
    '10 seconds' => [10, 60],
    'unknown' => [null, 1800],
    '2 days' => [2 * 86400, 1800],
]);

it('prefers ends_at over the period end for the TTL', function (): void {
    $now = CarbonImmutable::parse('2026-03-01 12:00:00');
    Carbon::setTestNow($now);

    expect($this->service->summaryTtl(summaryEnding($now->addDays(3), $now->addMinutes(10))))->toBe(600);
});
