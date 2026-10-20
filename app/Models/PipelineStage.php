<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Database\Factories\PipelineStageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    /** @use HasFactory<PipelineStageFactory> */
    use BelongsToAccount, HasFactory, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['account_id', 'user_id', 'name', 'color', 'position', 'probability', 'is_active'];

    /** Etapas criadas automaticamente no primeiro acesso ao funil (padrão CrmPlus). */
    public const DEFAULTS = [
        ['name' => 'Prospecção', 'color' => '#6366f1', 'position' => 1, 'probability' => 10],
        ['name' => 'Qualificação', 'color' => '#8b5cf6', 'position' => 2, 'probability' => 25],
        ['name' => 'Proposta', 'color' => '#f59e0b', 'position' => 3, 'probability' => 50],
        ['name' => 'Negociação', 'color' => '#f97316', 'position' => 4, 'probability' => 75],
        ['name' => 'Fechamento', 'color' => '#10b981', 'position' => 5, 'probability' => 90],
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'position' => 'integer',
            'probability' => 'integer',
        ];
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('position');
    }

    /**
     * Garante que a conta tenha etapas. Recebe o account_id explicitamente para
     * funcionar também no formulário público (sem usuário logado).
     */
    public static function ensureDefaultsFor(string $accountId): void
    {
        if (static::withoutGlobalScope('owned')->where('account_id', $accountId)->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $stage) {
            static::create($stage + ['account_id' => $accountId, 'is_active' => true]);
        }
    }
}
