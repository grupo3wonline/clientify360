<?php

declare(strict_types=1);

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Client;
use App\Models\Product;
use Filament\Actions\Action;
use Livewire\Livewire;
use Tests\Support\Personas;

function seedClients(int $count): void
{
    for ($i = 1; $i <= $count; $i++) {
        Client::create(['name' => "Cliente {$i}", 'email' => "c{$i}@ex.com"]);
    }
}

it('redirects the Filament create page to the list when the client limit is reached', function (): void {
    $this->actingAs(Personas::owner());
    seedClients(10);

    $this->get('/app/clients/create')->assertRedirect('/app/clients');

    Livewire::test(CreateClient::class)
        ->assertRedirect(ListClients::getUrl())
        ->assertNotified('Limite do plano atingido');
});

it('halts the Filament create when the limit is reached after mount', function (): void {
    $this->actingAs(Personas::owner());
    seedClients(9);

    $page = Livewire::test(CreateClient::class)->assertNoRedirect();

    Client::create(['name' => 'Cliente 10', 'email' => 'c10@ex.com']);

    $page->fillForm(['name' => 'Cliente 11', 'email' => 'c11@ex.com'])
        ->call('create')
        ->assertNotified('Limite do plano atingido');

    expect(Client::count())->toBe(10);
});

it('disables the "Novo" header action with a tooltip at the limit', function (): void {
    $this->actingAs(Personas::owner());
    seedClients(10);

    Livewire::test(ListClients::class)
        ->assertActionDisabled('create')
        ->assertActionExists('create', fn (Action $action): bool => $action->getTooltip() === 'Limite do plano atingido');
});

it('keeps the Filament create open for Profissional', function (): void {
    $this->actingAs(Personas::owner('professional'));
    seedClients(10);

    $this->get('/app/clients/create')->assertOk();
    Livewire::test(ListClients::class)->assertActionEnabled('create');
});

it('applies the products limit to the Filament product pages', function (): void {
    $this->actingAs(Personas::owner());

    for ($i = 1; $i <= 20; $i++) {
        Product::create(['name' => "Produto {$i}", 'price' => 10]);
    }

    $this->get('/app/products/create')->assertRedirect('/app/products');
    Livewire::test(CreateProduct::class)->assertRedirect(ListProducts::getUrl());
    Livewire::test(ListProducts::class)->assertActionDisabled('create');
});
