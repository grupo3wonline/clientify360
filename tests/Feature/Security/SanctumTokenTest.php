<?php

declare(strict_types=1);

use App\Support\ApiAbilities;
use Illuminate\Session\Middleware\StartSession;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\Support\AccountRecords;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

/*
 * Sanctum tokens: one ability per route group, travel-based expiration,
 * revocation and the creation rules. Plan gate, 5-token cap and removed
 * members are covered in ApiAbilitiesTest and ApiTokenManagementTest.
 */

const TOKEN_MESSAGE = 'O token não possui permissão para esta ação.';

dataset('api abilities', [
    // [route, method, parameter or null, required ability]
    ['api.v1.clients.index', 'get', null, ApiAbilities::CLIENTS_READ],
    ['api.v1.clients.show', 'get', 'client', ApiAbilities::CLIENTS_READ],
    ['api.v1.clients.store', 'post', null, ApiAbilities::CLIENTS_WRITE],
    ['api.v1.clients.update', 'put', 'client', ApiAbilities::CLIENTS_WRITE],
    ['api.v1.clients.destroy', 'delete', 'client', ApiAbilities::CLIENTS_WRITE],
    ['api.v1.products.index', 'get', null, ApiAbilities::PRODUCTS_READ],
    ['api.v1.products.show', 'get', 'product', ApiAbilities::PRODUCTS_READ],
    ['api.v1.quotes.index', 'get', null, ApiAbilities::QUOTES_READ],
    ['api.v1.quotes.show', 'get', 'quote', ApiAbilities::QUOTES_READ],
]);

it('requires the matching ability on every API route', function (string $route, string $method, ?string $parameter, string $ability): void {
    $owner = Personas::owner('professional');
    $url = route($route, $parameter === null ? [] : [$parameter => AccountRecords::make($parameter, $owner)->getKey()]);
    $payload = ['name' => 'Cliente API', 'person_type' => 'individual'];

    $others = array_values(array_diff(ApiAbilities::ALL, [$ability]));
    $this->withToken(ApiTokens::for($owner, $others))->{$method.'Json'}($url, $payload)
        ->assertForbidden()
        ->assertExactJson(['message' => TOKEN_MESSAGE]);

    $this->app['auth']->forgetGuards();
    $status = $this->withToken(ApiTokens::for($owner, [$ability]))->{$method.'Json'}($url, $payload)->getStatusCode();
    expect($status)->toBeIn([200, 201, 204]);
})->with('api abilities');

it('stops accepting a token once its expiration passes', function (): void {
    $owner = Personas::owner('professional');
    $token = ApiTokens::for($owner, ApiAbilities::ALL, now()->addDays(7));

    $this->withToken($token)->getJson(route('api.v1.me'))->assertOk();

    $this->travel(8)->days();
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson(route('api.v1.me'))->assertUnauthorized();
});

it('rejects a revoked token and a malformed one', function (): void {
    $owner = Personas::owner('professional');
    $token = ApiTokens::for($owner);
    $id = $owner->tokens()->value('id');
    $this->actingAs($owner);

    $this->delete(route('profile.tokens.destroy', $id))->assertRedirect();
    auth()->logout();
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson(route('api.v1.me'))->assertUnauthorized();
    $this->withToken('1|nao-e-um-token')->getJson(route('api.v1.me'))->assertUnauthorized();
    $this->withToken(explode('|', $token)[1])->getJson(route('api.v1.me'))->assertUnauthorized();
});

it('keeps the API token-only (no session middleware in the api group)', function (): void {
    $api = app('router')->getMiddlewareGroups()['api'] ?? [];

    expect($api)->not->toContain(StartSession::class)
        ->not->toContain(EnsureFrontendRequestsAreStateful::class);
});

it('never grants the wildcard or unknown abilities on creation', function (array $abilities): void {
    $owner = Personas::owner('professional');

    $this->actingAs($owner)
        ->post(route('profile.tokens.store'), ['name' => 'X', 'abilities' => $abilities, 'expires_in_days' => '30'])
        ->assertSessionHasErrorsIn('apiToken');

    expect(PersonalAccessToken::count())->toBe(0);
})->with([
    'wildcard' => [['*']],
    'unknown' => [['users:delete']],
    'mixed' => [[ApiAbilities::CLIENTS_READ, 'admin']],
]);

it('stores only the token hash', function (): void {
    $owner = Personas::owner('professional');
    $plain = explode('|', ApiTokens::for($owner))[1];

    expect(PersonalAccessToken::query()->value('token'))->toBe(hash('sha256', $plain))
        ->not->toBe($plain);
});
