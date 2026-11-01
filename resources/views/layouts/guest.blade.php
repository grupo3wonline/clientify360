<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        @include('layouts.partials.head', [
            'noindex' => true,
            'title' => trim($__env->yieldContent('title')) ?: 'Acesso',
            'description' => 'Área de acesso do Clientify360: entre, crie sua conta ou recupere sua senha.',
        ])
    </head>
    <body class="min-h-screen flex flex-col font-sans antialiased bg-surface-50 dark:bg-surface-950 text-surface-800 dark:text-surface-200">
        {{-- Compact header: logo, theme toggle and back to the site --}}
        <header class="glass-nav sticky top-0 z-40 border-b border-surface-200/70 dark:border-surface-800/70">
            <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center" aria-label="Clientify360, página inicial">
                    <img src="{{ asset('images/logo.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto dark:hidden">
                    <img src="{{ asset('images/logo-dark.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto hidden dark:block">
                </a>
                <div class="flex items-center gap-1">
                    <x-theme-toggle />
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                        <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> <span>Voltar ao site</span>
                    </a>
                </div>
            </div>
        </header>

        <main id="main-content" class="flex-1 grid lg:grid-cols-2">
            {{-- Brand panel (desktop) --}}
            <div class="hidden lg:flex relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-700 to-brand-950 text-white">
                <div class="absolute inset-0 opacity-20 pointer-events-none" aria-hidden="true">
                    <div class="absolute top-20 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
                    <div class="absolute bottom-10 right-0 w-96 h-96 bg-accent-400 rounded-full blur-3xl"></div>
                </div>
                <div class="relative z-10 flex flex-col justify-center px-12 xl:px-20 py-16 max-w-xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-accent-300">CRM para pequenos negócios</p>
                    <p class="mt-4 text-3xl xl:text-4xl font-bold leading-tight">Seus clientes, vendas e agenda organizados em um só lugar.</p>
                    <p class="mt-5 text-brand-100 leading-relaxed">Comece grátis no Básico e evolua para o Profissional quando precisar de mais recursos.</p>
                    <ul class="mt-10 space-y-4 text-brand-50">
                        <li class="flex items-center gap-3"><span class="c-icon-tile bg-white/10"><i class="fa-solid fa-shield-halved text-accent-300" aria-hidden="true"></i></span> Dados isolados por conta, com controle de acesso por papel</li>
                        <li class="flex items-center gap-3"><span class="c-icon-tile bg-white/10"><i class="fa-solid fa-mobile-screen text-accent-300" aria-hidden="true"></i></span> Funciona no celular, tablet e computador</li>
                        <li class="flex items-center gap-3"><span class="c-icon-tile bg-white/10"><i class="fa-solid fa-scale-balanced text-accent-300" aria-hidden="true"></i></span> Conformidade com LGPD, GDPR, CCPA e POPIA</li>
                    </ul>
                </div>
            </div>

            {{-- Auth card --}}
            <div class="flex flex-col items-center justify-center px-4 sm:px-8 py-10 bg-grid">
                <div class="w-full max-w-md">
                    <div class="c-card p-6 sm:p-8 shadow-xl shadow-surface-900/5 dark:shadow-none">
                        {{ $slot }}
                    </div>
                    <x-cta-3w variant="inline" placement="auth" class="mt-6" />
                </div>
            </div>
        </main>

        @include('layouts.partials.footer')

        <x-loading-overlay />
        <x-flash />
        <x-cookie-consent />

        @stack('scripts')
    </body>
</html>
