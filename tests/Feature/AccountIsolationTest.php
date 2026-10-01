<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Valida o isolamento por CONTA (tenant): colaboradores da mesma conta
 * enxergam os mesmos dados; contas diferentes não se enxergam.
 */
class AccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_collaborator_in_same_account_sees_account_data(): void
    {
        $owner = User::factory()->create();
        $account = $owner->account;

        // Dono cadastra um cliente.
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente da Conta', 'email' => 'conta@ex.com']);
        $this->assertSame($account->id, $client->account_id);

        // Colaborador adicionado à mesma conta.
        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $account, User::ROLE_COLLABORATOR);

        // Colaborador enxerga o cliente da conta.
        $this->actingAs($collaborator->fresh());
        $this->assertSame(1, Client::count());
        $this->assertTrue(Client::whereKey($client->id)->exists());
    }

    public function test_other_account_cannot_see_data(): void
    {
        $alice = User::factory()->create();
        $this->actingAs($alice);
        $aliceClient = Client::create(['name' => 'Cliente Alice', 'email' => 'alice@ex.com']);

        $bob = User::factory()->create();
        $this->actingAs($bob);

        $this->assertSame(0, Client::count());
        $this->assertFalse(Client::whereKey($aliceClient->id)->exists());
    }

    public function test_owner_has_owner_role_and_permissions(): void
    {
        $owner = User::factory()->create();
        app(PermissionRegistrar::class)->setPermissionsTeamId($owner->account_id);

        $owner = $owner->fresh();
        $this->assertTrue($owner->hasRole(User::ROLE_OWNER));
        $this->assertTrue($owner->can('account.billing'));
        $this->assertTrue($owner->can('clients.manage'));
    }

    public function test_collaborator_lacks_billing_permission(): void
    {
        $owner = User::factory()->create();
        $account = $owner->account;

        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $account, User::ROLE_COLLABORATOR);

        app(PermissionRegistrar::class)->setPermissionsTeamId($account->id);
        $collaborator = $collaborator->fresh();

        $this->assertTrue($collaborator->hasRole(User::ROLE_COLLABORATOR));
        $this->assertFalse($collaborator->can('account.billing'));
        $this->assertFalse($collaborator->can('finance.manage'));
        $this->assertTrue($collaborator->can('clients.manage'));
    }
}
