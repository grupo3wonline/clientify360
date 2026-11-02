<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\Support\ApiTokens;
use Tests\Support\Subscriptions;

/** A Profissional account owner, so a 403 can only come from the missing e-mail confirmation. */
function verifyUnverifiedProfessional(): User
{
    $user = User::factory()->unverified()->create();
    Subscriptions::makeProfessional($user->account);

    return $user->fresh();
}

// -------------------------------------------------- web: nothing works unverified

it('keeps an unverified user out of every app area', function (string $url): void {
    $this->actingAs(User::factory()->unverified()->create())
        ->get($url)
        ->assertRedirect(route('verification.notice'));
})->with(['/profile', '/clients', '/products', '/quotes', '/finance', '/billing', '/notifications', '/reports', '/app', '/app/clients']);

it('lets an unverified user reach only the verification screens, resend and logout', function (): void {
    $user = User::factory()->unverified()->create();
    Notification::fake();

    $this->actingAs($user)->get(route('verification.notice'))->assertOk();
    $this->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');
    Notification::assertSentTo($user, VerifyEmailNotification::class);

    $this->post(route('logout'))->assertRedirect();
});

it('activates the account only through the signed link, and then opens the app', function (): void {
    $user = User::factory()->unverified()->create();
    $link = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]);

    expect($user->hasVerifiedEmail())->toBeFalse();

    $this->actingAs($user)->get($link)->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $this->get('/clients')->assertOk();
});

it('rejects a tampered or expired activation link', function (string $case): void {
    $user = User::factory()->unverified()->create();

    $link = match ($case) {
        'wrong hash' => URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1('outro@email.com')]),
        'expired' => URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]),
        'unsigned' => route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]),
    };

    $this->actingAs($user)->get($link)->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
})->with(['wrong hash', 'expired', 'unsigned']);

it('does not let one user activate another user\'s account', function (): void {
    $victim = User::factory()->unverified()->create();
    $attacker = User::factory()->create();
    $link = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), ['id' => $victim->id, 'hash' => sha1($victim->email)]);

    $this->actingAs($attacker)->get($link)->assertForbidden();

    expect($victim->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('sends the activation e-mail on registration and leaves the account inactive', function (): void {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Maria Silva', 'username' => 'maria_silva', 'email' => 'maria@example.com', 'lgpd_consent' => '1',
        'password' => 'Senha12345', 'password_confirmation' => 'Senha12345',
    ])->assertRedirect(route('verification.notice'));

    $user = User::where('email', 'maria@example.com')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeFalse();
    Notification::assertSentTo($user, VerifyEmailNotification::class);
    $this->get('/clients')->assertRedirect(route('verification.notice'));
});

// ------------------------------------------------- changing the e-mail re-locks

it('locks the account again when the e-mail is changed until the new one is confirmed', function (): void {
    $user = User::factory()->create();
    expect($user->hasVerifiedEmail())->toBeTrue();

    $this->actingAs($user)->patch('/profile', ['name' => $user->name, 'username' => $user->username, 'email' => 'novo@example.com'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
    $this->get('/clients')->assertRedirect(route('verification.notice'));
});

// ------------------------------------------------------------------- the API

it('refuses API tokens of an unverified user, in JSON', function (): void {
    $user = verifyUnverifiedProfessional();

    $this->withToken(ApiTokens::for($user))->getJson('/api/v1/me')
        ->assertForbidden()
        ->assertExactJson(['message' => 'Confirme seu e-mail para usar a API.']);

    $this->withToken(ApiTokens::for($user))->getJson('/api/v1/clients')->assertForbidden();
});

it('cuts off an existing token as soon as the e-mail is changed (no stale access)', function (): void {
    $user = User::factory()->create();
    Subscriptions::makeProfessional($user->account);
    $token = ApiTokens::for($user->fresh());

    $this->withToken($token)->getJson('/api/v1/me')->assertOk();

    $user->forceFill(['email' => 'trocado@example.com', 'email_verified_at' => null])->save();

    // A real client sends a brand new request. Inside one test process Laravel's guard keeps the
    // user it already loaded, so drop the cached guards to behave like a fresh request.
    app('auth')->forgetGuards();

    $this->withToken($token)->getJson('/api/v1/me')->assertForbidden();
});

it('keeps the API working for a verified user', function (): void {
    $user = User::factory()->create();
    Subscriptions::makeProfessional($user->account);

    $this->withToken(ApiTokens::for($user->fresh()))->getJson('/api/v1/me')->assertOk();
});
