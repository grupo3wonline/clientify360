<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Client;
use App\Services\AiAnalysisService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class AiController extends Controller
{
    public function __construct(
        private readonly AiAnalysisService $aiService,
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function settings(Request $request): View
    {
        $user = $request->user();
        $isProfessional = $this->subscriptionService->isProfessional($user);
        $providers = $this->aiService->getAvailableProviders($user);

        return view('ai.settings', compact('user', 'isProfessional', 'providers'));
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$this->subscriptionService->isProfessional($user)) {
            return redirect()->route('ai.settings')
                ->with('error', 'Recurso disponível apenas no plano Profissional.');
        }

        $validated = $request->validate([
            'ai_gemini_key' => ['nullable', 'string', 'max:500'],
            'ai_groq_key' => ['nullable', 'string', 'max:500'],
            'ai_openrouter_key' => ['nullable', 'string', 'max:500'],
            'ai_preferred_provider' => ['nullable', 'string', 'in:gemini,groq,openrouter'],
        ]);

        $user->update($validated);

        return redirect()->route('ai.settings')
            ->with('success', 'Configurações de IA salvas com sucesso!');
    }

    public function analyze(Request $request, string $clientId): RedirectResponse
    {
        $user = $request->user();

        if (!$this->subscriptionService->isProfessional($user)) {
            return redirect()->back()
                ->with('error', 'Recurso disponível apenas no plano Profissional.');
        }

        $client = Client::findOrFail($clientId);
        $this->authorize('view', $client);

        $provider = $request->input('provider', $user->ai_preferred_provider ?? 'gemini');
        $result = $this->aiService->analyzeClient($user, $client, $provider);

        // O CRUD de clientes migrou para o Filament; volta para a edição do cliente no painel.
        $clientUrl = url('/app/clients/'.$client->getKey().'/edit');

        if ($result === null) {
            return redirect()->to($clientUrl)
                ->with('error', 'Erro ao analisar. Verifique suas credenciais de IA.');
        }

        return redirect()->to($clientUrl)
            ->with('ai_analysis', $result)
            ->with('success', 'Análise de IA gerada com sucesso!');
    }
}
