<x-app-layout>
    @section('title', 'Notificações')
    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Notificações</span>
        </nav>
    </x-slot>

    @php
        $typeLabels = [
            'overdue_reminder' => ['Lembrete atrasado', 'bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-300'],
            'upcoming_reminder' => ['Em breve', 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300'],
            'stale_interaction' => ['Atendimento pendente', 'bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300'],
            'overdue_entry' => ['Financeiro vencido', 'bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-300'],
            'low_stock' => ['Estoque baixo', 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300'],
            'new_lead' => ['Novo lead', 'bg-purple-100 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300'],
            'stale_deal' => ['Oportunidade parada', 'bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300'],
        ];
    @endphp

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Notificações</h1>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Alertas e lembretes importantes</p>
            </div>
            <div class="flex items-center gap-3">
                @if($notifications->count() > 0)
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                    {{ $notifications->count() }} {{ $notifications->count() === 1 ? 'alerta' : 'alertas' }}
                </span>
                <form method="POST" action="{{ route('notifications.markAllRead') }}" id="dismiss-all-form">
                    @csrf
                    @foreach($notifications as $notification)
                        <input type="hidden" name="keys[]" value="{{ $notification['id'] }}">
                    @endforeach
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-surface-600 dark:text-surface-300 bg-surface-100 dark:bg-surface-800 hover:bg-surface-200 dark:hover:bg-surface-700 transition">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Dispensar todas</span>
                    </button>
                </form>
                @endif
            </div>
        </div>

        @if($notifications->count() > 0)
        <div class="space-y-3">
            @foreach($notifications as $notification)
            @php($meta = $typeLabels[$notification['type']] ?? [ucfirst(str_replace('_', ' ', $notification['type'])), 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300'])
            <div class="relative bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-5 hover:shadow-md hover:border-brand-200 dark:hover:border-brand-800 transition-all group">
                <div class="flex items-start space-x-4">
                    <div class="w-10 h-10 rounded-xl bg-surface-100 dark:bg-surface-800 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <i class="{{ $notification['icon'] }}"></i>
                    </div>
                    <a href="{{ $notification['url'] }}" class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-surface-900 dark:text-white">{{ $notification['title'] }}</h3>
                            <span class="text-[10px] text-surface-400 flex-shrink-0 ml-3">{{ $notification['time']->diffForHumans() }}</span>
                        </div>
                        <p class="text-xs text-surface-500 dark:text-surface-400 mt-1 truncate">{{ $notification['message'] }}</p>
                        <div class="mt-2">
                            <span class="inline-flex items-center text-[10px] font-medium px-2 py-0.5 rounded-full {{ $meta[1] }}">
                                {{ $meta[0] }}
                            </span>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('notifications.markRead', $notification['id']) }}" class="flex-shrink-0">
                        @csrf
                        <button type="submit" title="Dispensar notificação"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-surface-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 transition">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-12 text-center">
            <div class="w-16 h-16 bg-accent-50 dark:bg-accent-950/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-check-circle text-2xl text-accent-500"></i>
            </div>
            <h3 class="text-base font-semibold text-surface-700 dark:text-surface-300 mb-2">Tudo em dia!</h3>
            <p class="text-sm text-surface-500 dark:text-surface-400">Você não tem alertas ou notificações pendentes.</p>
        </div>
        @endif
    </div>
</x-app-layout>
