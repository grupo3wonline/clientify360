<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            // Cria uma conta (via usuário dono) e vincula o produto a ela.
            'account_id' => fn () => User::factory()->create()->account_id,
            'type' => 'product',
            'name' => fake()->words(2, true),
            'unit' => 'un',
            'price' => fake()->randomFloat(2, 10, 1000),
            'cost' => fake()->randomFloat(2, 5, 500),
            'track_stock' => false,
            'stock' => 0,
            'min_stock' => 0,
            'is_active' => true,
        ];
    }

    /** Vincula o produto a uma conta já existente. */
    public function forAccount(string $accountId): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $accountId,
        ]);
    }
}
