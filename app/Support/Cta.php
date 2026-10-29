<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Builds the Grupo 3W Online CTA link and texts from config/cta.php.
 */
final class Cta
{
    /**
     * CTA URL with the UTM parameters and utm_content set to the placement
     * (e.g. "landing-hero", "app-card", "error-404").
     */
    public static function url(string $placement): string
    {
        $query = http_build_query([
            ...(array) config('cta.utm', []),
            'utm_content' => $placement,
        ]);

        return rtrim((string) config('cta.url'), '/').'/?'.$query;
    }

    /**
     * Urgency line with the current month name in pt-BR (e.g. "Garanta seu lugar na agenda de maio.").
     */
    public static function urgency(): string
    {
        $text = (string) config('cta.urgency', '');

        return str_replace(':month', now()->locale('pt_BR')->translatedFormat('F'), $text);
    }
}
