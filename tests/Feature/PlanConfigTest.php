<?php

declare(strict_types=1);

use App\Data\PlanData;

it('prices the professional plan at 29,90 with a 79,90 reference (62% OFF)', function (): void {
    expect(config('plans.professional.price'))->toBe(29.90)
        ->and(config('plans.professional.original_price'))->toBe(79.90)
        ->and(config('plans.professional'))->not->toHaveKey('discount_percent')
        ->and(PlanData::fromConfig('professional')->discountPercent().'% OFF')->toBe('62% OFF');
});

it('declares the same limits and feature flags on both plans', function (): void {
    $resources = ['clients', 'products', 'quotes_per_month', 'lead_forms', 'client_attachments'];
    $features = ['ai', 'export', 'pdf', 'api'];

    foreach (['basic', 'professional'] as $slug) {
        expect(array_keys(config("plans.{$slug}.limits")))->toBe($resources)
            ->and(array_keys(config("plans.{$slug}.features_enabled")))->toBe($features);
    }

    expect(config('plans.basic.features_enabled'))->each->toBeFalse()
        ->and(config('plans.professional.features_enabled'))->each->toBeTrue()
        ->and(config('plans.basic.limits'))->toBe([
            'clients' => 10, 'products' => 20, 'quotes_per_month' => 5, 'lead_forms' => 1, 'client_attachments' => 10,
        ]);
});

it('makes no unbacked marketing claims', function (): void {
    $copy = implode(' ', array_merge(config('plans.basic.features'), config('plans.professional.features')));

    expect($copy)->not->toContain('Suporte prioritário');
});
