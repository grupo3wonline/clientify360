<?php

declare(strict_types=1);

use App\Support\AccountCache;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Events\WebhookReceived;
use Tests\Support\Personas;

beforeEach(function (): void {
    $this->owner = Personas::owner();
    $this->account = $this->owner->account;
    $this->account->forceFill(['stripe_id' => 'cus_flush_test'])->save();

    $this->cache = app(AccountCache::class);
    $this->cache->putBilling($this->account->id, 'invoices', [['id' => 'in_1']], 1800);
    $this->cache->putBilling($this->account->id, 'summary', ['status' => 'active'], 1800);
});

function invoicePaidPayload(string $customer): array
{
    return [
        'id' => 'evt_test',
        'type' => 'invoice.payment_succeeded',
        'data' => ['object' => ['object' => 'invoice', 'id' => 'in_1', 'customer' => $customer]],
    ];
}

it('clears the account billing cache on WebhookReceived (AC-72)', function (): void {
    event(new WebhookReceived(invoicePaidPayload('cus_flush_test')));

    expect(Cache::has($this->cache->billingKey($this->account->id, 'invoices')))->toBeFalse()
        ->and(Cache::has($this->cache->billingKey($this->account->id, 'summary')))->toBeFalse();
});

it('leaves other accounts untouched', function (): void {
    event(new WebhookReceived(invoicePaidPayload('cus_someone_else')));

    expect(Cache::has($this->cache->billingKey($this->account->id, 'invoices')))->toBeTrue();
});

it('flushes through the real webhook route', function (): void {
    config(['cashier.webhook.secret' => null]);

    $this->postJson('/stripe/webhook', invoicePaidPayload('cus_flush_test'))->assertOk();

    expect(Cache::has($this->cache->billingKey($this->account->id, 'invoices')))->toBeFalse();
});
