@props(['href'])

{{-- "Baixar PDF" (Profissional). Downloads do not unload the page, so no loading overlay. --}}
@php($canPdf = auth()->check() && app(\App\Services\SubscriptionService::class)->hasFeature(auth()->user(), 'pdf'))

@if($canPdf)
    <a href="{{ $href }}" data-no-loading {{ $attributes->merge(['class' => 'c-btn-ghost']) }} title="Baixar o documento em PDF">
        <i class="fa-solid fa-file-pdf mr-2 text-xs" aria-hidden="true"></i> Baixar PDF
    </a>
@else
    <span class="inline-flex items-center gap-2">
        <button type="button" disabled aria-disabled="true" {{ $attributes->merge(['class' => 'c-btn-ghost opacity-50 cursor-not-allowed']) }}
                title="Disponível no plano Profissional">
            <i class="fa-solid fa-file-pdf mr-2 text-xs" aria-hidden="true"></i> Baixar PDF
        </button>
        @can('account.billing')
            <a href="{{ route('billing.index') }}" class="text-xs font-medium text-brand-600 dark:text-brand-400 underline">Disponível no plano Profissional</a>
        @else
            <span class="text-xs text-surface-500 dark:text-surface-400">Fale com o dono da conta</span>
        @endcan
    </span>
@endif
