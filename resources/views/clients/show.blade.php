<x-app-layout>
    @section('title', $client->name)

    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <a href="{{ route('clients.index') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Clientes</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">{{ $client->name }}</span>
        </nav>
    </x-slot>

    <div class="max-w-6xl mx-auto">
        <!-- Client Header Card -->
        <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-800 dark:to-surface-900 p-6 md:p-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center text-white text-2xl font-bold border border-white/20">
                            {{ strtoupper(substr($client->name, 0, 1)) }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-white">{{ $client->name }}</h1>
                            <p class="text-brand-200 text-sm mt-1">
                                <i class="fa-solid fa-calendar mr-1"></i> Cadastrado em {{ $client->created_at->format('d/m/Y') }}
                                @if($client->business_segment)
                                    <span class="mx-2">•</span>
                                    <i class="fa-solid fa-briefcase mr-1"></i> {{ $client->business_segment }}
                                @endif
                            </p>
                            @if($client->sourceLabel() || $client->tags->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                    @if($client->sourceLabel())
                                        <span class="c-badge bg-white/15 text-white border border-white/20"><i class="fa-solid fa-bullseye mr-1"></i>{{ $client->sourceLabel() }}</span>
                                    @endif
                                    @foreach($client->tags as $tag)
                                        <a href="{{ route('clients.index', ['tag' => $tag->id]) }}" class="c-badge" style="background: {{ $tag->color }}; color: {{ $tag->textColor() }}">{{ $tag->name }}</a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('clients.edit', $client) }}" class="inline-flex items-center px-4 py-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium rounded-xl border border-white/20 transition">
                            <i class="fa-solid fa-pen-to-square mr-2 text-xs"></i> Editar
                        </a>
                        <form id="delete-form-{{ $client->id }}" action="{{ route('clients.destroy', $client) }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
                        <button onclick="Swal.fire({ title: 'Excluir cliente?', text: 'Esta ação não pode ser desfeita!', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Excluir', cancelButtonText: 'Cancelar', customClass: { popup: 'rounded-2xl' } }).then((r) => { if (r.isConfirmed) document.getElementById('delete-form-{{ $client->id }}').submit() })"
                                class="inline-flex items-center px-4 py-2 bg-danger-500/20 hover:bg-danger-500/30 text-white text-sm font-medium rounded-xl border border-danger-400/30 transition">
                            <i class="fa-solid fa-trash-can mr-2 text-xs"></i> Excluir
                        </button>
                    </div>
                </div>
            </div>

            <!-- Score & Classification Bar -->
            <div class="p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-surface-200 dark:border-surface-800">
                <div class="flex items-center space-x-6">
                    <!-- Score Circle -->
                    <div class="text-center">
                        <div class="w-16 h-16 rounded-full border-4 {{ $client->score >= 80 ? 'border-accent-500' : ($client->score >= 50 ? 'border-amber-500' : ($client->score > 0 ? 'border-surface-300 dark:border-surface-600' : 'border-surface-200 dark:border-surface-700')) }} flex items-center justify-center">
                            <span class="text-xl font-bold {{ $client->score >= 80 ? 'text-accent-600 dark:text-accent-400' : ($client->score >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-surface-500') }}">{{ $client->score }}</span>
                        </div>
                        <p class="text-[10px] text-surface-500 mt-1 uppercase tracking-wider font-medium">Score</p>
                    </div>
                    <!-- Classification -->
                    @if($client->score_label)
                    <div>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold
                            {{ $client->score_label === 'Quente' ? 'bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-300' : '' }}
                            {{ $client->score_label === 'VIP' ? 'bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300' : '' }}
                            {{ $client->score_label === 'Morno' ? 'bg-orange-100 dark:bg-orange-950/50 text-orange-700 dark:text-orange-300' : '' }}
                            {{ $client->score_label === 'Frio' ? 'bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300' : '' }}
                            {{ $client->score_label === 'Novo' ? 'bg-purple-100 dark:bg-purple-950/50 text-purple-700 dark:text-purple-300' : '' }}">
                            {{ $client->score_label === 'Quente' ? '🔥' : ($client->score_label === 'VIP' ? '⭐' : ($client->score_label === 'Morno' ? '🌤️' : ($client->score_label === 'Frio' ? '❄️' : '🆕'))) }}
                            {{ $client->score_label }}
                        </span>
                        <p class="text-[10px] text-surface-500 mt-1 uppercase tracking-wider font-medium">Classificação</p>
                    </div>
                    @endif
                    @if($client->company_size)
                    <div>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-surface-100 dark:bg-surface-800 text-surface-700 dark:text-surface-300">
                            <i class="fa-solid fa-building mr-1.5 text-xs"></i> {{ $client->company_size }}
                        </span>
                        <p class="text-[10px] text-surface-500 mt-1 uppercase tracking-wider font-medium">Porte</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Contact Info -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Contact Card -->
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
                    <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                        <i class="fa-solid fa-address-card text-brand-500 mr-2"></i> Dados de Contato
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-[10px] text-surface-400 uppercase tracking-wider font-medium mb-0.5">E-mail</p>
                            @if($client->email)
                                <a href="mailto:{{ $client->email }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">{{ $client->email }}</a>
                            @else
                                <p class="text-sm text-surface-400 italic">Não informado</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-[10px] text-surface-400 uppercase tracking-wider font-medium mb-0.5">Telefone</p>
                            @if($client->phone)
                                <a href="tel:{{ $client->phone }}" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">{{ $client->phone }}</a>
                            @else
                                <p class="text-sm text-surface-400 italic">Não informado</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-[10px] text-surface-400 uppercase tracking-wider font-medium mb-0.5">Documento</p>
                            <p class="text-sm text-surface-700 dark:text-surface-300">{{ $client->document ?: 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-surface-400 uppercase tracking-wider font-medium mb-0.5">Endereço</p>
                            <p class="text-sm text-surface-700 dark:text-surface-300">
                                {{ $client->address ?? '' }}
                                @if($client->city || $client->state)
                                    <br>{{ $client->city }}{{ $client->state ? '/' . $client->state : '' }} {{ $client->zip_code ? '- ' . $client->zip_code : '' }}
                                @endif
                                @if(!$client->address && !$client->city)
                                    <span class="italic text-surface-400">Não informado</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                @if($client->notes)
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
                    <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-3 flex items-center">
                        <i class="fa-solid fa-note-sticky text-amber-500 mr-2"></i> Observações
                    </h3>
                    <p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-line leading-relaxed">{{ $client->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Right Column: Analysis -->
            <div class="lg:col-span-2 space-y-6">
                <!-- SWOT-style Analysis -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Strengths -->
                    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-accent-200 dark:border-accent-800/50 p-5">
                        <h4 class="text-sm font-semibold text-accent-700 dark:text-accent-300 mb-3 flex items-center">
                            <i class="fa-solid fa-thumbs-up mr-2"></i> Pontos Fortes
                        </h4>
                        @if($client->strengths)
                            <p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-line leading-relaxed">{{ $client->strengths }}</p>
                        @else
                            <p class="text-xs text-surface-400 italic">Não analisado ainda. Edite o cliente para adicionar.</p>
                        @endif
                    </div>

                    <!-- Weaknesses -->
                    <div class="bg-white dark:bg-surface-900 rounded-2xl border border-red-200 dark:border-red-800/50 p-5">
                        <h4 class="text-sm font-semibold text-red-700 dark:text-red-300 mb-3 flex items-center">
                            <i class="fa-solid fa-thumbs-down mr-2"></i> Pontos Fracos
                        </h4>
                        @if($client->weaknesses)
                            <p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-line leading-relaxed">{{ $client->weaknesses }}</p>
                        @else
                            <p class="text-xs text-surface-400 italic">Não analisado ainda. Edite o cliente para adicionar.</p>
                        @endif
                    </div>
                </div>

                <!-- Opportunities -->
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-brand-200 dark:border-brand-800/50 p-5">
                    <h4 class="text-sm font-semibold text-brand-700 dark:text-brand-300 mb-3 flex items-center">
                        <i class="fa-solid fa-lightbulb mr-2"></i> Oportunidades de Negócio
                    </h4>
                    @if($client->opportunities)
                        <p class="text-sm text-surface-600 dark:text-surface-400 whitespace-pre-line leading-relaxed">{{ $client->opportunities }}</p>
                    @else
                        <p class="text-xs text-surface-400 italic">Não analisado ainda. Edite o cliente para adicionar oportunidades identificadas.</p>
                    @endif
                </div>

                <!-- Recommended Services -->
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
                    <h4 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-4 flex items-center">
                        <i class="fa-solid fa-wand-magic-sparkles text-purple-500 mr-2"></i> Serviços Indicados
                    </h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="flex items-center space-x-2 p-3 rounded-xl {{ $client->needs_website ? 'bg-brand-50 dark:bg-brand-950/30 border border-brand-200 dark:border-brand-800' : 'bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 opacity-50' }}">
                            <i class="fa-solid fa-globe {{ $client->needs_website ? 'text-brand-600 dark:text-brand-400' : 'text-surface-400' }}"></i>
                            <span class="text-xs font-medium {{ $client->needs_website ? 'text-brand-700 dark:text-brand-300' : 'text-surface-500' }}">Website</span>
                        </div>
                        <div class="flex items-center space-x-2 p-3 rounded-xl {{ $client->needs_system ? 'bg-purple-50 dark:bg-purple-950/30 border border-purple-200 dark:border-purple-800' : 'bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 opacity-50' }}">
                            <i class="fa-solid fa-laptop-code {{ $client->needs_system ? 'text-purple-600 dark:text-purple-400' : 'text-surface-400' }}"></i>
                            <span class="text-xs font-medium {{ $client->needs_system ? 'text-purple-700 dark:text-purple-300' : 'text-surface-500' }}">Sistema</span>
                        </div>
                        <div class="flex items-center space-x-2 p-3 rounded-xl {{ $client->needs_marketing ? 'bg-orange-50 dark:bg-orange-950/30 border border-orange-200 dark:border-orange-800' : 'bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 opacity-50' }}">
                            <i class="fa-solid fa-bullhorn {{ $client->needs_marketing ? 'text-orange-600 dark:text-orange-400' : 'text-surface-400' }}"></i>
                            <span class="text-xs font-medium {{ $client->needs_marketing ? 'text-orange-700 dark:text-orange-300' : 'text-surface-500' }}">Marketing</span>
                        </div>
                        <div class="flex items-center space-x-2 p-3 rounded-xl {{ $client->needs_ecommerce ? 'bg-accent-50 dark:bg-accent-950/30 border border-accent-200 dark:border-accent-800' : 'bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 opacity-50' }}">
                            <i class="fa-solid fa-cart-shopping {{ $client->needs_ecommerce ? 'text-accent-600 dark:text-accent-400' : 'text-surface-400' }}"></i>
                            <span class="text-xs font-medium {{ $client->needs_ecommerce ? 'text-accent-700 dark:text-accent-300' : 'text-surface-500' }}">E-commerce</span>
                        </div>
                        <div class="flex items-center space-x-2 p-3 rounded-xl {{ $client->needs_seo ? 'bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-800' : 'bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 opacity-50' }}">
                            <i class="fa-solid fa-magnifying-glass-chart {{ $client->needs_seo ? 'text-teal-600 dark:text-teal-400' : 'text-surface-400' }}"></i>
                            <span class="text-xs font-medium {{ $client->needs_seo ? 'text-teal-700 dark:text-teal-300' : 'text-surface-500' }}">SEO</span>
                        </div>
                        <div class="flex items-center space-x-2 p-3 rounded-xl {{ $client->needs_social_media ? 'bg-pink-50 dark:bg-pink-950/30 border border-pink-200 dark:border-pink-800' : 'bg-surface-50 dark:bg-surface-800 border border-surface-200 dark:border-surface-700 opacity-50' }}">
                            <i class="fa-solid fa-hashtag {{ $client->needs_social_media ? 'text-pink-600 dark:text-pink-400' : 'text-surface-400' }}"></i>
                            <span class="text-xs font-medium {{ $client->needs_social_media ? 'text-pink-700 dark:text-pink-300' : 'text-surface-500' }}">Redes Sociais</span>
                        </div>
                    </div>
                    @if(!$client->needs_website && !$client->needs_system && !$client->needs_marketing && !$client->needs_ecommerce && !$client->needs_seo && !$client->needs_social_media)
                        <p class="text-xs text-surface-400 italic mt-3">Nenhum serviço indicado ainda. Edite o cliente para marcar os serviços relevantes.</p>
                    @endif
                </div>

                <!-- Recent Reminders -->
                @php
                    $clientReminders = $client->reminders()->where('is_completed', false)->orderBy('remind_at')->take(3)->get();
                @endphp
                @if($clientReminders->count() > 0)
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
                    <h4 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-3 flex items-center">
                        <i class="fa-solid fa-bell text-amber-500 mr-2"></i> Próximos Lembretes
                    </h4>
                    <div class="space-y-2">
                        @foreach($clientReminders as $reminder)
                        <div class="flex items-center justify-between p-3 bg-surface-50 dark:bg-surface-800 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <i class="fa-regular fa-clock text-xs {{ $reminder->remind_at->isPast() ? 'text-danger-500' : 'text-brand-500' }}"></i>
                                <span class="text-sm text-surface-700 dark:text-surface-300">{{ $reminder->title }}</span>
                            </div>
                            <span class="text-xs {{ $reminder->remind_at->isPast() ? 'text-danger-600 dark:text-danger-400' : 'text-surface-500' }}">{{ $reminder->remind_at->format('d/m H:i') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        @include('clients._commercial')

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('clients.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-700 dark:text-surface-300 text-sm font-medium rounded-xl transition">
                <i class="fa-solid fa-arrow-left mr-2 text-xs"></i> Voltar para lista
            </a>
        </div>
    </div>
</x-app-layout>
