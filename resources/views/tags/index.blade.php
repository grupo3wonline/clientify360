<x-app-layout>
    @section('title', 'Tags')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Tags']]" />
    </x-slot>

    <x-page-title help-description="Etiquetas para segmentar a carteira de clientes." help-example="VIP, Inadimplente, Recorrente." help-tip="Use cores diferentes para reconhecer a tag de longe." title="Tags de clientes" icon="fa-tags" subtitle="Segmente sua carteira (ex.: VIP, Inadimplente, Recorrente) e filtre a lista de clientes por tag." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 c-card divide-y divide-surface-100 dark:divide-surface-800">
            @forelse($tags as $tag)
                <div class="p-4 flex flex-wrap items-center gap-3" x-data="{ edit: false }">
                    <template x-if="!edit">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <span class="c-badge text-xs px-3 py-1" style="background: {{ $tag->color }}; color: {{ $tag->textColor() }}">{{ $tag->name }}</span>
                            <a href="{{ route('clients.index', ['tag' => $tag->id]) }}" class="text-xs text-surface-500 hover:text-brand-600">{{ $tag->clients_count }} cliente(s) →</a>
                        </div>
                    </template>
                    <form autocomplete="off" x-show="edit" x-cloak action="{{ route('tags.update', $tag) }}" method="POST" class="flex items-center gap-2 flex-1">
                        @csrf @method('PUT')
                        <input autocomplete="off" name="name" value="{{ $tag->name }}" maxlength="50" class="c-input py-1.5">
                        <input autocomplete="off" type="color" name="color" value="{{ $tag->color }}" class="h-9 w-10 rounded-lg border border-surface-200 dark:border-surface-700">
                        <button class="c-btn-primary py-1.5 px-3"><i class="fa-solid fa-check"></i></button>
                    </form>
                    <button type="button" @click="edit = !edit" class="p-2 rounded-lg text-surface-400 hover:text-brand-600" title="Editar"><i class="fa-solid fa-pen text-sm"></i></button>
                    <x-confirm-delete :action="route('tags.destroy', $tag)" title="Excluir tag?" text="Ela será removida de todos os clientes." />
                </div>
            @empty
                <x-empty-state icon="fa-tags" title="Nenhuma tag criada" text="Crie a primeira ao lado." />
            @endforelse
        </div>

        <div class="c-card p-5 h-fit">
            <h3 class="text-sm font-semibold mb-4"><i class="fa-solid fa-plus-circle text-brand-500 mr-2"></i>Nova tag</h3>
            <form autocomplete="off" action="{{ route('tags.store') }}" method="POST" class="space-y-3">
                @csrf
                <div><label class="c-label">Nome</label><input autocomplete="off" name="name" required maxlength="50" value="{{ old('name') }}" class="c-input" placeholder="Ex.: VIP">@error('name')<p class="c-error">{{ $message }}</p>@enderror</div>
                <div>
                    <label class="c-label">Cor</label>
                    <div class="flex flex-wrap gap-2" x-data="{ c: '#6366f1' }">
                        @foreach(['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#ec4899', '#8b5cf6', '#14b8a6', '#64748b'] as $color)
                            <button type="button" @click="c = '{{ $color }}'" class="w-7 h-7 rounded-full ring-offset-2 dark:ring-offset-surface-900" :class="c === '{{ $color }}' && 'ring-2 ring-surface-400'" style="background: {{ $color }}" aria-label="Cor {{ $color }}"></button>
                        @endforeach
                        <input autocomplete="off" type="color" name="color" x-model="c" class="h-7 w-9 rounded border-0 bg-transparent" aria-label="Outra cor">
                    </div>
                </div>
                <button class="c-btn-primary w-full">Criar tag</button>
            </form>
        </div>
    </div>
</x-app-layout>
