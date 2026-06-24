@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition shadow-sm']) }}>
