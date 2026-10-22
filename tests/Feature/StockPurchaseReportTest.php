<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura das seções de estoque e compras adicionadas à tela de Relatórios
 * (FEAT-003): giro/valor de estoque, compras do período e isolamento por conta.
 */
class StockPurchaseReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_stock_turnover_section(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        Product::factory()->forAccount($owner->account_id)->create([
            'name' => 'Teclado Mecânico',
            'track_stock' => true,
            'stock' => 10,
            'cost' => 50,
        ]);

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Giro e valor de estoque');
        $response->assertSee('Teclado Mecânico');
    }

    public function test_it_shows_purchases_in_period(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);

        $product = Product::factory()->forAccount($owner->account_id)->create([
            'track_stock' => true,
            'stock' => 0,
        ]);

        $service = app(PurchaseService::class);
        $purchase = $service->create([
            'supplier' => 'Distribuidora Relatorio',
            'document_number' => '9988',
            'purchased_at' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => '4', 'unit_cost' => '15.00'],
            ],
        ]);
        $service->confirm($purchase);

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Compras no período');
        $response->assertSee('Distribuidora Relatorio');
    }

    public function test_it_isolates_report_data_between_accounts(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other);
        Product::factory()->forAccount($other->account_id)->create([
            'name' => 'Produto De Outra Conta',
            'track_stock' => true,
            'stock' => 5,
            'cost' => 20,
        ]);
        $foreignProduct = Product::factory()->forAccount($other->account_id)->create([
            'track_stock' => true,
            'stock' => 0,
        ]);
        $service = app(PurchaseService::class);
        $foreignPurchase = $service->create([
            'supplier' => 'Fornecedor Invisível',
            'document_number' => '1111',
            'purchased_at' => now()->toDateString(),
            'items' => [
                ['product_id' => $foreignProduct->id, 'quantity' => '2', 'unit_cost' => '10.00'],
            ],
        ]);
        $service->confirm($foreignPurchase);

        $owner = User::factory()->create();
        $this->actingAs($owner);

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertDontSee('Produto De Outra Conta');
        $response->assertDontSee('Fornecedor Invisível');

        $this->assertSame(0, Purchase::whereNotNull('confirmed_at')->count());
    }
}
