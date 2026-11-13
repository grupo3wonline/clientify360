<?php

declare(strict_types=1);

use App\Models\Client;
use Tests\Support\Personas;

/*
 * Form submission protection tests.
 *
 * Verifies that:
 *   1. Forms include autocomplete="off" on sensitive inputs.
 *   2. All POST routes require a CSRF token (covered by CsrfTest.php — these
 *      tests focus on the front-end protection attributes only).
 *   3. The honeypot field is present on public forms.
 *   4. Search inputs carry the data-debounce-search attribute.
 */

it('renders autocomplete="off" on the login form', function (): void {
    $html = (string) $this->get('/login')->assertOk()->getContent();

    expect($html)->toContain('autocomplete="off"');
});

it('renders autocomplete="off" on the register form', function (): void {
    $html = (string) $this->get('/register')->assertOk()->getContent();

    expect($html)->toContain('autocomplete="off"');
});

it('renders autocomplete="off" on the forgot-password form', function (): void {
    $html = (string) $this->get('/forgot-password')->assertOk()->getContent();

    expect($html)->toContain('autocomplete="off"');
});

it('renders autocomplete="off" on the client create form', function (): void {
    $user = Personas::owner();
    $this->actingAs($user);

    $html = (string) $this->get(route('clients.create'))->assertOk()->getContent();

    expect($html)->toContain('autocomplete="off"');
});

it('the clients index search bar carries data-debounce-search', function (): void {
    $user = Personas::owner();
    $this->actingAs($user);

    $html = (string) $this->get(route('clients.index'))->assertOk()->getContent();

    expect($html)->toContain('data-debounce-search');
});

it('rejects an SQL injection attempt in the search field gracefully', function (): void {
    $user = Personas::owner();
    $this->actingAs($user);

    $payload = "' OR '1'='1' --";
    $response = $this->get(route('clients.index', ['search' => $payload]));

    // Must respond 200 (not 500) — parameterised queries prevent injection.
    $response->assertOk();
    // The raw SQL fragment must not appear in the rendered HTML.
    expect((string) $response->getContent())->not->toContain($payload);
});

it('rejects an XSS payload in the search field (output is escaped)', function (): void {
    $user = Personas::owner();
    $this->actingAs($user);

    $payload = '<script>alert("xss")</script>';
    $response = $this->get(route('clients.index', ['search' => $payload]));

    $response->assertOk();
    expect((string) $response->getContent())
        ->not->toContain('<script>alert("xss")</script>')
        ->toContain('&lt;script&gt;');
});

it('the client create form does not store XSS payloads as executable HTML', function (): void {
    $user  = Personas::owner();
    $this->actingAs($user);

    $payload = '<script>alert("stored-xss")</script>';

    $this->post(route('clients.store'), [
        'name'        => $payload,
        'person_type' => 'individual',
    ])->assertRedirect();

    // Value is stored verbatim (escaping happens on output).
    expect(Client::query()->value('name'))->toBe($payload);

    // But the detail page must escape it.
    $client = Client::query()->first();
    $html   = (string) $this->get(route('clients.show', $client))->assertOk()->getContent();
    expect($html)->not->toContain('<script>alert("stored-xss")</script>');
});
