@props(['action', 'title' => 'Excluir?', 'text' => 'Esta ação não pode ser desfeita.', 'label' => null, 'method' => 'DELETE', 'confirm' => 'Excluir', 'color' => '#dc2626'])

{{-- Botão com confirmação SweetAlert2 (mesmo padrão já usado em Clientes/Agenda). --}}
<form action="{{ $action }}" method="POST" x-data class="inline">
    @csrf
    @if(strtoupper($method) !== 'POST') @method($method) @endif
    <button type="button"
            @click="Swal.fire({ title: @js($title), text: @js($text), icon: 'warning', showCancelButton: true, confirmButtonColor: @js($color), cancelButtonColor: '#64748b', confirmButtonText: @js($confirm), cancelButtonText: 'Cancelar', customClass: { popup: 'rounded-2xl' } }).then((r) => { if (r.isConfirmed) $el.closest('form').submit() })"
            {{-- Classe própria substitui a padrão (ícone de lixeira) em vez de mesclar, evitando conflito de padding --}}
            {{ $attributes->has('class') ? $attributes : $attributes->merge(['class' => 'p-2 rounded-lg text-surface-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:text-danger-400 dark:hover:bg-danger-950/30 transition']) }}
            title="{{ $label ?? $confirm }}">
        {{ $slot->isEmpty() ? new \Illuminate\Support\HtmlString('<i class="fa-solid fa-trash-can text-sm"></i>') : $slot }}
    </button>
</form>
