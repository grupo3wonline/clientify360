<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Deal;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_page_renders_with_commercial_metrics(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Desempenho comercial')
            ->assertSee('Taxa de Conversão');
    }

    public function test_conversion_rate_counts_only_closed_deals_in_period(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::create(['name' => 'Cliente', 'email' => 'c@ex.com']);

        // 3 ganhas, 1 perdida no período → conversão 75%.
        foreach (range(1, 3) as $i) {
            Deal::create(['client_id' => $client->id, 'title' => "Ganha {$i}", 'value' => 1000, 'status' => Deal::STATUS_WON, 'closed_at' => now()->subDay()]);
        }
        Deal::create(['client_id' => $client->id, 'title' => 'Perdida', 'value' => 500, 'status' => Deal::STATUS_LOST, 'closed_at' => now()->subDay(), 'lost_reason' => 'Preço']);
        // 1 aberta não entra na conta de conversão.
        Deal::create(['client_id' => $client->id, 'title' => 'Aberta', 'value' => 999, 'status' => Deal::STATUS_OPEN]);

        $data = app(ReportService::class)->commercial(now()->subDays(30));

        $this->assertSame(3, $data['deals_won']);
        $this->assertSame(1, $data['deals_lost']);
        $this->assertSame(75.0, $data['conversion_rate']);
        $this->assertEquals(3000.0, $data['deals_won_value']);
        $this->assertSame(['Preço' => 1], $data['lost_reasons']);
    }
}
