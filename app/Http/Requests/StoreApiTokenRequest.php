<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ApiTokenService;
use App\Support\ApiAbilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreApiTokenRequest extends FormRequest
{
    /** @var string */
    protected $errorBag = 'apiToken';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(ApiAbilities::ALL)],
            'expires_in_days' => ['required', Rule::in(array_map('strval', ApiTokenService::EXPIRATION_DAYS))],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'name.required' => 'Dê um nome ao token (ex.: Integração ERP).',
            'name.max' => 'O nome deve ter no máximo 60 caracteres.',
            'abilities.required' => 'Selecione pelo menos uma permissão.',
            'abilities.min' => 'Selecione pelo menos uma permissão.',
            'abilities.*.in' => 'Permissão inválida.',
            'expires_in_days.required' => 'Escolha a validade do token.',
            'expires_in_days.in' => 'Escolha uma validade de 30, 90 ou 365 dias.',
        ];
    }
}
