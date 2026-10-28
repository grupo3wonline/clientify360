<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

it('limits public routes to 60 requests per minute per IP', function (): void {
    for ($i = 1; $i <= 60; $i++) {
        $this->get('/robots.txt')->assertOk();
    }

    $this->get('/robots.txt')->assertStatus(429);
});

it('limits authenticated routes to 120 requests per minute per user', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    for ($i = 1; $i <= 120; $i++) {
        $this->get('/dashboard')->assertRedirect('/app');
    }

    $this->get('/dashboard')->assertStatus(429);
});

it('answers API rate limit overflows with a pt-BR JSON message', function (): void {
    // The v1 group has no routes yet (Phase 3), so the limiter is exercised directly.
    $request = Request::create('/api/v1/clients');
    $limit = RateLimiter::limiter('api')($request);

    $response = ($limit->responseCallback)($request, ['Retry-After' => 60]);

    expect($limit->maxAttempts)->toBe(60)
        ->and($response->getStatusCode())->toBe(429)
        ->and($response->getData(true))->toBe(['message' => 'Muitas requisições. Tente novamente em instantes.']);
});
