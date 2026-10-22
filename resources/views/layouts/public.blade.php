<!DOCTYPE html>
<html lang="pt-BR" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <meta name="msapplication-TileColor" content="#4f46e5">
    <meta name="format-detection" content="telephone=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Clientify360">
    <meta name="application-name" content="Clientify360">

    {{-- Medição: Google Tag Manager + GA4 + Search Console --}}
    <x-analytics />

    {{-- SEO / SEM / GEO / AEO --}}
    @hasSection('seo')
        @yield('seo')
    @else
        <x-seo
            :title="trim($__env->yieldContent('title')) ?: null"
            :description="trim($__env->yieldContent('meta_description')) ?: 'Clientify360 - Sistema de gestão de clientes inteligente para o seu negócio.'"
        />
    @endif

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81', 950: '#1e1b4b',
                        },
                        // Aliases legados (mantidos para compatibilidade de views antigas)
                        primary: {
                            50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
                            400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca',
                            800: '#3730a3', 900: '#312e81', 950: '#1e1b4b',
                        },
                        accent: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
                            400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
                            800: '#065f46', 900: '#064e3b',
                        },
                        secondary: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
                            400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
                            800: '#065f46', 900: '#064e3b',
                        },
                        surface: {
                            50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
                            400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155',
                            800: '#1e293b', 900: '#0f172a', 950: '#020617',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); }
        html { scroll-behavior: smooth; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
    </style>

    @stack('styles')
</head>
<body class="min-h-screen bg-white dark:bg-surface-950 text-surface-800 dark:text-surface-200 font-sans antialiased">

    <x-analytics-noscript />

    <!-- Header -->
    <header class="glass-nav sticky top-0 z-50 border-b border-surface-200/60 dark:border-surface-800/60" x-data="{ mobileMenu: false }">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center space-x-3 group" aria-label="Página inicial">
                    <div class="w-9 h-9 bg-gradient-to-br from-brand-500 to-brand-700 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/25 group-hover:shadow-brand-500/40 transition-shadow">
                        <i class="fa-solid fa-chart-line text-white text-sm"></i>
                    </div>
                    <span class="text-lg font-bold bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-400 dark:to-brand-600 bg-clip-text text-transparent">Clientify360</span>
                </a>

                <!-- Desktop Navigation (âncoras da página única de vendas) -->
                @php
                    $navLinks = [
                        'inicio' => 'Início',
                        'sobre' => 'Sobre',
                        'servicos' => 'Serviços',
                        'ferramentas' => 'Ferramentas',
                        'planos' => 'Planos',
                        'contato' => 'Contato',
                    ];
                    $navBase = "px-4 py-2 rounded-lg text-sm font-medium transition text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-200 hover:bg-surface-100 dark:hover:bg-surface-800";
                    $navActive = "text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-950/50";
                    // Na home usamos âncoras relativas (#secao) para rolagem suave sem recarregar.
                    // Em outras páginas usamos a URL absoluta da home + âncora.
                    $onHome = request()->routeIs('home');
                    $anchorHref = fn (string $anchor): string => $onHome ? '#'.$anchor : route('home').'#'.$anchor;
                @endphp
                <div class="hidden md:flex items-center space-x-1" x-data="{ active: 'inicio' }" x-on:section-change.window="active = $event.detail">
                    @foreach($navLinks as $anchor => $label)
                        <a href="{{ $anchorHref($anchor) }}"
                           @click="active = '{{ $anchor }}'"
                           :class="active === '{{ $anchor }}' ? '{{ $navActive }}' : ''"
                           class="{{ $navBase }}">{{ $label }}</a>
                    @endforeach
                </div>

                <!-- Right side actions -->
                <div class="hidden md:flex items-center space-x-3">
                    <!-- Dark mode toggle -->
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2.5 rounded-xl hover:bg-surface-100 dark:hover:bg-surface-800 transition" aria-label="Alternar tema" title="Alternar tema claro/escuro">
                        <i x-show="!darkMode" class="fa-solid fa-moon text-surface-500 text-sm"></i>
                        <i x-show="darkMode" class="fa-solid fa-sun text-yellow-400 text-sm"></i>
                    </button>

                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition shadow-sm shadow-brand-600/25 hover:shadow-brand-600/40">
                            <i class="fa-solid fa-gauge-high mr-2 text-xs"></i> Painel
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-medium text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-200 transition">Entrar</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-xl hover:bg-brand-700 transition shadow-sm shadow-brand-600/25 hover:shadow-brand-600/40">
                            <i class="fa-solid fa-user-plus mr-2 text-xs"></i> Cadastrar
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <button @click="mobileMenu = !mobileMenu" class="md:hidden p-2.5 rounded-xl hover:bg-surface-100 dark:hover:bg-surface-800 transition" aria-label="Menu">
                    <i x-show="!mobileMenu" class="fa-solid fa-bars text-surface-600 dark:text-surface-400"></i>
                    <i x-show="mobileMenu" class="fa-solid fa-xmark text-surface-600 dark:text-surface-400"></i>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <div x-show="mobileMenu" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2" class="md:hidden pb-4 border-t border-surface-200 dark:border-surface-800 mt-2" x-cloak>
                <div class="flex flex-col space-y-1 pt-4">
                    @foreach($navLinks as $anchor => $label)
                        <a href="{{ $anchorHref($anchor) }}" @click="mobileMenu = false" class="px-4 py-2.5 rounded-xl text-sm font-medium text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800 transition">{{ $label }}</a>
                    @endforeach
                    <hr class="my-2 border-surface-200 dark:border-surface-800">
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="px-4 py-2.5 text-left rounded-xl text-sm font-medium text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                        <i class="fa-solid fa-circle-half-stroke mr-2"></i> Alternar Tema
                    </button>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-brand-600 text-white rounded-xl text-center text-sm font-medium">
                            <i class="fa-solid fa-gauge-high mr-2"></i> Painel
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl text-sm font-medium text-surface-600 dark:text-surface-400 hover:bg-surface-100 dark:hover:bg-surface-800 transition">Entrar</a>
                        <a href="{{ route('register') }}" class="px-4 py-2.5 bg-brand-600 text-white rounded-xl text-center text-sm font-medium">Cadastrar</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-surface-900 dark:bg-surface-950 text-surface-300 border-t border-surface-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                <!-- Brand -->
                <div class="lg:col-span-1">
                    <div class="flex items-center space-x-3 mb-5">
                        <div class="w-9 h-9 bg-gradient-to-br from-brand-500 to-brand-700 rounded-xl flex items-center justify-center">
                            <i class="fa-solid fa-chart-line text-white text-sm"></i>
                        </div>
                        <span class="text-lg font-bold text-white">Clientify360</span>
                    </div>
                    <p class="text-sm leading-relaxed text-surface-400">Sistema de gestão de clientes inteligente, seguro e fácil de usar. Organize seus contatos e impulsione seus resultados.</p>
                </div>

                <!-- Links -->
                <div>
                    <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Navegação</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ $anchorHref('inicio') }}" class="text-surface-400 hover:text-brand-400 transition">Início</a></li>
                        <li><a href="{{ $anchorHref('sobre') }}" class="text-surface-400 hover:text-brand-400 transition">Sobre</a></li>
                        <li><a href="{{ $anchorHref('servicos') }}" class="text-surface-400 hover:text-brand-400 transition">Serviços</a></li>
                        <li><a href="{{ $anchorHref('ferramentas') }}" class="text-surface-400 hover:text-brand-400 transition">Ferramentas</a></li>
                        <li><a href="{{ $anchorHref('planos') }}" class="text-surface-400 hover:text-brand-400 transition">Planos</a></li>
                        <li><a href="{{ $anchorHref('contato') }}" class="text-surface-400 hover:text-brand-400 transition">Contato</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('privacy') }}" class="text-surface-400 hover:text-brand-400 transition">Política de Privacidade</a></li>
                        <li><a href="{{ route('login') }}" class="text-surface-400 hover:text-brand-400 transition">Área do Cliente</a></li>
                        <li><a href="{{ route('register') }}" class="text-surface-400 hover:text-brand-400 transition">Criar Conta</a></li>
                    </ul>
                </div>

                <!-- Developer -->
                <div>
                    <h3 class="text-white font-semibold text-sm uppercase tracking-wider mb-4">Desenvolvido por</h3>
                    <p class="text-sm text-surface-400 mb-2"><strong class="text-surface-300">Grupo 3W Online</strong></p>
                    <p class="text-sm text-surface-400 mb-4">Sites e sistemas personalizados sob demanda.</p>
                    <a href="https://www.3wonline.com.br" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-brand-600/20 text-brand-400 border border-brand-600/30 rounded-xl hover:bg-brand-600/30 transition text-sm font-medium">
                        <i class="fa-solid fa-globe mr-2 text-xs"></i> Visite nosso site
                    </a>
                </div>
            </div>

            <hr class="border-surface-800 my-10">

            <div class="flex flex-col md:flex-row justify-between items-center text-xs text-surface-500">
                <p>&copy; {{ date('Y') }} Clientify360. Todos os direitos reservados.</p>
                <p class="mt-2 md:mt-0">Sistema desenvolvido por <strong class="text-surface-400">HOTWYL | WILLFROMBRASIL</strong></p>
            </div>
        </div>
    </footer>

    <!-- Loading Overlay -->
    <div x-data="loadingOverlay()" x-show="loading" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[9999] flex items-center justify-center bg-surface-900/60 backdrop-blur-sm" x-cloak>
        <div class="flex flex-col items-center space-y-4">
            <div class="relative">
                <div class="w-14 h-14 border-4 border-brand-200 dark:border-brand-900 rounded-full"></div>
                <div class="absolute top-0 left-0 w-14 h-14 border-4 border-transparent border-t-brand-500 rounded-full animate-spin"></div>
            </div>
            <p class="text-sm font-medium text-white">Processando...</p>
        </div>
    </div>

    <script>
        function loadingOverlay() {
            return {
                loading: false,
                init() {
                    document.querySelectorAll('form').forEach(form => {
                        form.addEventListener('submit', (e) => {
                            if (form.classList.contains('hidden')) return;
                            if (form.hasAttribute('data-no-loading')) return;
                            this.loading = true;
                        });
                    });

                    document.querySelectorAll('a[href]').forEach(link => {
                        link.addEventListener('click', (e) => {
                            const href = link.getAttribute('href');
                            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                            if (link.hasAttribute('target') && link.getAttribute('target') === '_blank') return;
                            if (link.hasAttribute('data-no-loading')) return;
                            if (e.ctrlKey || e.metaKey || e.shiftKey) return;
                            this.loading = true;
                        });
                    });

                    window.addEventListener('pageshow', () => {
                        this.loading = false;
                    });
                }
            }
        }
    </script>

    <x-cookie-consent />

    {{-- Scroll-spy: destaca o item do menu conforme a seção visível --}}
    <script>
        (function () {
            var ids = ['inicio', 'sobre', 'servicos', 'ferramentas', 'planos', 'contato'];
            var sections = ids
                .map(function (id) { return document.getElementById(id); })
                .filter(Boolean);
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
