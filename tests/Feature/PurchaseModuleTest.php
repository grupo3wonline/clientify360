<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FinancialCategory;
use App\Models\FinancialEntry;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cobertura do módulo de Compras: criação com múltiplos itens, telas, isolamento
 * por conta, validação por OwnedRule, confirmação (entrada no estoque + conta a
 * pagar única) e idempotência da confirmação. Segue o padrão de
 * ServiceOrderModuleTest.
 */
class PurchaseModuleTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validPayload(Product $a, Product $b, array $overrides = []): array
    {
        return array_merge([
            'supplier' => 'Distribuidora ACME',
            'document_number' => '12345',
            'purchased_at' => now()->toDateString(),
            'items' => [
                ['product_id' => $a->id, 'quantity' => '3', 'unit_cost' => '10.00'],
                ['product_id' => $b->id, 'quantity' => '2', 'unit_cost' => '25.00'],
            ],
        ], $overrides);
    }

    public function test_it_creates_a_purchase_with_multiple_items(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $a = Product::factory()->forAccount($owner->account_id)->create();
        $b = Product::factory()->forAccount($owner->account_id)->create();

        $response = $this->post(route('purchases.store'), $this->validPayload($a, $b));

        $purchase = Purchase::withoutGlobalScope('owned')->where('account_id', $owner->account_id)->firstOrFail();
        $response->assertRedirect(route('purchases.show', $purchase));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchases', [
            'account_id' => $owner->account_id,
            'user_id' => $owner->id,
            'number' => 'CMP-'.now()->format('Y').'-0001',
            'total' => '80.00',
        ]);
        $this->assertSame(2, $purchase->items()->count());
    }

    public function test_it_shows_index_create_and_show_screens(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $purchase = Purchase::factory()->forAccount($owner->account_id)->create();

        $this->get(route('purchases.index'))->assertOk();
        $this->get(route('purchases.create'))->assertOk();
        $this->get(route('purchases.show', $purchase))->assertOk();
    }

    public function test_it_isolates_purchases_between_accounts(): void
    {
        $other = User::factory()->create();
        $foreign = Purchase::factory()->forAccount($other->account_id)->create();

        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('purchases.show', $foreign))->assertNotFound();
    }

    public function test_it_rejects_product_from_another_account(): void
    {
        $other = User::factory()->create();
        $this->actingAs($other);
        $foreignProduct = Product::factory()->forAccount($other->account_id)->create();

        $owner = User::factory()->create();
        $this->actingAs($owner);
        $own = Product::factory()->forAccount($owner->account_id)->create();

        $this->post(route('purchases.store'), $this->validPayload($own, $own, [
            'items' => [
                ['product_id' => $foreignProduct->id, 'quantity' => '1', 'unit_cost' => '10'],
            ],
        ]))->assertSessionHasErrors('items.0.product_id');
    }

    public function test_it_confirm_moves_stock_in_and_generates_single_payable(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $a = Product::factory()->forAccount($owner->account_id)->create(['track_stock' => true, 'stock' => 5]);
        $b = Product::factory()->forAccount($owner->account_id)->create(['track_stock' => true, 'stock' => 0]);

        $this->post(route('purchases.store'), $this->validPayload($a, $b));
        $purchase = Purchase::withoutGlobalScope('owned')->where('account_id', $owner->account_id)->firstOrFail();

        $this->patch(route('purchases.confirm', $purchase));

        $this->assertSame('8.000', $a->fresh()->stock); // 5 + 3
        $this->assertSame('2.000', $b->fresh()->stock); // 0 + 2

        $movement = StockMovement::withoutGlobalScope('owned')
            ->where('product_id', $a->id)
            ->where('type', 'in')
            ->firstOrFail();
        $this->assertSame('8.000', $movement->balance_after);

        $entries = FinancialEntry::withoutGlobalScope('owned')
            ->where('account_id', $owner->account_id)
            ->where('direction', FinancialEntry::PAYABLE)
            ->get();

        $this->assertCount(1, $entries);
        $this->assertSame('80.00', $entries->first()->amount);

        $categoryId = FinancialCategory::withoutGlobalScope('owned')
            ->where('account_id', $owner->account_id)
            ->where('type', 'expense')
            ->where('name', 'Fornecedores / Compras')
            ->value('id');
        $this->assertSame($categoryId, $entries->first()->category_id);

        $this->assertNotNull($purchase->fresh()->confirmed_at);
    }

    public function test_it_confirm_is_idempotent(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner);
        $a = Product::factory()->forAccount($owner->account_id)->create(['track_stock' => true, 'stock' => 0]);
        $b = Product::factory()->forAccount($owner->account_id)->create(['track_stock' => true, 'stock' => 0]);

        $this->post(route('purchases.store'), $this->validPayload($a, $b));
        $purchase = Purchase::withoutGlobalScope('owned')->where('account_id', $owner->account_id)->firstOrFail();

        $this->patch(route('purchases.confirm', $purchase));
        $this->patch(route('purchases.confirm', $purchase));

        $this->assertSame('3.000', $a->fresh()->stock);
        $this->assertSame('2.000', $b->fresh()->stock);

        $payableCount = FinancialEntry::withoutGlobalScope('owned')
            ->where('account_id', $owner->account_id)
            ->where('direction', FinancialEntry::PAYABLE)
            ->count();
        $this->assertSame(1, $payableCount);

        $movementCount = StockMovement::withoutGlobalScope('owned')
            ->where('product_id', $a->id)
            ->count();
        $this->assertSame(1, $movementCount);
    }
}
