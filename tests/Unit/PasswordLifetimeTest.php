<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

// Needs the database (users table): Pest.php only auto-applies RefreshDatabase to Feature.
uses(RefreshDatabase::class);

beforeEach(function (): void {
    config(['security.password.max_age_days' => 90, 'security.password.warning_days' => 7]);
});

/** A user whose password was set $days ago, at the given clock time. */
function lifetimeUserWithPasswordAge(int $days, string $time = '12:00:00'): User
{
    return User::factory()->create([
        'password_changed_at' => now()->subDays($days)->setTimeFromTimeString($time),
    ]);
}

it('starts the clock when a user is created', function (): void {
    $this->travelTo('2026-11-02 10:00:00');

    $user = User::factory()->create();

    expect($user->password_changed_at->toDateTimeString())->toBe('2026-11-02 10:00:00');
});

it('renews the clock whenever the password changes', function (): void {
    $this->travelTo('2026-01-01 10:00:00');
    $user = User::factory()->create();

    $this->travelTo('2026-03-01 08:30:00');
    $user->update(['password' => 'NovaSenha123']);

    expect($user->fresh()->password_changed_at->toDateTimeString())->toBe('2026-03-01 08:30:00');
});

it('does not touch the clock when other fields change', function (): void {
    $this->travelTo('2026-01-01 10:00:00');
    $user = User::factory()->create();

    $this->travelTo('2026-03-01 08:30:00');
    $user->update(['name' => 'Outro Nome']);

    expect($user->fresh()->password_changed_at->toDateTimeString())->toBe('2026-01-01 10:00:00');
});

it('respects an explicit password_changed_at saved together with the password', function (): void {
    $user = User::factory()->create();

    $user->forceFill(['password' => 'OutraSenha123', 'password_changed_at' => '2020-05-05 00:00:00'])->save();

    expect($user->fresh()->password_changed_at->toDateString())->toBe('2020-05-05');
});

it('is valid through the end of its deadline day and blocked from the next day', function (int $age, bool $expired): void {
    $this->travelTo('2026-11-02 12:00:00');

    expect(lifetimeUserWithPasswordAge($age)->passwordExpired())->toBe($expired);
})->with([
    'day 89' => [89, false],
    'day 90: deadline day, still valid' => [90, false],
    'day 91: blocked' => [91, true],
    'day 200' => [200, true],
]);

it('reports whole days left by calendar day', function (int $age, int $left): void {
    $this->travelTo('2026-11-02 12:00:00');

    expect(lifetimeUserWithPasswordAge($age)->passwordDaysLeft())->toBe($left);
})->with([
    'brand new' => [0, 90],
    '83 days old' => [83, 7],
    '89 days old' => [89, 1],
]);

it('warns during the last 7 days only', function (int $age, bool $warns): void {
    $this->travelTo('2026-11-02 12:00:00');

    expect(lifetimeUserWithPasswordAge($age)->passwordExpiresSoon())->toBe($warns);
})->with([
    '82 days: still 8 left' => [82, false],
    '83 days: 7 left' => [83, true],
    '89 days: 1 left' => [89, true],
    '90 days: expires today' => [90, true],
    '91 days: expired, blocked not warned' => [91, false],
    '120 days: long expired' => [120, false],
]);

it('is never expired or warned when the date is unknown', function (): void {
    $user = User::factory()->create();
    $user->forceFill(['password_changed_at' => null])->saveQuietly();

    expect($user->fresh()->passwordExpired())->toBeFalse()
        ->and($user->fresh()->passwordExpiresSoon())->toBeFalse()
        ->and($user->fresh()->passwordDaysLeft())->toBeNull();
});

it('warning and block never leave a gap or overlap, at any time of the deadline day', function (): void {
    // Regression guard for the day/instant mix: on the last day the user must be warned for
    // the whole day (never blocked and never in a gap), and blocked from the next day on.
    $user = User::factory()->create(['password_changed_at' => '2026-08-04 18:45:00']); // deadline day: 2026-11-02

    foreach (['00:00:00', '12:00:00', '18:44:59', '18:45:00', '18:45:01', '23:59:59'] as $time) {
        $this->travelTo('2026-11-02 '.$time);
        $fresh = $user->fresh();

        expect($fresh->passwordExpiresSoon())->toBeTrue("not warned at {$time}")
            ->and($fresh->passwordExpired())->toBeFalse("blocked too early at {$time}");
    }

    $this->travelTo('2026-11-03 00:00:00');
    $fresh = $user->fresh();

    expect($fresh->passwordExpired())->toBeTrue()
        ->and($fresh->passwordExpiresSoon())->toBeFalse();
});
