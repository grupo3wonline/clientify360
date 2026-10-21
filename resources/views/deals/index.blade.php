<x-app-layout>
    @section('title', 'Funil de vendas')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Funil de vendas']]" />
    </x-slot>

    <x-page-title title="Funil de vendas" icon="fa-filter-circle-dollar" subtitle="Arraste os cards entre as etapas. No celular, use o seletor de etapa do card.">
        <x-slot name="actions">
            <a href="{{ route('deals.list') }}" class="c-btn-ghost"><i class="fa-solid fa-list mr-2 text-xs"></i> Lista</a>
            <a href="{{ route('stages.index') }}" class="c-btn-ghost"><i class="fa-solid fa-sliders mr-2 text-xs"></i> Etapas</a>
            <button type="button" x-data @click="$dispatch('open-modal', 'new-deal')" class="c-btn-primary"><i class="fa-solid fa-plus mr-2 text-xs"></i> Nova oportunidade</button>
        </x-slot>
    </x-page-title>

    {{-- Métricas --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        @foreach([
            ['Em aberto', $metrics['open_count'], 'fa-folder-open', 'text-brand-600'],
            ['Valor no funil', \App\Support\Format::money($metrics['open_value']), 'fa-sack-dollar', 'text-brand-600'],
            ['Previsão ponderada', \App\Support\Format::money($metrics['weighted_value']), 'fa-scale-balanced', 'text-amber-600', 'Soma de valor × probabilidade de cada etapa'],
            ['Ganhas no mês', $metrics['won_month_count'] . ' · ' . \App\Support\Format::money($metrics['won_month_value']), 'fa-trophy', 'text-accent-600'],
            ['Conversão (90 dias)', $metrics['conversion'] !== null ? str_replace('.', ',', (string) $metrics['conversion']) . '%' : '—', 'fa-bullseye', 'text-purple-600', 'Ganhas ÷ (ganhas + perdidas)'],
        ] as $m)
            <div class="c-card p-4" @isset($m[4]) title="{{ $m[4] }}" @endisset>
                <p class="text-xs text-surface-500 dark:text-surface-400 flex items-center gap-1.5"><i class="fa-solid {{ $m[2] }} {{ $m[3] }}"></i> {{ $m[0] }}</p>
                <p class="text-lg font-bold text-surface-900 dark:text-white mt-1 truncate">{{ $m[1] }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="mb-4 max-w-sm">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-surface-400 text-xs"></i>
            <input type="search" name="search" value="{{ $search }}" placeholder="Buscar por título ou cliente..." class="c-input pl-9">
        </div>
    </form>

    {{-- Kanban --}}
    <div x-data="kanban()" class="flex gap-4 overflow-x-auto pb-4 -mx-4 px-4 sm:mx-0 sm:px-0 snap-x">
        @foreach($stages as $stage)
            @php $stageDeals = $deals->get($stage->id, collect()); @endphp
            <section class="flex-shrink-0 w-72 snap-start"
                     @dragover.prevent="over = '{{ $stage->id }}'" @dragleave="over = null"
                     @drop.prevent="drop('{{ $stage->id }}')">
                <header class="flex items-center justify-between mb-3 px-1">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background: {{ $stage->color }}"></span>
                        <h2 class="text-sm font-semibold text-surface-800 dark:text-surface-200 truncate">{{ $stage->name }}</h2>
                        <span class="text-xs text-surface-400">{{ $stageDeals->count() }}</span>
                    </div>
                    <span class="text-[11px] text-surface-400" title="Probabilidade de fechamento">{{ $stage->probability }}%</span>
                </header>
                <p class="text-xs font-medium text-surface-500 dark:text-surface-400 px-1 mb-2">@money($stageDeals->sum('value'))</p>

                <div class="space-y-2.5 min-h-[120px] rounded-2xl p-2 transition"
                     :class="over === '{{ $stage->id }}' ? 'bg-brand-50 dark:bg-brand-950/40 ring-2 ring-brand-300 dark:ring-brand-700' : 'bg-surface-100/70 dark:bg-surface-900/60'">
                    @forelse($stageDeals as $deal)
                        <article draggable="true" @dragstart="dragging = '{{ $deal->id }}'" @dragend="dragging = null; over = null"
                                 class="c-card p-3.5 cursor-grab active:cursor-grabbing hover:shadow-md transition-shadow {{ $deal->isOverdue() ? 'border-l-4 !border-l-danger-400' : '' }}">
                            <a href="{{ route('deals.show', $deal) }}" class="block">
                                <h3 class="text-sm font-semibold text-surface-900 dark:text-white line-clamp-2">{{ $deal->title }}</h3>
                                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1 truncate"><i class="fa-solid fa-user mr-1"></i>{{ $deal->client?->name }}</p>
                            </a>
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-sm font-bold text-accent-700 dark:text-accent-400">@money($deal->value)</span>
                                @if($deal->expected_close_date)
                                    <span class="text-[11px] {{ $deal->isOverdue() ? 'text-danger-600 dark:text-danger-400 font-semibold' : 'text-surface-400' }}" title="Previsão de fechamento">
                                        <i class="fa-regular fa-calendar mr-0.5"></i>{{ $deal->expected_close_date->format('d/m') }}
                                    </span>
                                @endif
                            </div>
                            {{-- Alternativa ao arrastar (touch/teclado) --}}
                            <form action="{{ route('deals.move', $deal) }}" method="POST" class="mt-2.5">
                                @csrf @method('PATCH')
                                <label class="sr-only" for="stage-{{ $deal->id }}">Mover para etapa</label>
                                <select id="stage-{{ $deal->id }}" name="pipeline_stage_id" onchange="this.form.submit()"
                                        class="w-full text-xs py-1 px-2 rounded-lg border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800 text-surface-600 dark:text-surface-300">
                                    @foreach($stages as $s)
                                        <option value="{{ $s->id }}" @selected($s->id === $stage->id)>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </article>
                    @empty
                        <p class="text-xs text-center text-surface-400 py-6">Solte um card aqui</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    {{-- Modal: nova oportunidade --}}
    <x-modal name="new-deal" :show="$errors->any()" maxWidth="lg">
        <form action="{{ route('deals.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Nova oportunidade</h2>
            @include('deals._form', ['deal' => null])
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" x-on:click="$dispatch('close')" class="c-btn-ghost">Cancelar</button>
                <button class="c-btn-primary"><i class="fa-solid fa-check mr-2 text-xs"></i> Criar</button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
    <script>
        function kanban() {
            return {
                dragging: null,
                over: null,
                async drop(stageId) {
                    const id = this.dragging;
                    this.over = null;
                    if (!id) return;
                    try {
                        const res = await fetch(`{{ url('deals') }}/${id}/move`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            },
                            body: JSON.stringify({ pipeline_stage_id: stageId }),
                        });
                        if (!res.ok) throw new Error();
                        window.location.reload();
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Não foi possível mover', text: 'Tente novamente.', customClass: { popup: 'rounded-2xl' } });
                    }
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
