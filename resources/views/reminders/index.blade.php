<x-app-layout>
    @section('title', 'Agenda')

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Agenda</span>
        </nav>
    </x-slot>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content -->
        <div class="flex-1">
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Agenda & Lembretes</h1>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Gerencie seus compromissos e lembretes</p>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="flex items-center space-x-1 mb-6 bg-surface-100 dark:bg-surface-800 rounded-xl p-1">
                <a href="{{ route('reminders.index', ['filter' => 'upcoming']) }}"
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'upcoming' ? 'bg-white dark:bg-surface-700 text-brand-700 dark:text-brand-300 shadow-sm' : 'text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-200' }}">
                    <i class="fa-solid fa-clock mr-2 text-xs"></i> Próximos
                    @if($counts['upcoming'] > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300">{{ $counts['upcoming'] }}</span>
                    @endif
                </a>
                <a href="{{ route('reminders.index', ['filter' => 'overdue']) }}"
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'overdue' ? 'bg-white dark:bg-surface-700 text-red-700 dark:text-red-300 shadow-sm' : 'text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-200' }}">
                    <i class="fa-solid fa-exclamation-triangle mr-2 text-xs"></i> Atrasados
                    @if($counts['overdue'] > 0)
                        <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300">{{ $counts['overdue'] }}</span>
                    @endif
                </a>
                <a href="{{ route('reminders.index', ['filter' => 'completed']) }}"
                   class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition {{ $filter === 'completed' ? 'bg-white dark:bg-surface-700 text-accent-700 dark:text-accent-300 shadow-sm' : 'text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-200' }}">
                    <i class="fa-solid fa-check-circle mr-2 text-xs"></i> Concluídos
                    <span class="ml-1.5 px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-surface-200 dark:bg-surface-700 text-surface-600 dark:text-surface-400">{{ $counts['completed'] }}</span>
                </a>
            </div>

            <!-- Reminders List -->
            <div class="space-y-3">
                @forelse($reminders as $reminder)
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-2 mb-1">
                                @if($reminder->is_completed)
                                    <i class="fa-solid fa-check-circle text-accent-500"></i>
                                @elseif($reminder->remind_at->isPast())
                                    <i class="fa-solid fa-exclamation-circle text-red-500"></i>
                                @else
                                    <i class="fa-regular fa-clock text-brand-500"></i>
                                @endif
                                <h3 class="text-sm font-semibold text-surface-900 dark:text-white truncate {{ $reminder->is_completed ? 'line-through opacity-60' : '' }}">
                                    {{ $reminder->title }}
                                </h3>
                            </div>

                            @if($reminder->description)
                                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1 line-clamp-2">{{ $reminder->description }}</p>
                            @endif

                            <div class="flex items-center flex-wrap gap-3 mt-3">
                                <span class="inline-flex items-center text-xs {{ $reminder->remind_at->isPast() && !$reminder->is_completed ? 'text-red-600 dark:text-red-400' : 'text-surface-500 dark:text-surface-400' }}">
                                    <i class="fa-solid fa-calendar mr-1.5"></i>
                                    {{ $reminder->remind_at->format('d/m/Y H:i') }}
                                </span>
                                @if($reminder->client)
                                    <span class="inline-flex items-center text-xs text-brand-600 dark:text-brand-400">
                                        <i class="fa-solid fa-user mr-1.5"></i>
                                        {{ $reminder->client->name }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center space-x-1 flex-shrink-0">
                            @if(!$reminder->is_completed)
                            <form action="{{ route('reminders.complete', $reminder) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="p-2 rounded-lg text-surface-400 hover:text-accent-600 hover:bg-accent-50 dark:hover:text-accent-400 dark:hover:bg-accent-950/30 transition" title="Concluir">
                                    <i class="fa-solid fa-check text-sm"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('reminders.destroy', $reminder) }}" method="POST" x-data>
                                @csrf
                                @method('DELETE')
                                <button type="button" @click="Swal.fire({ title: 'Excluir lembrete?', text: 'Esta ação não pode ser desfeita.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Excluir', cancelButtonText: 'Cancelar', customClass: { popup: 'rounded-2xl' } }).then((r) => { if (r.isConfirmed) $el.closest('form').submit() })"
                                        class="p-2 rounded-lg text-surface-400 hover:text-red-600 hover:bg-red-50 dark:hover:text-red-400 dark:hover:bg-red-950/30 transition" title="Excluir">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-10 text-center">
                    <div class="w-14 h-14 bg-surface-100 dark:bg-surface-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-calendar-xmark text-xl text-surface-400"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-1">
                        @if($filter === 'overdue') Nenhum lembrete atrasado
                        @elseif($filter === 'completed') Nenhum lembrete concluído
                        @else Nenhum lembrete agendado
                        @endif
                    </h3>
                    <p class="text-xs text-surface-500 dark:text-surface-400">Crie um lembrete usando o formulário ao lado.</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($reminders->hasPages())
            <div class="mt-4">{{ $reminders->withQueryString()->links() }}</div>
            @endif
        </div>

        <!-- Sidebar: Create Form -->
        <div class="lg:w-80 flex-shrink-0">
            <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 sticky top-24">
                <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                    <i class="fa-solid fa-plus-circle text-brand-500 mr-2"></i> Novo Lembrete
                </h3>

                <form action="{{ route('reminders.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Título <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required autocomplete="off"
                                   placeholder="Ex: Ligar para cliente"
                                   class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                            @error('title')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Descrição</label>
                            <textarea name="description" id="description" rows="2" autocomplete="off"
                                      placeholder="Detalhes do lembrete..."
                                      class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition resize-none">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="client_id" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Cliente (opcional)</label>
                            <select name="client_id" id="client_id" autocomplete="off"
                                    class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                <option value="">Nenhum cliente</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="remind_at" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Data e Hora <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="remind_at" id="remind_at" value="{{ old('remind_at') }}" required autocomplete="off"
                                   class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                            @error('remind_at')
                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                            <i class="fa-solid fa-plus mr-2 text-xs"></i> Criar Lembrete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
