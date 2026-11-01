<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Tag;
use App\Models\User;
use App\Services\AccountService;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Valida que a unicidade do nome da tag é por CONTA (tenant), não por usuário,
 * e que a cor da tag precisa ter contraste acessível (WCAG AA).
 */
class TagIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tag_name_must_be_unique_within_the_account(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('tags.store'), ['name' => 'VIP', 'color' => '#16A34A'])
            ->assertSessionHasNoErrors();

        // Colaborador da MESMA conta não pode repetir o nome da tag.
        $collaborator = User::factory()->withoutAccount()->create();
        app(AccountService::class)->addMember($collaborator, $owner->account, User::ROLE_COLLABORATOR);

        $this->actingAs($collaborator->fresh())
            ->post(route('tags.store'), ['name' => 'VIP', 'color' => '#2563EB'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Tag::withoutGlobalScope('owned')->where('name', 'VIP')->count());
    }

    public function test_different_accounts_can_reuse_the_same_tag_name(): void
    {
        $alice = User::factory()->create();
        $this->actingAs($alice)
            ->post(route('tags.store'), ['name' => 'Quente', 'color' => '#DC2626'])
            ->assertSessionHasNoErrors();

        $bob = User::factory()->create();
        $this->actingAs($bob)
            ->post(route('tags.store'), ['name' => 'Quente', 'color' => '#DC2626'])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Tag::withoutGlobalScope('owned')->where('name', 'Quente')->count());
    }

    public function test_tag_color_must_be_a_valid_hex(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('tags.store'), ['name' => 'Inválida', 'color' => 'vermelho'])
            ->assertSessionHasErrors('color');

        $this->assertSame(0, Tag::count());
    }

    public function test_valid_tag_color_is_accepted(): void
    {
        $owner = User::factory()->create();

        $this->actingAs($owner)
            ->post(route('tags.store'), ['name' => 'Verde', 'color' => '#15803D'])
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Tag::count());
    }

    public function test_update_keeps_own_name_without_uniqueness_error(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $service = app(TagService::class);
        $tag = $service->create(['name' => 'VIP', 'color' => '#16A34A']);

        $this->put(route('tags.update', $tag), ['name' => 'VIP', 'color' => '#2563EB'])
            ->assertSessionHasNoErrors();

        $this->assertSame('#2563EB', $tag->fresh()->color);
    }

    public function test_service_detaches_clients_before_deleting_the_tag(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $service = app(TagService::class);
        $tag = $service->create(['name' => 'Para excluir', 'color' => '#DC2626']);
        $client = Client::factory()->create(['account_id' => $owner->account_id]);
        $tag->clients()->attach($client);

        $service->delete($tag);

        $this->assertSame(0, Tag::count());
        $this->assertDatabaseMissing('client_tag', ['tag_id' => $tag->id, 'client_id' => $client->id]);
    }
}
