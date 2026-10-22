<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\AuthorizesWithinAccount;

/**
 * Autorização por CONTA + permissão (ACL por equipe). O isolamento de dados
 * em si já é garantido pelo global scope por conta.
 */
final class ClientPolicy
{
    use AuthorizesWithinAccount;

    protected function resource(): string
    {
        return 'clients';
    }
}
