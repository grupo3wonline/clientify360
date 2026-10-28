<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Laravel\Scout\Builder;

/**
 * Single entry point for Scout (database engine) searches. Always filters by
 * the user's account explicitly, so results stay isolated even without auth
 * (queue/console).
 */
final class SearchService
{
    public const MAX_TERM_LENGTH = 100;

    /** @return LengthAwarePaginator<int, Client> */
    public function clients(User $user, string $term, int $perPage = 15): LengthAwarePaginator
    {
        $term = $this->normalize($term);

        if ($term === '' || $user->account_id === null) {
            return $this->empty($perPage);
        }

        return $this->scoped(Client::search($term), $user)
            ->query(fn ($query) => $query->with('tags'))
            ->paginate($perPage);
    }

    /** @return LengthAwarePaginator<int, Product> */
    public function products(User $user, string $term, int $perPage = 15): LengthAwarePaginator
    {
        $term = $this->normalize($term);

        if ($term === '' || $user->account_id === null) {
            return $this->empty($perPage);
        }

        return $this->scoped(Product::search($term), $user)->paginate($perPage);
    }

    public function normalize(string $term): string
    {
        return mb_substr(trim($term), 0, self::MAX_TERM_LENGTH);
    }

    private function scoped(Builder $builder, User $user): Builder
    {
        return $builder->where('account_id', $user->account_id);
    }

    /** @return LengthAwarePaginator<int, never> */
    private function empty(int $perPage): LengthAwarePaginator
    {
        return new Paginator([], 0, $perPage);
    }
}
