<x-app-layout>
    @section('title', 'Clientes')

    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
                <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
                <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
                <span class="text-surface-800 dark:text-surface-200 font-medium">Clientes</span>
            </nav>
        </div>
    </x-slot>

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Clientes</h1>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Gerencie sua base de clientes</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isProfessional())
                <a href="{{ route('export.clients') }}"
                   class="inline-flex items-center px-4 py-2.5 bg-accent-600 hover:bg-accent-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-accent-600/25"
                   title="Exportar clientes em CSV">
                    <i class="fa-solid fa-file-csv mr-2 text-xs"></i> Exportar
                </a>
            @endif
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                <i class="fa-solid fa-user-plus mr-2 text-xs"></i> Novo Cliente
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="mb-6">
        <form action="{{ route('clients.index') }}" method="GET">
            @if($tagId ?? false)<input type="hidden" name="tag" value="{{ $tagId }}">@endif
            <div class="relative max-w-lg">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       autocomplete="off"
                       placeholder="Buscar por nome, e-mail, telefone, documento ou cidade..."
                       class="w-full pl-11 pr-10 py-3 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 text-surface-800 dark:text-surface-200 placeholder-surface-400 shadow-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition"
                       aria-label="Buscar clientes">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-surface-400 text-sm"></i>
                </div>
                @if($search ?? false)
                    <a href="{{ route('clients.index') }}" class="absolute inset-y-0 right-0 pr-4 flex items-center text-surface-400 hover:text-surface-600 transition" title="Limpar busca">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>
        </form>

        @if(isset($tags) && $tags->isNotEmpty())
            <div class="flex flex-wrap items-center gap-1.5 mt-3" aria-label="Filtrar por tag">
                <span class="text-xs text-surface-500 mr-1"><i class="fa-solid fa-tags mr-1"></i>Tags:</span>
                @foreach($tags as $tag)
                    @php $isOn = ($tagId ?? null) === $tag->id; @endphp
                    <a href="{{ route('clients.index', array_filter(['search' => $search, 'tag' => $isOn ? null : $tag->id])) }}"
                       class="c-badge border transition {{ $isOn ? 'text-white border-transparent' : 'border-surface-200 dark:border-surface-700 text-surface-600 dark:text-surface-300 hover:border-surface-400' }}"
                       @if($isOn) style="background: {{ $tag->color }}" aria-current="true" @endif>
                        @unless($isOn)<span class="w-1.5 h-1.5 rounded-full mr-1" style="background: {{ $tag->color }}"></span>@endunless{{ $tag->name }}
                        @if($isOn)<i class="fa-solid fa-xmark ml-1"></i>@endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if($search ?? false)
        <div class="mb-4 inline-flex items-center px-3 py-1.5 bg-brand-50 dark:bg-brand-950/30 border border-brand-200 dark:border-brand-800 rounded-lg text-sm text-brand-700 dark:text-brand-300">
            <i class="fa-solid fa-filter mr-2 text-xs"></i>
            Resultados para: <strong class="ml-1">"{{ $search }}"</strong>
            <span class="ml-2 text-surface-400">({{ $clients->total() }} {{ $clients->total() === 1 ? 'resultado' : 'resultados' }})</span>
        </div>
    @endif

    <!-- Clients Table -->
    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
        @if($clients->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-surface-200 dark:border-surface-800">
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Nome</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider hidden md:table-cell">E-mail</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider hidden lg:table-cell">Telefone</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider hidden lg:table-cell">Cidade/UF</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider hidden md:table-cell">Score</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                        @foreach($clients as $client)
                            <tr class="hover:bg-surface-50 dark:hover:bg-surface-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                            {{ strtoupper(substr($client->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-surface-900 dark:text-white">{{ $client->name }}</p>
                                            <p class="text-xs text-surface-500 dark:text-surface-400 md:hidden">{{ $client->email ?? '—' }}</p>
                                            @if($client->tags->isNotEmpty())
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach($client->tags as $tag)<span class="c-badge text-white text-[10px]" style="background: {{ $tag->color }}">{{ $tag->name }}</span>@endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden md:table-cell">
                                    <span class="text-sm text-surface-600 dark:text-surface-400">{{ $client->email ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <span class="text-sm text-surface-600 dark:text-surface-400">{{ $client->phone ?? '—' }}</span>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <span class="text-sm text-surface-600 dark:text-surface-400">
                                        @if($client->city && $client->state)
                                            {{ $client->city }}/{{ $client->state }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 hidden md:table-cell text-center">
                                    @if($client->score > 0)
                                        <div class="inline-flex items-center space-x-1.5">
                                            <span class="text-sm font-bold {{ $client->score >= 80 ? 'text-accent-600 dark:text-accent-400' : ($client->score >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-surface-500') }}">{{ $client->score }}</span>
                                            @if($client->score_label)
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $client->score_label === 'Quente' ? 'bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-300' : ($client->score_label === 'VIP' ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300' : ($client->score_label === 'Frio' ? 'bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300' : 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400')) }}">{{ $client->score_label }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-surface-300 dark:text-surface-600">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-1">
                                        <a href="{{ route('clients.show', $client) }}"
                                           class="p-2 rounded-lg text-surface-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:text-brand-400 dark:hover:bg-brand-950/30 transition"
                                           title="Visualizar">
                                            <i class="fa-solid fa-eye text-sm"></i>
                                        </a>
                                        <a href="{{ route('clients.edit', $client) }}"
                                           class="p-2 rounded-lg text-surface-400 hover:text-amber-600 hover:bg-amber-50 dark:hover:text-amber-400 dark:hover:bg-amber-950/30 transition"
                                           title="Editar">
                                            <i class="fa-solid fa-pen-to-square text-sm"></i>
                                        </a>
                                        <form id="delete-form-{{ $client->id }}" action="{{ route('clients.destroy', $client) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button onclick="confirmDelete('{{ $client->id }}')"
                                                class="p-2 rounded-lg text-surface-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:text-danger-400 dark:hover:bg-danger-950/30 transition"
                                                title="Excluir">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-800">
                {{ $clients->withQueryString()->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-surface-100 dark:bg-surface-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-users-slash text-2xl text-surface-400 dark:text-surface-500"></i>
                </div>
                @if($search ?? false)
                    <h3 class="text-base font-semibold text-surface-700 dark:text-surface-300 mb-2">Nenhum cliente encontrado</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-5">
                        Nenhum resultado para "<strong>{{ $search }}</strong>". Tente outro termo.
                    </p>
                    <a href="{{ route('clients.index') }}" class="inline-flex items-center px-4 py-2.5 bg-surface-100 dark:bg-surface-800 hover:bg-surface-200 dark:hover:bg-surface-700 text-surface-700 dark:text-surface-300 text-sm font-medium rounded-xl transition">
                        <i class="fa-solid fa-arrow-left mr-2 text-xs"></i> Ver todos os clientes
                    </a>
                @else
                    <h3 class="text-base font-semibold text-surface-700 dark:text-surface-300 mb-2">Nenhum cliente cadastrado</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-5">Comece cadastrando seu primeiro cliente.</p>
                    <a href="{{ route('clients.create') }}" class="inline-flex items-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm">
                        <i class="fa-solid fa-user-plus mr-2 text-xs"></i> Cadastrar Cliente
                    </a>
                @endif
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: 'Esta ação não poderá ser desfeita!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'rounded-2xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>
