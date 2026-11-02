<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Changes the password of the signed-in user and keeps their session alive.
 *
 * The panel (/app) stores a hash of the password in the session (key "password_hash_web") and
 * logs the session out when it no longer matches. The Blade routes do not run that middleware,
 * so changing the password there leaves the panel's stored hash stale and the next /app request
 * signs the user out. This service refreshes that hash and ends every OTHER session, because
 * the old password may be the reason for the change.
 */
final class PasswordChangeService
{
    public function change(User $user, string $newPassword, Session $session): void
    {
        $user->update(['password' => Hash::make($newPassword)]);

        // Ends the other sessions and re-hashes for this device.
        Auth::logoutOtherDevices($newPassword);

        // Same value the panel's AuthenticateSession compares against.
        $session->put(
            'password_hash_'.Auth::getDefaultDriver(),
            Auth::guard()->hashPasswordForCookie($user->fresh()->getAuthPassword()),
        );
    }
}
