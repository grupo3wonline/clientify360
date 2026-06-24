<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Clientify360') }} - Autenticação</title>

        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
                            accent: {
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
            .auth-pattern {
                background-image: radial-gradient(circle at 1px 1px, rgba(99, 102, 241, 0.08) 1px, transparent 0);
                background-size: 32px 32px;
            }
            .dark .auth-pattern {
                background-image: radial-gradient(circle at 1px 1px, rgba(129, 140, 248, 0.06) 1px, transparent 0);
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-surface-50 dark:bg-surface-950 text-surface-800 dark:text-surface-200">
        <div class="min-h-screen flex">

            <!-- Left Panel: Branding (hidden on mobile) -->
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 relative overflow-hidden">
                <!-- Decorative elements -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-20 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
                    <div class="absolute bottom-20 right-10 w-96 h-96 bg-accent-400 rounded-full blur-3xl"></div>
                </div>

                <div class="relative z-10 flex flex-col justify-center px-12 xl:px-16 text-white">
                    <!-- Logo -->
                    <div class="flex items-center space-x-3 mb-12">
                        <div class="w-12 h-12 bg-white/15 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20">
                            <i class="fa-solid fa-chart-line text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold">Clientify360</span>
                    </div>

                    <h1 class="text-3xl xl:text-4xl font-bold leading-tight mb-6">
                        Gerencie seus clientes com inteligência e simplicidade
                    </h1>
                    <p class="text-brand-200 text-lg leading-relaxed mb-10">
                        Organize contatos, acompanhe interações e impulsione seus resultados. Tudo em um só lugar, seguro e acessível.
                    </p>

                    <!-- Feature highlights -->
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-shield-halved text-accent-300 text-sm"></i>
                            </div>
                            <span class="text-brand-100">Dados 100% seguros e isolados</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-mobile-screen text-accent-300 text-sm"></i>
                            </div>
                            <span class="text-brand-100">Acesse de qualquer dispositivo</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-bolt text-accent-300 text-sm"></i>
                            </div>
                            <span class="text-brand-100">Interface rápida e intuitiva</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Auth Form -->
            <div class="flex-1 flex flex-col justify-center items-center px-4 sm:px-8 auth-pattern">
                <!-- Mobile Logo -->
                <div class="lg:hidden mb-8">
                    <a href="{{ route('home') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-brand-500 to-brand-700 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/25">
                            <i class="fa-solid fa-chart-line text-white"></i>
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-400 dark:to-brand-600 bg-clip-text text-transparent">Clientify360</span>
                    </a>
                </div>

                <!-- Dark mode toggle -->
                <div class="absolute top-4 right-4">
                    <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                            class="p-2.5 rounded-xl text-surface-500 hover:text-surface-700 dark:text-surface-400 dark:hover:text-surface-200 hover:bg-surface-100 dark:hover:bg-surface-800 transition"
                            title="Alternar tema">
                        <i x-show="!darkMode" class="fa-solid fa-moon"></i>
                        <i x-show="darkMode" class="fa-solid fa-sun text-yellow-400"></i>
                    </button>
                </div>

                <!-- Form Card -->
                <div class="w-full max-w-md">
                    <div class="bg-white dark:bg-surface-900 rounded-2xl shadow-xl shadow-surface-200/50 dark:shadow-none border border-surface-200 dark:border-surface-800 p-8">
                        {{ $slot }}
                    </div>

                    <p class="mt-8 text-center text-xs text-surface-400 dark:text-surface-500">
                        &copy; {{ date('Y') }} Clientify360 &middot; Desenvolvido por <strong>HOTWYL | WILLFROMBRASIL</strong>
                    </p>
                </div>
            </div>
        </div>

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
    </body>
</html>
