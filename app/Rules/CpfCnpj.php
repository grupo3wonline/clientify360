<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida CPF (Pessoa Física) ou CNPJ (Pessoa Jurídica) conforme o tipo de pessoa.
 * Aceita o documento com ou sem máscara (normaliza para dígitos) e deixa o
 * campo vazio passar — a regra `nullable` cuida da obrigatoriedade.
 */
final class CpfCnpj implements ValidationRule
{
    public function __construct(
        private readonly ?string $personType = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $digits = preg_replace('/\D/', '', (string) $value) ?? '';

        if ($this->personType === 'company') {
            if (!$this->isValidCnpj($digits)) {
                $fail('Informe um CNPJ válido.');
            }

            return;
        }

        if (!$this->isValidCpf($digits)) {
            $fail('Informe um CPF válido.');
        }
    }

    private function isValidCpf(string $digits): bool
    {
        if (strlen($digits) !== 11 || preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $digits[$i] * (($t + 1) - $i);
            }

            $check = ((10 * $sum) % 11) % 10;

            if ((int) $digits[$t] !== $check) {
                return false;
            }
        }

        return true;
    }

    private function isValidCnpj(string $digits): bool
    {
        if (strlen($digits) !== 14 || preg_match('/^(\d)\1{13}$/', $digits) === 1) {
            return false;
        }

        $weightsFirst = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weightsSecond = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        if ((int) $digits[12] !== $this->cnpjCheckDigit($digits, $weightsFirst)) {
            return false;
        }

        return (int) $digits[13] === $this->cnpjCheckDigit($digits, $weightsSecond);
    }

    /**
     * @param  array<int, int>  $weights
     */
    private function cnpjCheckDigit(string $digits, array $weights): int
    {
        $sum = 0;

        foreach ($weights as $i => $weight) {
            $sum += (int) $digits[$i] * $weight;
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
