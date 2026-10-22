<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Purchases register goods received from a supplier. Confirming a purchase
        // moves each item into stock (StockService) and generates a single payable
        // (FinancialEntry PAYABLE) to the supplier. Numbering is unique per account
        // via unique(account_id, number). Confirmation is idempotent via confirmed_at.
        Schema::create('purchases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('user_id')->nullable();
            $table->string('number', 20)->nullable();
            $table->string('supplier')->nullable();
            $table->string('document_number', 60)->nullable();
            $table->date('purchased_at');
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->index('account_id');
            $table->index(['account_id', 'confirmed_at']);
            $table->unique(['account_id', 'number']);
        });

        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('user_id')->nullable();
            $table->uuid('purchase_id');
            $table->uuid('product_id')->nullable();
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('purchase_id')->references('id')->on('purchases')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->index('purchase_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
