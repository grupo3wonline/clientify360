<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
final class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+30 days');

        return [
            // Cria uma conta (via usuário dono) e vincula o agendamento a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'title' => fake()->sentence(3),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+1 hour'),
            'type' => 'meeting',
            'status' => Appointment::STATUS_PENDING,
        ];
    }

    /** Vincula o agendamento a uma conta já existente. */
    public function forAccount(string $accountId): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $accountId,
        ]);
    }

    /** Vincula o agendamento a um cliente (reaproveita a conta do cliente). */
    public function forClient(Client $client): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $client->account_id,
            'client_id' => $client->id,
        ]);
    }

    /** Vincula o agendamento a uma oportunidade (reaproveita a conta da oportunidade). */
    public function forDeal(Deal $deal): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $deal->account_id,
            'deal_id' => $deal->id,
        ]);
    }
}
