<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Plans
    |--------------------------------------------------------------------------
    */

    'basic' => [
        'name' => 'Básico',
        'slug' => 'basic',
        'stripe_price_id' => null,
        'price' => 0,
        'original_price' => 0,
        'limits' => [
            'clients' => 10,
        ],
        'features' => [
            'Até 10 clientes',
            'Cadastro básico de clientes',
            'Busca e filtros',
            'Agenda e lembretes',
            'Tema claro/escuro',
        ],
        'description' => 'Para quem está começando e quer organizar seus primeiros clientes.',
    ],

    'professional' => [
        'name' => 'Profissional',
        'slug' => 'professional',
        'stripe_price_id' => env('STRIPE_PROFESSIONAL_PRICE_ID', ''),
        'price' => 39.90,
        'original_price' => 79.90,
        'discount_percent' => 50,
        'limits' => [
            'clients' => -1,
        ],
        'features' => [
            'Clientes ilimitados',
            'Score e classificação de clientes',
            'Análise detalhada de perfil (SWOT)',
            'Gestão de atendimentos',
            'Relatórios e métricas avançadas',
            'Notificações inteligentes',
            'Integração com IA (Gemini, Groq, OpenRouter)',
            'Exportação de dados (CSV)',
            'Suporte prioritário',
        ],
        'description' => 'Para profissionais que querem crescer com inteligência e automação.',
    ],
];
