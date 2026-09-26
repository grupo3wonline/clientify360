<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use BelongsToUser, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['user_id', 'name', 'color', 'position', 'probability', 'is_active'];

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
     * Garante que o usuário tenha etapas. Recebe o user_id explicitamente para
     * funcionar também no formulário público (sem usuário logado).
     */
    public static function ensureDefaultsFor(string $userId): void
    {
        if (static::withoutGlobalScope('owned')->where('user_id', $userId)->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $stage) {
            static::create($stage + ['user_id' => $userId, 'is_active' => true]);
        }
    }
}
