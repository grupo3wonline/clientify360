<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_client_list_shows_account_data_and_hides_others(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        Client::create(['name' => 'Meu Cliente Único', 'email' => 'meu@ex.com']);

        $other = User::factory()->create();
        $this->actingAs($other);
        Client::create(['name' => 'Cliente Alheio Secreto', 'email' => 'alheio@ex.com']);

        $this->actingAs($owner)
            ->get('/app/clients')
            ->assertOk()
            ->assertSee('Meu Cliente Único')
            ->assertDontSee('Cliente Alheio Secreto');
    }

    public function test_owner_can_access_the_panel_dashboard(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)->get('/app')->assertOk();
    }

    public function test_user_without_account_cannot_access_panel(): void
    {
        $orphan = User::factory()->withoutAccount()->create();
        $this->assertNull($orphan->account_id);

        $this->actingAs($orphan)->get('/app')->assertForbidden();
    }
}
