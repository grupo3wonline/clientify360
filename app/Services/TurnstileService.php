<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Verifies Cloudflare Turnstile tokens (siteverify).
 *
 * Behaviour is controlled by the presence of a secret key, like {@see RecaptchaService}:
 * - secret key empty   => captcha DISABLED (fail-open in dev): verify() returns true.
 * - secret key present => captcha ENFORCED (fail-closed): any missing token, transport
 *   error, non-2xx answer or `success !== true` is a failed verification.
 *
 * Tokens are single-use and expire after 5 minutes; Cloudflare reports both cases as
 * a failure, so a replayed token is rejected here too.
 */
final class TurnstileService
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /** Cloudflare documents a 2048-character maximum for the token. */
    private const MAX_TOKEN_LENGTH = 2048;

    private const TIMEOUT_SECONDS = 8;

    /** Captcha is enabled only when a secret key is configured. */
    public function isEnabled(): bool
    {
        return filled(config('services.turnstile.secret_key'));
    }

    /**
     * @param  string|null  $token  The client-side token (cf-turnstile-response).
     * @param  string|null  $expectedAction  Action set on the widget (data-action).
     * @param  string|null  $ip  Optional visitor IP (remoteip).
     */
    public function verify(?string $token, ?string $expectedAction = null, ?string $ip = null): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if (blank($token) || strlen($token) > self::MAX_TOKEN_LENGTH) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(self::TIMEOUT_SECONDS)->post(self::VERIFY_URL, array_filter([
                'secret' => (string) config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (Throwable) {
            return false;
        }

        if ($response->failed()) {
            return false;
        }

        $data = $response->json();

        if (!is_array($data) || ($data['success'] ?? false) !== true) {
            return false;
        }

        // The action travels inside the signed token, so a token minted for the login
        // widget cannot be replayed on another form. Cloudflare only echoes it when the
        // widget set data-action, hence the check applies to a non-empty answer.
        $action = (string) ($data['action'] ?? '');

        return !($expectedAction !== null && $action !== '' && $action !== $expectedAction);
    }
}
