<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

it('rejects an unsigned webhook when the secret is set', function (): void {
    config(['cashier.webhook.secret' => 'whsec_test']);

    $this->postJson('/stripe/webhook', ['type' => 'customer.subscription.updated'])
        ->assertForbidden();
});

it('returns 503 in production when the webhook secret is missing', function (): void {
    $this->app->detectEnvironment(fn () => 'production');
    config(['cashier.webhook.secret' => '']);
    Log::spy();

    $this->postJson('/stripe/webhook', ['type' => 'customer.subscription.updated'])
        ->assertStatus(503)
        ->assertSee('Webhook não configurado.');

    Log::shouldHaveReceived('critical')->once();
});

it('registers a single stripe/webhook route named cashier.webhook', function (): void {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route): bool => $route->uri() === 'stripe/webhook');

    expect($routes)->toHaveCount(1)
        ->and($routes->first()->getName())->toBe('cashier.webhook')
        ->and(Route::has('cashier.payment'))->toBeFalse();
});
