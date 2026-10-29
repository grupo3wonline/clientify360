<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Traits\HasUuid;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuid, Notifiable, SoftDeletes;

    /** Papéis dentro de uma conta. */
    public const ROLE_OWNER = 'owner';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_COLLABORATOR = 'collaborator';

    public const ROLE_LABELS = [
        self::ROLE_OWNER => 'Dono',
        self::ROLE_MANAGER => 'Gestor',
        self::ROLE_COLLABORATOR => 'Colaborador',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id',
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
        'ai_gemini_key',
        'ai_groq_key',
        'ai_openrouter_key',
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

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Contas das quais este usuário é o dono.
     *
     * @return HasMany<Account, $this>
     */
    public function ownedAccounts(): HasMany
    {
        return $this->hasMany(Account::class, 'owner_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function isOwner(): bool
    {
        return $this->account_id !== null && $this->account?->owner_id === $this->id;
    }

    /** A conta do usuário tem o plano Profissional ativo? (assinatura é da conta) */
    public function isProfessional(): bool
    {
        return $this->account !== null && $this->account->subscribed('default');
    }

    /** Has the user accepted the current version of the Terms of Use (config/legal.php)? */
    public function hasAcceptedCurrentTerms(): bool
    {
        return $this->terms_accepted_at !== null
            && $this->terms_version === (string) config('legal.terms_version');
    }

    /**
     * Panel access requires an account. E-mail verification is enforced by the
     * panel auth middleware (redirect to /verify-email instead of a 403).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->account_id !== null;
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /** @param  string  $token */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
