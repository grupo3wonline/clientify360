@props([
    'variant' => 'section', // section | card | inline
    'placement' => 'site',
    'context' => 'blade', // blade | panel (Filament: styled by resources/css/panel.css)
    'dismissible' => false,
])
@php
    $href = \App\Support\Cta::url($placement);
    $panel = $context === 'panel';
    $root = 'c360-cta c360-cta--'.$variant.($panel ? ' c360-panel-ui' : '');
@endphp

@if($variant === 'section')
    <section {{ $attributes->merge(['class' => $root.' relative overflow-hidden bg-surface-950 text-white']) }} aria-labelledby="cta-3w-{{ $placement }}" data-cta="{{ $placement }}">
        <div class="absolute inset-0 opacity-40 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 -left-16 w-96 h-96 rounded-full bg-brand-600 blur-3xl opacity-40"></div>
            <div class="absolute -bottom-24 right-0 w-96 h-96 rounded-full bg-accent-500 blur-3xl opacity-25"></div>
        </div>
        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20 grid gap-10 lg:grid-cols-[1.4fr_1fr] items-center">
            <div>
                <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-accent-300">
                    <i class="fa-solid fa-code" aria-hidden="true"></i> {{ config('cta.company') }}
                </p>
                <h2 id="cta-3w-{{ $placement }}" class="mt-3 text-2xl md:text-4xl font-bold tracking-tight">{{ config('cta.headline') }}</h2>
                <p class="mt-4 text-surface-300 text-base md:text-lg leading-relaxed">{{ config('cta.pitch') }}</p>
                <ul class="mt-6 grid gap-3 sm:grid-cols-2 text-sm text-surface-200">
                    <li class="flex gap-2"><i class="fa-solid fa-check text-accent-400 mt-1" aria-hidden="true"></i> Sites institucionais, lojas e landing pages</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check text-accent-400 mt-1" aria-hidden="true"></i> Sistemas web e integrações sob medida</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check text-accent-400 mt-1" aria-hidden="true"></i> Projeto pensado para o seu processo</li>
                    <li class="flex gap-2"><i class="fa-solid fa-check text-accent-400 mt-1" aria-hidden="true"></i> Suporte de quem desenvolveu o Clientify360</li>
                </ul>
            </div>
            <div class="rounded-card border border-white/10 bg-white/5 p-6 md:p-8 backdrop-blur">
                <p class="flex gap-3 text-sm text-surface-200"><i class="fa-solid fa-hourglass-half text-warning-300 mt-0.5" aria-hidden="true"></i> <span>{{ config('cta.scarcity') }}</span></p>
                <p class="mt-3 flex gap-3 text-sm font-semibold text-white"><i class="fa-solid fa-calendar-days text-accent-300 mt-0.5" aria-hidden="true"></i> <span>{{ \App\Support\Cta::urgency() }}</span></p>
                <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" data-cta-link
                   class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-button bg-white px-6 py-3.5 font-bold text-brand-700 shadow-xl shadow-black/20 transition hover:bg-brand-50">
                    {{ config('cta.button') }} <i class="fa-solid fa-arrow-up-right-from-square text-xs" aria-hidden="true"></i>
                </a>
                <p class="mt-3 text-center text-xs text-surface-400">Abre o site www.3wonline.com.br em uma nova aba.</p>
            </div>
        </div>
    </section>
@elseif($variant === 'card')
    <aside {{ $attributes->merge(['class' => $root.' c-card relative overflow-hidden p-5 sm:p-6']) }} data-cta="{{ $placement }}"
           @if($dismissible) x-data="{ hidden: false, key: 'c360:cta:dismissed', init() { try { const t = Number(localStorage.getItem(this.key)); this.hidden = t > 0 && (Date.now() - t) < 7 * 864e5; } catch (e) {} }, dismiss() { this.hidden = true; try { localStorage.setItem(this.key, String(Date.now())); } catch (e) {} } }" x-show="!hidden" x-cloak @endif
           aria-label="Oferta do Grupo 3W Online">
        <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-brand-500/10 blur-2xl pointer-events-none" aria-hidden="true"></div>
        <div class="relative flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
            <div class="c-icon-tile bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-lg shadow-brand-600/25">
                <i class="fa-solid fa-laptop-code" aria-hidden="true"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-surface-900 dark:text-white">{{ config('cta.company') }}: {{ config('cta.pitch') }}</p>
                <p class="mt-1 text-xs text-surface-500 dark:text-surface-400">{{ config('cta.scarcity') }} {{ \App\Support\Cta::urgency() }}</p>
            </div>
            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" data-cta-link class="c-btn-primary whitespace-nowrap">
                {{ config('cta.button') }} <i class="fa-solid fa-arrow-up-right-from-square text-[10px]" aria-hidden="true"></i>
            </a>
        </div>
        @if($dismissible)
            <button type="button" @click="dismiss()" class="absolute top-2 right-2 p-1.5 rounded-lg text-surface-400 hover:text-surface-700 dark:hover:text-surface-200" aria-label="Ocultar esta oferta por 7 dias" title="Ocultar por 7 dias">
                <i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i>
            </button>
        @endif
    </aside>
@else
    <div {{ $attributes->merge(['class' => $root.' rounded-card border border-brand-200/70 dark:border-brand-900 bg-brand-50/70 dark:bg-brand-950/30 px-4 py-3 text-sm']) }} data-cta="{{ $placement }}">
        <p class="c360-cta__text text-surface-700 dark:text-surface-300">
            <i class="fa-solid fa-laptop-code text-brand-600 dark:text-brand-400 mr-1" aria-hidden="true"></i>
            <strong class="text-surface-900 dark:text-white">{{ config('cta.company') }}</strong>: {{ config('cta.pitch') }}
            <span class="c360-cta__urgency text-surface-500 dark:text-surface-400">{{ \App\Support\Cta::urgency() }}</span>
            <a href="{{ $href }}" target="_blank" rel="noopener noreferrer" data-cta-link class="c360-cta__link font-semibold text-brand-700 dark:text-brand-300 underline underline-offset-2 hover:no-underline">{{ config('cta.button') }}</a>
        </p>
    </div>
@endif
