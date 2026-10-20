<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
final class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        return [
            // Cria uma conta (via usuário dono) e vincula a oportunidade a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'title' => fake()->sentence(3),
            'value' => fake()->randomFloat(2, 100, 10000),
            'probability' => fake()->numberBetween(0, 100),
            'status' => Deal::STATUS_OPEN,
        ];
    }

    /** Vincula a oportunidade a uma conta já existente. */
    public function forAccount(string $accountId): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $accountId,
        ]);
    }

    /** Vincula a oportunidade a um cliente (reaproveita a conta do cliente). */
    public function forClient(Client $client): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $client->account_id,
            'client_id' => $client->id,
        ]);
    }

    /** Define a etapa do funil da oportunidade. */
    public function forStage(PipelineStage $stage): static
    {
        return $this->state(fn (array $attributes): array => [
            'pipeline_stage_id' => $stage->id,
        ]);
    }
}
