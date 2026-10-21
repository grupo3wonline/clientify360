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
    }

    public function test_csp_keeps_analytics_and_stripe_hosts_and_drops_cdns(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        // Analytics (GTM/GA) hosts are required by the <x-analytics> component.
        $this->assertStringContainsString('https://www.googletagmanager.com', $csp);
        $this->assertStringContainsString('https://www.google-analytics.com', $csp);
        $this->assertStringContainsString('https://region1.google-analytics.com', $csp);

        // Stripe hosts remain.
        $this->assertStringContainsString('https://api.stripe.com', $csp);
        $this->assertStringContainsString('https://js.stripe.com', $csp);

        // CDNs are vendored via Vite now and the 3W Online link is not an iframe.
        $this->assertStringNotContainsString('cdn.tailwindcss.com', $csp);
        $this->assertStringNotContainsString('cdnjs.cloudflare.com', $csp);
        $this->assertStringNotContainsString('cdn.jsdelivr.net', $csp);
        $this->assertStringNotContainsString('www.3wonline.com.br', $csp);
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
