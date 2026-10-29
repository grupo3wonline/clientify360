<?php

declare(strict_types=1);

use Tests\Support\Personas;

/* AC-50 (automated part), AC-52, R-PRIV-1 to R-PRIV-3: GTM gated by consent. */

beforeEach(function (): void {
    // x-analytics is disabled in testing/local; production with GTM/GA4 ids set is the case to guard.
    $this->app->detectEnvironment(fn (): string => 'production');
    config(['analytics.gtm_id' => 'GTM-TEST123', 'analytics.ga4_id' => 'G-TEST123']);
});

function assertConsentGated(string $html): void
{
    expect($html)->toContain('window.__consentConfig = ')
        ->toContain('GTM-TEST123')
        ->toContain("gtag('consent', 'default', { ad_storage: 'denied', analytics_storage: 'denied'")
        ->toContain('data-consent-banner')
        ->not->toMatch('#<script[^>]+googletagmanager\.com#')
        ->not->toContain('googletagmanager.com/gtm.js')
        ->not->toContain('googletagmanager.com/gtag/js')
        ->not->toMatch('#<noscript>\s*<iframe#');
}

it('never renders the GTM/GA4 tags on Blade pages, only the consent config', function (): void {
    foreach ([route('home'), route('privacy'), route('terms.show'), route('login')] as $url) {
        assertConsentGated((string) $this->get($url)->assertOk()->getContent());
    }

    $this->actingAs(Personas::owner());
    assertConsentGated((string) $this->get(route('profile.edit'))->assertOk()->getContent());
});

it('never renders the GTM/GA4 tags on the Filament panel, only the consent config', function (): void {
    $this->actingAs(Personas::owner());

    assertConsentGated((string) $this->get('/app')->assertOk()->getContent());
});

it('renders the banner with the three categories and the pt-BR buttons', function (): void {
    $this->get(route('home'))
        ->assertSee('Sua privacidade importa')
        ->assertSee('Aceitar todos')
        ->assertSee('Rejeitar opcionais')
        ->assertSee('Personalizar')
        ->assertSee('Salvar preferências')
        ->assertSee('Necessários')
        ->assertSee('Sempre ativos')
        ->assertSee('data-consent-category="analytics"', false)
        ->assertSee('data-consent-category="marketing"', false)
        ->assertSee('"endpoint":"'.route('consent.store').'"', false);
});

it('has the interactive cookie-preferences and do-not-sell links in the Blade and panel footers', function (): void {
    $privacy = url('/politica-de-privacidade');

    $pages = [(string) $this->get(route('home'))->getContent()];
    $this->actingAs(Personas::owner());
    $pages[] = (string) $this->get(route('profile.edit'))->getContent();
    $pages[] = (string) $this->get('/app')->getContent();

    foreach ($pages as $html) {
        expect($html)->toContain('href="'.$privacy.'#cookies" data-cookie-preferences')
            ->toContain('href="'.$privacy.'#nao-vender" data-do-not-sell')
            ->toContain('Preferências de cookies')
            ->toContain('Não vender ou compartilhar minhas informações pessoais');
    }
});

it('keeps consent and analytics out of the error pages', function (): void {
    $html = (string) $this->get('/pagina-que-nao-existe')->assertNotFound()->getContent();

    expect($html)->not->toContain('__consentConfig')
        ->not->toContain('data-consent-banner')
        ->not->toContain('GTM-TEST123');
});

it('loads the consent module from the Blade and panel bundles', function (): void {
    expect(file_get_contents(resource_path('js/app.js')))->toContain("import { initConsent } from './consent'")
        ->and(file_get_contents(resource_path('js/panel.js')))->toContain("import { initConsent } from './consent'")
        ->and(file_get_contents(resource_path('js/consent.js')))
        ->toContain('if (consent.analytics || consent.marketing)')
        ->toContain("'https://www.googletagmanager.com/gtm.js?id='")
        ->toContain('globalPrivacyControl');
});
