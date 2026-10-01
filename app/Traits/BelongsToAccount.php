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
            $accountId = auth()->user()?->account_id;

            if ($accountId !== null) {
                $query->where($query->getModel()->getTable().'.account_id', $accountId);
            }
        });

        static::creating(function ($model): void {
            $user = auth()->user();

            if ($user === null) {
                return;
            }

            if (empty($model->account_id)) {
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
