<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\PlanData;
use App\Support\StructuredData;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class PageController extends Controller
{
    /**
     * Authenticated (or customer-facing) path prefixes kept out of crawlers.
     * The auth pages (/login, /register, ...) are deliberately NOT listed: they
     * must stay crawlable so engines can read their noindex meta tag.
     */
    private const DISALLOWED_PATHS = [
        '/app', '/api', '/dashboard', '/profile', '/equipe', '/billing',
        '/clients', '/reminders', '/interactions', '/tags', '/appointments',
        '/service-orders', '/purchases', '/reports', '/notifications', '/ai',
        '/export', '/deals', '/pipeline', '/activities', '/products', '/quotes',
        '/finance', '/lead-forms', '/f/', '/stripe',
    ];

    /**
     * Public pages in the sitemap: route name => view files whose newest
     * modification time is the honest lastmod.
     */
    private const SITEMAP = [
        'home' => ['priority' => '1.0', 'frequency' => 'weekly', 'views' => ['pages/home.blade.php', 'pages/home']],
        'privacy' => ['priority' => '0.5', 'frequency' => 'yearly', 'views' => ['pages/privacy.blade.php']],
        'terms.show' => ['priority' => '0.4', 'frequency' => 'yearly', 'views' => ['terms/show.blade.php', 'terms/_content.blade.php']],
    ];

    /**
     * The unified sales page (Home + Sobre + Serviços + Ferramentas + Contato).
     */
    public function home(): View
    {
        $plans = PlanData::catalog();
        $faqs = $this->faqs($plans['professional']);

        return view('pages.home', [
            'plans' => $plans,
            'faqs' => $faqs,
            'schema' => [
                StructuredData::organization(),
                StructuredData::website(),
                StructuredData::softwareApplication(...array_values($plans)),
                StructuredData::faqPage($faqs),
            ],
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
        $urls = [];
        foreach (self::SITEMAP as $route => $entry) {
            $urls[] = [
                'loc' => route($route),
                'lastmod' => date(DATE_ATOM, $this->lastModified($entry['views'])),
                'frequency' => $entry['frequency'],
                'priority' => $entry['priority'],
            ];
        }

        $content = view('pages.sitemap', ['urls' => $urls])->render();

        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Serve robots.txt dynamically so the sitemap URL is absolute.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            ...array_map(fn (string $path): string => 'Disallow: '.$path, self::DISALLOWED_PATHS),
            'Allow: /llms.txt',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * Plain-text summary for AI answer engines (GEO/AEO), see llmstxt.org.
     */
    public function llms(): Response
    {
        $plans = PlanData::catalog();

        $content = view('pages.llms', [
            'plans' => $plans,
            'faqs' => $this->faqs($plans['professional']),
        ])->render();

        return response(trim($content)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * Newest modification time among the given view files/directories.
     *
     * @param  list<string>  $views  paths relative to resources/views
     */
    private function lastModified(array $views): int
    {
        $times = [];
        foreach ($views as $view) {
            $path = resource_path('views/'.$view);
            $files = is_dir($path) ? (glob($path.'/*.blade.php') ?: []) : [$path];
            foreach ($files as $file) {
                $times[] = is_file($file) ? (int) filemtime($file) : 0;
            }
        }

        return max([0, ...$times]) ?: time();
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
                'question' => 'O que é o Clientify360?',
                'answer' => 'O Clientify360 é um CRM online para pequenos negócios. Ele reúne clientes, funil de vendas, orçamentos, ordens de serviço, agenda, estoque e financeiro em um só lugar, funciona no celular e no computador e tem um plano gratuito.',
            ],
            [
                'question' => 'Como meus dados ficam isolados de outras empresas?',
                'answer' => 'Cada empresa tem a sua própria conta. Todos os registros são gravados com o identificador da conta e cada consulta filtra por ela, além de permissões por papel (dono ou colaborador). Um usuário nunca vê dados de outra conta.',
            ],
            [
                'question' => 'O sistema tem API?',
                'answer' => 'Sim. O plano Profissional inclui uma API REST (versão 1) com tokens de acesso, permissões por token e documentação OpenAPI. Ela permite integrar clientes, produtos e orçamentos com sites, ERPs e planilhas.',
            ],
            [
                'question' => 'Posso gerar orçamentos em PDF?',
                'answer' => 'Sim. No plano Profissional você baixa orçamentos e ordens de serviço em PDF com o logotipo da sua empresa, prontos para enviar ao cliente.',
            ],
            [
                'question' => 'Quem desenvolve o Clientify360?',
                'answer' => 'O Clientify360 é desenvolvido pelo Grupo 3W Online, empresa que cria sites e sistemas personalizados sob demanda. O contato é pelo WhatsApp (41) 99692-4829 ou pelo e-mail contato@3wonline.com.br.',
            ],
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
