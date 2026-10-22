<x-app-layout>
    @section('title', 'Compra ' . $purchase->number)

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <a href="{{ route('purchases.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Compras</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">{{ $purchase->number }}</span>
        </nav>
    </x-slot>

    <div class="max-w-3xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <p class="font-mono text-xs text-surface-500 dark:text-surface-400">{{ $purchase->number }}</p>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">{{ $purchase->supplier ?? 'Compra sem fornecedor' }}</h1>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                    @if($purchase->isConfirmed())
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300">Confirmada em {{ $purchase->confirmed_at?->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Pendente de confirmação</span>
                    @endif
                </p>
            </div>
            @unless($purchase->isConfirmed())
                <form action="{{ route('purchases.confirm', $purchase) }}" method="POST" x-data>
                    @csrf @method('PATCH')
                    <button type="button"
                            @click="Swal.fire({ title: 'Confirmar compra?', text: 'O estoque de cada item será atualizado e uma conta a pagar ao fornecedor será gerada.', icon: 'question', showCancelButton: true, confirmButtonColor: '#059669', cancelButtonColor: '#64748b', confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar', customClass: { popup: 'rounded-2xl' } }).then((r) => { if (r.isConfirmed) $el.closest('form').submit() })"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-xl bg-accent-600 hover:bg-accent-700 text-white transition">
                        <i class="fa-solid fa-check-double mr-2 text-xs"></i> Confirmar entrada
                    </button>
                </form>
            @endunless
        </div>

        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 mb-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Fornecedor</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $purchase->supplier ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Nº da nota</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $purchase->document_number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Data da compra</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $purchase->purchased_at?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Valor total</dt>
                    <dd class="text-sm font-semibold text-surface-900 dark:text-surface-100">@money($purchase->total)</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Observações</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ $purchase->notes ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
            <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-800">
                <h3 class="text-sm font-semibold"><i class="fa-solid fa-list-check text-brand-500 mr-2"></i>Itens da compra</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-800">
                    <thead class="bg-surface-50 dark:bg-surface-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Produto</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Qtd.</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Custo unit.</th>
                            <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                        @foreach($purchase->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-surface-900 dark:text-white">{{ $item->product?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm text-surface-700 dark:text-surface-300">{{ rtrim(rtrim(number_format((float) $item->quantity, 3, ',', '.'), '0'), ',') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-surface-700 dark:text-surface-300">@money($item->unit_cost)</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-surface-900 dark:text-white">@money($item->total)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-surface-50 dark:bg-surface-800/40">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-sm font-semibold text-surface-700 dark:text-surface-300">Total</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-accent-700 dark:text-accent-400">@money($purchase->total)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
