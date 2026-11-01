<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\FinancialEntry;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\AccountRecords;
use Tests\Support\ApiTokens;
use Tests\Support\Personas;

/** Queries run by one GET of $url (after a warm-up request). */
function countQueries(string $url): int
{
    test()->get($url)->assertOk();

    DB::flushQueryLog();
    DB::enableQueryLog();
    test()->get($url)->assertOk();
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

/** @return array{0: int, 1: int} query counts with 5 and with 30 tagged clients in the same account */
function queriesFor5And30(string $url): array
{
    $owner = Personas::owner();
    $tag = Tag::create(['account_id' => $owner->account_id, 'name' => 'VIP', 'color' => '#ff0000']);
    $seed = fn (int $n) => Client::factory()->count($n)->create(['account_id' => $owner->account_id])
        ->each(fn (Client $c) => $c->tags()->attach($tag->id));

    $seed(5);
    test()->actingAs($owner);
    $five = countQueries($url);

    $seed(25);
    $thirty = countQueries($url);

    return [$five, $thirty];
}

it('runs the same number of queries for 5 and 30 clients on the Blade list', function (): void {
    [$five, $thirty] = queriesFor5And30(route('clients.index'));

    expect($thirty)->toBe($five);
});

it('runs the same number of queries for 5 and 30 clients on the panel list', function (): void {
    [$five, $thirty] = queriesFor5And30('/app/clients');

    expect($thirty)->toBe($five);
});

/**
 * Query counts of $url with 5 and with 30 rows made by $seed (as guest, for the owner's account).
 *
 * @param  Closure(User): void  $seed
 * @return array{0: int, 1: int}
 */
function queryGrowth(Closure $url, Closure $seed, bool $api = false): array
{
    $owner = Personas::owner('professional');
    $token = $api ? ApiTokens::for($owner) : null;
    $run = function () use ($owner, $url, $token): int {
        $api = $token !== null;
        $request = fn () => $api ? test()->withToken($token)->getJson($url()) : test()->actingAs($owner)->get($url());
        $request()->assertOk();
        DB::flushQueryLog();
        DB::enableQueryLog();
        $request()->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();
        auth()->guard('web')->logout();
        app('auth')->forgetGuards();
        app('auth')->shouldUse('web');

        return $count;
    };

    for ($i = 0; $i < 5; $i++) {
        $seed($owner);
    }
    $five = $run();
    for ($i = 0; $i < 25; $i++) {
        $seed($owner);
    }

    return [$five, $run()];
}

dataset('listings', [
    'Blade products' => [fn () => route('products.index'), fn (User $u) => AccountRecords::make('product', $u)->update(['is_active' => true]), false],
    'Blade quotes' => [fn () => route('quotes.index'), fn (User $u) => AccountRecords::make('quote', $u), false],
    'Blade deals list' => [fn () => route('deals.list'), fn (User $u) => AccountRecords::make('deal', $u), false],
    'Blade finance entries' => [fn () => route('finance.entries', ['month' => now()->format('Y-m')]), fn (User $u) => FinancialEntry::factory()->forAccount($u->account_id)->create([
        'user_id' => $u->id, 'due_date' => now()->startOfMonth()->addDays(random_int(0, 20)),
        'client_id' => AccountRecords::make('client', $u)->id, 'category_id' => AccountRecords::make('category', $u)->id,
    ]), false],
    'panel products' => [fn () => '/app/products', fn (User $u) => AccountRecords::make('product', $u), false],
    'API clients' => [fn () => route('api.v1.clients.index'), fn (User $u) => AccountRecords::make('client', $u), true],
]);

it('runs the same number of queries for 5 and 30 rows', function (Closure $url, Closure $seed, bool $api): void {
    [$five, $thirty] = queryGrowth($url, $seed, $api);

    expect($thirty)->toBe($five);
})->with('listings');

it('creates the performance indexes', function (): void {
    expect(Schema::hasIndex('clients', 'clients_account_name_index'))->toBeTrue()
        ->and(Schema::hasIndex('financial_entries', ['account_id', 'status', 'due_date']))->toBeTrue()
        ->and(Schema::hasIndex('deals', 'deals_account_stage_index'))->toBeTrue();
});
