<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class SecurityCheckCommand extends Command
{
    protected $signature = 'security:check';

    protected $description = 'Verify security-critical configuration for production (debug, key, HTTPS, webhook, session, reCAPTCHA)';

    public function handle(): int
    {
        if (!$this->getLaravel()->environment('production')) {
            $this->info('Security check skipped: not running in production.');

            return self::SUCCESS;
        }

        $failures = array_filter($this->checks(), fn (array $check): bool => $check[0]);

        foreach ($failures as [, $message]) {
            $this->error($message);
        }

        if ($failures !== []) {
            return self::FAILURE;
        }

        $this->info('Security check passed: no critical configuration issues detected.');

        return self::SUCCESS;
    }

    /**
     * Each entry is [failed, message].
     *
     * @return list<array{0: bool, 1: string}>
     */
    private function checks(): array
    {
        return [
            [config('app.debug') === true, 'APP_DEBUG is enabled in production: stack traces and configuration can leak.'],
            [blank(config('app.key')), 'APP_KEY is empty: sessions, cookies and encrypted columns are not protected.'],
            [!str_starts_with((string) config('app.url'), 'https://'), 'APP_URL does not use https://: links, redirects and cookies are not served over HTTPS.'],
            [blank(config('cashier.webhook.secret')), 'STRIPE_WEBHOOK_SECRET is empty: Stripe webhooks are rejected (signature cannot be verified).'],
            [config('session.secure') !== true, 'Session cookie is not marked secure: set SESSION_SECURE_COOKIE=true (or remove the line) in production.'],
            // RecaptchaService treats an empty secret as fail-open (anti-bot off).
            [blank(config('services.recaptcha.secret_key')), 'reCAPTCHA secret key is empty in production: anti-bot protection is disabled (fail-open).'],
        ];
    }
}
