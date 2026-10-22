<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseItem>
 */
final class PurchaseItemFactory extends Factory
{
    protected $model = PurchaseItem::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(3, 1, 20);
        $unitCost = fake()->randomFloat(2, 5, 500);

        return [
            'account_id' => fn () => Purchase::factory()->create()->account_id,
            'purchase_id' => fn () => Purchase::factory()->create()->id,
            'product_id' => fn () => Product::factory()->create()->id,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total' => round($quantity * $unitCost, 2),
        ];
    }

    /** Vincula o item a uma compra já existente (reaproveita a conta). */
    public function forPurchase(Purchase $purchase): static
    {
        return $this->state(fn (array $attributes): array => [
            'account_id' => $purchase->account_id,
            'purchase_id' => $purchase->id,
        ]);
    }
}
