<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Database\Factories\FinancialEntryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialEntry extends Model
{
    /** @use HasFactory<FinancialEntryFactory> */
    use BelongsToAccount, HasFactory, HasUuid, SoftDeletes;

    public const RECEIVABLE = 'receivable';

    public const PAYABLE = 'payable';

    public const PAYMENT_METHODS = [
        'pix' => 'PIX',
        'cash' => 'Dinheiro',
        'credit_card' => 'Cartão de crédito',
        'debit_card' => 'Cartão de débito',
        'boleto' => 'Boleto',
        'transfer' => 'Transferência',
        'other' => 'Outro',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id', 'user_id', 'direction', 'category_id', 'client_id', 'quote_id', 'description', 'counterparty',
        'amount', 'due_date', 'paid_at', 'paid_amount', 'status', 'payment_method',
        'installment', 'installments_total', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'category_id')->withTrashed();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /** "Atrasado" é calculado (pendente + vencido), não gravado — nunca fica desatualizado. */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'pending')->whereDate('due_date', '<', now()->toDateString());
    }

    public function scopeReceivable(Builder $query): Builder
    {
        return $query->where('direction', self::RECEIVABLE);
    }

    public function scopePayable(Builder $query): Builder
    {
        return $query->where('direction', self::PAYABLE);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->lt(now()->startOfDay());
    }

    public function displayStatus(): string
    {
        if ($this->isOverdue()) {
            return 'overdue';
        }

        return $this->status;
    }

    public function statusLabel(): string
    {
        return match ($this->displayStatus()) {
            'overdue' => 'Atrasado',
            'paid' => $this->direction === self::RECEIVABLE ? 'Recebido' : 'Pago',
            'canceled' => 'Cancelado',
            default => 'Pendente',
        };
    }

    public function statusColor(): string
    {
        return match ($this->displayStatus()) {
            'overdue' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
            'paid' => 'bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300',
            'canceled' => 'bg-surface-100 text-surface-500 dark:bg-surface-800 dark:text-surface-400',
            default => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        };
    }
}
