<x-app-layout>
    @section('title', 'Relatórios')
    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Relatórios</span>
        </nav>
    </x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Relatórios</h1>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Visão geral do seu negócio</p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.index', ['period' => '7']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $period == '7' ? 'bg-brand-600 text-white' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400 hover:bg-surface-200' }} transition">7 dias</a>
            <a href="{{ route('reports.index', ['period' => '30']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $period == '30' ? 'bg-brand-600 text-white' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400 hover:bg-surface-200' }} transition">30 dias</a>
            <a href="{{ route('reports.index', ['period' => '90']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $period == '90' ? 'bg-brand-600 text-white' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400 hover:bg-surface-200' }} transition">90 dias</a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-5">
            <div class="flex items-center justify-between mb-2">
                <i class="fa-solid fa-users text-brand-500"></i>
                <span class="text-[10px] font-medium text-accent-600 dark:text-accent-400 bg-accent-50 dark:bg-accent-950/50 px-1.5 py-0.5 rounded">+{{ $stats['new_clients'] }}</span>
            </div>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">{{ $stats['total_clients'] }}</p>
            <p class="text-[10px] text-surface-500 uppercase tracking-wider mt-1">Clientes</p>
        </div>
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-5">
            <div class="flex items-center justify-between mb-2">
                <i class="fa-solid fa-headset text-purple-500"></i>
                <span class="text-[10px] font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/50 px-1.5 py-0.5 rounded">{{ $stats['open_interactions'] }} abertos</span>
            </div>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">{{ $stats['total_interactions'] }}</p>
            <p class="text-[10px] text-surface-500 uppercase tracking-wider mt-1">Atendimentos</p>
        </div>
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-5">
            <div class="flex items-center justify-between mb-2">
                <i class="fa-solid fa-check-circle text-accent-500"></i>
            </div>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">{{ $stats['resolved_interactions'] }}</p>
            <p class="text-[10px] text-surface-500 uppercase tracking-wider mt-1">Resolvidos</p>
        </div>
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-5">
            <div class="flex items-center justify-between mb-2">
                <i class="fa-solid fa-bell text-amber-500"></i>
                @if($stats['overdue_reminders'] > 0)
                <span class="text-[10px] font-medium text-red-600 bg-red-50 px-1.5 py-0.5 rounded">{{ $stats['overdue_reminders'] }} atrasados</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-surface-900 dark:text-white">{{ $stats['pending_reminders'] }}</p>
            <p class="text-[10px] text-surface-500 uppercase tracking-wider mt-1">Lembretes Pendentes</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Score Distribution -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                <i class="fa-solid fa-chart-pie text-brand-500 mr-2"></i> Classificação de Clientes
            </h3>
            @if(count($scoreDistribution) > 0)
            <div class="space-y-3">
                @foreach($scoreDistribution as $label => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm">{{ $label === 'Quente' ? '🔥' : ($label === 'VIP' ? '⭐' : ($label === 'Morno' ? '🌤️' : ($label === 'Frio' ? '❄️' : '🆕'))) }}</span>
                        <span class="text-sm text-surface-700 dark:text-surface-300">{{ $label }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-24 h-2 bg-surface-100 dark:bg-surface-800 rounded-full overflow-hidden">
                            <div class="h-full bg-brand-500 rounded-full" style="width: {{ $stats['total_clients'] > 0 ? ($count / $stats['total_clients']) * 100 : 0 }}%"></div>
                        </div>
                        <span class="text-sm font-semibold text-surface-900 dark:text-white w-8 text-right">{{ $count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-surface-400 italic">Nenhum cliente classificado ainda.</p>
            @endif
        </div>

        <!-- Services Demand -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                <i class="fa-solid fa-wand-magic-sparkles text-purple-500 mr-2"></i> Demanda de Serviços
            </h3>
            <div class="space-y-3">
                @php $services = ['website' => ['🌐', 'Website'], 'system' => ['💻', 'Sistema'], 'marketing' => ['📢', 'Marketing'], 'ecommerce' => ['🛒', 'E-commerce'], 'seo' => ['🔍', 'SEO'], 'social_media' => ['#️⃣', 'Redes Sociais']]; @endphp
                @foreach($services as $key => [$emoji, $name])
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm">{{ $emoji }}</span>
                        <span class="text-sm text-surface-700 dark:text-surface-300">{{ $name }}</span>
                    </div>
                    <span class="text-sm font-semibold text-surface-900 dark:text-white px-2 py-0.5 bg-surface-100 dark:bg-surface-800 rounded">{{ $servicesDemand[$key] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Clients -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                <i class="fa-solid fa-ranking-star text-amber-500 mr-2"></i> Top Clientes (por interações)
            </h3>
            @if($topClients->count() > 0)
            <div class="space-y-3">
                @foreach($topClients as $i => $client)
                <div class="flex items-center justify-between p-3 bg-surface-50 dark:bg-surface-800 rounded-xl">
                    <div class="flex items-center space-x-3">
                        <span class="w-6 h-6 rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 flex items-center justify-center text-xs font-bold">{{ $i + 1 }}</span>
                        <span class="text-sm font-medium text-surface-800 dark:text-surface-200">{{ $client->name }}</span>
                    </div>
                    <div class="flex items-center space-x-3 text-xs text-surface-500">
                        <span><i class="fa-solid fa-headset mr-1"></i>{{ $client->interactions_count }}</span>
                        <span><i class="fa-solid fa-bell mr-1"></i>{{ $client->reminders_count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-surface-400 italic">Nenhuma interação registrada no período.</p>
            @endif
        </div>

        <!-- Interactions by Channel -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                <i class="fa-solid fa-tower-broadcast text-teal-500 mr-2"></i> Canais de Atendimento
            </h3>
            @if(count($interactionsByChannel) > 0)
            <div class="space-y-3">
                @php $channels = ['phone' => ['📞', 'Telefone'], 'email' => ['📧', 'E-mail'], 'whatsapp' => ['💬', 'WhatsApp'], 'meeting' => ['🤝', 'Reunião'], 'other' => ['💭', 'Outro']]; @endphp
                @foreach($interactionsByChannel as $channel => $count)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-sm">{{ $channels[$channel][0] ?? '💭' }}</span>
                        <span class="text-sm text-surface-700 dark:text-surface-300">{{ $channels[$channel][1] ?? $channel }}</span>
                    </div>
                    <span class="text-sm font-semibold text-surface-900 dark:text-white">{{ $count }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-surface-400 italic">Nenhum atendimento no período.</p>
            @endif
        </div>
    </div>
</x-app-layout>
