<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\FinancialEntry;
use App\Models\Quote;
use App\Models\ServiceOrder;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura do módulo de Ordens de Serviço: CRUD, isolamento por conta,
 * numeração única por conta, validação server-side, transições de status e
 * geração de recebível (com a regra anti-duplicidade). Segue o padrão de dois
 * usuários na mesma conta de OwnedRuleAccountIsolationTest/AppointmentModuleTest.
 */
class ServiceOrderModuleTest extends TestCase
{
    use RefreshDatabase;

    private function collaboratorFor(User $owner, string $role = User::ROLE_COLLABORATOR): User
    {
        $member = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($member, $owner->account, $role);

        return $member->fresh();
    }

    /** @return array<string, mixed> */
    private function validPayload(Client $client, array $overrides = []): array
    {
        return array_merge([
            'client_id' => $client->id,
            'title' => 'Instalação do equipamento',
            'total' => '1500.00',
            'opened_at' => now()->format('Y-m-d\TH:i'),
        ], $overrides);
    }

    public function test_it_creates_a_service_order(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente Próprio', 'email' => 'cli-so@ex.com']);

        $response = $this->post(route('service-orders.store'), $this->validPayload($client));

        $order = ServiceOrder::withoutGlobalScope('owned')->where('account_id', $owner->account_id)->firstOrFail();
        $response->assertRedirect(route('service-orders.show', $order));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('service_orders', [
            'account_id' => $owner->account_id,
            'user_id' => $owner->id,
            'title' => 'Instalação do equipamento',
            'status' => ServiceOrder::STATUS_OPEN,
            'number' => 'OS-'.now()->format('Y').'-0001',
        ]);
    }

    public function test_it_shows_index_create_edit_and_show_screens(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $order = ServiceOrder::factory()->forAccount($owner->account_id)->create();

        $this->get(route('service-orders.index'))->assertOk();
        $this->get(route('service-orders.create'))->assertOk();
        $this->get(route('service-orders.edit', $order))->assertOk();
        $this->get(route('service-orders.show', $order))->assertOk();
    }

    public function test_it_updates_and_deletes_a_service_order(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente', 'email' => 'upd-so@ex.com']);
        $order = ServiceOrder::factory()->forClient($client)->create();

        $this->put(route('service-orders.update', $order), $this->validPayload($client, [
            'title' => 'Título atualizado',
            'status' => ServiceOrder::STATUS_IN_PROGRESS,
        ]))->assertRedirect(route('service-orders.show', $order));

        $this->assertDatabaseHas('service_orders', [
            'id' => $order->id,
            'title' => 'Título atualizado',
            'status' => ServiceOrder::STATUS_IN_PROGRESS,
        ]);

        $this->delete(route('service-orders.destroy', $order))->assertRedirect(route('service-orders.index'));
        $this->assertSoftDeleted('service_orders', ['id' => $order->id]);
    }

    public function test_it_isolates_service_orders_between_accounts(): void
    {
        $other = User::factory()->create();
        $foreign = ServiceOrder::factory()->forAccount($other->account_id)->create();

        $user = User::factory()->create();
        $client = Client::create(['name' => 'Cliente', 'email' => 'iso-so@ex.com']);
        $this->actingAs($user);

        $this->get(route('service-orders.show', $foreign))->assertNotFound();
        $this->get(route('service-orders.edit', $foreign))->assertNotFound();
        $this->put(route('service-orders.update', $foreign), $this->validPayload($client))->assertNotFound();
        $this->delete(route('service-orders.destroy', $foreign))->assertNotFound();

        $this->assertDatabaseHas('service_orders', ['id' => $foreign->id, 'deleted_at' => null]);
    }

    public function test_it_allows_collaborator_to_access_teammate_service_order(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $order = ServiceOrder::factory()->forAccount($owner->account_id)->create();
        $client = Client::create(['name' => 'Cliente', 'email' => 'collab-so@ex.com']);

        $collaborator = $this->collaboratorFor($owner);
        $this->actingAs($collaborator);

        $this->get(route('service-orders.show', $order))->assertOk();
        $this->put(route('service-orders.update', $order), $this->validPayload($client, [
            'title' => 'Editado pelo colaborador',
            'status' => ServiceOrder::STATUS_IN_PROGRESS,
        ]))->assertRedirect(route('service-orders.show', $order));
    }

    public function test_numbering_is_sequential_and_unique_per_account(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente', 'email' => 'num-so@ex.com']);

        $this->post(route('service-orders.store'), $this->validPayload($client));
        $this->post(route('service-orders.store'), $this->validPayload($client));

        $numbers = ServiceOrder::withoutGlobalScope('owned')
            ->where('account_id', $owner->account_id)
            ->orderBy('number')
            ->pluck('number')
            ->all();

        $year = now()->format('Y');
        $this->assertSame(["OS-{$year}-0001", "OS-{$year}-0002"], $numbers);
    }

