<x-app-layout>
    @section('title', 'Ordens de Serviço')

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Ordens de Serviço</span>
        </nav>
    </x-slot>

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Ordens de Serviço</h1>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Acompanhe a execução dos serviços pós-venda: abrir, andamento e conclusão.</p>
        </div>
        <a href="{{ route('service-orders.create') }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
            <i class="fa-solid fa-plus mr-2 text-xs"></i> Nova ordem de serviço
        </a>
    </div>

    <!-- Status counters / quick filters -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        @foreach(\App\Models\ServiceOrder::STATUSES as $st => $label)
            <a href="{{ route('service-orders.index', ['status' => $st]) }}"
               class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-4 hover:shadow-md transition {{ $status === $st ? 'ring-2 ring-brand-400' : '' }}">
                <p class="text-xs text-surface-500 dark:text-surface-400">{{ $label }}</p>
                <p class="text-lg font-bold mt-1 text-surface-900 dark:text-white">
                    {{ (int) ($totals[$st]->qty ?? 0) }}
                    <span class="text-xs font-medium text-surface-400">· @money($totals[$st]->amount ?? 0)</span>
                </p>
            </a>
        @endforeach
    </div>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 text-sm">
            <option value="">Todos os status</option>
            @foreach(\App\Models\ServiceOrder::STATUSES as $k => $label)
                <option value="{{ $k }}" @selected($status === $k)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="search" name="search" value="{{ $search }}" placeholder="Número, título ou cliente..." autocomplete="off"
               class="px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 text-sm w-full sm:w-72">
    </form>

    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-800">
                <thead class="bg-surface-50 dark:bg-surface-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Número</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Título / Cliente</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400 hidden sm:table-cell">Abertura</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Total</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-surface-500 dark:text-surface-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                    @forelse($orders as $order)
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/40 cursor-pointer" onclick="window.location='{{ route('service-orders.show', $order) }}'">
                            <td class="px-4 py-3 font-mono text-xs font-semibold">
                                <a href="{{ route('service-orders.show', $order) }}" class="text-brand-700 dark:text-brand-400">{{ $order->number }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-surface-900 dark:text-white">{{ $order->title }}</p>
                                <p class="text-xs text-surface-500">{{ $order->client?->name ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs whitespace-nowrap hidden sm:table-cell text-surface-600 dark:text-surface-300">{{ $order->opened_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right font-semibold whitespace-nowrap text-surface-900 dark:text-white">@money($order->total)</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <i class="fa-solid fa-screwdriver-wrench text-2xl text-surface-300 dark:text-surface-600 mb-3"></i>
                                <p class="text-sm text-surface-500 dark:text-surface-400">Nenhuma ordem de serviço encontrada.</p>
                                <p class="text-xs text-surface-400 dark:text-surface-500 mt-1">
                                    <i class="fa-solid fa-lightbulb text-amber-500 mr-1"></i>
                                    Dica: clique em "Nova ordem de serviço" para registrar o primeiro atendimento pós-venda.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-app-layout>
