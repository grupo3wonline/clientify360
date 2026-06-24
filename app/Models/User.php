<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use Billable, HasFactory, HasUuid, Notifiable, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'lgpd_consent',
        'lgpd_consent_at',
        'terms_accepted_at',
        'terms_version',
        'ai_gemini_key',
        'ai_groq_key',
        'ai_openrouter_key',
        'ai_preferred_provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'lgpd_consent' => 'boolean',
            'lgpd_consent_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'ai_gemini_key' => 'encrypted',
            'ai_groq_key' => 'encrypted',
            'ai_openrouter_key' => 'encrypted',
        ];
    }

    public function clients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Client::class);
    }
}
