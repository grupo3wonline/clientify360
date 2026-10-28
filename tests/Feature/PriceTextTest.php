<?php

declare(strict_types=1);

use Tests\Support\Personas;

/** AC-20 (except /llms.txt, added with the SEO phase). */
it('shows R$ 29,90 and a struck R$ 79,90 on the landing, never 39,90', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('R$ 29,90')
        ->assertSee('<s class="text-lg text-surface-400">R$ 79,90</s>', false)
        ->assertSee('62% OFF')
        ->assertSee('O plano Profissional custa R$ 29,90 por mês (preço promocional; valor de referência R$ 79,90)')
        ->assertDontSee('39,90')
        ->assertDontSee('39.90');
});

it('uses the new price on the /billing plan cards', function (): void {
    $this->actingAs(Personas::owner());

    $this->get(route('billing.index'))->assertOk()->assertSee('R$ 29,90')->assertDontSee('39,90');
});

it('keeps 39,90 out of the plan config and marketing sources', function (): void {
    $sources = [
        base_path('config/plans.php'),
        resource_path('views/pages/home.blade.php'),
        app_path('Http/Controllers/PageController.php'),
        app_path('Console/Commands/StripeSetupCommand.php'),
    ];

    foreach ($sources as $file) {
        expect(file_get_contents($file))->not->toContain('39,90')->not->toContain('39.90');
    }
});
