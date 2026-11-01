<x-app-layout>
    @section('title', 'Financeiro')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Financeiro']]" />
    </x-slot>

    <x-page-title help-description="Resumo financeiro do mês e fluxo de caixa." help-example="Veja quanto entra e sai nos próximos dias." help-tip="Lance as contas recorrentes no início do mês." title="Financeiro" icon="fa-chart-pie" :subtitle="'Resumo de ' . ucfirst(now()->translatedFormat('F \d\e Y'))">
        <x-slot name="actions">
            <a href="{{ route('finance.entries', ['direction' => 'payable']) }}" class="c-btn-ghost"><i class="fa-solid fa-arrow-up mr-2 text-xs text-danger-500"></i> A pagar</a>
            <a href="{{ route('finance.entries', ['direction' => 'receivable']) }}" class="c-btn-primary"><i class="fa-solid fa-arrow-down mr-2 text-xs"></i> A receber</a>
        </x-slot>
    </x-page-title>

    @if($summary['overdue_count'] > 0)
        <a href="{{ route('finance.entries', ['status' => 'overdue']) }}" class="flex items-center gap-3 p-4 mb-6 rounded-2xl bg-danger-50 dark:bg-danger-950/30 border border-danger-200 dark:border-danger-900 text-sm text-danger-800 dark:text-danger-300 hover:shadow-md transition">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <span><strong>{{ $summary['overdue_count'] }} lançamento(s) em atraso</strong> — @money($summary['overdue_receivable']) a receber e @money($summary['overdue_payable']) a pagar.</span>
            <i class="fa-solid fa-chevron-right ml-auto text-xs"></i>
        </a>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="c-card p-4"><p class="text-xs text-surface-500"><i class="fa-solid fa-arrow-down text-accent-500 mr-1"></i>Recebido no mês</p><p class="text-xl font-bold text-accent-700 dark:text-accent-400 mt-1">@money($summary['received'])</p><p class="text-[11px] text-surface-400 mt-1">+ @money($summary['to_receive']) previsto</p></div>
        <div class="c-card p-4"><p class="text-xs text-surface-500"><i class="fa-solid fa-arrow-up text-danger-500 mr-1"></i>Pago no mês</p><p class="text-xl font-bold text-danger-600 dark:text-danger-400 mt-1">@money($summary['paid'])</p><p class="text-[11px] text-surface-400 mt-1">+ @money($summary['to_pay']) previsto</p></div>
        <div class="c-card p-4"><p class="text-xs text-surface-500"><i class="fa-solid fa-scale-balanced text-brand-500 mr-1"></i>Saldo realizado</p><p class="text-xl font-bold mt-1 {{ $summary['balance'] < 0 ? 'text-danger-600' : 'text-surface-900 dark:text-white' }}">@money($summary['balance'])</p></div>
        <div class="c-card p-4" title="Saldo realizado + previsto a receber − previsto a pagar"><p class="text-xs text-surface-500"><i class="fa-solid fa-binoculars text-purple-500 mr-1"></i>Projeção do mês</p>
            @php $proj = $summary['balance'] + $summary['to_receive'] - $summary['to_pay']; @endphp
            <p class="text-xl font-bold mt-1 {{ $proj < 0 ? 'text-danger-600' : 'text-surface-900 dark:text-white' }}">@money($proj)</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Fluxo de caixa --}}
        <div class="lg:col-span-2 c-card p-5">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
                <h3 class="text-sm font-semibold"><i class="fa-solid fa-chart-column text-brand-500 mr-2"></i>Fluxo de caixa</h3>
                <div class="flex items-center gap-3 text-[11px] text-surface-500">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-accent-500"></span>Entradas</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-danger-400"></span>Saídas</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm border-2 border-dashed border-surface-400"></span>Previsto</span>
                </div>
            </div>
            @php
                $max = max(1, collect($cashFlow)->map(fn ($m) => max($m['in'] + $m['forecast_in'], $m['out'] + $m['forecast_out']))->max());
            @endphp
            <div class="flex items-end gap-2 sm:gap-4 h-56" role="img" aria-label="Gráfico de entradas e saídas por mês">
                @foreach($cashFlow as $m)
                    <div class="flex-1 flex flex-col items-center h-full min-w-0">
                        <div class="flex-1 w-full flex items-end justify-center gap-1">
                            @foreach([['in', 'forecast_in', 'bg-accent-500', 'border-accent-400'], ['out', 'forecast_out', 'bg-danger-400', 'border-danger-300']] as [$real, $fc, $bg, $bd])
                                <div class="w-1/2 max-w-[22px] flex flex-col justify-end h-full" title="{{ $m['label'] }} — realizado @money($m[$real]) · previsto @money($m[$fc])">
                                    @if($m[$fc] > 0)<div class="w-full border-2 border-dashed {{ $bd }} rounded-t" style="height: {{ $m[$fc] / $max * 100 }}%"></div>@endif
                                    @if($m[$real] > 0)<div class="w-full {{ $bg }} {{ $m[$fc] > 0 ? '' : 'rounded-t' }}" style="height: {{ $m[$real] / $max * 100 }}%"></div>@endif
                                </div>
                            @endforeach
                        </div>
                        <span class="text-[10px] sm:text-xs text-surface-500 mt-2 truncate">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Despesas por categoria --}}
        <div class="c-card p-5">
            <h3 class="text-sm font-semibold mb-4"><i class="fa-solid fa-chart-pie text-brand-500 mr-2"></i>Despesas pagas no mês</h3>
            @php $totalExp = max(0.01, $byCategory->sum('total')); @endphp
            @forelse($byCategory as $name => $row)
                <div class="mb-3">
                    <div class="flex justify-between text-xs mb-1"><span class="truncate">{{ $name }}</span><span class="font-semibold ml-2">@money($row['total'])</span></div>
                    <div class="h-2 rounded-full bg-surface-100 dark:bg-surface-800 overflow-hidden"><div class="h-full rounded-full" style="width: {{ $row['total'] / $totalExp * 100 }}%; background: {{ $row['color'] }}"></div></div>
                </div>
            @empty
                <p class="text-sm text-surface-500">Nenhuma despesa paga neste mês.</p>
            @endforelse
            <a href="{{ route('finance.categories') }}" class="block mt-4 text-xs text-brand-600 dark:text-brand-400 hover:underline">Gerenciar categorias →</a>
        </div>
    </div>

    {{-- Próximos vencimentos --}}
    <div class="c-card mt-6 overflow-hidden">
        <h3 class="px-5 py-4 text-sm font-semibold border-b border-surface-100 dark:border-surface-800"><i class="fa-solid fa-calendar-day text-brand-500 mr-2"></i>Vencidos e próximos 7 dias</h3>
        @forelse($upcoming as $e)
            <div class="px-5 py-3 flex items-center gap-3 border-b last:border-0 border-surface-100 dark:border-surface-800">
                <i class="fa-solid {{ $e->direction === 'receivable' ? 'fa-arrow-down text-accent-500' : 'fa-arrow-up text-danger-500' }}"></i>
                <div class="flex-1 min-w-0"><p class="text-sm font-medium truncate">{{ $e->description }}</p><p class="text-xs text-surface-500">{{ $e->client?->name ?? $e->counterparty }} · vence {{ $e->due_date->format('d/m') }}</p></div>
                <span class="text-sm font-semibold whitespace-nowrap">@money($e->amount)</span>
                <span class="c-badge {{ $e->statusColor() }}">{{ $e->statusLabel() }}</span>
            </div>
        @empty
            <x-empty-state icon="fa-mug-hot" title="Nada vencendo nos próximos dias" />
        @endforelse
    </div>
</x-app-layout>
