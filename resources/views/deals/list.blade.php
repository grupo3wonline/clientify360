<x-app-layout>
    @section('title', 'Oportunidades')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Funil de vendas', 'url' => route('deals.index')], ['label' => 'Lista']]" />
    </x-slot>

    <x-page-title title="Todas as oportunidades" icon="fa-list">
        <x-slot name="actions">
            <a href="{{ route('deals.index') }}" class="c-btn-ghost"><i class="fa-solid fa-table-columns mr-2 text-xs"></i> Kanban</a>
        </x-slot>
    </x-page-title>

    <div class="flex items-center gap-1 mb-4 bg-surface-100 dark:bg-surface-800 rounded-xl p-1 overflow-x-auto w-fit max-w-full">
        <a href="{{ route('deals.list') }}" class="c-tab {{ !$status ? 'c-tab-active' : '' }}">Todas</a>
        @foreach(\App\Models\Deal::STATUS_LABELS as $k => $label)
            <a href="{{ route('deals.list', ['status' => $k]) }}" class="c-tab {{ $status === $k ? 'c-tab-active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="c-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-surface-200 dark:divide-surface-800">
                <thead class="bg-surface-50 dark:bg-surface-800/50">
                    <tr><th class="c-th">Oportunidade</th><th class="c-th hidden sm:table-cell">Cliente</th><th class="c-th hidden md:table-cell">Etapa</th><th class="c-th text-right">Valor</th><th class="c-th">Status</th><th class="c-th hidden lg:table-cell">Atualizada</th></tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                    @forelse($deals as $deal)
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/40">
                            <td class="c-td"><a href="{{ route('deals.show', $deal) }}" class="font-medium text-brand-700 dark:text-brand-400 hover:underline">{{ $deal->title }}</a></td>
                            <td class="c-td hidden sm:table-cell">{{ $deal->client?->name }}</td>
                            <td class="c-td hidden md:table-cell">@if($deal->stage)<span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: {{ $deal->stage->color }}"></span>{{ $deal->stage->name }}</span>@endif</td>
                            <td class="c-td text-right font-semibold whitespace-nowrap">@money($deal->value)</td>
                            <td class="c-td">@include('deals._status', ['deal' => $deal])</td>
                            <td class="c-td text-xs text-surface-500 whitespace-nowrap hidden lg:table-cell">{{ $deal->updated_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="fa-filter-circle-dollar" title="Nenhuma oportunidade encontrada" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $deals->links() }}</div>
</x-app-layout>
