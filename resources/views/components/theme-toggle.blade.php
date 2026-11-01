@props(['label' => false])
{{-- Light/dark toggle backed by the Alpine store "theme" (resources/js/theme.js). --}}
<button type="button" x-data
        @click="$store.theme.toggle()"
        :aria-pressed="$store.theme.isDark.toString()"
        :aria-label="$store.theme.isDark ? 'Ativar tema claro' : 'Ativar tema escuro'"
        :title="$store.theme.isDark ? 'Ativar tema claro' : 'Ativar tema escuro'"
        aria-label="Ativar tema escuro"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-2 p-2.5 rounded-xl text-surface-500 hover:text-surface-800 dark:text-surface-400 dark:hover:text-surface-100 hover:bg-surface-100 dark:hover:bg-surface-800 transition']) }}>
    <i class="fa-solid fa-moon text-sm dark:hidden" aria-hidden="true"></i>
    <i class="fa-solid fa-sun text-sm text-warning-400 hidden dark:inline-block" aria-hidden="true"></i>
    @if($label)
        <span class="text-sm font-medium"><span class="dark:hidden">Tema escuro</span><span class="hidden dark:inline">Tema claro</span></span>
    @endif
</button>
