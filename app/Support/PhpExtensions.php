<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Runtime checks of the PHP installation, injectable so security:check can be
 * tested without changing the real extensions. Not final: tests replace it.
 */
class PhpExtensions
{
    /** Extensions the production server must load (media library, Cashier, dompdf, Excel). */
    public const REQUIRED = ['exif', 'fileinfo', 'intl', 'gd', 'zip'];

    /**
     * @param  list<string>  $names
     * @return list<string>
     */
    public function missing(array $names): array
    {
        return array_values(array_filter($names, fn (string $name): bool => !extension_loaded($name)));
    }

    /** The scheduler runs every command through Symfony Process, which needs proc_open. */
    public function procOpenDisabled(): bool
    {
        if (!function_exists('proc_open')) {
            return true;
        }

        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

        return in_array('proc_open', $disabled, true);
    }
}
