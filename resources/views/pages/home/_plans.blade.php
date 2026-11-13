@php($basic = $plans['basic'])
<section id="planos" class="c-section bg-white dark:bg-surface-950" aria-labelledby="planos-title">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Cabeçalho da seção --}}
        <div class="text-center">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-tags" aria-hidden="true"></i> Planos</p>
            <h2 id="planos-title" class="c-h2 mt-3">Escolha seu plano</h2>
            <p class="c-lead mt-4">Comece grátis e faça upgrade quando quiser.</p>
        </div>

        {{-- Gatilho de Urgência — oferta por tempo --}}
        <div class="mt-6 flex justify-center">
            <div class="inline-flex items-center gap-3 rounded-2xl border border-warning-200 dark:border-warning-800 bg-warning-50 dark:bg-warning-950/40 px-5 py-3 text-sm font-medium text-warning-800 dark:text-warning-200">
                <i class="fa-solid fa-fire text-warning-500" aria-hidden="true"></i>
                <span><strong>Oferta por tempo limitado:</strong> Profissional com {{ $pro->discountPercent() }}% de desconto</span>
                <span class="hidden sm:block text-warning-500 dark:text-warning-400">·</span>
                <span class="hidden sm:flex items-center gap-1.5 text-xs text-warning-600 dark:text-warning-400">
                    <i class="fa-solid fa-clock" aria-hidden="true"></i> Aproveite enquanto dura
                </span>
            </div>
        </div>

        {{-- Cards dos planos --}}
        <div class="mt-10 grid gap-6 md:grid-cols-2">

            {{-- Plano Básico --}}
            <article class="c-card p-7 sm:p-8 flex flex-col hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-surface-900 dark:text-white">{{ $basic->name }}</h3>
                    <span class="c-badge bg-surface-100 dark:bg-surface-800 text-surface-600 dark:text-surface-300">
                        <i class="fa-solid fa-infinity mr-1" aria-hidden="true"></i> Para sempre grátis
                    </span>
                </div>
                <p class="mt-1 text-sm text-surface-500 dark:text-surface-400">{{ $basic->description }}</p>

                <p class="mt-6 mb-8 flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold text-surface-900 dark:text-white">Grátis</span>
                    <span class="text-sm text-surface-500">para sempre</span>
                </p>

                <ul class="space-y-3 mb-8 flex-1" aria-label="Recursos do plano Básico">
                    @foreach($basic->features as $feature)
                        <li class="flex gap-2.5 text-sm text-surface-600 dark:text-surface-400">
                            <i class="fa-solid fa-check text-accent-500 mt-0.5 shrink-0" aria-hidden="true"></i>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('register') }}" class="c-btn-ghost w-full py-3">
                    <i class="fa-solid fa-user-plus text-xs" aria-hidden="true"></i> Criar conta grátis
                </a>
                <p class="mt-2 text-center text-xs text-surface-400">Sem cartão de crédito necessário.</p>
            </article>

            {{-- Plano Profissional — Escassez + Urgência + Autoridade --}}
            <article class="relative c-card p-7 sm:p-8 flex flex-col border-2 border-brand-500 dark:border-brand-500 shadow-xl shadow-brand-500/10 hover:shadow-2xl hover:shadow-brand-500/15 transition">
                {{-- Badge recomendado --}}
                <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-gradient-to-r from-brand-600 to-brand-500 px-5 py-1.5 text-xs font-bold text-white shadow-lg shadow-brand-600/30">
                    <i class="fa-solid fa-star text-[10px] mr-1" aria-hidden="true"></i>
                    RECOMENDADO · {{ $pro->discountPercent() }}% OFF
                </span>

                <div class="flex items-center justify-between mt-1">
                    <h3 class="text-xl font-bold text-surface-900 dark:text-white">{{ $pro->name }}</h3>
                    <span class="c-badge bg-brand-50 dark:bg-brand-950/50 text-brand-700 dark:text-brand-300">
                        <i class="fa-solid fa-trophy text-[10px] mr-1" aria-hidden="true"></i> Mais completo
                    </span>
                </div>
                <p class="mt-1 text-sm text-surface-500 dark:text-surface-400">{{ $pro->description }}</p>

                {{-- Preço com destaque de desconto --}}
                <div class="mt-6 mb-6">
                    <p class="text-xs text-surface-500 dark:text-surface-400 flex items-center gap-1">
                        <i class="fa-solid fa-tag text-warning-500" aria-hidden="true"></i>
                        Preço de referência <s class="text-lg text-surface-400 ml-1">{{ $pro->formattedOriginalPrice() }}</s>
                    </p>
                    <p class="mt-1 flex flex-wrap items-baseline gap-2">
                        <span class="sr-only">Preço promocional:</span>
                        <span class="text-4xl font-extrabold text-surface-900 dark:text-white">{{ $pro->formattedPrice() }}</span>
                        <span class="text-sm text-surface-500">/mês</span>
                    </p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="c-badge bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200">
                            <i class="fa-solid fa-piggy-bank text-[10px] mr-1" aria-hidden="true"></i>
                            Economize {{ $pro->formattedYearlySaving() }}/ano
                        </span>
                        <span class="c-badge bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200">
                            <i class="fa-solid fa-calendar text-[10px] mr-1" aria-hidden="true"></i>
                            Sem fidelidade
                        </span>
                    </div>
                </div>

                <ul class="space-y-3 mb-8 flex-1" aria-label="Recursos do plano Profissional">
                    @foreach($pro->features as $feature)
                        <li class="flex gap-2.5 text-sm text-surface-600 dark:text-surface-400">
                            <i class="fa-solid fa-check text-brand-500 mt-0.5 shrink-0" aria-hidden="true"></i>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('register') }}" class="c-btn-primary w-full py-3 text-base">
                    <i class="fa-solid fa-rocket text-xs" aria-hidden="true"></i> Começar agora
                </a>
                <p class="mt-3 text-center text-xs text-surface-500 dark:text-surface-400">
                    Crie a conta grátis e assine o Profissional no painel.
                    <span class="font-medium text-surface-700 dark:text-surface-200">Cancele a qualquer momento.</span>
                </p>
            </article>
        </div>

        {{-- Nota sobre dados — Reciprocidade --}}
        <div class="mt-8 c-card p-5 text-sm text-surface-600 dark:text-surface-400 flex gap-3">
            <i class="fa-solid fa-circle-info text-brand-500 mt-0.5 shrink-0" aria-hidden="true"></i>
            <p>
                Voltou para o Básico? <strong class="text-surface-700 dark:text-surface-200">Seus dados continuam disponíveis.</strong>
                Apenas novos cadastros acima dos limites do Básico ficam bloqueados até você reduzir o uso ou
                voltar ao Profissional. Pagamentos processados com segurança pela
                <a href="https://stripe.com" target="_blank" rel="noopener noreferrer" class="underline hover:text-brand-600">Stripe</a>.
            </p>
        </div>

        {{-- Comparação rápida de recursos — Autoridade --}}
        <div class="mt-6 overflow-x-auto rounded-card border border-surface-200 dark:border-surface-800">
            <table class="w-full text-sm" role="table" aria-label="Comparação de recursos por plano">
                <thead>
                    <tr class="border-b border-surface-200 dark:border-surface-800 bg-surface-50 dark:bg-surface-900/50">
                        <th scope="col" class="c-th text-left pl-5">Recurso</th>
                        <th scope="col" class="c-th text-center">Básico</th>
                        <th scope="col" class="c-th text-center text-brand-600 dark:text-brand-400">Profissional</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-100 dark:divide-surface-800">
                    @foreach([
                        ['Clientes', '10', 'Ilimitados'],
                        ['Produtos e serviços', '20', 'Ilimitados'],
                        ['Orçamentos / mês', '5', 'Ilimitados'],
                        ['Formulários de captação', '1', 'Ilimitados'],
                        ['Anexos por cliente', '10', '1.000'],
                        ['Análise com IA', null, true],
                        ['Exportação XLSX/CSV', null, true],
                        ['PDFs (orçamentos e OS)', null, true],
                        ['API REST + Swagger', null, true],
                    ] as [$feature, $basic, $pro])
                        <tr class="hover:bg-surface-50 dark:hover:bg-surface-900/50 transition">
                            <td class="c-td pl-5">{{ $feature }}</td>
                            <td class="c-td text-center text-surface-500">
                                @if($basic === null)
                                    <i class="fa-solid fa-xmark text-surface-300 dark:text-surface-600" aria-label="Não disponível no plano Básico"></i>
                                @else
                                    {{ $basic }}
                                @endif
                            </td>
                            <td class="c-td text-center text-brand-600 dark:text-brand-400 font-semibold">
                                @if($pro === true)
                                    <i class="fa-solid fa-check text-accent-500" aria-label="Disponível no plano Profissional"></i>
                                @else
                                    {{ $pro }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
