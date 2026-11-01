<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAccountLogoRequest extends FormRequest
{
    /** @var string */
    protected $errorBag = 'accountLogo';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:max_width=2000,max_height=2000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $message = 'Envie uma imagem PNG, JPG ou WEBP de até 2 MB.';

        return [
            'logo.required' => $message,
            'logo.uploaded' => $message,
            'logo.image' => $message,
            'logo.mimes' => $message,
            'logo.max' => $message,
            'logo.dimensions' => 'A imagem deve ter no máximo 2000 × 2000 pixels.',
        ];
    }
}
