<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Legal document versions
    |--------------------------------------------------------------------------
    |
    | terms_version: users must (re)accept the Terms of Use when it changes.
    | privacy_version: the cookie banner is shown again when it changes.
    |
    */

    'terms_version' => '2026-10',
    'privacy_version' => '2026-10',

    // Human-readable date of the last update of both documents.
    'updated_at' => '28/10/2026',

    /*
    |--------------------------------------------------------------------------
    | Data controller (LGPD "controlador", GDPR "controller", POPIA "responsible party")
    |--------------------------------------------------------------------------
    */

    'controller' => [
        'name' => 'Grupo 3W Online',
        'website' => 'https://www.3wonline.com.br',
        'email' => 'contato@3wonline.com.br',
        'whatsapp' => '(41) 99692-4829',
        'whatsapp_url' => 'https://wa.me/5541996924829',
        'city' => 'Curitiba/PR, Brasil',
        'developer' => 'HOTWYL | WILLFROMBRASIL',
        // Encarregado (LGPD) / DPO (GDPR) / Information Officer (POPIA).
        'dpo_email' => 'contato@3wonline.com.br',
    ],

];
