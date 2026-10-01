<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

/**
 * Auditoria (LGPD): alterações em dados pessoais de clientes são registradas,
 * com o usuário causador.
 */
class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_changes_are_logged_with_causer(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::create(['name' => 'João', 'email' => 'joao@ex.com']);

        $created = Activity::where('log_name', 'client')->where('description', 'created')->latest()->first();
        $this->assertNotNull($created);
        $this->assertSame($client->id, $created->subject_id);
        $this->assertSame($user->id, $created->causer_id);

        $client->update(['name' => 'João Silva']);

        $updated = Activity::where('log_name', 'client')->where('description', 'updated')->latest()->first();
        $this->assertNotNull($updated);
        $this->assertSame('João Silva', $updated->properties['attributes']['name']);
        $this->assertSame('João', $updated->properties['old']['name']);
    }

    public function test_unchanged_fields_do_not_create_log(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::create(['name' => 'Maria', 'email' => 'maria@ex.com']);
        $countAfterCreate = Activity::where('log_name', 'client')->count();

        // Alterar um campo não auditado (city) não deve gerar log.
        $client->update(['city' => 'Curitiba']);

        $this->assertSame($countAfterCreate, Activity::where('log_name', 'client')->count());
    }
}
