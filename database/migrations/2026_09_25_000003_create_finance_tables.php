<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Financeiro — o CrmPlus usava 3 tabelas (contas_pagar, contas_receber, transacoes)
 * com dados duplicados. Aqui é UMA tabela de lançamentos: "a pagar" e "a receber"
 * são o campo `direction`, e o fluxo de caixa é simplesmente o que tem `paid_at`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_categories', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name', 80);
            $table->string('type', 10); // income | expense
            $table->string('color', 7)->default('#64748b');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'type']);
        });

        Schema::create('financial_entries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('direction', 10); // receivable | payable
            $table->uuid('category_id')->nullable();
            $table->uuid('client_id')->nullable();
            $table->uuid('quote_id')->nullable();
            $table->string('description');
            $table->string('counterparty')->nullable(); // fornecedor / pagador avulso
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->decimal('paid_amount', 12, 2)->nullable();
            $table->string('status', 10)->default('pending'); // pending | paid | canceled
            $table->string('payment_method', 30)->nullable();
            $table->unsignedSmallInteger('installment')->nullable();
            $table->unsignedSmallInteger('installments_total')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('financial_categories')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('quote_id')->references('id')->on('quotes')->nullOnDelete();
            $table->index(['user_id', 'direction', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_entries');
        Schema::dropIfExists('financial_categories');
    }
};
