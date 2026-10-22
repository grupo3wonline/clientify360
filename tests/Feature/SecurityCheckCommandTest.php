<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Guard de produção para o reCAPTCHA: o comando security:check torna visível,
 * com exit code != 0, a condição de fail-open silencioso (secret vazio em produção).
 */
class SecurityCheckCommandTest extends TestCase
{
    public function test_it_fails_in_production_when_recaptcha_secret_is_empty(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['services.recaptcha.secret_key' => '']);

        $this->artisan('security:check')->assertExitCode(1);
    }

    public function test_it_passes_in_testing_when_recaptcha_secret_is_empty(): void
    {
        config(['services.recaptcha.secret_key' => '']);

        $this->artisan('security:check')->assertExitCode(0);
    }

    public function test_it_passes_in_production_when_recaptcha_secret_is_present(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['services.recaptcha.secret_key' => 'prod-secret']);

        $this->artisan('security:check')->assertExitCode(0);
    }
}
