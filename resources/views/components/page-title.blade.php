@props(['title', 'subtitle' => null, 'icon' => null])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <h1 class="text-2xl font-bold text-surface-900 dark:text-white flex items-center gap-2">
            @if($icon)<i class="fa-solid {{ $icon }} text-brand-500 text-xl"></i>@endif
            <span class="truncate">{{ $title }}</span>
        </h1>
        @if($subtitle)
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
