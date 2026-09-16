@props([
    'text' => '',
    'position' => 'top',
    'icon' => 'fa-circle-question',
])

@php
    $positions = [
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
    ];
    $positionClass = $positions[$position] ?? $positions['top'];
@endphp

{{--
    Uso como botão de ajuda:
    <x-tooltip text="Explicação sobre este campo." />

    Ou envolvendo um elemento:
    <x-tooltip text="Dica">Passe o mouse aqui</x-tooltip>
--}}

<span x-data="{ show: false }" class="relative inline-flex items-center">
    <span @mouseenter="show = true"
          @mouseleave="show = false"
          @focus="show = true"
          @blur="show = false"
          tabindex="0"
          role="button"
          aria-label="{{ $text }}"
          class="inline-flex items-center cursor-help">
        @if (trim($slot) !== '')
            {{ $slot }}
        @else
            <i class="fa-solid {{ $icon }} text-surface-400 hover:text-brand-500 transition text-sm"></i>
        @endif
    </span>

    <span x-show="show"
          x-cloak
          x-transition:enter="transition ease-out duration-150"
          x-transition:enter-start="opacity-0 scale-95"
          x-transition:enter-end="opacity-100 scale-100"
          class="absolute {{ $positionClass }} z-50 w-max max-w-xs px-3 py-2 rounded-lg bg-surface-900 dark:bg-surface-700 text-white text-xs font-medium shadow-lg pointer-events-none"
          role="tooltip">
        {{ $text }}
    </span>
</span>
