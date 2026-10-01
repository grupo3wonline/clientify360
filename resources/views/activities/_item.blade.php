@php
    $outcomeCls = ['positive' => 'text-accent-600', 'neutral' => 'text-surface-400', 'negative' => 'text-red-500'][$activity->outcome] ?? 'text-brand-500';
@endphp
<li class="relative pl-10 pb-5 last:pb-0">
    <span class="absolute left-[15px] top-8 bottom-0 w-px bg-surface-200 dark:bg-surface-800" aria-hidden="true"></span>
    <span class="absolute left-0 top-0 w-8 h-8 rounded-full bg-surface-100 dark:bg-surface-800 flex items-center justify-center">
        <i class="{{ $activity->typeIcon() }} text-xs {{ $outcomeCls }}"></i>
    </span>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-sm font-semibold text-surface-900 dark:text-white">{{ $activity->title }}</p>
            <p class="text-xs text-surface-500 dark:text-surface-400 mt-0.5">
                {{ $activity->typeLabel() }} · {{ $activity->happened_at->format('d/m/Y H:i') }}
                @if(!empty($showClient) && $activity->client) · <a class="text-brand-600 dark:text-brand-400 hover:underline" href="{{ url('/app/clients/'.$activity->client_id.'/edit') }}">{{ $activity->client->name }}</a>@endif
                @if($activity->deal && empty($hideDeal)) · <a class="hover:underline" href="{{ route('deals.show', $activity->deal_id) }}"><i class="fa-solid fa-handshake mr-0.5"></i>{{ $activity->deal->title }}</a>@endif
            </p>
            @if($activity->description)<p class="text-sm text-surface-600 dark:text-surface-400 mt-1.5 whitespace-pre-line">{{ $activity->description }}</p>@endif
            @if($activity->next_step)
                <p class="mt-2 inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-lg {{ $activity->reminder?->is_completed ? 'bg-accent-50 text-accent-700 dark:bg-accent-950/30 dark:text-accent-300 line-through' : 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-300' }}">
                    <i class="fa-solid fa-forward-step"></i> {{ $activity->next_step }}
                    @if($activity->next_step_at) — {{ $activity->next_step_at->format('d/m H:i') }}@endif
                </p>
            @endif
        </div>
        <x-confirm-delete :action="route('activities.destroy', $activity)" title="Remover do histórico?" class="p-1.5 rounded-lg text-surface-300 hover:text-red-600 transition" />
    </div>
</li>
