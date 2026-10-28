<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaRuleTest extends TestCase
{
    use RefreshDatabase;

    private function enableCaptcha(): void
    {
        config([
            'services.recaptcha.secret_key' => 'test-secret',
            'services.recaptcha.site_key' => 'test-site',
            'services.recaptcha.min_score' => 0.5,
        ]);
    }

    public function test_login_is_rejected_when_captcha_verification_fails(): void
    {
        $this->enableCaptcha();

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
        ]);

        $user = User::factory()->create();

        $this->post('/login', [
            'username' => $user->username,
            'email' => $user->email,
            'password' => 'password',
            'g-recaptcha-response' => 'bad-token',
        ])->assertSessionHasErrors('g-recaptcha-response');

        $this->assertGuest();
    }

    public function test_register_is_rejected_when_captcha_verification_fails(): void
    {
        $this->enableCaptcha();

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
        ]);

        $this->post('/register', [
            'name' => 'Test User',
            'username' => 'test_user',
            'lgpd_consent' => '1',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'g-recaptcha-response' => 'bad-token',
        ])->assertSessionHasErrors('g-recaptcha-response');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_captcha_is_skipped_when_secret_is_empty(): void
    {
        // Default test env has no secret => captcha disabled, login works
        // without any token (this is why the existing suite stays green).
        $user = User::factory()->create();

        $this->post('/login', [
            'username' => $user->username,
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasNoErrors();

        $this->assertAuthenticated();
    }

    public function test_login_honeypot_rejects_bots(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'username' => $user->username,
            'email' => $user->email,
            'password' => 'password',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertGuest();
    }

    public function test_register_honeypot_rejects_bots(): void
    {
        $this->post('/register', [
            'name' => 'Bot User',
            'username' => 'bot_user',
            'lgpd_consent' => '1',
            'email' => 'bot@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'bot@example.com']);
    }
}
