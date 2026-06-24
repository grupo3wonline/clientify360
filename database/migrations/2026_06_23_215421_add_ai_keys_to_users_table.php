<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('ai_gemini_key')->nullable()->after('terms_version');
            $table->text('ai_groq_key')->nullable()->after('ai_gemini_key');
            $table->text('ai_openrouter_key')->nullable()->after('ai_groq_key');
            $table->string('ai_preferred_provider', 20)->nullable()->after('ai_openrouter_key');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['ai_gemini_key', 'ai_groq_key', 'ai_openrouter_key', 'ai_preferred_provider']);
        });
    }
};
