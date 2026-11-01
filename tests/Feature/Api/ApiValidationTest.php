<?php

declare(strict_types=1);

use App\Models\Tag;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

beforeEach(function (): void {
    $this->owner = Personas::owner('professional');
    $this->token = ApiTokens::for($this->owner);
});

it('returns 422 with pt-BR messages for an invalid client', function (): void {
    $this->withToken($this->token)->postJson('/api/v1/clients', ['email' => 'nao-e-email', 'state' => 'PRR'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.name.0', 'O nome é obrigatório.')
        ->assertJsonPath('errors.email.0', 'Informe um e-mail válido.')
        ->assertJsonPath('errors.state.0', 'O estado deve ter no máximo 2 caracteres.');
});

it('rejects a tag of another account', function (): void {
    $other = Personas::owner();
    $foreignTag = Tag::create(['account_id' => $other->account_id, 'name' => 'Alheia', 'color' => '#000000']);

    $this->withToken($this->token)->postJson('/api/v1/clients', ['name' => 'X', 'tag_ids' => [$foreignTag->id]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('tag_ids.0');
});

it('validates listing parameters', function (string $query, string $field): void {
    $this->withToken($this->token)->getJson('/api/v1/clients?'.$query)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);
})->with([
    'per_page above 100' => ['per_page=101', 'per_page'],
    'per_page zero' => ['per_page=0', 'per_page'],
    'page not integer' => ['page=abc', 'page'],
    'search too long' => ['search='.str_repeat('a', 101), 'search'],
]);
