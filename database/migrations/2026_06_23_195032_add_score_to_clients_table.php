<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->unsignedTinyInteger('score')->default(0)->after('notes');
            $table->string('score_label', 20)->nullable()->after('score');

            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropIndex(['score']);
            $table->dropColumn(['score', 'score_label']);
        });
    }
};
