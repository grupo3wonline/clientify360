<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\Support\Personas;

it('forces the actor account on create even when another account_id is given', function (): void {
    $other = Personas::owner();
    $actor = Personas::owner();

    $this->actingAs($actor);
    $client = Client::create(['name' => 'Forçado', 'account_id' => $other->account_id]);

    expect($client->account_id)->toBe($actor->account_id)
        ->and($client->user_id)->toBe($actor->id);
});

it('keeps an explicit account_id for an authenticated user without an account', function (): void {
    $target = Personas::owner();
    $orphan = User::factory()->withoutAccount()->create();

    $this->actingAs($orphan);
    $client = Client::create(['name' => 'Explícito', 'account_id' => $target->account_id]);

    expect($client->account_id)->toBe($target->account_id);
});

it('keeps an explicit account_id for guests (public capture, jobs, console)', function (): void {
    $target = Personas::owner();
    Auth::forgetGuards();

    $client = Client::create(['name' => 'Convidado', 'account_id' => $target->account_id]);

    expect($client->account_id)->toBe($target->account_id);
});

it('shows nothing to an authenticated user without an account (fail closed)', function (): void {
    $owner = Personas::owner();
    Client::factory()->count(2)->create(['account_id' => $owner->account_id]);
    $orphan = User::factory()->withoutAccount()->create();

    expect(Client::count())->toBe(2);

    $this->actingAs($orphan);

    expect(Client::count())->toBe(0);
});
