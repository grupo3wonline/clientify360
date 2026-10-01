<?php

declare(strict_types=1);

namespace App\Support;

/** Formatação pt-BR usada pelas diretivas @money e @qty. */
final class Format
{
    public static function money(float|int|string|null $value): string
    {
        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }

    /** 1.500 / 2,5 / 0,125 — sem zeros sobrando. */
    public static function qty(float|int|string|null $value): string
    {
        $formatted = number_format((float) $value, 3, ',', '.');

        return rtrim(rtrim($formatted, '0'), ',');
    }
}
