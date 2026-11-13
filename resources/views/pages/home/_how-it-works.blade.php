@php
    $steps = [
        ['fa-user-plus', 'Crie sua conta grátis', 'Informe nome, nome de usuário, e-mail e senha e confirme o e-mail. <strong class="text-surface-700 dark:text-surface-200">Sem cartão de crédito.</strong>', 'accent'],
        ['fa-address-book', 'Cadastre seus clientes', 'Adicione os clientes um a um ou capte leads com um formulário público. Use tags para organizar.', 'brand'],
        ['fa-filter-circle-dollar', 'Acompanhe as vendas', 'Crie oportunidades no funil, envie orçamentos e registre cada atendimento e próximo passo.', 'info'],
        ['fa-chart-simple', 'Analise e evolua', 'Veja relatórios e o fluxo de caixa. Quando precisar de mais, faça o upgrade para o Profissional.', 'warning'],
    ];
    $stepTiles = [
        'accent'  => 'bg-accent-50 text-accent-700 dark:bg-accent-950/50 dark:text-accent-400',
        'brand'   => 'bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400',
        'info'    => 'bg-info-50 text-info-700 dark:bg-info-950/50 dark:text-info-400',
        'warning' => 'bg-warning-50 text-warning-700 dark:bg-warning-950/50 dark:text-warning-400',
    ];
@endphp
<section id="como-funciona" class="c-section bg-surface-50 dark:bg-surface-900" aria-labelledby="como-funciona-title">
    <div class="c-container">
        <div class="text-center max-w-2xl mx-auto">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-route" aria-hidden="true"></i> Como funciona</p>
            <h2 id="como-funciona-title" class="c-h2 mt-3">Comece a usar em 4 passos</h2>
            <p class="c-lead mt-4">Instruções de uso para sair do zero e ter seu negócio organizado ainda hoje.</p>
        </div>

        {{-- Linha conectora para desktop --}}
        <div class="relative mt-12">
            {{-- Linha decorativa horizontal (lg+) --}}
            <div class="hidden lg:block absolute top-16 left-[calc(12.5%+2rem)] right-[calc(12.5%+2rem)] h-0.5 bg-gradient-to-r from-accent-300 via-brand-400 to-warning-300 dark:from-accent-700 dark:via-brand-600 dark:to-warning-700" aria-hidden="true"></div>

            <ol class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                @foreach($steps as $i => [$icon, $title, $text, $tone])
                    <li class="relative c-card p-6 hover:shadow-lg hover:-translate-y-1 transition duration-300">
                        {{-- Número do passo --}}
                        <div class="relative z-10 flex items-center gap-3 mb-4">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white shadow-md shadow-brand-600/30">{{ $i + 1 }}</span>
                            <span class="c-icon-tile {{ $stepTiles[$tone] }}">
                                <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>
                            </span>
                        </div>
                        <h3 class="font-bold text-surface-900 dark:text-white">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-surface-600 dark:text-surface-400 leading-relaxed">{!! $text !!}</p>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('register') }}" class="c-btn-primary px-7 py-3.5 text-base">
                <i class="fa-solid fa-rocket text-xs" aria-hidden="true"></i> Criar minha conta grátis
            </a>
            <p class="mt-3 text-sm text-surface-500 dark:text-surface-400">
                <i class="fa-solid fa-clock mr-1" aria-hidden="true"></i>
                Leva menos de 2 minutos para criar a conta.
            </p>
        </div>
    </div>
</section>
