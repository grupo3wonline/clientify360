@php
    $cls = ['open' => 'bg-info-100 text-info-700 dark:bg-info-900/40 dark:text-info-300', 'won' => 'bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300', 'lost' => 'bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300'][$deal->status] ?? '';
@endphp
<span class="c-badge {{ $cls }}">{{ \App\Models\Deal::STATUS_LABELS[$deal->status] ?? $deal->status }}</span>
