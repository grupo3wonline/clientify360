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
            'products' => 20,
            'quotes_per_month' => 5,
            'lead_forms' => 1,
        ],
        'features' => [
            'Até 10 clientes',
            'Cadastro básico de clientes',
            'Busca, filtros e tags',
            'Funil de vendas (Kanban) e histórico',
            'Até 20 produtos/serviços',
            'Até 5 orçamentos por mês',
            'Contas a pagar e a receber',
            '1 formulário de captação de leads',
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
            'products' => -1,
            'quotes_per_month' => -1,
            'lead_forms' => -1,
        ],
        'features' => [
            'Clientes ilimitados',
            'Produtos, orçamentos e formulários ilimitados',
            'Controle de estoque com alerta de mínimo',
            'Fluxo de caixa e parcelamento automático',
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
