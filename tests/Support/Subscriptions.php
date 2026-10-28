<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\Account;
use Laravel\Cashier\Subscription;

/** Local Cashier rows only: nothing here talks to Stripe. */
final class Subscriptions
{
    public static function makeProfessional(Account $account): Subscription
    {
        return self::create($account, ['stripe_status' => 'active', 'ends_at' => null]);
    }

    /** A subscription that was canceled and whose paid period is over (account back on Básico). */
    public static function makeEnded(Account $account): Subscription
    {
        return self::create($account, ['stripe_status' => 'canceled', 'ends_at' => now()->subDay()]);
    }

    /** Canceled at period end, still inside the paid period (grace period). */
    public static function makeCanceling(Account $account, ?\DateTimeInterface $endsAt = null): Subscription
    {
        return self::create($account, ['stripe_status' => 'active', 'ends_at' => $endsAt ?? now()->addDays(10)]);
    }

    /** @param  array<string, mixed>  $attributes */
    private static function create(Account $account, array $attributes): Subscription
    {
        if (!$account->hasStripeId()) {
            $account->forceFill(['stripe_id' => 'cus_test_'.$account->id])->save();
        }

        $subscription = $account->subscriptions()->create(array_merge([
            'type' => 'default',
            'stripe_id' => 'sub_test_'.uniqid(),
            'stripe_price' => 'price_test',
            'quantity' => 1,
        ], $attributes));

        $account->unsetRelation('subscriptions');

        return $subscription;
    }
}
