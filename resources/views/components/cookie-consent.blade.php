{{--
    Banner de consentimento de cookies.
    Conformidade: LGPD (Brasil), GDPR (UE), CCPA (Califórnia), POPIA (África do Sul).
    - Cookies essenciais são sempre carregados (base legal: legítimo interesse / necessidade).
    - Preferências de consentimento persistidas em localStorage por 6 meses.
--}}
<div x-data="cookieConsent()"
     x-show="!decided"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-8"
     x-transition:enter-end="opacity-100 translate-y-0"
     class="fixed inset-x-0 bottom-0 z-[9998] p-4 sm:p-6"
     role="dialog"
     aria-live="polite"
     aria-label="Aviso de cookies">
    <div class="max-w-4xl mx-auto rounded-2xl border border-surface-200 dark:border-surface-700 bg-white dark:bg-surface-900 shadow-2xl overflow-hidden">
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                <div class="hidden sm:flex w-11 h-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 dark:bg-brand-950/50">
                    <i class="fa-solid fa-cookie-bite text-brand-500"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-base font-semibold text-surface-900 dark:text-surface-100">
                        Nós valorizamos a sua privacidade
                    </h2>
                    <p class="mt-1 text-sm text-surface-600 dark:text-surface-400">
                        Usamos cookies essenciais para o funcionamento do sistema e, com o seu consentimento,
                        cookies de preferência e análise para melhorar a sua experiência. Você pode aceitar,
                        recusar os opcionais ou saber mais na nossa
                        <a href="{{ route('privacy') }}" class="text-brand-600 dark:text-brand-400 font-medium hover:underline">Política de Privacidade</a>.
                    </p>

                    <div class="mt-4 flex flex-col sm:flex-row sm:items-center gap-2.5">
                        <button type="button"
                                @click="acceptAll()"
                                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-brand-600 text-white text-sm font-medium hover:bg-brand-700 transition shadow-sm shadow-brand-600/25">
                            <i class="fa-solid fa-check mr-2 text-xs"></i> Aceitar todos
                        </button>
                        <button type="button"
                                @click="acceptEssential()"
                                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-surface-300 dark:border-surface-600 text-surface-700 dark:text-surface-300 text-sm font-medium hover:bg-surface-100 dark:hover:bg-surface-800 transition">
                            Apenas essenciais
                        </button>
                        <a href="{{ route('privacy') }}"
                           class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-medium text-surface-500 dark:text-surface-400 hover:text-surface-800 dark:hover:text-surface-200 transition">
                            Gerenciar preferências
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function cookieConsent() {
        return {
            decided: true,
            init() {
                try {
                    const stored = JSON.parse(localStorage.getItem('cookie_consent') || 'null');
                    // Reexibe se não houver decisão ou se passaram mais de 180 dias.
                    if (!stored || !stored.date || (Date.now() - stored.date) > 1000 * 60 * 60 * 24 * 180) {
                        this.decided = false;
                    }
                } catch (e) {
                    this.decided = false;
                }
            },
            save(preferences) {
                localStorage.setItem('cookie_consent', JSON.stringify({
                    ...preferences,
                    essential: true,
                    date: Date.now(),
                }));
                this.decided = true;
                window.dispatchEvent(new CustomEvent('cookie-consent-updated', { detail: preferences }));
            },
            acceptAll() {
                this.save({ analytics: true, preferences: true });
            },
            acceptEssential() {
                this.save({ analytics: false, preferences: false });
            },
        }
    }
</script>
@endpush
