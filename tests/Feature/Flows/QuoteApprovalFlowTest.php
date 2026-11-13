<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Product;
use App\Models\Quote;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;

/*
 * Quote approval flow: create → view → send → approve.
 *
 * Approving a quote:
 *   - marks it as approved
 *   - decrements product stock (when managed)
 *   - generates accounts-receivable entries
 *   - cannot be duplicated without plan limit check
 */

it('creates and views a quote', function (): void {
    $owner  = Personas::owner();
    $client = AccountRecords::make('client', $owner);
    $this->actingAs($owner);

    // The create form loads without error.
    $this->get(route('quotes.create'))->assertOk();

    // Store a minimal quote.
    $this->post(route('quotes.store'), [
        'client_id' => $client->id,
        'title'     => 'Orçamento de Teste',
        'items'     => [
            ['description' => 'Serviço X', 'quantity' => 2, 'unit_price' => 150.00],
        ],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $quote = Quote::where('account_id', $owner->account_id)->firstOrFail();

    // View and print
    $this->get(route('quotes.show', $quote))->assertOk();
    $this->get(route('quotes.print', $quote))->assertOk();
});

it('sends and then approves a quote (full lifecycle)', function (): void {
    $owner  = Personas::owner();
    $client = AccountRecords::make('client', $owner);
    $this->actingAs($owner);

    $this->post(route('quotes.store'), [
        'client_id' => $client->id,
        'title'     => 'Orçamento Ciclo Completo',
        'items'     => [
            ['description' => 'Item', 'quantity' => 1, 'unit_price' => 500.00],
        ],
    ])->assertRedirect();

    $quote = Quote::where('account_id', $owner->account_id)->firstOrFail();

    // Send
    $this->post(route('quotes.send', $quote))->assertRedirect();
    expect($quote->fresh()->status)->toBe('sent');

    // Approve
    $this->post(route('quotes.approve', $quote))->assertRedirect();
    expect($quote->fresh()->status)->toBe('approved');
});

it('duplicates a quote', function (): void {
    $owner = Personas::owner();
    $quote = AccountRecords::make('quote', $owner);
    $this->actingAs($owner);

    $this->post(route('quotes.duplicate', $quote))->assertRedirect();

    expect(Quote::where('account_id', $owner->account_id)->count())->toBe(2);
});

it('blocks cross-account quote access', function (): void {
    $owner = Personas::owner();
    $other = Personas::owner();
    $quote = AccountRecords::make('quote', $other);

    $this->actingAs($owner);

    $this->get(route('quotes.show', $quote))->assertForbidden();
    $this->post(route('quotes.send', $quote))->assertForbidden();
    $this->post(route('quotes.approve', $quote))->assertForbidden();
});
