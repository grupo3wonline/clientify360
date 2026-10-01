<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Histórico de relacionamento (ligação, reunião, e-mail...).
 * O "próximo passo" (conceito do crm-master) vira um Reminder na Agenda,
 * reaproveitando as notificações de lembrete que já existem.
 */
class Activity extends Model
{
    use BelongsToAccount, HasUuid, SoftDeletes;

    public const TYPES = [
        'note' => ['label' => 'Nota', 'icon' => 'fa-solid fa-note-sticky'],
        'call' => ['label' => 'Ligação', 'icon' => 'fa-solid fa-phone'],
        'whatsapp' => ['label' => 'WhatsApp', 'icon' => 'fa-brands fa-whatsapp'],
        'email' => ['label' => 'E-mail', 'icon' => 'fa-solid fa-envelope'],
        'meeting' => ['label' => 'Reunião', 'icon' => 'fa-solid fa-people-group'],
        'visit' => ['label' => 'Visita', 'icon' => 'fa-solid fa-location-dot'],
        'proposal' => ['label' => 'Proposta', 'icon' => 'fa-solid fa-file-signature'],
        'other' => ['label' => 'Outro', 'icon' => 'fa-solid fa-circle-dot'],
    ];

    public const OUTCOMES = [
        'positive' => 'Positivo',
        'neutral' => 'Neutro',
        'negative' => 'Negativo',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id', 'user_id', 'client_id', 'deal_id', 'reminder_id', 'type', 'title', 'description',
        'happened_at', 'outcome', 'next_step', 'next_step_at',
    ];

    protected function casts(): array
    {
        return [
            'happened_at' => 'datetime',
            'next_step_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst($this->type);
    }

    public function typeIcon(): string
    {
        return self::TYPES[$this->type]['icon'] ?? 'fa-solid fa-circle-dot';
    }
}
