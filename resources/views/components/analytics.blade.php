@php
    $gtmId = config('analytics.gtm_id');
    $ga4Id = config('analytics.ga4_id');
    $siteVerification = config('analytics.google_site_verification');
    $enabled = ! app()->environment('testing', 'local');
@endphp

@if($siteVerification)
    <meta name="google-site-verification" content="{{ $siteVerification }}">
@endif

@if($enabled && $gtmId)
    {{-- Google Tag Manager --}}
    <script>
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '{{ $gtmId }}');
    </script>
@endif

@if($enabled && $ga4Id)
    {{-- Google Analytics 4 --}}
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', '{{ $ga4Id }}');
    </script>
@endif
