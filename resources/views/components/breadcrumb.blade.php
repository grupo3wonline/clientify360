@props([
    'items' => [],
    'public' => false, // true: starts at "Início" (site); false: starts at "Dashboard" (app)
])
{{--
    Uso:
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'url' => route('clients.index')],
        ['label' => 'Novo cliente'],
    ]" />
--}}
<nav aria-label="Trilha de navegação" {{ $attributes->merge(['class' => 'mb-4']) }}>
    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-surface-500 dark:text-surface-400">
        <li class="flex items-center">
            <a href="{{ $public ? url('/') : route('dashboard') }}"
               class="inline-flex items-center gap-1.5 hover:text-brand-600 dark:hover:text-brand-400 transition"
               title="{{ $public ? 'Ir para a página inicial' : 'Ir para o painel' }}">
                <i class="fa-solid {{ $public ? 'fa-house' : 'fa-gauge-high' }} text-xs" aria-hidden="true"></i>
                <span>{{ $public ? 'Início' : 'Dashboard' }}</span>
            </a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-1.5 min-w-0">
                <i class="fa-solid fa-chevron-right text-[10px] text-surface-300 dark:text-surface-600" aria-hidden="true"></i>
                @if (! $loop->last && ! empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition truncate">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-medium text-surface-800 dark:text-surface-200 truncate" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
