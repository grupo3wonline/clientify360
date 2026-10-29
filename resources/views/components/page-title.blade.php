@props(['title', 'subtitle' => null, 'icon' => null, 'helpDescription' => null, 'helpExample' => null, 'helpTip' => null])
{{-- Page heading (the page's single <h1>), help ("help" slot or help-* props) and "actions" slot. --}}
<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <div class="flex items-center gap-3">
            @if($icon)
                <span class="c-icon-tile bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400" aria-hidden="true">
                    <i class="fa-solid {{ $icon }}"></i>
                </span>
            @endif
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-surface-900 dark:text-white truncate">{{ $title }}</h1>
            @isset($help)
                {{ $help }}
            @elseif($helpDescription)
                <x-help :title="$title" :description="$helpDescription" :example="$helpExample" :tip="$helpTip" />
            @endisset
        </div>
        @if($subtitle)
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-2 max-w-3xl">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
