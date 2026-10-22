<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Salvaguarda: um usuário sem conta (órfão) receberia 403 do painel Filament.
 * O login deve provisionar a conta sob demanda e liberar o acesso ao /app.
 */
class OrphanUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_orphan_user_gets_account_provisioned_on_login(): void
    {
        $user = User::factory()->withoutAccount()->create();

        $this->assertNull($user->account_id);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/app');

        $user->refresh();
        $this->assertNotNull($user->account_id, 'A conta deveria ter sido provisionada no login.');
        $this->assertSame($user->id, $user->account->owner_id);
    }

    public function test_orphan_user_can_access_panel_after_login(): void
    {
        $user = User::factory()->withoutAccount()->create();

        // Antes do login: sem conta, o painel deve recusar o acesso.
        $this->assertFalse($user->canAccessPanel(app(Panel::class)));

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        // Depois do login: conta provisionada e e-mail verificado (factory) => acesso liberado.
        $user->refresh();
        $this->assertTrue($user->canAccessPanel(app(Panel::class)));

        $this->get('/app')->assertSuccessful();
    }

    public function test_existing_account_is_not_replaced_on_login(): void
    {
        $user = User::factory()->create();
        $originalAccountId = $user->account_id;

        $this->assertNotNull($originalAccountId);

        $this->post('/login', [
            'login' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();
        $this->assertSame($originalAccountId, $user->account_id, 'A conta existente não deve ser substituída.');
    }
}
