<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Immutable cookie-consent receipt (proof of consent). No SoftDeletes and no
 * updated_at: like StockMovement, it is an event log that is never edited.
 */
class CookieConsent extends Model
{
    use HasUuid, Prunable;

    public const UPDATED_AT = null;

    public const SOURCES = ['banner', 'preferences', 'do_not_sell', 'gpc'];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'consent_id',
        'user_id',
        'analytics',
        'marketing',
        'policy_version',
        'source',
        'ip_hash',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'analytics' => 'boolean',
            'marketing' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Consent receipts are kept for 5 years (privacy policy, "Retenção"). */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subYears(5));
    }
}
