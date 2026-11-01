@props(['context' => 'blade'])
{{--
    Cookie consent banner (LGPD, GDPR, CCPA/CPRA, POPIA). Hidden by default and
    controlled by resources/js/consent.js: it is shown when there is no valid
    c360_consent cookie and reopened by the "Preferências de cookies" links.
    Granular categories: necessários (always on), análise and marketing.
    It never blocks the page and Esc does not dismiss it without a choice.
--}}
<div id="c360-consent" data-consent-banner hidden
     role="dialog" aria-modal="false" aria-labelledby="c360-consent-title" aria-describedby="c360-consent-text"
     class="c360-consent {{ $context === 'panel' ? 'c360-panel-ui' : '' }} fixed inset-x-0 bottom-0 z-[9998] p-3 sm:p-6">
    <div class="c360-consent__box max-w-4xl mx-auto rounded-2xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 shadow-2xl p-5 sm:p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex items-start gap-4">
            <span class="c360-consent__icon hidden sm:flex w-11 h-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400" aria-hidden="true">
                <i class="fa-solid fa-cookie-bite"></i>
            </span>
            <div class="flex-1 min-w-0">
                <h2 id="c360-consent-title" tabindex="-1" class="c360-consent__title text-base font-semibold text-surface-900 dark:text-white focus:outline-none">Sua privacidade importa</h2>
                <p id="c360-consent-text" class="c360-consent__text mt-1 text-sm text-surface-600 dark:text-surface-300 leading-relaxed">
                    Usamos cookies necessários para o funcionamento do site. Com a sua permissão, também usamos cookies de análise e de marketing para melhorar sua experiência. Você pode mudar sua escolha a qualquer momento em “Preferências de cookies”.
                    <a href="{{ url('/politica-de-privacidade') }}#cookies" class="c360-consent__link font-medium text-brand-600 dark:text-brand-400 underline underline-offset-2 hover:no-underline">Saiba mais</a>
                </p>

                <fieldset data-consent-panel hidden class="c360-consent__panel mt-4 space-y-3">
                    <legend class="sr-only">Categorias de cookies</legend>
                    <label class="c360-consent__option flex items-start gap-3 rounded-xl border border-surface-200 dark:border-surface-700 p-3">
                        <input type="checkbox" checked disabled autocomplete="off" class="mt-1 rounded border-surface-300 text-brand-600">
                        <span class="text-sm">
                            <span class="font-semibold text-surface-900 dark:text-white">Necessários</span>
                            <span class="c360-consent__badge ml-1 text-xs text-accent-700 dark:text-accent-400">Sempre ativos</span>
                            <span class="block text-surface-500 dark:text-surface-400">Sessão, segurança (CSRF) e a sua escolha de cookies. Sem eles o site não funciona.</span>
                        </span>
                    </label>
                    <label class="c360-consent__option flex items-start gap-3 rounded-xl border border-surface-200 dark:border-surface-700 p-3 cursor-pointer">
                        <input type="checkbox" data-consent-category="analytics" autocomplete="off" class="mt-1 rounded border-surface-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm">
                            <span class="font-semibold text-surface-900 dark:text-white">Análise</span>
                            <span class="block text-surface-500 dark:text-surface-400">Google Analytics: estatísticas de uso anônimas para melhorar as páginas.</span>
                        </span>
                    </label>
                    <label class="c360-consent__option flex items-start gap-3 rounded-xl border border-surface-200 dark:border-surface-700 p-3 cursor-pointer">
                        <input type="checkbox" data-consent-category="marketing" autocomplete="off" class="mt-1 rounded border-surface-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm">
                            <span class="font-semibold text-surface-900 dark:text-white">Marketing</span>
                            <span class="block text-surface-500 dark:text-surface-400">Google Tag Manager e anúncios: medir campanhas e mostrar ofertas relevantes.</span>
                        </span>
                    </label>
                </fieldset>

                <div class="c360-consent__actions mt-4 flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2.5">
                    <button type="button" data-consent-action="accept" class="c360-consent__btn c360-consent__btn--primary inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition">
                        Aceitar todos
                    </button>
                    <button type="button" data-consent-action="reject" class="c360-consent__btn inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-surface-300 dark:border-surface-600 text-surface-700 dark:text-surface-200 text-sm font-medium hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                        Rejeitar opcionais
                    </button>
                    <button type="button" data-consent-action="customize" aria-expanded="false" class="c360-consent__btn inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-surface-600 dark:text-surface-300 text-sm font-medium hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                        Personalizar
                    </button>
                    <button type="button" data-consent-action="save" hidden class="c360-consent__btn c360-consent__btn--primary inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition">
                        Salvar preferências
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
