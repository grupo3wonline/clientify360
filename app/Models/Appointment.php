<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Agendamento da Agenda/Calendário.
 *
 * Duração: `starts_at` (obrigatório) + `ends_at` (opcional). A duração em
 * minutos é derivável na view via `starts_at->diffInMinutes($ends_at)`.
 * Recorrência não é suportada (fora de escopo; melhoria futura).
 *
 * @property Carbon $starts_at
 * @property Carbon|null $ends_at
 */
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use BelongsToAccount, HasFactory, HasUuid, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_DONE = 'done';

    public const STATUS_CANCELLED = 'cancelled';

    /** Tipos de agendamento (rótulos pt-BR). */
    public const TYPES = [
        'meeting' => 'Reunião',
        'visit' => 'Visita',
        'call' => 'Ligação',
        'video' => 'Videochamada',
    ];

    /** Situações do agendamento (rótulos pt-BR). */
    public const STATUSES = [
        self::STATUS_PENDING => 'Pendente',
        self::STATUS_CONFIRMED => 'Confirmado',
        self::STATUS_DONE => 'Concluído',
        self::STATUS_CANCELLED => 'Cancelado',
    ];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'user_id',
        'client_id',
        'deal_id',
        'reminder_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'type',
        'status',
        'location',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
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
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function confirm(): void
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);
    }

    public function markDone(): void
    {
        $this->update(['status' => self::STATUS_DONE]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }
}
