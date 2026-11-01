<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FinancialEntry;
use App\Traits\CachesPerAccount;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class FinanceService
{
    use CachesPerAccount;

    private const TTL = 600;

    /**
     * Cria 1..N lançamentos dividindo o valor. A diferença de arredondamento
     * vai para a primeira parcela, para que a soma bata exatamente com o total
     * (ex.: R$ 100,00 em 3x = 33,34 + 33,33 + 33,33).
     *
     * @param  array<string, mixed>  $data
     * @return Collection<int, FinancialEntry>
     */
    public function createInstallments(array $data, int $installments = 1): Collection
    {
        $installments = max(1, min($installments, 60));
        $total = round((float) $data['amount'], 2);
        $base = floor($total / $installments * 100) / 100;
        $remainder = round($total - $base * $installments, 2);
        $firstDue = Carbon::parse($data['due_date']);

        return DB::transaction(function () use ($data, $installments, $base, $remainder, $firstDue): Collection {
            $entries = collect();

            for ($i = 1; $i <= $installments; $i++) {
                $entries->push(FinancialEntry::create([
                    ...$data,
                    'amount' => $i === 1 ? round($base + $remainder, 2) : $base,
                    'due_date' => $firstDue->copy()->addMonthsNoOverflow($i - 1)->toDateString(),
                    'description' => $installments > 1 ? "{$data['description']} ({$i}/{$installments})" : $data['description'],
                    'installment' => $installments > 1 ? $i : null,
                    'installments_total' => $installments > 1 ? $installments : null,
                    'status' => 'pending',
                ]));
            }

            return $entries;
        });
    }

    /** Baixa (pagamento/recebimento). */
    public function settle(FinancialEntry $entry, ?string $paidAt = null, ?float $paidAmount = null, ?string $method = null): void
    {
        $entry->update([
            'status' => 'paid',
            'paid_at' => $paidAt ?? now()->toDateString(),
            'paid_amount' => $paidAmount ?? (float) $entry->amount,
            'payment_method' => $method ?? $entry->payment_method,
        ]);
    }

    public function reopen(FinancialEntry $entry): void
    {
        $entry->update(['status' => 'pending', 'paid_at' => null, 'paid_amount' => null]);
    }

    /** Resumo do mês para o painel financeiro e o dashboard. */
    public function monthSummary(?Carbon $month = null): array
    {
        $month ??= now();

        return $this->rememberForAccount('finance:summary:'.$month->format('Y-m'), self::TTL, fn (): array => $this->buildMonthSummary($month));
    }

    /** @return array<string, float|int> */
    private function buildMonthSummary(Carbon $month): array
    {
        // Limites como datetime cobrindo o mês inteiro: `paid_at`/`due_date` têm cast `date`
        // e são gravados como `Y-m-d 00:00:00`, então o fim do mês precisa alcançar o fim do dia.
        $start = $month->copy()->startOfMonth()->startOfDay();
        $end = $month->copy()->endOfMonth()->endOfDay();

        $received = (float) FinancialEntry::receivable()->where('status', 'paid')->whereBetween('paid_at', [$start, $end])->sum('paid_amount');
        $paid = (float) FinancialEntry::payable()->where('status', 'paid')->whereBetween('paid_at', [$start, $end])->sum('paid_amount');

        return [
            'received' => $received,
            'paid' => $paid,
            'balance' => $received - $paid,
            'to_receive' => (float) FinancialEntry::receivable()->pending()->whereBetween('due_date', [$start, $end])->sum('amount'),
            'to_pay' => (float) FinancialEntry::payable()->pending()->whereBetween('due_date', [$start, $end])->sum('amount'),
            'overdue_receivable' => (float) FinancialEntry::receivable()->overdue()->sum('amount'),
            'overdue_payable' => (float) FinancialEntry::payable()->overdue()->sum('amount'),
            'overdue_count' => FinancialEntry::overdue()->count(),
        ];
    }

    /**
     * Fluxo de caixa dos últimos N meses (realizado) + mês atual e próximo (previsto).
     * Agregado em PHP para funcionar igual em MySQL e SQLite.
     *
     * @return array<int, array{label: string, in: float, out: float, forecast_in: float, forecast_out: float}>
     */
    public function cashFlow(int $monthsBack = 5, int $monthsAhead = 1): array
    {
        // The current month is part of the key so the window rolls over on its own.
        return $this->rememberForAccount('finance:cashflow:'.now()->format('Y-m').":{$monthsBack}:{$monthsAhead}", self::TTL, fn (): array => $this->buildCashFlow($monthsBack, $monthsAhead));
    }

    /** @return array<int, array{label: string, in: float, out: float, forecast_in: float, forecast_out: float}> */
    private function buildCashFlow(int $monthsBack, int $monthsAhead): array
    {
        $from = now()->subMonths($monthsBack)->startOfMonth()->startOfDay();
        $to = now()->addMonths($monthsAhead)->endOfMonth()->endOfDay();

        $paid = FinancialEntry::where('status', 'paid')
            ->whereBetween('paid_at', [$from, $to])
            ->get(['direction', 'paid_at', 'paid_amount']);

        $pending = FinancialEntry::pending()
            ->whereBetween('due_date', [now()->startOfMonth()->startOfDay(), $to])
            ->get(['direction', 'due_date', 'amount']);

        $months = [];
        for ($d = $from->copy(); $d->lte($to); $d->addMonth()) {
            $months[$d->format('Y-m')] = [
                'label' => ucfirst($d->translatedFormat('M/y')),
                'in' => 0.0, 'out' => 0.0, 'forecast_in' => 0.0, 'forecast_out' => 0.0,
            ];
        }

        foreach ($paid as $e) {
            $key = $e->paid_at->format('Y-m');
            if (isset($months[$key])) {
                $months[$key][$e->direction === 'receivable' ? 'in' : 'out'] += (float) $e->paid_amount;
            }
        }

        foreach ($pending as $e) {
            $key = $e->due_date->format('Y-m');
            if (isset($months[$key])) {
                $months[$key][$e->direction === 'receivable' ? 'forecast_in' : 'forecast_out'] += (float) $e->amount;
            }
        }

        return array_values($months);
    }
}
