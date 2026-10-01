<x-app-layout>
    @section('title', 'Dashboard')

    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <h2 class="text-lg font-semibold text-surface-800 dark:text-surface-200">Dashboard</h2>
        </div>
    </x-slot>

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-800 dark:to-surface-900 rounded-2xl p-6 md:p-8 mb-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-10 -right-10 w-64 h-64 bg-white rounded-full blur-3xl"></div>
            <div class="absolute -bottom-10 -left-10 w-48 h-48 bg-accent-400 rounded-full blur-3xl"></div>
        </div>
        <div class="relative z-10">
            <h3 class="text-xl md:text-2xl font-bold mb-2">
                Olá, {{ Auth::user()->name }}! 👋
            </h3>
            <p class="text-brand-200 text-sm md:text-base">
                Gerencie seus clientes de forma simples e eficiente. Utilize o menu para navegar entre as funcionalidades.
            </p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
        <!-- Total Clients -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 hover:shadow-lg hover:shadow-surface-200/50 dark:hover:shadow-none transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-users text-brand-600 dark:text-brand-400 text-lg"></i>
                </div>
                <a href="{{ route('clients.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">Ver todos →</a>
            </div>
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Total de Clientes</p>
            <div class="flex items-baseline space-x-2">
                <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ $clientCount }}</p>
                @if($clientLimit > 0)
                    <span class="text-sm text-surface-400">/ {{ $clientLimit }}</span>
                @endif
            </div>
            @if($clientLimit > 0)
                <div class="mt-4">
                    <div class="w-full bg-surface-100 dark:bg-surface-800 rounded-full h-2">
                        <div class="bg-gradient-to-r from-brand-500 to-brand-600 h-2 rounded-full transition-all duration-500" style="width: {{ min(($clientCount / $clientLimit) * 100, 100) }}%"></div>
                    </div>
                    <p class="text-xs text-surface-400 mt-2">
                        {{ max($clientLimit - $clientCount, 0) }} vagas restantes
                    </p>
                </div>
            @endif
        </div>

        <!-- Current Plan -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 hover:shadow-lg hover:shadow-surface-200/50 dark:hover:shadow-none transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/50 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-crown text-amber-600 dark:text-amber-400 text-lg"></i>
                </div>
                @can('account.billing')
                    <a href="{{ route('billing.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline font-medium">Gerenciar →</a>
                @endcan
            </div>
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400 mb-1">Plano Atual</p>
            <p class="text-3xl font-bold text-surface-900 dark:text-white">{{ $currentPlan['name'] }}</p>
            <p class="text-xs text-surface-400 mt-2">
                @if(($currentPlan['price'] ?? 0) > 0)
                    R$ {{ number_format($currentPlan['price'], 2, ',', '.') }} / mês
                @else
                    Gratuito
                @endif
            </p>
        </div>

        <!-- Quick Action -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 hover:shadow-lg hover:shadow-surface-200/50 dark:hover:shadow-none transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-accent-50 dark:bg-accent-950/50 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-bolt text-accent-600 dark:text-accent-400 text-lg"></i>
                </div>
            </div>
            <p class="text-sm font-medium text-surface-500 dark:text-surface-400 mb-3">Ação Rápida</p>
            <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                <i class="fa-solid fa-user-plus mr-2 text-xs"></i> Novo Cliente
            </a>
        </div>
    </div>

    <!-- Resumo comercial e financeiro (módulos CRM/Vendas/Financeiro) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('deals.index') }}" class="c-card p-5 hover:shadow-lg transition-shadow">
            <p class="text-xs font-medium text-surface-500 dark:text-surface-400"><i class="fa-solid fa-filter-circle-dollar text-brand-500 mr-1.5"></i>Funil de vendas</p>
            <p class="text-xl font-bold text-surface-900 dark:text-white mt-2">@money($business['pipeline_value'])</p>
            <p class="text-xs text-surface-400 mt-1">{{ $business['open_deals'] }} em aberto · previsão @money($business['pipeline_weighted'])</p>
        </a>
        <a href="{{ route('finance.index') }}" class="c-card p-5 hover:shadow-lg transition-shadow">
            <p class="text-xs font-medium text-surface-500 dark:text-surface-400"><i class="fa-solid fa-arrow-down text-accent-500 mr-1.5"></i>Recebido no mês</p>
            <p class="text-xl font-bold text-accent-700 dark:text-accent-400 mt-2">@money($business['received_month'])</p>
            <p class="text-xs mt-1 {{ $business['overdue_count'] ? 'text-red-600 font-semibold' : 'text-surface-400' }}">
                @if($business['overdue_count']){{ $business['overdue_count'] }} lançamento(s) em atraso @else + @money($business['to_receive_month']) a receber @endif
            </p>
        </a>
        <a href="{{ route('quotes.index', ['status' => 'sent']) }}" class="c-card p-5 hover:shadow-lg transition-shadow">
            <p class="text-xs font-medium text-surface-500 dark:text-surface-400"><i class="fa-solid fa-hourglass-half text-amber-500 mr-1.5"></i>Orçamentos aguardando</p>
            <p class="text-xl font-bold text-surface-900 dark:text-white mt-2">{{ $business['quotes_waiting'] }}</p>
            <p class="text-xs text-surface-400 mt-1">@money($business['quotes_waiting_value']) em negociação</p>
        </a>
        <a href="{{ route('lead-forms.index') }}" class="c-card p-5 hover:shadow-lg transition-shadow">
            <p class="text-xs font-medium text-surface-500 dark:text-surface-400"><i class="fa-solid fa-magnet text-purple-500 mr-1.5"></i>Leads (7 dias)</p>
            <p class="text-xl font-bold text-surface-900 dark:text-white mt-2">{{ $business['new_leads_week'] }}</p>
            <p class="text-xs mt-1 {{ $business['low_stock'] ? 'text-amber-600 font-semibold' : 'text-surface-400' }}">
                @if($business['low_stock'])<i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $business['low_stock'] }} produto(s) com estoque baixo @else {{ $business['reminders_today'] }} lembrete(s) hoje @endif
            </p>
        </a>
    </div>

    <!-- Upgrade Banner (basic plan only, apenas quem gerencia cobrança) -->
    @if(($currentPlan['slug'] ?? '') === 'basic' && auth()->user()->can('account.billing'))
        <div class="bg-gradient-to-r from-brand-50 to-accent-50 dark:from-brand-950/30 dark:to-accent-950/30 rounded-2xl border border-brand-200 dark:border-brand-800/50 p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-start space-x-4">
                    <div class="w-10 h-10 bg-brand-100 dark:bg-brand-900/50 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fa-solid fa-rocket text-brand-600 dark:text-brand-400"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-surface-800 dark:text-surface-200 mb-1">Desbloqueie todo o potencial</h4>
                        <p class="text-xs text-surface-500 dark:text-surface-400">
                            Upgrade para Profissional: clientes, orçamentos, produtos e formulários ilimitados, IA, exportação e suporte prioritário.
                        </p>
                    </div>
                </div>
                <a href="{{ route('billing.index') }}" class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm whitespace-nowrap">
                    <i class="fa-solid fa-arrow-up mr-2 text-xs"></i> Upgrade — @money($professionalPrice)/mês
                </a>
            </div>
        </div>
    @endif

    <!-- Quick Actions + Tips -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Quick Actions -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <h4 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                <i class="fa-solid fa-bolt text-amber-500 mr-2"></i> Ações Rápidas
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('clients.create') }}" class="flex items-center px-4 py-3 rounded-xl bg-surface-50 dark:bg-surface-800 hover:bg-brand-50 dark:hover:bg-brand-950/30 border border-surface-200 dark:border-surface-700 hover:border-brand-200 dark:hover:border-brand-800 transition group">
                    <i class="fa-solid fa-user-plus text-brand-500 mr-3 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Novo Cliente</span>
                </a>
                <a href="{{ route('clients.index') }}" class="flex items-center px-4 py-3 rounded-xl bg-surface-50 dark:bg-surface-800 hover:bg-accent-50 dark:hover:bg-accent-950/30 border border-surface-200 dark:border-surface-700 hover:border-accent-200 dark:hover:border-accent-800 transition group">
                    <i class="fa-solid fa-list text-accent-500 mr-3 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Ver Clientes</span>
                </a>
                <a href="{{ route('quotes.create') }}" class="flex items-center px-4 py-3 rounded-xl bg-surface-50 dark:bg-surface-800 hover:bg-amber-50 dark:hover:bg-amber-950/30 border border-surface-200 dark:border-surface-700 hover:border-amber-200 dark:hover:border-amber-800 transition group">
                    <i class="fa-solid fa-file-invoice-dollar text-amber-500 mr-3 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Novo Orçamento</span>
                </a>
                <a href="{{ route('deals.index') }}" class="flex items-center px-4 py-3 rounded-xl bg-surface-50 dark:bg-surface-800 hover:bg-purple-50 dark:hover:bg-purple-950/30 border border-surface-200 dark:border-surface-700 hover:border-purple-200 dark:hover:border-purple-800 transition group">
                    <i class="fa-solid fa-filter-circle-dollar text-purple-500 mr-3 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-medium text-surface-700 dark:text-surface-300">Funil de Vendas</span>
                </a>
            </div>
        </div>

        <!-- Tips -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <h4 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                <i class="fa-solid fa-lightbulb text-amber-500 mr-2"></i> Dicas de Uso
            </h4>
            <ul class="space-y-3">
                <li class="flex items-start space-x-3">
                    <i class="fa-solid fa-check-circle text-accent-500 mt-0.5 text-sm"></i>
                    <span class="text-sm text-surface-600 dark:text-surface-400">Cadastre clientes com informações completas para facilitar o contato.</span>
                </li>
                <li class="flex items-start space-x-3">
                    <i class="fa-solid fa-check-circle text-accent-500 mt-0.5 text-sm"></i>
                    <span class="text-sm text-surface-600 dark:text-surface-400">Use o campo de observações para registrar detalhes importantes.</span>
                </li>
                <li class="flex items-start space-x-3">
                    <i class="fa-solid fa-check-circle text-accent-500 mt-0.5 text-sm"></i>
                    <span class="text-sm text-surface-600 dark:text-surface-400">Mantenha os dados sempre atualizados para comunicação eficiente.</span>
                </li>
                <li class="flex items-start space-x-3">
                    <i class="fa-solid fa-check-circle text-accent-500 mt-0.5 text-sm"></i>
                    <span class="text-sm text-surface-600 dark:text-surface-400">Seus dados são privados — somente você tem acesso.</span>
                </li>
            </ul>
        </div>
    </div>
</x-app-layout>
