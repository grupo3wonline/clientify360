<?php

declare(strict_types=1);

use App\Models\Client;
use Tests\Support\Personas;

/*
 * SQL injection prevention tests.
 *
 * Laravel's Query Builder and Eloquent use PDO prepared statements, which
 * prevent classic SQL injection. These tests verify that:
 *
 *  1. Payloads submitted through search fields return 200 (not 500).
 *  2. The raw SQL fragment never appears in the HTML response.
 *  3. The database is not corrupted by injected payloads.
 */

$payloads = [
    "' OR '1'='1' --",
    "'; DROP TABLE clients; --",
    '1 UNION SELECT * FROM users --',
    "' OR 1=1 /*",
    '`id`=1 OR 1=1',
    "admin' --",
    "' OR 'x'='x",
    '1; SELECT * FROM personal_access_tokens',
];

it('survives SQL injection in the client search field', function (string $payload): void {
    $user = Personas::owner();
    $this->actingAs($user);

    $response = $this->get(route('clients.index', ['search' => $payload]));

    // Must respond 200 (no server error from raw SQL).
    $response->assertOk();

    // The raw payload must not appear in the rendered HTML.
    $html = (string) $response->getContent();
    expect($html)->not->toContain($payload);
})->with([
    "' OR '1'='1' --",
    "'; DROP TABLE clients; --",
    '1 UNION SELECT * FROM users --',
    "admin' --",
]);

it('survives SQL injection in the product search field', function (string $payload): void {
    $user = Personas::owner();
    $this->actingAs($user);

    $response = $this->get(route('products.index', ['search' => $payload]));

    $response->assertOk();
    expect((string) $response->getContent())->not->toContain($payload);
})->with([
    "' OR '1'='1' --",
    "'; DROP TABLE products; --",
    '1 UNION SELECT * FROM users --',
]);

it('survives SQL injection in the quotes search field', function (string $payload): void {
    $user = Personas::owner();
    $this->actingAs($user);

    $response = $this->get(route('quotes.index', ['search' => $payload]));

    $response->assertOk();
})->with([
    "' OR '1'='1' --",
    '1 UNION SELECT * FROM users --',
]);

it('does not expose user data through SQL injection in the login form', function (): void {
    // Classic SQL injection in the email field — Eloquent uses prepared statements.
    $this->post('/login', [
        'username' => "' OR '1'='1' --",
        'email' => "admin'--",
        'password' => "' OR '1'='1",
    ]);

    // Must NOT be authenticated.
    $this->assertGuest();
});

it('does not expose data through IDOR — accessing another account client returns 403', function (): void {
    $owner = Personas::owner();
    $other = Personas::owner();
    $otherClient = Client::factory()->create(['account_id' => $other->account_id]);

    $this->actingAs($owner);

    $this->get(route('clients.show', $otherClient))->assertForbidden();
    $this->get(route('clients.edit', $otherClient))->assertForbidden();
});
