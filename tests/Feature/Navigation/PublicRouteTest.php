<?php

declare(strict_types=1);

use Tests\Support\Personas;

/*
 * Public route navigation tests.
 *
 * Verifies that every public-facing URL returns the expected HTTP status,
 * and that authenticated routes redirect unauthenticated visitors correctly.
 */

// ── Public pages (200) ──────────────────────────────────────────────────────

it('renders the home page', function (): void {
    $this->get('/')->assertOk();
});

it('renders the privacy policy page', function (): void {
    $this->get('/politica-de-privacidade')->assertOk();
});

it('renders the terms of service page for guests', function (): void {
    $this->get('/termos-de-uso')->assertOk();
});

it('renders the sitemap.xml', function (): void {
    $this->get('/sitemap.xml')->assertOk();
});

it('renders the robots.txt', function (): void {
    $this->get('/robots.txt')->assertOk();
});

it('renders the llms.txt for AI crawlers', function (): void {
    $this->get('/llms.txt')->assertOk();
});

// ── Auth pages (200 for guests) ─────────────────────────────────────────────

it('renders the login page for guests', function (): void {
    $this->get('/login')->assertOk();
});

it('renders the register page for guests', function (): void {
    $this->get('/register')->assertOk();
});

it('renders the forgot-password page for guests', function (): void {
    $this->get('/forgot-password')->assertOk();
});

// ── Old marketing anchor redirects (301) ────────────────────────────────────

it('redirects /sobre to /#sobre (301)', function (): void {
    $this->get('/sobre')->assertRedirect('/#sobre');
});

it('redirects /servicos to /#servicos (301)', function (): void {
    $this->get('/servicos')->assertRedirect('/#servicos');
});

it('redirects /ferramentas to /#ferramentas (301)', function (): void {
    $this->get('/ferramentas')->assertRedirect('/#ferramentas');
});

it('redirects /contato to /#contato (301)', function (): void {
    $this->get('/contato')->assertRedirect();
});

// ── Authentication guards ─────────────────────────────────────────────────

it('redirects unauthenticated users away from /dashboard', function (): void {
    $this->get('/dashboard')->assertRedirect('/login');
});

it('redirects unauthenticated users away from /profile', function (): void {
    $this->get('/profile')->assertRedirect('/login');
});

it('redirects unauthenticated users away from /clients', function (): void {
    $this->get('/clients')->assertRedirect('/login');
});

it('redirects unauthenticated users away from /billing', function (): void {
    $this->get('/billing')->assertRedirect('/login');
});

// ── Authenticated navigation (after login + verification + terms) ────────────

it('gives verified owners access to the clients index', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('clients.index'))
         ->assertOk();
});

it('gives verified owners access to the profile page', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('profile.edit'))
         ->assertOk();
});

it('gives verified owners access to the notifications page', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('notifications.index'))
         ->assertOk();
});

it('gives verified owners access to the reports page', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('reports.index'))
         ->assertOk();
});

it('gives verified owners access to the pipeline (deals) page', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('deals.index'))
         ->assertOk();
});

it('gives verified owners access to the products index', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('products.index'))
         ->assertOk();
});

it('gives verified owners access to the quotes index', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('quotes.index'))
         ->assertOk();
});

it('gives verified owners access to the finance dashboard', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('finance.index'))
         ->assertOk();
});

it('gives verified owners access to the appointments index', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('appointments.index'))
         ->assertOk();
});

it('gives verified owners access to the service orders index', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner)
         ->get(route('service-orders.index'))
         ->assertOk();
});

// ── Error pages ──────────────────────────────────────────────────────────────

it('renders a 404 page for unknown routes', function (): void {
    $this->get('/rota-que-nao-existe')->assertNotFound();
});

it('renders a 404 page for unknown lead form slugs', function (): void {
    $this->get('/f/slug-que-nao-existe')->assertNotFound();
});
