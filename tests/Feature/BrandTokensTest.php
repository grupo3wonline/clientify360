<?php

declare(strict_types=1);

/* AC-44, AC-45: one token source, no external font requests. */

it('loads the brand tokens from resources/brand/tokens.json', function (): void {
    $tokens = json_decode((string) file_get_contents(resource_path('brand/tokens.json')), true, 512, JSON_THROW_ON_ERROR);

    expect(config('brand.colors.brand.600'))->toBe($tokens['colors']['brand']['600'])
        ->and(config('brand.colors.accent.600'))->toBe($tokens['colors']['accent']['600'])
        ->and(config('brand.font.sans'))->toBe('Inter Variable');
});

it('reads the tokens in tailwind.config.js instead of hard-coding the palette', function (): void {
    $config = (string) file_get_contents(base_path('tailwind.config.js'));

    expect($config)->toContain('resources/brand/tokens.json')
        ->and($config)->not->toMatch('/#[0-9a-fA-F]{6}/');
});

it('references no Google or Bunny fonts and sets no Filament font', function (): void {
    $files = array_merge(
        glob(resource_path('views/layouts/*.blade.php')) ?: [],
        glob(resource_path('views/layouts/partials/*.blade.php')) ?: [],
        glob(resource_path('views/filament/hooks/*.blade.php')) ?: [],
        [app_path('Providers/Filament/AdminPanelProvider.php')],
    );

    foreach ($files as $file) {
        expect((string) file_get_contents($file))
            ->not->toContain('fonts.googleapis')
            ->not->toContain('fonts.gstatic')
            ->not->toContain('fonts.bunny');
    }

    expect((string) file_get_contents(app_path('Providers/Filament/AdminPanelProvider.php')))->not->toContain('->font(');
});

it('keeps hex colors out of panel.css', function (): void {
    expect((string) file_get_contents(resource_path('css/panel.css')))->not->toMatch('/#[0-9a-fA-F]{3,6}\b/');
});
