<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    /** @use HasFactory<DealFactory> */
    use BelongsToAccount, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_OPEN = 'open';

    public const STATUS_WON = 'won';

    public const STATUS_LOST = 'lost';

    public const STATUS_LABELS = [
        self::STATUS_OPEN => 'Aberta',
        self::STATUS_WON => 'Ganha',
        self::STATUS_LOST => 'Perdida',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id', 'user_id', 'client_id', 'pipeline_stage_id', 'title', 'description', 'value',
        'probability', 'status', 'expected_close_date', 'closed_at', 'lost_reason', 'source',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'probability' => 'integer',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeWon(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_WON);
    }

    /** Valor ponderado pela probabilidade (previsão de receita do funil). */
    public function weightedValue(): float
    {
        return round((float) $this->value * $this->probability / 100, 2);
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OPEN
            && $this->expected_close_date !== null
            && $this->expected_close_date->isPast();
    }

    public function markWon(): void
    {
        $this->update(['status' => self::STATUS_WON, 'probability' => 100, 'closed_at' => now(), 'lost_reason' => null]);
    }

    public function markLost(string $reason): void
    {
        $this->update(['status' => self::STATUS_LOST, 'probability' => 0, 'closed_at' => now(), 'lost_reason' => $reason]);
    }

    public function reopen(): void
    {
        $this->update([
            'status' => self::STATUS_OPEN,
            'closed_at' => null,
            'lost_reason' => null,
            'probability' => $this->stage?->probability ?? 50,
        ]);
    }
}
