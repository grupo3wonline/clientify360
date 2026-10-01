<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use BelongsToAccount, HasUuid, SoftDeletes;

    public const STATUS_LABELS = [
        'draft' => 'Rascunho',
        'sent' => 'Enviado',
        'approved' => 'Aprovado',
        'rejected' => 'Recusado',
        'expired' => 'Expirado',
    ];

    public const STATUS_COLORS = [
        'draft' => 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-300',
        'sent' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        'approved' => 'bg-accent-100 text-accent-700 dark:bg-accent-900/40 dark:text-accent-300',
        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'expired' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id', 'user_id', 'client_id', 'deal_id', 'number', 'title', 'status', 'valid_until',
        'subtotal', 'discount', 'total', 'payment_terms', 'notes', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('position');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'sent'], true);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? self::STATUS_COLORS['draft'];
    }

    public function isExpired(): bool
    {
        return $this->isEditable() && $this->valid_until !== null && $this->valid_until->endOfDay()->isPast();
    }
}
