<?php

declare(strict_types=1);

/**
 * Evaluates config/session.php with the given variables and returns `secure`.
 * env() reads $_SERVER/$_ENV/getenv on every call, so the variables are set
 * there for the evaluation and restored afterwards.
 *
 * @param  array<string, string|null>  $vars
 */
function sessionSecureWith(array $vars): bool
{
    [$server, $env] = [$_SERVER, $_ENV];
    $putenv = array_map(fn (string $key): string|false => getenv($key), array_combine(array_keys($vars), array_keys($vars)));

    foreach ($vars as $key => $value) {
        setEnvVar($key, $value);
    }

    try {
        return (require base_path('config/session.php'))['secure'];
    } finally {
        foreach ($putenv as $key => $value) {
            setEnvVar($key, $value === false ? null : $value);
        }

        [$_SERVER, $_ENV] = [$server, $env];
    }
}

function setEnvVar(string $key, ?string $value): void
{
    unset($_SERVER[$key], $_ENV[$key]);
    putenv($key);

    if ($value === null) {
        return;
    }

    $_SERVER[$key] = $_ENV[$key] = $value;
    putenv("{$key}={$value}");
}

it('marks the session cookie secure in production when the key is absent', function (): void {
    expect(sessionSecureWith(['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => null]))->toBeTrue();
});

it('marks the session cookie secure in production when the key is empty', function (): void {
    expect(sessionSecureWith(['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => '']))->toBeTrue();
});

it('honors an explicit false in production', function (): void {
    expect(sessionSecureWith(['APP_ENV' => 'production', 'SESSION_SECURE_COOKIE' => 'false']))->toBeFalse();
});

it('does not mark the cookie secure in testing when the key is absent', function (): void {
    expect(sessionSecureWith(['APP_ENV' => 'testing', 'SESSION_SECURE_COOKIE' => null]))->toBeFalse();
});

it('keeps the session cookie http-only and same-site lax', function (): void {
    expect(config('session.http_only'))->toBeTrue()
        ->and(config('session.same_site'))->toBe('lax');
});

it('has no empty SESSION_SECURE_COOKIE line and no L5_SWAGGER_CONST_HOST reference', function (): void {
    $example = (string) file_get_contents(base_path('.env.example'));

    expect($example)->not->toMatch('/^SESSION_SECURE_COOKIE=\s*$/m')
        ->and($example)->not->toContain('L5_SWAGGER_CONST_HOST')
        ->and((string) file_get_contents(base_path('config/l5-swagger.php')))->not->toContain('L5_SWAGGER_CONST_HOST');
});
