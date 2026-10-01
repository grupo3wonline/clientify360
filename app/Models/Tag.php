<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToAccount;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use BelongsToAccount, HasUuid;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['account_id', 'user_id', 'name', 'color'];

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class);
    }
}
