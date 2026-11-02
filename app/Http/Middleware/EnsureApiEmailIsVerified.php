<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API counterpart of the web "verified" middleware: a user whose e-mail is not
 * confirmed (never confirmed, or changed it and has not confirmed the new one)
 * cannot use their API tokens. Answers JSON 403 in the API error envelope instead
 * of the redirect the web middleware sends.
 */
final class EnsureApiEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof MustVerifyEmail && !$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Confirme seu e-mail para usar a API.'], 403);
        }

        return $next($request);
    }
}
