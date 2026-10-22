<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RecaptchaServiceTest extends TestCase
{
    private function service(): RecaptchaService
    {
        return app(RecaptchaService::class);
    }

    private function enableCaptcha(): void
    {
        config([
            'services.recaptcha.secret_key' => 'test-secret',
            'services.recaptcha.site_key' => 'test-site',
            'services.recaptcha.min_score' => 0.5,
        ]);
    }

    public function test_captcha_is_disabled_when_secret_is_empty(): void
    {
        config(['services.recaptcha.secret_key' => '']);

        $this->assertFalse($this->service()->isEnabled());
        // Disabled => fail-open (passes) even without a token.
        $this->assertTrue($this->service()->verify(null));
    }

    public function test_verification_fails_when_google_reports_failure(): void
    {
        $this->enableCaptcha();

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response(['success' => false]),
        ]);

        $this->assertTrue($this->service()->isEnabled());
        $this->assertFalse($this->service()->verify('token', 'login'));
    }

    public function test_verification_fails_when_score_below_minimum(): void
    {
        $this->enableCaptcha();

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.1,
                'action' => 'login',
            ]),
        ]);

        $this->assertFalse($this->service()->verify('token', 'login'));
    }

    public function test_verification_fails_on_action_mismatch(): void
    {
        $this->enableCaptcha();

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'register',
            ]),
        ]);

        $this->assertFalse($this->service()->verify('token', 'login'));
    }

    public function test_verification_passes_when_success_and_score_ok(): void
    {
        $this->enableCaptcha();

        Http::fake([
            'www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'score' => 0.9,
                'action' => 'login',
            ]),
        ]);

        $this->assertTrue($this->service()->verify('token', 'login'));
    }

    public function test_verification_fails_when_token_is_empty_but_captcha_enabled(): void
    {
        $this->enableCaptcha();

        Http::fake();

        $this->assertFalse($this->service()->verify(null, 'login'));
    }
}
