<x-app-layout>
    @section('title', 'Captação de leads')

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Captação de leads']]" />
    </x-slot>

    <x-page-title title="Captação de leads" icon="fa-magnet" subtitle="Formulários com link público: cada envio vira cliente + oportunidade no funil, automaticamente.">
        <x-slot name="actions">
            <a href="{{ route('lead-forms.create') }}" class="c-btn-primary"><i class="fa-solid fa-plus mr-2 text-xs"></i> Novo formulário</a>
        </x-slot>
    </x-page-title>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
        @forelse($forms as $form)
            <div class="c-card p-5 flex flex-col" x-data="{ copied: false }">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <a href="{{ route('lead-forms.show', $form) }}" class="text-base font-semibold text-surface-900 dark:text-white hover:text-brand-600 truncate block">{{ $form->name }}</a>
                        <p class="text-xs text-surface-500 truncate">{{ $form->title }}</p>
                    </div>
                    <span class="c-badge {{ $form->is_active ? 'bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300' : 'bg-surface-100 text-surface-500 dark:bg-surface-800' }}">{{ $form->is_active ? 'Ativo' : 'Pausado' }}</span>
                </div>
                <p class="text-3xl font-bold mt-4" style="color: {{ $form->color }}">{{ $form->submissions_count }} <span class="text-sm font-medium text-surface-400">leads</span></p>
                <div class="mt-4 flex items-center gap-2">
                    <input readonly value="{{ $form->publicUrl() }}" class="c-input py-1.5 text-xs font-mono" aria-label="Link público">
                    <button type="button" @click="navigator.clipboard.writeText(@js($form->publicUrl())); copied = true; setTimeout(() => copied = false, 1500)" class="c-btn-ghost px-3 py-1.5" title="Copiar link">
                        <i class="fa-solid" :class="copied ? 'fa-check text-accent-500' : 'fa-copy'"></i>
                    </button>
                    <a href="{{ $form->publicUrl() }}" target="_blank" rel="noopener" class="c-btn-ghost px-3 py-1.5" title="Abrir"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                </div>
            </div>
        @empty
            <div class="c-card md:col-span-2 xl:col-span-3">
                <x-empty-state icon="fa-magnet" title="Nenhum formulário ainda" text="Crie um formulário, coloque o link na bio do Instagram, no WhatsApp ou no botão do seu site.">
                    <a href="{{ route('lead-forms.create') }}" class="c-btn-primary">Criar primeiro formulário</a>
                </x-empty-state>
            </div>
        @endforelse
    </div>

    @if($recent->isNotEmpty())
        <div class="c-card overflow-hidden">
            <h3 class="px-5 py-4 text-sm font-semibold border-b border-surface-100 dark:border-surface-800"><i class="fa-solid fa-inbox text-brand-500 mr-2"></i>Últimos leads recebidos</h3>
            @foreach($recent as $s)
                <div class="px-5 py-3 flex items-center gap-3 border-b last:border-0 border-surface-100 dark:border-surface-800 text-sm">
                    <i class="fa-solid fa-user-plus text-brand-500"></i>
                    <div class="flex-1 min-w-0">
                        @if($s->client)<a href="{{ route('clients.show', $s->client_id) }}" class="font-medium hover:underline">{{ $s->client->name }}</a>@else<span class="font-medium">{{ $s->data['name'] ?? $s->data['email'] ?? 'Lead' }}</span>@endif
                        <span class="text-xs text-surface-500">via {{ $s->form?->name }}</span>
                        @if($s->note)<p class="text-xs text-warning-700"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $s->note }}</p>@endif
                    </div>
                    <span class="text-xs text-surface-400 whitespace-nowrap">{{ $s->created_at->diffForHumans() }}</span>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
