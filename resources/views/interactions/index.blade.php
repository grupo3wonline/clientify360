<x-app-layout>
    @section('title', 'Atendimentos')

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Atendimentos</span>
        </nav>
    </x-slot>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Content -->
        <div class="flex-1">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Gestão de Atendimentos</h1>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Registre e acompanhe interações com clientes</p>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="flex items-center flex-wrap gap-1 mb-6 bg-surface-100 dark:bg-surface-800 rounded-xl p-1">
                <a href="{{ route('interactions.index', ['status' => 'all']) }}"
                   class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ $status === 'all' ? 'bg-white dark:bg-surface-700 text-surface-800 dark:text-surface-200 shadow-sm' : 'text-surface-600 dark:text-surface-400' }}">
                    Todos <span class="ml-1.5 px-1.5 py-0.5 text-[10px] rounded-full bg-surface-200 dark:bg-surface-600">{{ $counts['all'] }}</span>
                </a>
                <a href="{{ route('interactions.index', ['status' => 'open']) }}"
                   class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ $status === 'open' ? 'bg-white dark:bg-surface-700 text-danger-700 dark:text-danger-300 shadow-sm' : 'text-surface-600 dark:text-surface-400' }}">
                    <i class="fa-solid fa-circle text-[6px] text-danger-500 mr-1.5"></i> Abertos
                    @if($counts['open'] > 0)<span class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full bg-danger-100 dark:bg-danger-900 text-danger-700 dark:text-danger-300">{{ $counts['open'] }}</span>@endif
                </a>
                <a href="{{ route('interactions.index', ['status' => 'in_progress']) }}"
                   class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ $status === 'in_progress' ? 'bg-white dark:bg-surface-700 text-warning-700 dark:text-warning-300 shadow-sm' : 'text-surface-600 dark:text-surface-400' }}">
                    <i class="fa-solid fa-circle text-[6px] text-warning-500 mr-1.5"></i> Em Andamento
                    @if($counts['in_progress'] > 0)<span class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full bg-warning-100 dark:bg-warning-900 text-warning-700 dark:text-warning-300">{{ $counts['in_progress'] }}</span>@endif
                </a>
                <a href="{{ route('interactions.index', ['status' => 'resolved']) }}"
                   class="flex items-center px-3 py-2 rounded-lg text-xs font-medium transition {{ $status === 'resolved' ? 'bg-white dark:bg-surface-700 text-accent-700 dark:text-accent-300 shadow-sm' : 'text-surface-600 dark:text-surface-400' }}">
                    <i class="fa-solid fa-circle text-[6px] text-accent-500 mr-1.5"></i> Resolvidos
                    <span class="ml-1 px-1.5 py-0.5 text-[10px] rounded-full bg-surface-200 dark:bg-surface-600">{{ $counts['resolved'] }}</span>
                </a>
            </div>

            <!-- Interactions List -->
            <div class="space-y-3">
                @forelse($interactions as $interaction)
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-5 hover:shadow-md transition-shadow" x-data="{ showActions: false }">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center flex-wrap gap-2 mb-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                    {{ $interaction->status === 'open' ? 'bg-danger-100 dark:bg-danger-950/50 text-danger-700 dark:text-danger-300' : '' }}
                                    {{ $interaction->status === 'in_progress' ? 'bg-warning-100 dark:bg-warning-950/50 text-warning-700 dark:text-warning-300' : '' }}
                                    {{ $interaction->status === 'resolved' ? 'bg-accent-100 dark:bg-accent-950/50 text-accent-700 dark:text-accent-300' : '' }}
                                    {{ $interaction->status === 'closed' ? 'bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-400' : '' }}">
                                    {{ $interaction->status === 'open' ? 'Aberto' : ($interaction->status === 'in_progress' ? 'Em Andamento' : ($interaction->status === 'resolved' ? 'Resolvido' : 'Fechado')) }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-brand-50 dark:bg-brand-950/30 text-brand-700 dark:text-brand-300">
                                    {{ $interaction->type === 'support' ? 'Suporte' : ($interaction->type === 'sales' ? 'Vendas' : ($interaction->type === 'follow_up' ? 'Follow-up' : ($interaction->type === 'complaint' ? 'Reclamação' : 'Outro'))) }}
                                </span>
                                <span class="text-[10px] text-surface-400">
                                    <i class="fa-solid {{ $interaction->channel === 'phone' ? 'fa-phone' : ($interaction->channel === 'email' ? 'fa-envelope' : ($interaction->channel === 'whatsapp' ? 'fa-comment-dots' : ($interaction->channel === 'meeting' ? 'fa-handshake' : 'fa-comment'))) }} mr-0.5"></i>
                                    {{ $interaction->channel === 'phone' ? 'Telefone' : ($interaction->channel === 'email' ? 'E-mail' : ($interaction->channel === 'whatsapp' ? 'WhatsApp' : ($interaction->channel === 'meeting' ? 'Reunião' : 'Outro'))) }}
                                </span>
                            </div>
                            <h3 class="text-sm font-semibold text-surface-900 dark:text-white">{{ $interaction->subject }}</h3>
                            @if($interaction->description)
                                <p class="text-xs text-surface-500 dark:text-surface-400 mt-1 line-clamp-2">{{ $interaction->description }}</p>
                            @endif
                            <div class="flex items-center gap-3 mt-3">
                                <span class="text-xs text-brand-600 dark:text-brand-400"><i class="fa-solid fa-user mr-1"></i>{{ $interaction->client->name ?? 'Removido' }}</span>
                                <span class="text-xs text-surface-400"><i class="fa-solid fa-clock mr-1"></i>{{ $interaction->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($interaction->resolution_notes)
                                <div class="mt-3 p-3 bg-accent-50 dark:bg-accent-950/20 rounded-xl border border-accent-200 dark:border-accent-800/50">
                                    <p class="text-xs text-accent-700 dark:text-accent-300"><i class="fa-solid fa-check-circle mr-1"></i> <strong>Resolução:</strong> {{ $interaction->resolution_notes }}</p>
                                </div>
                            @endif
                        </div>
                        <button @click="showActions = !showActions" class="p-2 rounded-lg text-surface-400 hover:text-surface-600 hover:bg-surface-100 dark:hover:bg-surface-800 transition flex-shrink-0">
                            <i class="fa-solid fa-ellipsis-vertical text-sm"></i>
                        </button>
                    </div>
                    <div x-show="showActions" x-transition class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-800" x-cloak>
                        <div class="flex flex-wrap gap-2">
                            @if($interaction->status !== 'resolved' && $interaction->status !== 'closed')
                            <form action="{{ route('interactions.updateStatus', $interaction) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-warning-100 dark:bg-warning-950/30 text-warning-700 dark:text-warning-300 rounded-lg hover:bg-warning-200 transition">
                                    <i class="fa-solid fa-spinner mr-1"></i> Em Andamento
                                </button>
                            </form>
                            <form action="{{ route('interactions.updateStatus', $interaction) }}" method="POST" class="inline" x-data>
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="resolved">
                                <input type="hidden" name="resolution_notes" id="res-{{ $interaction->id }}">
                                <button type="button" @click="Swal.fire({ title: 'Resolver atendimento', input: 'textarea', inputLabel: 'Notas da resolução', inputPlaceholder: 'Descreva como foi resolvido...', showCancelButton: true, confirmButtonText: 'Resolver', cancelButtonText: 'Cancelar', confirmButtonColor: '#059669', customClass: { popup: 'rounded-2xl' } }).then((r) => { if (r.isConfirmed) { document.getElementById('res-{{ $interaction->id }}').value = r.value; $el.closest('form').submit(); } })"
                                        class="px-3 py-1.5 text-xs font-medium bg-accent-100 dark:bg-accent-950/30 text-accent-700 dark:text-accent-300 rounded-lg hover:bg-accent-200 transition">
                                    <i class="fa-solid fa-check mr-1"></i> Resolver
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('interactions.destroy', $interaction) }}" method="POST" class="inline" x-data>
                                @csrf @method('DELETE')
                                <button type="button" @click="Swal.fire({ title: 'Excluir?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Excluir', cancelButtonText: 'Cancelar', customClass: { popup: 'rounded-2xl' } }).then((r) => { if (r.isConfirmed) $el.closest('form').submit() })"
                                        class="px-3 py-1.5 text-xs font-medium bg-danger-100 dark:bg-danger-950/30 text-danger-700 dark:text-danger-300 rounded-lg hover:bg-danger-200 transition">
                                    <i class="fa-solid fa-trash mr-1"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-10 text-center">
                    <div class="w-14 h-14 bg-surface-100 dark:bg-surface-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-headset text-xl text-surface-400"></i>
                    </div>
                    <h3 class="text-sm font-semibold text-surface-700 dark:text-surface-300 mb-1">Nenhum atendimento registrado</h3>
                    <p class="text-xs text-surface-500 dark:text-surface-400">Registre um novo atendimento usando o formulário ao lado.</p>
                </div>
                @endforelse
            </div>

            @if($interactions->hasPages())
            <div class="mt-4">{{ $interactions->withQueryString()->links() }}</div>
            @endif
        </div>

        <!-- Sidebar: Create Form -->
        <div class="lg:w-80 flex-shrink-0">
            <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 sticky top-24">
                <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                    <i class="fa-solid fa-plus-circle text-brand-500 mr-2"></i> Novo Atendimento
                </h3>
                <form action="{{ route('interactions.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="client_id" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Cliente <span class="text-danger-500">*</span></label>
                            <select name="client_id" id="client_id" required autocomplete="off"
                                    class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                <option value="">Selecione</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="type" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Tipo <span class="text-danger-500">*</span></label>
                            <select name="type" id="type" required autocomplete="off"
                                    class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                <option value="">Selecione</option>
                                <option value="support" {{ old('type') === 'support' ? 'selected' : '' }}>Suporte</option>
                                <option value="sales" {{ old('type') === 'sales' ? 'selected' : '' }}>Vendas</option>
                                <option value="follow_up" {{ old('type') === 'follow_up' ? 'selected' : '' }}>Follow-up</option>
                                <option value="complaint" {{ old('type') === 'complaint' ? 'selected' : '' }}>Reclamação</option>
                                <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>Outro</option>
                            </select>
                            @error('type')<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="channel" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Canal <span class="text-danger-500">*</span></label>
                            <select name="channel" id="channel" required autocomplete="off"
                                    class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                                <option value="phone" {{ old('channel', 'phone') === 'phone' ? 'selected' : '' }}>📞 Telefone</option>
                                <option value="email" {{ old('channel') === 'email' ? 'selected' : '' }}>📧 E-mail</option>
                                <option value="whatsapp" {{ old('channel') === 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp</option>
                                <option value="meeting" {{ old('channel') === 'meeting' ? 'selected' : '' }}>🤝 Reunião</option>
                                <option value="other" {{ old('channel') === 'other' ? 'selected' : '' }}>💭 Outro</option>
                            </select>
                        </div>
                        <div>
                            <label for="subject" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Assunto <span class="text-danger-500">*</span></label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required autocomplete="off"
                                   placeholder="Resumo do atendimento"
                                   class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                            @error('subject')<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="description" class="block text-xs font-medium text-surface-700 dark:text-surface-300 mb-1">Descrição</label>
                            <textarea name="description" id="description" rows="3" autocomplete="off"
                                      placeholder="Detalhes do atendimento..."
                                      class="w-full px-3 py-2 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition resize-none">{{ old('description') }}</textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                            <i class="fa-solid fa-plus mr-2 text-xs"></i> Registrar Atendimento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
