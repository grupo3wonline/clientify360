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

    public function paginate(int $perPage = 15, ?string $search = null, ?string $tagId = null): LengthAwarePaginator
    {
        $query = Client::with('tags')->orderBy('name');

        if ($tagId !== null && $tagId !== '') {
            $query->whereHas('tags', fn ($q) => $q->where('tags.id', $tagId));
        }

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
        $client = Client::create($dto->toArray());
        $this->syncTags($client, $dto);

        return $client;
    }

    public function update(string $id, ClientDTO $dto): Client
    {
        $client = Client::findOrFail($id);
        $client->update($dto->toArray());
        $this->syncTags($client, $dto);

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

    /** Só vincula tags do próprio usuário (Tag tem escopo global por user_id). */
    private function syncTags(Client $client, ClientDTO $dto): void
    {
        if ($dto->tagIds === null) {
            return;
        }

        $allowed = \App\Models\Tag::whereIn('id', $dto->tagIds)->pluck('id')->all();
        $client->tags()->sync($allowed);
    }
}
