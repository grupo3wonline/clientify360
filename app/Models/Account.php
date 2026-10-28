<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Conta/tenant do SaaS. Agrupa o dono e os colaboradores e é a titular da
 * assinatura Stripe (Billable) e de todos os dados de negócio.
 */
class Account extends Model implements HasMedia
{
    use Billable, HasUuid, InteractsWithMedia, SoftDeletes;

    public const LOGO_MIME_TYPES = ['image/png', 'image/jpeg', 'image/webp'];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'owner_id',
    ];

    /** Account logo for branded PDFs (private disk, single file). */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->useDisk('media')
            ->singleFile()
            ->acceptsMimeTypes(self::LOGO_MIME_TYPES);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(AccountInvitation::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
