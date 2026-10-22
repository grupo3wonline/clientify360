<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\AuthorizesWithinAccount;

final class AppointmentPolicy
{
    use AuthorizesWithinAccount;

    protected function resource(): string
    {
        return 'appointments';
    }
}
