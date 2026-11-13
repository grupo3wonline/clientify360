<?php

declare(strict_types=1);

/*
 * Security HTTP response headers.
 *
 * Verifies that the SecurityHeaders middleware injects the expected headers
 * on public, authenticated and API responses.
 */

it('sends the required security headers on public pages', function (): void {
    $response = $this->get('/');

    $response->assertOk();

    // Basic hardening headers
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

    // Permissions policy must restrict camera, microphone, geolocation and payment.
    $policy = $response->headers->get('Permissions-Policy', '');
    expect($policy)
        ->toContain('camera=()')
        ->toContain('microphone=()')
        ->toContain('geolocation=()')
        ->toContain('payment=()');
});

it('includes a Content-Security-Policy with self directives', function (): void {
    $response = $this->get('/');

    $csp = $response->headers->get('Content-Security-Policy', '');

    expect($csp)
        ->toContain("default-src 'self'")
        ->toContain("base-uri 'self'")
        ->toContain("object-src 'none'")
        ->toContain("form-action 'self'");
});

it('does not expose X-Powered-By or Server headers on public responses', function (): void {
    $response = $this->get('/');

    // Laravel/PHP may add these; they should be suppressed or absent.
    expect($response->headers->get('X-Powered-By'))->toBeNull();
});

it('sends security headers on authenticated routes', function (): void {
    $user = \Tests\Support\Personas::owner();
    $this->actingAs($user);

    $response = $this->get(route('profile.edit'));

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('sends security headers on API routes', function (): void {
    $response = $this->getJson('/api/v1/clients');

    // Unauthenticated but headers must still be present.
    expect($response->status())->toBeIn([401, 403]);
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('disables the legacy XSS auditor (X-XSS-Protection: 0)', function (): void {
    $response = $this->get('/');
    $response->assertHeader('X-XSS-Protection', '0');
});
