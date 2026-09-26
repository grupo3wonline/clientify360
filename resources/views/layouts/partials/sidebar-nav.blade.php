@php
    // [rota, padrão "ativo", ícone, rótulo]
    $groups = [
        'Principal' => [
            ['dashboard', 'dashboard', 'fa-gauge-high', 'Dashboard'],
            ['clients.index', 'clients.*', 'fa-users', 'Clientes'],
            ['reminders.index', 'reminders.*', 'fa-calendar-check', 'Agenda'],
            ['interactions.index', 'interactions.*', 'fa-headset', 'Atendimentos'],
        ],
        'Comercial' => [
            ['deals.index', ['deals.*', 'stages.*'], 'fa-filter-circle-dollar', 'Funil de vendas'],
            ['activities.index', 'activities.*', 'fa-timeline', 'Histórico'],
            ['quotes.index', 'quotes.*', 'fa-file-invoice-dollar', 'Orçamentos'],
            ['products.index', 'products.*', 'fa-boxes-stacked', 'Produtos e estoque'],
            ['lead-forms.index', 'lead-forms.*', 'fa-magnet', 'Captação de leads'],
            ['tags.index', 'tags.*', 'fa-tags', 'Tags'],
        ],
        'Financeiro' => [
            ['finance.index', 'finance.index', 'fa-chart-pie', 'Visão geral'],
            ['finance.entries', ['finance.entries', 'finance.categories'], 'fa-money-bill-transfer', 'Contas a pagar/receber'],
        ],
        'Análise' => [
            ['reports.index', 'reports.*', 'fa-chart-bar', 'Relatórios'],
            ['notifications.index', 'notifications.*', 'fa-bell', 'Notificações'],
            ['ai.settings', 'ai.*', 'fa-wand-magic-sparkles', 'IA / Análise'],
        ],
        'Conta' => [
            ['profile.edit', 'profile.*', 'fa-user-gear', 'Meu Perfil'],
            ['billing.index', 'billing.*', 'fa-credit-card', 'Assinatura'],
        ],
    ];
@endphp

<nav class="flex-1 px-4 py-5 space-y-5 overflow-y-auto scrollbar-thin" aria-label="Menu principal">
    @foreach($groups as $groupLabel => $items)
        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">{{ $groupLabel }}</p>
            <div class="space-y-0.5">
                @foreach($items as [$route, $pattern, $icon, $label])
                    @php $active = request()->routeIs(...(array) $pattern); @endphp
                    <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
                       class="flex items-center px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200 {{ $active ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 shadow-sm' : 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800 hover:text-surface-900 dark:hover:text-surface-200' }}">
                        <i class="fa-solid {{ $icon }} w-5 mr-3 text-center {{ $active ? 'text-brand-500' : '' }}"></i>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</nav>
