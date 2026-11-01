<?php

declare(strict_types=1);

use App\Models\Product;
use Tests\Support\Personas;

it('blocks a collaborator from finance, product writes, billing and team routes', function (): void {
    $owner = Personas::owner();
    $product = Product::factory()->forAccount($owner->account_id)->create();
    $collaborator = Personas::collaborator($owner->account);

    $this->actingAs($collaborator);

    $this->get(route('finance.index'))->assertForbidden();
    $this->get(route('finance.entries'))->assertForbidden();
    $this->post(route('finance.store'), [])->assertForbidden();
    $this->post(route('products.store'), [])->assertForbidden();
    $this->put(route('products.update', $product), [])->assertForbidden();
    $this->delete(route('products.destroy', $product))->assertForbidden();
    $this->get(route('billing.index'))->assertForbidden();
    $this->get(route('team.index'))->assertForbidden();

    // Read access that the collaborator role does have.
    $this->get(route('products.index'))->assertOk();
    $this->get(route('products.show', $product))->assertOk();
    $this->get(route('clients.index'))->assertOk();
});

it('gives a manager everything but billing', function (): void {
    $owner = Personas::owner();
    $manager = Personas::manager($owner->account);

    $this->actingAs($manager);

    $this->get(route('finance.index'))->assertOk();
    $this->get(route('team.index'))->assertOk();
    $this->get(route('billing.index'))->assertForbidden();
    $this->get(route('billing.invoices'))->assertForbidden();
});

it('lets the owner reach finance, products, team and reports', function (): void {
    $owner = Personas::owner();

    $this->actingAs($owner);

    $this->get(route('finance.index'))->assertOk();
    $this->get(route('products.create'))->assertOk();
    $this->get(route('team.index'))->assertOk();
    $this->get(route('reports.index'))->assertOk();
});

it('hides sidebar links the collaborator cannot open', function (): void {
    $owner = Personas::owner();
    $collaborator = Personas::collaborator($owner->account);

    $html = $this->actingAs($collaborator)->get(route('clients.index'))->assertOk()->getContent();

    expect($html)->not->toContain('href="'.route('finance.index').'"')
        ->and($html)->not->toContain('href="'.route('team.index').'"')
        ->and($html)->toContain('href="'.route('clients.index').'"');
});
