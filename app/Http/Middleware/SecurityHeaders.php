<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds security headers to all HTTP responses to protect against
 * common web vulnerabilities (XSS, clickjacking, MIME sniffing, etc.).
 */
final class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        // HSTS apenas sob HTTPS, para não bloquear ambientes locais em HTTP.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Build the Content-Security-Policy.
     *
     * Front-end assets (Tailwind, Alpine, SweetAlert2, Font Awesome) are now
     * bundled via Vite and served from 'self' — no more CDN origins. The only
     * remaining external origins are Google Fonts (Inter webfont), Google Tag
     * Manager/Analytics, and Stripe (billing checkout). 'unsafe-inline'/'eval'
     * stay for Filament and the inline theme bootstrap.
     */
    private function contentSecurityPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://www.google-analytics.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https: https://www.googletagmanager.com https://www.google-analytics.com",
            "connect-src 'self' https://api.stripe.com https://www.google-analytics.com https://www.googletagmanager.com https://region1.google-analytics.com",
            "frame-src 'self' https://js.stripe.com https://www.googletagmanager.com",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            'upgrade-insecure-requests',
        ];

        return implode('; ', $directives);
    }
}
