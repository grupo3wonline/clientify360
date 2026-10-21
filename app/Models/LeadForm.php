<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class LeadForm extends Model
{
    use BelongsToAccount, HasUuid, SoftDeletes;

    /** Campos que o serviço de captação sabe mapear para o cadastro do cliente. */
    public const MAPPABLE_FIELDS = [
        'name' => 'Nome',
        'email' => 'E-mail',
        'phone' => 'Telefone / WhatsApp',
        'company' => 'Empresa',
        'city' => 'Cidade',
        'message' => 'Mensagem',
    ];

    public const FIELD_TYPES = ['text' => 'Texto', 'email' => 'E-mail', 'tel' => 'Telefone', 'textarea' => 'Texto longo'];

    /**
     * Curated button colors drawn from the design tokens. Every value is dark enough that white
     * text clears WCAG AA (>= 4.5:1), so no user pick can produce an unreadable button.
     * Single source of truth: the builder UI and server validation both read from here.
     */
    public const BRAND_PALETTE = [
        '#4f46e5' => 'Índigo',
        '#4338ca' => 'Índigo escuro',
        '#2563eb' => 'Azul',
        '#059669' => 'Verde',
        '#047857' => 'Verde escuro',
        '#dc2626' => 'Vermelho',
        '#b45309' => 'Âmbar',
        '#334155' => 'Grafite',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id', 'user_id', 'name', 'slug', 'title', 'description', 'color', 'button_text', 'success_message',
        'redirect_url', 'fields', 'default_source', 'create_deal', 'pipeline_stage_id', 'is_active',
        'submissions_count',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'create_deal' => 'boolean',
            'is_active' => 'boolean',
            'submissions_count' => 'integer',
        ];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LeadFormSubmission::class)->latest();
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function publicUrl(): string
    {
        return route('lead-forms.public', $this->slug);
    }

    /** Slug curto e não-sequencial: evita que alguém "adivinhe" formulários de outros clientes. */
    public static function generateSlug(string $name): string
    {
        do {
            $slug = Str::limit(Str::slug($name), 60, '').'-'.Str::lower(Str::random(6));
        } while (static::withoutGlobalScope('owned')->withTrashed()->where('slug', $slug)->exists());

        return $slug;
    }
}
