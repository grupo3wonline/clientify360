<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A assinatura passa a pertencer à conta (Billable = Account). O Cashier
 * resolve a chave estrangeira como `account_id`, então renomeamos a coluna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->renameColumn('user_id', 'account_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->renameColumn('account_id', 'user_id');
        });
    }
};
