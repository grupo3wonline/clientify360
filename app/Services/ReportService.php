<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Deal;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Traits\CachesPerAccount;
use Carbon\Carbon;

/**
 * Métricas comerciais (funil, orçamentos e produtos) para a tela de Relatórios.
 * Mantém o controller fino e reaproveita os scopes dos models.
 */
final class ReportService
{
    use CachesPerAccount;

    private const TTL = 900;

    /**
     * Desempenho comercial no período informado.
     *
     * @return array<string, mixed>
     */
    public function commercial(Carbon $startDate): array
    {
        return $this->rememberForAccount('reports:commercial:'.$startDate->format('Y-m-d'), self::TTL, fn (): array => $this->buildCommercial($startDate));
    }

    /** @return array<string, mixed> */
    private function buildCommercial(Carbon $startDate): array
    {
        $wonInPeriod = Deal::won()->where('closed_at', '>=', $startDate);
        $lostInPeriod = Deal::where('status', Deal::STATUS_LOST)->where('closed_at', '>=', $startDate);

        $wonCount = (clone $wonInPeriod)->count();
        $lostCount = (clone $lostInPeriod)->count();
        $closedCount = $wonCount + $lostCount;

        $approvedQuotes = Quote::where('status', 'approved')->where('approved_at', '>=', $startDate);

        return [
            'deals_won' => $wonCount,
            'deals_lost' => $lostCount,
            'deals_won_value' => (float) (clone $wonInPeriod)->sum('value'),
            'conversion_rate' => $closedCount > 0 ? round($wonCount / $closedCount * 100, 1) : 0.0,
            'open_pipeline_value' => (float) Deal::open()->sum('value'),
            'quotes_approved' => (clone $approvedQuotes)->count(),
            'quotes_approved_value' => (float) (clone $approvedQuotes)->sum('total'),
            'quotes_waiting' => Quote::where('status', 'sent')->count(),
            'lost_reasons' => (clone $lostInPeriod)
                ->whereNotNull('lost_reason')
                ->selectRaw('lost_reason, COUNT(*) as total')
                ->groupBy('lost_reason')
                ->orderByDesc('total')
                ->limit(5)
                ->pluck('total', 'lost_reason')
                ->toArray(),
            'top_products' => $this->topProducts($startDate),
        ];
    }

    /**
     * Giro e valor de estoque por produto rastreado (track_stock = true).
     * Agregação em PHP para evitar SQL específico de driver.
     *
     * @return array{products: array<int, array{name: string, stock: float, cost: float, stock_value: float, below_min: bool}>, total_stock_value: float}
     */
    public function stockTurnover(): array
    {
        return $this->rememberForAccount('reports:stock', self::TTL, fn (): array => $this->buildStockTurnover());
    }

    /** @return array{products: array<int, array{name: string, stock: float, cost: float, stock_value: float, below_min: bool}>, total_stock_value: float} */
    private function buildStockTurnover(): array
    {
        $products = Product::where('track_stock', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product): array => [
                'name' => (string) $product->name,
                'stock' => (float) $product->stock,
                'cost' => (float) $product->cost,
                'stock_value' => round((float) $product->stock * (float) $product->cost, 2),
                'below_min' => $product->isLowStock(),
            ])
            ->sortByDesc('stock_value')
            ->values()
            ->all();

        $totalStockValue = array_sum(array_column($products, 'stock_value'));

        return [
            'products' => $products,
            'total_stock_value' => round((float) $totalStockValue, 2),
        ];
    }

    /**
     * Compras confirmadas no período: total comprado, por fornecedor e itens
     * mais comprados. Baseado nos models Purchase/PurchaseItem (FEAT-001),
     * respeitando o isolamento por conta via global scope 'owned'.
     *
     * @return array{total_purchased: float, by_supplier: array<int, array{supplier: string, total: float}>, top_items: array<int, array{name: string, quantity: float, total: float}>}
     */
    public function purchases(Carbon $startDate): array
    {
        return $this->rememberForAccount('reports:purchases:'.$startDate->format('Y-m-d'), self::TTL, fn (): array => $this->buildPurchases($startDate));
    }

    /** @return array{total_purchased: float, by_supplier: array<int, array{supplier: string, total: float}>, top_items: array<int, array{name: string, quantity: float, total: float}>} */
    private function buildPurchases(Carbon $startDate): array
    {
        $confirmedInPeriod = Purchase::whereNotNull('confirmed_at')
            ->where('purchased_at', '>=', $startDate->copy()->startOfDay());

        $totalPurchased = (float) (clone $confirmedInPeriod)->sum('total');

        $bySupplier = (clone $confirmedInPeriod)
            ->whereNotNull('supplier')
            ->selectRaw('supplier, SUM(total) as total')
            ->groupBy('supplier')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn (Purchase $purchase): array => [
                'supplier' => (string) $purchase->supplier,
                'total' => (float) $purchase->total,
            ])
            ->all();

        $purchaseIds = (clone $confirmedInPeriod)->pluck('id');

        $topItems = $purchaseIds->isEmpty()
            ? []
            : PurchaseItem::whereIn('purchase_id', $purchaseIds)
                ->with('product')
                ->get()
                ->groupBy('product_id')
                ->map(fn ($items): array => [
                    'name' => (string) (optional($items->first()->product)->name ?? 'Produto removido'),
                    'quantity' => (float) $items->sum(fn (PurchaseItem $item): float => (float) $item->quantity),
                    'total' => (float) $items->sum(fn (PurchaseItem $item): float => (float) $item->total),
                ])
                ->sortByDesc('total')
                ->take(5)
                ->values()
                ->all();

        return [
            'total_purchased' => round($totalPurchased, 2),
            'by_supplier' => $bySupplier,
            'top_items' => $topItems,
        ];
    }

    /**
     * Produtos mais vendidos (itens de orçamentos aprovados no período).
     *
     * @return array<int, array{name: string, quantity: float, total: float}>
     */
    private function topProducts(Carbon $startDate): array
    {
        $approvedQuoteIds = Quote::where('status', 'approved')
            ->where('approved_at', '>=', $startDate)
            ->pluck('id');

        if ($approvedQuoteIds->isEmpty()) {
            return [];
        }

        return QuoteItem::whereIn('quote_id', $approvedQuoteIds)
            ->selectRaw('description, SUM(quantity) as quantity, SUM(total) as total')
            ->groupBy('description')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn (QuoteItem $item): array => [
                'name' => (string) $item->description,
                'quantity' => (float) $item->quantity,
                'total' => (float) $item->total,
            ])
            ->all();
    }
}
