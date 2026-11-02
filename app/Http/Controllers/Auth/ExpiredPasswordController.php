<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PasswordChangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Forced password change after the lifetime ran out. Lives outside the password-expiry
 * middleware (it is where that middleware sends the user), but still requires a session.
 */
final class ExpiredPasswordController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        // Nothing to renew: do not let the page become a second "change password" form.
        if (!$request->user()->passwordExpired()) {
            return redirect()->route('dashboard');
        }

        return view('auth.password-expired', ['user' => $request->user()]);
    }

    public function update(Request $request, PasswordChangeService $changer): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            // "different" blocks the loophole of "changing" the password to the same one.
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ], [
            'password.different' => 'A nova senha deve ser diferente da senha atual.',
        ]);

        $changer->change($user, $validated['password'], $request->session());
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Senha atualizada. Ela é válida por '.config('security.password.max_age_days').' dias.');
    }
}
