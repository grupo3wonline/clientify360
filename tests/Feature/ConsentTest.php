<?php

declare(strict_types=1);

use App\Models\CookieConsent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Tests\Support\Personas;

/* AC-4 (cookie_consents), AC-51, R-PRIV-1: consent receipts. */

it('stores a consent receipt with a hashed IP and returns 204 with the cookie', function (): void {
    $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.7'])
        ->postJson(route('consent.store'), ['analytics' => true, 'marketing' => false, 'source' => 'banner'])
        ->assertNoContent();

    $row = CookieConsent::sole();
    expect(Str::isUuid($row->id))->toBeTrue()
        ->and(Str::isUuid($row->consent_id))->toBeTrue()
        ->and($row->analytics)->toBeTrue()
        ->and($row->marketing)->toBeFalse()
        ->and($row->source)->toBe('banner')
        ->and($row->policy_version)->toBe(config('legal.privacy_version'))
        ->and($row->user_id)->toBeNull()
        ->and($row->ip_hash)->toBe(hash_hmac('sha256', '203.0.113.7', config('app.key')))
        ->and($row->ip_hash)->not->toContain('203.0.113.7');

    $cookie = collect($response->headers->getCookies())->firstWhere(fn ($c) => $c->getName() === 'c360_consent');
    $value = json_decode((string) $cookie->getValue(), true);

    expect($cookie->isHttpOnly())->toBeFalse()
        ->and($cookie->getSameSite())->toBe('lax')
        ->and($cookie->getPath())->toBe('/')
        ->and($cookie->getExpiresTime())->toBeGreaterThan(now()->addDays(179)->getTimestamp())
        ->and($value)->toMatchArray(['v' => config('legal.privacy_version'), 'id' => $row->consent_id, 'necessary' => true, 'analytics' => true, 'marketing' => false]);
});

it('reuses the receipt id when the visitor changes the choice', function (): void {
    $id = (string) Str::uuid();

    $this->postJson(route('consent.store'), ['analytics' => false, 'marketing' => false, 'source' => 'preferences', 'consent_id' => $id])
        ->assertNoContent();

    expect(CookieConsent::sole()->consent_id)->toBe($id);
});

it('rejects invalid input with 422 JSON', function (array $payload, string $field): void {
    $this->postJson(route('consent.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors($field);

    expect(CookieConsent::count())->toBe(0);
})->with([
    'missing analytics' => [['marketing' => true, 'source' => 'banner'], 'analytics'],
    'unknown source' => [['analytics' => true, 'marketing' => true, 'source' => 'hack'], 'source'],
    'bad receipt id' => [['analytics' => true, 'marketing' => true, 'source' => 'banner', 'consent_id' => 'x'], 'consent_id'],
]);

it('is CSRF-protected (web group, not in the CSRF exclusions)', function (): void {
    // CSRF verification is skipped while running tests, so check the wiring instead.
    expect(app('router')->getRoutes()->getByName('consent.store')->gatherMiddleware())->toContain('web')
        ->and(file_get_contents(base_path('bootstrap/app.php')))->not->toContain("'privacidade/consentimento'");
});

it('limits the endpoint to 20 requests per minute', function (): void {
    for ($i = 1; $i <= 20; $i++) {
        $this->postJson(route('consent.store'), ['analytics' => false, 'marketing' => false, 'source' => 'banner'])->assertNoContent();
    }

    $this->postJson(route('consent.store'), ['analytics' => false, 'marketing' => false, 'source' => 'banner'])->assertStatus(429);
});

it('logs consent changes of authenticated users in the privacy log', function (): void {
    $user = Personas::owner();

    $this->actingAs($user)
        ->postJson(route('consent.store'), ['analytics' => false, 'marketing' => false, 'source' => 'do_not_sell'])
        ->assertNoContent();

    $log = Activity::where('log_name', 'privacy')->where('event', 'consent.updated')->sole();
    expect(CookieConsent::sole()->user_id)->toBe($user->id)
        ->and($log->causer_id)->toBe($user->id)
        ->and($log->properties['source'])->toBe('do_not_sell')
        ->and($log->properties['marketing'])->toBeFalse();
});

it('still returns 204 with the cookie when the database write fails', function (): void {
    Log::spy();
    DB::statement('DROP TABLE cookie_consents');

    $response = $this->postJson(route('consent.store'), ['analytics' => true, 'marketing' => true, 'source' => 'banner'])
        ->assertNoContent();

    expect(collect($response->headers->getCookies())->contains(fn ($c) => $c->getName() === 'c360_consent'))->toBeTrue();
    Log::shouldHaveReceived('error')->once();
});
