<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * `exists` restrito aos registros da CONTA do usuário logado.
 * Ex.: OwnedRule::exists('clients') — rejeita o ID de um cliente de outra conta,
 * coisa que um simples 'exists:clients,id' deixaria passar.
 *
 * O filtro é por `account_id` (mesma chave de isolamento do global scope do
 * trait BelongsToAccount), e não por `user_id`: assim colaboradores da mesma
 * conta referenciam os dados uns dos outros e registros padrão com `user_id`
 * nulo (ex.: etapas do funil) continuam válidos. Sem usuário logado o filtro
 * renderiza com "NULL" (comportamento consistente com o formulário público).
 */
final class OwnedRule
{
    public static function exists(string $table, bool $softDeletes = true): Exists
    {
        $rule = Rule::exists($table, 'id')->where('account_id', auth()->user()?->account_id);

        return $softDeletes ? $rule->whereNull('deleted_at') : $rule;
    }
}
