<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\AuthorizesWithinAccount;

final class ServiceOrderPolicy
{
    use AuthorizesWithinAccount;

    protected function resource(): string
    {
        return 'service_orders';
    }
}
