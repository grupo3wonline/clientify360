<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    |
    | limits: -1 = unlimited. Enforced by the `plan.limit` middleware (Blade),
    | the EnforcesPlanLimit concern (Filament) and SubscriptionService::canCreate().
    | features_enabled: gated by the `plan.feature` middleware (export, pdf, api)
    | and by AiController (ai). The `features` lists are the marketing copy and
    | must only contain claims the code actually delivers.
    |
    */

    'basic' => [
        'name' => 'Básico',
        'slug' => 'basic',
        'stripe_price_id' => null,
        'price' => 0,
        'original_price' => 0,
        'limits' => [
            'clients' => 10,
            'products' => 20,
            'quotes_per_month' => 5,
            'lead_forms' => 1,
            'client_attachments' => 10,
        ],
        'features_enabled' => [
            'ai' => false,
            'export' => false,
            'pdf' => false,
            'api' => false,
        ],
        'features' => [
            'Até 10 clientes, 20 produtos/serviços e 5 orçamentos por mês',
            'Funil de vendas (Kanban), histórico e tags',
            'Agenda, lembretes e atendimentos',
            'Contas a pagar e a receber',
            '1 formulário de captação de leads',
            'Até 10 anexos de clientes',
            'Relatórios e notificações',
            'Equipe com papéis e permissões',
            'Tema claro/escuro',
        ],
        'description' => 'Para quem está começando e quer organizar seus primeiros clientes.',
    ],

    'professional' => [
        'name' => 'Profissional',
        'slug' => 'professional',
        'stripe_price_id' => env('STRIPE_PROFESSIONAL_PRICE_ID', ''),
        'price' => 29.90,
        'original_price' => 79.90,
        'limits' => [
            'clients' => -1,
            'products' => -1,
            'quotes_per_month' => -1,
            'lead_forms' => -1,
            'client_attachments' => 1000,
        ],
        'features_enabled' => [
            'ai' => true,
            'export' => true,
            'pdf' => true,
            'api' => true,
        ],
        'features' => [
            'Tudo do Básico, sem limites de clientes, produtos, orçamentos e formulários',
            'Análise de clientes com IA (Gemini, Groq, OpenRouter)',
            'Exportação de clientes em CSV e Excel',
            'Orçamentos e ordens de serviço em PDF com o seu logotipo',
            'API REST com tokens e documentação',
            'Até 1.000 anexos',
        ],
        'description' => 'Para profissionais que querem crescer com inteligência e automação.',
    ],
];
