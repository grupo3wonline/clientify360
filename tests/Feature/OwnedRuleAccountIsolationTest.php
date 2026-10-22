<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\PipelineStage;
use App\Models\Product;
use App\Models\User;
use App\Services\AccountService;
use App\Support\OwnedRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Regressão de isolamento para a regra OwnedRule (filtro por CONTA, não por
 * user_id) e para o `exists` escopado de Interaction/Reminder. Exercita os
 * cenários que o bug original quebrava: multi-colaborador na mesma conta,
 * registros padrão com user_id nulo e referência cruzada entre contas.
 */
class OwnedRuleAccountIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function collaboratorFor(User $owner, string $role = User::ROLE_COLLABORATOR): User
    {
        $member = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($member, $owner->account, $role);

        return $member->fresh();
    }

    public function test_it_allows_collaborator_to_reference_record_created_by_teammate(): void
    {
        // Dono A cria cliente e produto na conta.
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente de A', 'email' => 'a-cli@ex.com']);
        $product = Product::factory()->forAccount($owner->account_id)->create();
        PipelineStage::ensureDefaultsFor($owner->account_id);

        // Colaborador B (mesma conta) cria um orçamento referenciando os dados de A.
        $collaborator = $this->collaboratorFor($owner);
        $this->actingAs($collaborator);

        $response = $this->post(route('quotes.store'), [
            'client_id' => $client->id,
            'title' => 'Orçamento do colaborador',
            'items' => [
                ['product_id' => $product->id, 'description' => 'Item 1', 'quantity' => 2, 'unit_price' => 100],
            ],
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_it_accepts_default_pipeline_stage_with_null_user_id(): void
    {
        $owner = User::factory()->create();

        // Etapa padrão criada SEM usuário logado (como no formulário público via
        // PipelineStage::ensureDefaultsFor()): user_id fica NULL. Criamos antes
        // de autenticar para que o hook de creating não preencha user_id.
        PipelineStage::ensureDefaultsFor($owner->account_id);
        $defaultStage = PipelineStage::withoutGlobalScope('owned')
            ->where('account_id', $owner->account_id)
            ->whereNull('user_id')
            ->firstOrFail();
        $this->assertNull($defaultStage->user_id);

        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente', 'email' => 'cli@ex.com']);

        $response = $this->post(route('deals.store'), [
            'client_id' => $client->id,
            'pipeline_stage_id' => $defaultStage->id,
            'title' => 'Oportunidade em etapa padrão',
            'value' => 1500,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_it_rejects_interaction_client_from_another_account(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other);
        $otherClient = Client::create(['name' => 'Cliente de Outra Conta', 'email' => 'outra@ex.com']);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('interactions.store'), [
            'client_id' => $otherClient->id,
            'type' => 'support',
            'channel' => 'phone',
            'subject' => 'Assunto',
        ]);

        $response->assertSessionHasErrors('client_id');
    }

    public function test_it_accepts_interaction_client_from_same_account(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $client = Client::create(['name' => 'Cliente Próprio', 'email' => 'proprio@ex.com']);

        $response = $this->post(route('interactions.store'), [
            'client_id' => $client->id,
            'type' => 'support',
            'channel' => 'phone',
            'subject' => 'Assunto',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('interactions.index'));
        $response->assertSessionHas('success');
    }

    public function test_it_rejects_reminder_client_from_another_account(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other);
        $otherClient = Client::create(['name' => 'Cliente de Outra Conta', 'email' => 'outra-r@ex.com']);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('reminders.store'), [
            'title' => 'Lembrete',
            'client_id' => $otherClient->id,
            'remind_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('client_id');
    }

    public function test_it_accepts_reminder_without_client(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('reminders.store'), [
            'title' => 'Lembrete sem cliente',
            'client_id' => null,
            'remind_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('reminders.index'));
        $response->assertSessionHas('success');
    }

    public function test_it_scopes_owned_rule_by_account_not_user(): void
    {
        // Colaborador B valida um cliente criado por A: a regra é por conta.
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $client = Client::create(['name' => 'Cliente de A', 'email' => 'rule-a@ex.com']);

        $collaborator = $this->collaboratorFor($owner);
        $this->actingAs($collaborator);

        $validator = Validator::make(
            ['client_id' => $client->id],
            ['client_id' => [OwnedRule::exists('clients')]]
        );

        $this->assertTrue($validator->passes());
    }
}
