<?php

declare(strict_types=1);

namespace App\Repositories;

use App\DTOs\ClientDTO;
use App\Interfaces\ClientRepositoryInterface;
use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final class ClientRepository implements ClientRepositoryInterface
{
    public function all(): Collection
    {
        return Client::orderBy('name')->get();
    }

    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $query = Client::orderBy('name');

        if ($search !== null && $search !== '') {
            $escapedSearch = str_replace(['%', '_'], ['\%', '\_'], $search);
            $query->where(function ($q) use ($escapedSearch): void {
                $q->where('name', 'like', "%{$escapedSearch}%")
                  ->orWhere('email', 'like', "%{$escapedSearch}%")
                  ->orWhere('phone', 'like', "%{$escapedSearch}%")
                  ->orWhere('document', 'like', "%{$escapedSearch}%")
                  ->orWhere('city', 'like', "%{$escapedSearch}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function findById(string $id): ?Client
    {
        return Client::find($id);
    }

    public function create(ClientDTO $dto): Client
    {
        return Client::create($dto->toArray());
    }

    public function update(string $id, ClientDTO $dto): Client
    {
        $client = Client::findOrFail($id);
        $client->update($dto->toArray());

        return $client->fresh();
    }

    public function delete(string $id): bool
    {
        $client = Client::findOrFail($id);

        return (bool) $client->delete();
    }

    public function countAll(): int
    {
        return Client::count();
    }
}
