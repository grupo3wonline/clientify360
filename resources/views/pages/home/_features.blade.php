@php
    // [icon, title, text, pro-only]
    $features = [
        ['fa-brain', 'Análise com IA', 'Integração com Gemini, Groq e OpenRouter para análise automática de perfil, projeções e qualificação.', true],
        ['fa-ranking-star', 'Score & Classificação', 'Pontue e ranqueie clientes (Quente, Morno, Frio, VIP). Identifique os melhores leads.', false],
        ['fa-headset', 'Gestão de Atendimentos', 'Registre interações por telefone, WhatsApp, e-mail ou reunião. Acompanhe status e resolução.', false],
        ['fa-chart-pie', 'Relatórios Avançados', 'KPIs, métricas de atendimento, demanda de serviços, ranking de clientes e análise por período.', false],
        ['fa-calendar-check', 'Agenda & Lembretes', 'Agende follow-ups, ligações e reuniões. Alertas de lembretes atrasados e próximos compromissos.', false],
        ['fa-shield-halved', 'Segurança & LGPD', 'Dados isolados por conta, criptografia, sessões seguras e conformidade com a LGPD.', false],
        ['fa-filter-circle-dollar', 'Funil de vendas', 'Kanban de oportunidades com etapas personalizáveis, valores e histórico de cada negociação.', false],
        ['fa-file-invoice-dollar', 'Orçamentos e PDF', 'Monte orçamentos com produtos e serviços. No Profissional, baixe em PDF com o seu logotipo.', false],
        ['fa-screwdriver-wrench', 'Ordens de serviço', 'Controle execução, status e faturamento de cada serviço prestado.', false],
        ['fa-boxes-stacked', 'Produtos, estoque e compras', 'Catálogo, saldo de estoque, entradas de mercadoria e alerta de estoque baixo.', false],
        ['fa-money-bill-transfer', 'Financeiro', 'Contas a pagar e a receber, categorias e fluxo de caixa do mês.', false],
        ['fa-magnet', 'Captação de leads', 'Formulários públicos que transformam visitantes em clientes no seu funil.', false],
        ['fa-users-gear', 'Equipe e permissões', 'Convide colaboradores e defina o que cada um pode ver e editar.', false],
        ['fa-plug', 'API REST', 'Integre clientes, produtos e orçamentos com outros sistemas usando tokens.', true],
        ['fa-file-export', 'Exportação CSV e Excel', 'Exporte sua base de clientes para backup ou para outras ferramentas.', true],
    ];
@endphp
<section id="funcionalidades" class="c-section bg-surface-50 dark:bg-surface-900" aria-labelledby="funcionalidades-title">
    <div class="c-container">
        <div class="text-center max-w-2xl mx-auto">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Funcionalidades Completas</p>
            <h2 id="funcionalidades-title" class="c-h2 mt-3">Tudo que você precisa para crescer</h2>
            <p class="c-lead mt-4">Plataforma completa com IA, score de clientes, atendimentos e muito mais.</p>
        </div>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($features as [$icon, $title, $text, $proOnly])
                <article class="group c-card p-6 transition hover:border-brand-200 dark:hover:border-brand-800 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <span class="c-icon-tile bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400 transition group-hover:scale-110"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                        @if($proOnly)
                            <span class="c-badge bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-200">PRO</span>
                        @else
                            <span class="c-badge bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200">GRÁTIS</span>
                        @endif
                    </div>
                    <h3 class="mt-5 text-lg font-bold text-surface-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-surface-600 dark:text-surface-400 leading-relaxed">{{ $text }}</p>
                </article>
            @endforeach
        </div>
        <p class="mt-6 text-center text-xs text-surface-500 dark:text-surface-400">GRÁTIS: disponível no plano Básico, dentro dos limites do plano. PRO: exclusivo do plano Profissional.</p>
    </div>
</section>
