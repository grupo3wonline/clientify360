<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PasswordChangeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request, PasswordChangeService $changer): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            // "different": changing to the same password would only restart the 90-day clock.
            'password' => ['required', Password::defaults(), 'confirmed', 'different:current_password'],
        ], [
            'password.different' => 'A nova senha deve ser diferente da senha atual.',
        ]);

        $changer->change($request->user(), $validated['password'], $request->session());

        return back()->with('status', 'password-updated');
    }
}
