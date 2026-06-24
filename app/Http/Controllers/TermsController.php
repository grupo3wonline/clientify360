<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TermsController extends Controller
{
    private const CURRENT_VERSION = '1.0';

    public function show(): View
    {
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
            'terms_version' => self::CURRENT_VERSION,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Termos aceitos com sucesso! Bem-vindo ao Clientify360.');
    }
}
