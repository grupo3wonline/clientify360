<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Tag;
use App\Services\SubscriptionService;
use App\Support\AccountCache;
use App\Support\ApiAbilities;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

beforeEach(function (): void {
    $this->other = Personas::owner('professional');
    $this->foreign = Client::factory()->create(['account_id' => $this->other->account_id, 'name' => 'Ana Alheia']);

    $this->owner = Personas::owner('professional');
    $this->token = ApiTokens::for($this->owner);
});

it('lists only own-account clients with the paginated shape and no account_id', function (): void {
    Client::factory()->count(3)->create(['account_id' => $this->owner->account_id]);

    $response = $this->withToken($this->token)->getJson('/api/v1/clients?per_page=2')
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'email', 'phone', 'document', 'person_type', 'tags', 'created_at']], 'links', 'meta' => ['total', 'per_page']])
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.per_page', 2);

    expect($response->json('data.0'))->not->toHaveKeys(['account_id', 'user_id', 'score', 'strengths'])
        ->and(collect($response->json('data'))->pluck('name'))->not->toContain('Ana Alheia');
});

it('creates, shows, updates and deletes a client', function (): void {
    $tag = Tag::create(['account_id' => $this->owner->account_id, 'name' => 'VIP', 'color' => '#ff0000']);

    $id = $this->withToken($this->token)->postJson('/api/v1/clients', [
        'name' => 'Cliente API',
        'email' => 'api@ex.com',
        'tag_ids' => [$tag->id],
    ])->assertCreated()
        ->assertJsonPath('data.name', 'Cliente API')
        ->assertJsonPath('data.tags.0.name', 'VIP')
        ->json('data.id');

    $client = Client::withoutGlobalScope('owned')->findOrFail($id);
    expect($client->account_id)->toBe($this->owner->account_id);

    $this->withToken($this->token)->getJson("/api/v1/clients/{$id}")->assertOk()->assertJsonPath('data.email', 'api@ex.com');

    $this->withToken($this->token)->putJson("/api/v1/clients/{$id}", ['name' => 'Cliente API Editado'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Cliente API Editado');

    $this->withToken($this->token)->deleteJson("/api/v1/clients/{$id}")->assertNoContent();

    expect(Client::withoutGlobalScope('owned')->withTrashed()->find($id)?->trashed())->toBeTrue();
});

it('returns 404 for another account client on every verb', function (): void {
    $id = $this->foreign->id;

    $this->withToken($this->token)->getJson("/api/v1/clients/{$id}")->assertNotFound()->assertExactJson(['message' => 'Registro não encontrado.']);
    $this->withToken($this->token)->putJson("/api/v1/clients/{$id}", ['name' => 'Hack'])->assertNotFound();
    $this->withToken($this->token)->deleteJson("/api/v1/clients/{$id}")->assertNotFound();

    expect($this->foreign->fresh()->name)->toBe('Ana Alheia');
});

it('searches only inside the token account', function (): void {
    Client::factory()->create(['account_id' => $this->owner->account_id, 'name' => 'Ana Própria']);

    $names = $this->withToken($this->token)->getJson('/api/v1/clients?search=Ana')
        ->assertOk()
        ->json('data.*.name');

    expect($names)->toBe(['Ana Própria']);
});

it('refuses to create beyond the plan limit (defensive branch)', function (): void {
    $plans = Mockery::mock(SubscriptionService::class, [new AccountCache])->makePartial();
    $plans->shouldReceive('canCreate')->with(Mockery::any(), 'clients')->andReturnFalse();
    $plans->shouldReceive('limitMessage')->andReturn('Limite de clientes atingido.');
    $this->app->instance(SubscriptionService::class, $plans);

    $this->withToken($this->token)->postJson('/api/v1/clients', ['name' => 'Excedente'])
        ->assertForbidden()
        ->assertExactJson(['message' => 'Limite de clientes atingido.']);

    expect(Client::where('name', 'Excedente')->exists())->toBeFalse();
});

it('lists products and quotes of the account only', function (): void {
    Product::factory()->forAccount($this->other->account_id)->create(['name' => 'Produto Alheio']);
    Product::factory()->forAccount($this->owner->account_id)->create(['name' => 'Produto Próprio']);
    $quote = Quote::factory()->forClient(Client::factory()->create(['account_id' => $this->owner->account_id]))->create();
    $foreignQuote = Quote::factory()->forClient($this->foreign)->create();

    expect($this->withToken($this->token)->getJson('/api/v1/products')->assertOk()->json('data.*.name'))->toBe(['Produto Próprio']);
    $this->withToken($this->token)->getJson("/api/v1/quotes/{$quote->id}")->assertOk()->assertJsonPath('data.number', $quote->number);
    $this->withToken($this->token)->getJson("/api/v1/quotes/{$foreignQuote->id}")->assertNotFound();
    expect($this->withToken($this->token)->getJson('/api/v1/quotes')->assertOk()->json('data.*.id'))->toBe([$quote->id]);
});

it('describes the token owner on /me', function (): void {
    $this->withToken($this->token)->getJson('/api/v1/me')
        ->assertOk()
        ->assertJsonPath('data.id', $this->owner->id)
        ->assertJsonPath('data.account.id', $this->owner->account_id)
        ->assertJsonPath('data.plan', 'professional')
        ->assertJsonPath('data.abilities', ApiAbilities::ALL);
});
