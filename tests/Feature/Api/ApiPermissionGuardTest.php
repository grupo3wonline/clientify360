<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

it('resolves Spatie web-guard permissions for a user authenticated by Sanctum', function (): void {
    $owner = Personas::owner('professional');

    $this->withToken(ApiTokens::for($owner))->getJson('/api/v1/me')->assertOk();

    expect(Auth::getDefaultDriver())->toBe('sanctum')
        ->and(auth()->user()?->is($owner))->toBeTrue()
        ->and(auth()->user()->can('clients.view'))->toBeTrue()
        ->and(auth()->user()->can('account.billing'))->toBeTrue();
});

it('applies the route permission middleware under the sanctum guard', function (): void {
    $owner = Personas::owner('professional');
    $reader = ApiTokens::memberWithPermissions($owner, ['clients.view']);

    $this->withToken(ApiTokens::for($reader))->getJson('/api/v1/clients')->assertOk();
    $this->withToken(ApiTokens::for($reader))->getJson('/api/v1/products')->assertForbidden();
});
