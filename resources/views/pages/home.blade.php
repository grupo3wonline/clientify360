@extends('layouts.public')

@section('title', 'Home')
@section('meta_description', 'Clientify360 - CRM com IA integrada para gestão de clientes. Score, análises, atendimentos e relatórios. De R$79,90 por R$39,90/mês.')

@section('content')
    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 dark:from-brand-900 dark:via-surface-900 dark:to-surface-950 text-white py-20 md:py-28 lg:py-36">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-80 h-80 bg-accent-400 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center px-3 py-1.5 bg-red-500/20 backdrop-blur-sm border border-red-400/30 rounded-full text-sm text-red-200 mb-6 animate-pulse">
                        <i class="fa-solid fa-fire mr-2 text-red-300"></i> Oferta por tempo limitado — 50% OFF
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6">
                        CRM com <span class="bg-gradient-to-r from-accent-300 to-accent-400 bg-clip-text text-transparent">IA Integrada</span> para Gestão de Clientes
                    </h1>
                    <p class="text-lg md:text-xl text-brand-200 mb-6 leading-relaxed max-w-xl">
                        Score de clientes, análises com inteligência artificial, gestão de atendimentos, agenda e relatórios. Tudo em uma plataforma segura e intuitiva.
                    </p>

                    <div class="flex items-center space-x-3 mb-8 bg-white/10 backdrop-blur-sm border border-white/20 rounded-xl px-5 py-3 w-fit">
                        <span class="text-brand-300 line-through text-lg">R$ 79,90</span>
                        <span class="text-3xl font-bold text-white">R$ 39,90<span class="text-sm font-normal text-brand-200">/mês</span></span>
                        <span class="px-2 py-0.5 bg-red-500 text-white text-xs font-bold rounded-full">-50%</span>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-7 py-4 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition shadow-xl shadow-black/10 text-base">
                            <i class="fa-solid fa-rocket mr-2"></i> Começar Grátis Agora
                        </a>
                        <a href="#planos" class="inline-flex items-center justify-center px-7 py-4 border-2 border-white/25 hover:border-white/50 text-white font-medium rounded-xl transition text-base backdrop-blur-sm">
                            <i class="fa-solid fa-tag mr-2 text-sm"></i> Ver Planos
                        </a>
                    </div>
                    <p class="text-xs text-brand-300 mt-4"><i class="fa-solid fa-shield-halved mr-1"></i> Sem cartão de crédito para começar. Cancele quando quiser.</p>
                </div>

                <div class="hidden lg:flex justify-center">
                    <div class="bg-white/5 backdrop-blur-md rounded-3xl p-8 border border-white/10 shadow-2xl">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4 bg-white/10 rounded-xl p-4">
                                <div class="w-10 h-10 bg-accent-500/20 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-brain text-accent-300"></i>
                                </div>
                                <div>
                                    <p class="font-medium">IA para análise de clientes</p>
                                    <p class="text-sm text-brand-300">Gemini, Groq e OpenRouter</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 bg-white/10 rounded-xl p-4">
                                <div class="w-10 h-10 bg-accent-500/20 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-ranking-star text-accent-300"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Score e classificação</p>
                                    <p class="text-sm text-brand-300">Ranqueie e qualifique leads</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 bg-white/10 rounded-xl p-4">
                                <div class="w-10 h-10 bg-accent-500/20 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-headset text-accent-300"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Gestão de atendimentos</p>
                                    <p class="text-sm text-brand-300">Controle total de interações</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 bg-white/10 rounded-xl p-4">
                                <div class="w-10 h-10 bg-accent-500/20 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-chart-line text-accent-300"></i>
                                </div>
                                <div>
                                    <p class="font-medium">Relatórios inteligentes</p>
                                    <p class="text-sm text-brand-300">Métricas e projeções</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scarcity Banner -->
    <section class="bg-red-600 dark:bg-red-900 py-3">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-white text-sm font-medium">
                <i class="fa-solid fa-clock mr-2 animate-pulse"></i>
                <strong>OFERTA LIMITADA:</strong> Plano Profissional de <span class="line-through">R$ 79,90</span> por apenas <strong>R$ 39,90/mês</strong> — Economize R$ 480/ano!
                <a href="{{ route('register') }}" class="ml-3 underline font-bold hover:no-underline">Aproveitar →</a>
            </p>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-20 md:py-28 bg-white dark:bg-surface-950" id="funcionalidades">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-3">Funcionalidades Completas</p>
                <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-4">Tudo que você precisa para crescer</h2>
                <p class="text-surface-500 dark:text-surface-400 max-w-2xl mx-auto text-lg">Plataforma completa com IA, score de clientes, atendimentos e muito mais.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-brand-200 dark:hover:border-brand-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-brain text-brand-600 dark:text-brand-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Análise com IA</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Integração com Gemini, Groq e OpenRouter para análise automática de perfil, projeções e qualificação.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">PRO</span>
                </div>

                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-accent-200 dark:hover:border-accent-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-accent-50 dark:bg-accent-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-ranking-star text-accent-600 dark:text-accent-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Score & Classificação</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Pontue e ranqueie clientes (Quente, Morno, Frio, VIP). Identifique os melhores leads.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">PRO</span>
                </div>

                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-purple-200 dark:hover:border-purple-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-headset text-purple-600 dark:text-purple-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Gestão de Atendimentos</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Registre interações por telefone, WhatsApp, e-mail ou reunião. Acompanhe status e resolução.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">PRO</span>
                </div>

                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-orange-200 dark:hover:border-orange-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-orange-50 dark:bg-orange-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-chart-pie text-orange-600 dark:text-orange-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Relatórios Avançados</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">KPIs, métricas de atendimento, demanda de serviços, ranking de clientes e análise por período.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">PRO</span>
                </div>

                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-teal-200 dark:hover:border-teal-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-teal-50 dark:bg-teal-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-check text-teal-600 dark:text-teal-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Agenda & Lembretes</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Agende follow-ups, ligações e reuniões. Alertas de lembretes atrasados e próximos compromissos.</p>
                </div>

                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-red-200 dark:hover:border-red-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-shield-halved text-red-600 dark:text-red-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Segurança & LGPD</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Dados isolados por usuário, criptografia, sessões seguras e conformidade total com a LGPD.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-20 md:py-28 bg-surface-50 dark:bg-surface-900" id="planos">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-flex items-center px-3 py-1.5 bg-red-100 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-full text-sm text-red-700 dark:text-red-300 mb-4 animate-pulse">
                    <i class="fa-solid fa-fire mr-2"></i> Promoção por tempo limitado — 50% de desconto
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-4">Escolha seu plano</h2>
                <p class="text-surface-500 dark:text-surface-400 text-lg">Comece grátis e faça upgrade quando quiser.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Basic -->
                <div class="bg-white dark:bg-surface-800 rounded-2xl border border-surface-200 dark:border-surface-700 p-8">
                    <h3 class="text-xl font-bold text-surface-900 dark:text-white">Básico</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">{{ config('plans.basic.description') }}</p>
                    <div class="mt-6 mb-8">
                        <span class="text-4xl font-bold text-surface-900 dark:text-white">Grátis</span>
                        <span class="text-surface-400 text-sm ml-1">para sempre</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach(config('plans.basic.features') as $feature)
                        <li class="flex items-center text-sm text-surface-600 dark:text-surface-400">
                            <i class="fa-solid fa-check text-accent-500 mr-2.5"></i> {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full text-center px-5 py-3 bg-surface-100 dark:bg-surface-700 hover:bg-surface-200 dark:hover:bg-surface-600 text-surface-700 dark:text-surface-300 font-medium rounded-xl transition">
                        Criar Conta Grátis
                    </a>
                </div>

                <!-- Pro -->
                <div class="bg-white dark:bg-surface-800 rounded-2xl border-2 border-brand-500 p-8 relative shadow-xl shadow-brand-500/10">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 bg-red-500 text-white text-xs font-bold rounded-full">
                        🔥 MAIS POPULAR — 50% OFF
                    </div>
                    <h3 class="text-xl font-bold text-surface-900 dark:text-white">Profissional</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">{{ config('plans.professional.description') }}</p>
                    <div class="mt-6 mb-2">
                        <span class="text-lg text-surface-400 line-through">R$ 79,90</span>
                    </div>
                    <div class="mb-8 flex items-baseline flex-wrap gap-2">
                        <span class="text-4xl font-bold text-surface-900 dark:text-white">R$ 39,90</span>
                        <span class="text-surface-400 text-sm">/mês</span>
                        <span class="px-2 py-0.5 bg-red-500 text-white text-[10px] font-bold rounded-full">ECONOMIZE R$ 480/ANO</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        @foreach(config('plans.professional.features') as $feature)
                        <li class="flex items-center text-sm text-surface-600 dark:text-surface-400">
                            <i class="fa-solid fa-check text-brand-500 mr-2.5"></i> {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}" class="block w-full text-center px-5 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl transition shadow-lg shadow-brand-600/25">
                        <i class="fa-solid fa-rocket mr-2"></i> Começar Agora — 50% OFF
                    </a>
                    <p class="text-center text-[10px] text-surface-400 mt-3">Cancele a qualquer momento. Sem fidelidade.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-20 md:py-28 bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 dark:from-brand-900 dark:to-surface-950 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 right-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div class="inline-flex items-center px-4 py-2 bg-red-500/20 backdrop-blur-sm border border-red-400/30 rounded-full text-sm text-red-200 mb-6">
                <i class="fa-solid fa-hourglass-half mr-2 animate-pulse"></i> Vagas limitadas com desconto de 50%
            </div>
            <h2 class="text-3xl md:text-4xl font-bold mb-5">Não perca essa oportunidade</h2>
            <p class="text-lg text-brand-200 mb-4">De <span class="line-through">R$ 79,90</span> por apenas <strong class="text-white text-2xl">R$ 39,90/mês</strong></p>
            <p class="text-brand-300 mb-10 max-w-2xl mx-auto">CRM completo com IA integrada, score de clientes, gestão de atendimentos e relatórios avançados. Tudo por menos de R$ 1,33/dia.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition shadow-xl text-lg">
                <i class="fa-solid fa-rocket mr-2"></i> Garantir Meu Desconto de 50%
            </a>
            <p class="mt-6 text-sm text-brand-300">
                <i class="fa-solid fa-building mr-1"></i>
                Desenvolvido por <strong>Grupo 3W Online</strong> — <a href="https://www.3wonline.com.br" target="_blank" class="underline hover:no-underline">www.3wonline.com.br</a>
            </p>
        </div>
    </section>
@endsection
