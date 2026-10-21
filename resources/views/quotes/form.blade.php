<x-app-layout>
    @php $editing = $quote->exists; @endphp
    @section('title', $editing ? "Editar {$quote->number}" : 'Novo orçamento')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Orçamentos', 'url' => route('quotes.index')], ['label' => $editing ? $quote->number : 'Novo']]" />
    </x-slot>

    <x-page-title :title="$editing ? 'Editar orçamento ' . $quote->number : 'Novo orçamento'" icon="fa-file-invoice-dollar" />

    <form action="{{ $editing ? route('quotes.update', $quote) : route('quotes.store') }}" method="POST"
          x-data="quoteEditor(@js(array_values($items)), @js((float) old('discount', $quote->discount ?? 0)))" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        @csrf
        @if($editing) @method('PUT') @endif

        <div class="xl:col-span-2 space-y-6">
            <div class="c-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="c-label" for="title">Título <span class="text-red-500">*</span></label>
                    <input id="title" name="title" required maxlength="255" value="{{ old('title', $quote->title) }}" class="c-input" placeholder="Ex.: Desenvolvimento de loja virtual" autocomplete="off">
                    @error('title') <p class="c-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="c-label" for="client_id">Cliente <span class="text-red-500">*</span></label>
                    <select id="client_id" name="client_id" required class="c-input" autocomplete="off">
                        <option value="">Selecione...</option>
                        @foreach($clients as $c)<option value="{{ $c->id }}" @selected(old('client_id', $quote->client_id) === $c->id)>{{ $c->name }}</option>@endforeach
                    </select>
                    @error('client_id') <p class="c-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="c-label" for="deal_id">Oportunidade (opcional)</label>
                    <select id="deal_id" name="deal_id" class="c-input" autocomplete="off">
                        <option value="">Nenhuma</option>
                        @foreach($deals as $d)<option value="{{ $d->id }}" @selected(old('deal_id', $quote->deal_id) === $d->id)>{{ $d->title }} — {{ $d->client?->name }}</option>@endforeach
                    </select>
                    <p class="c-hint">Ao aprovar, a oportunidade é marcada como ganha.</p>
                </div>
            </div>

            {{-- Itens --}}
            <div class="c-card overflow-hidden">
                <div class="px-5 py-4 border-b border-surface-100 dark:border-surface-800 flex flex-wrap items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold"><i class="fa-solid fa-list-check text-brand-500 mr-2"></i>Itens</h3>
                    <div class="relative w-full sm:w-80" @click.outside="closeResults()">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-surface-400 text-xs" aria-hidden="true"></i>
                        <input type="search" x-model="term"
                               @input.debounce.300ms="search()" @focus="search()"
                               @keydown.down.prevent="moveHighlight(1)"
                               @keydown.up.prevent="moveHighlight(-1)"
                               @keydown.enter.prevent="chooseHighlighted()"
                               @keydown.escape.prevent="closeResults()"
                               placeholder="Buscar produto/serviço do catálogo..." class="c-input pl-9 py-2" autocomplete="off"
                               role="combobox" aria-autocomplete="list" aria-controls="catalog-results"
                               :aria-expanded="results.length ? 'true' : 'false'"
                               :aria-activedescendant="highlighted >= 0 && results.length ? 'catalog-opt-' + highlighted : null"
                               aria-label="Buscar produto ou serviço do catálogo">
                        <ul x-show="results.length" x-cloak id="catalog-results" role="listbox" aria-label="Resultados do catálogo"
                            class="absolute z-20 mt-1 w-full c-card shadow-xl max-h-72 overflow-y-auto py-1">
                            <template x-for="(p, idx) in results" :key="p.id">
                                <li role="option" :id="'catalog-opt-' + idx" :aria-selected="highlighted === idx ? 'true' : 'false'">
                                    <button type="button" @click="addProduct(p)" @mouseenter="highlighted = idx"
                                            class="w-full text-left px-4 py-2 text-sm flex justify-between gap-3"
                                            :class="highlighted === idx ? 'bg-surface-50 dark:bg-surface-800' : 'hover:bg-surface-50 dark:hover:bg-surface-800'">
                                    <span><span x-text="p.name"></span> <span class="text-xs text-surface-400" x-text="p.sku ? '· ' + p.sku : ''"></span></span>
                                    <span class="text-xs whitespace-nowrap"><span x-text="brl(p.price)"></span><span x-show="p.track_stock" class="ml-1 text-surface-400" x-text="'(' + p.stock + ' ' + p.unit + ')'"></span></span>
                                </button></li>
                            </template>
                        </ul>
                    </div>
                </div>

                @error('items') <p class="c-error px-5 pt-3">{{ $message }}</p> @enderror
                <div class="divide-y divide-surface-100 dark:divide-surface-800">
                    <template x-for="(item, i) in items" :key="item.key">
                        <div class="p-4 grid grid-cols-12 gap-3 items-end">
                            <input type="hidden" :name="`items[${i}][product_id]`" :value="item.product_id ?? ''">
                            <div class="col-span-12 md:col-span-6">
                                <label class="c-label text-xs" x-show="i === 0">Descrição</label>
                                <input :name="`items[${i}][description]`" x-model="item.description" required maxlength="255" class="c-input py-2" placeholder="Descrição do item" autocomplete="off" :aria-label="'Descrição item ' + (i + 1)">
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <label class="c-label text-xs" x-show="i === 0">Qtd.</label>
                                <input type="number" step="0.001" min="0.001" :name="`items[${i}][quantity]`" x-model.number="item.quantity" required class="c-input py-2" autocomplete="off" :aria-label="'Quantidade item ' + (i + 1)">
                            </div>
                            <div class="col-span-5 md:col-span-2">
                                <label class="c-label text-xs" x-show="i === 0">Unitário</label>
                                <input type="number" step="0.01" min="0" :name="`items[${i}][unit_price]`" x-model.number="item.unit_price" required class="c-input py-2" autocomplete="off" :aria-label="'Preço unitário item ' + (i + 1)">
                            </div>
                            <div class="col-span-3 md:col-span-2 flex items-center justify-end gap-2">
                                <span class="text-sm font-semibold whitespace-nowrap" x-text="brl(lineTotal(item))"></span>
                                <button type="button" @click="items.splice(i, 1)" class="p-1.5 text-surface-400 hover:text-red-600" title="Remover item" :aria-label="'Remover item ' + (i + 1)"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </template>
                    <div x-show="!items.length" class="p-8 text-center text-sm text-surface-500">Busque um item do catálogo acima ou adicione uma linha livre.</div>
                </div>
                <div class="px-5 py-3 bg-surface-50 dark:bg-surface-800/40">
                    <button type="button" @click="addBlank()" class="text-sm font-medium text-brand-600 dark:text-brand-400 hover:underline"><i class="fa-solid fa-plus mr-1"></i>Adicionar linha livre</button>
                </div>
            </div>

            <div class="c-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="c-label" for="payment_terms">Condições de pagamento</label>
                    <textarea id="payment_terms" name="payment_terms" rows="3" maxlength="1000" class="c-input resize-none" placeholder="Ex.: 50% na aprovação e 50% na entrega. PIX ou boleto." autocomplete="off">{{ old('payment_terms', $quote->payment_terms) }}</textarea>
                </div>
                <div>
                    <label class="c-label" for="notes">Observações</label>
                    <textarea id="notes" name="notes" rows="3" maxlength="3000" class="c-input resize-none" placeholder="Prazo de entrega, garantias, o que não está incluso..." autocomplete="off">{{ old('notes', $quote->notes) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Resumo --}}
        <aside>
            <div class="c-card p-5 xl:sticky xl:top-24 space-y-4">
                <div>
                    <label class="c-label" for="valid_until">Válido até</label>
                    <input id="valid_until" type="date" name="valid_until" value="{{ old('valid_until', $quote->valid_until?->format('Y-m-d')) }}" class="c-input" autocomplete="off">
                </div>
                <dl class="space-y-2 text-sm pt-2 border-t border-surface-100 dark:border-surface-800">
                    <div class="flex justify-between"><dt class="text-surface-500">Subtotal</dt><dd x-text="brl(subtotal())"></dd></div>
                    <div class="flex justify-between items-center gap-3"><dt class="text-surface-500"><label for="discount">Desconto (R$)</label></dt>
                        <dd><input id="discount" type="number" step="0.01" min="0" name="discount" x-model.number="discount" class="c-input py-1.5 w-32 text-right" autocomplete="off"></dd></div>
                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-surface-100 dark:border-surface-800"><dt>Total</dt><dd class="text-accent-700 dark:text-accent-400" x-text="brl(total())"></dd></div>
                </dl>
                <p class="c-hint">Os totais são recalculados no servidor ao salvar.</p>
                <button class="c-btn-primary w-full" :disabled="!items.length"><i class="fa-solid fa-floppy-disk mr-2 text-xs"></i> Salvar orçamento</button>
                <a href="{{ $editing ? route('quotes.show', $quote) : route('quotes.index') }}" class="c-btn-ghost w-full">Cancelar</a>
            </div>
        </aside>
    </form>

    @push('scripts')
    <script>
        function quoteEditor(initialItems, initialDiscount) {
            let seq = 0;
            const withKey = (it) => ({ key: ++seq, product_id: null, description: '', quantity: 1, unit_price: 0, ...it, quantity: Number(it.quantity ?? 1), unit_price: Number(it.unit_price ?? 0) });
            return {
                items: initialItems.map(withKey),
                discount: initialDiscount,
                term: '',
                results: [],
                highlighted: -1,
                brl: (v) => Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }),
                lineTotal: (it) => Math.round((Number(it.quantity) || 0) * (Number(it.unit_price) || 0) * 100) / 100,
                subtotal() { return this.items.reduce((s, it) => s + this.lineTotal(it), 0); },
                total() { return Math.max(0, this.subtotal() - Math.min(Number(this.discount) || 0, this.subtotal())); },
                addBlank() { this.items.push(withKey({})); },
                addProduct(p) {
                    const existing = this.items.find((it) => it.product_id === p.id);
                    if (existing) { existing.quantity = Number(existing.quantity) + 1; }
                    else { this.items.push(withKey({ product_id: p.id, description: p.name, unit_price: p.price })); }
                    this.term = ''; this.closeResults();
                },
                closeResults() { this.results = []; this.highlighted = -1; },
                moveHighlight(dir) {
                    if (!this.results.length) { return; }
                    const count = this.results.length;
                    this.highlighted = (this.highlighted + dir + count) % count;
                },
                chooseHighlighted() {
                    if (this.highlighted >= 0 && this.results[this.highlighted]) { this.addProduct(this.results[this.highlighted]); }
                },
                async search() {
                    try {
                        const res = await fetch(`{{ route('products.search') }}?q=${encodeURIComponent(this.term)}`, { headers: { Accept: 'application/json' } });
                        this.results = res.ok ? await res.json() : [];
                    } catch (e) {
                        this.results = [];
                    }
                    this.highlighted = this.results.length ? 0 : -1;
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
