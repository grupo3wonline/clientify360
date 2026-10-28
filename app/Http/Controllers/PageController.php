<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\PlanData;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class PageController extends Controller
{
    /**
     * The unified sales page (Home + Sobre + Serviços + Ferramentas + Contato).
     */
    public function home(): View
    {
        $plans = PlanData::catalog();

        return view('pages.home', [
            'plans' => $plans,
            'faqs' => $this->faqs($plans['professional']),
        ]);
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    /**
     * Generate a dynamic XML sitemap for public pages (SEO).
     */
    public function sitemap(): Response
    {
        $routes = [
            ['name' => 'home', 'priority' => '1.0', 'frequency' => 'weekly'],
            ['name' => 'privacy', 'priority' => '0.5', 'frequency' => 'yearly'],
        ];

        $lastModified = now()->toAtomString();

        $content = view('pages.sitemap', compact('routes', 'lastModified'))->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml');
    }

    /**
     * Serve robots.txt dynamically so the sitemap URL is absolute.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /$',
            'Allow: /politica-de-privacidade',
            '',
            '# Áreas privadas / autenticadas',
            'Disallow: /dashboard',
            'Disallow: /clients',
            'Disallow: /billing',
            'Disallow: /profile',
            'Disallow: /reminders',
            'Disallow: /interactions',
            'Disallow: /reports',
            'Disallow: /notifications',
            'Disallow: /ai',
            'Disallow: /export',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /password',
            'Disallow: /termos-de-uso',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode(PHP_EOL, $lines), 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Frequently asked questions used for the FAQ section and FAQPage
     * structured data (AEO/GEO).
     *
     * @return array<int, array{question: string, answer: string}>
     */
    private function faqs(PlanData $professional): array
    {
        return [
            [
                'question' => 'O Clientify360 é realmente gratuito?',
                'answer' => 'Sim. O plano Básico é gratuito para sempre e permite cadastrar até 10 clientes, com agenda, lembretes e tema claro/escuro. Você só faz upgrade para o plano Profissional quando quiser recursos avançados como IA e clientes ilimitados.',
            ],
            [
                'question' => 'Preciso de cartão de crédito para começar?',
                'answer' => 'Não. Você cria sua conta gratuita sem informar cartão de crédito. O cartão só é necessário caso decida assinar o plano Profissional.',
            ],
            [
                'question' => 'Como funciona a análise com Inteligência Artificial?',
                'answer' => 'No plano Profissional você conecta suas próprias credenciais de IA (Google Gemini, Groq ou OpenRouter) e a plataforma analisa automaticamente o perfil dos seus clientes, identificando pontos fortes, fracos, oportunidades e serviços recomendados.',
            ],
            [
                'question' => 'Meus dados estão seguros e em conformidade com a LGPD?',
                'answer' => 'Sim. Cada usuário acessa somente os seus próprios dados, com isolamento por conta, criptografia e sessões seguras. Estamos em conformidade com a LGPD, GDPR, CCPA e POPIA, e você pode solicitar a exclusão dos seus dados a qualquer momento.',
            ],
            [
                'question' => 'Posso cancelar quando quiser?',
                'answer' => 'Sim. O plano Profissional não tem fidelidade. Você pode cancelar a qualquer momento direto no painel, sem multas nem burocracia.',
            ],
            [
                'question' => 'Quanto custa o plano Profissional?',
                'answer' => 'O plano Profissional custa '.$professional->formattedPrice().' por mês (preço promocional; valor de referência '.$professional->formattedOriginalPrice().'), com clientes ilimitados, IA, exportação, PDFs e API.',
            ],
        ];
    }
}
