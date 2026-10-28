<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

function passesPasswordDefaults(string $password): bool
{
    return Validator::make(['password' => $password], ['password' => [Password::defaults()]])->passes();
}

it('accepts an 8-character password outside production', function (): void {
    expect(passesPasswordDefaults('abcdefgh'))->toBeTrue()
        ->and(passesPasswordDefaults('abcdefg'))->toBeFalse();
});

it('requires length, mixed case, numbers and an uncompromised password in production', function (): void {
    $this->app->detectEnvironment(fn () => 'production');
    // HIBP range API: no leaked suffixes, so only the local rules decide.
    Http::fake(['api.pwnedpasswords.com/*' => Http::response('', 200)]);

    expect(passesPasswordDefaults('abcdefgh1'))->toBeFalse()
        ->and(passesPasswordDefaults('Abcdefgh1'))->toBeFalse()
        ->and(passesPasswordDefaults('Abcdefghi12'))->toBeTrue();
});
