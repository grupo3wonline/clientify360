<x-app-layout>
    @section('title', $product->name)

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Produtos e estoque', 'url' => route('products.index')], ['label' => $product->name]]" />
    </x-slot>

    <x-page-title :title="$product->name" :subtitle="$product->sku ? 'Código ' . $product->sku : null" icon="{{ $product->type === 'service' ? 'fa-screwdriver-wrench' : 'fa-box' }}">
        <x-slot name="actions">
            <a href="{{ route('products.edit', $product) }}" class="c-btn-ghost"><i class="fa-solid fa-pen mr-2 text-xs"></i> Editar</a>
        </x-slot>
    </x-page-title>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <div class="c-card p-5 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-surface-500">Preço</span><strong>@money($product->salePrice()) / {{ $product->unit }}</strong></div>
                @if($product->promo_price)<div class="flex justify-between"><span class="text-surface-500">Preço normal</span><span class="line-through">@money($product->price)</span></div>@endif
                <div class="flex justify-between"><span class="text-surface-500">Custo</span><span>{{ $product->cost !== null ? \App\Support\Format::money($product->cost) : '—' }}</span></div>
                <div class="flex justify-between"><span class="text-surface-500">Margem</span><span>{{ $product->marginPercent() !== null ? str_replace('.', ',', (string) $product->marginPercent()) . '%' : '—' }}</span></div>
                @if($product->supplier)<div class="flex justify-between"><span class="text-surface-500">Fornecedor</span><span>{{ $product->supplier }}</span></div>@endif
                @if($product->ncm)<div class="flex justify-between"><span class="text-surface-500">NCM</span><span>{{ $product->ncm }}</span></div>@endif
                @if($product->description)<p class="pt-3 border-t border-surface-100 dark:border-surface-800 text-surface-600 dark:text-surface-400">{{ $product->description }}</p>@endif
            </div>

            @if($product->track_stock)
                <div class="c-card p-5">
                    <p class="text-xs text-surface-500">Saldo em estoque</p>
                    <p class="text-3xl font-bold mt-1 {{ $product->isLowStock() ? 'text-warning-700' : 'text-surface-900 dark:text-white' }}">@qty($product->stock) <span class="text-base font-medium text-surface-400">{{ $product->unit }}</span></p>
                    <p class="text-xs text-surface-500 mt-1">Mínimo: @qty($product->min_stock) {{ $product->unit }}</p>

                    <form action="{{ route('products.movement', $product) }}" method="POST" class="mt-5 pt-5 border-t border-surface-100 dark:border-surface-800 space-y-3" x-data="{ type: 'in' }">
                        @csrf
                        <div class="grid grid-cols-3 gap-1 bg-surface-100 dark:bg-surface-800 rounded-xl p-1">
                            @foreach(\App\Models\StockMovement::TYPES as $k => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="{{ $k }}" x-model="type" class="sr-only peer">
                                    <span class="block text-center py-1.5 rounded-lg text-xs font-medium peer-checked:bg-white dark:peer-checked:bg-surface-700 peer-checked:shadow-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div>
                            <label class="c-label" x-text="type === 'adjust' ? 'Novo saldo (contagem física)' : 'Quantidade'"></label>
                            <input type="number" step="0.001" min="0" name="quantity" required class="c-input">
                            @error('quantity') <p class="c-error">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="type === 'in'" class="grid grid-cols-2 gap-3">
                            <div><label class="c-label">Custo unit.</label><input type="number" step="0.01" min="0" name="unit_cost" class="c-input" :disabled="type !== 'in'"></div>
                            <div><label class="c-label">Nº da nota</label><input name="document" maxlength="60" class="c-input" :disabled="type !== 'in'"></div>
                        </div>
                        <div><label class="c-label">Motivo</label><input name="reason" maxlength="255" class="c-input" placeholder="Opcional"></div>
                        <button class="c-btn-primary w-full">Registrar movimentação</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2 c-card overflow-hidden h-fit">
            <h3 class="px-5 py-4 text-sm font-semibold border-b border-surface-100 dark:border-surface-800"><i class="fa-solid fa-clock-rotate-left text-brand-500 mr-2"></i>Movimentações</h3>
            @if(!$product->track_stock)
                <x-empty-state icon="fa-box-open" title="Este item não controla estoque" text="Ative o controle de estoque na edição do produto." />
            @elseif($movements->isEmpty())
                <x-empty-state icon="fa-clock-rotate-left" title="Nenhuma movimentação ainda" />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-surface-100 dark:divide-surface-800">
                        <thead class="bg-surface-50 dark:bg-surface-800/50"><tr><th class="c-th">Data</th><th class="c-th">Tipo</th><th class="c-th text-right">Qtd.</th><th class="c-th text-right">Saldo</th><th class="c-th">Motivo</th></tr></thead>
                        <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                            @foreach($movements as $m)
                                <tr>
                                    <td class="c-td text-xs whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="c-td"><span class="c-badge {{ ['in' => 'bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300', 'out' => 'bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300', 'adjust' => 'bg-info-100 text-info-700 dark:bg-info-900/40 dark:text-info-300'][$m->type] }}">{{ \App\Models\StockMovement::TYPES[$m->type] }}</span></td>
                                    <td class="c-td text-right">{{ $m->type === 'out' ? '−' : '+' }}@qty($m->quantity)</td>
                                    <td class="c-td text-right font-semibold">@qty($m->balance_after)</td>
                                    <td class="c-td text-xs">{{ $m->reason }} @if($m->document)<span class="text-surface-400">· NF {{ $m->document }}</span>@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $movements->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
