<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TermsController extends Controller
{
    /**
     * Public, read-only terms page. An authenticated user who has not accepted
     * the current version gets the acceptance form instead.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        if ($user !== null && !$user->hasAcceptedCurrentTerms()) {
            return view('terms.accept');
        }

        return view('terms.show');
    }

    public function accept(Request $request): RedirectResponse
    {
        $request->validate([
            'accept_terms' => ['required', 'accepted'],
        ], [
            'accept_terms.required' => 'Você deve aceitar os termos de uso.',
            'accept_terms.accepted' => 'Você deve aceitar os termos de uso para continuar.',
        ]);

        $request->user()->update([
            'terms_accepted_at' => now(),
            'terms_version' => (string) config('legal.terms_version'),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Termos aceitos com sucesso! Bem-vindo ao Clientify360.');
    }
}
