<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Per-account cache without tags (the database store has none).
 *
 * Derived data uses versioned keys ("acct:{id}:v{n}:{key}"): bump() moves the
 * account to a new version and the old keys expire through their TTL.
 * Billing data (Stripe) uses unversioned keys, so data writes do not evict it.
 *
 * Only cache plain arrays/scalars: config('cache.serializable_classes') is
 * false, so objects would not unserialize from the database store.
 *
 * A cache outage never breaks a request or a model write: store failures are
 * logged once per request and the value is computed uncached. Exceptions
 * thrown by the callback itself propagate unchanged (it runs only once).
 */
final class AccountCache
{
    public const BILLING_KEYS = ['summary', 'invoices'];

    private static bool $warned = false;

    public function remember(string $accountId, string $key, int $ttlSeconds, Closure $callback): mixed
    {
        $version = $this->attempt($accountId, fn (): int => (int) Cache::get($this->versionKey($accountId), 1));

        if ($version === null) {
            return $callback();
        }

        return $this->rememberKey($accountId, "acct:{$accountId}:v{$version}:{$key}", $ttlSeconds, $callback);
    }

    public function bump(string $accountId): void
    {
        $this->attempt($accountId, function () use ($accountId): bool {
            $versionKey = $this->versionKey($accountId);

            if (Cache::has($versionKey)) {
                Cache::increment($versionKey);
            } else {
                Cache::forever($versionKey, 2);
            }

            return true;
        });
    }

    public function rememberBilling(string $accountId, string $key, int $ttlSeconds, Closure $callback): mixed
    {
        return $this->rememberKey($accountId, $this->billingKey($accountId, $key), $ttlSeconds, $callback);
    }

    public function getBilling(string $accountId, string $key): mixed
    {
        return $this->attempt($accountId, fn (): mixed => Cache::get($this->billingKey($accountId, $key)));
    }

    public function putBilling(string $accountId, string $key, mixed $value, int $ttlSeconds): void
    {
        $this->attempt($accountId, fn (): bool => Cache::put($this->billingKey($accountId, $key), $value, $ttlSeconds));
    }

    public function forgetBilling(string $accountId): void
    {
        $this->attempt($accountId, function () use ($accountId): bool {
            foreach (self::BILLING_KEYS as $key) {
                Cache::forget($this->billingKey($accountId, $key));
            }

            return true;
        });
    }

    public function billingKey(string $accountId, string $key): string
    {
        return "acct:{$accountId}:billing:{$key}";
    }

    private function rememberKey(string $accountId, string $fullKey, int $ttlSeconds, Closure $callback): mixed
    {
        $cached = $this->attempt($accountId, fn (): mixed => Cache::get($fullKey));

        if ($cached !== null) {
            return $cached;
        }

        $value = $callback();

        if ($value !== null) {
            $this->attempt($accountId, fn (): bool => Cache::put($fullKey, $value, $ttlSeconds));
        }

        return $value;
    }

    /** Runs a store operation; returns null (and warns once) when the store fails. */
    private function attempt(string $accountId, Closure $operation): mixed
    {
        try {
            return $operation();
        } catch (Throwable $e) {
            $this->warn($accountId, $e);

            return null;
        }
    }

    private function versionKey(string $accountId): string
    {
        return "acct:{$accountId}:ver";
    }

    private function warn(string $accountId, Throwable $e): void
    {
        if (self::$warned) {
            return;
        }

        self::$warned = true;

        Log::warning('Account cache unavailable', [
            'account_id' => $accountId,
            'exception' => $e::class,
        ]);
    }
}
