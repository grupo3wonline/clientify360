<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToAccount, HasFactory, HasUuid, Searchable, SoftDeletes;

    public const UNITS = ['un' => 'Unidade', 'h' => 'Hora', 'mes' => 'Mês', 'kg' => 'Kg', 'm' => 'Metro', 'm2' => 'm²', 'cx' => 'Caixa', 'pct' => 'Pacote'];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id', 'user_id', 'type', 'sku', 'name', 'description', 'unit', 'price', 'promo_price', 'cost',
        'track_stock', 'stock', 'min_stock', 'ncm', 'supplier', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'stock' => 'decimal:3',
            'min_stock' => 'decimal:3',
            'track_stock' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Columns searched by Scout's database engine (always filtered by account in SearchService).
     *
     * @return array<string, mixed>
     */
    #[SearchUsingPrefix(['sku'])]
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('track_stock', true)->whereColumn('stock', '<=', 'min_stock');
    }

    /** Preço efetivo: promoção tem prioridade (regra do sistema-vendas). */
    public function salePrice(): float
    {
        return $this->promo_price !== null && (float) $this->promo_price > 0
            ? (float) $this->promo_price
            : (float) $this->price;
    }

    /** Margem sobre o preço de venda, em %. */
    public function marginPercent(): ?float
    {
        $price = $this->salePrice();

        if ($this->cost === null || $price <= 0) {
            return null;
        }

        return round(($price - (float) $this->cost) / $price * 100, 1);
    }

    public function isLowStock(): bool
    {
        return $this->track_stock && (float) $this->stock <= (float) $this->min_stock;
    }
}
