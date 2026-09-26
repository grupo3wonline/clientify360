@php
    $cls = ['open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300', 'won' => 'bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300', 'lost' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300'][$deal->status] ?? '';
@endphp
<span class="c-badge {{ $cls }}">{{ \App\Models\Deal::STATUS_LABELS[$deal->status] ?? $deal->status }}</span>
