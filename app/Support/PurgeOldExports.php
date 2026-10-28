<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/** Daily cleanup: deletes export files older than two days from the private disk. */
final class PurgeOldExports
{
    public const MAX_AGE_SECONDS = 2 * 24 * 3600;

    public function __invoke(): int
    {
        $disk = Storage::disk('local');
        $limit = now()->getTimestamp() - self::MAX_AGE_SECONDS;
        $deleted = 0;

        foreach ($disk->allFiles('exports') as $file) {
            if ($disk->lastModified($file) < $limit && $disk->delete($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
