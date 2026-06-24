@extends('layouts.public')

@section('title', 'Contato')
@section('meta_description', 'Entre em contato com a equipe Clientify360. Fale conosco para dúvidas, suporte ou parcerias.')

@section('content')
    <section class="bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 dark:from-brand-900 dark:to-surface-950 text-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Contato</h1>
            <p class="text-lg text-brand-200 max-w-2xl mx-auto">Fale conosco. Estamos prontos para ajudar.</p>
        </div>
    </section>

    <section class="py-16 md:py-24 bg-white dark:bg-surface-950">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div>
                <h2 class="text-2xl font-bold text-surface-900 dark:text-white mb-6">Fale Conosco</h2>
                <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-8">Tem dúvidas sobre o Clientify360? Quer saber mais sobre nossos planos ou precisa de suporte? Entre em contato pelos canais abaixo.</p>

                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 bg-brand-50 dark:bg-brand-950/50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-envelope text-brand-600 dark:text-brand-400"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-surface-900 dark:text-white text-sm">E-mail</h3>
                            <p class="text-sm text-surface-500 dark:text-surface-400">contato@clientify360.com.br</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 bg-accent-50 dark:bg-accent-950/50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-globe text-accent-600 dark:text-accent-400"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-surface-900 dark:text-white text-sm">Site Desenvolvedor</h3>
                            <a href="https://www.3wonline.com.br" target="_blank" class="text-sm text-brand-600 dark:text-brand-400 hover:underline">www.3wonline.com.br</a>
                        </div>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div class="w-10 h-10 bg-purple-50 dark:bg-purple-950/50 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid fa-clock text-purple-600 dark:text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-surface-900 dark:text-white text-sm">Horário de Atendimento</h3>
                            <p class="text-sm text-surface-500 dark:text-surface-400">Segunda a Sexta, 9h às 18h</p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 p-5 bg-brand-50 dark:bg-brand-950/30 rounded-2xl border border-brand-200 dark:border-brand-800">
                    <p class="text-sm font-semibold text-brand-800 dark:text-brand-200 mb-2"><i class="fa-solid fa-tag mr-1"></i> Oferta Especial</p>
                    <p class="text-xs text-brand-700 dark:text-brand-300 mb-3">Plano Profissional com 50% OFF — de <span class="line-through">R$ 79,90</span> por <strong>R$ 39,90/mês</strong></p>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white text-xs font-medium rounded-xl transition">
                        <i class="fa-solid fa-rocket mr-1.5"></i> Aproveitar Desconto
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-900 dark:to-surface-950 text-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <p class="text-brand-200 mb-3">Precisa de um site ou sistema personalizado?</p>
            <a href="https://www.3wonline.com.br" target="_blank" class="inline-flex items-center px-6 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-medium rounded-xl transition">
                <i class="fa-solid fa-building mr-2"></i> Conheça o Grupo 3W Online
            </a>
        </div>
    </section>
@endsection
