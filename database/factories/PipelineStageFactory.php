<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PipelineStage>
 */
final class PipelineStageFactory extends Factory
{
    protected $model = PipelineStage::class;

    public function definition(): array
    {
        return [
            // Cria uma conta (via usuário dono) e vincula a etapa a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'position' => fake()->numberBetween(1, 10),
            'probability' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    /** Vincula a etapa a uma conta já existente. */
    public function forAccount(string $accountId): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $accountId,
        ]);
    }

    /** Etapa padrão criada pela conta (sem colaborador — user_id nulo). */
    public function defaultStage(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
        ]);
    }
}
