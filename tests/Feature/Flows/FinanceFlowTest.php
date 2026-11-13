<?php

declare(strict_types=1);

use App\Models\FinancialEntry;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;

/*
 * Finance flow: create entry → settle → reopen → cancel → delete.
 *
 * Verifies each transition is accessible to the owner and that the
 * account isolation is enforced.
 */

it('creates a payable entry', function (): void {
    $owner    = Personas::owner();
    $category = AccountRecords::make('category', $owner);
    $this->actingAs($owner);

    $this->post(route('finance.store'), [
        'direction'   => 'payable',
        'description' => 'Conta de luz',
        'amount'      => 150.00,
        'due_date'    => now()->addDays(5)->toDateString(),
        'category_id' => $category->id,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(FinancialEntry::where('account_id', $owner->account_id)->count())->toBe(1);
});

it('settles and reopens a financial entry', function (): void {
    $owner = Personas::owner();
    $entry = AccountRecords::make('entry', $owner);
    $this->actingAs($owner);

    $this->post(route('finance.settle', $entry), [
        'paid_at' => now()->toDateString(),
        'amount'  => $entry->amount,
    ])->assertRedirect();

    expect($entry->fresh()->status)->toBe('paid');

    $this->post(route('finance.reopen', $entry))->assertRedirect();

    expect($entry->fresh()->status)->toBe('pending');
});

it('cancels a financial entry', function (): void {
    $owner = Personas::owner();
    $entry = AccountRecords::make('entry', $owner);
    $this->actingAs($owner);

    $this->post(route('finance.cancel', $entry))->assertRedirect();

    expect($entry->fresh()->status)->toBe('cancelled');
});

it('accesses the finance dashboard and entries list', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);

    $this->get(route('finance.index'))->assertOk();
    $this->get(route('finance.entries'))->assertOk();
    $this->get(route('finance.categories'))->assertOk();
});

it('blocks cross-account finance entry access', function (): void {
    $owner = Personas::owner();
    $other = Personas::owner();
    $entry = AccountRecords::make('entry', $other);

    $this->actingAs($owner);

    $this->post(route('finance.settle', $entry), ['paid_at' => now()->toDateString()])->assertForbidden();
    $this->post(route('finance.cancel', $entry))->assertForbidden();
    $this->delete(route('finance.destroy', $entry))->assertForbidden();
});
