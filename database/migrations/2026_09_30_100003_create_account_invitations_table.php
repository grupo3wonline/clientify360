<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Convites para colaboradores entrarem numa conta com um papel definido.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_invitations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('account_id');
            $table->string('email');
            $table->string('role', 30)->default('collaborator');
            $table->string('token', 64)->unique();
            $table->uuid('invited_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
            $table->foreign('invited_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['account_id', 'email']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_invitations');
    }
};
