<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FinancialCategory;
use App\Models\FinancialEntry;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Regras de negócio das ordens de serviço: numeração por conta/ano, transições
 * de status e geração de recebível (contas a receber) ao concluir ou via ação
 * explícita "Gerar cobrança".
 */
final class ServiceOrderService
{
    public function __construct(
        private readonly FinanceService $finance,
    ) {}

    /** Próximo número no formato OS-2026-0001, sequencial por conta e por ano. */
    public function nextNumber(string $accountId): string
    {
        $prefix = 'OS-'.now()->format('Y').'-';

        $last = ServiceOrder::withoutGlobalScope('owned')->withTrashed()
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
    public function create(array $data): ServiceOrder
    {
        return DB::transaction(function () use ($data): ServiceOrder {
            $accountId = (string) auth()->user()->account_id;

            // account_id/user_id são preenchidos pelo trait BelongsToAccount ao criar.
            return ServiceOrder::create([
                ...$data,
                'number' => $this->nextNumber($accountId),
                'status' => ServiceOrder::STATUS_OPEN,
                'opened_at' => $data['opened_at'] ?? now(),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(ServiceOrder $order, array $data): ServiceOrder
    {
        $order->update($data);

        return $order;
    }

    /** open -> in_progress. */
    public function start(ServiceOrder $order): ServiceOrder
    {
        if (!$order->isOpen()) {
            throw new RuntimeException('Apenas ordens de serviço abertas podem ser iniciadas.');
        }

        $order->update(['status' => ServiceOrder::STATUS_IN_PROGRESS]);

        return $order;
    }

    /** open/in_progress -> done; grava completed_at e tenta gerar o recebível. */
    public function complete(ServiceOrder $order): ServiceOrder
    {
        if ($order->isDone() || $order->isCancelled()) {
            throw new RuntimeException('Esta ordem de serviço já foi finalizada.');
        }

        return DB::transaction(function () use ($order): ServiceOrder {
            $order->update([
                'status' => ServiceOrder::STATUS_DONE,
                'completed_at' => $order->completed_at ?? now(),
            ]);

            $this->generateReceivable($order);

            return $order;
        });
    }

    /** Qualquer status (exceto done) -> cancelled. */
    public function cancel(ServiceOrder $order): ServiceOrder
    {
        if ($order->isDone()) {
            throw new RuntimeException('Ordens de serviço concluídas não podem ser canceladas.');
        }

        $order->update(['status' => ServiceOrder::STATUS_CANCELLED]);

        return $order;
    }

    /**
     * Gera a conta a receber da ordem de serviço, respeitando a regra
     * anti-duplicidade. Usada tanto por complete() quanto pela ação explícita.
     */
    public function generateReceivable(ServiceOrder $order): void
    {
        // Anti-duplicate rule: skip when a linked quote already generated the
        // receivables on approval (QuoteService::approve) — otherwise the
        // customer would be billed twice. Also skip zero/negative totals and
        // orders that already produced a financial entry (billing idempotency).
        if ($order->quote_id !== null) {
            return;
        }

        if ((float) $order->total <= 0) {
            return;
        }

        DB::transaction(function () use ($order): void {
            $exists = FinancialEntry::withoutGlobalScope('owned')
                ->where('account_id', $order->account_id)
                ->where('description', 'like', "Ordem de serviço {$order->number} —%")
                ->exists();

            if ($exists) {
                return;
            }

            FinancialCategory::ensureDefaultsFor($order->account_id);
            $categoryId = FinancialCategory::withoutGlobalScope('owned')
                ->where('account_id', $order->account_id)
                ->where('type', 'income')
                ->where('name', 'Prestação de serviços')
                ->value('id');

            $this->finance->createInstallments([
                'account_id' => $order->account_id,
                'user_id' => $order->user_id,
                'direction' => FinancialEntry::RECEIVABLE,
                'description' => "Ordem de serviço {$order->number} — {$order->title}",
                'client_id' => $order->client_id,
                'category_id' => $categoryId,
                'amount' => (float) $order->total,
                'due_date' => now()->toDateString(),
            ], 1);
        });
    }
}
