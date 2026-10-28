<?php

declare(strict_types=1);

use App\Support\ApiAbilities;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

it('creates a token with the chosen abilities and shows the plaintext once', function (): void {
    $owner = Personas::owner('professional');

    $this->actingAs($owner)->post(route('profile.tokens.store'), [
        'name' => 'Integração ERP',
        'abilities' => [ApiAbilities::CLIENTS_READ, ApiAbilities::PRODUCTS_READ],
        'expires_in_days' => '30',
    ])->assertRedirect(route('profile.edit').'#tokens-api')->assertSessionHas('plain_token');

    $token = $owner->tokens()->firstOrFail();
    expect($token->name)->toBe('Integração ERP')
        ->and($token->abilities)->toBe([ApiAbilities::CLIENTS_READ, ApiAbilities::PRODUCTS_READ])
        ->and($token->expires_at->isSameDay(now()->addDays(30)))->toBeTrue();

    $plain = session('plain_token');
    $this->get(route('profile.edit'))->assertOk()->assertSee($plain)->assertSee('Copie agora: este token não será exibido novamente.');
    $this->get(route('profile.edit'))->assertOk()->assertDontSee($plain);
});

it('validates the token form in pt-BR', function (): void {
    $owner = Personas::owner('professional');

    $this->actingAs($owner)->post(route('profile.tokens.store'), ['abilities' => ['admin:*'], 'expires_in_days' => '7'])
        ->assertSessionHasErrorsIn('apiToken', ['name', 'abilities.0', 'expires_in_days']);

    expect($owner->tokens()->count())->toBe(0);
});

it('refuses the sixth active token', function (): void {
    $owner = Personas::owner('professional');
    foreach (range(1, 5) as $_) {
        ApiTokens::for($owner);
    }

    $this->actingAs($owner)->post(route('profile.tokens.store'), [
        'name' => 'Sexto', 'abilities' => [ApiAbilities::CLIENTS_READ], 'expires_in_days' => '30',
    ])->assertSessionHasErrorsIn('apiToken', ['name' => 'Limite de 5 tokens ativos atingido. Revogue um token antes de criar outro.']);

    expect($owner->tokens()->count())->toBe(5);
});

it('revokes a token', function (): void {
    $owner = Personas::owner('professional');
    ApiTokens::for($owner);
    $id = $owner->tokens()->value('id');

    $this->actingAs($owner)->delete(route('profile.tokens.destroy', $id))
        ->assertRedirect()->assertSessionHas('success', 'Token revogado.');

    expect($owner->tokens()->count())->toBe(0);
});

it('lets a Básico user revoke but not create tokens', function (): void {
    $owner = Personas::owner('basic');
    ApiTokens::for($owner);
    $id = $owner->tokens()->value('id');

    $this->actingAs($owner)->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Tokens inativos no plano Básico. Você ainda pode revogá-los; para usá-los novamente, assine o Profissional.');

    $this->post(route('profile.tokens.store'), [
        'name' => 'Novo', 'abilities' => [ApiAbilities::CLIENTS_READ], 'expires_in_days' => '30',
    ])->assertForbidden();

    $this->delete(route('profile.tokens.destroy', $id))->assertRedirect()->assertSessionHas('success', 'Token revogado.');
    expect($owner->tokens()->count())->toBe(0);
});

it('returns 404 when revoking another user token', function (): void {
    $other = Personas::owner('professional');
    ApiTokens::for($other);
    $foreignId = $other->tokens()->value('id');
    $owner = Personas::owner('professional');

    $this->actingAs($owner)->delete(route('profile.tokens.destroy', $foreignId))->assertNotFound();

    expect($other->tokens()->count())->toBe(1);
});
