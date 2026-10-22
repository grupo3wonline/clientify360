<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class SecurityCheckCommand extends Command
{
    protected $signature = 'security:check';

    protected $description = 'Verify security-critical configuration (e.g. reCAPTCHA) for production';

    public function handle(): int
    {
        // Surfaces a condition that RecaptchaService handles silently as fail-open:
        // an empty secret in production means anti-bot protection is off.
        if ($this->getLaravel()->environment('production') && blank(config('services.recaptcha.secret_key'))) {
            $this->error('reCAPTCHA secret key is empty in production: anti-bot protection is disabled (fail-open).');

            return self::FAILURE;
        }

        $this->info('Security check passed: no critical configuration issues detected.');

        return self::SUCCESS;
    }
}
