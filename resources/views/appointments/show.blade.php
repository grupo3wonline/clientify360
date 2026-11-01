<x-app-layout>
    @section('title', 'Agendamento')

    @php
        $statusColors = [
            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
            'confirmed' => 'bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300',
            'done' => 'bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300',
            'cancelled' => 'bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300',
        ];
        $badge = $statusColors[$appointment->status] ?? 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-300';
    @endphp

    <x-slot name="header">
        <x-breadcrumb :items="[['label' => 'Agendamentos', 'url' => route('appointments.index')], ['label' => \Illuminate\Support\Str::limit($appointment->title, 40)]]" />
    </x-slot>

    <div class="max-w-3xl">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $appointment->color ?? '#6366f1' }}"></span>
                <div>
                    <div class="flex items-center gap-3"><h1 class="text-2xl font-bold text-surface-900 dark:text-white">{{ $appointment->title }}</h1><x-help title="Como usar esta página" description="Detalhes do compromisso e ações rápidas de status." example="Confirme o agendamento depois de falar com o cliente." tip="Use Cancelar em vez de excluir para manter o histórico." /></div>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">
                        {{ $appointment->typeLabel() }}
                        <span class="inline-flex items-center px-2 py-0.5 ml-1 rounded-full text-[11px] font-medium {{ $badge }}">{{ $appointment->statusLabel() }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('appointments.edit', $appointment) }}" class="inline-flex items-center px-3 py-2 text-sm rounded-xl text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                    <i class="fa-solid fa-pen mr-2 text-xs"></i> Editar
                </a>
                <form autocomplete="off" action="{{ route('appointments.destroy', $appointment) }}" method="POST" x-data>
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="Swal.fire({ title: 'Excluir agendamento?', text: 'Esta ação não pode ser desfeita.', icon: 'warning', showCancelButton: true, confirmButtonColor: brandColors.danger['600'], cancelButtonColor: brandColors.surface['500'], confirmButtonText: 'Excluir', cancelButtonText: 'Cancelar' }).then((r) => { if (r.isConfirmed) $el.closest('form').requestSubmit() })"
                            class="inline-flex items-center px-3 py-2 text-sm rounded-xl text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-950/30 transition">
                        <i class="fa-solid fa-trash-can mr-2 text-xs"></i> Excluir
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick status actions -->
        <div class="flex flex-wrap items-center gap-2 mb-6">
            @if($appointment->status !== 'confirmed' && $appointment->status !== 'cancelled')
                <form autocomplete="off" action="{{ route('appointments.confirm', $appointment) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm rounded-xl bg-brand-600 hover:bg-brand-700 text-white transition">
                        <i class="fa-solid fa-circle-check mr-2 text-xs"></i> Confirmar
                    </button>
                </form>
            @endif
            @if($appointment->status !== 'done' && $appointment->status !== 'cancelled')
                <form autocomplete="off" action="{{ route('appointments.done', $appointment) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm rounded-xl bg-accent-600 hover:bg-accent-700 text-white transition">
                        <i class="fa-solid fa-check-double mr-2 text-xs"></i> Concluir
                    </button>
                </form>
            @endif
            @if($appointment->status !== 'cancelled')
                <form autocomplete="off" action="{{ route('appointments.cancel', $appointment) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-3 py-2 text-sm rounded-xl border border-danger-200 dark:border-danger-800 text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-950/30 transition">
                        <i class="fa-solid fa-ban mr-2 text-xs"></i> Cancelar
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Início</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $appointment->starts_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Término</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $appointment->ends_at ? $appointment->ends_at->format('d/m/Y H:i') : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Cliente</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $appointment->client?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Oportunidade</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $appointment->deal?->title ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Local</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100">{{ $appointment->location ?? '—' }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium text-surface-500 dark:text-surface-400 mb-1">Descrição</dt>
                    <dd class="text-sm text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ $appointment->description ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-app-layout>
