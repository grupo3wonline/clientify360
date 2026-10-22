<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * Define o contexto de equipe do Spatie Permission a partir da conta do
 * usuário autenticado, para que papéis/permissões sejam avaliados dentro
 * da conta correta (isolamento de ACL por tenant).
 */
final class SetSpatieTeam
{
    public function __construct(
        private readonly PermissionRegistrar $registrar
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->account_id !== null) {
            $this->registrar->setPermissionsTeamId($user->account_id);
        }

        return $next($request);
    }
}
