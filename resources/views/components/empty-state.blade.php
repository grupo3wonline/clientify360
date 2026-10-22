@props(['icon' => 'fa-inbox', 'title', 'text' => null])

<div {{ $attributes->merge(['class' => 'p-10 text-center']) }}>
    <div class="w-14 h-14 bg-surface-100 dark:bg-surface-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid {{ $icon }} text-xl text-surface-400"></i>
    </div>
    <h3 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-1">{{ $title }}</h3>
    @if($text)<p class="text-xs text-surface-500 dark:text-surface-400 max-w-sm mx-auto">{{ $text }}</p>@endif
    @if($slot->isNotEmpty())<div class="mt-4">{{ $slot }}</div>@endif
</div>
