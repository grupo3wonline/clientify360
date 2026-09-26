<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Movimentação de estoque (conceito de OSPOS `inventory` + sistema-vendas `compras`).
 * Toda alteração de saldo passa por aqui e deixa rastro em stock_movements,
 * com o saldo resultante — assim o histórico é auditável.
 */
final class StockService
{
    /**
     * @param  string  $type  in | out | adjust  (adjust = define o saldo absoluto)
     */
    public function move(
        Product $product,
        string $type,
        float $quantity,
        ?string $reason = null,
        ?float $unitCost = null,
        ?string $document = null,
        ?Model $reference = null,
    ): StockMovement {
        if (!in_array($type, ['in', 'out', 'adjust'], true)) {
            throw new InvalidArgumentException("Tipo de movimentação inválido: {$type}");
        }

        if ($type !== 'adjust' && $quantity <= 0) {
            throw new InvalidArgumentException('A quantidade deve ser maior que zero.');
        }

        return DB::transaction(function () use ($product, $type, $quantity, $reason, $unitCost, $document, $reference): StockMovement {
            // lockForUpdate evita que duas vendas simultâneas leiam o mesmo saldo
            $locked = Product::withoutGlobalScope('owned')->lockForUpdate()->findOrFail($product->id);
            $current = (float) $locked->stock;

            [$delta, $newBalance] = match ($type) {
                'in' => [$quantity, $current + $quantity],
                'out' => [-$quantity, $current - $quantity],
                'adjust' => [$quantity - $current, $quantity],
            };

            $updates = ['stock' => $newBalance];

            // Entrada com custo informado atualiza o custo do produto (último custo de compra)
            if ($type === 'in' && $unitCost !== null && $unitCost > 0) {
                $updates['cost'] = $unitCost;
            }

            $locked->update($updates);
            $product->setRawAttributes($locked->getAttributes(), true);

            return StockMovement::create([
                'user_id' => $locked->user_id,
                'product_id' => $locked->id,
                'type' => $type,
                'quantity' => abs($delta),
                'balance_after' => $newBalance,
                'unit_cost' => $unitCost,
                'reason' => $reason,
                'document' => $document,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
            ]);
        });
    }
}
