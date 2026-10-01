<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use BelongsToAccount, HasUuid;

    public const TYPES = ['in' => 'Entrada', 'out' => 'Saída', 'adjust' => 'Ajuste'];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id', 'user_id', 'product_id', 'type', 'quantity', 'balance_after', 'unit_cost',
        'reason', 'document', 'reference_type', 'reference_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'balance_after' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
