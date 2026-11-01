@php
    $navLinks = [
        'beneficios' => 'Benefícios',
        'funcionalidades' => 'Funcionalidades',
        'como-funciona' => 'Como funciona',
        'planos' => 'Planos',
        'faq' => 'Dúvidas',
        'sobre' => 'Sobre',
        'contato' => 'Contato',
    ];
    // On the home page anchors scroll in place; elsewhere they point to the home page.
    $onHome = request()->routeIs('home');
    $anchorHref = fn (string $anchor): string => $onHome ? '#'.$anchor : url('/').'#'.$anchor;
@endphp
<header class="glass-nav sticky top-0 z-50 border-b border-surface-200/70 dark:border-surface-800/70" x-data="{ mobileMenu: false, active: '' }"
        x-on:section-change.window="active = $event.detail" @keydown.escape.window="mobileMenu = false">
    <nav class="c-container" aria-label="Menu do site">
        <div class="flex items-center justify-between h-16 gap-4">
            <a href="{{ url('/') }}" class="flex items-center shrink-0" aria-label="Clientify360, página inicial">
                <img src="{{ asset('images/logo.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto dark:hidden">
                <img src="{{ asset('images/logo-dark.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto hidden dark:block">
            </a>

            <div class="hidden lg:flex items-center gap-1">
                @foreach($navLinks as $anchor => $label)
                    <a href="{{ $anchorHref($anchor) }}"
                       :class="active === '{{ $anchor }}' ? 'text-brand-700 dark:text-brand-300 bg-brand-50 dark:bg-brand-950/50' : ''"
                       class="px-3 py-2 rounded-lg text-sm font-medium text-surface-600 dark:text-surface-400 hover:text-surface-900 dark:hover:text-surface-100 hover:bg-surface-100 dark:hover:bg-surface-800 transition">{{ $label }}</a>
                @endforeach
            </div>

            <div class="flex items-center gap-2">
                <x-theme-toggle />
                <div class="hidden sm:flex items-center gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="c-btn-primary"><i class="fa-solid fa-gauge-high text-xs" aria-hidden="true"></i> Painel</a>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-semibold text-surface-700 dark:text-surface-300 hover:text-brand-600 dark:hover:text-brand-400 transition">Entrar</a>
                        <a href="{{ route('register') }}" class="c-btn-primary"><i class="fa-solid fa-rocket text-xs" aria-hidden="true"></i> Começar grátis</a>
                    @endauth
                </div>
                <button type="button" @click="mobileMenu = !mobileMenu" class="lg:hidden p-2.5 rounded-xl text-surface-600 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition"
                        :aria-expanded="mobileMenu.toString()" aria-expanded="false" aria-controls="mobile-menu" aria-label="Abrir menu">
                    <i class="fa-solid fa-bars" x-show="!mobileMenu" aria-hidden="true"></i>
                    <i class="fa-solid fa-xmark" x-show="mobileMenu" x-cloak aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div id="mobile-menu" x-show="mobileMenu" x-cloak x-transition.origin.top class="lg:hidden pb-4 border-t border-surface-200 dark:border-surface-800">
            <div class="flex flex-col gap-1 pt-3">
                @foreach($navLinks as $anchor => $label)
                    <a href="{{ $anchorHref($anchor) }}" @click="mobileMenu = false" class="px-4 py-2.5 rounded-xl text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 transition">{{ $label }}</a>
                @endforeach
                <div class="grid grid-cols-2 gap-2 pt-3 sm:hidden">
                    @auth
                        <a href="{{ route('dashboard') }}" class="c-btn-primary col-span-2">Painel</a>
                    @else
                        <a href="{{ route('login') }}" class="c-btn-ghost">Entrar</a>
                        <a href="{{ route('register') }}" class="c-btn-primary">Começar grátis</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>
