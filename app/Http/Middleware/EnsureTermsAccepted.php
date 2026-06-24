<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has accepted the current version
 * of the Terms of Service before accessing the application.
 */
final class EnsureTermsAccepted
{
    private const CURRENT_VERSION = '1.0';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->terms_accepted_at !== null && $user->terms_version === self::CURRENT_VERSION) {
            return $next($request);
        }

        // Allow access to terms acceptance page, logout, and profile deletion
        if ($request->routeIs('terms.show', 'terms.accept', 'logout')) {
            return $next($request);
        }

        return redirect()->route('terms.show');
    }
}
