@props(['context' => 'blade'])
{{-- Controlled by resources/js/loading-overlay.js (window.loading.show/hide). Hidden without JS. --}}
<div id="loading-overlay" hidden role="status" aria-live="polite"
     class="c360-overlay {{ $context === 'panel' ? 'c360-panel-ui' : '' }} fixed inset-0 z-[9999] flex items-center justify-center bg-surface-900/60 backdrop-blur-sm">
    <div class="c360-overlay__box flex flex-col items-center gap-4 rounded-card bg-white/10 px-8 py-6">
        <span class="c360-overlay__spinner relative block w-12 h-12" aria-hidden="true">
            <span class="absolute inset-0 rounded-full border-4 border-white/25"></span>
            <span class="absolute inset-0 rounded-full border-4 border-transparent border-t-white animate-spin"></span>
        </span>
        <p data-loading-message class="c360-overlay__message text-sm font-medium text-white">Processando...</p>
    </div>
</div>
