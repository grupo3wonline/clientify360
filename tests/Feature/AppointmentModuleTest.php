<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura do módulo de Agendamentos: CRUD, isolamento por conta, validação
 * server-side e mudanças de situação. Segue o padrão de dois usuários na mesma
 * conta de OwnedRuleAccountIsolationTest/TeamManagementTest.
 */
class AppointmentModuleTest extends TestCase
{
    use RefreshDatabase;

    private function collaboratorFor(User $owner, string $role = User::ROLE_COLLABORATOR): User
    {
        $member = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($member, $owner->account, $role);

        return $member->fresh();
    }

    /** @return array<string, mixed> */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Reunião de alinhamento',
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d\TH:i'),
            'type' => 'meeting',
        ], $overrides);
    }

    public function test_it_creates_an_appointment(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $response = $this->post(route('appointments.store'), $this->validPayload());

        $response->assertRedirect(route('appointments.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('appointments', [
            'account_id' => $owner->account_id,
            'user_id' => $owner->id,
            'title' => 'Reunião de alinhamento',
            'status' => Appointment::STATUS_PENDING,
        ]);
    }

    public function test_it_lists_appointments_in_the_calendar(): void
    {
        $owner = User::factory()->create();
        Appointment::factory()->forAccount($owner->account_id)->create();
        $this->actingAs($owner);

        $this->get(route('appointments.index'))->assertOk();
    }

    public function test_it_shows_create_and_edit_screens(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $appointment = Appointment::factory()->forAccount($owner->account_id)->create();

        $this->get(route('appointments.create'))->assertOk();
        $this->get(route('appointments.edit', $appointment))->assertOk();
        $this->get(route('appointments.show', $appointment))->assertOk();
    }

    public function test_it_updates_and_deletes_an_appointment(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $appointment = Appointment::factory()->forAccount($owner->account_id)->create();

        $this->put(route('appointments.update', $appointment), $this->validPayload([
            'title' => 'Título atualizado',
            'status' => Appointment::STATUS_CONFIRMED,
        ]))->assertRedirect(route('appointments.show', $appointment));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'title' => 'Título atualizado',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $this->delete(route('appointments.destroy', $appointment))->assertRedirect(route('appointments.index'));
        $this->assertSoftDeleted('appointments', ['id' => $appointment->id]);
    }

    public function test_it_isolates_appointments_between_accounts(): void
    {
        $other = User::factory()->create();
        $foreign = Appointment::factory()->forAccount($other->account_id)->create();

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('appointments.show', $foreign))->assertNotFound();
        $this->get(route('appointments.edit', $foreign))->assertNotFound();
        $this->put(route('appointments.update', $foreign), $this->validPayload())->assertNotFound();
        $this->delete(route('appointments.destroy', $foreign))->assertNotFound();

        $this->assertDatabaseHas('appointments', ['id' => $foreign->id, 'deleted_at' => null]);
    }

    public function test_it_allows_collaborator_to_access_teammate_appointment_in_same_account(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $appointment = Appointment::factory()->forAccount($owner->account_id)->create();

        $collaborator = $this->collaboratorFor($owner);
        $this->actingAs($collaborator);

        $this->get(route('appointments.show', $appointment))->assertOk();
        $this->put(route('appointments.update', $appointment), $this->validPayload([
            'title' => 'Editado pelo colaborador',
            'status' => Appointment::STATUS_CONFIRMED,
        ]))->assertRedirect(route('appointments.show', $appointment));
    }

    public function test_it_rejects_end_before_start(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $response = $this->post(route('appointments.store'), $this->validPayload([
            'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addHour()->format('Y-m-d\TH:i'),
        ]));

        $response->assertSessionHasErrors('ends_at');
    }

    public function test_it_rejects_invalid_type_and_status(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->post(route('appointments.store'), $this->validPayload(['type' => 'party']))
            ->assertSessionHasErrors('type');

        $appointment = Appointment::factory()->forAccount($owner->account_id)->create();
        $this->put(route('appointments.update', $appointment), $this->validPayload(['status' => 'invalid']))
            ->assertSessionHasErrors('status');
    }

    public function test_it_rejects_client_from_another_account(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other);
        $foreignClient = Client::create(['name' => 'Cliente de Outra Conta', 'email' => 'outra-ap@ex.com']);

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('appointments.store'), $this->validPayload(['client_id' => $foreignClient->id]))
            ->assertSessionHasErrors('client_id');
    }

    public function test_it_confirms_marks_done_and_cancels(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $appointment = Appointment::factory()->forAccount($owner->account_id)->create();

        $this->patch(route('appointments.confirm', $appointment));
        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->fresh()->status);

        $this->patch(route('appointments.done', $appointment));
        $this->assertSame(Appointment::STATUS_DONE, $appointment->fresh()->status);

        $this->patch(route('appointments.cancel', $appointment));
        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
    }
}
