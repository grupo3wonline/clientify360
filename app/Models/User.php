<?php

declare(strict_types=1);

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Traits\HasUuid;
use Carbon\CarbonImmutable;
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
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property Carbon|null $password_changed_at When the current password was set (drives the 90-day expiry).
 */
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
        'password_changed_at',
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
            'password_changed_at' => 'datetime',
            'lgpd_consent' => 'boolean',
            'lgpd_consent_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'ai_gemini_key' => 'encrypted',
            'ai_groq_key' => 'encrypted',
            'ai_openrouter_key' => 'encrypted',
        ];
    }

    /**
     * Keeps password_changed_at in step with the password on EVERY write path (registration,
     * reset, profile, forced change, Filament, seeders), so no caller can forget it.
     * An explicit value set together with the password is respected (tests, imports).
     */
    protected static function booted(): void
    {
        static::saving(function (self $user): void {
            if ($user->isDirty('password') && !$user->isDirty('password_changed_at')) {
                $user->password_changed_at = now();
            }
        });
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** When the password stops being valid, or null when it is unknown. */
    public function passwordExpiresAt(): ?CarbonImmutable
    {
        if ($this->password_changed_at === null) {
            return null;
        }

        return CarbonImmutable::instance($this->password_changed_at)
            ->addDays((int) config('security.password.max_age_days'));
    }

    /**
     * The password is valid through the END of its deadline day: on that day the user is told
     * "expires today" and is blocked from the next day on. Everything below is derived from one
     * number (days left, by calendar day) so the reminder and the block can never disagree.
     */
    public function passwordExpired(): bool
    {
        $days = $this->passwordDaysLeft();

        return $days !== null && $days < 0;
    }

    /**
     * Calendar days until the deadline day (0 = expires today, negative = expired), or null
     * when the date is unknown. By calendar day so "7 days" matches what the user reads.
     */
    public function passwordDaysLeft(): ?int
    {
        $expiresAt = $this->passwordExpiresAt();

        if ($expiresAt === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($expiresAt->startOfDay(), false);
    }

    /** Inside the reminder window (default: the last 7 days, including the deadline day). */
    public function passwordExpiresSoon(): bool
    {
        $days = $this->passwordDaysLeft();

        return $days !== null && $days >= 0 && $days <= (int) config('security.password.warning_days');
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
