<?php

declare(strict_types=1);

use App\Support\AccountCache;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    Cache::flush();
    $this->cache = new AccountCache;
});

it('remembers a value until the account version is bumped', function (): void {
    $calls = 0;
    $compute = function () use (&$calls): int {
        return ++$calls;
    };

    expect($this->cache->remember('acc-a', 'stats', 600, $compute))->toBe(1)
        ->and($this->cache->remember('acc-a', 'stats', 600, $compute))->toBe(1);

    $this->cache->bump('acc-a');

    expect($this->cache->remember('acc-a', 'stats', 600, $compute))->toBe(2);

    $this->cache->bump('acc-a');

    expect($this->cache->remember('acc-a', 'stats', 600, $compute))->toBe(3);
});

it('isolates versions between accounts', function (): void {
    $this->cache->remember('acc-a', 'stats', 600, fn (): string => 'A');
    $this->cache->remember('acc-b', 'stats', 600, fn (): string => 'B');

    $this->cache->bump('acc-a');

    expect($this->cache->remember('acc-a', 'stats', 600, fn (): string => 'A2'))->toBe('A2')
        ->and($this->cache->remember('acc-b', 'stats', 600, fn (): string => 'B2'))->toBe('B');
});

it('does not evict billing keys on bump and forgets them on forgetBilling', function (): void {
    $this->cache->rememberBilling('acc-a', 'invoices', 1800, fn (): array => ['inv_1']);
    $this->cache->putBilling('acc-a', 'summary', ['status' => 'active'], 1800);

    $this->cache->bump('acc-a');

    expect($this->cache->rememberBilling('acc-a', 'invoices', 1800, fn (): array => ['other']))->toBe(['inv_1'])
        ->and($this->cache->getBilling('acc-a', 'summary'))->toBe(['status' => 'active']);

    $this->cache->forgetBilling('acc-a');

    expect(Cache::has($this->cache->billingKey('acc-a', 'invoices')))->toBeFalse()
        ->and($this->cache->getBilling('acc-a', 'summary'))->toBeNull();
});

it('lets callback exceptions propagate after a single call', function (): void {
    $calls = 0;

    expect(function () use (&$calls): void {
        $this->cache->rememberBilling('acc-a', 'invoices', 1800, function () use (&$calls): never {
            $calls++;
            throw new RuntimeException('stripe down');
        });
    })->toThrow(RuntimeException::class, 'stripe down');

    expect($calls)->toBe(1);
});

it('computes uncached and never throws when the store fails', function (): void {
    Cache::shouldReceive('get')->andThrow(new RuntimeException('store down'));
    Cache::shouldReceive('has')->andThrow(new RuntimeException('store down'));
    Cache::shouldReceive('put')->andThrow(new RuntimeException('store down'));
    Cache::shouldReceive('forget')->andThrow(new RuntimeException('store down'));

    expect($this->cache->remember('acc-a', 'stats', 600, fn (): string => 'fresh'))->toBe('fresh')
        ->and($this->cache->rememberBilling('acc-a', 'summary', 600, fn (): string => 'fresh'))->toBe('fresh')
        ->and($this->cache->getBilling('acc-a', 'summary'))->toBeNull();

    $this->cache->bump('acc-a');
    $this->cache->forgetBilling('acc-a');
});
