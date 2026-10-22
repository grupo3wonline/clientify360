@props(['status'])

@if ($status)
    <div role="status" aria-live="polite" {{ $attributes->merge(['class' => 'font-medium text-sm text-success-700 dark:text-success-400']) }}>
        {{ $status }}
    </div>
@endif
