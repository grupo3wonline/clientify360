<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Deal;
use App\Models\FinancialEntry;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Garante que os módulos comerciais respeitam o isolamento por CONTA:
 * colaboradores operam os dados da conta; contas distintas não se cruzam.
 */
class CommercialAccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function collaboratorFor(User $owner, string $role = User::ROLE_COLLABORATOR): User
    {
        $member = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($member, $owner->account, $role);

        return $member->fresh();
    }

    public function test_collaborator_can_view_and_edit_account_client(): void
    {
        // O CRUD de clientes migrou para o Filament (ClientResource). A regra de
        // negócio verificada aqui é o compartilhamento por CONTA: o colaborador
        // enxerga e edita os clientes da conta do dono através do mesmo escopo global.
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente do Dono', 'email' => 'dono-cli@ex.com']);

        $collaborator = $this->collaboratorFor($owner);
        $this->actingAs($collaborator);

        $this->assertTrue(Client::whereKey($client->id)->exists(), 'colaborador vê o cliente da conta');

        $client->update(['name' => 'Editado pelo Colaborador']);
        $this->assertSame('Editado pelo Colaborador', $client->fresh()->name);
    }

    public function test_cannot_view_client_from_another_account(): void
    {
        $alice = User::factory()->create();
        $this->actingAs($alice);
        $aliceClient = Client::create(['name' => 'Cliente Alice', 'email' => 'alice-cli@ex.com']);

        $bob = User::factory()->create();
        $this->actingAs($bob);

        // Global scope esconde o registro de outra conta.
        $this->assertFalse(Client::whereKey($aliceClient->id)->exists());
        $this->assertNull(Client::find($aliceClient->id));
    }

    public function test_commercial_data_is_shared_within_account_not_across(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente', 'email' => 'c@ex.com']);
        PipelineStage::ensureDefaultsFor($owner->account_id);
        Deal::create(['client_id' => $client->id, 'title' => 'Negócio', 'value' => 500, 'pipeline_stage_id' => PipelineStage::first()->id]);
        Product::create(['name' => 'Produto', 'price' => 10]);
        FinancialEntry::create(['direction' => 'receivable', 'description' => 'Venda', 'amount' => 500, 'due_date' => now(), 'status' => 'pending']);

        // Colaborador da mesma conta vê tudo.
        $collaborator = $this->collaboratorFor($owner);
        $this->actingAs($collaborator);
        $this->assertSame(1, Deal::count());
        $this->assertSame(1, Product::count());

        // Outra conta não vê nada.
        $other = User::factory()->create();
        $this->actingAs($other);
        $this->assertSame(0, Client::count());
        $this->assertSame(0, Deal::count());
        $this->assertSame(0, Product::count());
        $this->assertSame(0, FinancialEntry::count());
    }

    public function test_collaborator_cannot_access_finance_module(): void
    {
        $owner = User::factory()->create();
        $collaborator = $this->collaboratorFor($owner);

        // Colaborador não tem finance.view (apenas Dono e Gestor têm).
        $this->assertFalse($collaborator->can('finance.view'));
        $this->assertFalse($collaborator->can('finance.manage'));
    }

    public function test_manager_has_finance_but_not_billing(): void
    {
        $owner = User::factory()->create();
        $manager = $this->collaboratorFor($owner, User::ROLE_MANAGER);

        $this->assertTrue($manager->can('finance.manage'));
        $this->assertTrue($manager->can('account.members'));
        $this->assertFalse($manager->can('account.billing'));
    }
}
