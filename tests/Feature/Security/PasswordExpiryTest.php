<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Tests\Support\ApiTokens;
use Tests\Support\Subscriptions;

beforeEach(function (): void {
    config(['security.password.max_age_days' => 90, 'security.password.warning_days' => 7]);
    $this->travelTo('2026-11-02 12:00:00');
});

/** A user whose password was set $days days ago. */
function expiryUserWithOldPassword(int $days): User
{
    return User::factory()->create(['password_changed_at' => now()->subDays($days)]);
}

// ------------------------------------------------------------------ the block

it('lets a user with a valid password in', function (): void {
    $this->actingAs(expiryUserWithOldPassword(30))->get(route('profile.edit'))->assertOk();
});

it('lets the user in on the deadline day itself', function (): void {
    $this->actingAs(expiryUserWithOldPassword(90))->get(route('profile.edit'))->assertOk();
});

it('sends an expired user to the change-password page from every authenticated area', function (string $url): void {
    $this->actingAs(expiryUserWithOldPassword(91))
        ->get($url)
        ->assertRedirect(route('password.expired'));
})->with(['/profile', '/clients', '/finance', '/billing', '/notifications', '/app']);

it('does not trap the user: the change page, logout and e-mail verification stay reachable', function (): void {
    $user = expiryUserWithOldPassword(200);

    $this->actingAs($user)->get(route('password.expired'))->assertOk()->assertSee('Sua senha expirou');
    $this->actingAs($user)->post(route('logout'))->assertRedirect();
});

it('never shows the expired page to someone whose password is still valid', function (): void {
    $this->actingAs(expiryUserWithOldPassword(10))->get(route('password.expired'))->assertRedirect(route('dashboard'));
});

// --------------------------------------------------------------- the change

it('unblocks the user after a valid new password and restarts the clock', function (): void {
    $user = expiryUserWithOldPassword(120);

    $this->actingAs($user)->put(route('password.expired.update'), [
        'current_password' => 'password',
        'password' => 'NovaSenhaForte123',
        'password_confirmation' => 'NovaSenhaForte123',
    ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

    $fresh = $user->fresh();
    expect($fresh->password_changed_at->toDateString())->toBe('2026-11-02')
        ->and($fresh->passwordExpired())->toBeFalse()
        ->and($fresh->passwordDaysLeft())->toBe(90);

    $this->get(route('profile.edit'))->assertOk();
});

it('keeps the user signed in on the panel after the forced change (no logout loop)', function (): void {
    $user = expiryUserWithOldPassword(120);

    $this->actingAs($user)->put(route('password.expired.update'), [
        'current_password' => 'password',
        'password' => 'NovaSenhaForte123',
        'password_confirmation' => 'NovaSenhaForte123',
    ]);

    $this->get('/app')->assertOk();
    $this->assertAuthenticatedAs($user->fresh());
});

it('rejects an invalid forced change', function (array $payload, string $field): void {
    $user = expiryUserWithOldPassword(120);
    $before = $user->password;

    $this->actingAs($user)
        ->put(route('password.expired.update'), $payload)
        ->assertSessionHasErrors($field);

    expect($user->fresh()->password)->toBe($before)
        ->and($user->fresh()->passwordExpired())->toBeTrue();
})->with([
    'wrong current password' => [['current_password' => 'errada', 'password' => 'NovaSenhaForte123', 'password_confirmation' => 'NovaSenhaForte123'], 'current_password'],
    'same password as before' => [['current_password' => 'password', 'password' => 'password', 'password_confirmation' => 'password'], 'password'],
    'confirmation mismatch' => [['current_password' => 'password', 'password' => 'NovaSenhaForte123', 'password_confirmation' => 'outra'], 'password'],
    'too short' => [['current_password' => 'password', 'password' => 'abc', 'password_confirmation' => 'abc'], 'password'],
]);

it('also refuses to "change" to the same password on the regular profile form', function (): void {
    $user = expiryUserWithOldPassword(80);

    $this->actingAs($user)->put(route('password.update'), [
        'current_password' => 'password',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrorsIn('updatePassword', 'password');

    expect($user->fresh()->password_changed_at->toDateString())->not->toBe('2026-11-02');
});

it('restarts the clock when the password is changed on the profile form', function (): void {
    $user = expiryUserWithOldPassword(80);

    $this->actingAs($user)->put(route('password.update'), [
        'current_password' => 'password',
        'password' => 'NovaSenhaForte123',
        'password_confirmation' => 'NovaSenhaForte123',
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->passwordDaysLeft())->toBe(90);
});

it('stays signed in on the panel after changing the password on the profile (regression)', function (): void {
    // Before the fix, opening /app and then changing the password logged the user out.
    $user = expiryUserWithOldPassword(10);

    $this->actingAs($user)->get('/app')->assertOk();
    $this->put(route('password.update'), [
        'current_password' => 'password',
        'password' => 'NovaSenhaForte123',
        'password_confirmation' => 'NovaSenhaForte123',
    ]);

    $this->get('/app')->assertOk();
});

it('restarts the clock when the password is reset by e-mail', function (): void {
    $user = expiryUserWithOldPassword(80);
    $token = Password::createToken($user);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'NovaSenhaForte123',
        'password_confirmation' => 'NovaSenhaForte123',
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->passwordDaysLeft())->toBe(90);
});

// ------------------------------------------------------------------- the API

/** The API needs the Profissional plan; without it a 403 could come from the plan, not from the password. */
function expiryProfessionalUserWithPasswordAge(int $days): User
{
    $user = expiryUserWithOldPassword($days);
    Subscriptions::makeProfessional($user->account);

    return $user->fresh();
}

it('blocks the API for an expired password, with a JSON explanation', function (): void {
    $user = expiryProfessionalUserWithPasswordAge(120);

    $this->withToken(ApiTokens::for($user))->getJson('/api/v1/me')
        ->assertForbidden()
        ->assertExactJson(['message' => 'Sua senha expirou. Acesse o sistema e defina uma nova senha.']);
});

it('keeps the API working while the password is valid', function (): void {
    $user = expiryProfessionalUserWithPasswordAge(30);

    $this->withToken(ApiTokens::for($user))->getJson('/api/v1/me')->assertOk();
});
