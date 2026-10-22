{{-- Histórico + formulário, usado na ficha do cliente e da oportunidade. --}}
<div class="c-card p-5" x-data="{ adding: {{ $errors->hasAny(['title', 'next_step', 'next_step_at']) ? 'true' : 'false' }} }">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200"><i class="fa-solid fa-timeline text-brand-500 mr-2"></i>Histórico de relacionamento</h3>
        <button type="button" @click="adding = !adding" class="text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline">
            <span x-show="!adding"><i class="fa-solid fa-plus mr-1"></i>Registrar atividade</span><span x-show="adding" x-cloak>Fechar</span>
        </button>
    </div>
    <div x-show="adding" x-cloak class="mb-5 pb-5 border-b border-surface-100 dark:border-surface-800">
        @include('activities._form', ['client' => $client, 'deal' => $deal ?? null])
    </div>
    @if($activities->isEmpty())
        <p class="text-sm text-surface-500">Nenhuma interação registrada ainda. Registre ligações, reuniões e mensagens para nunca perder o fio da conversa.</p>
    @else
        <ul>
            @foreach($activities as $activity)
                @include('activities._item', ['hideDeal' => !empty($deal)])
            @endforeach
        </ul>
    @endif
</div>
