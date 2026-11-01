<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Support\PhpExtensions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Production guard: security:check exits non-zero for each insecure
 * configuration (debug on, missing key, no HTTPS, missing webhook secret,
 * insecure session cookie, empty reCAPTCHA secret).
 */
class SecurityCheckCommandTest extends TestCase
{
    /** A production configuration that passes every check. */
    private function secureProductionConfig(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        config([
            'app.debug' => false,
            'app.key' => 'base64:'.base64_encode(str_repeat('k', 32)),
            'app.url' => 'https://clientify360.example',
            'cashier.webhook.secret' => 'whsec_test',
            'session.secure' => true,
            'services.recaptcha.secret_key' => 'prod-secret',
        ]);

        $this->fakePhp();
    }

    /** @param  list<string>  $missing */
    private function fakePhp(array $missing = [], bool $procOpenDisabled = false): void
    {
        $this->instance(PhpExtensions::class, new class($missing, $procOpenDisabled) extends PhpExtensions
        {
            /** @param  list<string>  $missingNames */
            public function __construct(private readonly array $missingNames, private readonly bool $noProcOpen) {}

            public function missing(array $names): array
            {
                return array_values(array_intersect($names, $this->missingNames));
            }

            public function procOpenDisabled(): bool
            {
                return $this->noProcOpen;
            }
        });
    }

    public function test_it_passes_in_production_when_every_check_is_satisfied(): void
    {
        $this->secureProductionConfig();

        $this->artisan('security:check')->assertExitCode(0);
    }

    public function test_it_is_skipped_outside_production(): void
    {
        config(['services.recaptcha.secret_key' => '', 'app.debug' => true]);

        $this->artisan('security:check')
            ->expectsOutput('Security check skipped: not running in production.')
            ->assertExitCode(0);
    }

    /** @return array<string, array{0: string, 1: mixed, 2: string}> */
    public static function insecureSettings(): array
    {
        return [
            'debug enabled' => ['app.debug', true, 'APP_DEBUG is enabled'],
            'empty app key' => ['app.key', '', 'APP_KEY is empty'],
            'http app url' => ['app.url', 'http://clientify360.example', 'APP_URL does not use https://'],
            'empty webhook secret' => ['cashier.webhook.secret', '', 'STRIPE_WEBHOOK_SECRET is empty'],
            'insecure session cookie' => ['session.secure', false, 'Session cookie is not marked secure'],
            'empty recaptcha secret' => ['services.recaptcha.secret_key', '', 'reCAPTCHA secret key is empty'],
        ];
    }

    #[DataProvider('insecureSettings')]
    public function test_it_fails_in_production_for_each_insecure_setting(string $key, mixed $value, string $message): void
    {
        $this->secureProductionConfig();
        config([$key => $value]);

        $this->artisan('security:check')
            ->expectsOutputToContain($message)
            ->assertExitCode(1);
    }

    /** @return array<string, array{0: string}> */
    public static function requiredExtensions(): array
    {
        return array_combine(PhpExtensions::REQUIRED, array_map(fn (string $name): array => [$name], PhpExtensions::REQUIRED));
    }

    #[DataProvider('requiredExtensions')]
    public function test_it_fails_in_production_when_a_required_extension_is_missing(string $extension): void
    {
        $this->secureProductionConfig();
        $this->fakePhp([$extension]);

        $this->artisan('security:check')
            ->expectsOutputToContain("Extensão PHP ausente: {$extension}")
            ->assertExitCode(1);
    }

    public function test_it_fails_in_production_when_proc_open_is_disabled(): void
    {
        $this->secureProductionConfig();
        $this->fakePhp([], true);

        $this->artisan('security:check')
            ->expectsOutputToContain('Função PHP `proc_open` desabilitada: o agendador não executa comandos')
            ->assertExitCode(1);
    }

    public function test_the_real_checker_reports_loaded_extensions_as_present(): void
    {
        $this->assertSame(['definitely_not_an_extension'], (new PhpExtensions)->missing(['json', 'definitely_not_an_extension']));
    }
}
