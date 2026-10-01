<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\AccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A assinatura é da conta e só o Dono (permissão account.billing) gerencia.
 */
class BillingAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_open_billing(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->get(route('billing.index'))
            ->assertOk()
            ->assertSee('Assinatura');
    }

    public function test_collaborator_cannot_open_billing(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $owner->account, User::ROLE_COLLABORATOR);

        $this->actingAs($collaborator->fresh());

        $this->get(route('billing.index'))->assertForbidden();
        $this->post(route('billing.subscribe'))->assertForbidden();
    }

    public function test_manager_cannot_open_billing(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($manager, $owner->account, User::ROLE_MANAGER);

        $this->actingAs($manager->fresh());

        $this->get(route('billing.index'))->assertForbidden();
    }

    public function test_free_account_sees_basic_plan(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->assertFalse($owner->isProfessional());
        $this->get(route('billing.index'))->assertOk()->assertSee('Básico');
    }
}
