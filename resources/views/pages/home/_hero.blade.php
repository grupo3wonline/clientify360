<section id="inicio" class="relative overflow-hidden bg-surface-950 text-white scroll-mt-20" aria-labelledby="hero-title">
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-40 -left-32 w-[36rem] h-[36rem] rounded-full bg-brand-600/40 blur-3xl"></div>
        <div class="absolute -bottom-48 right-[-10rem] w-[34rem] h-[34rem] rounded-full bg-accent-500/20 blur-3xl"></div>
        <div class="absolute inset-0 bg-grid opacity-40"></div>
    </div>

    <div class="relative c-container py-16 md:py-24 lg:py-28 grid gap-12 lg:grid-cols-[1.1fr_1fr] items-center">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-semibold text-accent-200">
                <i class="fa-solid fa-tag" aria-hidden="true"></i> Preço promocional: Profissional de {{ $pro->formattedOriginalPrice() }} por {{ $pro->formattedPrice() }}/mês
            </p>

            <h1 id="hero-title" class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.05]">
                O CRM com IA que organiza
                <span class="bg-gradient-to-r from-brand-300 via-accent-300 to-accent-400 bg-clip-text text-transparent">clientes, vendas e agenda</span>
                do seu negócio
            </h1>
            <p class="mt-6 text-lg text-surface-300 leading-relaxed max-w-xl">
                Cadastre clientes, acompanhe o funil de vendas, envie orçamentos, controle estoque e financeiro e receba análises com inteligência artificial. Tudo em uma plataforma segura, que funciona no celular e no computador.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-button bg-white px-7 py-4 font-bold text-brand-700 shadow-xl shadow-black/20 transition hover:bg-brand-50">
                    <i class="fa-solid fa-rocket" aria-hidden="true"></i> Começar grátis agora
                </a>
                <a href="#planos" class="inline-flex items-center justify-center gap-2 rounded-button border border-white/20 px-7 py-4 font-semibold text-white transition hover:border-white/50 hover:bg-white/5">
                    <i class="fa-solid fa-table-list text-sm" aria-hidden="true"></i> Comparar planos
                </a>
            </div>
            <p class="mt-4 text-sm text-surface-400"><i class="fa-solid fa-shield-halved mr-1" aria-hidden="true"></i> Sem cartão de crédito para começar. Cancele quando quiser.</p>
            <p class="mt-2 text-sm text-surface-400">
                Precisa de um sistema sob medida?
                <a href="{{ \App\Support\Cta::url('landing-hero') }}" target="_blank" rel="noopener noreferrer" data-cta="landing-hero" class="font-semibold text-accent-300 underline underline-offset-2 hover:no-underline">Fale com o Grupo 3W Online</a>
            </p>
        </div>

        {{-- Product preview (decorative) --}}
        <div class="relative" aria-hidden="true">
            <div class="rounded-3xl border border-white/10 bg-white/5 p-4 sm:p-6 shadow-2xl backdrop-blur">
                <div class="flex items-center gap-2 pb-4 border-b border-white/10">
                    <span class="w-3 h-3 rounded-full bg-danger-400"></span><span class="w-3 h-3 rounded-full bg-warning-400"></span><span class="w-3 h-3 rounded-full bg-accent-400"></span>
                    <span class="ml-3 text-xs text-surface-400">clientify360 · painel</span>
                </div>
                <div class="grid grid-cols-3 gap-3 pt-4">
                    <div class="rounded-xl bg-white/10 p-3"><p class="text-[11px] text-surface-400">Clientes</p><p class="text-xl font-bold">128</p></div>
                    <div class="rounded-xl bg-white/10 p-3"><p class="text-[11px] text-surface-400">Orçamentos</p><p class="text-xl font-bold">24</p></div>
                    <div class="rounded-xl bg-white/10 p-3"><p class="text-[11px] text-surface-400">A receber</p><p class="text-xl font-bold">R$ 8,4 mil</p></div>
                </div>
                <div class="mt-4 space-y-2.5">
                    <div class="flex items-center gap-3 rounded-xl bg-white/10 p-3"><span class="c-icon-tile bg-accent-500/20 text-accent-300"><i class="fa-solid fa-brain"></i></span><div><p class="text-sm font-semibold">IA para análise de clientes</p><p class="text-xs text-surface-400">Gemini, Groq e OpenRouter</p></div></div>
                    <div class="flex items-center gap-3 rounded-xl bg-white/10 p-3"><span class="c-icon-tile bg-brand-500/25 text-brand-200"><i class="fa-solid fa-filter-circle-dollar"></i></span><div><p class="text-sm font-semibold">Funil de vendas em Kanban</p><p class="text-xs text-surface-400">Arraste oportunidades entre etapas</p></div></div>
                    <div class="flex items-center gap-3 rounded-xl bg-white/10 p-3"><span class="c-icon-tile bg-warning-500/20 text-warning-300"><i class="fa-solid fa-calendar-check"></i></span><div><p class="text-sm font-semibold">Agenda e lembretes</p><p class="text-xs text-surface-400">Nada de follow-up esquecido</p></div></div>
                </div>
                <p class="mt-4 text-[11px] text-surface-500">Números ilustrativos.</p>
            </div>
        </div>
    </div>

    <div class="relative border-t border-white/10 bg-white/5">
        <div class="c-container py-3 text-center text-sm text-surface-200">
            <i class="fa-solid fa-tag mr-1 text-accent-300" aria-hidden="true"></i>
            <strong>Preço promocional:</strong> Profissional de <s>{{ $pro->formattedOriginalPrice() }}</s> por <strong>{{ $pro->formattedPrice() }}/mês</strong>, uma economia de {{ $pro->formattedYearlySaving() }} por ano.
            <a href="#planos" class="ml-2 font-semibold underline underline-offset-2 hover:no-underline">Ver planos</a>
        </div>
    </div>
</section>
