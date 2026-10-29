{{--
    Shared footer (public, auth, app, error pages and the Filament panel).
    $minimal (bool): compact version without route() lookups that need the session (error pages).
    $context (string): "blade" (default) or "panel" (Filament, styled by panel.css).
--}}
@php
    $minimal = $minimal ?? false;
    $panel = ($context ?? 'blade') === 'panel';
    $privacyUrl = url('/politica-de-privacidade');
@endphp
<footer class="c360-footer {{ $panel ? 'c360-panel-ui' : '' }} border-t border-surface-200 dark:border-surface-800 bg-white dark:bg-surface-950 text-sm" role="contentinfo">
    @unless($minimal || $panel)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-6 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <a href="{{ url('/') }}" class="inline-flex items-center" aria-label="Clientify360, página inicial">
                    <img src="{{ asset('images/logo.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto dark:hidden" loading="lazy" decoding="async">
                    <img src="{{ asset('images/logo-dark.svg') }}" alt="Clientify360" width="160" height="32" class="h-8 w-auto hidden dark:block" loading="lazy" decoding="async">
                </a>
                <p class="mt-3 max-w-md text-surface-500 dark:text-surface-400 leading-relaxed">
                    CRM para pequenos negócios: clientes, funil de vendas, orçamentos, agenda e financeiro em um só lugar, com dados isolados por conta.
                </p>
            </div>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wider text-surface-900 dark:text-white">Navegação</h2>
                <ul class="mt-3 space-y-2">
                    <li><a href="{{ url('/') }}" class="text-surface-500 hover:text-brand-600 dark:text-surface-400 dark:hover:text-brand-400">Início</a></li>
                    <li><a href="{{ url('/#planos') }}" class="text-surface-500 hover:text-brand-600 dark:text-surface-400 dark:hover:text-brand-400">Planos</a></li>
                    <li><a href="{{ url('/#faq') }}" class="text-surface-500 hover:text-brand-600 dark:text-surface-400 dark:hover:text-brand-400">Perguntas frequentes</a></li>
                    <li><a href="{{ url('/#contato') }}" class="text-surface-500 hover:text-brand-600 dark:text-surface-400 dark:hover:text-brand-400">Contato</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-xs font-semibold uppercase tracking-wider text-surface-900 dark:text-white">Fale com a gente</h2>
                <ul class="mt-3 space-y-2">
                    <li><a href="https://wa.me/5541996924829" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-surface-500 hover:text-accent-600 dark:text-surface-400 dark:hover:text-accent-400"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> (41) 99692-4829</a></li>
                    <li><a href="mailto:contato@3wonline.com.br" class="inline-flex items-center gap-2 text-surface-500 hover:text-brand-600 dark:text-surface-400 dark:hover:text-brand-400"><i class="fa-solid fa-envelope" aria-hidden="true"></i> contato@3wonline.com.br</a></li>
                </ul>
            </div>
        </div>
    @endunless
    <div class="c360-footer__bar max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 {{ $minimal || $panel ? '' : 'border-t border-surface-200 dark:border-surface-800' }} flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between text-xs text-surface-500 dark:text-surface-400">
        <nav aria-label="Links legais" class="c360-footer__links flex flex-wrap gap-x-4 gap-y-2">
            <a href="{{ url('/') }}" class="hover:text-brand-600 dark:hover:text-brand-400">Início</a>
            <a href="{{ $privacyUrl }}" class="hover:text-brand-600 dark:hover:text-brand-400">Política de Privacidade</a>
            <a href="{{ url('/termos-de-uso') }}" class="hover:text-brand-600 dark:hover:text-brand-400">Termos de Uso</a>
            <a href="{{ $privacyUrl }}#cookies" data-cookie-preferences data-no-loading class="hover:text-brand-600 dark:hover:text-brand-400">Preferências de cookies</a>
            <a href="{{ $privacyUrl }}#nao-vender" data-do-not-sell data-no-loading class="hover:text-brand-600 dark:hover:text-brand-400">Não vender ou compartilhar minhas informações pessoais</a>
        </nav>
        <p class="c360-footer__credits flex flex-col sm:flex-row gap-1 sm:gap-3">
            <span>&copy; {{ date('Y') }} Clientify360</span>
            <span class="font-medium text-surface-600 dark:text-surface-300">Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL</span>
        </p>
    </div>
</footer>
