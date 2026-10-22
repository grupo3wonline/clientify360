<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ClientDTO;
use App\Interfaces\ClientRepositoryInterface;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class ClientService
{
    public function __construct(
        private readonly ClientRepositoryInterface $repository
    ) {}

    public function findAll(): Collection
    {
        return $this->repository->all();
    }

    public function paginate(int $perPage = 15, ?string $search = null, ?string $tagId = null): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $search, $tagId);
    }

    public function findById(string $id): ?Client
    {
        return $this->repository->findById($id);
    }

    public function create(ClientDTO $dto): Client
    {
        return $this->repository->create($dto);
    }

    public function update(string $id, ClientDTO $dto): Client
    {
        return $this->repository->update($id, $dto);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function countAll(): int
    {
        return $this->repository->countAll();
    }
}
