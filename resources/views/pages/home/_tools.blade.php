{{-- Content of the former /ferramentas page --}}
@php
    $tools = [
        ['fa-users', 'Cadastro de Clientes', 'Cadastro completo com nome, contato, endereço, documento e observações.', false],
        ['fa-calendar-check', 'Agenda & Lembretes', 'Lembretes vinculados a clientes com filtros por status.', false],
        ['fa-brain', 'Análise com IA', '3 provedores de IA para análise automática de perfil e oportunidades.', true],
        ['fa-ranking-star', 'Score & Classificação', 'Pontue de 0-100 e classifique como Quente, Morno, Frio, VIP.', false],
        ['fa-headset', 'Gestão de Atendimentos', 'Registre interações e acompanhe status com histórico completo.', false],
        ['fa-chart-pie', 'Relatórios & Métricas', 'KPIs, demanda de serviços, ranking de clientes e análise por período.', false],
        ['fa-bell', 'Notificações Inteligentes', 'Alertas de lembretes atrasados e atendimentos parados.', false],
        ['fa-file-csv', 'Exportação CSV', 'Exporte sua base de clientes para backup ou importação (também em Excel).', true],
        ['fa-shield-halved', 'Segurança & LGPD', 'Dados isolados, criptografia e conformidade LGPD completa.', false],
        ['fa-file-pdf', 'PDF com seu logotipo', 'Orçamentos e ordens de serviço em PDF prontos para enviar.', true],
        ['fa-paperclip', 'Anexos de clientes', 'Guarde contratos e documentos em área privada da sua conta.', false],
        ['fa-magnifying-glass', 'Busca global', 'Encontre clientes e produtos por nome, e-mail, documento ou SKU.', false],
    ];
@endphp
<section id="ferramentas" class="c-section bg-surface-50 dark:bg-surface-900" aria-labelledby="ferramentas-title">
    <div class="c-container">
        <div class="text-center max-w-2xl mx-auto">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-toolbox" aria-hidden="true"></i> Ferramentas</p>
            <h2 id="ferramentas-title" class="c-h2 mt-3">Recursos poderosos para sua rotina</h2>
            <p class="c-lead mt-4">Recursos poderosos para otimizar sua rotina e aumentar suas vendas.</p>
        </div>
        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($tools as [$icon, $title, $text, $proOnly])
                <article class="c-card p-5 flex gap-4 {{ $proOnly ? 'ring-1 ring-brand-200 dark:ring-brand-800' : '' }}">
                    <span class="c-icon-tile {{ $proOnly ? 'bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400' : 'bg-accent-50 text-accent-700 dark:bg-accent-950/40 dark:text-accent-400' }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <div class="min-w-0">
                        <h3 class="font-bold text-surface-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">{{ $text }}</p>
                        <span class="mt-2 c-badge {{ $proOnly ? 'bg-brand-100 text-brand-700 dark:bg-brand-900 dark:text-brand-200' : 'bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200' }}">{{ $proOnly ? 'PRO' : 'GRÁTIS' }}</span>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-10 text-center">
            <p class="text-lg font-bold text-surface-900 dark:text-white">Desbloqueie TODAS as ferramentas PRO</p>
            <p class="mt-1 text-surface-600 dark:text-surface-400">De <s>{{ $pro->formattedOriginalPrice() }}</s> por {{ $pro->formattedPrice() }}/mês</p>
            <a href="{{ route('register') }}" class="mt-4 c-btn-primary px-6 py-3"><i class="fa-solid fa-unlock text-xs" aria-hidden="true"></i> Começar grátis e evoluir depois</a>
        </div>
    </div>
</section>
