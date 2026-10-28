<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

/** Validated listing parameters of an API v1 index endpoint. */
final class ListQueryData extends Data
{
    public const DEFAULT_PER_PAGE = 15;

    public function __construct(
        public readonly int $perPage = self::DEFAULT_PER_PAGE,
        public readonly ?string $search = null,
    ) {}

    /** @param  array<string, mixed>  $validated */
    public static function fromValidated(array $validated): self
    {
        $search = isset($validated['search']) ? trim((string) $validated['search']) : null;

        return new self(
            perPage: (int) ($validated['per_page'] ?? self::DEFAULT_PER_PAGE),
            search: $search === '' ? null : $search,
        );
    }
}
