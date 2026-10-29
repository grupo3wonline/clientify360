import fs from 'node:fs';
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Design tokens — single source of truth: resources/brand/tokens.json
 * (also read by config/brand.php for Filament, PDFs and inline styles).
 * Legacy aliases (primary/secondary) and `success` reference the same objects.
 */
const tokens = JSON.parse(fs.readFileSync(new URL('./resources/brand/tokens.json', import.meta.url), 'utf8'));
const { brand, accent, surface, danger, warning, info } = tokens.colors;

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './app/Support/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: [`"${tokens.font.sans}"`, ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand,
                accent,
                surface,
                success: accent,
                danger,
                warning,
                info,
                primary: brand,
                secondary: accent,
            },
            borderRadius: {
                card: tokens.radius.card,
                button: tokens.radius.button,
            },
        },
    },
    plugins: [forms],
};
