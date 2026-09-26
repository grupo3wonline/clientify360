<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToUser;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadFormSubmission extends Model
{
    use BelongsToUser, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'lead_form_id', 'user_id', 'client_id', 'deal_id', 'data', 'ip', 'user_agent', 'referrer',
        'utm_source', 'utm_medium', 'utm_campaign', 'note',
    ];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(LeadForm::class, 'lead_form_id')->withTrashed();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class)->withTrashed();
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
