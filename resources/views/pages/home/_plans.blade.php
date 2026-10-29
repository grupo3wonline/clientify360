@php($basic = $plans['basic'])
<section id="planos" class="c-section bg-white dark:bg-surface-950" aria-labelledby="planos-title">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-tags" aria-hidden="true"></i> Planos</p>
            <h2 id="planos-title" class="c-h2 mt-3">Escolha seu plano</h2>
            <p class="c-lead mt-4">Comece grátis e faça upgrade quando quiser.</p>
        </div>

        <div class="mt-12 grid gap-6 md:grid-cols-2">
            {{-- Básico --}}
            <article class="c-card p-7 sm:p-8 flex flex-col">
                <h3 class="text-xl font-bold text-surface-900 dark:text-white">{{ $basic->name }}</h3>
                <p class="mt-1 text-sm text-surface-500 dark:text-surface-400">{{ $basic->description }}</p>
                <p class="mt-6 mb-8"><span class="text-4xl font-extrabold text-surface-900 dark:text-white">Grátis</span> <span class="text-sm text-surface-500">para sempre</span></p>
                <ul class="space-y-3 mb-8 flex-1">
                    @foreach($basic->features as $feature)
                        <li class="flex gap-2.5 text-sm text-surface-600 dark:text-surface-400"><i class="fa-solid fa-check text-accent-500 mt-0.5" aria-hidden="true"></i> {{ $feature }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="c-btn-ghost w-full py-3">Criar conta grátis</a>
            </article>

            {{-- Profissional --}}
            <article class="relative c-card p-7 sm:p-8 flex flex-col border-2 border-brand-500 dark:border-brand-500 shadow-xl shadow-brand-500/10">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-brand-600 px-4 py-1 text-xs font-bold text-white shadow">RECOMENDADO · {{ $pro->discountPercent() }}% OFF</span>
                <h3 class="text-xl font-bold text-surface-900 dark:text-white">{{ $pro->name }}</h3>
                <p class="mt-1 text-sm text-surface-500 dark:text-surface-400">{{ $pro->description }}</p>
                <p class="mt-6 text-xs text-surface-500 dark:text-surface-400">Preço de referência <s class="text-lg text-surface-400">{{ $pro->formattedOriginalPrice() }}</s></p>
                <p class="mb-8 flex flex-wrap items-baseline gap-2">
                    <span class="sr-only">Preço promocional:</span>
                    <span class="text-4xl font-extrabold text-surface-900 dark:text-white">{{ $pro->formattedPrice() }}</span>
                    <span class="text-sm text-surface-500">/mês</span>
                    <span class="c-badge bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200">Preço promocional · economize {{ $pro->formattedYearlySaving() }}/ano</span>
                </p>
                <ul class="space-y-3 mb-8 flex-1">
                    @foreach($pro->features as $feature)
                        <li class="flex gap-2.5 text-sm text-surface-600 dark:text-surface-400"><i class="fa-solid fa-check text-brand-500 mt-0.5" aria-hidden="true"></i> {{ $feature }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="c-btn-primary w-full py-3"><i class="fa-solid fa-rocket text-xs" aria-hidden="true"></i> Começar agora</a>
                <p class="mt-3 text-center text-xs text-surface-500 dark:text-surface-400">Crie a conta grátis e assine o Profissional no painel. Cancele a qualquer momento. Sem fidelidade.</p>
            </article>
        </div>

        <div class="mt-8 c-card p-5 text-sm text-surface-600 dark:text-surface-400 flex gap-3">
            <i class="fa-solid fa-circle-info text-brand-500 mt-0.5" aria-hidden="true"></i>
            <p>Voltou para o Básico? Seus dados continuam disponíveis. Apenas novos cadastros acima dos limites do Básico ficam bloqueados até você reduzir o uso ou voltar ao Profissional. Pagamentos processados pela Stripe.</p>
        </div>
    </div>
</section>
