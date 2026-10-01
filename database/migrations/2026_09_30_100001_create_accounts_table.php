<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A "conta" é o tenant do SaaS: agrupa o dono e os colaboradores, e é a dona
 * de todos os dados de negócio. A assinatura Stripe (Cashier) passa a ser da conta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('owner_id')->nullable();

            // Colunas do Cashier (Billable) na conta.
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
