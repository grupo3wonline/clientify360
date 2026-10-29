{{-- Visão comercial do cliente: oportunidades, orçamentos, financeiro e histórico. --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="space-y-6">
        {{-- Resumo financeiro --}}
        <div class="c-card p-5">
            <h3 class="text-sm font-semibold mb-4"><i class="fa-solid fa-wallet text-brand-500 mr-2"></i>Financeiro do cliente</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-surface-500">Já recebido</dt><dd class="font-semibold text-accent-700 dark:text-accent-400">@money($finance['received'])</dd></div>
                <div class="flex justify-between"><dt class="text-surface-500">Em aberto</dt><dd class="font-semibold">@money($finance['open'])</dd></div>
                <div class="flex justify-between"><dt class="text-surface-500">Em atraso</dt><dd class="font-semibold {{ $finance['overdue'] > 0 ? 'text-danger-600' : '' }}">@money($finance['overdue'])</dd></div>
            </dl>
        </div>

        {{-- Oportunidades --}}
        <div class="c-card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold"><i class="fa-solid fa-handshake text-brand-500 mr-2"></i>Oportunidades</h3>
                <button type="button" x-data @click="$dispatch('open-modal', 'client-deal')" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline"><i class="fa-solid fa-plus mr-1"></i>Nova</button>
            </div>
            @forelse($client->deals as $deal)
                <a href="{{ route('deals.show', $deal) }}" class="block py-2 border-b last:border-0 border-surface-100 dark:border-surface-800 hover:bg-surface-50 dark:hover:bg-surface-800/40 -mx-2 px-2 rounded-lg">
                    <div class="flex justify-between gap-2"><span class="text-sm font-medium truncate">{{ $deal->title }}</span><span class="text-sm font-semibold whitespace-nowrap">@money($deal->value)</span></div>
                    <div class="flex items-center gap-2 mt-1">@include('deals._status')@if($deal->status === 'open' && $deal->stage)<span class="text-xs text-surface-500 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full" style="background: {{ $deal->stage->color }}"></span>{{ $deal->stage->name }}</span>@endif</div>
                </a>
            @empty
                <p class="text-sm text-surface-500">Nenhuma oportunidade.</p>
            @endforelse
        </div>

        {{-- Orçamentos --}}
        <div class="c-card p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold"><i class="fa-solid fa-file-invoice-dollar text-brand-500 mr-2"></i>Orçamentos</h3>
                <a href="{{ route('quotes.create', ['client_id' => $client->id]) }}" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline"><i class="fa-solid fa-plus mr-1"></i>Novo</a>
            </div>
            @forelse($client->quotes as $q)
                <a href="{{ route('quotes.show', $q) }}" class="flex items-center justify-between gap-2 py-2 border-b last:border-0 border-surface-100 dark:border-surface-800 hover:bg-surface-50 dark:hover:bg-surface-800/40 -mx-2 px-2 rounded-lg">
                    <span class="text-sm min-w-0"><span class="font-mono text-xs font-semibold">{{ $q->number }}</span> <span class="text-surface-500 truncate">{{ $q->title }}</span></span>
                    <span class="c-badge {{ $q->statusColor() }}">{{ $q->statusLabel() }}</span>
                </a>
            @empty
                <p class="text-sm text-surface-500">Nenhum orçamento.</p>
            @endforelse
        </div>
    </div>

    <div class="lg:col-span-2">
        @include('activities._timeline', ['activities' => $client->activities, 'client' => $client])
    </div>
</div>

<x-modal name="client-deal" maxWidth="lg">
    <form autocomplete="off" action="{{ route('deals.store') }}" method="POST" class="p-6 space-y-4">
        @csrf
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white">Nova oportunidade para {{ $client->name }}</h2>
        @include('deals._form', ['deal' => null, 'fixedClient' => $client])
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" x-on:click="$dispatch('close')" class="c-btn-ghost">Cancelar</button>
            <button class="c-btn-primary">Criar</button>
        </div>
    </form>
</x-modal>
