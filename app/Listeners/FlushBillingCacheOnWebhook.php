<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\Account;
use App\Support\AccountCache;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Events\WebhookReceived;

/**
 * Clears the cached subscription summary and invoices of the account a Stripe
 * event belongs to. Listens to WebhookReceived (fired for every verified
 * event), not WebhookHandled, which skips events without a Cashier handler
 * such as invoice.payment_succeeded.
 */
final class FlushBillingCacheOnWebhook
{
    public function __construct(
        private readonly AccountCache $cache,
    ) {}

    public function handle(WebhookReceived $event): void
    {
        $object = $event->payload['data']['object'] ?? [];
        $customerId = ($object['object'] ?? null) === 'customer'
            ? ($object['id'] ?? null)
            : ($object['customer'] ?? null);

        $account = is_string($customerId) ? Account::where('stripe_id', $customerId)->first() : null;

        if ($account === null) {
            Log::debug('Stripe webhook without a matching account', ['type' => $event->payload['type'] ?? null]);

            return;
        }

        $this->cache->forgetBilling($account->id);
    }
}
