<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Checkboxes desmarcados não são enviados pelo navegador. O formulário manda
     * `_tags=1` para indicar que o seletor de tags estava na tela: sem nenhuma
     * marcada, tratamos como "remover todas" em vez de "não alterar".
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('_tags') && !$this->has('tag_ids')) {
            $this->merge(['tag_ids' => []]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'document' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'source' => ['nullable', 'string', \Illuminate\Validation\Rule::in(array_keys(\App\Models\Client::SOURCES))],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['string', \Illuminate\Validation\Rule::exists('tags', 'id')->where('user_id', $this->user()?->id)],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'score_label' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.email' => 'Informe um e-mail válido.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'document.max' => 'O documento não pode ter mais de 20 caracteres.',
            'state.max' => 'O estado deve ter no máximo 2 caracteres.',
            'zip_code.max' => 'O CEP não pode ter mais de 10 caracteres.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }
}
