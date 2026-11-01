<section class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 text-white" aria-labelledby="final-cta-title">
    <div class="absolute inset-0 opacity-20 pointer-events-none" aria-hidden="true">
        <div class="absolute top-10 right-20 w-72 h-72 bg-white rounded-full blur-3xl"></div>
    </div>
    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24 text-center">
        <p class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm text-brand-100">
            <i class="fa-solid fa-tag" aria-hidden="true"></i> Preço promocional com {{ $pro->discountPercent() }}% de desconto
        </p>
        <h2 id="final-cta-title" class="mt-6 text-3xl md:text-4xl font-bold">Não perca essa oportunidade</h2>
        <p class="mt-4 text-lg text-brand-100">De <s>{{ $pro->formattedOriginalPrice() }}</s> por apenas <strong class="text-white text-2xl">{{ $pro->formattedPrice() }}/mês</strong></p>
        <p class="mt-3 text-brand-200 max-w-2xl mx-auto">CRM completo com IA integrada, score de clientes, gestão de atendimentos e relatórios avançados. Tudo por cerca de {{ \App\Support\Format::money($pro->price / 30) }} por dia.</p>
        <a href="{{ route('register') }}" class="mt-8 inline-flex items-center justify-center gap-2 rounded-button bg-white px-8 py-4 text-lg font-bold text-brand-700 shadow-xl transition hover:bg-brand-50">
            <i class="fa-solid fa-rocket" aria-hidden="true"></i> Criar minha conta grátis
        </a>
        <p class="mt-4 text-sm text-brand-200">Comece no Básico grátis. O desconto vale ao assinar o Profissional no painel.</p>
    </div>
</section>
