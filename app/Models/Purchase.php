<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Database\Factories\PurchaseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Compra (entrada de mercadoria) de um fornecedor. Ao confirmar, dá entrada no
 * estoque de cada item e gera UMA conta a pagar (FinancialEntry PAYABLE) com o
 * total. O fornecedor é texto livre (não há entidade Supplier).
 *
 * @property Carbon $purchased_at
 * @property Carbon|null $confirmed_at
 */
class Purchase extends Model
{
    /** @use HasFactory<PurchaseFactory> */
    use BelongsToAccount, HasFactory, HasUuid, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'user_id',
        'number',
        'supplier',
        'document_number',
        'purchased_at',
        'total',
        'notes',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'purchased_at' => 'date',
            'confirmed_at' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    /** @return HasMany<PurchaseItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
