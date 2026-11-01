<?php

declare(strict_types=1);

namespace App\Support;

/** Sanctum token abilities of the API v1, with their pt-BR labels. */
final class ApiAbilities
{
    public const CLIENTS_READ = 'clients:read';

    public const CLIENTS_WRITE = 'clients:write';

    public const PRODUCTS_READ = 'products:read';

    public const QUOTES_READ = 'quotes:read';

    public const ALL = [
        self::CLIENTS_READ,
        self::CLIENTS_WRITE,
        self::PRODUCTS_READ,
        self::QUOTES_READ,
    ];

    public const LABELS = [
        self::CLIENTS_READ => 'Ler clientes',
        self::CLIENTS_WRITE => 'Criar, editar e excluir clientes',
        self::PRODUCTS_READ => 'Ler produtos',
        self::QUOTES_READ => 'Ler orçamentos',
    ];

    public static function label(string $ability): string
    {
        return self::LABELS[$ability] ?? $ability;
    }
}
