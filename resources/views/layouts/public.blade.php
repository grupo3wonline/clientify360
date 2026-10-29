<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('layouts.partials.head', ['noindex' => false])

    {{-- SEO / SEM / GEO / AEO --}}
    @hasSection('seo')
        @yield('seo')
    @else
        <x-seo
            :title="trim($__env->yieldContent('title')) ?: null"
            :description="trim($__env->yieldContent('meta_description')) ?: 'Clientify360: CRM para pequenos negócios, com clientes, funil de vendas, orçamentos, agenda e financeiro em um só lugar.'"
        />
    @endif

    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-white dark:bg-surface-950 text-surface-800 dark:text-surface-200 font-sans antialiased">
    <x-analytics-noscript />

    <a href="#conteudo" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[10000] focus:px-4 focus:py-2.5 focus:rounded-xl focus:bg-brand-600 focus:text-white focus:text-sm">Pular para o conteúdo</a>

    @include('layouts.partials.public-header')

    <main id="conteudo" class="flex-1">
        @yield('content')
    </main>

    @hasSection('hide_cta')
    @else
        <x-cta-3w variant="section" :placement="trim($__env->yieldContent('cta_placement')) ?: 'site'" />
    @endif

    @include('layouts.partials.footer')

    <x-loading-overlay />
    <x-flash />
    <x-cookie-consent />

    {{-- Scroll-spy: highlights the menu item of the visible section --}}
    <script>
        (function () {
            var sections = Array.prototype.slice.call(document.querySelectorAll('main section[id]'));
            if (!sections.length || !('IntersectionObserver' in window)) return;
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        window.dispatchEvent(new CustomEvent('section-change', { detail: entry.target.id }));
                    }
                });
            }, { rootMargin: '-40% 0px -55% 0px', threshold: 0 });
            sections.forEach(function (section) { observer.observe(section); });
        })();
    </script>

    @stack('scripts')
</body>
</html>
