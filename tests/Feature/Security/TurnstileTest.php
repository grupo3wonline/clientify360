<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\TurnstileService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

const CAPTCHA_TURNSTILE_URL = 'challenges.cloudflare.com/turnstile/v0/siteverify';

function captchaEnableTurnstile(): void
{
    config(['services.turnstile.secret_key' => 'test-secret', 'services.turnstile.site_key' => 'test-site']);
}

function captchaEnableRecaptcha(): void
{
    config(['services.recaptcha.secret_key' => 'test-secret', 'services.recaptcha.site_key' => 'test-site', 'services.recaptcha.min_score' => 0.5]);
}

function captchaLoginPayload(User $user, array $extra = []): array
{
    return ['username' => $user->username, 'email' => $user->email, 'password' => 'password', ...$extra];
}

// ---------------------------------------------------------------- service

it('is disabled and fail-open without a secret key', function (): void {
    config(['services.turnstile.secret_key' => '']);
    Http::fake();

    expect(app(TurnstileService::class)->isEnabled())->toBeFalse()
        ->and(app(TurnstileService::class)->verify(null))->toBeTrue();
    Http::assertNothingSent();
});

it('accepts a token Cloudflare confirms', function (): void {
    captchaEnableTurnstile();
    Http::fake([CAPTCHA_TURNSTILE_URL => Http::response(['success' => true, 'action' => 'login'])]);

    expect(app(TurnstileService::class)->verify('good-token', 'login', '203.0.113.9'))->toBeTrue();

    Http::assertSent(fn ($request): bool => $request['secret'] === 'test-secret'
        && $request['response'] === 'good-token'
        && $request['remoteip'] === '203.0.113.9');
});

