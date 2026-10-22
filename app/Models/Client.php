<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use BelongsToAccount, HasFactory, HasUuid, LogsActivity, SoftDeletes;

    /** Auditoria LGPD: registra alterações nos dados pessoais do cliente. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'document', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('client');
    }

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'user_id',
        'name',
        'email',
        'phone',
        'document',
        'person_type',
        'address',
        'city',
        'state',
        'zip_code',
        'notes',
        'source',
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

    /** Tipos de pessoa: física (PF) e jurídica (PJ). */
    public const PERSON_TYPES = [
        'individual' => 'Pessoa Física',
        'company' => 'Pessoa Jurídica',
    ];

    /** Origens de lead (crm-master: "Como chegou até nós"). */
    public const SOURCES = [
        'indicacao' => 'Indicação',
        'site' => 'Site / Formulário',
        'google' => 'Google',
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'whatsapp' => 'WhatsApp',
        'linkedin' => 'LinkedIn',
        'evento' => 'Evento',
        'prospeccao' => 'Prospecção ativa',
        'outro' => 'Outro',
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

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->latest('happened_at');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class)->latest();
    }

    public function financialEntries(): HasMany
    {
        return $this->hasMany(FinancialEntry::class);
    }

    public function sourceLabel(): ?string
    {
        return $this->source ? (self::SOURCES[$this->source] ?? $this->source) : null;
    }

    public function personTypeLabel(): string
    {
        return self::PERSON_TYPES[$this->person_type] ?? (string) $this->person_type;
    }

    /** Aplica máscara de CPF/CNPJ conforme o tipo; retorna o documento cru se o tamanho não casar. */
    public function formattedDocument(): ?string
    {
        if ($this->document === null || $this->document === '') {
            return $this->document;
        }

        $digits = preg_replace('/\D/', '', $this->document) ?? '';

        if ($this->person_type === 'company' && strlen($digits) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
        }

        if ($this->person_type !== 'company' && strlen($digits) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
        }

        return $this->document;
    }
}
