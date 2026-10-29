<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\CookieConsent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCookieConsentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'analytics' => ['required', 'boolean'],
            'marketing' => ['required', 'boolean'],
            'source' => ['required', 'string', Rule::in(CookieConsent::SOURCES)],
            'consent_id' => ['nullable', 'uuid'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'analytics.required' => 'Informe a preferência de cookies de análise.',
            'analytics.boolean' => 'Preferência de cookies de análise inválida.',
            'marketing.required' => 'Informe a preferência de cookies de marketing.',
            'marketing.boolean' => 'Preferência de cookies de marketing inválida.',
            'source.required' => 'Origem do consentimento não informada.',
            'source.in' => 'Origem do consentimento inválida.',
            'consent_id.uuid' => 'Identificador de consentimento inválido.',
        ];
    }
}
