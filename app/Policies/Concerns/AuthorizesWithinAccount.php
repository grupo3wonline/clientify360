<?php

declare(strict_types=1);

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Base de autorização por CONTA + permissão (ACL por equipe). As policies
 * concretas definem apenas o prefixo de permissão (ex.: 'deals').
 */
trait AuthorizesWithinAccount
{
    /** Prefixo da permissão do recurso, ex.: 'clients', 'deals'. */
    abstract protected function resource(): string;

    public function viewAny(User $user): bool
    {
        return $user->can($this->resource().'.view');
    }

    public function view(User $user, Model $model): bool
    {
        return $this->sameAccount($user, $model) && $user->can($this->resource().'.view');
    }

    public function create(User $user): bool
    {
        return $user->can($this->resource().'.manage');
    }

    public function update(User $user, Model $model): bool
    {
        return $this->sameAccount($user, $model) && $user->can($this->resource().'.manage');
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->update($user, $model);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->update($user, $model);
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->update($user, $model);
    }

    protected function sameAccount(User $user, Model $model): bool
    {
        return $user->account_id !== null
            && $user->account_id === $model->getAttribute('account_id');
    }
}
