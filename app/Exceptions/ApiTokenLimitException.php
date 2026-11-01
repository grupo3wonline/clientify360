<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/** The user already has the maximum number of active API tokens. */
final class ApiTokenLimitException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Limite de 5 tokens ativos atingido. Revogue um token antes de criar outro.');
    }
}
