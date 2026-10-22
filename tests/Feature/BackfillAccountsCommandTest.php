<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Valida o comando accounts:backfill, que reprovisiona usuários órfãos
 * (sem account_id) que de outra forma receberiam 403 no painel.
 */
class BackfillAccountsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_provisions_accounts_for_orphan_users(): void
    {
        $orphans = User::factory()->withoutAccount()->count(3)->create();

        $orphans->each(fn (User $user) => $this->assertNull($user->account_id));

        $this->artisan('accounts:backfill')
            ->expectsOutputToContain('Usuários sem conta: 3')
            ->assertSuccessful();

        $orphans->each(function (User $user): void {
            $user->refresh();
            $this->assertNotNull($user->account_id);
            $this->assertSame($user->id, $user->account->owner_id);
        });
    }

    public function test_dry_run_does_not_create_accounts(): void
    {
        $orphan = User::factory()->withoutAccount()->create();

        $this->artisan('accounts:backfill', ['--dry-run' => true])
            ->expectsOutputToContain($orphan->email)
            ->assertSuccessful();

        $orphan->refresh();
        $this->assertNull($orphan->account_id, 'Dry-run não deve provisionar contas.');
    }

    public function test_it_reports_nothing_to_do_when_no_orphans(): void
    {
        // Usuário já provisionado pela factory.
        User::factory()->create();

        $this->artisan('accounts:backfill')
            ->expectsOutputToContain('Nenhum usuário órfão encontrado')
            ->assertSuccessful();
    }
}
