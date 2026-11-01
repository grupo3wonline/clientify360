<?php

declare(strict_types=1);

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Client;
use App\Models\Product;
use App\Services\SearchService;
use Filament\Facades\Filament;
use Tests\Support\Personas;

beforeEach(function (): void {
    $this->other = Personas::owner();
    Client::factory()->create(['account_id' => $this->other->account_id, 'name' => 'Maria Alheia', 'email' => 'maria@alheia.com']);
    Product::factory()->forAccount($this->other->account_id)->create(['name' => 'Cadeira Alheia', 'sku' => 'CAD-999']);

    $this->owner = Personas::owner();
    Client::factory()->create(['account_id' => $this->owner->account_id, 'name' => 'Maria Própria', 'email' => 'maria@propria.com']);
    Product::factory()->forAccount($this->owner->account_id)->create(['name' => 'Cadeira Própria', 'sku' => 'CAD-001']);
});

it('returns only own-account clients and products in Filament global search', function (): void {
    $this->actingAs($this->owner);
    Filament::setCurrentPanel('app');

    $clients = ClientResource::getGlobalSearchResults('Maria');
    $products = ProductResource::getGlobalSearchResults('CAD');

    expect($clients->pluck('title')->all())->toBe(['Maria Própria'])
        ->and($clients->first()->details)->toBe(['E-mail' => 'maria@propria.com'])
        ->and($products->pluck('title')->all())->toBe(['Cadeira Própria'])
        ->and($products->first()->details)->toBe(['SKU' => 'CAD-001']);
});

it('filters by account even without an authenticated user', function (): void {
    $results = app(SearchService::class)->clients($this->owner, 'Maria');

    expect(collect($results->items())->pluck('name')->all())->toBe(['Maria Própria']);
});

it('treats SQL-like input as a plain term and never leaks other accounts', function (): void {
    $this->actingAs($this->owner);

    $results = app(SearchService::class)->clients($this->owner, "' OR 1=1 --");
    $all = app(SearchService::class)->clients($this->owner, '%');

    expect($results->total())->toBe(0)
        ->and(collect($all->items())->pluck('account_id')->unique()->all())->toBe([$this->owner->account_id]);
});

it('returns nothing for an empty term and cuts terms at 100 characters', function (): void {
    $service = app(SearchService::class);

    expect($service->clients($this->owner, '   ')->total())->toBe(0)
        ->and(mb_strlen($service->normalize(str_repeat('a', 150))))->toBe(100);
});
