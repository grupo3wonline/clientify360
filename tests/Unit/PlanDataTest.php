<?php

declare(strict_types=1);

use App\Data\PlanData;

it('builds the professional plan from config', function (): void {
    $plan = PlanData::fromConfig('professional');

    expect($plan->slug)->toBe('professional')
        ->and($plan->name)->toBe('Profissional')
        ->and($plan->price)->toBe(29.90)
        ->and($plan->originalPrice)->toBe(79.90)
        ->and($plan->discountPercent())->toBe(62)
        ->and($plan->isFree())->toBeFalse()
        ->and($plan->formattedPrice())->toBe('R$ 29,90')
        ->and($plan->formattedOriginalPrice())->toBe('R$ 79,90')
        ->and($plan->limit('clients'))->toBe(-1)
        ->and($plan->hasFeature('api'))->toBeTrue();
});

it('builds the free basic plan from config', function (): void {
    $plan = PlanData::fromConfig('basic');

    expect($plan->isFree())->toBeTrue()
        ->and($plan->discountPercent())->toBe(0)
        ->and($plan->stripePriceId)->toBeNull()
        ->and($plan->limit('clients'))->toBe(10)
        ->and($plan->limit('unknown'))->toBe(-1)
        ->and($plan->hasFeature('export'))->toBeFalse();
});

it('lists every plan keyed by slug', function (): void {
    expect(array_keys(PlanData::catalog()))->toBe(['basic', 'professional']);
});

it('rejects an unknown plan', function (): void {
    PlanData::fromConfig('enterprise');
})->throws(InvalidArgumentException::class);
