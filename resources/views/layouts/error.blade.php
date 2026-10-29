{{--
    Error pages layout. Must not touch the session, the database, CSRF, consent or analytics:
    404/500 can render outside the web middleware or with the database down.
--}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('layouts.partials.theme-init')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Erro') | Clientify360</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/favicon.svg') }}">
    @if (file_exists(public_path('build/manifest.json')))
        <link rel="preload" as="font" type="font/woff2" crossorigin href="{{ Vite::asset('resources/fonts/inter-latin-wght-normal.woff2') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen flex flex-col bg-surface-50 dark:bg-surface-950 text-surface-800 dark:text-surface-200 font-sans antialiased">
    <header class="glass-nav border-b border-surface-200/70 dark:border-surface-800/70">
        <div class="max-w-7xl mx-auto h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center" aria-label="Clientify360, página inicial">
                <img src="{{ asset('images/logo.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto dark:hidden">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto hidden dark:block">
            </a>
            <div class="flex items-center gap-1">
                <x-theme-toggle />
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                    <i class="fa-solid fa-house text-xs" aria-hidden="true"></i> Início
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 flex items-center justify-center px-4 py-16 md:py-24 bg-grid">
        <div class="w-full max-w-xl text-center">
            <div class="mx-auto mb-6 w-20 h-20 rounded-3xl flex items-center justify-center @yield('tone', 'bg-brand-50 text-brand-600 dark:bg-brand-950/50 dark:text-brand-400')">
                <i class="fa-solid @yield('icon', 'fa-circle-exclamation') text-3xl" aria-hidden="true"></i>
            </div>
            <p class="text-6xl md:text-7xl font-extrabold tracking-tight text-surface-900 dark:text-white">@yield('code')</p>
            <h1 class="mt-3 text-2xl md:text-3xl font-bold text-surface-900 dark:text-white">@yield('heading')</h1>
            <div class="mt-4 text-surface-600 dark:text-surface-300 leading-relaxed space-y-3">
                @yield('message')
            </div>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/') }}" class="c-btn-primary px-6 py-3">
                    <i class="fa-solid fa-house text-xs" aria-hidden="true"></i> Voltar para a página inicial
                </a>
                @yield('secondary')
            </div>
        </div>
    </main>

    <x-cta-3w variant="section" :placement="'error-'.trim($__env->yieldContent('code'))" />

    @include('layouts.partials.footer', ['minimal' => true])

    <x-loading-overlay />
</body>
</html>
