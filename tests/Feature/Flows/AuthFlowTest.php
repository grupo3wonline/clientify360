<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

/*
 * End-to-end account flow: register (name, username, e-mail, password, consent)
 * -> verify the e-mail through the signed link -> logout -> log in with the
 * 3 fields -> /app -> forgot password -> reset -> log in with the new password.
 */

it('takes a new user from registration to a password reset', function (): void {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Maria Souza', 'username' => 'maria_souza', 'email' => 'maria@example.com',
        'password' => 'password', 'password_confirmation' => 'password', 'lgpd_consent' => '1',
    ])->assertRedirect(route('verification.notice'));

    $user = User::where('email', 'maria@example.com')->firstOrFail();
    expect($user->account_id)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse();
    Notification::assertSentTo($user, VerifyEmailNotification::class);

    // Unverified: the panel sends the user back to the verification notice.
    $this->get('/app')->assertRedirect(route('verification.notice'));

    $verifyUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id, 'hash' => sha1($user->getEmailForVerification()),
    ]);
    $this->get($verifyUrl)->assertRedirect();
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    $this->post('/logout')->assertRedirect();
    $this->assertGuest();

    // Two fields are not enough: username, e-mail and password are all required.
    $this->post('/login', ['email' => 'maria@example.com', 'password' => 'password'])->assertSessionHasErrors('username');
    $this->assertGuest();

    $this->post('/login', ['username' => 'maria_souza', 'email' => 'maria@example.com', 'password' => 'password'])
        ->assertRedirect();
    $this->assertAuthenticatedAs($user->fresh());
    // First access: the current Terms of Use must be accepted in-app.
    $this->get('/app')->assertRedirect(route('terms.show'));
    $this->post(route('terms.accept'), ['accept_terms' => '1'])->assertRedirect(route('dashboard'));
    $this->get('/app')->assertOk();

    $this->post('/logout');
    $this->post('/forgot-password', ['email' => 'maria@example.com'])->assertSessionHas('status');

    $token = null;
    Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $n) use (&$token): bool {
        $token = $n->token;

        return true;
    });
    $this->get(route('password.reset', ['token' => $token, 'email' => 'maria@example.com']))->assertOk();

    $this->post('/reset-password', [
        'token' => $token, 'email' => 'maria@example.com',
        'password' => 'nova-senha-123', 'password_confirmation' => 'nova-senha-123',
    ])->assertSessionHasNoErrors()->assertRedirect(route('login'));

    $this->post('/login', ['username' => 'maria_souza', 'email' => 'maria@example.com', 'password' => 'password'])
        ->assertSessionHasErrors();
    $this->assertGuest();

    $this->post('/login', ['username' => 'maria_souza', 'email' => 'maria@example.com', 'password' => 'nova-senha-123'])
        ->assertRedirect();
    $this->assertAuthenticatedAs($user->fresh());
});

it('refuses an expired or tampered verification link', function (): void {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user);
    $params = ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())];

    $this->get(URL::temporarySignedRoute('verification.verify', now()->subMinute(), $params))->assertForbidden();
    $this->get(URL::temporarySignedRoute('verification.verify', now()->addHour(), $params).'x')->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
