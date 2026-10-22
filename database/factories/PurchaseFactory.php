<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Purchase>
 */
final class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition(): array
    {
        return [
            // Cria uma conta (via usuário dono) e vincula a compra a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'number' => 'CMP-'.now()->format('Y').'-'.str_pad((string) fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'supplier' => fake()->company(),
            'document_number' => (string) fake()->numberBetween(1000, 999999),
            'purchased_at' => now()->toDateString(),
            'total' => 0,
            'notes' => fake()->optional()->sentence(),
            'confirmed_at' => null,
        ];
    }

    /** Vincula a compra a uma conta já existente. */
    public function forAccount(string $accountId): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $accountId,
        ]);
    }
}
