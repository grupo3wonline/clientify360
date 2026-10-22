<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura do tipo de pessoa (PF/PJ) no cadastro de cliente e da validação
 * de CPF/CNPJ via App\Rules\CpfCnpj (com e sem máscara), além da persistência
 * de person_type no create e update (que passa pelo ClientDTO).
 */
class ClientPersonTypeTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_CPF = '529.982.247-25';

    private const VALID_CNPJ = '11.222.333/0001-81';

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Cliente Teste',
            'person_type' => 'individual',
        ], $overrides);
    }

    public function test_it_defaults_person_type_to_individual(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $client = Client::create(['name' => 'Sem Tipo']);

        $this->assertSame('individual', $client->fresh()->person_type);
    }

    public function test_it_accepts_valid_cpf_for_individual(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $response = $this->post(route('clients.store'), $this->payload([
            'person_type' => 'individual',
            'document' => self::VALID_CPF,
        ]));

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('clients', [
            'account_id' => $owner->account_id,
            'name' => 'Cliente Teste',
            'person_type' => 'individual',
        ]);
    }

    public function test_it_rejects_invalid_cpf(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $response = $this->post(route('clients.store'), $this->payload([
            'person_type' => 'individual',
            'document' => '111.111.111-11',
        ]));

        $response->assertSessionHasErrors('document');
    }

    public function test_it_accepts_valid_cnpj_for_company(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $response = $this->post(route('clients.store'), $this->payload([
            'person_type' => 'company',
            'document' => self::VALID_CNPJ,
        ]));

        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('clients', [
            'account_id' => $owner->account_id,
            'person_type' => 'company',
        ]);
    }

    public function test_it_rejects_invalid_cnpj(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $response = $this->post(route('clients.store'), $this->payload([
            'person_type' => 'company',
            'document' => '11.222.333/0001-99',
        ]));

        $response->assertSessionHasErrors('document');
    }

    public function test_it_accepts_document_with_and_without_mask(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $masked = $this->post(route('clients.store'), $this->payload([
            'person_type' => 'individual',
            'document' => self::VALID_CPF,
        ]));
        $masked->assertSessionHasNoErrors();

        $unmasked = $this->post(route('clients.store'), $this->payload([
            'name' => 'Cliente Sem Mascara',
            'person_type' => 'individual',
            'document' => '52998224725',
        ]));
        $unmasked->assertSessionHasNoErrors();
    }

    public function test_it_persists_person_type_on_create_and_update(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $this->post(route('clients.store'), $this->payload([
            'person_type' => 'individual',
            'document' => self::VALID_CPF,
        ]))->assertSessionHasNoErrors();

        $client = Client::where('name', 'Cliente Teste')->firstOrFail();
        $this->assertSame('individual', $client->person_type);

        $this->put(route('clients.update', $client), $this->payload([
            'person_type' => 'company',
            'document' => self::VALID_CNPJ,
        ]))->assertSessionHasNoErrors();

        $this->assertSame('company', $client->fresh()->person_type);
    }
}
