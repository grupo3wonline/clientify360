<?php

declare(strict_types=1);

use App\Models\Client;
use App\Support\AccountCache;
use Tests\Support\Personas;

/** AC-70: usage is never part of the cached summary. */
it('shows fresh usage on /billing while the billing summary is cached', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);

    for ($i = 1; $i <= 10; $i++) {
        Client::create(['name' => "Cliente {$i}", 'email' => "c{$i}@ex.com"]);
    }

    app(AccountCache::class)->putBilling($owner->account_id, 'summary', ['plan' => 'basic', 'status' => 'free'], 1800);

    $this->get(route('billing.index'))
        ->assertOk()
        ->assertSee('10 de 10')
        ->assertSee('Você está usando 10 de 10 clientes permitidos no Básico.');

    Client::where('name', 'Cliente 10')->firstOrFail()->delete();

    $this->get(route('billing.index'))
        ->assertOk()
        ->assertSee('9 de 10')
        ->assertDontSee('Você está usando 10 de 10 clientes');
});
