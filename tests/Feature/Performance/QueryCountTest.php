<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

it('creates the performance indexes', function (): void {
    expect(Schema::hasIndex('clients', 'clients_account_name_index'))->toBeTrue()
        ->and(Schema::hasIndex('financial_entries', ['account_id', 'status', 'due_date']))->toBeTrue()
        ->and(Schema::hasIndex('deals', 'deals_account_stage_index'))->toBeTrue();
});
