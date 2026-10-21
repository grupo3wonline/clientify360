<x-app-layout>
    @section('title', 'Histórico')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Histórico']]" />
    </x-slot>

    <x-page-title title="Histórico de relacionamento" icon="fa-timeline" subtitle="Todas as interações com seus clientes, em ordem cronológica." />

    <div class="flex flex-col lg:flex-row gap-6">
        <div class="flex-1 min-w-0">
            <form method="GET" class="flex flex-wrap gap-2 mb-4">
                <select name="type" class="c-input w-auto" autocomplete="off" onchange="this.form.submit()">
                    <option value="">Todos os tipos</option>
                    @foreach(\App\Models\Activity::TYPES as $k => $t)<option value="{{ $k }}" @selected($type === $k)>{{ $t['label'] }}</option>@endforeach
                </select>
                <select name="client_id" class="c-input w-auto max-w-xs" autocomplete="off" onchange="this.form.submit()">
                    <option value="">Todos os clientes</option>
                    @foreach($clients as $c)<option value="{{ $c->id }}" @selected($clientId === $c->id)>{{ $c->name }}</option>@endforeach
                </select>
            </form>

            <div class="c-card p-5">
                @if($activities->isEmpty())
                    <x-empty-state icon="fa-timeline" title="Nenhuma atividade encontrada" text="Use o formulário ao lado para registrar a primeira." />
                @else
                    <ul>
                        @foreach($activities as $activity)
                            @include('activities._item', ['showClient' => true])
                        @endforeach
                    </ul>
                @endif
            </div>
            <div class="mt-4">{{ $activities->links() }}</div>
        </div>

        <aside class="lg:w-80 flex-shrink-0">
            <div class="c-card p-5 lg:sticky lg:top-24">
                <h3 class="text-sm font-semibold mb-4"><i class="fa-solid fa-plus-circle text-brand-500 mr-2"></i>Nova atividade</h3>
                @include('activities._form', ['client' => null, 'clients' => $clients])
            </div>
        </aside>
    </div>
</x-app-layout>
