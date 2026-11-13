<?php

declare(strict_types=1);

use App\Models\Client;
use App\Models\Interaction;
use App\Models\Reminder;
use Tests\Support\AccountRecords;
use Tests\Support\Personas;

/*
 * CRM client flow: create → view → search → interaction → reminder → delete.
 *
 * Tests the full CRUD lifecycle from an owner's perspective, verifying that
 * each step returns the expected HTTP status and that data isolation holds.
 */

it('creates, views and deletes a client', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);

    // 1. Create
    $this->post(route('clients.store'), [
        'name'        => 'João da Silva',
        'email'       => 'joao@example.com',
        'person_type' => 'individual',
    ])->assertRedirect(route('clients.index'))
      ->assertSessionHasNoErrors();

    $client = Client::where('email', 'joao@example.com')->firstOrFail();

    expect($client->account_id)->toBe($owner->account_id);

    // 2. View
    $this->get(route('clients.show', $client))->assertOk();
    $this->get(route('clients.edit', $client))->assertOk();

    // 3. Update
    $this->put(route('clients.update', $client), [
        'name'        => 'João da Silva Atualizado',
        'person_type' => 'individual',
    ])->assertRedirect();

    expect($client->fresh()->name)->toBe('João da Silva Atualizado');

    // 4. Delete (soft)
    $this->delete(route('clients.destroy', $client))->assertRedirect(route('clients.index'));
    expect(Client::find($client->id))->toBeNull();
    expect(Client::withTrashed()->find($client->id))->not->toBeNull();
});

it('searches clients by name without SQL injection side-effects', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);

    Client::factory()->create(['account_id' => $owner->account_id, 'name' => 'Maria Oliveira']);

    $response = $this->get(route('clients.index', ['search' => 'Maria']));
    $response->assertOk();
    expect((string) $response->getContent())->toContain('Maria Oliveira');

    // SQL injection payload must not cause a 500.
    $this->get(route('clients.index', ['search' => "' OR '1'='1' --"]))->assertOk();
});

it('records an interaction on a client', function (): void {
    $owner  = Personas::owner();
    $client = AccountRecords::make('client', $owner);
    $this->actingAs($owner);

    $this->post(route('interactions.store'), [
        'client_id' => $client->id,
        'type'      => 'support',
        'channel'   => 'whatsapp',
        'subject'   => 'Teste de atendimento',
        'status'    => 'open',
    ])->assertRedirect();

    expect(Interaction::where('client_id', $client->id)->count())->toBe(1);
});

it('creates and completes a reminder', function (): void {
    $owner = Personas::owner();
    $this->actingAs($owner);

    $this->post(route('reminders.store'), [
        'title'     => 'Ligar para cliente',
        'remind_at' => now()->addDay()->toDateTimeString(),
    ])->assertRedirect();

    $reminder = Reminder::where('account_id', $owner->account_id)->firstOrFail();

    $this->patch(route('reminders.complete', $reminder))->assertRedirect();

    expect($reminder->fresh()->completed_at)->not->toBeNull();
});

it('blocks cross-account client access (IDOR)', function (): void {
    $owner = Personas::owner();
    $other = Personas::owner();

    $otherClient = Client::factory()->create(['account_id' => $other->account_id]);

    $this->actingAs($owner);

    $this->get(route('clients.show', $otherClient))->assertForbidden();
    $this->get(route('clients.edit', $otherClient))->assertForbidden();
    $this->put(route('clients.update', $otherClient), ['name' => 'Hacked'])->assertForbidden();
    $this->delete(route('clients.destroy', $otherClient))->assertForbidden();
});
