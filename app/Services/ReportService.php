<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Deal;
use App\Models\Quote;
use App\Models\QuoteItem;
use Carbon\Carbon;

/**
 * Métricas comerciais (funil, orçamentos e produtos) para a tela de Relatórios.
 * Mantém o controller fino e reaproveita os scopes dos models.
 */
final class ReportService
{
    /**
     * Desempenho comercial no período informado.
     *
     * @return array<string, mixed>
     */
    public function commercial(Carbon $startDate): array
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
