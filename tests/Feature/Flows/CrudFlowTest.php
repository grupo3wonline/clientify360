<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

/* CRUD flows of the main modules through HTTP only (web and API v1). */

beforeEach(function (): void {
    $this->owner = Personas::owner('professional');
    $this->actingAs($this->owner);
});

it('creates, lists, shows, edits, searches, exports and deletes a client on the web', function (): void {
    $this->post(route('clients.store'), ['name' => 'Padaria Sol', 'email' => 'sol@example.com', 'person_type' => 'individual'])
        ->assertRedirect(route('clients.index'))->assertSessionHas('success');
    $client = Client::where('email', 'sol@example.com')->firstOrFail();

    $this->get(route('clients.index'))->assertOk()->assertSee('Padaria Sol');
    $this->get(route('clients.index', ['search' => 'Padaria']))->assertOk()->assertSee('Padaria Sol');
    $this->get(route('clients.index', ['search' => 'Inexistente']))->assertOk()->assertDontSee('Padaria Sol');
    $this->get(route('clients.show', $client))->assertOk()->assertSee('sol@example.com');
    $this->get(route('clients.edit', $client))->assertOk();

    $this->put(route('clients.update', $client), ['name' => 'Padaria Sol Nascente', 'email' => 'sol@example.com'])
        ->assertRedirect(route('clients.index'));
    expect($client->fresh()->name)->toBe('Padaria Sol Nascente');

    $csv = $this->get(route('export.clients'))->assertOk()->streamedContent();
    expect($csv)->toContain('Padaria Sol Nascente');

    $this->delete(route('clients.destroy', $client))->assertRedirect(route('clients.index'));
    expect(Client::find($client->id))->toBeNull()
        ->and(Client::withTrashed()->find($client->id))->not->toBeNull(); // soft delete
});

it('creates, updates, moves stock and deletes a product on the web', function (): void {
    $this->post(route('products.store'), ['type' => 'product', 'name' => 'Café 500g', 'unit' => 'un', 'price' => 19.9, 'track_stock' => 1, 'stock' => 5, 'is_active' => 1])
        ->assertRedirect();
    $product = Product::where('name', 'Café 500g')->firstOrFail();
    expect((float) $product->stock)->toBe(5.0);

    $this->get(route('products.index', ['search' => 'Café']))->assertOk()->assertSee('Café 500g');
    $this->getJson(route('products.search', ['q' => 'Caf']))->assertOk()->assertJsonFragment(['name' => 'Café 500g']);

    $this->put(route('products.update', $product), ['type' => 'product', 'name' => 'Café 1kg', 'unit' => 'un', 'price' => 35])
        ->assertRedirect();
    expect($product->fresh()->name)->toBe('Café 1kg');

    $this->delete(route('products.destroy', $product))->assertRedirect();
    expect(Product::find($product->id))->toBeNull();
});

it('creates a quote, sends, approves, duplicates and downloads it as PDF', function (): void {
    $client = Client::create(['name' => 'Cliente Orçamento', 'person_type' => 'individual']);

    $this->post(route('quotes.store'), [
        'client_id' => $client->id, 'title' => 'Site institucional',
        'items' => [['description' => 'Desenvolvimento', 'quantity' => 1, 'unit_price' => 1500]],
    ])->assertRedirect();
    $quote = Quote::where('title', 'Site institucional')->firstOrFail();
    expect((float) $quote->total)->toBe(1500.0);

    $this->get(route('quotes.show', $quote))->assertOk()->assertSee('Site institucional');
    $this->post(route('quotes.send', $quote))->assertRedirect();
    $this->post(route('quotes.approve', $quote), ['installments' => 2, 'first_due_date' => now()->addWeek()->toDateString()])
        ->assertRedirect();
    expect($quote->fresh()->status)->toBe('approved')
        ->and($quote->fresh()->entries()->count())->toBe(2);

    $this->post(route('quotes.duplicate', $quote))->assertRedirect();
    expect(Quote::count())->toBe(2);

    $pdf = $this->get(route('quotes.pdf', $quote))->assertOk();
    expect($pdf->headers->get('Content-Type'))->toBe('application/pdf');
});

it('runs the client CRUD through API v1', function (): void {
    $token = ApiTokens::for($this->owner);
    auth()->logout();

    $id = $this->withToken($token)->postJson(route('api.v1.clients.store'), ['name' => 'Cliente API', 'email' => 'api@example.com', 'person_type' => 'individual'])
        ->assertCreated()->json('data.id');

    $this->withToken($token)->getJson(route('api.v1.clients.index', ['search' => 'Cliente API']))
        ->assertOk()->assertJsonPath('data.0.id', $id);
    $this->withToken($token)->putJson(route('api.v1.clients.update', $id), ['name' => 'Cliente API 2', 'email' => 'api@example.com'])
        ->assertOk()->assertJsonPath('data.name', 'Cliente API 2');
    $this->withToken($token)->getJson(route('api.v1.clients.show', $id))->assertOk()->assertJsonPath('data.email', 'api@example.com');
    $this->withToken($token)->deleteJson(route('api.v1.clients.destroy', $id))->assertNoContent();
    $this->withToken($token)->getJson(route('api.v1.clients.show', $id))->assertNotFound();
});
