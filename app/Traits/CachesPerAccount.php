<?php

declare(strict_types=1);

namespace App\Traits;

use App\Support\AccountCache;
use Closure;

/**
 * Wraps a producer in AccountCache for the authenticated user's account.
 * Without an account (guest/console) the value is computed uncached.
 */
trait CachesPerAccount
{
    private function rememberForAccount(string $key, int $ttlSeconds, Closure $callback): mixed
    {
        $accountId = auth()->user()?->account_id;

        if ($accountId === null) {
            return $callback();
        }

        return app(AccountCache::class)->remember((string) $accountId, $key, $ttlSeconds, $callback);
    }
}
