<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Client;
use App\Rules\CpfCnpj;
use App\Support\OwnedRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Not final: the API v1 request extends it to reuse rules() and messages(). */
class UpdateClientRequest extends FormRequest
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
        // Web routes pass the id; API routes bind the model. Resolving it here (account
        // scope) answers 404 for another account's id before validation can answer 422.
        $route = $this->route('client');
        $client = $route instanceof Client ? $route : Client::find($route);
        abort_if($client === null, 404);

        if ($this->has('_tags') && !$this->has('tag_ids')) {
            $this->merge(['tag_ids' => []]);
        }

        // person_type é NOT NULL: quando ausente, mantém o valor atual do cliente,
        // para não quebrar atualizações parciais.
        if (!$this->filled('person_type')) {
            $this->merge(['person_type' => $client->person_type ?? 'individual']);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'person_type' => ['required', 'string', Rule::in(array_keys(Client::PERSON_TYPES))],
            'document' => ['nullable', 'string', 'max:20', new CpfCnpj($this->input('person_type'))],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:2'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'source' => ['nullable', 'string', Rule::in(array_keys(Client::SOURCES))],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['string', OwnedRule::exists('tags', false)],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'score_label' => ['nullable', 'string', 'max:20'],
            'strengths' => ['nullable', 'string', 'max:2000'],
            'weaknesses' => ['nullable', 'string', 'max:2000'],
            'opportunities' => ['nullable', 'string', 'max:2000'],
            'needs_website' => ['nullable', 'boolean'],
            'needs_system' => ['nullable', 'boolean'],
            'needs_marketing' => ['nullable', 'boolean'],
            'needs_ecommerce' => ['nullable', 'boolean'],
            'needs_seo' => ['nullable', 'boolean'],
            'needs_social_media' => ['nullable', 'boolean'],
            'business_segment' => ['nullable', 'string', 'max:100'],
            'company_size' => ['nullable', 'string', 'max:30'],
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
            'person_type.required' => 'O tipo de pessoa é obrigatório.',
            'person_type.in' => 'Selecione um tipo de pessoa válido.',
            'state.max' => 'O estado deve ter no máximo 2 caracteres.',
            'zip_code.max' => 'O CEP não pode ter mais de 10 caracteres.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];
    }
}
