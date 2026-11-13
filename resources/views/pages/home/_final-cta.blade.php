<section class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 text-white"
         aria-labelledby="final-cta-title">
    {{-- Background decorativo --}}
    <div class="absolute inset-0 opacity-20 pointer-events-none" aria-hidden="true">
        <div class="absolute top-10 right-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 left-10 w-56 h-56 bg-accent-400 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 bg-grid opacity-30"></div>
    </div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 text-center">

        {{-- Gatilho de Urgência --}}
        <p class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-brand-100">
            <i class="fa-solid fa-fire text-warning-400" aria-hidden="true"></i>
            Preço promocional com {{ $pro->discountPercent() }}% de desconto — oferta por tempo limitado
        </p>

        {{-- Gatilho de Transformação --}}
        <h2 id="final-cta-title" class="mt-6 text-3xl md:text-4xl font-bold leading-tight">
            Transforme a gestão do seu negócio<br class="hidden sm:block"> ainda hoje
        </h2>

        {{-- Gatilho de Escassez / Valor --}}
        <div class="mt-5 flex flex-col sm:flex-row items-center justify-center gap-4">
            <div class="text-center sm:text-left">
                <p class="text-brand-200 text-sm">Preço de referência</p>
                <p class="text-xl line-through text-brand-300">{{ $pro->formattedOriginalPrice() }}/mês</p>
            </div>
            <div class="hidden sm:block w-px h-10 bg-white/20"></div>
            <div class="text-center sm:text-left">
                <p class="text-brand-200 text-sm">Preço promocional</p>
                <p class="text-3xl font-extrabold text-white">{{ $pro->formattedPrice() }}<span class="text-lg font-normal text-brand-200">/mês</span></p>
            </div>
            <div class="hidden sm:block w-px h-10 bg-white/20"></div>
            <div class="text-center sm:text-left">
                <p class="text-brand-200 text-sm">Economia anual</p>
                <p class="text-xl font-bold text-accent-300">{{ $pro->formattedYearlySaving() }}</p>
            </div>
        </div>

        {{-- Valor por dia — Reciprocidade --}}
        <p class="mt-4 text-brand-200 text-sm">
            <i class="fa-solid fa-mug-hot mr-1" aria-hidden="true"></i>
            Menos que um café por dia:
            <strong class="text-white">{{ \App\Support\Format::money($pro->price / 30) }}/dia</strong>
            para ter um CRM completo.
        </p>

        {{-- CTA principal --}}
        <a href="{{ route('register') }}"
           class="mt-8 inline-flex items-center justify-center gap-2 rounded-button bg-white px-8 py-4 text-lg font-bold text-brand-700 shadow-xl shadow-black/20 transition hover:bg-brand-50 hover:-translate-y-0.5 hover:shadow-2xl hover:shadow-black/25">
            <i class="fa-solid fa-rocket" aria-hidden="true"></i> Criar minha conta grátis
        </a>

        {{-- Garantias — Autoridade --}}
        <ul class="mt-6 flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm text-brand-200" aria-label="Garantias">
            <li class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-accent-400 text-xs" aria-hidden="true"></i>
                Sem cartão de crédito
            </li>
            <li class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-accent-400 text-xs" aria-hidden="true"></i>
                Cancele quando quiser
            </li>
            <li class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-accent-400 text-xs" aria-hidden="true"></i>
                Dados seguros (LGPD)
            </li>
            <li class="flex items-center gap-1.5">
                <i class="fa-solid fa-check text-accent-400 text-xs" aria-hidden="true"></i>
                Plano Básico sempre grátis
            </li>
        </ul>

        <p class="mt-5 text-xs text-brand-300">
            Comece no Básico grátis. O desconto vale ao assinar o Profissional no painel.
        </p>
    </div>
</section>
