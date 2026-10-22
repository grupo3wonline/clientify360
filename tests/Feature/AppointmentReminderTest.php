<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Reminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Reaproveitamento do mecanismo de Lembretes ao criar/confirmar agendamentos. */
class AppointmentReminderTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Reunião importante',
            'starts_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(2)->addHour()->format('Y-m-d\TH:i'),
            'type' => 'meeting',
        ], $overrides);
    }

    public function test_it_generates_a_reminder_when_requested(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->post(route('appointments.store'), $this->payload(['generate_reminder' => '1']));

        $appointment = Appointment::firstOrFail();
        $this->assertNotNull($appointment->reminder_id);

        $reminder = Reminder::findOrFail($appointment->reminder_id);
        $this->assertSame($owner->account_id, $reminder->account_id);
        $this->assertSame('Agendamento: Reunião importante', $reminder->title);
        $this->assertTrue($reminder->remind_at->equalTo($appointment->starts_at));
    }

    public function test_it_does_not_generate_a_reminder_when_not_requested(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->post(route('appointments.store'), $this->payload());

        $appointment = Appointment::firstOrFail();
        $this->assertNull($appointment->reminder_id);
        $this->assertSame(0, Reminder::count());
    }
}
