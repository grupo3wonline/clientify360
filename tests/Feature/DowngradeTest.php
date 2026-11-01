<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Product;
use App\Services\SubscriptionService;
use Tests\Support\Personas;
use Tests\Support\Subscriptions;

/**
 * AC-19: when the Profissional subscription ends, existing data stays listed
 * and editable, but creation beyond the Básico limits is blocked everywhere.
 */
beforeEach(function (): void {
    $owner = Personas::owner();
    $subscription = Subscriptions::makeProfessional($owner->account);

    $this->actingAs($owner->fresh());

    for ($i = 1; $i <= 12; $i++) {
        Client::create(['name' => "Cliente {$i}", 'email' => "c{$i}@ex.com"]);
    }

    $subscription->update(['stripe_status' => 'canceled', 'ends_at' => now()->subDay()]);

    $this->owner = $owner->fresh();
    $this->actingAs($this->owner);
});

it('falls back to the Básico limits', function (): void {
    $service = app(SubscriptionService::class);

    expect($service->isProfessional($this->owner))->toBeFalse()
        ->and($service->currentPlan($this->owner)->slug)->toBe('basic')
        ->and($service->canCreate($this->owner, 'clients'))->toBeFalse();
});

it('keeps every existing record listed and editable', function (): void {
    expect(Client::count())->toBe(12);

    $this->get(route('clients.index'))->assertOk()->assertSee('Cliente 12');
    $this->get('/app/clients')->assertOk()->assertSee('Cliente 12');

    $client = Client::where('name', 'Cliente 12')->firstOrFail();

    $this->put(route('clients.update', $client), ['name' => 'Cliente 12 editado', 'person_type' => 'individual'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($client->fresh()->name)->toBe('Cliente 12 editado');
    $this->get("/app/clients/{$client->id}/edit")->assertOk();
});

it('blocks new clients on the Blade route (plan.limit) with the limit message', function (): void {
    $this->get(route('clients.create'))->assertRedirect(route('clients.index'));

    $this->post(route('clients.store'), ['name' => 'Novo', 'person_type' => 'individual'])
        ->assertRedirect(route('clients.index'))
        ->assertSessionHas('error', 'Você atingiu o limite de 10 clientes do plano Básico. Faça upgrade para o plano Profissional para liberar mais.');

    expect(Client::count())->toBe(12);
});

it('blocks new clients on the Filament create page', function (): void {
    $this->get('/app/clients/create')->assertRedirect('/app/clients');
});

it('still allows resources that are under the Básico limit', function (): void {
    $this->get(route('products.create'))->assertOk();

    Product::create(['name' => 'Produto', 'price' => 10]);

    expect(app(SubscriptionService::class)->canCreate($this->owner, 'products'))->toBeTrue();
});

it('blocks Profissional-only features after the downgrade', function (): void {
    $this->get(route('export.clients'))->assertForbidden();
});
