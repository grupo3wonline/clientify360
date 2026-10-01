@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-brand-400 dark:border-brand-600 text-start text-base font-medium text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-900/50 focus:outline-none focus:text-brand-800 dark:focus:text-brand-200 focus:bg-brand-100 dark:focus:bg-brand-900 focus:border-brand-700 dark:focus:border-brand-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-surface-600 dark:text-surface-400 hover:text-surface-800 dark:hover:text-surface-200 hover:bg-surface-50 dark:hover:bg-surface-700 hover:border-surface-300 dark:hover:border-surface-600 focus:outline-none focus:text-surface-800 dark:focus:text-surface-200 focus:bg-surface-50 dark:focus:bg-surface-700 focus:border-surface-300 dark:focus:border-surface-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
