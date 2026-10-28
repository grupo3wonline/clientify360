<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Rules\Recaptcha;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 5;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            // Honeypot: only bots fill it. A present/non-empty value fails.
            'website' => ['nullable', 'prohibited'],
            // reCAPTCHA v3 token (passes silently when captcha is disabled).
            'g-recaptcha-response' => [new Recaptcha('login')],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Informe seu nome de usuário.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Informe um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'website.prohibited' => 'Falha na verificação de segurança. Recarregue a página e tente novamente.',
        ];
    }

    /**
     * Authenticate with username + e-mail + password together. The Eloquent
     * provider ANDs the non-password keys, so both must belong to the same user.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $credentials = [
            'username' => $this->username(),
            'email' => $this->email(),
            'password' => (string) $this->input('password'),
        ];

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /** 5 attempts per credential tuple (username|email) and IP. */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username().'|'.$this->email()).'|'.$this->ip());
    }

    private function username(): string
    {
        return (string) $this->string('username')->trim();
    }

    private function email(): string
    {
        return Str::lower((string) $this->string('email')->trim());
    }
}
