<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vendas — mesclado de:
 *  - sistema-vendas-master (produtos: código, estoque, NCM, preço, promoção, custo, último fornecedor; compras)
 *  - Sistema-de-Vendas-PHP/OSPOS (items.reorder_level, inventory = movimentações de estoque)
 *  - CrmPlus (orcamentos + orcamento_itens, com status e validade)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('type', 10)->default('product'); // product | service
            $table->string('sku', 60)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 10)->default('un');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('promo_price', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->boolean('track_stock')->default(false);
            $table->decimal('stock', 12, 3)->default(0);
            $table->decimal('min_stock', 12, 3)->default(0);
            $table->string('ncm', 10)->nullable();
            $table->string('supplier')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'name']);
            $table->index(['user_id', 'sku']);
        });

        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('product_id');
            $table->string('type', 10); // in | out | adjust
            $table->decimal('quantity', 12, 3);
            $table->decimal('balance_after', 12, 3);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reason')->nullable();
            $table->string('document', 60)->nullable(); // nº da nota de compra
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('quotes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('client_id');
            $table->uuid('deal_id')->nullable();
            $table->string('number', 20);
            $table->string('title');
            $table->string('status', 10)->default('draft'); // draft | sent | approved | rejected | expired
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('deal_id')->references('id')->on('deals')->nullOnDelete();
            // Numeração única POR USUÁRIO (no CrmPlus era global, o que quebra o multi-tenant)
            $table->unique(['user_id', 'number']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('quote_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('quote_id');
            $table->uuid('product_id')->nullable();
            $table->string('description');
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total', 12, 2);
            $table->unsignedSmallInteger('position')->default(1);
            $table->timestamps();

            $table->foreign('quote_id')->references('id')->on('quotes')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->index('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
    }
};
