<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ExportClientsXlsxJob;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Support\Subscriptions;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_professional_can_download_clients_xlsx(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        // Simula plano Profissional ativo na conta.
        $this->mockProfessional($user);

        $this->actingAs($user)
            ->from(route('clients.index'))
            ->post(route('export.clients.xlsx'))
            ->assertRedirect(route('clients.index'))
            ->assertSessionHas('success', "Sua planilha está sendo gerada. Enviaremos o link de download para {$user->email} em alguns minutos.");

        Queue::assertPushed(ExportClientsXlsxJob::class, fn (ExportClientsXlsxJob $job): bool => $job->accountId === $user->account_id && $job->userId === $user->id);
    }

    public function test_basic_plan_cannot_export(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post(route('export.clients.xlsx'))->assertForbidden();
        $this->get(route('export.clients'))->assertForbidden();
    }

    private function mockProfessional(User $user): void
    {
        // Cria uma assinatura ativa para a conta (Cashier Billable = Account).
        Subscriptions::makeProfessional($user->account);

        Client::create(['name' => 'Cliente Export', 'email' => 'exp@ex.com']);
    }
}
