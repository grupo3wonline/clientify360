<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\DTOs\ClientDTO;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClientRepositoryInterface
{
    public function paginate(int $perPage = 15, ?string $search = null, ?string $tagId = null): LengthAwarePaginator;

    public function findById(string $id): ?Client;

    public function create(ClientDTO $dto): Client;

    public function update(string $id, ClientDTO $dto): Client;

    public function delete(string $id): bool;

    public function countAll(): int;
}
