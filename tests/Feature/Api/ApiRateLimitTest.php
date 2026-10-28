<?php

declare(strict_types=1);

use Tests\Support\ApiTokens;
use Tests\Support\Personas;

it('throttles the API per user with a pt-BR 429 and Retry-After', function (): void {
    config(['services.rate_limits.api' => 3]);
    $owner = Personas::owner('professional');
    $token = ApiTokens::for($owner);

    foreach (range(1, 3) as $_) {
        $this->withToken($token)->getJson('/api/v1/me')->assertOk();
    }

    $this->withToken($token)->getJson('/api/v1/me')
        ->assertStatus(429)
        ->assertHeader('Retry-After')
        ->assertExactJson(['message' => 'Muitas requisições. Tente novamente em instantes.']);
});
