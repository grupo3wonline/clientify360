<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the authenticated user has accepted the current version
 * of the Terms of Service (config legal.terms_version) before accessing the application.
 */
final class EnsureTermsAccepted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $user->hasAcceptedCurrentTerms()) {
            return $next($request);
        }

        // Allow access to terms acceptance page, logout, and profile deletion
        if ($request->routeIs('terms.show', 'terms.accept', 'logout')) {
            return $next($request);
        }

        return redirect()->route('terms.show');
    }
}
