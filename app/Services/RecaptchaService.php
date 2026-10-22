<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Verifies Google reCAPTCHA v3 (classic) tokens.
 *
 * Behaviour is controlled by the presence of a secret key:
 * - secret key empty  => captcha DISABLED (fail-open in dev): verify() returns true.
 * - secret key present => captcha ENFORCED (fail-closed): verify() returns true only
 *   when Google reports success, the score meets the minimum and (when provided) the
 *   action matches. Any HTTP/parsing failure is treated as a failed verification.
 */
final class RecaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /** Captcha is enabled only when a secret key is configured. */
    public function isEnabled(): bool
    {
        return filled(config('services.recaptcha.secret_key'));
    }

    /**
     * Verify a reCAPTCHA v3 token.
     *
     * @param  string|null  $token  The client-side token (g-recaptcha-response).
     * @param  string|null  $expectedAction  Optional action to match against the response.
     * @param  string|null  $ip  Optional client IP (remoteip).
     */
    public function verify(?string $token, ?string $expectedAction = null, ?string $ip = null): bool
    {
        // Captcha disabled (no secret): fail-open so dev/tests keep working.
        if (!$this->isEnabled()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post(self::VERIFY_URL, array_filter([
                'secret' => (string) config('services.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (Throwable) {
            // Network/transport failure: fail-closed when enabled.
            return false;
        }

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();

        if (($data['success'] ?? false) !== true) {
            return false;
        }

        $score = (float) ($data['score'] ?? 0);

        if ($score < (float) config('services.recaptcha.min_score', 0.5)) {
            return false;
        }

        // When an action is expected, the response action must match.
        if ($expectedAction !== null && ($data['action'] ?? null) !== $expectedAction) {
            return false;
        }

        return true;
    }
}
