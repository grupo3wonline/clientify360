<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\Http;

final class AiAnalysisService
{
    private const GEMINI_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    private const GROQ_URL = 'https://api.groq.com/openai/v1/chat/completions';

    private const OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';

    public function analyzeClient(User $user, Client $client, string $provider): ?string
    {
        $prompt = $this->buildClientAnalysisPrompt($client);

        return match ($provider) {
            'gemini' => $this->callGemini($user->ai_gemini_key, $prompt),
            'groq' => $this->callGroq($user->ai_groq_key, $prompt),
            'openrouter' => $this->callOpenRouter($user->ai_openrouter_key, $prompt),
            default => null,
        };
    }

    public function getAvailableProviders(User $user): array
    {
        $providers = [];

        if (!empty($user->ai_gemini_key)) {
            $providers[] = ['id' => 'gemini', 'name' => 'Google Gemini 2.0 Flash', 'icon' => 'fa-brands fa-google'];
        }
        if (!empty($user->ai_groq_key)) {
            $providers[] = ['id' => 'groq', 'name' => 'Groq Llama 3.3 70B', 'icon' => 'fa-solid fa-bolt'];
        }
        if (!empty($user->ai_openrouter_key)) {
            $providers[] = ['id' => 'openrouter', 'name' => 'OpenRouter Gemma 3n', 'icon' => 'fa-solid fa-route'];
        }

        return $providers;
    }

    private function buildClientAnalysisPrompt(Client $client): string
    {
        $data = "Nome: {$client->name}\n";
        $data .= $client->email ? "Email: {$client->email}\n" : '';
        $data .= $client->phone ? "Telefone: {$client->phone}\n" : '';
        $data .= $client->business_segment ? "Segmento: {$client->business_segment}\n" : '';
        $data .= $client->company_size ? "Porte: {$client->company_size}\n" : '';
        $data .= $client->city ? "Cidade: {$client->city}/{$client->state}\n" : '';
        $data .= $client->notes ? "Observações: {$client->notes}\n" : '';
        $data .= "Score atual: {$client->score}/100\n";
        $data .= $client->score_label ? "Classificação: {$client->score_label}\n" : '';

        return "Você é um analista de negócios. Analise o perfil do cliente abaixo e forneça em português (pt-BR):

1. PONTOS FORTES: Liste 3-5 pontos fortes do cliente
2. PONTOS FRACOS: Liste 3-5 pontos fracos ou riscos
3. OPORTUNIDADES: Liste 3-5 oportunidades de negócio
4. SERVIÇOS RECOMENDADOS: Indique quais serviços são relevantes (Website, Sistema, Marketing Digital, E-commerce, SEO, Redes Sociais)
5. SCORE SUGERIDO: Sugira um score de 0-100 e classificação (Quente/Morno/Frio/VIP/Novo)

Dados do cliente:
{$data}

Responda de forma objetiva e profissional, focando em oportunidades reais de venda de serviços de tecnologia (sites, sistemas e marketing digital).";
    }

    private function callGemini(string $apiKey, string $prompt): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->post(self::GEMINI_URL.'?key='.$apiKey, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function callGroq(string $apiKey, string $prompt): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders(['Authorization' => 'Bearer '.$apiKey])
                ->post(self::GROQ_URL, [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'max_tokens' => 1500,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function callOpenRouter(string $apiKey, string $prompt): ?string
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(30)
                ->withHeaders(['Authorization' => 'Bearer '.$apiKey])
                ->post(self::OPENROUTER_URL, [
                    'model' => 'google/gemma-3n-e2b-it:free',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'max_tokens' => 1500,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
