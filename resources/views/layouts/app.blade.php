<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', windowWidth: window.innerWidth, sidebarOpen: window.innerWidth >= 1024 }" :class="{ 'dark': darkMode }" @resize.window.debounce.100ms="windowWidth = window.innerWidth; if (windowWidth >= 1024) sidebarOpen = true">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- Área autenticada (privada): não deve ser indexada por buscadores. --}}
        <meta name="robots" content="noindex, nofollow">
        <meta name="description" content="Painel do Clientify360 - gerencie seus clientes, atendimentos, agenda e relatórios.">

        <title>{{ config('app.name', 'Clientify360') }} - @yield('title', 'Painel')</title>

        {{-- Medição (GTM/GA4) --}}
        <x-analytics />

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
                                50: '#eef2ff',
                                100: '#e0e7ff',
                                200: '#c7d2fe',
                                300: '#a5b4fc',
                                400: '#818cf8',
                                500: '#6366f1',
                                600: '#4f46e5',
                                700: '#4338ca',
                                800: '#3730a3',
                                900: '#312e81',
                                950: '#1e1b4b',
                            },
                            primary: {
                                50: '#eef2ff',
                                100: '#e0e7ff',
                                200: '#c7d2fe',
                                300: '#a5b4fc',
                                400: '#818cf8',
                                500: '#6366f1',
                                600: '#4f46e5',
                                700: '#4338ca',
                                800: '#3730a3',
                                900: '#312e81',
                                950: '#1e1b4b',
                            },
                            secondary: {
                                50: '#ecfdf5',
                                100: '#d1fae5',
                                200: '#a7f3d0',
                                300: '#6ee7b7',
                                400: '#34d399',
                                500: '#10b981',
                                600: '#059669',
                                700: '#047857',
                                800: '#065f46',
                                900: '#064e3b',
                            },
                            accent: {
                                50: '#ecfdf5',
                                100: '#d1fae5',
                                200: '#a7f3d0',
                                300: '#6ee7b7',
                                400: '#34d399',
                                500: '#10b981',
                                600: '#059669',
                                700: '#047857',
                                800: '#065f46',
                                900: '#064e3b',
                            },
                            danger: {
                                50: '#fef2f2',
                                100: '#fee2e2',
                                200: '#fecaca',
                                300: '#fca5a5',
                                400: '#f87171',
                                500: '#ef4444',
                                600: '#dc2626',
                                700: '#b91c1c',
                                800: '#991b1b',
                                900: '#7f1d1d',
                                950: '#450a0a',
                            },
                            surface: {
                                50: '#f8fafc',
                                100: '#f1f5f9',
                                200: '#e2e8f0',
                                300: '#cbd5e1',
                                400: '#94a3b8',
                                500: '#64748b',
                                600: '#475569',
                                700: '#334155',
                                800: '#1e293b',
                                900: '#0f172a',
                                950: '#020617',
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
            .sidebar-transition { transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out; }
            .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
            .dark .glass { background: rgba(30, 41, 59, 0.85); }
            .scrollbar-thin::-webkit-scrollbar { width: 4px; }
            .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
            .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(100, 116, 139, 0.3); border-radius: 2px; }
            .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(100, 116, 139, 0.5); }
        </style>

        {{-- Componentes reutilizáveis das telas de CRM/Vendas/Financeiro (Tailwind @apply via CDN) --}}
        <style type="text/tailwindcss">
            @layer components {
                .c-card { @apply bg-white dark:bg-surface-900 rounded-2xl border border-surface-200 dark:border-surface-800; }
                .c-label { @apply block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5; }
                .c-input { @apply w-full px-3.5 py-2.5 rounded-xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-800 text-surface-800 dark:text-surface-200 placeholder-surface-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-sm transition; }
                .c-hint { @apply mt-1 text-xs text-surface-500 dark:text-surface-400; }
                .c-error { @apply mt-1.5 text-xs text-danger-600 dark:text-danger-400; }
                .c-btn { @apply inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium rounded-xl transition disabled:opacity-50; }
                .c-btn-primary { @apply c-btn bg-brand-600 hover:bg-brand-700 text-white shadow-sm shadow-brand-600/25; }
                .c-btn-success { @apply c-btn bg-accent-600 hover:bg-accent-700 text-white shadow-sm shadow-accent-600/25; }
                .c-btn-danger { @apply c-btn bg-danger-600 hover:bg-danger-700 text-white; }
                .c-btn-ghost { @apply c-btn bg-white dark:bg-surface-800 border border-surface-200 dark:border-surface-700 hover:bg-surface-50 dark:hover:bg-surface-700 text-surface-700 dark:text-surface-300; }
                .c-badge { @apply inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold; }
                .c-th { @apply px-4 py-3 text-left text-xs font-semibold text-surface-500 dark:text-surface-400 uppercase tracking-wider; }
                .c-td { @apply px-4 py-3 text-sm text-surface-700 dark:text-surface-300; }
                .c-tab { @apply flex items-center px-3.5 py-2 rounded-lg text-sm font-medium transition text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-200 whitespace-nowrap; }
                .c-tab-active { @apply bg-white dark:bg-surface-700 text-brand-700 dark:text-brand-300 shadow-sm; }
            }
        </style>

        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-surface-50 dark:bg-surface-950 text-surface-800 dark:text-surface-200">
        <!-- Skip to content (acessibilidade: pular navegação) -->
        <a href="#main-content"
           class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[10000] focus:px-4 focus:py-2.5 focus:rounded-xl focus:bg-brand-600 focus:text-white focus:text-sm focus:font-medium focus:shadow-lg">
            Pular para o conteúdo principal
        </a>

        <div class="min-h-screen flex">

            <!-- Sidebar Overlay (mobile) -->
            <div x-show="sidebarOpen && windowWidth < 1024"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="sidebarOpen = false"
                 class="fixed inset-0 z-40 bg-surface-900/60 backdrop-blur-sm lg:hidden"
                 x-cloak></div>

            <!-- Sidebar -->
            <aside id="sidebar" aria-label="Barra lateral de navegação"
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                   class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-surface-900 border-r border-surface-200 dark:border-surface-800 sidebar-transition lg:translate-x-0 lg:static lg:inset-auto flex flex-col">

                <!-- Logo -->
                <div class="flex items-center h-16 px-6 border-b border-surface-200 dark:border-surface-800">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
                        <div class="w-9 h-9 bg-gradient-to-br from-brand-500 to-brand-700 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/25 group-hover:shadow-brand-500/40 transition-shadow">
                            <i class="fa-solid fa-chart-line text-white text-sm" aria-hidden="true"></i>
                        </div>
                        <span class="text-lg font-bold bg-gradient-to-r from-brand-600 to-brand-800 dark:from-brand-400 dark:to-brand-600 bg-clip-text text-transparent">Clientify360</span>
                    </a>
                </div>

                <!-- Navigation -->
                @include('layouts.partials.sidebar-nav')

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-surface-200 dark:border-surface-800">
                    <div class="flex items-center space-x-3 px-2">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-surface-800 dark:text-surface-200 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-surface-500 dark:text-surface-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">

                <!-- Top Bar -->
                <header class="sticky top-0 z-30 h-16 glass border-b border-surface-200/80 dark:border-surface-800/80">
                    <div class="flex items-center justify-between h-full px-4 sm:px-6">
                        <!-- Left: Hamburger + Breadcrumb -->
                        <div class="flex items-center space-x-4">
                            <button @click="sidebarOpen = !sidebarOpen"
                                    type="button"
                                    aria-label="Abrir menu de navegação"
                                    aria-controls="sidebar"
                                    :aria-expanded="sidebarOpen.toString()"
                                    class="p-2 rounded-lg text-surface-500 hover:text-surface-700 dark:text-surface-400 dark:hover:text-surface-200 hover:bg-surface-100 dark:hover:bg-surface-800 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-surface-900 lg:hidden">
                                <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
                            </button>

                            @isset($header)
                                <div class="hidden sm:block">
                                    {{ $header }}
                                </div>
                            @endisset
                        </div>

                        <!-- Right: Actions -->
                        <div class="flex items-center space-x-2">
                            <!-- Dark mode toggle -->
                            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                                    type="button"
                                    :aria-pressed="darkMode.toString()"
                                    class="p-2.5 rounded-xl text-surface-500 hover:text-surface-700 dark:text-surface-400 dark:hover:text-surface-200 hover:bg-surface-100 dark:hover:bg-surface-800 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-surface-900"
                                    :aria-label="darkMode ? 'Ativar tema claro' : 'Ativar tema escuro'"
                                    :title="darkMode ? 'Ativar tema claro' : 'Ativar tema escuro'">
                                <i x-show="!darkMode" class="fa-solid fa-moon text-sm" aria-hidden="true"></i>
                                <i x-show="darkMode" class="fa-solid fa-sun text-yellow-400 text-sm" aria-hidden="true"></i>
                            </button>

                            <!-- User Dropdown -->
                            <div class="relative" x-data="{ userMenu: false }" @keydown.escape.window="userMenu = false">
                                <button @click="userMenu = !userMenu"
                                        type="button"
                                        :aria-expanded="userMenu.toString()"
                                        aria-haspopup="true"
                                        aria-label="Abrir menu do usuário"
                                        class="flex items-center space-x-2 p-1.5 pr-3 rounded-xl hover:bg-surface-100 dark:hover:bg-surface-800 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-surface-900">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <span class="hidden sm:block text-sm font-medium text-surface-700 dark:text-surface-300">{{ Auth::user()->name }}</span>
                                    <i class="fa-solid fa-chevron-down text-xs text-surface-400" aria-hidden="true"></i>
                                </button>

                                <div x-show="userMenu" @click.outside="userMenu = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute right-0 mt-2 w-56 bg-white dark:bg-surface-800 rounded-xl shadow-xl border border-surface-200 dark:border-surface-700 py-2 z-50"
                                     role="menu"
                                     aria-label="Opções da conta"
                                     x-cloak>
                                    <a href="{{ route('profile.edit') }}" role="menuitem" title="Atualize seus dados, senha e preferências da conta" class="flex items-center px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700 transition focus:outline-none focus-visible:bg-surface-50 dark:focus-visible:bg-surface-700">
                                        <i class="fa-solid fa-user-gear w-5 mr-3 text-surface-400" aria-hidden="true"></i> Meu Perfil
                                    </a>
                                    <a href="{{ route('billing.index') }}" role="menuitem" title="Gerencie seu plano, pagamentos e faturas" class="flex items-center px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700 transition focus:outline-none focus-visible:bg-surface-50 dark:focus-visible:bg-surface-700">
                                        <i class="fa-solid fa-credit-card w-5 mr-3 text-surface-400" aria-hidden="true"></i> Assinatura
                                    </a>
                                    <hr class="my-2 border-surface-200 dark:border-surface-700">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" role="menuitem" title="Encerre sua sessão com segurança neste dispositivo" class="flex items-center w-full px-4 py-2.5 text-sm text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-950/30 transition focus:outline-none focus-visible:bg-danger-50 dark:focus-visible:bg-danger-950/30">
                                            <i class="fa-solid fa-right-from-bracket w-5 mr-3" aria-hidden="true"></i> Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main id="main-content" tabindex="-1" class="flex-1 p-4 sm:p-6 lg:p-8 focus:outline-none">
                    {{ $slot }}
                </main>

                <!-- Footer -->
                <footer class="border-t border-surface-200 dark:border-surface-800 py-4 px-4 sm:px-6">
                    <p class="text-center text-xs text-surface-500 dark:text-surface-400">
                        &copy; {{ date('Y') }} Clientify360 &middot; Sistema desenvolvido por <strong class="text-surface-600 dark:text-surface-300">HOTWYL | WILLFROMBRASIL</strong>
                    </p>
                </footer>
            </div>
        </div>

        <!-- SweetAlert2 for flash messages -->
        @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Tudo certo',
                    text: @js(session('success')),
                    icon: 'success',
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: '#4f46e5',
                    customClass: { popup: 'rounded-2xl' }
                });
            });
        </script>
        @endif

        @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Ops, algo deu errado',
                    text: @js(session('error')),
                    icon: 'error',
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: '#dc2626',
                    customClass: { popup: 'rounded-2xl' }
                });
            });
        </script>
        @endif

        <!-- Loading Overlay -->
        <div x-data="loadingOverlay()" x-show="loading" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[9999] flex items-center justify-center bg-surface-900/60 backdrop-blur-sm" x-cloak>
            <div class="flex flex-col items-center space-y-4">
                <div class="relative">
                    <div class="w-14 h-14 border-4 border-brand-200 dark:border-brand-900 rounded-full"></div>
                    <div class="absolute top-0 left-0 w-14 h-14 border-4 border-transparent border-t-brand-500 rounded-full animate-spin"></div>
                </div>
                <p class="text-sm font-medium text-white" x-text="message">Processando...</p>
            </div>
        </div>

        <script>
            function loadingOverlay() {
                return {
                    loading: false,
                    message: 'Processando...',
                    show(el) {
                        // Permite mensagem contextual via data-loading-text (ex.: "Salvando...", "Carregando...")
                        this.message = (el && el.getAttribute('data-loading-text')) || 'Processando...';
                        this.loading = true;
                    },
                    init() {
                        document.querySelectorAll('form').forEach(form => {
                            form.addEventListener('submit', (e) => {
                                if (form.classList.contains('hidden')) return;
                                if (form.hasAttribute('data-no-loading')) return;
                                this.show(form);
                            });
                        });

                        document.querySelectorAll('a[href]').forEach(link => {
                            link.addEventListener('click', (e) => {
                                const href = link.getAttribute('href');
                                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                                if (link.hasAttribute('target') && link.getAttribute('target') === '_blank') return;
                                if (link.hasAttribute('data-no-loading')) return;
                                if (e.ctrlKey || e.metaKey || e.shiftKey) return;
                                this.show(link);
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

        @stack('scripts')
    </body>
</html>
