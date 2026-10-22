<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Duration model DECISION: the appointment stores `starts_at` (required)
        // plus a nullable `ends_at`. Validation enforces ends_at >= starts_at and
        // the duration in minutes is derivable in the view via diffInMinutes().
        // Recurrence is intentionally out of scope (future enhancement).
        Schema::create('appointments', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->uuid('user_id')->nullable();
            $table->uuid('client_id')->nullable();
            $table->uuid('deal_id')->nullable();
            $table->uuid('reminder_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('type', 20)->default('meeting'); // meeting | visit | call | video
            $table->string('status', 20)->default('pending'); // pending | confirmed | done | cancelled
            $table->string('location')->nullable();
            $table->string('color', 32)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('deal_id')->references('id')->on('deals')->nullOnDelete();
            $table->foreign('reminder_id')->references('id')->on('reminders')->nullOnDelete();

            $table->index('account_id');
            $table->index('starts_at');
            $table->index(['account_id', 'starts_at']);
            $table->index(['account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
