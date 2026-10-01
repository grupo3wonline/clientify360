@php
    $gtmId = config('analytics.gtm_id');
    $enabled = ! app()->environment('testing', 'local');
@endphp

@if($enabled && $gtmId)
    {{-- Google Tag Manager (noscript) --}}
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
                height="0" width="0" style="display:none;visibility:hidden"
                title="gtm"></iframe>
    </noscript>
@endif
