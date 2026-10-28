<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Isolamento de dados por CONTA (tenant). Adiciona um global scope que filtra
 * os registros pela conta do usuário autenticado, e preenche automaticamente
 * `account_id` (a conta) e `user_id` (o colaborador que criou) ao criar.
 *
 * O scope mantém o nome 'owned' por compatibilidade com chamadas existentes
 * de `withoutGlobalScope('owned')`.
 *
 * @property string|null $account_id
 * @property string|null $user_id
 * @property-read Account|null $account
 * @property-read User|null $user
 */
trait BelongsToAccount
{
    protected static function bootBelongsToAccount(): void
    {
        static::addGlobalScope('owned', function (Builder $query): void {
            $user = auth()->user();

            if ($user === null) {
                return; // guest/console/queue: callers filter explicitly
            }

            $table = $query->getModel()->getTable();

            // Fail closed: an authenticated user without an account sees nothing.
            $user->account_id !== null
                ? $query->where($table.'.account_id', $user->account_id)
                : $query->whereRaw('1 = 0');
        });

        static::creating(function ($model): void {
            $user = auth()->user();

            if ($user === null) {
                return; // guest/console/queue: the caller sets account_id from trusted data
            }

            // Model-owned invariant: a record is always created in the actor's account,
            // whatever account_id came from the input. A user without an account keeps
            // an explicit value (it is never overwritten with null).
            if ($user->account_id !== null) {
                $model->account_id = $user->account_id;
            }

            // Mantém o rastro de qual colaborador criou o registro (auditoria),
            // quando a tabela tiver a coluna user_id.
            if (empty($model->user_id) && in_array('user_id', $model->getFillable(), true)) {
                $model->user_id = $user->id;
            }
        });
    }

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