it('rejects every failure mode when enabled (fail-closed)', function (string $case): void {
    captchaEnableTurnstile();

    match ($case) {
        'rejected' => Http::fake([CAPTCHA_TURNSTILE_URL => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']])]),
        'replayed' => Http::fake([CAPTCHA_TURNSTILE_URL => Http::response(['success' => false, 'error-codes' => ['timeout-or-duplicate']])]),
        'http error' => Http::fake([CAPTCHA_TURNSTILE_URL => Http::response('', 500)]),
        'garbage body' => Http::fake([CAPTCHA_TURNSTILE_URL => Http::response('not json', 200)]),
        'network down' => Http::fake([CAPTCHA_TURNSTILE_URL => fn () => throw new RuntimeException('timeout')]),
        'wrong action' => Http::fake([CAPTCHA_TURNSTILE_URL => Http::response(['success' => true, 'action' => 'register'])]),
        default => Http::fake(),
    };

    $token = $case === 'oversized' ? str_repeat('a', 2049) : 'some-token';

    expect(app(TurnstileService::class)->verify($token, 'login'))->toBeFalse();
})->with(['rejected', 'replayed', 'http error', 'garbage body', 'network down', 'wrong action', 'oversized']);

it('rejects a missing or empty token when enabled without calling Cloudflare', function (?string $token): void {
    captchaEnableTurnstile();
    Http::fake();

    expect(app(TurnstileService::class)->verify($token, 'login'))->toBeFalse();
    Http::assertNothingSent();
})->with([null, '']);

// ------------------------------------------------- forms: every public form

it('blocks login when Turnstile fails', function (): void {
    captchaEnableTurnstile();
    Http::fake([CAPTCHA_TURNSTILE_URL => Http::response(['success' => false])]);
    $user = User::factory()->create();

    $this->post('/login', captchaLoginPayload($user, ['cf-turnstile-response' => 'bad']))
        ->assertSessionHasErrors('cf-turnstile-response');

    $this->assertGuest();
});

it('lets login through with a valid Turnstile token', function (): void {
    captchaEnableTurnstile();
    Http::fake([CAPTCHA_TURNSTILE_URL => Http::response(['success' => true, 'action' => 'login'])]);
    $user = User::factory()->create();

    $this->post('/login', captchaLoginPayload($user, ['cf-turnstile-response' => 'good']))->assertSessionHasNoErrors();

    $this->assertAuthenticated();
});

it('blocks login when the captcha FIELD IS MISSING (bot that omits the token)', function (string $which): void {
    // Regression: a non-implicit rule is skipped for an absent field, which let a bot bypass the
    // captcha by simply not sending it. Both captchas must be enforced even then.
    $which === 'turnstile' ? captchaEnableTurnstile() : captchaEnableRecaptcha();
    Http::fake(['*' => Http::response(['success' => false])]);
    $user = User::factory()->create();

    $this->post('/login', captchaLoginPayload($user))->assertSessionHasErrors($which === 'turnstile' ? 'cf-turnstile-response' : 'g-recaptcha-response');

    $this->assertGuest();
})->with(['turnstile', 'recaptcha']);

it('requires BOTH captchas when both are configured', function (): void {
    captchaEnableTurnstile();
    captchaEnableRecaptcha();
    Http::fake([
        CAPTCHA_TURNSTILE_URL => Http::response(['success' => true, 'action' => 'login']),
        'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
    ]);
    $user = User::factory()->create();

    $this->post('/login', captchaLoginPayload($user, ['cf-turnstile-response' => 'good', 'g-recaptcha-response' => 'bad']))
        ->assertSessionHasErrors('g-recaptcha-response');

    $this->assertGuest();
});

it('blocks register, forgot-password, reset-password and the public lead form without a token', function (): void {
    captchaEnableTurnstile();
    Http::fake(['*' => Http::response(['success' => false])]);

    $this->post('/register', [
        'name' => 'Teste', 'username' => 'teste_captcha', 'email' => 'captcha@example.com', 'lgpd_consent' => '1',
        'password' => 'Senha12345', 'password_confirmation' => 'Senha12345',
    ])->assertSessionHasErrors('cf-turnstile-response');
    $this->assertDatabaseMissing('users', ['email' => 'captcha@example.com']);

    $this->post('/forgot-password', ['email' => 'alguem@example.com'])->assertSessionHasErrors('cf-turnstile-response');

    $this->post('/reset-password', [
        'token' => 'x', 'email' => 'alguem@example.com', 'password' => 'Senha12345', 'password_confirmation' => 'Senha12345',
    ])->assertSessionHasErrors('cf-turnstile-response');
});

it('does not send a reset e-mail when the captcha fails', function (): void {
    captchaEnableTurnstile();
    Http::fake(['*' => Http::response(['success' => false])]);
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email, 'cf-turnstile-response' => 'bad']);

    Notification::assertNothingSent();
});

// -------------------------------------------------------------- rendering

it('renders the widget only when the site key is set', function (): void {
    config(['services.turnstile.site_key' => '']);
    $this->get('/login')->assertDontSee('challenges.cloudflare.com', false);

    captchaEnableTurnstile();
    $this->get('/login')
        ->assertSee('https://challenges.cloudflare.com/turnstile/v0/api.js', false)
        ->assertSee('test-site', false);
});

it('renders the widget on every public form', function (string $url): void {
    captchaEnableTurnstile();

    $this->get($url)->assertOk()->assertSee('challenges.cloudflare.com/turnstile', false);
})->with(['/login', '/register', '/forgot-password', '/reset-password/token-qualquer']);

it('allows Cloudflare in the CSP only while the widget is configured', function (): void {
    config(['services.turnstile.site_key' => '']);
    expect((string) $this->get('/login')->headers->get('Content-Security-Policy'))->not->toContain('challenges.cloudflare.com');

    captchaEnableTurnstile();
    $csp = (string) $this->get('/login')->headers->get('Content-Security-Policy');

    expect($csp)->toContain("script-src 'self'")->toContain('challenges.cloudflare.com')
        ->and(preg_match("/frame-src[^;]*challenges\.cloudflare\.com/", $csp))->toBe(1)
        ->and(preg_match("/script-src[^;]*challenges\.cloudflare\.com/", $csp))->toBe(1);
});
