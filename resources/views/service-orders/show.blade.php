<x-app-layout>
    @section('title', 'Ordem de serviço ' . $order->number)

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <a href="{{ route('service-orders.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Ordens de Serviço</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">{{ $order->number }}</span>
        </nav>
    </x-slot>

    <div class="max-w-3xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div>
                <p class="font-mono text-xs text-surface-500 dark:text-surface-400">{{ $order->number }}</p>
                <h1 class="text-2xl font-bold text-surface-900 dark:text-white">{{ $order->title }}</h1>
                <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-pdf-button :href="route('service-orders.pdf', $order)" />
                <a href="{{ route('service-orders.edit', $order) }}" class="inline-flex items-center px-3 py-2 text-sm rounded-xl text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                    <i class="fa-solid fa-pen mr-2 text-xs"></i> Editar
                </a>
                <form action="{{ route('service-orders.destroy', $order) }}" method="POST" x-data>
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="Swal.fire({ title: 'Excluir ordem de serviço?', text: 'Esta ação não pode ser desfeita.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Excluir', cancelButtonText: 'Cancelar', customClass: { popup: 'rounded-2xl' } }).then((r) => { if (r.isConfirmed) $el.closest('form').submit() })"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-xl text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-950/30 transition">
                        <i class="fa-solid fa-trash-can mr-2 text-xs"></i> Excluir
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick status actions -->
        <div class="flex flex-wrap items-center gap-2 mb-6">
            @if($order->isOpen())
                <form action="{{ route('service-orders.start', $order) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition">
                        <i class="fa-solid fa-play mr-2 text-xs"></i> Iniciar
                    </button>
                </form>
            @endif
            @if($order->isOpen() || $order->isInProgress())
                <form action="{{ route('service-orders.complete', $order) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm rounded-xl bg-accent-600 hover:bg-accent-700 text-white transition">
                        <i class="fa-solid fa-check-double mr-2 text-xs"></i> Concluir
                    </button>
                </form>
            @endif
            @if(!$order->isDone() && !$order->isCancelled())
                <form action="{{ route('service-orders.cancel', $order) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm rounded-xl border border-danger-200 dark:border-danger-800 text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-950/30 transition">
                        <i class="fa-solid fa-ban mr-2 text-xs"></i> Cancelar
                    </button>
                </form>
            @endif
            @if($order->isDone() && $order->quote_id === null && (float) $order->total > 0)
                <form action="{{ route('service-orders.billing', $order) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm rounded-xl border border-brand-200 dark:border-brand-800 text-brand-700 dark:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-950/30 transition">
                        <i class="fa-solid fa-file-invoice-dollar mr-2 text-xs"></i> Gerar cobrança
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Cliente</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $order->client?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Orçamento vinculado</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $order->quote?->number ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Valor total</dt>
                    <dd class="text-sm font-semibold text-surface-900 dark:text-surface-100">@money($order->total)</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Abertura</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $order->opened_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Previsão de entrega</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $order->expected_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Conclusão</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $order->completed_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Descrição</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ $order->description ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>
