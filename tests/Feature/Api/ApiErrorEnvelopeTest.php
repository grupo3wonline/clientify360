<?php

declare(strict_types=1);

use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

beforeEach(function (): void {
    $this->owner = Personas::owner('professional');
    $this->token = ApiTokens::for($this->owner);
});

it('answers an unsupported method with 405 JSON and an Allow header', function (): void {
    $product = Product::factory()->forAccount($this->owner->account_id)->create();

    $response = $this->withToken($this->token)->deleteJson("/api/v1/products/{$product->id}")
        ->assertStatus(405)
        ->assertHeader('Allow');

    expect($response->json())->toHaveKey('message')->not->toHaveKey('trace');
});

it('keeps the errors bag on 422', function (): void {
    $this->withToken($this->token)->postJson('/api/v1/clients', [])
        ->assertUnprocessable()
        ->assertJsonStructure(['message', 'errors' => ['name']]);
});

it('answers unknown API routes with the pt-BR 404 envelope', function (): void {
    $this->withToken($this->token)->getJson('/api/v1/nao-existe')
        ->assertNotFound()
        ->assertExactJson(['message' => 'Registro não encontrado.']);
});

it('hides internal errors behind a generic 500 even with debug on', function (): void {
    config(['app.debug' => true]);
    Route::middleware('api')->get('/api/v1/__boom', fn () => throw new RuntimeException('segredo interno'));

    $response = $this->getJson('/api/v1/__boom')->assertStatus(500);

    expect($response->json())->toBe(['message' => 'Erro interno. Tente novamente mais tarde.'])
        ->and($response->getContent())->not->toContain('segredo interno');
});
