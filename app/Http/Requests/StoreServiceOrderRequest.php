<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ServiceOrder;
use App\Support\OwnedRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validação server-side para criação de ordens de serviço. */
class StoreServiceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        // O isolamento por conta vem do global scope 'owned'; os IDs
        // referenciados são validados via OwnedRule (escopo por conta).
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'string', OwnedRule::exists('clients')],
            'quote_id' => ['nullable', 'string', OwnedRule::exists('quotes')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'total' => ['required', 'numeric', 'min:0'],
            'opened_at' => ['required', 'date'],
            'expected_at' => ['nullable', 'date', 'after_or_equal:opened_at'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:opened_at'],
            'status' => ['nullable', Rule::in(array_keys(ServiceOrder::STATUSES))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'Selecione o cliente.',
            'client_id.exists' => 'Cliente inválido.',
            'quote_id.exists' => 'Orçamento inválido.',
            'title.required' => 'Informe o título da ordem de serviço.',
            'total.required' => 'Informe o valor total.',
            'total.numeric' => 'O valor total deve ser numérico.',
            'total.min' => 'O valor total não pode ser negativo.',
            'opened_at.required' => 'Informe a data de abertura.',
            'opened_at.date' => 'A data de abertura é inválida.',
            'expected_at.after_or_equal' => 'A previsão deve ser igual ou posterior à abertura.',
            'completed_at.after_or_equal' => 'A conclusão deve ser igual ou posterior à abertura.',
            'status.in' => 'Situação inválida.',
        ];
    }
}
