<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\RecaptchaService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule for the reCAPTCHA v3 token (field: g-recaptcha-response).
 *
 * When the captcha is disabled (no secret key configured) the underlying
 * service returns true, so the rule passes silently and existing flows/tests
 * keep working without a token.
 */
final class Recaptcha implements ValidationRule
{
    /**
     * Run even when the field is missing or empty. Without this Laravel skips the rule for
     * an absent field, so a bot could bypass the captcha by simply omitting the token.
     */
    public bool $implicit = true;

    private readonly RecaptchaService $service;

    public function __construct(
        private readonly string $action,
        ?RecaptchaService $service = null,
    ) {
        $this->service = $service ?? app(RecaptchaService::class);
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
