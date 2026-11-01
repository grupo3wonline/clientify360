{{-- Contact: only WhatsApp and e-mail links (no form, no iframe), plus the Grupo 3W Online CTA. --}}
@php($whatsApp = 'https://wa.me/5541996924829?text='.rawurlencode('Olá! Vim pelo Clientify360 e quero falar sobre um projeto.'))
<section id="contato" class="c-section bg-white dark:bg-surface-950" aria-labelledby="contato-title">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto">
            <p class="c-eyebrow justify-center"><i class="fa-solid fa-comments" aria-hidden="true"></i> Contato</p>
            <h2 id="contato-title" class="c-h2 mt-3">Fale Conosco</h2>
            <p class="c-lead mt-4">Tem dúvidas sobre o Clientify360? Quer saber mais sobre nossos planos ou precisa de suporte? Entre em contato pelos canais abaixo.</p>
        </div>

        <div class="mt-12 grid gap-6 lg:grid-cols-[1.2fr_1fr]">
            <div class="c-card p-6 sm:p-8">
                <span class="c-icon-tile bg-brand-600 text-white"><i class="fa-solid fa-building" aria-hidden="true"></i></span>
                <h3 class="mt-4 text-xl font-bold text-surface-900 dark:text-white">Grupo 3W Online</h3>
                <p class="mt-2 text-surface-600 dark:text-surface-400 leading-relaxed">O Grupo 3W Online desenvolve sites e sistemas personalizados sob demanda para empresas que querem vender mais e trabalhar melhor. É a equipe que criou e mantém o Clientify360.</p>
                <p class="mt-3 text-surface-600 dark:text-surface-400 leading-relaxed">Precisa de um site ou sistema personalizado? Conte o que você imagina: a gente transforma a ideia em um projeto com escopo, prazo e investimento claros.</p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <a href="{{ $whatsApp }}" target="_blank" rel="noopener noreferrer" class="group flex items-center gap-3 rounded-card border border-surface-200 dark:border-surface-800 p-4 transition hover:border-accent-400 hover:shadow-md">
                        <span class="c-icon-tile bg-accent-50 text-accent-700 dark:bg-accent-950/50 dark:text-accent-400"><i class="fa-brands fa-whatsapp text-lg" aria-hidden="true"></i></span>
                        <span><span class="block text-xs text-surface-500">WhatsApp</span><span class="font-semibold text-surface-900 dark:text-white">(41) 99692-4829</span></span>
                    </a>
                    <a href="mailto:contato@3wonline.com.br" class="group flex items-center gap-3 rounded-card border border-surface-200 dark:border-surface-800 p-4 transition hover:border-brand-400 hover:shadow-md">
                        <span class="c-icon-tile bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                        <span class="min-w-0"><span class="block text-xs text-surface-500">E-mail</span><span class="font-semibold text-surface-900 dark:text-white break-all">contato@3wonline.com.br</span></span>
                    </a>
                </div>

                <div class="mt-6 flex items-start gap-3 rounded-card bg-surface-50 dark:bg-surface-900 p-4">
                    <span class="c-icon-tile bg-white dark:bg-surface-800 text-brand-600 dark:text-brand-400"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
                    <div>
                        <h3 class="font-semibold text-surface-900 dark:text-white">Horário de Atendimento</h3>
                        <p class="text-sm text-surface-600 dark:text-surface-400">Segunda a Sexta, 9h às 18h</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="c-card p-6 sm:p-8 bg-gradient-to-br from-brand-600 to-brand-800 text-white border-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-accent-200">Sob medida para você</p>
                    <h3 class="mt-2 text-xl font-bold">{{ config('cta.headline') }}</h3>
                    <p class="mt-3 text-sm text-brand-100">{{ config('cta.scarcity') }}</p>
                    <p class="mt-2 text-sm font-semibold">{{ \App\Support\Cta::urgency() }}</p>
                    <a href="{{ \App\Support\Cta::url('contact') }}" target="_blank" rel="noopener noreferrer" data-cta="contact" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-button bg-white px-5 py-3 font-bold text-brand-700 transition hover:bg-brand-50">
                        {{ config('cta.button') }} <i class="fa-solid fa-arrow-up-right-from-square text-xs" aria-hidden="true"></i>
                    </a>
                </div>
                <div class="c-card p-6">
                    <p class="text-sm font-semibold text-brand-800 dark:text-brand-200"><i class="fa-solid fa-tag mr-1" aria-hidden="true"></i> Oferta Especial</p>
                    <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">Plano Profissional com {{ $pro->discountPercent() }}% OFF: de <s>{{ $pro->formattedOriginalPrice() }}</s> por <strong class="text-surface-900 dark:text-white">{{ $pro->formattedPrice() }}/mês</strong>.</p>
                    <a href="{{ route('register') }}" class="mt-4 c-btn-primary w-full"><i class="fa-solid fa-rocket text-xs" aria-hidden="true"></i> Aproveitar o preço promocional</a>
                </div>
            </div>
        </div>
    </div>
</section>
