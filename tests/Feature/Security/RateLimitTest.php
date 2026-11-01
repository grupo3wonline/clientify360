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

/*
 * Phase 7 extension. Already covered elsewhere: login tuple 5 and login IP 20
 * (ThreeFieldLoginTest), API 60 per user (ApiRateLimitTest), consent 20
 * (ConsentTest), XLSX export 3 (ExportFlowTest).
 */
it('limits register and forgot-password POSTs to 10 per minute per IP', function (string $url, array $payload): void {
    for ($i = 1; $i <= 10; $i++) {
        expect($this->post($url, $payload)->getStatusCode())->not->toBe(429);
    }

    $this->post($url, $payload)->assertStatus(429);
})->with([
    'register' => ['/register', ['name' => 'X', 'username' => 'x', 'email' => 'not-an-email', 'password' => 'a']],
    'forgot password' => ['/forgot-password', ['email' => 'nobody@example.com']],
]);

it('limits the public lead form submissions to 6 per minute', function (): void {
    for ($i = 1; $i <= 6; $i++) {
        $this->post('/f/nao-existe', [])->assertNotFound();
    }

    $this->post('/f/nao-existe', [])->assertStatus(429);
});

it('keeps the documented limits on the sensitive routes', function (string $name, string $throttle): void {
    expect(app('router')->getRoutes()->getByName($name)?->gatherMiddleware() ?? [])->toContain($throttle);
})->with([
    ['consent.store', 'throttle:20,1'],
    ['lead-forms.submit', 'throttle:6,1'],
    ['password.email', 'throttle:10,1'],
    ['verification.send', 'throttle:6,1'],
    ['export.clients.xlsx', 'throttle:3,1'],
    ['api.v1.clients.index', 'throttle:api'],
    ['clients.index', 'throttle:app'],
    ['home', 'throttle:public'],
]);

it('answers API rate limit overflows with a pt-BR JSON message', function (): void {
    // The v1 group has no routes yet (Phase 3), so the limiter is exercised directly.
    $request = Request::create('/api/v1/clients');
    $limit = RateLimiter::limiter('api')($request);

    $response = ($limit->responseCallback)($request, ['Retry-After' => 60]);

    expect($limit->maxAttempts)->toBe(60)
        ->and($response->getStatusCode())->toBe(429)
        ->and($response->getData(true))->toBe(['message' => 'Muitas requisições. Tente novamente em instantes.']);
});
