<?php

declare(strict_types=1);

use App\Models\Client;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

/*
 * SQL-injection-style and malformed query input on every listing filter:
 * no 500, no other-account rows, and the tables stay intact. Queries are
 * built with bindings (no raw SQL from input); this test pins it.
 */

const SQLI_PAYLOADS = [
    "' OR '1'='1",
    '1; DROP TABLE clients; --',
    '" UNION SELECT id, name FROM users --',
    '%_%',
    "\\' OR 1=1 #",
];

/** @return list<array{0: string, 1: list<string>}> */
function listingFilters(): array
{
    return [
        ['clients.index', ['search', 'tag']],
        ['products.index', ['search', 'filter']],
        ['products.search', ['q']],
        ['quotes.index', ['search', 'status']],
        ['service-orders.index', ['search', 'status']],
        ['purchases.index', ['search']],
        ['deals.index', ['search']],
        ['deals.list', ['status']],
        ['interactions.index', ['status']],
        ['activities.index', ['type', 'client_id']],
        ['appointments.index', ['month', 'year']],
        ['finance.entries', ['direction', 'status', 'month']],
        ['reports.index', ['period']],
        ['reminders.index', ['filter']],
        ['billing.index', ['checkout']],
    ];
}

beforeEach(function (): void {
    $this->victim = Personas::owner('professional');
    Client::factory()->create(['account_id' => $this->victim->account_id, 'user_id' => $this->victim->id, 'name' => 'Segredo da Outra Conta']);
    $this->owner = Personas::owner('professional');
    Client::factory()->create(['account_id' => $this->owner->account_id, 'user_id' => $this->owner->id, 'name' => 'Cliente Próprio']);
});

it('handles SQL-like strings on every listing filter', function (): void {
    $this->actingAs($this->owner);

    foreach (listingFilters() as [$route, $params]) {
        foreach ($params as $param) {
            foreach (SQLI_PAYLOADS as $payload) {
                $response = $this->get(route($route, [$param => $payload]));
                expect($response->getStatusCode())->toBeLessThan(500, "{$route}?{$param}={$payload}");
                $this->assertStringNotContainsString('Segredo da Outra Conta', (string) $response->getContent(), "{$route}?{$param}");
            }
        }
    }

    auth()->logout();
    expect(Client::count())->toBe(2);
});

it('does not crash on array input where a string is expected', function (): void {
    $this->actingAs($this->owner);
    $crashes = [];

    foreach (listingFilters() as [$route, $params]) {
        foreach ($params as $param) {
            if ($this->get(route($route).'?'.$param.'[]=1&'.$param.'[x]=2')->getStatusCode() >= 500) {
                $crashes[] = "{$route}?{$param}[]";
            }
        }
    }

    expect($crashes)->toBe([]);
});

it('treats SQL-like API search as a plain term', function (): void {
    $token = ApiTokens::for($this->owner);

    foreach (SQLI_PAYLOADS as $payload) {
        $this->withToken($token)->getJson(route('api.v1.clients.index', ['search' => $payload]))
            ->assertOk()
            ->assertDontSee('Segredo da Outra Conta');
    }

    $this->withToken($token)->getJson(route('api.v1.clients.index').'?search[]=x')->assertStatus(422);
});

it('keeps login immune to SQL-like credentials', function (): void {
    $this->post('/login', ['username' => "admin' --", 'email' => "x' OR '1'='1@example.com", 'password' => "' OR '1'='1"])
        ->assertSessionHasErrors();

    $this->assertGuest();
});
