@php
    $steps = [
        ['fa-user-plus', 'Crie sua conta grátis', 'Informe nome, nome de usuário, e-mail e senha e confirme o e-mail. Não pedimos cartão de crédito.'],
        ['fa-address-book', 'Cadastre seus clientes', 'Adicione os clientes um a um ou capte leads com um formulário público. Use tags para organizar.'],
        ['fa-filter-circle-dollar', 'Acompanhe as vendas', 'Crie oportunidades no funil, envie orçamentos e registre cada atendimento e próximo passo.'],
        ['fa-chart-simple', 'Analise e evolua', 'Veja relatórios e o fluxo de caixa. Quando precisar de mais, faça o upgrade para o Profissional.'],
    ];
@endphp
<section id="como-funciona" class="c-section bg-white dark:bg-surface-950" aria-labelledby="como-funciona-title">
    <div class="c-container">
        <div class="text-center max-w-2xl mx-auto">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-route" aria-hidden="true"></i> Como funciona</p>
            <h2 id="como-funciona-title" class="c-h2 mt-3">Comece a usar em 4 passos</h2>
            <p class="c-lead mt-4">Instruções de uso para sair do zero e ter seu negócio organizado ainda hoje.</p>
        </div>
        <ol class="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            @foreach($steps as $i => [$icon, $title, $text])
                <li class="relative c-card p-6">
                    <span class="absolute -top-3 left-6 inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-brand-600 px-2 text-xs font-bold text-white shadow">{{ $i + 1 }}</span>
                    <span class="c-icon-tile mt-2 bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <h3 class="mt-4 font-bold text-surface-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-surface-600 dark:text-surface-400 leading-relaxed">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-10 text-center">
            <a href="{{ route('register') }}" class="c-btn-primary px-6 py-3"><i class="fa-solid fa-rocket text-xs" aria-hidden="true"></i> Criar minha conta grátis</a>
        </div>
    </div>
</section>
