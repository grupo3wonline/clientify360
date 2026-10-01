@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-accent-600 dark:text-accent-400']) }}>
        {{ $status }}
    </div>
@endif
