<?php

declare(strict_types=1);

namespace App\Support;

use Laravel\Cashier\Cashier;
use Stripe\StripeClient;

/**
 * Single source of Stripe clients for commands. Not final on purpose: tests
 * bind a subclass that returns a mocked client (no network).
 */
class StripeClientFactory
{
    public function make(): StripeClient
    {
        return Cashier::stripe();
    }
}
