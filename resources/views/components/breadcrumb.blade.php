@props([
    'items' => [],
])

{{--
    Uso:
    <x-breadcrumb :items="[
        ['label' => 'Clientes', 'url' => route('clients.index')],
        ['label' => 'Novo Cliente'],
    ]" />
--}}

<nav aria-label="Trilha de navegação" class="mb-4">
    <ol class="flex flex-wrap items-center gap-1.5 text-sm text-surface-500 dark:text-surface-400">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-1.5 hover:text-brand-600 dark:hover:text-brand-400 transition"
               title="Ir para o painel">
                <i class="fa-solid fa-house text-xs"></i>
                <span class="sr-only sm:not-sr-only">Início</span>
            </a>
        </li>

        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <i class="fa-solid fa-chevron-right text-[10px] text-surface-300 dark:text-surface-600"></i>
                @if (! $loop->last && ! empty($item['url']))
                    <a href="{{ $item['url'] }}"
                       class="hover:text-brand-600 dark:hover:text-brand-400 transition">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-medium text-surface-800 dark:text-surface-200" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
