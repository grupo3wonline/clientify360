<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;
use Tests\Support\Personas;

it('ignores an attacker-supplied account_id when creating a client over HTTP', function (): void {
    $victim = Personas::owner();
    $attacker = Personas::owner();

    $this->actingAs($attacker)->post(route('clients.store'), [
        'name' => 'Injetado',
        'email' => 'injetado@ex.com',
        'account_id' => $victim->account_id,
        'user_id' => $victim->id,
    ])->assertRedirect(route('clients.index'));

    $client = Client::withoutGlobalScope('owned')->where('email', 'injetado@ex.com')->firstOrFail();

    expect($client->account_id)->toBe($attacker->account_id)
        ->and($client->user_id)->toBe($attacker->id);
});

it('ignores an attacker-supplied account_id when creating a product over HTTP', function (): void {
    $victim = Personas::owner();
    $attacker = Personas::owner();

    $this->actingAs($attacker)->post(route('products.store'), [
        'type' => 'product',
        'name' => 'Produto injetado',
        'unit' => 'un',
        'price' => 10,
        'account_id' => $victim->account_id,
    ])->assertRedirect();

    $product = Product::withoutGlobalScope('owned')->where('name', 'Produto injetado')->firstOrFail();

    expect($product->account_id)->toBe($attacker->account_id);
});

it('does not move a client to another account on update', function (): void {
    $victim = Personas::owner();
    $owner = Personas::owner();
    $client = Client::factory()->create(['account_id' => $owner->account_id]);

    $this->actingAs($owner)->put(route('clients.update', $client), [
        'name' => 'Atualizado',
        'account_id' => $victim->account_id,
    ])->assertRedirect(route('clients.index'));

    expect($client->fresh()->account_id)->toBe($owner->account_id);
});

it('does not let registration join an existing account or pre-verify the e-mail', function (): void {
    $victim = Personas::owner();

    $this->post('/register', [
        'name' => 'Invasor', 'username' => 'invasor', 'email' => 'invasor@example.com',
        'password' => 'password', 'password_confirmation' => 'password', 'lgpd_consent' => '1',
        'account_id' => $victim->account_id, 'email_verified_at' => now()->toDateTimeString(),
    ])->assertRedirect(route('verification.notice'));

    $user = User::where('email', 'invasor@example.com')->firstOrFail();

    expect($user->account_id)->not->toBe($victim->account_id)
        ->and($user->email_verified_at)->toBeNull()
        ->and($user->account->owner_id)->toBe($user->id);
});

it('does not move a user to another account through the profile form', function (): void {
    $victim = Personas::owner();
    $user = Personas::owner();

    $this->actingAs($user)->patch(route('profile.update'), [
        'name' => 'Nome', 'email' => $user->email, 'account_id' => $victim->account_id, 'username' => 'outro',
    ])->assertRedirect();

    expect($user->fresh()->account_id)->toBe($user->account_id)
        ->and($user->fresh()->username)->toBe($user->username);
});

it('refuses to promote a member to owner', function (): void {
    $owner = Personas::owner();
    $member = Personas::collaborator($owner->account);

    $this->actingAs($owner)->patch(route('team.updateRole', $member), ['role' => User::ROLE_OWNER])
        ->assertSessionHasErrors('role');

    app(PermissionRegistrar::class)->setPermissionsTeamId($owner->account_id);
    expect($member->fresh()->hasRole(User::ROLE_OWNER))->toBeFalse();
});
