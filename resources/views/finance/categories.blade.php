<x-app-layout>
    @section('title', 'Categorias financeiras')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Financeiro', 'url' => route('finance.index')], ['label' => 'Categorias']]" />
    </x-slot>

    <x-page-title title="Categorias financeiras" icon="fa-folder-tree" subtitle="Organize receitas e despesas para entender para onde vai o dinheiro." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @foreach(['income' => ['Receitas', 'fa-arrow-down text-accent-500'], 'expense' => ['Despesas', 'fa-arrow-up text-danger-500']] as $type => [$label, $icon])
            <div class="c-card overflow-hidden h-fit">
                <h3 class="px-5 py-4 text-sm font-semibold border-b border-surface-100 dark:border-surface-800"><i class="fa-solid {{ $icon }} mr-2"></i>{{ $label }}</h3>
                @foreach($categories->where('type', $type) as $cat)
                    <div class="px-5 py-3 flex items-center gap-3 border-b last:border-0 border-surface-100 dark:border-surface-800">
                        <span class="w-3 h-3 rounded-full" style="background: {{ $cat->color }}"></span>
                        <span class="flex-1 text-sm">{{ $cat->name }}</span>
                        <span class="text-xs text-surface-400">{{ $cat->entries_count }}</span>
                        <x-confirm-delete :action="route('finance.categories.destroy', $cat)" title="Excluir categoria?" text="Os lançamentos continuam existindo, apenas sem esta categoria nas próximas listagens." />
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="c-card p-5 h-fit">
            <h3 class="text-sm font-semibold mb-4"><i class="fa-solid fa-plus-circle text-brand-500 mr-2"></i>Nova categoria</h3>
            <form action="{{ route('finance.categories.store') }}" method="POST" class="space-y-3">
                @csrf
                <div><label class="c-label">Nome</label><input name="name" required maxlength="80" class="c-input">@error('name')<p class="c-error">{{ $message }}</p>@enderror</div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="c-label">Tipo</label><select name="type" class="c-input"><option value="expense">Despesa</option><option value="income">Receita</option></select></div>
                    <div><label class="c-label">Cor</label><input type="color" name="color" value="#64748b" class="h-10 w-full rounded-lg border border-surface-200 dark:border-surface-700"></div>
                </div>
                <button class="c-btn-primary w-full">Criar</button>
            </form>
        </div>
    </div>
</x-app-layout>
