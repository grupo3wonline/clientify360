<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Immutable log of cookie-consent decisions (LGPD/GDPR proof of consent).
        // One row per decision; no updated_at and no soft deletes. The IP is stored
        // only as an HMAC hash. Rows are pruned after 5 years (CookieConsent::prunable).
        Schema::create('cookie_consents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('consent_id');
            $table->uuid('user_id')->nullable();
            $table->boolean('analytics')->default(false);
            $table->boolean('marketing')->default(false);
            $table->string('policy_version', 20);
            $table->string('source', 20);
            $table->char('ip_hash', 64)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->index('consent_id');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consents');
    }
};
