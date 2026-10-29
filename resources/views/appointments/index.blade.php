<x-app-layout>
    @section('title', 'Agendamentos')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Agendamentos']]" />
    </x-slot>

    @php
        $months = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        $weekdays = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

        $prev = $reference->copy()->subMonthNoOverflow();
        $next = $reference->copy()->addMonthNoOverflow();

        // Monta a grade do calendário iniciando no domingo da primeira semana.
        $gridStart = $reference->copy()->startOfMonth()->startOfWeek(\Illuminate\Support\Carbon::SUNDAY);
        $gridEnd = $reference->copy()->endOfMonth()->endOfWeek(\Illuminate\Support\Carbon::SATURDAY);
        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $days[] = $d->copy();
        }

        $statusDot = [
            'pending' => '#f59e0b',
            'confirmed' => '#6366f1',
            'done' => '#10b981',
            'cancelled' => '#ef4444',
        ];
    @endphp

    <!-- Page Header + counters -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 gap-4">
        <div>
            <div class="flex items-center gap-3"><h1 class="text-2xl font-bold text-surface-900 dark:text-white">Agendamentos</h1><x-help title="Como usar esta página" description="Organize seus compromissos com clientes em lista ou calendário." example="Agende uma visita técnica para amanhã às 14h e confirme com o cliente." tip="Marque como concluído depois do atendimento para manter a agenda limpa." /></div>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Calendário de reuniões, visitas, ligações e videochamadas.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-3 text-xs">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-brand-50 dark:bg-brand-950/40 text-brand-700 dark:text-brand-300 font-medium">Hoje: {{ $counters['today'] }}</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-accent-50 dark:bg-accent-950/40 text-accent-700 dark:text-accent-300 font-medium">Semana: {{ $counters['week'] }}</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 font-medium">Pendentes: {{ $counters['pending'] }}</span>
            </div>
            <a href="{{ route('appointments.create') }}" class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                <i class="fa-solid fa-plus mr-2 text-xs"></i> Novo agendamento
            </a>
        </div>
    </div>

    <!-- Calendar -->
    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
        <!-- Month navigation -->
        <div class="flex items-center justify-between px-5 py-4 border-b border-surface-200 dark:border-surface-800">
            <a href="{{ route('appointments.index', ['month' => $prev->month, 'year' => $prev->year]) }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-800 transition" title="Mês anterior">
                <i class="fa-solid fa-chevron-left text-sm"></i>
            </a>
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold text-surface-900 dark:text-white">{{ $months[$reference->month - 1] }} {{ $reference->year }}</h2>
                <a href="{{ route('appointments.index') }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Hoje</a>
            </div>
            <a href="{{ route('appointments.index', ['month' => $next->month, 'year' => $next->year]) }}"
               class="inline-flex items-center justify-center w-9 h-9 rounded-xl text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-800 transition" title="Próximo mês">
                <i class="fa-solid fa-chevron-right text-sm"></i>
            </a>
        </div>

        <!-- Weekday header -->
        <div class="grid grid-cols-7 border-b border-surface-200 dark:border-surface-800">
            @foreach($weekdays as $wd)
                <div class="px-2 py-2 text-center text-[11px] font-semibold uppercase tracking-wider text-surface-400 dark:text-surface-500">{{ $wd }}</div>
            @endforeach
        </div>

        <!-- Day cells -->
        <div class="grid grid-cols-7">
            @foreach($days as $day)
                @php
                    $key = $day->format('Y-m-d');
                    $dayAppointments = $appointments[$key] ?? collect();
                    $inMonth = $day->month === $reference->month;
                    $isToday = $day->isToday();
                @endphp
                <div class="min-h-[7rem] border-b border-r border-surface-100 dark:border-surface-800 p-1.5 {{ $inMonth ? 'bg-white dark:bg-surface-900' : 'bg-surface-50/60 dark:bg-surface-950/40' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 text-xs rounded-full {{ $isToday ? 'bg-brand-600 text-white font-semibold' : ($inMonth ? 'text-surface-600 dark:text-surface-300' : 'text-surface-300 dark:text-surface-600') }}">{{ $day->day }}</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($dayAppointments as $appointment)
                            <a href="{{ route('appointments.show', $appointment) }}"
                               title="{{ $appointment->starts_at->format('H:i') }} — {{ $appointment->title }} ({{ $appointment->statusLabel() }})"
                               class="flex items-center gap-1 px-1.5 py-1 rounded-lg text-[11px] bg-surface-50 dark:bg-surface-800 hover:bg-surface-100 dark:hover:bg-surface-700 transition {{ $appointment->status === 'cancelled' ? 'opacity-50 line-through' : '' }}">
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background-color: {{ $appointment->color ?: ($statusDot[$appointment->status] ?? '#6366f1') }}"></span>
                                <span class="font-medium text-surface-500 dark:text-surface-400">{{ $appointment->starts_at->format('H:i') }}</span>
                                <span class="truncate text-surface-700 dark:text-surface-200">{{ $appointment->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap items-center gap-4 mt-4 text-xs text-surface-500 dark:text-surface-400">
        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background:#f59e0b"></span> Pendente</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background:#6366f1"></span> Confirmado</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background:#10b981"></span> Concluído</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background:#ef4444"></span> Cancelado</span>
    </div>
</x-app-layout>
