<?php

declare(strict_types=1);

use Tests\Support\Personas;

/*
 * Accessibility + security cross-cutting concerns.
 *
 * Verifies that:
 *  - CSRF meta tag is present in all layouts
 *  - `lang` attribute is set to pt-BR
 *  - `autocomplete="off"` is on auth forms
 *  - Theme toggle is present
 *  - Skip-to-content link is present in app layout
 *  - Loading overlay is present on key pages
 *  - Footer credit is present on all pages
 */

$publicPages = ['/' => 'public', '/login' => 'guest', '/register' => 'guest', '/politica-de-privacidade' => 'public'];
$authPages   = ['clients.index', 'products.index', 'quotes.index', 'finance.index', 'appointments.index'];

it('sets lang="pt-BR" on all public pages', function (string $url): void {
    $html = (string) $this->get($url)->assertOk()->getContent();
    expect($html)->toContain('lang="pt-BR"');
})->with(['/', '/login', '/register', '/politica-de-privacidade', '/termos-de-uso']);

it('sets lang="pt-BR" on authenticated pages', function (string $route): void {
    $owner = Personas::owner();
    $html  = (string) $this->actingAs($owner)->get(route($route))->assertOk()->getContent();
    expect($html)->toContain('lang="pt-BR"');
})->with(['clients.index', 'products.index', 'finance.index']);

it('includes the CSRF meta tag on all pages', function (string $url): void {
    $html = (string) $this->get($url)->assertOk()->getContent();
    expect($html)->toContain('name="csrf-token"');
})->with(['/', '/login', '/register']);

it('includes the CSRF meta tag on authenticated pages', function (): void {
    $owner = Personas::owner();
    $html  = (string) $this->actingAs($owner)->get(route('clients.index'))->assertOk()->getContent();
    expect($html)->toContain('name="csrf-token"');
});

it('renders the footer credit on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();
    expect($html)->toContain('HOTWYL | WILLFROMBRASIL');
});

it('renders the footer credit on the login page', function (): void {
    $html = (string) $this->get('/login')->assertOk()->getContent();
    expect($html)->toContain('HOTWYL | WILLFROMBRASIL');
});

it('renders the footer credit on authenticated pages', function (): void {
    $owner = Personas::owner();
    $html  = (string) $this->actingAs($owner)->get(route('clients.index'))->assertOk()->getContent();
    expect($html)->toContain('HOTWYL | WILLFROMBRASIL');
});

it('renders a skip-to-content link on the app layout', function (): void {
    $owner = Personas::owner();
    $html  = (string) $this->actingAs($owner)->get(route('clients.index'))->assertOk()->getContent();
    // Skip-to-content is an accessibility link for keyboard users.
    expect($html)->toContain('Pular para');
});

it('renders the loading overlay div on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();
    expect($html)->toContain('id="loading-overlay"');
});

it('renders the loading overlay div on the login page', function (): void {
    $html = (string) $this->get('/login')->assertOk()->getContent();
    expect($html)->toContain('id="loading-overlay"');
});

it('renders the loading overlay div on authenticated pages', function (): void {
    $owner = Personas::owner();
    $html  = (string) $this->actingAs($owner)->get(route('clients.index'))->assertOk()->getContent();
    expect($html)->toContain('id="loading-overlay"');
});

it('renders the cookie consent banner on public pages', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();
    expect($html)->toContain('data-consent-banner');
});

it('renders the theme toggle button on the home page', function (): void {
    $html = (string) $this->get('/')->assertOk()->getContent();
    // The toggle button contains the moon or sun icon.
    expect($html)->toContain('fa-moon');
});

it('sets autocomplete="off" on the login form', function (): void {
    $html = (string) $this->get('/login')->assertOk()->getContent();
    expect($html)->toContain('autocomplete="off"');
});

it('sets autocomplete="off" on the register form', function (): void {
    $html = (string) $this->get('/register')->assertOk()->getContent();
    expect($html)->toContain('autocomplete="off"');
});
