<x-app-layout>
    @section('title', 'Etapas do funil')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Funil de vendas', 'url' => route('deals.index')], ['label' => 'Etapas']]" />
    </x-slot>

    <x-page-title help-description="Personalize as etapas do seu funil." help-example="Crie a etapa Visita técnica entre Contato e Proposta." help-tip="A probabilidade de cada etapa entra no cálculo da previsão." title="Etapas do funil" icon="fa-sliders" subtitle="A probabilidade de cada etapa é usada no cálculo da previsão ponderada de vendas." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 c-card divide-y divide-surface-100 dark:divide-surface-800">
            @foreach($stages as $stage)
                <form autocomplete="off" action="{{ route('stages.update', $stage) }}" method="POST" class="p-4 flex flex-wrap items-end gap-3">
                    @csrf @method('PUT')
                    <div class="w-16"><label class="c-label text-xs">Ordem</label><input autocomplete="off" type="number" name="position" min="1" max="99" value="{{ $stage->position }}" class="c-input px-2"></div>
                    <div class="flex-1 min-w-[140px]"><label class="c-label text-xs">Nome</label><input autocomplete="off" name="name" value="{{ $stage->name }}" maxlength="60" required class="c-input"></div>
                    <div class="w-24"><label class="c-label text-xs">Prob. %</label><input autocomplete="off" type="number" name="probability" min="0" max="100" value="{{ $stage->probability }}" class="c-input px-2"></div>
                    <div><label class="c-label text-xs">Cor</label><input autocomplete="off" type="color" name="color" value="{{ $stage->color }}" class="h-10 w-12 rounded-lg border border-surface-200 dark:border-surface-700 bg-transparent"></div>
                    <div class="flex items-center gap-1">
                        <button class="c-btn-ghost px-3" title="Salvar"><i class="fa-solid fa-floppy-disk"></i></button>
                    </div>
                    <span class="text-xs text-surface-400 w-full">{{ $stage->deals_count }} oportunidade(s) aberta(s)</span>
                </form>
                <div class="px-4 pb-3 -mt-2 flex justify-end">
                    <x-confirm-delete :action="route('stages.destroy', $stage)" title="Excluir etapa?" label="Excluir etapa" />
                </div>
            @endforeach
        </div>

        <div class="c-card p-5 h-fit">
            <h3 class="text-sm font-semibold mb-4"><i class="fa-solid fa-plus-circle text-brand-500 mr-2"></i>Nova etapa</h3>
            <form autocomplete="off" action="{{ route('stages.store') }}" method="POST" class="space-y-3">
                @csrf
                <div><label class="c-label">Nome</label><input autocomplete="off" name="name" required maxlength="60" class="c-input" value="{{ old('name') }}">@error('name')<p class="c-error">{{ $message }}</p>@enderror</div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="c-label">Prob. %</label><input autocomplete="off" type="number" name="probability" min="0" max="100" value="50" class="c-input"></div>
                    <div><label class="c-label">Cor</label><input autocomplete="off" type="color" name="color" value="#6366f1" class="h-10 w-full rounded-lg border border-surface-200 dark:border-surface-700"></div>
                </div>
                <button class="c-btn-primary w-full">Adicionar</button>
            </form>
        </div>
    </div>
</x-app-layout>
