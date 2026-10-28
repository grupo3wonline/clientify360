<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\Account;
use App\Models\User;
use App\Services\AccountService;

/**
 * Test users by role. The factory provisions an account (owner role) for every
 * user created without withoutAccount(). Create other-account fixtures before
 * actingAs(), never while authenticated as a different account.
 */
final class Personas
{
    /** @param  'basic'|'professional'  $plan */
    public static function owner(string $plan = 'basic'): User
    {
        $owner = User::factory()->create();

        if ($plan === 'professional') {
            Subscriptions::makeProfessional($owner->account);
        }

        return $owner->fresh();
    }

    public static function manager(Account $account): User
    {
        return self::member($account, User::ROLE_MANAGER);
    }

    public static function collaborator(Account $account): User
    {
        return self::member($account, User::ROLE_COLLABORATOR);
    }

    private static function member(Account $account, string $role): User
    {
        $user = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($user, $account, $role);

        return $user->fresh();
    }
}
