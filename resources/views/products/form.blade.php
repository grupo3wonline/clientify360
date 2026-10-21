<x-app-layout>
    @php $editing = $product->exists; @endphp
    @section('title', $editing ? 'Editar ' . $product->name : 'Novo item')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Produtos e estoque', 'url' => route('products.index')], ['label' => $editing ? $product->name : 'Novo item']]" />
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-page-title :title="$editing ? 'Editar item' : 'Novo produto ou serviço'" icon="fa-box" />

        <form action="{{ $editing ? route('products.update', $product) : route('products.store') }}" method="POST"
              x-data="{ type: @js(old('type', $product->type)), track: {{ old('track_stock', $product->track_stock) ? 'true' : 'false' }}, price: @js((float) old('price', $product->price)), cost: @js((float) old('cost', $product->cost)) }"
              class="c-card overflow-hidden">
            @csrf
            @if($editing) @method('PUT') @endif

            <div class="p-6 space-y-5">
                <div class="flex gap-2">
                    @foreach(['product' => ['Produto', 'fa-box'], 'service' => ['Serviço', 'fa-screwdriver-wrench']] as $k => [$label, $icon])
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="type" value="{{ $k }}" x-model="type" class="sr-only peer">
                            <span class="flex items-center justify-center gap-2 p-3 rounded-xl border-2 text-sm font-medium transition peer-checked:border-brand-500 peer-checked:bg-brand-50 dark:peer-checked:bg-brand-950/40 peer-checked:text-brand-700 dark:peer-checked:text-brand-300 border-surface-200 dark:border-surface-700">
                                <i class="fa-solid {{ $icon }}"></i> {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="c-label" for="name">Nome <span class="text-danger-500">*</span></label>
                        <input id="name" name="name" required maxlength="255" value="{{ old('name', $product->name) }}" class="c-input">
                        @error('name') <p class="c-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="c-label" for="sku">Código / SKU</label>
                        <input id="sku" name="sku" maxlength="60" value="{{ old('sku', $product->sku) }}" class="c-input" placeholder="Ex.: CAM-AZ-M">
                        <p class="c-hint">Código interno para localizar o item rapidamente.</p>
                    </div>
                </div>

                <div>
                    <label class="c-label" for="description">Descrição (aparece no orçamento)</label>
                    <textarea id="description" name="description" rows="2" maxlength="2000" class="c-input resize-none">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="c-label" for="unit">Unidade</label>
                        <select id="unit" name="unit" class="c-input">
                            @foreach(\App\Models\Product::UNITS as $k => $label)<option value="{{ $k }}" @selected(old('unit', $product->unit) === $k)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="c-label" for="price">Preço (R$) <span class="text-danger-500">*</span></label>
                        <input id="price" type="number" step="0.01" min="0" name="price" x-model.number="price" required class="c-input">
                        @error('price') <p class="c-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="c-label" for="promo_price">Promoção (R$)</label>
                        <input id="promo_price" type="number" step="0.01" min="0" name="promo_price" value="{{ old('promo_price', $product->promo_price) }}" class="c-input">
                        @error('promo_price') <p class="c-error">{{ $message }}</p> @enderror
                        <p class="c-hint">Quando preenchido, substitui o preço no orçamento.</p>
                    </div>
                    <div>
                        <label class="c-label" for="cost">Custo (R$)</label>
                        <input id="cost" type="number" step="0.01" min="0" name="cost" x-model.number="cost" class="c-input">
                        <p class="c-hint" x-show="!(price > 0 && cost > 0)">Usado para calcular sua margem de lucro.</p>
                        <p class="c-hint" x-show="price > 0 && cost > 0" x-cloak x-text="'Margem: ' + (((price - cost) / price) * 100).toFixed(1).replace('.', ',') + '%'"></p>
                    </div>
                </div>

                <div x-show="type === 'product'" class="rounded-xl border border-surface-200 dark:border-surface-800 p-4 space-y-4">
                    <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                        <input type="checkbox" name="track_stock" value="1" x-model="track" class="rounded border-surface-300 text-brand-600 focus:ring-brand-500">
                        Controlar estoque deste produto
                    </label>
                    <div x-show="track" x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @unless($editing)
                            <div>
                                <label class="c-label" for="stock">Saldo inicial</label>
                                <input id="stock" type="number" step="0.001" min="0" name="stock" value="{{ old('stock', 0) }}" class="c-input">
                                <p class="c-hint">Quantidade em estoque hoje. Depois, use movimentações.</p>
                            </div>
                        @endunless
                        <div>
                            <label class="c-label" for="min_stock">Estoque mínimo</label>
                            <input id="min_stock" type="number" step="0.001" min="0" name="min_stock" value="{{ old('min_stock', (float) $product->min_stock) }}" class="c-input">
                            <p class="c-hint">Abaixo disso você recebe alerta.</p>
                        </div>
                        <div>
                            <label class="c-label" for="supplier">Fornecedor</label>
                            <input id="supplier" name="supplier" maxlength="255" value="{{ old('supplier', $product->supplier) }}" class="c-input">
                        </div>
                        <div>
                            <label class="c-label" for="ncm">NCM</label>
                            <input id="ncm" name="ncm" maxlength="10" value="{{ old('ncm', $product->ncm) }}" class="c-input" placeholder="0000.00.00">
                            <p class="c-hint">Código fiscal do produto (opcional).</p>
                        </div>
                    </div>
                    @if($editing)<p x-show="track" class="c-hint"><i class="fa-solid fa-circle-info mr-1"></i>O saldo muda apenas por movimentação (entrada/saída/ajuste) na página do produto, para manter o histórico auditável.</p>@endif
                </div>

                <label class="flex items-center gap-2 text-sm cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="rounded border-surface-300 text-brand-600 focus:ring-brand-500">
                    Ativo (disponível nos orçamentos)
                </label>
            </div>

            <div class="px-6 py-4 bg-surface-50 dark:bg-surface-800/50 border-t border-surface-200 dark:border-surface-800 flex justify-between gap-3">
                <div>@if($editing)<x-confirm-delete :action="route('products.destroy', $product)" title="Excluir item?" text="Orçamentos antigos continuam mostrando o item." />@endif</div>
                <div class="flex gap-2">
                    <a href="{{ $editing ? route('products.show', $product) : route('products.index') }}" class="c-btn-ghost">Cancelar</a>
                    <button class="c-btn-primary"><i class="fa-solid fa-floppy-disk mr-2 text-xs"></i> Salvar</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
