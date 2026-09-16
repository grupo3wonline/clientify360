@extends('layouts.public')

@section('title', 'CRM com IA Integrada')
@section('meta_description', 'Clientify360 - CRM com IA integrada para gestão de clientes. Score, análises, atendimentos e relatórios. De R$79,90 por R$39,90/mês. Comece grátis.')

@section('content')
    {{-- ============================================================= --}}
    {{-- HERO --}}
    {{-- ============================================================= --}}
    <section id="inicio" class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 dark:from-brand-900 dark:via-surface-900 dark:to-surface-950 text-white py-20 md:py-28 lg:py-36 scroll-mt-16">
        <div class="absolute inset-0 opacity-10" aria-hidden="true">
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

    {{-- Scarcity Banner --}}
    <section class="bg-red-600 dark:bg-red-900 py-3">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-white text-sm font-medium">
                <i class="fa-solid fa-clock mr-2 animate-pulse"></i>
                <strong>OFERTA LIMITADA:</strong> Plano Profissional de <span class="line-through">R$ 79,90</span> por apenas <strong>R$ 39,90/mês</strong> — Economize R$ 480/ano!
                <a href="{{ route('register') }}" class="ml-3 underline font-bold hover:no-underline">Aproveitar →</a>
            </p>
        </div>
    </section>

    {{-- ============================================================= --}}
    {{-- SOBRE (merged from about.blade.php) --}}
    {{-- ============================================================= --}}
    <section id="sobre" class="py-20 md:py-28 bg-white dark:bg-surface-950 scroll-mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-3">Sobre o Clientify360</p>
                    <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-6">Democratizar a gestão inteligente de clientes</h2>
                    <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-4">O Clientify360 nasceu para resolver um problema real: profissionais e pequenas empresas precisam de ferramentas poderosas de CRM, mas não podem pagar por soluções complexas e caras.</p>
                    <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-4">Nossa plataforma combina o poder da inteligência artificial com uma interface simples e intuitiva, permitindo que qualquer profissional gerencie seus clientes como as grandes empresas — mas por uma fração do custo.</p>
                    <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-8">Com integração a Gemini, Groq e OpenRouter, oferecemos análises automáticas de perfil, qualificação de leads e projeções de negócio que antes só eram possíveis com equipes de dados dedicadas.</p>

                    {{-- Valores --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-2xl flex items-center justify-center mb-3">
                                <i class="fa-solid fa-lock text-brand-600 dark:text-brand-400"></i>
                            </div>
                            <h3 class="font-bold text-surface-900 dark:text-white text-sm mb-1">Privacidade em Primeiro Lugar</h3>
                            <p class="text-xs text-surface-500 dark:text-surface-400">Seus dados são seus. Cada usuário acessa apenas suas próprias informações, com criptografia e conformidade LGPD.</p>
                        </div>
                        <div>
                            <div class="w-12 h-12 bg-accent-50 dark:bg-accent-950/50 rounded-2xl flex items-center justify-center mb-3">
                                <i class="fa-solid fa-rocket text-accent-600 dark:text-accent-400"></i>
                            </div>
                            <h3 class="font-bold text-surface-900 dark:text-white text-sm mb-1">Inovação Acessível</h3>
                            <p class="text-xs text-surface-500 dark:text-surface-400">Tecnologia de ponta (IA, análise de dados, automação) disponível para todos, sem complexidade.</p>
                        </div>
                        <div>
                            <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-2xl flex items-center justify-center mb-3">
                                <i class="fa-solid fa-handshake text-purple-600 dark:text-purple-400"></i>
                            </div>
                            <h3 class="font-bold text-surface-900 dark:text-white text-sm mb-1">Foco no Resultado</h3>
                            <p class="text-xs text-surface-500 dark:text-surface-400">Cada funcionalidade foi pensada para gerar resultado: mais vendas, melhor atendimento e clientes mais satisfeitos.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-800">
                        <i class="fa-solid fa-brain text-3xl text-brand-500 mb-3"></i>
                        <p class="text-2xl font-bold text-surface-900 dark:text-white">3</p>
                        <p class="text-xs text-surface-500">IAs Integradas</p>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-800">
                        <i class="fa-solid fa-shield-halved text-3xl text-accent-500 mb-3"></i>
                        <p class="text-2xl font-bold text-surface-900 dark:text-white">100%</p>
                        <p class="text-xs text-surface-500">Conforme LGPD</p>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-800">
                        <i class="fa-solid fa-mobile-screen text-3xl text-purple-500 mb-3"></i>
                        <p class="text-2xl font-bold text-surface-900 dark:text-white">100%</p>
                        <p class="text-xs text-surface-500">Responsivo</p>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-800">
                        <i class="fa-solid fa-bolt text-3xl text-amber-500 mb-3"></i>
                        <p class="text-2xl font-bold text-surface-900 dark:text-white">24/7</p>
                        <p class="text-xs text-surface-500">Disponível</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================= --}}
    {{-- FUNCIONALIDADES (Features grid from home) --}}
    {{-- ============================================================= --}}
    <section class="py-20 md:py-28 bg-surface-50 dark:bg-surface-900" id="funcionalidades">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-3">Funcionalidades Completas</p>
                <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-4">Tudo que você precisa para crescer</h2>
                <p class="text-surface-500 dark:text-surface-400 max-w-2xl mx-auto text-lg">Plataforma completa com IA, score de clientes, atendimentos e muito mais.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-brand-200 dark:hover:border-brand-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-brain text-brand-600 dark:text-brand-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Análise com IA</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Integração com Gemini, Groq e OpenRouter para análise automática de perfil, projeções e qualificação.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">PRO</span>
                </div>

                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-accent-200 dark:hover:border-accent-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-accent-50 dark:bg-accent-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-ranking-star text-accent-600 dark:text-accent-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Score & Classificação</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Pontue e ranqueie clientes (Quente, Morno, Frio, VIP). Identifique os melhores leads.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">PRO</span>
                </div>

                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-purple-200 dark:hover:border-purple-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-headset text-purple-600 dark:text-purple-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Gestão de Atendimentos</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Registre interações por telefone, WhatsApp, e-mail ou reunião. Acompanhe status e resolução.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">PRO</span>
                </div>

                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-orange-200 dark:hover:border-orange-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-orange-50 dark:bg-orange-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-chart-pie text-orange-600 dark:text-orange-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Relatórios Avançados</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">KPIs, métricas de atendimento, demanda de serviços, ranking de clientes e análise por período.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full">PRO</span>
                </div>

                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-teal-200 dark:hover:border-teal-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-teal-50 dark:bg-teal-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-calendar-check text-teal-600 dark:text-teal-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Agenda & Lembretes</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Agende follow-ups, ligações e reuniões. Alertas de lembretes atrasados e próximos compromissos.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-accent-100 dark:bg-accent-900 text-accent-700 dark:text-accent-300 rounded-full">GRÁTIS</span>
                </div>

                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:border-red-200 dark:hover:border-red-800 hover:shadow-lg transition-all group">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-950/50 rounded-xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-shield-halved text-red-600 dark:text-red-400 text-lg"></i>
                    </div>
                    <h3 class="text-lg font-bold text-surface-900 dark:text-white mb-2">Segurança & LGPD</h3>
                    <p class="text-surface-500 dark:text-surface-400 text-sm leading-relaxed">Dados isolados por usuário, criptografia, sessões seguras e conformidade total com a LGPD.</p>
                    <span class="inline-flex items-center mt-3 px-2 py-0.5 text-[10px] font-bold bg-accent-100 dark:bg-accent-900 text-accent-700 dark:text-accent-300 rounded-full">GRÁTIS</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================= --}}
    {{-- SERVIÇOS (merged from services.blade.php) --}}
    {{-- ============================================================= --}}
    <section id="servicos" class="py-20 md:py-28 bg-white dark:bg-surface-950 scroll-mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-3">Nossos Serviços</p>
                <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-4">Recursos que transformam sua gestão</h2>
                <p class="text-surface-500 dark:text-surface-400 max-w-2xl mx-auto text-lg">Conheça em detalhes as funcionalidades que vão impulsionar suas vendas.</p>
            </div>

            <div class="space-y-16">
                {{-- IA --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full mb-3">PRO</span>
                        <h3 class="text-2xl font-bold text-surface-900 dark:text-white mb-4"><i class="fa-solid fa-brain text-brand-500 mr-2"></i>Análise com Inteligência Artificial</h3>
                        <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-4">Utilize modelos de IA de última geração para analisar automaticamente o perfil dos seus clientes. Identifique pontos fortes, fracos, oportunidades de negócio e serviços recomendados.</p>
                        <ul class="space-y-2 text-sm text-surface-600 dark:text-surface-400">
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Google Gemini 2.0 Flash</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Groq Llama 3.3 70B Versatile</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>OpenRouter Gemma 3n (Gratuito)</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Cada usuário usa suas próprias credenciais</li>
                        </ul>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-8 border border-surface-200 dark:border-surface-800">
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3 p-3 bg-white dark:bg-surface-800 rounded-xl"><i class="fa-solid fa-thumbs-up text-accent-500"></i><span class="text-sm">Pontos Fortes identificados</span></div>
                            <div class="flex items-center space-x-3 p-3 bg-white dark:bg-surface-800 rounded-xl"><i class="fa-solid fa-thumbs-down text-red-500"></i><span class="text-sm">Pontos Fracos detectados</span></div>
                            <div class="flex items-center space-x-3 p-3 bg-white dark:bg-surface-800 rounded-xl"><i class="fa-solid fa-lightbulb text-amber-500"></i><span class="text-sm">Oportunidades de negócio</span></div>
                            <div class="flex items-center space-x-3 p-3 bg-white dark:bg-surface-800 rounded-xl"><i class="fa-solid fa-wand-magic-sparkles text-purple-500"></i><span class="text-sm">Serviços recomendados</span></div>
                        </div>
                    </div>
                </div>

                {{-- Score --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div class="order-2 lg:order-1 bg-surface-50 dark:bg-surface-900 rounded-2xl p-8 border border-surface-200 dark:border-surface-800 text-center">
                        <div class="inline-flex items-center space-x-3 flex-wrap justify-center gap-2">
                            <span class="px-3 py-1.5 bg-red-100 dark:bg-red-950/50 text-red-700 rounded-full text-sm font-semibold">🔥 Quente</span>
                            <span class="px-3 py-1.5 bg-orange-100 dark:bg-orange-950/50 text-orange-700 rounded-full text-sm font-semibold">🌤️ Morno</span>
                            <span class="px-3 py-1.5 bg-blue-100 dark:bg-blue-950/50 text-blue-700 rounded-full text-sm font-semibold">❄️ Frio</span>
                            <span class="px-3 py-1.5 bg-amber-100 dark:bg-amber-950/50 text-amber-700 rounded-full text-sm font-semibold">⭐ VIP</span>
                        </div>
                        <p class="text-6xl font-bold text-brand-600 mt-6">85<span class="text-lg text-surface-400">/100</span></p>
                        <p class="text-sm text-surface-500 mt-2">Score de exemplo</p>
                    </div>
                    <div class="order-1 lg:order-2">
                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full mb-3">PRO</span>
                        <h3 class="text-2xl font-bold text-surface-900 dark:text-white mb-4"><i class="fa-solid fa-ranking-star text-accent-500 mr-2"></i>Score & Classificação</h3>
                        <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-4">Atribua pontuações e classifique seus clientes para priorizar os mais valiosos. Identifique leads quentes e foque onde há mais retorno.</p>
                        <ul class="space-y-2 text-sm text-surface-600 dark:text-surface-400">
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Score numérico (0-100)</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Classificação visual (Quente/Morno/Frio/VIP)</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Análise SWOT por cliente</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Serviços indicados por perfil</li>
                        </ul>
                    </div>
                </div>

                {{-- Atendimentos --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full mb-3">PRO</span>
                        <h3 class="text-2xl font-bold text-surface-900 dark:text-white mb-4"><i class="fa-solid fa-headset text-purple-500 mr-2"></i>Gestão de Atendimentos</h3>
                        <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-4">Registre interações por telefone, WhatsApp, e-mail ou reunião. Controle status e resolução de cada atendimento.</p>
                        <ul class="space-y-2 text-sm text-surface-600 dark:text-surface-400">
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Tipos: Suporte, Vendas, Follow-up, Reclamação</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Canais: Telefone, WhatsApp, E-mail, Reunião</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Fluxo: Aberto → Em Andamento → Resolvido</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Notas de resolução e histórico</li>
                        </ul>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-8 border border-surface-200 dark:border-surface-800">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-surface-800 rounded-xl"><span class="text-sm">📞 Ligação com João</span><span class="px-2 py-0.5 bg-accent-100 text-accent-700 text-[10px] rounded-full font-semibold">Resolvido</span></div>
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-surface-800 rounded-xl"><span class="text-sm">💬 WhatsApp - Maria</span><span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] rounded-full font-semibold">Em Andamento</span></div>
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-surface-800 rounded-xl"><span class="text-sm">📧 E-mail - Pedro</span><span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] rounded-full font-semibold">Aberto</span></div>
                        </div>
                    </div>
                </div>

                {{-- Relatórios + Notificações --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-8 border border-surface-200 dark:border-surface-800">
                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full mb-3">PRO</span>
                        <h3 class="text-xl font-bold text-surface-900 dark:text-white mb-3"><i class="fa-solid fa-chart-bar text-orange-500 mr-2"></i>Relatórios</h3>
                        <p class="text-sm text-surface-600 dark:text-surface-400 mb-4">KPIs, classificação de clientes, demanda de serviços, ranking e análise por canal.</p>
                        <ul class="space-y-1.5 text-xs text-surface-500">
                            <li><i class="fa-solid fa-check mr-1.5 text-accent-500"></i>Filtro por período</li>
                            <li><i class="fa-solid fa-check mr-1.5 text-accent-500"></i>Top clientes</li>
                            <li><i class="fa-solid fa-check mr-1.5 text-accent-500"></i>Demanda de serviços digitais</li>
                        </ul>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-8 border border-surface-200 dark:border-surface-800">
                        <h3 class="text-xl font-bold text-surface-900 dark:text-white mb-3"><i class="fa-solid fa-bell text-amber-500 mr-2"></i>Notificações & Lembretes</h3>
                        <p class="text-sm text-surface-600 dark:text-surface-400 mb-4">Alertas sobre lembretes atrasados, compromissos próximos e atendimentos parados.</p>
                        <ul class="space-y-1.5 text-xs text-surface-500">
                            <li><i class="fa-solid fa-check mr-1.5 text-accent-500"></i>Lembretes com data e hora</li>
                            <li><i class="fa-solid fa-check mr-1.5 text-accent-500"></i>Vinculação a clientes</li>
                            <li><i class="fa-solid fa-check mr-1.5 text-accent-500"></i>Central de notificações</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================= --}}
    {{-- FERRAMENTAS (merged from tools.blade.php) --}}
    {{-- ============================================================= --}}
    <section id="ferramentas" class="py-20 md:py-28 bg-surface-50 dark:bg-surface-900 scroll-mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-3">Ferramentas</p>
                <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-4">Recursos poderosos para sua rotina</h2>
                <p class="text-surface-500 dark:text-surface-400 max-w-2xl mx-auto text-lg">Tudo o que você precisa para otimizar seu dia a dia e aumentar suas vendas.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-users text-brand-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Cadastro de Clientes</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Cadastro completo com nome, contato, endereço, documento e observações.</p>
                    <span class="text-[10px] font-medium text-accent-600 bg-accent-50 px-2 py-0.5 rounded-full">GRÁTIS</span>
                </div>
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-teal-50 dark:bg-teal-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-calendar-check text-teal-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Agenda & Lembretes</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Lembretes vinculados a clientes com filtros por status.</p>
                    <span class="text-[10px] font-medium text-accent-600 bg-accent-50 px-2 py-0.5 rounded-full">GRÁTIS</span>
                </div>
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-brain text-brand-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Análise com IA</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">3 provedores de IA para análise automática de perfil e oportunidades.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-accent-50 dark:bg-accent-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-ranking-star text-accent-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Score & Classificação</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Pontue de 0-100 e classifique como Quente, Morno, Frio, VIP.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-headset text-purple-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Gestão de Atendimentos</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Registre interações e acompanhe status com histórico completo.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-orange-50 dark:bg-orange-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-chart-pie text-orange-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Relatórios & Métricas</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">KPIs, demanda de serviços, ranking de clientes e análise por período.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-bell text-amber-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Notificações Inteligentes</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Alertas de lembretes atrasados e atendimentos parados.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-green-50 dark:bg-green-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-file-csv text-green-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Exportação CSV</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Exporte sua base de clientes para backup ou importação.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-white dark:bg-surface-950 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-shield-halved text-red-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Segurança & LGPD</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Dados isolados, criptografia e conformidade LGPD completa.</p>
                    <span class="text-[10px] font-medium text-accent-600 bg-accent-50 px-2 py-0.5 rounded-full">GRÁTIS</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================= --}}
    {{-- PLANOS (Pricing) --}}
    {{-- ============================================================= --}}
    <section class="py-20 md:py-28 bg-white dark:bg-surface-950 scroll-mt-16" id="planos">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-flex items-center px-3 py-1.5 bg-red-100 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-full text-sm text-red-700 dark:text-red-300 mb-4 animate-pulse">
                    <i class="fa-solid fa-fire mr-2"></i> Promoção por tempo limitado — 50% de desconto
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-4">Escolha seu plano</h2>
                <p class="text-surface-500 dark:text-surface-400 text-lg">Comece grátis e faça upgrade quando quiser.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Básico --}}
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-700 p-8">
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

                {{-- Profissional --}}
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl border-2 border-brand-500 p-8 relative shadow-xl shadow-brand-500/10">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 bg-red-500 text-white text-xs font-bold rounded-full whitespace-nowrap">
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

    {{-- ============================================================= --}}
    {{-- FAQ (AEO / GEO — perguntas frequentes com dados estruturados) --}}
    {{-- ============================================================= --}}
    <section id="faq" class="py-20 md:py-28 bg-surface-50 dark:bg-surface-900 scroll-mt-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-3">Dúvidas Frequentes</p>
                <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-4">Perguntas frequentes</h2>
                <p class="text-surface-500 dark:text-surface-400 text-lg">Tudo o que você precisa saber antes de começar.</p>
            </div>

            <div class="space-y-4" x-data="{ open: 1 }">
                @foreach($faqs as $index => $faq)
                    <div class="bg-white dark:bg-surface-950 rounded-2xl border border-surface-200 dark:border-surface-800 overflow-hidden">
                        <button type="button" @click="open === {{ $index }} ? open = null : open = {{ $index }}" class="w-full flex items-center justify-between px-6 py-5 text-left" :aria-expanded="open === {{ $index }}">
                            <span class="font-semibold text-surface-900 dark:text-white pr-4">{{ $faq['question'] }}</span>
                            <i class="fa-solid fa-chevron-down text-brand-500 transition-transform flex-shrink-0" :class="{ 'rotate-180': open === {{ $index }} }"></i>
                        </button>
                        <div x-show="open === {{ $index }}" x-collapse x-cloak>
                            <p class="px-6 pb-5 text-sm text-surface-600 dark:text-surface-400 leading-relaxed">{{ $faq['answer'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================= --}}
    {{-- CONTATO (merged from contact.blade.php + working form) --}}
    {{-- ============================================================= --}}
    <section id="contato" class="py-20 md:py-28 bg-white dark:bg-surface-950 scroll-mt-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-3">Contato</p>
                <h2 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-white mb-4">Fale conosco</h2>
                <p class="text-surface-500 dark:text-surface-400 max-w-2xl mx-auto text-lg">Tem dúvidas sobre o Clientify360? Quer saber mais sobre nossos planos ou precisa de suporte? Use os canais abaixo para falar conosco.</p>
            </div>

            {{-- Cards de contato --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                <a href="mailto:contato@clientify360.com.br" class="group bg-surface-50 dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 text-center hover:border-brand-300 dark:hover:border-brand-700 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-envelope text-brand-600 dark:text-brand-400 text-lg"></i>
                    </div>
                    <h3 class="font-semibold text-surface-900 dark:text-white text-sm mb-1">E-mail</h3>
                    <p class="text-sm text-brand-600 dark:text-brand-400 break-words">contato@clientify360.com.br</p>
                    <p class="text-xs text-surface-400 mt-2">Clique para enviar um e-mail</p>
                </a>

                <a href="https://www.3wonline.com.br" target="_blank" rel="noopener noreferrer" class="group bg-surface-50 dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 text-center hover:border-accent-300 dark:hover:border-accent-700 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-accent-50 dark:bg-accent-950/50 rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-globe text-accent-600 dark:text-accent-400 text-lg"></i>
                    </div>
                    <h3 class="font-semibold text-surface-900 dark:text-white text-sm mb-1">Site do Desenvolvedor</h3>
                    <p class="text-sm text-brand-600 dark:text-brand-400">www.3wonline.com.br</p>
                    <p class="text-xs text-surface-400 mt-2">Grupo 3W Online</p>
                </a>

                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800 p-6 text-center">
                    <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-clock text-purple-600 dark:text-purple-400 text-lg"></i>
                    </div>
                    <h3 class="font-semibold text-surface-900 dark:text-white text-sm mb-1">Horário de Atendimento</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Segunda a Sexta</p>
                    <p class="text-xs text-surface-400 mt-2">9h às 18h</p>
                </div>
            </div>

            {{-- Oferta especial --}}
            <div class="max-w-4xl mx-auto mt-8">
                <div class="p-6 bg-brand-50 dark:bg-brand-950/30 rounded-2xl border border-brand-200 dark:border-brand-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
                    <div>
                        <p class="text-sm font-semibold text-brand-800 dark:text-brand-200 mb-1"><i class="fa-solid fa-tag mr-1"></i> Oferta Especial</p>
                        <p class="text-sm text-brand-700 dark:text-brand-300">Plano Profissional com 50% OFF — de <span class="line-through">R$ 79,90</span> por <strong>R$ 39,90/mês</strong></p>
                    </div>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-brand-600/25 whitespace-nowrap">
                        <i class="fa-solid fa-rocket mr-1.5"></i> Aproveitar Desconto
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================= --}}
    {{-- CTA FINAL --}}
    {{-- ============================================================= --}}
    <section class="py-20 md:py-28 bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 dark:from-brand-900 dark:to-surface-950 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" aria-hidden="true">
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
                Desenvolvido por <strong>Grupo 3W Online</strong> — <a href="https://www.3wonline.com.br" target="_blank" rel="noopener noreferrer" class="underline hover:no-underline">www.3wonline.com.br</a>
            </p>
        </div>
    </section>

    {{-- Botão flutuante: voltar ao topo --}}
    <button type="button"
        x-data="{ show: false }"
        x-init="window.addEventListener('scroll', () => show = window.scrollY > 600)"
        x-show="show" x-transition
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-40 w-12 h-12 bg-brand-600 hover:bg-brand-700 text-white rounded-full shadow-lg shadow-brand-600/30 flex items-center justify-center transition"
        aria-label="Voltar ao topo" title="Voltar ao topo" x-cloak>
        <i class="fa-solid fa-arrow-up"></i>
    </button>
@endsection

@push('scripts')
    {{-- Alpine Collapse plugin para o FAQ --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    {{-- FAQ structured data (AEO/GEO) --}}
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faqs)->map(fn ($faq) => [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
        ])->all(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush
