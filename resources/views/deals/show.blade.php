<x-app-layout>
    @section('title', $deal->title)

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Funil de vendas', 'url' => route('deals.index')], ['label' => $deal->title]]" />
    </x-slot>

    <x-page-title :title="$deal->title" icon="fa-handshake">
        <x-slot name="actions">
            @if($deal->status === 'open')
                <form action="{{ route('deals.win', $deal) }}" method="POST">@csrf<button class="c-btn-success"><i class="fa-solid fa-trophy mr-2 text-xs"></i> Ganhou</button></form>
                <button type="button" x-data @click="$dispatch('open-modal', 'lose-deal')" class="c-btn-ghost text-danger-600"><i class="fa-solid fa-thumbs-down mr-2 text-xs"></i> Perdeu</button>
                <a href="{{ route('quotes.create', ['deal_id' => $deal->id]) }}" class="c-btn-primary"><i class="fa-solid fa-file-invoice-dollar mr-2 text-xs"></i> Criar orçamento</a>
            @else
                <form action="{{ route('deals.reopen', $deal) }}" method="POST">@csrf<button class="c-btn-ghost"><i class="fa-solid fa-rotate-left mr-2 text-xs"></i> Reabrir</button></form>
            @endif
            <x-confirm-delete :action="route('deals.destroy', $deal)" title="Excluir oportunidade?" />
        </x-slot>
    </x-page-title>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <div class="c-card p-5 space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-surface-500">Status</span>@include('deals._status')</div>
                <div class="flex justify-between"><span class="text-surface-500">Valor</span><strong class="text-accent-700 dark:text-accent-400">@money($deal->value)</strong></div>
                <div class="flex justify-between"><span class="text-surface-500">Probabilidade</span><span>{{ $deal->probability }}%</span></div>
                <div class="flex justify-between"><span class="text-surface-500">Cliente</span><a href="{{ route('clients.show', $deal->client_id) }}" class="text-brand-600 dark:text-brand-400 hover:underline">{{ $deal->client?->name }}</a></div>
                @if($deal->expected_close_date)<div class="flex justify-between"><span class="text-surface-500">Previsão</span><span class="{{ $deal->isOverdue() ? 'text-danger-600 font-semibold' : '' }}">{{ $deal->expected_close_date->format('d/m/Y') }}</span></div>@endif
                @if($deal->source)<div class="flex justify-between"><span class="text-surface-500">Origem</span><span>{{ \App\Models\Client::SOURCES[$deal->source] ?? $deal->source }}</span></div>@endif
                @if($deal->lost_reason)<div class="pt-2 border-t border-surface-100 dark:border-surface-800"><span class="text-surface-500 block mb-1">Motivo da perda</span>{{ $deal->lost_reason }}</div>@endif
                @if($deal->status === 'open')
                    <form action="{{ route('deals.move', $deal) }}" method="POST" class="pt-3 border-t border-surface-100 dark:border-surface-800">
                        @csrf @method('PATCH')
                        <label class="c-label" for="move-stage">Etapa</label>
                        <select id="move-stage" name="pipeline_stage_id" onchange="this.form.submit()" class="c-input">
                            @foreach($stages as $s)<option value="{{ $s->id }}" @selected($s->id === $deal->pipeline_stage_id)>{{ $s->name }} ({{ $s->probability }}%)</option>@endforeach
                        </select>
                    </form>
                @endif
            </div>

            <details class="c-card p-5 group" @if($errors->has('title') || $errors->has('value')) open @endif>
                <summary class="cursor-pointer text-sm font-semibold text-surface-800 dark:text-surface-200 list-none flex justify-between items-center">
                    <span><i class="fa-solid fa-pen-to-square text-brand-500 mr-2"></i> Editar dados</span>
                    <i class="fa-solid fa-chevron-down text-xs transition group-open:rotate-180"></i>
                </summary>
                <form action="{{ route('deals.update', $deal) }}" method="POST" class="space-y-4 mt-4">
                    @csrf @method('PUT')
                    <input type="hidden" name="probability" value="{{ $deal->probability }}">
                    @include('deals._form', ['deal' => $deal])
                    <button class="c-btn-primary w-full">Salvar</button>
                </form>
            </details>

            @if($deal->description)
                <div class="c-card p-5"><h3 class="text-sm font-semibold mb-2">Descrição</h3><p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-line">{{ $deal->description }}</p></div>
            @endif
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="c-card p-5">
                <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-3"><i class="fa-solid fa-file-invoice-dollar text-brand-500 mr-2"></i>Orçamentos</h3>
                @forelse($deal->quotes as $q)
                    <a href="{{ route('quotes.show', $q) }}" class="flex items-center justify-between py-2 border-b last:border-0 border-surface-100 dark:border-surface-800 hover:bg-surface-50 dark:hover:bg-surface-800/40 -mx-2 px-2 rounded-lg">
                        <span class="text-sm"><strong>{{ $q->number }}</strong> · {{ $q->title }}</span>
                        <span class="flex items-center gap-2"><span class="text-sm font-semibold">@money($q->total)</span><span class="c-badge {{ $q->statusColor() }}">{{ $q->statusLabel() }}</span></span>
                    </a>
                @empty
                    <p class="text-sm text-surface-500">Nenhum orçamento vinculado.</p>
                @endforelse
            </div>

            @include('activities._timeline', ['activities' => $deal->activities, 'client' => $deal->client, 'deal' => $deal])
        </div>
    </div>

    <x-modal name="lose-deal" :show="$errors->has('lost_reason')" maxWidth="md">
        <form action="{{ route('deals.lose', $deal) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Por que perdemos?</h2>
            <div>
                <select name="lost_reason" class="c-input" required x-data x-on:change="if ($el.value === '__other') { $el.name = ''; $refs.other.name = 'lost_reason'; $refs.other.classList.remove('hidden'); $refs.other.focus() }">
                    <option value="">Selecione...</option>
                    @foreach(['Preço', 'Escolheu concorrente', 'Sem orçamento no momento', 'Sem resposta / sumiu', 'Não era o perfil', 'Adiou o projeto'] as $r)<option>{{ $r }}</option>@endforeach
                    <option value="__other">Outro...</option>
                </select>
                <input x-ref="other" class="c-input mt-2 hidden" maxlength="255" placeholder="Descreva o motivo">
                @error('lost_reason') <p class="c-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" x-on:click="$dispatch('close')" class="c-btn-ghost">Cancelar</button>
                <button class="c-btn-danger">Marcar como perdida</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
