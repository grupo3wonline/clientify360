<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Database\Factories\ServiceOrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Ordem de Serviço — post-sale execution tracking (open -> in_progress -> done).
 *
 * The sale itself is handled by the quote approval flow (QuoteService::approve).
 * A service order represents the delivery/execution that follows the sale.
 *
 * @property Carbon $opened_at
 * @property Carbon|null $expected_at
 * @property Carbon|null $completed_at
 */
class ServiceOrder extends Model
{
    /** @use HasFactory<ServiceOrderFactory> */
    use BelongsToAccount, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    /** Situações da ordem de serviço (rótulos pt-BR). */
    public const STATUSES = [
        self::STATUS_OPEN => 'Aberta',
        self::STATUS_IN_PROGRESS => 'Em andamento',
        self::STATUS_DONE => 'Concluída',
        self::STATUS_CANCELLED => 'Cancelada',
    ];

    /** Cores dos badges de status (classes Tailwind). */
    public const STATUS_COLORS = [
        self::STATUS_OPEN => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        self::STATUS_IN_PROGRESS => 'bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300',
        self::STATUS_DONE => 'bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300',
        self::STATUS_CANCELLED => 'bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'user_id',
        'client_id',
        'quote_id',
        'number',
        'title',
        'description',
        'status',
        'opened_at',
        'expected_at',
        'completed_at',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'expected_at' => 'datetime',
            'completed_at' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? self::STATUS_COLORS[self::STATUS_OPEN];
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}
