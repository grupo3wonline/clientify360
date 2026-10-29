<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Tests\Support\Personas;

/*
 * AC-59: CSRF is enforced on every state-changing web route except the
 * Stripe webhook. Laravel skips the check while running unit tests, so the
 * middleware is rebound to a subclass that does not.
 */

beforeEach(function (): void {
    $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    });
});

it('answers 419 to a POST without a token', function (string $url, bool $authenticated): void {
    if ($authenticated) {
        $this->actingAs(Personas::owner());
    }

    $this->post($url, ['name' => 'Sem token'])->assertStatus(419);
})->with([
    'client store' => ['/clients', true],
    'login' => ['/login', false],
    'register' => ['/register', false],
    'forgot password' => ['/forgot-password', false],
    'cookie consent' => ['/privacidade/consentimento', false],
    'logout' => ['/logout', true],
]);

it('accepts the same POST with a valid token', function (): void {
    $this->actingAs(Personas::owner());
    $this->withSession(['_token' => 'known-token']);

    $this->post('/clients', ['_token' => 'known-token', 'name' => 'Com token', 'person_type' => 'individual'])
        ->assertRedirect(route('clients.index'));
});

it('excludes only the Stripe webhook from CSRF', function (): void {
    $except = (new ReflectionClass(ValidateCsrfToken::class))->getStaticPropertyValue('neverVerify');

    expect(array_values($except))->toBe(['stripe/webhook']);
});

it('keeps every state-changing web route under the web group (CSRF)', function (): void {
    $unprotected = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r): bool => array_diff($r->methods(), ['GET', 'HEAD', 'OPTIONS']) !== [])
        ->reject(fn ($r): bool => str_starts_with($r->uri(), 'api/') || $r->uri() === 'stripe/webhook')
        // Laravel's local-disk upload URL is authorized by its signature, not by a session.
        ->reject(fn ($r): bool => $r->getName() === 'storage.local.upload')
        // The Filament panel lists the CSRF middleware explicitly instead of the web group.
        ->reject(fn ($r): bool => in_array('web', $r->gatherMiddleware(), true)
            || collect($r->gatherMiddleware())->contains(fn ($m): bool => is_string($m) && is_a($m, VerifyCsrfToken::class, true)))
        ->map(fn ($r): string => $r->uri())
        ->values();

    expect($unprotected->all())->toBe([]);
});
