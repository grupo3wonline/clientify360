<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CRM comercial — mesclado de CrmPlus (tags, funil, oportunidades, atividades)
 * e crm-master (origem do cliente + histórico com "próximo passo").
 */
return new class extends Migration
{
    public function up(): void
    {
        // Origem do cliente ("Como chegou até nós" — crm-master.customers.source)
        Schema::table('clients', function (Blueprint $table): void {
            $table->string('source', 50)->nullable()->after('notes');
            $table->index(['user_id', 'source']);
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name', 50);
            $table->string('color', 7)->default('#6366f1');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('client_tag', function (Blueprint $table): void {
            $table->uuid('client_id');
            $table->uuid('tag_id');
            $table->primary(['client_id', 'tag_id']);

            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });

        Schema::create('pipeline_stages', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name', 60);
            $table->string('color', 7)->default('#6366f1');
            $table->unsignedSmallInteger('position')->default(1);
            $table->unsignedTinyInteger('probability')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'position']);
        });

        Schema::create('deals', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('client_id');
            $table->uuid('pipeline_stage_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('value', 12, 2)->default(0);
            $table->unsignedTinyInteger('probability')->default(50);
            $table->string('status', 10)->default('open'); // open | won | lost
            $table->date('expected_close_date')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->string('source', 50)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('pipeline_stage_id')->references('id')->on('pipeline_stages')->nullOnDelete();
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'pipeline_stage_id']);
        });

        Schema::create('activities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('client_id');
            $table->uuid('deal_id')->nullable();
            $table->uuid('reminder_id')->nullable();
            $table->string('type', 20)->default('note');
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('happened_at');
            $table->string('outcome', 10)->nullable(); // positive | neutral | negative
            $table->text('next_step')->nullable();
            $table->dateTime('next_step_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->cascadeOnDelete();
            $table->foreign('deal_id')->references('id')->on('deals')->nullOnDelete();
            $table->foreign('reminder_id')->references('id')->on('reminders')->nullOnDelete();
            $table->index(['user_id', 'happened_at']);
            $table->index(['client_id', 'happened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
        Schema::dropIfExists('deals');
        Schema::dropIfExists('pipeline_stages');
        Schema::dropIfExists('client_tag');
        Schema::dropIfExists('tags');

        Schema::table('clients', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'source']);
            $table->dropColumn('source');
        });
    }
};
