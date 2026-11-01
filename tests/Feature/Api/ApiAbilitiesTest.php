<?php

declare(strict_types=1);

use App\Services\AccountService;
use App\Services\SubscriptionService;
use App\Support\ApiAbilities;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

it('lets a read-only token read and refuses writes with the token message', function (): void {
    $owner = Personas::owner('professional');
    $token = ApiTokens::for($owner, [ApiAbilities::CLIENTS_READ]);

    $this->withToken($token)->getJson('/api/v1/clients')->assertOk();
    $this->withToken($token)->postJson('/api/v1/clients', ['name' => 'X'])
        ->assertForbidden()
        ->assertExactJson(['message' => 'O token não possui permissão para esta ação.']);
    $this->withToken($token)->getJson('/api/v1/products')
        ->assertForbidden()
        ->assertExactJson(['message' => 'O token não possui permissão para esta ação.']);
});

it('rejects an expired token with 401', function (): void {
    $owner = Personas::owner('professional');
    $token = ApiTokens::for($owner, ApiAbilities::ALL, now()->addDay());

    $this->travel(2)->days();

    $this->withToken($token)->getJson('/api/v1/clients')
        ->assertUnauthorized()
        ->assertExactJson(['message' => 'Não autenticado.']);
});

it('rejects requests without a token with 401', function (): void {
    $this->getJson('/api/v1/clients')->assertUnauthorized()->assertExactJson(['message' => 'Não autenticado.']);
});

it('answers a user without clients.manage in pt-BR, without the package text', function (): void {
    $owner = Personas::owner('professional');
    $reader = ApiTokens::memberWithPermissions($owner, ['clients.view']);

    $response = $this->withToken(ApiTokens::for($reader))->postJson('/api/v1/clients', ['name' => 'X'])->assertForbidden();
    $message = (string) $response->json('message');

    expect($message)->toBe('Você não tem permissão para esta ação.')
        ->and(mb_strtolower($message))->not->toContain('unauthorized')
        ->and($message)->not->toContain('User does not have');
});

it('blocks a Básico account with the API feature message', function (): void {
    $owner = Personas::owner('basic');

    $this->withToken(ApiTokens::for($owner))->getJson('/api/v1/clients')
        ->assertForbidden()
        ->assertExactJson(['message' => app(SubscriptionService::class)->featureMessage('api')]);
});

it('locks out the token of a member removed from the account', function (): void {
    $owner = Personas::owner('professional');
    $member = Personas::collaborator($owner->account);
    $token = ApiTokens::for($member);

    app(AccountService::class)->removeMember($member, $owner->account);

    $this->withToken($token)->getJson('/api/v1/clients')->assertUnauthorized();
});
