<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks every authenticated area while the password is older than the configured
 * lifetime (config/security.php, default 90 days) until the user sets a new one.
 *
 * Web and panel: redirect to the "password expired" page. API: JSON 403, so a script or
 * integration holding a token learns why it stopped working instead of following a redirect.
 */
final class EnsurePasswordNotExpired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user instanceof User || !$user->passwordExpired()) {
            return $next($request);
        }

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['message' => 'Sua senha expirou. Acesse o sistema e defina uma nova senha.'], 403);
        }

        return redirect()->route('password.expired');
    }
}
