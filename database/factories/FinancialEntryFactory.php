<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FinancialEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialEntry>
 */
final class FinancialEntryFactory extends Factory
{
    protected $model = FinancialEntry::class;

    public function definition(): array
    {
        return [
            // Cria uma conta (via usuário dono) e vincula o lançamento a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'direction' => FinancialEntry::RECEIVABLE,
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 50, 5000),
            'due_date' => fake()->dateTimeBetween('now', '+60 days'),
            'status' => 'pending',
        ];
    }

    /** Vincula o lançamento a uma conta já existente. */
    public function forAccount(string $accountId): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $accountId,
        ]);
    }

    public function payable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'direction' => FinancialEntry::PAYABLE,
        ]);
    }
}
