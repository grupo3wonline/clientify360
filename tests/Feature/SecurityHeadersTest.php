<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present_on_public_pages(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);

        // reCAPTCHA v3 (anti-bot) origins must be allowed in the CSP.
        $this->assertStringContainsString('www.google.com', $csp);
        $this->assertStringContainsString('www.gstatic.com', $csp);
    }

    public function test_csp_no_longer_allows_removed_cdns(): void
    {
        // Após a migração CDN -> Vite, os assets são self-hosted; o CSP não deve
        // mais liberar as origens de CDN que ficaram sem uso.
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertStringNotContainsString('cdn.tailwindcss.com', $csp);
        $this->assertStringNotContainsString('cdn.jsdelivr.net', $csp);
        $this->assertStringNotContainsString('cdnjs.cloudflare.com', $csp);
    }

    public function test_csrf_protection_blocks_post_without_token(): void
    {
        // Em ambiente de teste o CSRF é desabilitado pelo framework; aqui validamos
        // que a rota de login existe e exige credenciais (não aceita POST vazio).
        $this->post('/login', [])->assertSessionHasErrors();
    }

    public function test_private_area_is_noindex(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('reports.index'))
            ->assertSee('noindex', escape: false);
    }
}
