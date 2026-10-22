<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migra o isolamento de dados de `user_id` para `account_id` (tenant).
 * Adiciona `account_id` a todas as tabelas de negócio, faz backfill a partir
 * da conta do usuário dono do registro e indexa a nova coluna.
 *
 * `user_id` é mantido onde existe para registrar qual colaborador criou o dado.
 */
return new class extends Migration
{
    /**
     * Tabelas que hoje têm user_id e passam a ter account_id para isolamento.
     *
     * @var list<string>
     */
    private array $tables = [
        'clients',
        'tags',
        'pipeline_stages',
        'deals',
        'activities',
        'products',
        'stock_movements',
        'quotes',
        'financial_categories',
        'financial_entries',
        'lead_forms',
        'lead_form_submissions',
        'reminders',
        'interactions',
        'dismissed_notifications',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            if (!Schema::hasColumn($table, 'account_id')) {
                Schema::table($table, function (Blueprint $t): void {
                    $t->uuid('account_id')->nullable()->after('id');
                    $t->index('account_id');
                });
            }
        }

        // Backfill: account_id = conta do usuário dono do registro.
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'user_id')) {
                DB::statement(
                    "UPDATE {$table} SET account_id = (SELECT users.account_id FROM users WHERE users.id = {$table}.user_id) WHERE account_id IS NULL"
                );
            }
        }

        // Numeração de orçamento é única por CONTA (antes era por user_id).
        Schema::table('quotes', function (Blueprint $t): void {
            $t->dropUnique('quotes_user_id_number_unique');
            $t->unique(['account_id', 'number']);
        });

        // user_id passa a ser auxiliar (qual colaborador criou) e pode ser nulo —
        // ex.: etapas/categorias padrão criadas automaticamente pela conta.
        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'user_id')) {
                Schema::table($table, function (Blueprint $t): void {
                    $t->uuid('user_id')->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $t): void {
            $t->dropUnique('quotes_account_id_number_unique');
            $t->unique(['user_id', 'number']);
        });

        foreach ($this->tables as $table) {
            if (Schema::hasColumn($table, 'account_id')) {
                Schema::table($table, function (Blueprint $t) use ($table): void {
                    $t->dropIndex($table.'_account_id_index');
                    $t->dropColumn('account_id');
                });
            }
        }
    }
};
