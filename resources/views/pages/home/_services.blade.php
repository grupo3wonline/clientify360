{{-- Content of the former /servicos page ("Nossos Serviços") --}}
<section id="servicos" class="c-section bg-white dark:bg-surface-950" aria-labelledby="servicos-title">
    <div class="c-container">
        <div class="text-center max-w-2xl mx-auto">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-briefcase" aria-hidden="true"></i> Nossos Serviços</p>
            <h2 id="servicos-title" class="c-h2 mt-3">Recursos que transformam sua gestão</h2>
            <p class="c-lead mt-4">Conheça todas as funcionalidades que vão transformar sua gestão de clientes.</p>
        </div>

        <div class="mt-14 space-y-14">
            {{-- IA --}}
            <div class="grid gap-10 lg:grid-cols-2 items-center">
                <div>
                    <span class="c-badge bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-200">PRO</span>
                    <h3 class="mt-3 text-2xl font-bold text-surface-900 dark:text-white"><i class="fa-solid fa-brain text-brand-500 mr-2" aria-hidden="true"></i>Análise com Inteligência Artificial</h3>
                    <p class="mt-4 text-surface-600 dark:text-surface-400 leading-relaxed">Utilize modelos de IA de última geração para analisar automaticamente o perfil dos seus clientes. Identifique pontos fortes, fracos, oportunidades de negócio e serviços recomendados.</p>
                    <ul class="mt-4 space-y-2 text-sm text-surface-600 dark:text-surface-400">
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Google Gemini 2.0 Flash</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Groq Llama 3.3 70B Versatile</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>OpenRouter Gemma 3n (Gratuito)</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Cada usuário usa suas próprias credenciais</li>
                    </ul>
                </div>
                <div class="c-card bg-surface-50 dark:bg-surface-900 p-6 sm:p-8 space-y-3" aria-hidden="true">
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-surface-800"><i class="fa-solid fa-thumbs-up text-accent-500"></i><span class="text-sm">Pontos Fortes identificados</span></div>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-surface-800"><i class="fa-solid fa-thumbs-down text-danger-500"></i><span class="text-sm">Pontos Fracos detectados</span></div>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-surface-800"><i class="fa-solid fa-lightbulb text-warning-500"></i><span class="text-sm">Oportunidades de negócio</span></div>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-white dark:bg-surface-800"><i class="fa-solid fa-wand-magic-sparkles text-brand-500"></i><span class="text-sm">Serviços recomendados</span></div>
                </div>
            </div>

            {{-- Score --}}
            <div class="grid gap-10 lg:grid-cols-2 items-center">
                <div class="order-2 lg:order-1 c-card bg-surface-50 dark:bg-surface-900 p-8 text-center" aria-hidden="true">
                    <div class="flex flex-wrap justify-center gap-2">
                        <span class="px-3 py-1.5 rounded-full text-sm font-semibold bg-danger-100 text-danger-700 dark:bg-danger-950/50 dark:text-danger-300">🔥 Quente</span>
                        <span class="px-3 py-1.5 rounded-full text-sm font-semibold bg-warning-100 text-warning-800 dark:bg-warning-950/50 dark:text-warning-300">🌤️ Morno</span>
                        <span class="px-3 py-1.5 rounded-full text-sm font-semibold bg-info-100 text-info-700 dark:bg-info-950/50 dark:text-info-300">❄️ Frio</span>
                        <span class="px-3 py-1.5 rounded-full text-sm font-semibold bg-warning-50 text-warning-700 dark:bg-warning-950/30 dark:text-warning-200">⭐ VIP</span>
                    </div>
                    <p class="mt-6 text-6xl font-extrabold text-brand-600 dark:text-brand-400">85<span class="text-lg text-surface-400">/100</span></p>
                    <p class="mt-2 text-sm text-surface-500">Score de exemplo</p>
                </div>
                <div class="order-1 lg:order-2">
                    <span class="c-badge bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200">GRÁTIS</span>
                    <h3 class="mt-3 text-2xl font-bold text-surface-900 dark:text-white"><i class="fa-solid fa-ranking-star text-accent-500 mr-2" aria-hidden="true"></i>Score & Classificação</h3>
                    <p class="mt-4 text-surface-600 dark:text-surface-400 leading-relaxed">Atribua pontuações e classifique seus clientes para priorizar os mais valiosos. Identifique leads quentes e foque onde há mais retorno.</p>
                    <ul class="mt-4 space-y-2 text-sm text-surface-600 dark:text-surface-400">
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Score numérico (0-100)</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Classificação visual (Quente/Morno/Frio/VIP)</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Análise SWOT por cliente (com IA, no Profissional)</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Serviços indicados por perfil (com IA, no Profissional)</li>
                    </ul>
                </div>
            </div>

            {{-- Atendimentos --}}
            <div class="grid gap-10 lg:grid-cols-2 items-center">
                <div>
                    <span class="c-badge bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200">GRÁTIS</span>
                    <h3 class="mt-3 text-2xl font-bold text-surface-900 dark:text-white"><i class="fa-solid fa-headset text-brand-500 mr-2" aria-hidden="true"></i>Gestão de Atendimentos</h3>
                    <p class="mt-4 text-surface-600 dark:text-surface-400 leading-relaxed">Registre interações por telefone, WhatsApp, e-mail ou reunião. Controle status e resolução de cada atendimento.</p>
                    <ul class="mt-4 space-y-2 text-sm text-surface-600 dark:text-surface-400">
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Tipos: Suporte, Vendas, Follow-up, Reclamação</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Canais: Telefone, WhatsApp, E-mail, Reunião</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Fluxo: Aberto → Em Andamento → Resolvido</li>
                        <li><i class="fa-solid fa-check text-accent-500 mr-2" aria-hidden="true"></i>Notas de resolução e histórico</li>
                    </ul>
                </div>
                <div class="c-card bg-surface-50 dark:bg-surface-900 p-6 sm:p-8 space-y-3" aria-hidden="true">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-surface-800"><span class="text-sm">📞 Ligação com João</span><span class="c-badge bg-accent-100 text-accent-800">Resolvido</span></div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-surface-800"><span class="text-sm">💬 WhatsApp - Maria</span><span class="c-badge bg-warning-100 text-warning-800">Em Andamento</span></div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-surface-800"><span class="text-sm">📧 E-mail - Pedro</span><span class="c-badge bg-danger-100 text-danger-700">Aberto</span></div>
                </div>
            </div>

            {{-- Relatórios + Notificações --}}
            <div class="grid gap-6 md:grid-cols-2">
                <article class="c-card bg-surface-50 dark:bg-surface-900 p-6 sm:p-8">
                    <span class="c-badge bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200">GRÁTIS</span>
                    <h3 class="mt-3 text-xl font-bold text-surface-900 dark:text-white"><i class="fa-solid fa-chart-bar text-warning-500 mr-2" aria-hidden="true"></i>Relatórios</h3>
                    <p class="mt-3 text-sm text-surface-600 dark:text-surface-400">KPIs, classificação de clientes, demanda de serviços, ranking e análise por canal.</p>
                    <ul class="mt-4 space-y-1.5 text-xs text-surface-500 dark:text-surface-400">
                        <li><i class="fa-solid fa-check mr-1.5 text-accent-500" aria-hidden="true"></i>Filtro por período</li>
                        <li><i class="fa-solid fa-check mr-1.5 text-accent-500" aria-hidden="true"></i>Top clientes</li>
                        <li><i class="fa-solid fa-check mr-1.5 text-accent-500" aria-hidden="true"></i>Demanda de serviços digitais</li>
                    </ul>
                </article>
                <article class="c-card bg-surface-50 dark:bg-surface-900 p-6 sm:p-8">
                    <span class="c-badge bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200">GRÁTIS</span>
                    <h3 class="mt-3 text-xl font-bold text-surface-900 dark:text-white"><i class="fa-solid fa-bell text-warning-500 mr-2" aria-hidden="true"></i>Notificações & Lembretes</h3>
                    <p class="mt-3 text-sm text-surface-600 dark:text-surface-400">Alertas sobre lembretes atrasados, compromissos próximos e atendimentos parados.</p>
                    <ul class="mt-4 space-y-1.5 text-xs text-surface-500 dark:text-surface-400">
                        <li><i class="fa-solid fa-check mr-1.5 text-accent-500" aria-hidden="true"></i>Lembretes com data e hora</li>
                        <li><i class="fa-solid fa-check mr-1.5 text-accent-500" aria-hidden="true"></i>Vinculação a clientes</li>
                        <li><i class="fa-solid fa-check mr-1.5 text-accent-500" aria-hidden="true"></i>Central de notificações</li>
                    </ul>
                </article>
            </div>
        </div>

        <div class="mt-12 rounded-card border border-brand-200 dark:border-brand-900 bg-brand-50 dark:bg-brand-950/30 p-6 text-center">
            <p class="text-lg font-bold text-brand-900 dark:text-brand-100">Acesse tudo por apenas {{ $pro->formattedPrice() }}/mês</p>
            <p class="mt-1 text-sm text-brand-800 dark:text-brand-200">Valor de referência: {{ $pro->formattedOriginalPrice() }}. Preço promocional, sem fidelidade.</p>
        </div>
    </div>
</section>
