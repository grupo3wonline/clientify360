<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class DismissedNotification extends Model
{
    use BelongsToAccount, HasUuid;

    public $timestamps = true;

    protected $fillable = [
        'account_id',
        'user_id',
        'notification_key',
        'dismissed_at',
    ];

    protected function casts(): array
    {
        return [
            'dismissed_at' => 'datetime',
        ];
    }
}
