<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        @include('layouts.partials.head', [
            'noindex' => true,
            'title' => trim($__env->yieldContent('title')) ?: 'Painel',
            'description' => 'Painel do Clientify360: clientes, vendas, agenda e financeiro.',
        ])
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-surface-50 dark:bg-surface-950 text-surface-800 dark:text-surface-200"
          x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
        <a href="#main-content"
           class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[10000] focus:px-4 focus:py-2.5 focus:rounded-xl focus:bg-brand-600 focus:text-white focus:text-sm focus:font-medium focus:shadow-lg">
            Pular para o conteúdo principal
        </a>

        <div class="min-h-screen lg:flex">
            {{-- Mobile backdrop --}}
            <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
                 class="fixed inset-0 z-40 bg-surface-900/60 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

            {{-- Sidebar: off-canvas below lg, static from lg --}}
            <aside id="sidebar" aria-label="Barra lateral de navegação"
                   :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                   class="-translate-x-full fixed inset-y-0 left-0 z-50 w-72 lg:w-64 bg-white dark:bg-surface-900 border-r border-surface-200 dark:border-surface-800 sidebar-transition lg:translate-x-0 lg:sticky lg:top-0 lg:h-screen flex flex-col shrink-0">
                <div class="flex items-center justify-between h-16 px-5 border-b border-surface-200 dark:border-surface-800">
                    <a href="{{ route('dashboard') }}" class="flex items-center" aria-label="Clientify360, ir para o painel">
                        <img src="{{ asset('images/logo.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto dark:hidden">
                        <img src="{{ asset('images/logo-dark.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto hidden dark:block">
                    </a>
                    <button type="button" @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg text-surface-500 hover:bg-surface-100 dark:hover:bg-surface-800" aria-label="Fechar menu">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                @include('layouts.partials.sidebar-nav')

                <div class="p-4 border-t border-surface-200 dark:border-surface-800">
                    <div class="flex items-center gap-3 px-2">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-sm font-bold shadow-sm" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-surface-800 dark:text-surface-200 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-surface-500 dark:text-surface-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex-1 flex flex-col min-w-0 min-h-screen">
                {{-- Top bar --}}
                <header class="sticky top-0 z-30 h-16 glass border-b border-surface-200/80 dark:border-surface-800/80">
                    <div class="flex items-center justify-between h-full gap-3 px-4 sm:px-6">
                        <div class="flex items-center gap-3 min-w-0">
                            <button @click="sidebarOpen = true" type="button" aria-label="Abrir menu de navegação" aria-controls="sidebar"
                                    :aria-expanded="sidebarOpen.toString()" aria-expanded="false"
                                    class="p-2 rounded-lg text-surface-500 hover:text-surface-700 dark:text-surface-400 dark:hover:text-surface-200 hover:bg-surface-100 dark:hover:bg-surface-800 transition lg:hidden">
                                <i class="fa-solid fa-bars text-lg" aria-hidden="true"></i>
                            </button>
                            <p class="text-sm font-semibold text-surface-800 dark:text-surface-100 truncate">@yield('title', 'Painel')</p>
                        </div>

                        <div class="flex items-center gap-1 sm:gap-2">
                            <a href="{{ route('notifications.index') }}" class="relative p-2.5 rounded-xl text-surface-500 hover:text-surface-800 dark:text-surface-400 dark:hover:text-surface-100 hover:bg-surface-100 dark:hover:bg-surface-800 transition" title="Notificações" aria-label="Notificações">
                                <i class="fa-solid fa-bell text-sm" aria-hidden="true"></i>
                            </a>
                            <x-theme-toggle />

                            <div class="relative" x-data="{ userMenu: false }" @keydown.escape.window="userMenu = false">
                                <button @click="userMenu = !userMenu" type="button" :aria-expanded="userMenu.toString()" aria-expanded="false" aria-haspopup="true" aria-label="Abrir menu do usuário"
                                        class="flex items-center gap-2 p-1.5 sm:pr-3 rounded-xl hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white text-xs font-bold" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <span class="hidden sm:block text-sm font-medium text-surface-700 dark:text-surface-300 max-w-[10rem] truncate">{{ Auth::user()->name }}</span>
                                    <i class="fa-solid fa-chevron-down text-xs text-surface-400" aria-hidden="true"></i>
                                </button>

                                <div x-show="userMenu" @click.outside="userMenu = false" x-transition.origin.top.right x-cloak
                                     class="absolute right-0 mt-2 w-60 bg-white dark:bg-surface-800 rounded-xl shadow-xl border border-surface-200 dark:border-surface-700 py-2 z-50"
                                     role="menu" aria-label="Opções da conta">
                                    <a href="{{ route('dashboard') }}" role="menuitem" class="flex items-center px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700 transition">
                                        <i class="fa-solid fa-gauge-high w-5 mr-3 text-surface-400" aria-hidden="true"></i> Dashboard
                                    </a>
                                    <a href="{{ route('profile.edit') }}" role="menuitem" title="Atualize seus dados, senha e preferências da conta" class="flex items-center px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700 transition">
                                        <i class="fa-solid fa-user-gear w-5 mr-3 text-surface-400" aria-hidden="true"></i> Meu perfil
                                    </a>
                                    @can('account.billing')
                                        <a href="{{ route('billing.index') }}" role="menuitem" title="Veja seu plano, a vigência e gerencie a assinatura" class="flex items-center px-4 py-2.5 text-sm text-surface-700 dark:text-surface-300 hover:bg-surface-50 dark:hover:bg-surface-700 transition">
                                            <i class="fa-solid fa-credit-card w-5 mr-3 text-surface-400" aria-hidden="true"></i> Minha assinatura
                                        </a>
                                    @endcan
                                    <hr class="my-2 border-surface-200 dark:border-surface-700">
                                    <form method="POST" action="{{ route('logout') }}" autocomplete="off" data-loading-text="Saindo...">
                                        @csrf
                                        <button type="submit" role="menuitem" title="Encerre sua sessão com segurança neste dispositivo" class="flex items-center w-full px-4 py-2.5 text-sm text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-950/30 transition">
                                            <i class="fa-solid fa-right-from-bracket w-5 mr-3" aria-hidden="true"></i> Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main id="main-content" tabindex="-1" class="flex-1 w-full max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 focus:outline-none">
                    @isset($header)
                        {{ $header }}
                    @endisset

                    {{ $slot }}

                    <x-cta-3w variant="card" placement="app-card" :dismissible="true" class="mt-10" />
                </main>

                @include('layouts.partials.footer', ['minimal' => true])
            </div>
        </div>

        <x-loading-overlay />
        <x-flash />
        <x-cookie-consent />

        @stack('scripts')
    </body>
</html>
