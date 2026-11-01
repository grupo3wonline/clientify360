<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CookieConsent;
use App\Models\User;
use App\Support\PrivacyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

/**
 * Records cookie-consent decisions (proof of consent) and builds the
 * first-party "c360_consent" cookie read by resources/js/consent.js.
 */
final class CookieConsentService
{
    public const COOKIE = 'c360_consent';

    public const COOKIE_DAYS = 180;

    /**
     * Store one immutable consent row. The raw IP is never stored, only an
     * HMAC hash. A database failure is logged and does not block the user's
     * choice: the returned model is then unsaved (exists = false).
     *
     * @param  array{analytics: bool, marketing: bool, source: string, consent_id?: string|null}  $data
     */
    public function record(array $data, ?User $user, Request $request): CookieConsent
    {
        $consent = new CookieConsent([
            'consent_id' => ($data['consent_id'] ?? null) ?: (string) Str::uuid(),
            'user_id' => $user?->id,
            'analytics' => $data['analytics'],
            'marketing' => $data['marketing'],
            'policy_version' => (string) config('legal.privacy_version'),
            'source' => $data['source'],
            'ip_hash' => $this->hashIp($request->ip()),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);

        try {
            $consent->save();
        } catch (Throwable $e) {
            Log::error('Falha ao registrar o consentimento de cookies.', ['exception' => $e->getMessage()]);

            return $consent;
        }

        if ($user !== null) {
            PrivacyLog::record('consent.updated', 'Preferências de cookies atualizadas', $user, $consent, [
                'analytics' => $consent->analytics,
                'marketing' => $consent->marketing,
                'source' => $consent->source,
                'policy_version' => $consent->policy_version,
            ]);
        }

        return $consent;
    }

    /** The cookie JS reads: no personal data, only the choice and a random receipt id. */
    public function cookie(CookieConsent $consent, Request $request): Cookie
    {
        $value = (string) json_encode([
            'v' => $consent->policy_version,
            'id' => $consent->consent_id,
            'necessary' => true,
            'analytics' => $consent->analytics,
            'marketing' => $consent->marketing,
            'ts' => now()->getTimestamp(),
        ], JSON_UNESCAPED_SLASHES);

        return Cookie::create(self::COOKIE, $value)
            ->withExpires(now()->addDays(self::COOKIE_DAYS))
            ->withPath('/')
            ->withSecure($request->secure())
            ->withHttpOnly(false)
            ->withSameSite(Cookie::SAMESITE_LAX);
    }

    private function hashIp(?string $ip): ?string
    {
        return $ip === null ? null : hash_hmac('sha256', $ip, (string) config('app.key'));
    }
}
