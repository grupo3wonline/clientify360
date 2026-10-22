<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\OwnedRule;
use Illuminate\Foundation\Http\FormRequest;

/** Validação server-side para criação de compras. */
class StorePurchaseRequest extends FormRequest
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
            'supplier' => ['nullable', 'string', 'max:255'],
            'document_number' => ['nullable', 'string', 'max:60'],
            'purchased_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['array', 'min:1'],
            'items.*.product_id' => ['required', 'string', OwnedRule::exists('products')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'purchased_at.required' => 'Informe a data da compra.',
            'purchased_at.date' => 'A data da compra é inválida.',
            'items.array' => 'Os itens da compra são inválidos.',
            'items.min' => 'Adicione ao menos um item.',
            'items.*.product_id.required' => 'Selecione o produto do item.',
            'items.*.product_id.exists' => 'Produto inválido.',
            'items.*.quantity.required' => 'Informe a quantidade do item.',
            'items.*.quantity.numeric' => 'A quantidade deve ser numérica.',
            'items.*.quantity.gt' => 'A quantidade deve ser maior que zero.',
            'items.*.unit_cost.required' => 'Informe o custo unitário do item.',
            'items.*.unit_cost.numeric' => 'O custo unitário deve ser numérico.',
            'items.*.unit_cost.min' => 'O custo unitário não pode ser negativo.',
        ];
    }
}
