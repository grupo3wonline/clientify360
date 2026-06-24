@extends('layouts.public')

@section('title', 'Serviços')
@section('meta_description', 'Serviços do Clientify360 - CRM com IA, Score de clientes, Gestão de atendimentos, Relatórios avançados e muito mais.')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 dark:from-brand-900 dark:to-surface-950 text-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Nossos Serviços</h1>
            <p class="text-lg text-brand-200 max-w-2xl mx-auto">Conheça todas as funcionalidades que vão transformar sua gestão de clientes.</p>
        </div>
    </section>

    <section class="bg-red-600 dark:bg-red-900 py-3">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-white text-sm font-medium"><i class="fa-solid fa-clock mr-2 animate-pulse"></i><strong>PROMOÇÃO:</strong> Todas as funcionalidades PRO de <span class="line-through">R$ 79,90</span> por <strong>R$ 39,90/mês</strong> <a href="{{ route('register') }}" class="ml-2 underline font-bold">Aproveitar →</a></p>
        </div>
    </section>

    <section class="py-16 md:py-24 bg-white dark:bg-surface-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-16">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full mb-3">PRO</span>
                        <h2 class="text-2xl font-bold text-surface-900 dark:text-white mb-4"><i class="fa-solid fa-brain text-brand-500 mr-2"></i>Análise com Inteligência Artificial</h2>
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
                        <h2 class="text-2xl font-bold text-surface-900 dark:text-white mb-4"><i class="fa-solid fa-ranking-star text-accent-500 mr-2"></i>Score & Classificação</h2>
                        <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-4">Atribua pontuações e classifique seus clientes para priorizar os mais valiosos. Identifique leads quentes e foque onde há mais retorno.</p>
                        <ul class="space-y-2 text-sm text-surface-600 dark:text-surface-400">
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Score numérico (0-100)</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Classificação visual (Quente/Morno/Frio/VIP)</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Análise SWOT por cliente</li>
                            <li><i class="fa-solid fa-check text-accent-500 mr-2"></i>Serviços indicados por perfil</li>
                        </ul>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300 rounded-full mb-3">PRO</span>
                        <h2 class="text-2xl font-bold text-surface-900 dark:text-white mb-4"><i class="fa-solid fa-headset text-purple-500 mr-2"></i>Gestão de Atendimentos</h2>
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

    <section class="py-16 bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-900 dark:to-surface-950 text-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <div class="inline-flex items-center px-3 py-1.5 bg-red-500/20 border border-red-400/30 rounded-full text-sm text-red-200 mb-4 animate-pulse">
                <i class="fa-solid fa-hourglass-half mr-2"></i> Últimas vagas com 50% de desconto
            </div>
            <h2 class="text-2xl md:text-3xl font-bold mb-4">Acesse tudo por apenas R$ 39,90/mês</h2>
            <p class="text-brand-200 mb-6">Valor original: <span class="line-through">R$ 79,90</span>. Promoção por tempo limitado.</p>
            <a href="{{ route('register') }}" class="inline-flex items-center px-7 py-4 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition shadow-xl">
                <i class="fa-solid fa-rocket mr-2"></i> Garantir 50% OFF Agora
            </a>
        </div>
    </section>
@endsection
