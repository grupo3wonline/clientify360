<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialCategory extends Model
{
    use BelongsToAccount, HasUuid, SoftDeletes;

    public const DEFAULTS = [
        ['name' => 'Vendas de produtos', 'type' => 'income', 'color' => '#10b981'],
        ['name' => 'Prestação de serviços', 'type' => 'income', 'color' => '#6366f1'],
        ['name' => 'Outras receitas', 'type' => 'income', 'color' => '#14b8a6'],
        ['name' => 'Fornecedores / Compras', 'type' => 'expense', 'color' => '#f97316'],
        ['name' => 'Impostos', 'type' => 'expense', 'color' => '#ef4444'],
        ['name' => 'Aluguel e contas', 'type' => 'expense', 'color' => '#f59e0b'],
        ['name' => 'Marketing', 'type' => 'expense', 'color' => '#ec4899'],
        ['name' => 'Salários e pró-labore', 'type' => 'expense', 'color' => '#8b5cf6'],
        ['name' => 'Outras despesas', 'type' => 'expense', 'color' => '#64748b'],
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['account_id', 'user_id', 'name', 'type', 'color'];

    public function entries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class, 'category_id');
    }

    public static function ensureDefaultsFor(string $accountId): void
    {
        if (static::withoutGlobalScope('owned')->withTrashed()->where('account_id', $accountId)->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $category) {
            static::create($category + ['account_id' => $accountId]);
        }
    }
}
