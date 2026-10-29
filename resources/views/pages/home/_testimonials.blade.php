{{-- Honest social proof: use cases and verifiable facts only (no invented quotes, ratings or customer counts). --}}
@php
    $useCases = [
        ['fa-user-tie', 'Autônomos', 'Consultores, corretores e profissionais liberais que precisam lembrar de cada retorno.'],
        ['fa-screwdriver-wrench', 'Prestadores de serviço', 'Assistências, instaladores e oficinas que trabalham com orçamento e ordem de serviço.'],
        ['fa-store', 'Pequenas lojas', 'Comércios que controlam estoque, compras e contas a receber.'],
        ['fa-bullhorn', 'Agências', 'Equipes que captam leads, acompanham propostas e dividem a carteira de clientes.'],
    ];
    $badges = [
        ['fa-lock', 'Dados isolados por conta'],
        ['fa-scale-balanced', 'Conformidade com LGPD, GDPR, CCPA e POPIA'],
        ['fa-credit-card', 'Pagamentos processados pela Stripe'],
    ];
@endphp
<section id="depoimentos" class="c-section bg-surface-50 dark:bg-surface-900" aria-labelledby="depoimentos-title">
    <div class="c-container">
        <div class="text-center max-w-2xl mx-auto">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-people-group" aria-hidden="true"></i> Para quem é</p>
            <h2 id="depoimentos-title" class="c-h2 mt-3">Feito para quem atende clientes todos os dias</h2>
            <p class="c-lead mt-4">Veja se o Clientify360 combina com a rotina do seu negócio.</p>
        </div>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($useCases as [$icon, $title, $text])
                <article class="c-card p-6 text-center">
                    <span class="c-icon-tile mx-auto bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                    <h3 class="mt-4 font-bold text-surface-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm text-surface-600 dark:text-surface-400">{{ $text }}</p>
                </article>
            @endforeach
        </div>
        <ul class="mt-10 flex flex-wrap justify-center gap-3">
            @foreach($badges as [$icon, $label])
                <li class="inline-flex items-center gap-2 rounded-full border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-950 px-4 py-2 text-sm font-medium text-surface-700 dark:text-surface-300">
                    <i class="fa-solid {{ $icon }} text-accent-600 dark:text-accent-400" aria-hidden="true"></i> {{ $label }}
                </li>
            @endforeach
        </ul>
    </div>
</section>
