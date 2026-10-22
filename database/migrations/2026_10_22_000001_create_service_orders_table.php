<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Service orders track post-sale execution (open -> in_progress -> done).
        // Numbering is unique per account via unique(account_id, number). The
        // client_id/quote_id FKs are nullable + nullOnDelete so an order survives
        // the deletion of its client or quote, mirroring the appointments table.
        Schema::create('service_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('user_id')->nullable();
            $table->uuid('client_id')->nullable();
            $table->uuid('quote_id')->nullable();
            $table->string('number', 20);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open'); // open | in_progress | done | cancelled
            $table->dateTime('opened_at');
            $table->dateTime('expected_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->decimal('total', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('quote_id')->references('id')->on('quotes')->nullOnDelete();

            $table->index('account_id');
            $table->index(['account_id', 'status']);
            $table->unique(['account_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
