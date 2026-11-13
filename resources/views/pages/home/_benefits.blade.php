@php
    $benefits = [
        ['fa-clock', 'brand', 'Menos tempo com planilhas', 'Clientes, orçamentos, agenda e contas no mesmo sistema. Você para de copiar dados de um lugar para outro.'],
        ['fa-hand-holding-dollar', 'accent', 'Mais vendas fechadas', 'O funil mostra cada oportunidade e o próximo passo. Os lembretes avisam quando é hora de retomar o contato.'],
        ['fa-chart-line', 'warning', 'Decisões com números', 'Relatórios de vendas, estoque e fluxo de caixa mostram o que está funcionando no seu negócio.'],
        ['fa-shield-halved', 'info', 'Dados protegidos', 'Cada conta só enxerga os próprios registros, com permissões por papel e conformidade com LGPD, GDPR, CCPA e POPIA.'],
    ];
    $tones = [
        'brand'   => ['tile' => 'bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400',   'glow' => 'shadow-brand-500/15'],
        'accent'  => ['tile' => 'bg-accent-50 text-accent-700 dark:bg-accent-950/50 dark:text-accent-400', 'glow' => 'shadow-accent-500/15'],
        'warning' => ['tile' => 'bg-warning-50 text-warning-700 dark:bg-warning-950/50 dark:text-warning-400', 'glow' => 'shadow-warning-500/15'],
        'info'    => ['tile' => 'bg-info-50 text-info-700 dark:bg-info-950/50 dark:text-info-400', 'glow' => 'shadow-info-500/15'],
    ];
@endphp
<section id="beneficios" class="c-section bg-white dark:bg-surface-950" aria-labelledby="beneficios-title">
    <div class="c-container">
        <div class="max-w-2xl">
            <p class="c-eyebrow"><i class="fa-solid fa-star" aria-hidden="true"></i> Benefícios</p>
            <h2 id="beneficios-title" class="c-h2 mt-3">Por que pequenos negócios escolhem o Clientify360</h2>
            <p class="c-lead mt-4">Uma ferramenta simples para o dia a dia, com recursos que antes só existiam em sistemas caros.</p>
        </div>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($benefits as [$icon, $tone, $title, $text])
                <article class="c-card p-6 transition duration-300 hover:-translate-y-1 hover:shadow-lg {{ $tones[$tone]['glow'] }} group">
                    {{-- Ícone com efeito Claymorphism --}}
                    <span class="c-icon-tile {{ $tones[$tone]['tile'] }} c-clay group-hover:scale-110 transition duration-300">
                        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-surface-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-surface-600 dark:text-surface-400 leading-relaxed">{{ $text }}</p>
                </article>
            @endforeach
        </div>

        {{-- Prova social — Autoridade --}}
        <div class="mt-10 flex flex-wrap justify-center gap-3">
            <span class="inline-flex items-center gap-2 rounded-full border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 px-4 py-2 text-sm text-surface-600 dark:text-surface-300">
                <i class="fa-solid fa-code text-brand-500" aria-hidden="true"></i>
                Laravel 12 + PHP 8.2
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 px-4 py-2 text-sm text-surface-600 dark:text-surface-300">
                <i class="fa-solid fa-credit-card text-accent-600" aria-hidden="true"></i>
                Pagamentos via Stripe
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 px-4 py-2 text-sm text-surface-600 dark:text-surface-300">
                <i class="fa-solid fa-scale-balanced text-info-600" aria-hidden="true"></i>
                LGPD · GDPR · CCPA · POPIA
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 px-4 py-2 text-sm text-surface-600 dark:text-surface-300">
                <i class="fa-solid fa-mobile-screen text-warning-600" aria-hidden="true"></i>
                Mobile First
            </span>
        </div>
    </div>
</section>
