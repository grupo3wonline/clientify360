<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\Support\Subscriptions;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_download_clients_xlsx(): void
    {
        Excel::fake();

        $user = User::factory()->create();
        // Simula plano Profissional ativo na conta.
        $this->mockProfessional($user);

        $this->actingAs($user)
            ->get(route('export.clients.xlsx'))
            ->assertOk();

        Excel::assertDownloaded('clientes_'.now()->format('Y-m-d').'.xlsx');
    }

    public function test_basic_plan_cannot_export(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('export.clients.xlsx'))->assertForbidden();
        $this->get(route('export.clients'))->assertForbidden();
    }

    private function mockProfessional(User $user): void
    {
        // Cria uma assinatura ativa para a conta (Cashier Billable = Account).
        Subscriptions::makeProfessional($user->account);

        Client::create(['name' => 'Cliente Export', 'email' => 'exp@ex.com']);
    }
}
