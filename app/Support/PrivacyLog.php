<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * LGPD audit trail for access to personal data (downloads, exports, PDFs),
 * written to the activity log under the "privacy" log name.
 */
final class PrivacyLog
{
    /** @param  array<string, mixed>  $properties */
    public static function record(string $event, string $description, ?User $causer, ?Model $subject = null, array $properties = []): void
    {
        $log = activity('privacy')->event($event)->withProperties($properties);

        if ($causer !== null) {
            $log->causedBy($causer);
        }

        if ($subject !== null) {
            $log->performedOn($subject);
        }

        $log->log($description);
    }
}
