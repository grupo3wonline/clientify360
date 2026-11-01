<?php

declare(strict_types=1);

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Widgets\BusinessOverview;
use Illuminate\Support\Facades\Vite;
use Livewire\Livewire;
use Tests\Support\Personas;

/* AC-38 (Filament), AC-39, AC-40 (Filament), AC-76. */

it('adds the scoped panel styles, footer, CTA and overlay to /app', function (): void {
    $this->actingAs(Personas::owner());

    $html = (string) $this->get('/app')->assertOk()->getContent();

    expect($html)->toContain(Vite::asset('resources/css/panel.css'))
        ->toContain('c360-panel-ui')
        ->toContain('Sistema desenvolvido pelo desenvolvedor HOTWYL | WILLFROMBRASIL')
        ->toContain('https://www.3wonline.com.br/?utm_source=clientify360')
        ->toContain('utm_content=panel-footer')
        ->toContain('id="loading-overlay"')
        ->toContain('--c360-brand-600:'.config('brand.colors.brand.600'))
        ->not->toContain('fonts.bunny');
});

it('does not poll the dashboard overview widget (no recurring overlay)', function (): void {
    $this->actingAs(Personas::owner());

    expect((string) $this->get('/app')->assertOk()->getContent())->not->toContain('wire:poll');

    Livewire::test(BusinessOverview::class)
        ->assertSee('Visão geral')
        ->assertDontSeeHtml('wire:poll');
});

it('renders Filament text inputs with autocomplete="off"', function (): void {
    $this->actingAs(Personas::owner());

    Livewire::test(CreateClient::class)
        ->assertSeeHtml('autocomplete="off"');
});
