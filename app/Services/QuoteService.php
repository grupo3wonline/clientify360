<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Activity;
use App\Models\FinancialCategory;
use App\Models\Product;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Orçamentos (CrmPlus) + conversão em venda (sistema-vendas / OSPOS).
 *
 * Aprovar um orçamento é o que o sistema considera "venda":
 *  1. baixa o estoque dos produtos com controle de estoque;
 *  2. gera as contas a receber (à vista ou parcelado);
 *  3. marca a oportunidade vinculada como ganha;
 *  4. registra no histórico do cliente.
 */
final class QuoteService
{
    public function __construct(
        private readonly StockService $stock,
        private readonly FinanceService $finance,
    ) {}

    /** Próximo número no formato ORC-2026-0001, sequencial por usuário e por ano. */
    public function nextNumber(string $userId): string
    {
        $prefix = 'ORC-' . now()->format('Y') . '-';

        $last = Quote::withoutGlobalScope('owned')->withTrashed()
            ->where('user_id', $userId)
            ->where('number', 'like', $prefix . '%')
            ->orderByDesc('number')
            ->value('number');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cria ou atualiza o orçamento com seus itens. Os totais são SEMPRE
     * recalculados no servidor — nunca confiamos no total vindo do navegador.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array{product_id?: ?string, description: string, quantity: numeric, unit_price: numeric}>  $items
     */
    public function save(?Quote $quote, array $data, array $items): Quote
    {
        return DB::transaction(function () use ($quote, $data, $items): Quote {
            if ($quote === null) {
                $userId = (string) auth()->id();
                $quote = new Quote(['user_id' => $userId, 'number' => $this->nextNumber($userId), 'status' => 'draft']);
            } elseif (!$quote->isEditable()) {
                throw new RuntimeException('Orçamentos aprovados, recusados ou expirados não podem ser editados.');
            }

            $quote->fill([
                'client_id' => $data['client_id'],
                'deal_id' => $data['deal_id'] ?? null,
                'title' => $data['title'],
                'valid_until' => $data['valid_until'] ?? null,
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'discount' => (float) ($data['discount'] ?? 0),
            ]);
            $quote->save();

            $quote->items()->delete();
            $subtotal = 0.0;

            // Produtos do próprio usuário (escopo global) — impede usar produto de outra conta
            $productIds = array_filter(array_column($items, 'product_id'));
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach (array_values($items) as $index => $item) {
                $quantity = round((float) $item['quantity'], 3);
                $unitPrice = round((float) $item['unit_price'], 2);
                $total = round($quantity * $unitPrice, 2);
                $subtotal += $total;

                $productId = $item['product_id'] ?? null;

                $quote->items()->create([
                    'product_id' => $productId && $products->has($productId) ? $productId : null,
                    'description' => $item['description'],
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $total,
                    'position' => $index + 1,
                ]);
            }

            $discount = min(max((float) $quote->discount, 0), $subtotal);

            $quote->update([
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'total' => round($subtotal - $discount, 2),
            ]);

            return $quote->fresh(['items', 'client']);
        });
    }

    public function markSent(Quote $quote): void
    {
        $this->assertEditable($quote);
        $quote->update(['status' => 'sent']);
    }

    public function reject(Quote $quote): void
    {
        $this->assertEditable($quote);
        $quote->update(['status' => 'rejected']);
    }

    /**
     * @param  array{installments?: int, first_due_date?: string, payment_method?: ?string}  $options
     */
    public function approve(Quote $quote, array $options = []): Quote
    {
        $this->assertEditable($quote);

        return DB::transaction(function () use ($quote, $options): Quote {
            $quote->loadMissing('items.product', 'client', 'deal');

            // 1. Estoque
            $insufficient = [];
            foreach ($quote->items as $item) {
                $product = $item->product;
                if ($product === null || !$product->track_stock) {
                    continue;
                }
                if ((float) $product->stock < (float) $item->quantity) {
                    $insufficient[] = sprintf(
                        '%s (saldo %s, pedido %s)',
                        $product->name,
                        rtrim(rtrim(number_format((float) $product->stock, 3, ',', '.'), '0'), ','),
                        rtrim(rtrim(number_format((float) $item->quantity, 3, ',', '.'), '0'), ','),
                    );
                }
            }

            if ($insufficient !== [] && !($options['allow_negative_stock'] ?? false)) {
                throw new RuntimeException('Estoque insuficiente: ' . implode('; ', $insufficient) . '.');
            }

            foreach ($quote->items as $item) {
                if ($item->product !== null && $item->product->track_stock) {
                    $this->stock->move($item->product, 'out', (float) $item->quantity, "Venda {$quote->number}", null, $quote->number, $quote);
                }
            }

            // 2. Contas a receber
            if ((float) $quote->total > 0) {
                FinancialCategory::ensureDefaultsFor($quote->user_id);
                $hasServiceOnly = $quote->items->every(fn ($i) => $i->product?->type === 'service');
                $categoryName = $hasServiceOnly ? 'Prestação de serviços' : 'Vendas de produtos';
                $categoryId = FinancialCategory::where('type', 'income')->where('name', $categoryName)->value('id');

                $this->finance->createInstallments([
                    'direction' => 'receivable',
                    'description' => "Orçamento {$quote->number} — {$quote->title}",
                    'client_id' => $quote->client_id,
                    'quote_id' => $quote->id,
                    'category_id' => $categoryId,
                    'amount' => (float) $quote->total,
                    'due_date' => $options['first_due_date'] ?? now()->toDateString(),
                    'payment_method' => $options['payment_method'] ?? null,
                ], max(1, (int) ($options['installments'] ?? 1)));
            }

            // 3. Oportunidade
            $quote->deal?->markWon();

            // 4. Histórico do cliente
            Activity::create([
                'user_id' => $quote->user_id,
                'client_id' => $quote->client_id,
                'deal_id' => $quote->deal_id,
                'type' => 'proposal',
                'title' => "Orçamento {$quote->number} aprovado",
                'description' => 'Total: R$ ' . number_format((float) $quote->total, 2, ',', '.'),
                'happened_at' => now(),
                'outcome' => 'positive',
            ]);

            $quote->update(['status' => 'approved', 'approved_at' => now()]);

            return $quote->fresh();
        });
    }

    public function duplicate(Quote $quote): Quote
    {
        $items = $quote->items->map(fn ($i) => [
            'product_id' => $i->product_id,
            'description' => $i->description,
            'quantity' => $i->quantity,
            'unit_price' => $i->unit_price,
        ])->all();

        return $this->save(null, [
            'client_id' => $quote->client_id,
            'deal_id' => $quote->deal_id,
            'title' => $quote->title . ' (cópia)',
            'valid_until' => now()->addDays(15)->toDateString(),
            'payment_terms' => $quote->payment_terms,
            'notes' => $quote->notes,
            'discount' => $quote->discount,
        ], $items);
    }

    private function assertEditable(Quote $quote): void
    {
        if (!$quote->isEditable()) {
            throw new RuntimeException('Este orçamento já foi finalizado.');
        }
    }
}
