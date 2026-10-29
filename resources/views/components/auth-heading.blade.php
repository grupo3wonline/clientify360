@props(['title', 'subtitle' => null, 'icon' => 'fa-right-to-bracket', 'help' => null])
{{-- Heading of the auth card (the page's single <h1>) with an optional help tooltip. --}}
<div class="text-center mb-6">
    <span class="c-icon-tile mx-auto mb-4 bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-surface-900 dark:text-white inline-flex items-center gap-2">
        {{ $title }}
        @if($help)
            <x-tooltip :text="$help" position="bottom" />
        @endif
    </h1>
    @if($subtitle)
        <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">{{ $subtitle }}</p>
    @endif
</div>
