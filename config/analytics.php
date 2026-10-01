<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Google Tag Manager / Google Analytics 4
    |--------------------------------------------------------------------------
    |
    | IDs usados para medição (SEO/SEM/geomarketing). Deixe em branco para
    | desativar. Os scripts só são injetados fora do ambiente de testes.
    |
    */

    'gtm_id' => env('GTM_ID', ''),
    'ga4_id' => env('GA4_ID', ''),

    // Verificação do Google Search Console (meta tag), opcional.
    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION', ''),
];
