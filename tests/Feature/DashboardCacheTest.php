<?php

declare(strict_types=1);

use App\Filament\Widgets\BusinessOverview;
use App\Models\Client;
use App\Models\Quote;
use App\Services\QuoteService;
use Illuminate\Support\Facades\Cache;
use Tests\Support\Personas;

function accountVersion(string $accountId): int
{
    return (int) Cache::get("acct:{$accountId}:ver", 1);
}

it('bumps the account version on create, update, delete and restore', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);
    $start = accountVersion($owner->account_id);

    $client = Client::create(['name' => 'Versão']);
    expect(accountVersion($owner->account_id))->toBe($start + 1);

    $client->update(['name' => 'Versão 2']);
    expect(accountVersion($owner->account_id))->toBe($start + 2);

    $client->delete();
    expect(accountVersion($owner->account_id))->toBe($start + 3);

    $client->restore();
    expect(accountVersion($owner->account_id))->toBeGreaterThan($start + 3);
});

it('does not touch another account version', function (): void {
    $other = Personas::owner();
    $owner = Personas::owner();
    $before = accountVersion($other->account_id);

    $this->actingAs($owner);
    Client::create(['name' => 'Só minha conta']);

    expect(accountVersion($other->account_id))->toBe($before);
});

it('reflects a new client in the dashboard stats on the next call', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);
    $widget = new BusinessOverview;

    expect($widget->overview()['clients'])->toBe(0);
    expect($widget->overview()['clients'])->toBe(0); // served from cache

    Client::create(['name' => 'Novo cliente']);

    expect($widget->overview()['clients'])->toBe(1);
});

it('bumps the cache when overdue quotes are expired in bulk', function (): void {
    $owner = Personas::owner();
    $client = Client::factory()->create(['account_id' => $owner->account_id]);
    Quote::factory()->forClient($client)->create(['status' => 'sent', 'valid_until' => now()->subDays(3)]);
    $this->actingAs($owner);
    $before = accountVersion($owner->account_id);

    expect(app(QuoteService::class)->expireOverdue())->toBe(1)
        ->and(accountVersion($owner->account_id))->toBe($before + 1)
        ->and(app(QuoteService::class)->expireOverdue())->toBe(0)
        ->and(accountVersion($owner->account_id))->toBe($before + 1);
});
