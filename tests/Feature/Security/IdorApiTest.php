<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Tests\Support\AccountRecords;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

/* Every {id} route of API v1: another account's id answers the pt-BR 404 envelope. */

const IDOR_API_ROUTES = [
    ['api.v1.clients.show', 'client', 'get'],
    ['api.v1.clients.update', 'client', 'put'],
    ['api.v1.clients.destroy', 'client', 'delete'],
    ['api.v1.products.show', 'product', 'get'],
    ['api.v1.quotes.show', 'quote', 'get'],
];

dataset('model-bound api routes', IDOR_API_ROUTES);

it('answers 404 for another account id on every API route', function (string $route, string $parameter, string $method): void {
    $victim = Personas::owner('professional');
    $record = AccountRecords::make($parameter, $victim);
    $snapshot = $record->fresh()?->getAttributes();
    $token = ApiTokens::for(Personas::owner('professional'));

    $this->withToken($token)
        ->{$method.'Json'}(route($route, [$parameter => $record->getKey()]), ['name' => 'Invasor'])
        ->assertNotFound()
        ->assertExactJson(['message' => 'Registro não encontrado.']);

    $this->app['auth']->forgetGuards();
    expect($record->fresh()?->getAttributes())->toBe($snapshot);
})->with('model-bound api routes');

it('covers every API route with a parameter', function (): void {
    $bound = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route): bool => str_starts_with((string) $route->getName(), 'api.v1.') && $route->parameterNames() !== [])
        ->map(fn ($route): string => (string) $route->getName())
        ->values();

    expect($bound->diff(collect(IDOR_API_ROUTES)->pluck(0))->values()->all())->toBe([]);
});
