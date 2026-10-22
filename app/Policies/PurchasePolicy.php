<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\AuthorizesWithinAccount;

final class PurchasePolicy
{
    use AuthorizesWithinAccount;

    protected function resource(): string
    {
        return 'purchases';
    }
}
