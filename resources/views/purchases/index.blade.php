<x-app-layout>
    @section('title', 'Compras')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Compras']]" />
    </x-slot>

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 gap-4">
        <div>
            <div class="flex items-center gap-3"><h1 class="text-2xl font-bold text-surface-900 dark:text-white">Compras</h1><x-help title="Como usar esta página" description="Entradas de mercadoria de fornecedores, com estoque e contas a pagar." example="Registre a compra de 50 unidades do produto X." tip="Confirmar a compra atualiza o estoque e gera a conta a pagar." /></div>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Registre a entrada de mercadoria. Ao confirmar, o estoque é atualizado e uma conta a pagar ao fornecedor é gerada.</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
            <i class="fa-solid fa-plus mr-2 text-xs"></i> Nova compra
        </a>
    </div>

    <!-- Period summary -->
    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-4">
            <p class="text-xs text-surface-500 dark:text-surface-400">Compras registradas</p>
            <p class="text-lg font-bold mt-1 text-surface-900 dark:text-white">{{ (int) ($summary->qty ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-4">
            <p class="text-xs text-surface-500 dark:text-surface-400">Valor total</p>
            <p class="text-lg font-bold mt-1 text-surface-900 dark:text-white">@money($summary->amount ?? 0)</p>
        </div>
    </div>

    <form autocomplete="off" method="GET" class="flex flex-wrap gap-2 mb-4">
        <input type="search" name="search" value="{{ $search }}" placeholder="Número, fornecedor ou nº da nota..." autocomplete="off"
               class="px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 text-sm w-full sm:w-80">
    </form>

    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="c-table-stack min-w-full divide-y divide-surface-200 dark:divide-surface-800">
                <thead class="bg-surface-50 dark:bg-surface-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Número</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Fornecedor / Nota</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400 hidden sm:table-cell">Data</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Total</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Situação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                    @forelse($purchases as $purchase)
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/40 cursor-pointer" onclick="window.location='{{ route('purchases.show', $purchase) }}'">
                            <td data-label="Número" class="px-4 py-3 font-mono text-xs font-semibold">
                                <a href="{{ route('purchases.show', $purchase) }}" class="text-brand-700 dark:text-brand-400">{{ $purchase->number }}</a>
                            </td>
                            <td data-label="Fornecedor / Nota" class="px-4 py-3">
                                <p class="font-medium text-surface-900 dark:text-white">{{ $purchase->supplier ?? '—' }}</p>
                                <p class="text-xs text-surface-500">{{ $purchase->document_number ? 'Nota '.$purchase->document_number : $purchase->items_count.' item(ns)' }}</p>
                            </td>
                            <td data-label="Data" class="px-4 py-3 text-xs whitespace-nowrap hidden sm:table-cell text-surface-600 dark:text-surface-300">{{ $purchase->purchased_at?->format('d/m/Y') ?? '—' }}</td>
                            <td data-label="Total" class="px-4 py-3 text-right font-semibold whitespace-nowrap text-surface-900 dark:text-white">@money($purchase->total)</td>
                            <td data-label="Situação" class="px-4 py-3">
                                @if($purchase->isConfirmed())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">Confirmada</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Pendente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <i class="fa-solid fa-cart-shopping text-2xl text-surface-300 dark:text-surface-600 mb-3"></i>
                                <p class="text-sm text-surface-500 dark:text-surface-400">Nenhuma compra encontrada.</p>
                                <p class="text-xs text-surface-400 dark:text-surface-500 mt-1">
                                    <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i>
                                    Dica: clique em "Nova compra" para dar entrada de mercadoria de um fornecedor.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $purchases->links() }}</div>
</x-app-layout>
