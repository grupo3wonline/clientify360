@props(['action', 'title' => 'Excluir?', 'text' => 'Esta ação não pode ser desfeita.', 'label' => null, 'method' => 'DELETE', 'confirm' => 'Excluir', 'tone' => 'danger'])
{{-- Button with a SweetAlert2 confirmation. requestSubmit() fires "submit", so the loading overlay shows. --}}
<form action="{{ $action }}" method="POST" x-data class="inline" autocomplete="off"
      @submit="if ($el.dataset.confirmed !== '1') { $event.preventDefault(); }">
    @csrf
    @if(strtoupper($method) !== 'POST') @method($method) @endif
    <button type="button"
            @click="Swal.fire({ title: @js($title), text: @js($text), icon: 'warning', showCancelButton: true, confirmButtonColor: brandColors[@js($tone)]['600'], confirmButtonText: @js($confirm), cancelButtonText: 'Cancelar' }).then((r) => { if (r.isConfirmed) { const f = $el.closest('form'); f.dataset.confirmed = '1'; f.requestSubmit(); } })"
            {{-- A custom class replaces the default (trash icon) instead of merging, avoiding padding conflicts --}}
            {{ $attributes->has('class') ? $attributes : $attributes->merge(['class' => 'p-2 rounded-lg text-surface-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:text-danger-400 dark:hover:bg-danger-950/30 transition']) }}
            title="{{ $label ?? $confirm }}" aria-label="{{ $label ?? $confirm }}">
        {{ $slot->isEmpty() ? new \Illuminate\Support\HtmlString('<i class="fa-solid fa-trash-can text-sm" aria-hidden="true"></i>') : $slot }}
    </button>
</form>
