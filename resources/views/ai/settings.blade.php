<x-app-layout>
    @section('title', 'Configurações IA')
    <x-slot name="header">
        <nav class="text-sm text-surface-500 dark:text-surface-400" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-600 dark:hover:text-brand-400 transition">Dashboard</a>
            <span class="mx-1.5 text-surface-300 dark:text-surface-600">/</span>
            <span class="text-surface-800 dark:text-surface-200 font-medium">Configurações IA</span>
        </nav>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-surface-900 dark:text-white">Inteligência Artificial</h1>
            <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Configure suas credenciais para análises com IA</p>
        </div>

        @if(!$isProfessional)
        <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-2xl p-6 mb-6">
            <div class="flex items-start space-x-4">
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900/50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-lock text-amber-600 dark:text-amber-400"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-amber-800 dark:text-amber-200">Recurso Exclusivo — Plano Profissional</h3>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">A análise com IA está disponível apenas no plano Profissional. Faça upgrade para acessar.</p>
                    <a href="{{ route('billing.index') }}" class="inline-flex items-center mt-3 px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-medium rounded-xl transition">
                        <i class="fa-solid fa-rocket mr-2"></i> Fazer Upgrade
                    </a>
                </div>
            </div>
        </div>
        @else
        @if(count($providers) > 0)
        <div class="bg-accent-50 dark:bg-accent-950/30 border border-accent-200 dark:border-accent-800/50 rounded-2xl p-4 mb-6">
            <p class="text-sm text-accent-800 dark:text-accent-200"><i class="fa-solid fa-check-circle mr-2"></i> <strong>{{ count($providers) }}</strong> provedor(es) configurado(s):</p>
            <div class="flex flex-wrap gap-2 mt-2">
                @foreach($providers as $provider)
                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium bg-white dark:bg-surface-800 rounded-lg border border-accent-200 dark:border-accent-800">
                    <i class="{{ $provider['icon'] }} mr-1.5"></i> {{ $provider['name'] }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        <form action="{{ route('ai.saveSettings') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-950/50 rounded-lg flex items-center justify-center">
                            <i class="fa-brands fa-google text-blue-600 dark:text-blue-400 text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200">Google Gemini 2.0 Flash</h3>
                            <p class="text-[10px] text-surface-400">generativelanguage.googleapis.com</p>
                        </div>
                    </div>
                    <input type="password" name="ai_gemini_key" value="{{ $user->ai_gemini_key ? '••••••••••' : '' }}" autocomplete="off"
                           placeholder="Cole sua API Key do Google AI Studio"
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition"
                           onfocus="if(this.value==='••••••••••')this.value=''">
                    <p class="text-[10px] text-surface-400 mt-1.5">Obtenha em: <a href="https://aistudio.google.com/apikey" target="_blank" class="text-brand-600 hover:underline">aistudio.google.com/apikey</a></p>
                </div>

                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-orange-100 dark:bg-orange-950/50 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-bolt text-orange-600 dark:text-orange-400 text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200">Groq — Llama 3.3 70B Versatile</h3>
                            <p class="text-[10px] text-surface-400">api.groq.com</p>
                        </div>
                    </div>
                    <input type="password" name="ai_groq_key" value="{{ $user->ai_groq_key ? '••••••••••' : '' }}" autocomplete="off"
                           placeholder="Cole sua API Key do Groq"
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition"
                           onfocus="if(this.value==='••••••••••')this.value=''">
                    <p class="text-[10px] text-surface-400 mt-1.5">Obtenha em: <a href="https://console.groq.com/keys" target="_blank" class="text-brand-600 hover:underline">console.groq.com/keys</a></p>
                </div>

                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-950/50 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-route text-purple-600 dark:text-purple-400 text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200">OpenRouter — Gemma 3n (Gratuito)</h3>
                            <p class="text-[10px] text-surface-400">openrouter.ai</p>
                        </div>
                    </div>
                    <input type="password" name="ai_openrouter_key" value="{{ $user->ai_openrouter_key ? '••••••••••' : '' }}" autocomplete="off"
                           placeholder="Cole sua API Key do OpenRouter"
                           class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition"
                           onfocus="if(this.value==='••••••••••')this.value=''">
                    <p class="text-[10px] text-surface-400 mt-1.5">Obtenha em: <a href="https://openrouter.ai/keys" target="_blank" class="text-brand-600 hover:underline">openrouter.ai/keys</a></p>
                </div>

                <div class="bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6">
                    <h3 class="text-sm font-semibold text-surface-800 dark:text-surface-200 mb-3">Provedor Preferido</h3>
                    <select name="ai_preferred_provider" autocomplete="off"
                            class="w-full px-4 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition">
                        <option value="gemini" {{ $user->ai_preferred_provider === 'gemini' ? 'selected' : '' }}>Google Gemini 2.0 Flash</option>
                        <option value="groq" {{ $user->ai_preferred_provider === 'groq' ? 'selected' : '' }}>Groq Llama 3.3 70B</option>
                        <option value="openrouter" {{ $user->ai_preferred_provider === 'openrouter' ? 'selected' : '' }}>OpenRouter Gemma 3n</option>
                    </select>
                </div>

                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-sm shadow-brand-600/25">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Salvar Configurações
                </button>
            </div>
        </form>
        @endif
    </div>
</x-app-layout>
