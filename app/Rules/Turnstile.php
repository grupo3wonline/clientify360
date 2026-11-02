<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\TurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule for the Cloudflare Turnstile token (field: cf-turnstile-response).
 *
 * Implicit on purpose: Laravel skips non-implicit rules when the field is absent, which
 * would let a bot bypass the captcha by simply omitting the field. When the captcha is
 * disabled (no secret key) the service returns true and the rule passes silently.
 */
final class Turnstile implements ValidationRule
{
    /** Run even when the field is missing or empty. */
    public bool $implicit = true;

    private readonly TurnstileService $service;

    public function __construct(
        private readonly string $action,
        ?TurnstileService $service = null,
    ) {
        $this->service = $service ?? app(TurnstileService::class);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $token = is_string($value) ? $value : null;

        if ($this->service->verify($token, $this->action, request()->ip())) {
            return;
        }

        $fail('Falha na verificação de segurança. Recarregue a página e tente novamente.');
    }
}
