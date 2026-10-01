<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * `exists` restrito aos registros do usuário logado.
 * Ex.: OwnedRule::exists('clients') — rejeita o ID de um cliente de outra conta,
 * coisa que um simples 'exists:clients,id' deixaria passar.
 */
final class OwnedRule
{
    public static function exists(string $table, bool $softDeletes = true): Exists
    {
        $rule = Rule::exists($table, 'id')->where('user_id', auth()->id());

        return $softDeletes ? $rule->whereNull('deleted_at') : $rule;
    }
}
