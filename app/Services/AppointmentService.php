<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Appointment;
use App\Models\Reminder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Regras de negócio dos agendamentos. Espelha o mecanismo Atividade→Lembrete
 * do ActivityController: ao criar/confirmar um agendamento, opcionalmente
 * gera um Reminder (que alimenta a Agenda e as Notificações).
 */
final class AppointmentService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Appointment
    {
        $generateReminder = (bool) ($data['generate_reminder'] ?? false);
        unset($data['generate_reminder']);

        return DB::transaction(function () use ($data, $generateReminder): Appointment {
            $reminderId = $generateReminder
                ? $this->makeReminder($data['title'], $data['description'] ?? null, $data['starts_at'], $data['client_id'] ?? null)
                : null;

            return Appointment::create($data + ['reminder_id' => $reminderId]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Appointment $appointment, array $data): Appointment
    {
        unset($data['generate_reminder']);
        $appointment->update($data);

        return $appointment;
    }

    /** Confirma o agendamento e, se ainda não houver lembrete, gera um. */
    public function confirm(Appointment $appointment): Appointment
    {
        DB::transaction(function () use ($appointment): void {
            if ($appointment->reminder_id === null && Carbon::parse($appointment->starts_at)->isFuture()) {
                $reminderId = $this->makeReminder(
                    $appointment->title,
                    $appointment->description,
                    $appointment->starts_at,
                    $appointment->client_id,
                );
                $appointment->reminder_id = $reminderId;
            }

            $appointment->status = Appointment::STATUS_CONFIRMED;
            $appointment->save();
        });

        return $appointment;
    }

    public function markDone(Appointment $appointment): Appointment
    {
        $appointment->markDone();

        return $appointment;
    }

    public function cancel(Appointment $appointment): Appointment
    {
        $appointment->cancel();

        return $appointment;
    }

    /** Cria um lembrete vinculado ao agendamento (account_id/user_id via trait). */
    private function makeReminder(string $title, ?string $description, mixed $remindAt, ?string $clientId): string
    {
        return Reminder::create([
            'client_id' => $clientId,
            'title' => 'Agendamento: '.$title,
            'description' => $description,
            'remind_at' => $remindAt,
        ])->id;
    }
}
