@php
    // [rota, padrão "ativo", ícone, rótulo, (opcional) permissão ACL, (opcional) descrição/tooltip]
    $groups = [
        'Principal' => [
            ['dashboard', 'dashboard', 'fa-gauge-high', 'Dashboard'],
            ['clients.index', 'clients.*', 'fa-users', 'Clientes', 'clients.view'],
            ['reminders.index', 'reminders.*', 'fa-calendar-check', 'Agenda', 'clients.view'],
            ['appointments.index', 'appointments.*', 'fa-calendar-days', 'Agendamentos', 'appointments.view'],
            ['interactions.index', 'interactions.*', 'fa-headset', 'Atendimentos', 'clients.view'],
        ],
        'Comercial' => [
            ['deals.index', ['deals.*', 'stages.*'], 'fa-filter-circle-dollar', 'Funil de vendas', 'deals.view'],
            ['activities.index', 'activities.*', 'fa-timeline', 'Histórico', 'deals.view'],
            ['quotes.index', 'quotes.*', 'fa-file-invoice-dollar', 'Orçamentos', 'quotes.view'],
            ['service-orders.index', 'service-orders.*', 'fa-screwdriver-wrench', 'Ordens de Serviço', 'service_orders.view', 'Gerencie ordens de serviço: status, execução e faturamento'],
            ['products.index', 'products.*', 'fa-boxes-stacked', 'Produtos e estoque', 'products.view'],
            ['purchases.index', 'purchases.*', 'fa-cart-shopping', 'Compras', 'purchases.view', 'Entrada de mercadoria e contas a pagar ao fornecedor'],
            ['lead-forms.index', 'lead-forms.*', 'fa-magnet', 'Captação de leads', 'leads.view'],
            ['tags.index', 'tags.*', 'fa-tags', 'Tags', 'clients.view'],
        ],
        'Financeiro' => [
            ['finance.index', 'finance.index', 'fa-chart-pie', 'Visão geral', 'finance.view', 'Resumo do mês e fluxo de caixa'],
            ['finance.entries', ['finance.entries', 'finance.categories'], 'fa-money-bill-transfer', 'Contas a pagar/receber', 'finance.view', 'Lançamentos a pagar e a receber'],
            ['billing.index', 'billing.index', 'fa-credit-card', 'Minha assinatura', 'account.billing', 'Plano atual, vigência, renovação e troca de plano'],
            ['billing.invoices', 'billing.invoices', 'fa-receipt', 'Histórico de pagamentos', 'account.billing', 'Faturas da assinatura com PDF e recibo'],
        ],
        'Análise' => [
            ['reports.index', 'reports.*', 'fa-chart-bar', 'Relatórios', 'reports.view'],
            ['notifications.index', 'notifications.*', 'fa-bell', 'Notificações'],
            ['ai.settings', 'ai.*', 'fa-wand-magic-sparkles', 'IA / Análise', 'clients.view'],
        ],
        'Conta' => [
            ['profile.edit', 'profile.*', 'fa-user-gear', 'Meu perfil', null, 'Edite seus dados pessoais, e-mail e senha'],
            ['team.index', 'team.*', 'fa-users-gear', 'Equipe', 'account.members', 'Convide membros e defina suas permissões de acesso'],
        ],
    ];
@endphp

<nav class="flex-1 px-4 py-5 space-y-5 overflow-y-auto scrollbar-thin" aria-label="Menu principal">
    @foreach($groups as $groupLabel => $items)
        @php($items = array_filter($items, fn (array $item): bool => ($item[4] ?? null) === null || (auth()->user()?->can($item[4]) ?? false)))
        @continue($items === [])
        <div>
            <p class="px-3 mb-2 text-[11px] font-semibold text-surface-400 dark:text-surface-500 uppercase tracking-wider">{{ $groupLabel }}</p>
            <div class="space-y-0.5">
                @foreach($items as $item)
                    @php([$route, $pattern, $icon, $label] = $item)
                    @php($description = $item[5] ?? null)
                    @php($active = request()->routeIs(...(array) $pattern))
                    <a href="{{ route($route) }}" @if($active) aria-current="page" @endif @if($description) title="{{ $description }}" @endif
                       class="flex items-center px-3 py-2 rounded-xl text-sm font-medium transition-all duration-200 {{ $active ? 'bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300 shadow-sm' : 'text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800 hover:text-surface-900 dark:hover:text-surface-200' }}">
                        <i class="fa-solid {{ $icon }} w-5 mr-3 text-center {{ $active ? 'text-brand-500' : '' }}" aria-hidden="true"></i>
                        <span class="flex-1">{{ $label }}</span>
                        @if($route === 'notifications.index' && ($notificationCount ?? 0) > 0)
                            <span class="ml-2 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[10px] font-bold bg-danger-500 text-white"
                                  aria-label="{{ $notificationCount }} notificações pendentes">
                                {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</nav>
