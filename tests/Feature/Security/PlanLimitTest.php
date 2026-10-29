<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\LeadForm;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;

/*
 * AC-17: every Blade creation path stops at the Básico limit with the pt-BR
 * limit message and creates nothing; Profissional passes. The Filament pages
 * (FilamentPlanLimitTest), the API (ApiClientsTest), the public lead capture
 * (CommercialModulesTest) and attachments (AttachmentTest) are covered there.
 */

/** @return array<string, array{0: class-string, 1: int, 2: Closure(User): void, 3: list<array{0: string, 1: string, 2: bool}>, 4: string}> */
function planLimitCases(): array
{
    $quote = fn (User $u) => AccountRecords::make('quote', $u);

    return [
        'clients' => [Client::class, 10, fn (User $u) => Client::factory()->create(['account_id' => $u->account_id, 'user_id' => $u->id]),
            [['get', 'clients.create', false], ['post', 'clients.store', false]], 'clients.index'],
        'products' => [Product::class, 20, fn (User $u) => Product::factory()->forAccount($u->account_id)->create(['user_id' => $u->id]),
            [['get', 'products.create', false], ['post', 'products.store', false]], 'products.index'],
        'quotes_per_month' => [Quote::class, 5, $quote,
            [['get', 'quotes.create', false], ['post', 'quotes.store', false], ['post', 'quotes.duplicate', true]], 'quotes.index'],
        'lead_forms' => [LeadForm::class, 1, fn (User $u) => LeadForm::create([
            'account_id' => $u->account_id, 'user_id' => $u->id, 'name' => 'F', 'slug' => 'f-'.Str::lower(Str::random(8)),
            'title' => 'T', 'fields' => [['name' => 'email', 'label' => 'E-mail', 'type' => 'email', 'required' => true]],
        ]), [['get', 'lead-forms.create', false], ['post', 'lead-forms.store', false]], 'lead-forms.index'],
    ];
}

dataset('plan limits', planLimitCases());

it('blocks every Blade creation path at the Básico limit', function (string $model, int $limit, Closure $seed, array $paths, string $back): void {
    $owner = Personas::owner();
    for ($i = 0; $i < $limit; $i++) {
        $seed($owner);
    }
    $existing = $model::query()->first();
    $this->actingAs($owner);

    foreach ($paths as [$method, $route, $withRecord]) {
        $url = $withRecord ? route($route, $existing) : route($route);
        $this->{$method}($url, [])
            ->assertRedirect(route($back))
            ->assertSessionHas('error', fn (string $message): bool => str_contains($message, 'Você atingiu o limite de '.$limit)
                && str_contains($message, 'plano Básico'));
    }

    expect($model::count())->toBe($limit);
})->with('plan limits');

it('keeps the same creation paths open on Profissional', function (string $model, int $limit, Closure $seed, array $paths): void {
    $owner = Personas::owner('professional');
    for ($i = 0; $i < $limit; $i++) {
        $seed($owner);
    }
    $this->actingAs($owner);

    foreach ($paths as [$method, $route]) {
        if ($method === 'get') {
            $this->get(route($route))->assertOk();
        }
    }
})->with('plan limits');

it('lists every plan.limit route in the dataset or in the documented exceptions', function (): void {
    $covered = collect(planLimitCases())->flatMap(fn (array $case): array => array_column($case[3], 1));
    $elsewhere = ['clients.attachments.store']; // AttachmentTest: limit redirect to the Anexos card

    $limited = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($r): bool => collect($r->gatherMiddleware())->contains(fn ($m): bool => is_string($m) && str_starts_with($m, 'plan.limit')))
        ->map(fn ($r): string => (string) $r->getName());

    expect($limited->diff($covered)->diff($elsewhere)->values()->all())->toBe([]);
});
