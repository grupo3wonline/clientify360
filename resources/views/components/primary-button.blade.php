@props(['href' => null, 'disabled' => false])

@php
    $classes = 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 dark:bg-brand-500 border border-transparent rounded-xl font-semibold text-sm text-white tracking-wide hover:bg-brand-700 dark:hover:bg-brand-600 focus:bg-brand-700 dark:focus:bg-brand-600 active:bg-brand-800 dark:active:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:focus:ring-offset-surface-800 transition ease-in-out duration-150 shadow-sm shadow-brand-600/25 disabled:opacity-60 disabled:cursor-not-allowed disabled:pointer-events-none aria-disabled:opacity-60 aria-disabled:cursor-not-allowed aria-disabled:pointer-events-none';
@endphp

@if ($href)
    {{-- $href is for navigation only; do not use for action-performing buttons. --}}
    <a
        href="{{ $disabled ? '#' : $href }}"
        @if ($disabled) aria-disabled="true" tabindex="-1" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </a>
@else
    <button @disabled($disabled) {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
