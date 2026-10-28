<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Product;
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
