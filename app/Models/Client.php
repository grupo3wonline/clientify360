<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasUuid;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToUser, HasFactory, HasUuid, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'document',
        'address',
        'city',
        'state',
        'zip_code',
        'notes',
        'score',
        'score_label',
        'strengths',
        'weaknesses',
        'opportunities',
        'needs_website',
        'needs_system',
        'needs_marketing',
        'needs_ecommerce',
        'needs_seo',
        'needs_social_media',
        'business_segment',
        'company_size',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function reminders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Reminder::class);
    }

    public function interactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Interaction::class);
    }
}
