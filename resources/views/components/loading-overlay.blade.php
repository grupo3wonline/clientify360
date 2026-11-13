@props(['context' => 'blade'])
{{--
    Overlay de carregamento de tela inteira.
    Controlado por resources/js/loading-overlay.js (window.loading.show/hide).
    Oculto sem JS (atributo hidden). Nunca fica preso: há um failsafe de 15 s.
--}}
<div id="loading-overlay" hidden role="status" aria-live="polite" aria-label="Processando, aguarde..."
     class="c360-overlay {{ $context === 'panel' ? 'c360-panel-ui' : '' }} fixed inset-0 z-[9999] flex items-center justify-center bg-surface-900/70 backdrop-blur-sm">
    <div class="c360-overlay__box flex flex-col items-center gap-4 rounded-2xl bg-white/10 dark:bg-white/5 border border-white/20 px-8 py-7 shadow-2xl">
        {{-- Spinner com dois anéis (um estático, um girando) --}}
        <span class="c360-overlay__spinner relative block w-14 h-14" aria-hidden="true">
            {{-- Anel externo estático --}}
            <span class="absolute inset-0 rounded-full border-4 border-white/20"></span>
            {{-- Anel girante --}}
            <span class="absolute inset-0 rounded-full border-4 border-transparent border-t-white"
                  style="animation: c360-spin 0.8s linear infinite;"></span>
            {{-- Ponto central --}}
            <span class="absolute inset-0 flex items-center justify-center">
                <span class="w-2.5 h-2.5 rounded-full bg-white/60 animate-pulse"></span>
            </span>
        </span>
        <p data-loading-message class="c360-overlay__message text-sm font-medium text-white/90 tracking-wide">Processando...</p>
    </div>
</div>
