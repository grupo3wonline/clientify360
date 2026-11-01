<x-app-layout>
    @section('title', 'Nova compra')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Compras', 'url' => route('purchases.index')], ['label' => 'Nova']]" />
    </x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3"><h1 class="text-2xl font-bold text-surface-900 dark:text-white">Nova compra</h1><x-help title="Como usar esta página" description="Lance os itens comprados, quantidades e custos." example="Fornecedor ACME, 10 caixas a R$ 25,00." tip="Revise os custos: eles alimentam o valor do estoque." /></div>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Dê entrada de mercadoria de um fornecedor. Ao confirmar, o estoque é atualizado e uma conta a pagar é gerada.</p>
        </div>
        <a href="{{ route('purchases.index') }}" class="inline-flex items-center px-3 py-2 text-sm rounded-xl text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition">
            <i class="fa-solid fa-arrow-left mr-2 text-xs"></i> Voltar
        </a>
    </div>

    <form autocomplete="off" action="{{ route('purchases.store') }}" method="POST"
          x-data="purchaseEditor(@js(old('items', [])))" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        @csrf

        <div class="xl:col-span-2 space-y-6">
            <div class="c-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="c-label" for="supplier">Fornecedor</label>
                    <input id="supplier" name="supplier" maxlength="255" value="{{ old('supplier') }}" autocomplete="off" class="c-input" placeholder="Ex.: Distribuidora ACME">
                    @error('supplier') <p class="c-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="c-label" for="document_number">Nº da nota</label>
                    <input id="document_number" name="document_number" maxlength="60" value="{{ old('document_number') }}" autocomplete="off" class="c-input" placeholder="Ex.: 12345">
                    @error('document_number') <p class="c-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="c-label" for="purchased_at">Data da compra <span class="text-danger-500">*</span></label>
                    <input id="purchased_at" type="date" name="purchased_at" value="{{ old('purchased_at', now()->toDateString()) }}" autocomplete="off" required class="c-input">
                    @error('purchased_at') <p class="c-error">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="c-label" for="notes">Observações</label>
                    <textarea id="notes" name="notes" rows="2" maxlength="5000" autocomplete="off" class="c-input resize-none" placeholder="Condições, frete, prazo...">{{ old('notes') }}</textarea>
                    @error('notes') <p class="c-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Itens --}}
            <div class="c-card overflow-hidden">
                <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-800 flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold"><i class="fa-solid fa-list-check text-brand-500 mr-2"></i>Itens</h3>
                    <div class="relative w-full sm:w-80" @click.outside="results = []">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-surface-400 text-xs"></i>
                        <input type="search" x-model="term" @input.debounce.300ms="search()" @focus="search()" placeholder="Buscar produto do catálogo..." class="c-input pl-9 py-2" autocomplete="off">
                        <ul x-show="results.length" x-cloak class="absolute z-20 mt-1 w-full c-card shadow-xl max-h-72 overflow-y-auto py-1">
                            <template x-for="p in results" :key="p.id">
                                <li><button type="button" @click="addProduct(p)" class="w-full text-left px-4 py-2 hover:bg-surface-50 dark:hover:bg-surface-800 text-sm flex justify-between gap-3">
                                    <span><span x-text="p.name"></span> <span class="text-xs text-surface-400" x-text="p.sku ? '· ' + p.sku : ''"></span></span>
                                    <span class="text-xs whitespace-nowrap text-surface-400" x-text="p.unit"></span>
                                </button></li>
                            </template>
                        </ul>
                    </div>
                </div>

                @error('items') <p class="c-error px-5 pt-3">{{ $message }}</p> @enderror
                <div class="divide-y divide-surface-100 dark:divide-surface-800">
                    <template x-for="(item, i) in items" :key="item.key">
                        <div class="p-4 grid grid-cols-12 gap-3 items-end">
                            <input autocomplete="off" type="hidden" :name="`items[${i}][product_id]`" :value="item.product_id ?? ''">
                            <div class="col-span-12 md:col-span-6">
                                <label class="c-label text-xs" x-show="i === 0">Produto</label>
                                <input autocomplete="off" :value="item.name" readonly class="c-input py-2 bg-surface-50 dark:bg-surface-800/40" placeholder="Busque um produto acima">
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="c-label text-xs" x-show="i === 0">Qtd.</label>
                                <input type="number" step="0.001" min="0.001" :name="`items[${i}][quantity]`" x-model.number="item.quantity" required autocomplete="off" class="c-input py-2">
                            </div>
                            <div class="col-span-5 md:col-span-2">
                                <label class="c-label text-xs" x-show="i === 0">Custo unit.</label>
                                <input type="number" step="0.01" min="0" :name="`items[${i}][unit_cost]`" x-model.number="item.unit_cost" required autocomplete="off" class="c-input py-2">
                            </div>
                            <div class="col-span-3 md:col-span-2 flex items-center justify-end gap-2">
                                <span class="text-sm font-semibold whitespace-nowrap" x-text="brl(lineTotal(item))"></span>
                                <button type="button" @click="items.splice(i, 1)" class="p-1.5 text-surface-400 hover:text-danger-600" title="Remover item"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </template>
                    <div x-show="!items.length" class="p-8 text-center text-sm text-surface-500">Busque um produto do catálogo acima para adicionar à compra.</div>
                </div>
            </div>
        </div>

        {{-- Resumo --}}
        <aside>
            <div class="c-card p-5 xl:sticky xl:top-24 space-y-4">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between text-lg font-bold"><dt>Total</dt><dd class="text-accent-700 dark:text-accent-400" x-text="brl(total())"></dd></div>
                </dl>
                <p class="c-hint">Os totais são recalculados no servidor ao salvar. A compra fica pendente até você confirmá-la.</p>
                <button class="c-btn-primary w-full" :disabled="!items.length"><i class="fa-solid fa-floppy-disk mr-2 text-xs"></i> Salvar compra</button>
                <a href="{{ route('purchases.index') }}" class="c-btn-ghost w-full">Cancelar</a>
            </div>
        </aside>
    </form>

    @push('scripts')
    <script>
        function purchaseEditor(initialItems) {
            let seq = 0;
            const withKey = (it) => ({ key: ++seq, product_id: null, name: '', quantity: 1, unit_cost: 0, ...it, quantity: Number(it.quantity ?? 1), unit_cost: Number(it.unit_cost ?? 0) });
            return {
                items: (initialItems || []).map(withKey),
                term: '',
                results: [],
                brl: (v) => Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
                lineTotal: (it) => Math.round((Number(it.quantity) || 0) * (Number(it.unit_cost) || 0) * 100) / 100,
                total() { return this.items.reduce((s, it) => s + this.lineTotal(it), 0); },
                addProduct(p) {
                    const existing = this.items.find((it) => it.product_id === p.id);
                    if (existing) { existing.quantity = Number(existing.quantity) + 1; }
                    else { this.items.push(withKey({ product_id: p.id, name: p.name, unit_cost: p.cost ?? 0 })); }
                    this.term = ''; this.results = [];
                },
                async search() {
                    const res = await fetch(`{{ route('products.search') }}?q=${encodeURIComponent(this.term)}`, { headers: { Accept: 'application/json' } });
                    this.results = res.ok ? await res.json() : [];
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