    public function test_two_accounts_can_have_the_same_number(): void
    {
        $year = now()->format('Y');

        $ownerA = User::factory()->create();
        $this->actingAs($ownerA);
        $clientA = Client::create(['name' => 'Cliente A', 'email' => 'a-so@ex.com']);
        $this->post(route('service-orders.store'), $this->validPayload($clientA));

        $ownerB = User::factory()->create();
        $this->actingAs($ownerB);
        $clientB = Client::create(['name' => 'Cliente B', 'email' => 'b-so@ex.com']);
        $this->post(route('service-orders.store'), $this->validPayload($clientB));

        $this->assertDatabaseHas('service_orders', ['account_id' => $ownerA->account_id, 'number' => "OS-{$year}-0001"]);
        $this->assertDatabaseHas('service_orders', ['account_id' => $ownerB->account_id, 'number' => "OS-{$year}-0001"]);
    }

    public function test_it_rejects_client_and_quote_from_another_account(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other);
        $foreignClient = Client::create(['name' => 'Cliente Alheio', 'email' => 'fc-so@ex.com']);
        $foreignQuote = Quote::factory()->forClient($foreignClient)->create();

        $user = User::factory()->create();
        $this->actingAs($user);
        $client = Client::create(['name' => 'Cliente', 'email' => 'own-so@ex.com']);

        $this->post(route('service-orders.store'), $this->validPayload($client, ['client_id' => $foreignClient->id]))
            ->assertSessionHasErrors('client_id');

        $this->post(route('service-orders.store'), $this->validPayload($client, ['quote_id' => $foreignQuote->id]))
            ->assertSessionHasErrors('quote_id');
    }

    public function test_it_rejects_incoherent_dates_and_invalid_status(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente', 'email' => 'dt-so@ex.com']);

        $this->post(route('service-orders.store'), $this->validPayload($client, [
            'opened_at' => now()->format('Y-m-d\TH:i'),
            'expected_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors('expected_at');

        $this->post(route('service-orders.store'), $this->validPayload($client, [
            'opened_at' => now()->format('Y-m-d\TH:i'),
            'completed_at' => now()->subDay()->format('Y-m-d\TH:i'),
        ]))->assertSessionHasErrors('completed_at');

        $order = ServiceOrder::factory()->forClient($client)->create();
        $this->put(route('service-orders.update', $order), $this->validPayload($client, ['status' => 'invalid']))
            ->assertSessionHasErrors('status');
    }

    public function test_it_transitions_status_through_the_actions(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $order = ServiceOrder::factory()->forAccount($owner->account_id)->create();

        $this->patch(route('service-orders.start', $order));
        $this->assertSame(ServiceOrder::STATUS_IN_PROGRESS, $order->fresh()->status);

        $this->patch(route('service-orders.complete', $order));
        $fresh = $order->fresh();
        $this->assertSame(ServiceOrder::STATUS_DONE, $fresh->status);
        $this->assertNotNull($fresh->completed_at);
    }

    public function test_it_cancels_an_open_service_order(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $order = ServiceOrder::factory()->forAccount($owner->account_id)->create();

        $this->patch(route('service-orders.cancel', $order));
        $this->assertSame(ServiceOrder::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_completing_without_quote_generates_a_single_receivable(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente', 'email' => 'rec-so@ex.com']);
        $order = ServiceOrder::factory()->forClient($client)->create(['total' => 800]);

        $this->patch(route('service-orders.complete', $order));

        $entries = FinancialEntry::withoutGlobalScope('owned')
            ->where('account_id', $owner->account_id)
            ->where('direction', FinancialEntry::RECEIVABLE)
            ->get();

        $this->assertCount(1, $entries);
        $this->assertSame('800.00', $entries->first()->amount);
        $this->assertDatabaseHas('financial_entries', [
            'account_id' => $owner->account_id,
            'direction' => FinancialEntry::RECEIVABLE,
            'description' => "Ordem de serviço {$order->number} — {$order->title}",
        ]);
    }

    public function test_completing_with_linked_quote_does_not_generate_a_receivable(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente', 'email' => 'dup-so@ex.com']);
        $quote = Quote::factory()->forAccount($owner->account_id)->create(['client_id' => $client->id]);
        $order = ServiceOrder::factory()->forClient($client)->create([
            'quote_id' => $quote->id,
            'total' => 1200,
        ]);

        $before = FinancialEntry::withoutGlobalScope('owned')->where('account_id', $owner->account_id)->count();
        $this->patch(route('service-orders.complete', $order));
        $after = FinancialEntry::withoutGlobalScope('owned')->where('account_id', $owner->account_id)->count();

        $this->assertSame($before, $after);
        $this->assertDatabaseMissing('financial_entries', [
            'description' => "Ordem de serviço {$order->number} — {$order->title}",
        ]);
    }

    public function test_billing_action_does_not_duplicate_an_existing_receivable(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente', 'email' => 'bill-so@ex.com']);
        $order = ServiceOrder::factory()->forClient($client)->create(['total' => 500]);

        // Conclui (gera o primeiro recebível) e depois dispara a ação explícita.
        $this->patch(route('service-orders.complete', $order));
        $this->patch(route('service-orders.billing', $order));

        $count = FinancialEntry::withoutGlobalScope('owned')
            ->where('account_id', $owner->account_id)
            ->where('direction', FinancialEntry::RECEIVABLE)
            ->count();

        $this->assertSame(1, $count);
    }
}
