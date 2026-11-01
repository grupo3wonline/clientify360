@props([
    'title' => 'Como usar esta página',
    'description' => null,
    'example' => null,
    'tip' => null,
    'align' => 'left', // left | right
])
{{-- "?" help button: keyboard accessible popover with description, usage example and tip. --}}
<div x-data="{ open: false }" class="relative inline-flex" @keydown.escape.window="open = false" @click.outside="open = false">
    <button type="button" @click="open = !open" :aria-expanded="open.toString()" aria-expanded="false"
            aria-label="Ajuda: {{ $title }}" title="Ajuda"
            class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-500 hover:text-brand-600 hover:border-brand-300 dark:text-surface-400 dark:hover:text-brand-400 transition">
        <i class="fa-solid fa-question text-xs" aria-hidden="true"></i>
    </button>
    <div x-show="open" x-cloak x-transition.origin.top role="dialog" aria-label="{{ $title }}"
         class="absolute z-40 mt-10 w-[min(22rem,calc(100vw-2rem))] {{ $align === 'right' ? 'right-0' : 'left-0 sm:left-auto sm:right-0 md:left-0 md:right-auto' }} rounded-card border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 p-4 text-left shadow-xl shadow-surface-900/10">
        <p class="text-sm font-semibold text-surface-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-brand-500" aria-hidden="true"></i> {{ $title }}
        </p>
        @if($description)
            <p class="mt-2 text-sm text-surface-600 dark:text-surface-300 leading-relaxed">{{ $description }}</p>
        @endif
        @if($example)
            <p class="mt-3 text-xs text-surface-600 dark:text-surface-300"><span class="font-semibold text-surface-800 dark:text-surface-100">Exemplo de uso:</span> {{ $example }}</p>
        @endif
        @if($tip)
            <p class="mt-3 rounded-lg bg-accent-50 dark:bg-accent-950/40 px-3 py-2 text-xs text-accent-800 dark:text-accent-200"><i class="fa-solid fa-lightbulb mr-1" aria-hidden="true"></i> <span class="font-semibold">Dica:</span> {{ $tip }}</p>
        @endif
        {{ $slot }}
        <button type="button" @click="open = false" class="mt-3 text-xs font-medium text-brand-600 dark:text-brand-400 hover:underline">Fechar</button>
    </div>
</div>
