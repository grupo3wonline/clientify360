<x-app-layout>
    @section('title', 'Orçamentos')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Orçamentos']]" />
    </x-slot>

    <x-page-title title="Orçamentos" icon="fa-file-invoice-dollar" subtitle="Aprovar um orçamento confirma a venda: baixa o estoque e gera as contas a receber.">
        <x-slot name="actions">
            <a href="{{ route('quotes.create') }}" class="c-btn-primary"><i class="fa-solid fa-plus mr-2 text-xs"></i> Novo orçamento</a>
        </x-slot>
    </x-page-title>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        @foreach(['draft', 'sent', 'approved', 'rejected'] as $st)
            <a href="{{ route('quotes.index', ['status' => $st]) }}" class="c-card p-4 hover:shadow-md transition {{ $status === $st ? 'ring-2 ring-brand-400' : '' }}">
                <p class="text-xs text-surface-500">{{ \App\Models\Quote::STATUS_LABELS[$st] }}</p>
                <p class="text-lg font-bold mt-1">{{ (int) ($totals[$st]->qty ?? 0) }} <span class="text-xs font-medium text-surface-400">· @money($totals[$st]->amount ?? 0)</span></p>
            </a>
        @endforeach
    </div>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <select name="status" class="c-input w-auto" onchange="this.form.submit()">
            <option value="">Todos os status</option>
            @foreach(\App\Models\Quote::STATUS_LABELS as $k => $label)<option value="{{ $k }}" @selected($status === $k)>{{ $label }}</option>@endforeach
        </select>
        <input type="search" name="search" value="{{ $search }}" placeholder="Número, título ou cliente..." class="c-input w-full sm:w-72">
    </form>

    <div class="c-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-800">
                <thead class="bg-surface-50 dark:bg-surface-800/50"><tr><th class="c-th">Número</th><th class="c-th">Título / Cliente</th><th class="c-th hidden sm:table-cell">Validade</th><th class="c-th text-right">Total</th><th class="c-th">Status</th></tr></thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                    @forelse($quotes as $q)
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/40 cursor-pointer" onclick="window.location='{{ route('quotes.show', $q) }}'">
                            <td class="c-td font-mono text-xs font-semibold"><a href="{{ route('quotes.show', $q) }}" class="text-brand-700 dark:text-brand-400">{{ $q->number }}</a></td>
                            <td class="c-td"><p class="font-medium text-surface-900 dark:text-white">{{ $q->title }}</p><p class="text-xs text-surface-500">{{ $q->client?->name }}</p></td>
                            <td class="c-td text-xs whitespace-nowrap hidden sm:table-cell">{{ $q->valid_until?->format('d/m/Y') ?? '—' }}</td>
                            <td class="c-td text-right font-semibold whitespace-nowrap">@money($q->total)</td>
                            <td class="c-td"><span class="c-badge {{ $q->statusColor() }}">{{ $q->statusLabel() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <x-empty-state icon="fa-file-invoice-dollar" title="Nenhum orçamento encontrado" text="Monte orçamentos com seus produtos e serviços e envie ao cliente em PDF.">
                                <a href="{{ route('quotes.create') }}" class="c-btn-primary">Criar orçamento</a>
                            </x-empty-state>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $quotes->links() }}</div>
</x-app-layout>
