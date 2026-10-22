<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Appointment;
use App\Support\OwnedRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validação server-side para criação de agendamentos. */
class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // A autorização é feita no controller via policy (authorizeResource).
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'type' => ['required', Rule::in(array_keys(Appointment::TYPES))],
            'status' => ['nullable', Rule::in(array_keys(Appointment::STATUSES))],
            'client_id' => ['nullable', 'string', OwnedRule::exists('clients')],
            'deal_id' => ['nullable', 'string', OwnedRule::exists('deals')],
            'location' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
            'generate_reminder' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Informe o título do agendamento.',
            'starts_at.required' => 'Informe a data e hora de início.',
            'starts_at.date' => 'A data de início é inválida.',
            'ends_at.after_or_equal' => 'A data de término deve ser igual ou posterior ao início.',
            'type.required' => 'Selecione o tipo do agendamento.',
            'type.in' => 'Tipo de agendamento inválido.',
            'status.in' => 'Situação inválida.',
            'client_id.exists' => 'Cliente inválido.',
            'deal_id.exists' => 'Oportunidade inválida.',
        ];
    }
}
