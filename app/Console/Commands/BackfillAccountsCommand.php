<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AccountService;
use Illuminate\Console\Command;

/**
 * Reprovisiona usuários órfãos (sem account_id), que de outra forma
 * receberiam 403 ao acessar o painel Filament por não terem conta.
 */
final class BackfillAccountsCommand extends Command
{
    protected $signature = 'accounts:backfill {--dry-run : Apenas lista os usuários órfãos, sem provisionar}';

    protected $description = 'Provisiona uma conta para usuários que estão sem account_id';

    public function __construct(
        private readonly AccountService $accounts
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $orphans = User::query()->whereNull('account_id')->get();

        if ($orphans->isEmpty()) {
            $this->info('Nenhum usuário órfão encontrado. Nada a fazer.');

            return self::SUCCESS;
        }

        $this->warn("Usuários sem conta: {$orphans->count()}");

        if ($this->option('dry-run')) {
            $orphans->each(fn (User $user) => $this->line("  - {$user->email}"));
            $this->info('Execução em dry-run: nenhuma conta foi criada.');

            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($orphans as $user) {
            try {
                $account = $this->accounts->provisionForOwner($user);
                $this->line("  OK  {$user->email} -> conta {$account->id}");
            } catch (\Throwable $e) {
                $failures++;
                $this->error("  ERR {$user->email} -> {$e->getMessage()}");
            }
        }

        if ($failures > 0) {
            $this->error("Concluído com {$failures} falha(s).");

            return self::FAILURE;
        }

        $this->info('Todos os usuários órfãos foram provisionados.');

        return self::SUCCESS;
    }
}
