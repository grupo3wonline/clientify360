{{--
    Consent configuration only. It never loads Google Tag Manager/Analytics:
    resources/js/consent.js injects them after the visitor's consent.
    The Google Consent Mode v2 default (everything denied) runs first.
--}}
@php
    $enabled = ! app()->environment('testing', 'local');
    $siteVerification = config('analytics.google_site_verification');
    $consentConfig = [
        'version' => (string) config('legal.privacy_version'),
        'endpoint' => route('consent.store'),
        'privacyUrl' => route('privacy'),
        'gtmId' => $enabled && filled(config('analytics.gtm_id')) ? (string) config('analytics.gtm_id') : null,
        'ga4Id' => $enabled && filled(config('analytics.ga4_id')) ? (string) config('analytics.ga4_id') : null,
    ];
@endphp

@if($siteVerification)
    <meta name="google-site-verification" content="{{ $siteVerification }}">
@endif

<script>
    window.__consentConfig = {!! json_encode($consentConfig, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!};
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
    window.gtag('consent', 'default', { ad_storage: 'denied', analytics_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied', wait_for_update: 500 });
</script>
