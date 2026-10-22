<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FinancialCategory;
use App\Models\FinancialEntry;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;

/**
 * Regras de negócio das compras: numeração por conta/ano, criação do cabeçalho
 * com itens (total derivado) e confirmação idempotente que dá entrada no estoque
 * de cada item e gera UMA conta a pagar ao fornecedor com o total da compra.
 */
final class PurchaseService
{
    public function __construct(
        private readonly StockService $stock,
        private readonly FinanceService $finance,
    ) {}

    /** Próximo número no formato CMP-2026-0001, sequencial por conta e por ano. */
    public function nextNumber(string $accountId): string
    {
        $prefix = 'CMP-'.now()->format('Y').'-';

        $last = Purchase::withoutGlobalScope('owned')->withTrashed()
            ->where('account_id', $accountId)
            ->where('number', 'like', $prefix.'%')
            ->orderByDesc('number')
            ->value('number');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data): Purchase {
            $accountId = (string) auth()->user()->account_id;

            $items = $data['items'] ?? [];
            unset($data['items']);

            // account_id/user_id são preenchidos pelo trait BelongsToAccount ao criar.
            $purchase = Purchase::create([
                ...$data,
                'number' => $this->nextNumber($accountId),
                'purchased_at' => $data['purchased_at'] ?? now()->toDateString(),
                'total' => 0,
            ]);

            $total = 0.0;

            foreach ($items as $item) {
                $quantity = (float) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $lineTotal = round($quantity * $unitCost, 2);
                $total += $lineTotal;

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'total' => $lineTotal,
                ]);
            }

            $purchase->update(['total' => round($total, 2)]);

            return $purchase;
        });
    }

    /**
     * Confirma a compra: entrada no estoque de cada item + UMA conta a pagar.
     * Idempotente — se já confirmada, retorna cedo sem duplicar nada.
     */
    public function confirm(Purchase $purchase): void
    {
        if ($purchase->isConfirmed()) {
            return;
        }

        DB::transaction(function () use ($purchase): void {
            $purchase->load('items.product');

            foreach ($purchase->items as $item) {
                $product = $item->product;

                if (!$product instanceof Product) {
                    continue;
                }

                $this->stock->move(
                    $product,
                    'in',
                    (float) $item->quantity,
                    reason: 'Compra '.$purchase->number,
                    unitCost: (float) $item->unit_cost,
                    document: $purchase->document_number,
                    reference: $purchase,
                );
            }

            FinancialCategory::ensureDefaultsFor($purchase->account_id);
            $categoryId = FinancialCategory::withoutGlobalScope('owned')
                ->where('account_id', $purchase->account_id)
                ->where('type', 'expense')
                ->where('name', 'Fornecedores / Compras')
                ->value('id');

            $this->finance->createInstallments([
                'account_id' => $purchase->account_id,
                'user_id' => $purchase->user_id,
                'direction' => FinancialEntry::PAYABLE,
                'description' => 'Compra '.$purchase->number.' — '.($purchase->supplier ?? 'Fornecedor'),
                'counterparty' => $purchase->supplier,
                'category_id' => $categoryId,
                'amount' => (float) $purchase->total,
                'due_date' => $purchase->purchased_at->toDateString(),
            ], 1);

            $purchase->update(['confirmed_at' => now()]);
        });
    }
}
