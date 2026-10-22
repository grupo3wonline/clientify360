<x-app-layout>
    @section('title', 'Produtos e estoque')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Produtos e estoque']]" />
    </x-slot>

    <x-page-title title="Produtos e serviços" icon="fa-boxes-stacked" subtitle="Catálogo usado nos orçamentos, com controle de estoque opcional por item.">
        <x-slot name="actions">
            <a href="{{ route('products.create') }}" class="c-btn-primary"><i class="fa-solid fa-plus mr-2 text-xs"></i> Novo item</a>
        </x-slot>
    </x-page-title>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="c-card p-4"><p class="text-xs text-surface-500">Itens ativos</p><p class="text-xl font-bold mt-1">{{ $stats['total'] }}</p></div>
        <a href="{{ route('products.index', ['filter' => 'low_stock']) }}" class="c-card p-4 hover:shadow-md transition {{ $stats['low_stock'] ? 'border-warning-300 dark:border-warning-700' : '' }}">
            <p class="text-xs text-surface-500"><i class="fa-solid fa-triangle-exclamation text-warning-500 mr-1"></i>Estoque baixo</p>
            <p class="text-xl font-bold mt-1 {{ $stats['low_stock'] ? 'text-warning-700' : '' }}">{{ $stats['low_stock'] }}</p>
        </a>
        <div class="c-card p-4" title="Saldo × custo dos itens com controle de estoque"><p class="text-xs text-surface-500">Valor em estoque (custo)</p><p class="text-xl font-bold mt-1">@money($stats['stock_value'])</p></div>
    </div>

    <div class="flex flex-col md:flex-row md:items-center gap-3 mb-4">
        <div class="flex items-center gap-1 bg-surface-100 dark:bg-surface-800 rounded-xl p-1 overflow-x-auto">
            @foreach([null => 'Ativos', 'product' => 'Produtos', 'service' => 'Serviços', 'low_stock' => 'Estoque baixo', 'inactive' => 'Inativos'] as $k => $label)
                <a href="{{ route('products.index', array_filter(['filter' => $k, 'search' => $search])) }}" class="c-tab {{ $filter == $k ? 'c-tab-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <form method="GET" class="md:ml-auto md:w-72">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="search" name="search" value="{{ $search }}" placeholder="Nome, código ou descrição..." class="c-input">
        </form>
    </div>

    <div class="c-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-800">
                <thead class="bg-surface-50 dark:bg-surface-800/50">
                    <tr><th class="c-th">Item</th><th class="c-th hidden sm:table-cell">Código</th><th class="c-th text-right">Preço</th><th class="c-th text-right hidden md:table-cell">Margem</th><th class="c-th text-right">Estoque</th><th class="c-th"></th></tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                    @forelse($products as $p)
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/40">
                            <td class="c-td">
                                <a href="{{ route('products.show', $p) }}" class="font-medium text-surface-900 dark:text-white hover:text-brand-600">{{ $p->name }}</a>
                                <span class="c-badge ml-1 {{ $p->type === 'service' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : 'bg-surface-100 text-surface-600 dark:bg-surface-800 dark:text-surface-300' }}">{{ $p->type === 'service' ? 'Serviço' : 'Produto' }}</span>
                            </td>
                            <td class="c-td text-xs text-surface-500 hidden sm:table-cell">{{ $p->sku ?: '—' }}</td>
                            <td class="c-td text-right whitespace-nowrap">
                                @if($p->promo_price)<span class="text-xs line-through text-surface-400 mr-1">@money($p->price)</span>@endif
                                <strong>@money($p->salePrice())</strong><span class="text-xs text-surface-400">/{{ $p->unit }}</span>
                            </td>
                            <td class="c-td text-right text-xs hidden md:table-cell">{{ $p->marginPercent() !== null ? str_replace('.', ',', (string) $p->marginPercent()) . '%' : '—' }}</td>
                            <td class="c-td text-right whitespace-nowrap">
                                @if($p->track_stock)
                                    <span class="{{ $p->isLowStock() ? 'text-warning-700 font-semibold' : '' }}">@qty($p->stock) {{ $p->unit }}</span>
                                    @if($p->isLowStock())<i class="fa-solid fa-triangle-exclamation text-warning-500 ml-1" title="Abaixo do mínimo (@qty($p->min_stock))"></i>@endif
                                @else
                                    <span class="text-xs text-surface-400">não controla</span>
                                @endif
                            </td>
                            <td class="c-td text-right"><a href="{{ route('products.edit', $p) }}" class="p-2 text-surface-400 hover:text-brand-600" title="Editar"><i class="fa-solid fa-pen text-sm"></i></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <x-empty-state icon="fa-boxes-stacked" title="Nenhum item encontrado" text="Cadastre produtos e serviços para montar orçamentos em segundos.">
                                <a href="{{ route('products.create') }}" class="c-btn-primary">Cadastrar primeiro item</a>
                            </x-empty-state>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
</x-app-layout>
