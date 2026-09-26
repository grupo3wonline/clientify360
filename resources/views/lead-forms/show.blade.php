<x-app-layout>
    @section('title', $form->name)

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Captação de leads', 'url' => route('lead-forms.index')], ['label' => $form->name]]" />
    </x-slot>

    <x-page-title :title="$form->name" icon="fa-magnet" :subtitle="$form->submissions_count . ' lead(s) captado(s)'">
        <x-slot name="actions">
            <form action="{{ route('lead-forms.toggle', $form) }}" method="POST">@csrf
                <button class="c-btn-ghost">{!! $form->is_active ? '<i class="fa-solid fa-pause mr-2 text-xs"></i> Pausar' : '<i class="fa-solid fa-play mr-2 text-xs"></i> Ativar' !!}</button></form>
            <a href="{{ route('lead-forms.edit', $form) }}" class="c-btn-ghost"><i class="fa-solid fa-pen mr-2 text-xs"></i> Editar</a>
            <x-confirm-delete :action="route('lead-forms.destroy', $form)" title="Excluir formulário?" text="O link deixará de funcionar. Os clientes já criados são mantidos." />
        </x-slot>
    </x-page-title>

    <div class="c-card p-5 mb-6" x-data="{ copied: null, copy(t, k) { navigator.clipboard.writeText(t); this.copied = k; setTimeout(() => this.copied = null, 1500) } }">
        <h3 class="text-sm font-semibold mb-3"><i class="fa-solid fa-share-nodes text-brand-500 mr-2"></i>Onde divulgar</h3>
        @php
            $url = $form->publicUrl();
            $utm = $url . '?utm_source=instagram&utm_medium=bio';
        @endphp
        <div class="space-y-3">
            <div>
                <label class="c-label text-xs">Link direto (WhatsApp, e-mail, botão do site)</label>
                <div class="flex gap-2"><input readonly value="{{ $url }}" class="c-input py-2 font-mono text-xs"><button type="button" @click="copy(@js($url), 'url')" class="c-btn-ghost px-3"><i class="fa-solid" :class="copied === 'url' ? 'fa-check text-accent-500' : 'fa-copy'"></i></button><a href="{{ $url }}" target="_blank" rel="noopener" class="c-btn-ghost px-3"><i class="fa-solid fa-arrow-up-right-from-square"></i></a></div>
            </div>
            <div>
                <label class="c-label text-xs">Com rastreamento de campanha (exemplo: bio do Instagram)</label>
                <div class="flex gap-2"><input readonly value="{{ $utm }}" class="c-input py-2 font-mono text-xs"><button type="button" @click="copy(@js($utm), 'utm')" class="c-btn-ghost px-3"><i class="fa-solid" :class="copied === 'utm' ? 'fa-check text-accent-500' : 'fa-copy'"></i></button></div>
                <p class="c-hint">Troque <code>utm_source</code> por <code>facebook</code>, <code>google</code>, etc. — a origem aparece em cada lead abaixo.</p>
            </div>
        </div>
    </div>

    <div class="c-card overflow-hidden">
        <h3 class="px-5 py-4 text-sm font-semibold border-b border-surface-100 dark:border-surface-800"><i class="fa-solid fa-inbox text-brand-500 mr-2"></i>Envios recebidos</h3>
        @forelse($submissions as $s)
            <details class="border-b last:border-0 border-surface-100 dark:border-surface-800 group">
                <summary class="px-5 py-3 flex items-center gap-3 cursor-pointer list-none hover:bg-surface-50 dark:hover:bg-surface-800/40">
                    <i class="fa-solid fa-chevron-right text-[10px] text-surface-400 transition group-open:rotate-90"></i>
                    <div class="flex-1 min-w-0 text-sm">
                        <span class="font-medium">{{ $s->data['name'] ?? $s->data['email'] ?? 'Lead' }}</span>
                        @if($s->utm_source)<span class="c-badge bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 ml-1">{{ $s->utm_source }}</span>@endif
                        @if($s->note)<span class="block text-xs text-amber-600"><i class="fa-solid fa-triangle-exclamation mr-1"></i>{{ $s->note }}</span>@endif
                    </div>
                    @if($s->client)<a href="{{ route('clients.show', $s->client_id) }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Ver cliente</a>@endif
                    @if($s->deal)<a href="{{ route('deals.show', $s->deal_id) }}" class="text-xs text-brand-600 dark:text-brand-400 hover:underline">Oportunidade</a>@endif
                    <span class="text-xs text-surface-400 whitespace-nowrap">{{ $s->created_at->format('d/m/Y H:i') }}</span>
                </summary>
                <dl class="px-12 pb-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    @foreach($s->data as $k => $v)
                        @continue($v === null || $v === '')
                        <div><dt class="text-xs text-surface-500">{{ collect($form->fields)->firstWhere('name', $k)['label'] ?? $k }}</dt><dd class="whitespace-pre-line break-words">{{ $v }}</dd></div>
                    @endforeach
                    @if($s->referrer)<div><dt class="text-xs text-surface-500">Página de origem</dt><dd class="text-xs break-all">{{ $s->referrer }}</dd></div>@endif
                </dl>
            </details>
        @empty
            <x-empty-state icon="fa-inbox" title="Nenhum envio ainda" text="Divulgue o link acima. Cada envio aparecerá aqui e no seu funil." />
        @endforelse
    </div>
    <div class="mt-4">{{ $submissions->links() }}</div>
</x-app-layout>
