<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * Sanctum token with a UUID primary key. No SoftDeletes: a revoked token
 * must be hard deleted so it can never authenticate again.
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;
}
