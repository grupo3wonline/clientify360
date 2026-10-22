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

    /**
     * Readable foreground color (black or white) for text placed over the tag
     * background, chosen from the WCAG relative luminance of the tag color.
     */
    public function textColor(): string
    {
        $luminance = self::relativeLuminance((string) $this->color);

        if ($luminance === null) {
            return '#ffffff';
        }

        return $luminance > 0.179 ? '#111827' : '#ffffff';
    }

    /**
     * WCAG relative luminance (0..1) of a hex color, or null when the value is
     * not a valid 3/6-digit hex color. Used by textColor() to pick a readable
     * foreground (black or white) over the tag background.
     */
    public static function relativeLuminance(string $color): ?float
    {
        $hex = ltrim($color, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return null;
        }

        $channels = array_map(
            static function (int $value): float {
                $channel = $value / 255;

                return $channel <= 0.03928
                    ? $channel / 12.92
                    : (($channel + 0.055) / 1.055) ** 2.4;
            },
            [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
            ],
        );

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
