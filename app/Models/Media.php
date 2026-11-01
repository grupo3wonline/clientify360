<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

/**
 * Media row with a UUID primary key.
 *
 * Uses Laravel's HasUuids instead of App\Traits\HasUuid: the vendor base class
 * already uses a trait named HasUuid (public bootHasUuid fills the `uuid`
 * column), and the project trait would clash with it. No SoftDeletes: the file
 * lifecycle follows the parent model and a hard delete removes the file.
 */
class Media extends BaseMedia
{
    use HasUuids;
}
