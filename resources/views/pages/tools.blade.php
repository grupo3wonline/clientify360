@extends('layouts.public')

@section('title', 'Ferramentas')
@section('meta_description', 'Ferramentas do Clientify360 - IA integrada, Score de clientes, Agenda, Relatórios e Notificações inteligentes.')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 dark:from-brand-900 dark:to-surface-950 text-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Ferramentas</h1>
            <p class="text-lg text-brand-200 max-w-2xl mx-auto">Recursos poderosos para otimizar sua rotina e aumentar suas vendas.</p>
        </div>
    </section>

    <section class="bg-red-600 dark:bg-red-900 py-3">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-white text-sm font-medium"><i class="fa-solid fa-fire mr-2 animate-pulse"></i><strong>50% OFF:</strong> Acesse TODAS as ferramentas por apenas <strong>R$ 39,90/mês</strong> (era R$ 79,90) <a href="{{ route('register') }}" class="ml-2 underline font-bold">Garantir →</a></p>
        </div>
    </section>

    <section class="py-16 md:py-24 bg-white dark:bg-surface-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-users text-brand-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Cadastro de Clientes</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Cadastro completo com nome, contato, endereço, documento e observações.</p>
                    <span class="text-[10px] font-medium text-accent-600 bg-accent-50 px-2 py-0.5 rounded-full">GRÁTIS</span>
                </div>
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-teal-50 dark:bg-teal-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-calendar-check text-teal-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Agenda & Lembretes</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Lembretes vinculados a clientes com filtros por status.</p>
                    <span class="text-[10px] font-medium text-accent-600 bg-accent-50 px-2 py-0.5 rounded-full">GRÁTIS</span>
                </div>
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-brain text-brand-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Análise com IA</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">3 provedores de IA para análise automática de perfil e oportunidades.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-accent-50 dark:bg-accent-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-ranking-star text-accent-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Score & Classificação</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Pontue de 0-100 e classifique como Quente, Morno, Frio, VIP.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-headset text-purple-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Gestão de Atendimentos</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Registre interações e acompanhe status com histórico completo.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-orange-50 dark:bg-orange-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-chart-pie text-orange-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Relatórios & Métricas</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">KPIs, demanda de serviços, ranking de clientes e análise por período.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-amber-50 dark:bg-amber-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-bell text-amber-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Notificações Inteligentes</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Alertas de lembretes atrasados e atendimentos parados.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-brand-200 dark:border-brand-800 hover:shadow-lg transition-all ring-1 ring-brand-200 dark:ring-brand-800">
                    <div class="w-12 h-12 bg-green-50 dark:bg-green-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-file-csv text-green-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Exportação CSV</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Exporte sua base de clientes para backup ou importação.</p>
                    <span class="text-[10px] font-bold text-brand-700 bg-brand-100 px-2 py-0.5 rounded-full">PRO</span>
                </div>
                <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-7 border border-surface-200 dark:border-surface-800 hover:shadow-lg transition-all">
                    <div class="w-12 h-12 bg-red-50 dark:bg-red-950/50 rounded-xl flex items-center justify-center mb-4"><i class="fa-solid fa-shield-halved text-red-600 text-lg"></i></div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Segurança & LGPD</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400 mb-3">Dados isolados, criptografia e conformidade LGPD completa.</p>
                    <span class="text-[10px] font-medium text-accent-600 bg-accent-50 px-2 py-0.5 rounded-full">GRÁTIS</span>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-900 dark:to-surface-950 text-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <div class="inline-flex items-center px-3 py-1.5 bg-red-500/20 border border-red-400/30 rounded-full text-sm text-red-200 mb-4 animate-pulse"><i class="fa-solid fa-fire mr-2"></i> 50% OFF por tempo limitado</div>
            <h2 class="text-2xl md:text-3xl font-bold mb-4">Desbloqueie TODAS as ferramentas PRO</h2>
            <p class="text-brand-200 mb-6">De <span class="line-through">R$ 79,90</span> por <strong class="text-white text-xl">R$ 39,90/mês</strong></p>
            <a href="{{ route('register') }}" class="inline-flex items-center px-7 py-4 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition shadow-xl"><i class="fa-solid fa-rocket mr-2"></i> Começar com 50% OFF</a>
        </div>
    </section>
@endsection
