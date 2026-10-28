<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Composite indexes for the account-scoped filters of the listings, reports and
 * dashboard (every query starts with account_id). Valid on MySQL 5.7 and SQLite.
 * Each index is skipped when one with the same columns or name already exists.
 */
return new class extends Migration
{
    /** @var array<string, array<string, list<string>>> table => [index name => columns] */
    private const INDEXES = [
        'clients' => [
            'clients_account_created_index' => ['account_id', 'created_at'],
            'clients_account_name_index' => ['account_id', 'name'],
        ],
        'quotes' => [
            'quotes_account_status_index' => ['account_id', 'status'],
            'quotes_account_created_index' => ['account_id', 'created_at'],
        ],
        'financial_entries' => [
            'financial_entries_account_status_due_index' => ['account_id', 'status', 'due_date'],
        ],
        'deals' => [
            'deals_account_stage_index' => ['account_id', 'pipeline_stage_id'],
        ],
        'reminders' => [
            'reminders_account_remind_at_index' => ['account_id', 'remind_at'],
        ],
        'interactions' => [
            'interactions_account_status_index' => ['account_id', 'status'],
        ],
        'products' => [
            'products_account_name_index' => ['account_id', 'name'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (Schema::hasIndex($table, $name) || Schema::hasIndex($table, $columns)) {
                    continue;
                }

                Schema::table($table, fn (Blueprint $t) => $t->index($columns, $name));
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, fn (Blueprint $t) => $t->dropIndex($name));
                }
            }
        }
    }
};
