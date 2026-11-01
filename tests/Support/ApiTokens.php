<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\User;
use App\Support\ApiAbilities;
use DateTimeInterface;
use Spatie\Permission\PermissionRegistrar;

/** Sanctum tokens for API tests. */
final class ApiTokens
{
    /** @param  list<string>  $abilities */
    public static function for(User $user, array $abilities = ApiAbilities::ALL, ?DateTimeInterface $expiresAt = null): string
    {
        return $user->createToken('teste', $abilities, $expiresAt ?? now()->addDays(30))->plainTextToken;
    }

    /**
     * A member of the account with only the given direct permissions (no role),
     * to exercise permission checks that none of the built-in roles would fail.
     *
     * @param  list<string>  $permissions
     */
    public static function memberWithPermissions(User $owner, array $permissions): User
    {
        $user = User::factory()->withoutAccount()->create(['account_id' => $owner->account_id]);
        app(PermissionRegistrar::class)->setPermissionsTeamId($owner->account_id);
        $user->givePermissionTo($permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user->fresh();
    }
}
