<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ApiTokenService;
use App\Services\PersonalDataExportService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly PersonalDataExportService $exportService
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, ApiTokenService $tokens, SubscriptionService $plans): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'apiTokens' => $tokens->list($user),
            'canCreateTokens' => $plans->hasFeature($user, 'api'),
            'account' => $user->account,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Export the authenticated user's own personal data as a JSON download (LGPD).
     *
     * Scope is strictly the data subject (the logged-in user): identity and
     * account-membership fields only. It never includes other users' data, the
     * whole tenant, credentials or AI keys.
     */
    public function exportPersonalData(Request $request): StreamedResponse
    {
        return $this->exportService->streamForUser($request->user());
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // The owner's account logo is account data: it goes with the owner.
        if ($user->isOwner()) {
            $user->account?->clearMediaCollection('logo');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
