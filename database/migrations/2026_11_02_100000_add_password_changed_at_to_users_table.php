<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // When the current password was set. Drives the 90-day expiry (config/security.php).
            $table->timestamp('password_changed_at')->nullable()->after('password');
        });

        // Existing users start their 90 days now. Using created_at/updated_at here would lock
        // out every older account on the first request after the deploy.
        DB::table('users')->whereNull('password_changed_at')->update(['password_changed_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('password_changed_at');
        });
    }
};
