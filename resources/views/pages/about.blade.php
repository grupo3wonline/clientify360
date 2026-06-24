@extends('layouts.public')

@section('title', 'Sobre')
@section('meta_description', 'Conheça o Clientify360 - CRM inteligente com IA integrada para profissionais e empresas que querem crescer.')

@section('content')
    <!-- Hero -->
    <section class="bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 dark:from-brand-900 dark:to-surface-950 text-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-3xl md:text-5xl font-bold mb-4">Sobre o Clientify360</h1>
            <p class="text-lg text-brand-200 max-w-2xl mx-auto">A plataforma que une gestão de clientes, inteligência artificial e automação para transformar a forma como você faz negócios.</p>
        </div>
    </section>

    <!-- Mission -->
    <section class="py-16 md:py-24 bg-white dark:bg-surface-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <p class="text-sm font-semibold text-brand-600 dark:text-brand-400 uppercase tracking-wider mb-3">Nossa Missão</p>
                    <h2 class="text-3xl font-bold text-surface-900 dark:text-white mb-6">Democratizar a gestão inteligente de clientes</h2>
                    <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-4">O Clientify360 nasceu para resolver um problema real: profissionais e pequenas empresas precisam de ferramentas poderosas de CRM, mas não podem pagar por soluções complexas e caras.</p>
                    <p class="text-surface-600 dark:text-surface-400 leading-relaxed mb-4">Nossa plataforma combina o poder da inteligência artificial com uma interface simples e intuitiva, permitindo que qualquer profissional gerencie seus clientes como as grandes empresas — mas por uma fração do custo.</p>
                    <p class="text-surface-600 dark:text-surface-400 leading-relaxed">Com integração a Gemini, Groq e OpenRouter, oferecemos análises automáticas de perfil, qualificação de leads e projeções de negócio que antes só eram possíveis com equipes de dados dedicadas.</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-800">
                        <i class="fa-solid fa-brain text-3xl text-brand-500 mb-3"></i>
                        <p class="text-2xl font-bold text-surface-900 dark:text-white">3</p>
                        <p class="text-xs text-surface-500">IAs Integradas</p>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-800">
                        <i class="fa-solid fa-shield-halved text-3xl text-accent-500 mb-3"></i>
                        <p class="text-2xl font-bold text-surface-900 dark:text-white">100%</p>
                        <p class="text-xs text-surface-500">LGPD Compliant</p>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-800">
                        <i class="fa-solid fa-mobile-screen text-3xl text-purple-500 mb-3"></i>
                        <p class="text-2xl font-bold text-surface-900 dark:text-white">100%</p>
                        <p class="text-xs text-surface-500">Responsivo</p>
                    </div>
                    <div class="bg-surface-50 dark:bg-surface-900 rounded-2xl p-6 text-center border border-surface-200 dark:border-surface-800">
                        <i class="fa-solid fa-bolt text-3xl text-amber-500 mb-3"></i>
                        <p class="text-2xl font-bold text-surface-900 dark:text-white">24/7</p>
                        <p class="text-xs text-surface-500">Disponível</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values -->
    <section class="py-16 md:py-24 bg-surface-50 dark:bg-surface-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-surface-900 dark:text-white mb-4">Nossos Valores</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-14 h-14 bg-brand-50 dark:bg-brand-950/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-lock text-brand-600 dark:text-brand-400 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Privacidade em Primeiro Lugar</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Seus dados são seus. Cada usuário acessa apenas suas próprias informações, com criptografia e conformidade LGPD.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-accent-50 dark:bg-accent-950/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-rocket text-accent-600 dark:text-accent-400 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Inovação Acessível</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Tecnologia de ponta (IA, análise de dados, automação) disponível para todos, sem complexidade.</p>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 bg-purple-50 dark:bg-purple-950/50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-handshake text-purple-600 dark:text-purple-400 text-xl"></i>
                    </div>
                    <h3 class="font-bold text-surface-900 dark:text-white mb-2">Foco no Resultado</h3>
                    <p class="text-sm text-surface-500 dark:text-surface-400">Cada funcionalidade foi pensada para gerar resultado: mais vendas, melhor atendimento e clientes mais satisfeitos.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-16 bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-900 dark:to-surface-950 text-white">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <div class="inline-flex items-center px-3 py-1.5 bg-red-500/20 border border-red-400/30 rounded-full text-sm text-red-200 mb-4 animate-pulse">
                <i class="fa-solid fa-fire mr-2"></i> 50% OFF por tempo limitado
            </div>
            <h2 class="text-2xl md:text-3xl font-bold mb-4">Pronto para transformar sua gestão?</h2>
            <p class="text-brand-200 mb-6">De <span class="line-through">R$ 79,90</span> por apenas <strong class="text-white text-xl">R$ 39,90/mês</strong></p>
            <a href="{{ route('register') }}" class="inline-flex items-center px-7 py-4 bg-white text-brand-700 font-bold rounded-xl hover:bg-brand-50 transition shadow-xl">
                <i class="fa-solid fa-rocket mr-2"></i> Começar Agora com 50% OFF
            </a>
        </div>
    </section>
@endsection
