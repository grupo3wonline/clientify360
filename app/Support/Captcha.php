<?php

declare(strict_types=1);

namespace App\Support;

use App\Rules\Recaptcha;
use App\Rules\Turnstile;

/**
 * Single place that lists the anti-bot checks of a public form. Each check
 * disables itself when its secret key is empty, so the set can be extended
 * without touching the controllers.
 */
final class Captcha
{
    /**
     * Validation rules for the two token fields, to be spread into a rules array.
     *
     * @return array<string, list<Recaptcha|Turnstile>>
     */
    public static function rules(string $action): array
    {
        return [
            'g-recaptcha-response' => [new Recaptcha($action)],
            'cf-turnstile-response' => [new Turnstile($action)],
        ];
    }
}
