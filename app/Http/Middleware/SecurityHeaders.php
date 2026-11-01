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
        // Modern recommendation: disable the legacy XSS auditor (removed from browsers).
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy($request));

        // HSTS only over HTTPS, so local HTTP environments are not locked out.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /**
     * Build the Content-Security-Policy.
     *
     * Assets are bundled by Vite and fonts are local, so only Google Tag
     * Manager/Analytics and reCAPTCHA v3 (www.google.com / www.gstatic.com)
     * remain as external script origins. Stripe Checkout and the billing portal
     * are full-page redirects (no Stripe.js), so Stripe only appears in
     * form-action, which Chromium enforces on the redirect chain of a form POST.
     * 'unsafe-inline' and 'unsafe-eval' stay because Livewire/Alpine evaluate
     * expressions and Filament emits inline scripts.
     */
    private function contentSecurityPolicy(Request $request): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://www.google-analytics.com https://www.google.com https://www.gstatic.com",
            "style-src 'self' 'unsafe-inline'",
            "font-src 'self' data:",
            "img-src 'self' data: https:",
            "connect-src 'self' https://www.google-analytics.com https://region1.google-analytics.com https://www.googletagmanager.com",
            "frame-src 'self' https://www.googletagmanager.com https://www.google.com",
            "base-uri 'self'",
            "form-action 'self' https://checkout.stripe.com https://billing.stripe.com",
            "frame-ancestors 'self'",
            "object-src 'none'",
        ];

        // Only upgrade over HTTPS, so local HTTP is not rewritten.
        if ($request->secure()) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }
}
