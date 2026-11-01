<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApiTokenLimitException;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Sanctum\NewAccessToken;

/** Personal API tokens (Sanctum) of the logged-in user. */
final class ApiTokenService
{
    public const MAX_ACTIVE_TOKENS = 5;

    public const EXPIRATION_DAYS = [30, 90, 365];

    /** @return Collection<int, PersonalAccessToken> */
    public function list(User $user): Collection
    {
        /** @var Collection<int, PersonalAccessToken> */
        return $user->tokens()->latest()->get();
    }

    /**
     * @param  list<string>  $abilities
     *
     * @throws ApiTokenLimitException
     */
    public function create(User $user, string $name, array $abilities, int $days): NewAccessToken
    {
        $active = $user->tokens()
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->count();

        if ($active >= self::MAX_ACTIVE_TOKENS) {
            throw new ApiTokenLimitException;
        }

        return $user->createToken($name, $abilities, now()->addDays($days));
    }

    /** Scoped to the user's own tokens: another user's id is a 404. */
    public function revoke(User $user, string $tokenId): void
    {
        $user->tokens()->whereKey($tokenId)->firstOrFail()->delete();
    }
}
