<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Captação de leads — adaptado de CrmPlus (formularios_captura + capturas).
 * Diferença: o envio cria/atualiza um CLIENTE (+ oportunidade opcional no funil),
 * já que o Clientify360 não tem a entidade "prospecto".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_forms', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#4f46e5');
            $table->string('button_text', 50)->default('Enviar');
            $table->string('success_message', 500)->nullable();
            $table->string('redirect_url', 500)->nullable();
            $table->json('fields');
            $table->string('default_source', 50)->default('site');
            $table->boolean('create_deal')->default(true);
            $table->uuid('pipeline_stage_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('submissions_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('pipeline_stage_id')->references('id')->on('pipeline_stages')->nullOnDelete();
            $table->index('user_id');
        });

        Schema::create('lead_form_submissions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('lead_form_id');
            $table->uuid('user_id');
            $table->uuid('client_id')->nullable();
            $table->uuid('deal_id')->nullable();
            $table->json('data');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('note')->nullable(); // ex.: "limite do plano atingido — cliente não criado"
            $table->timestamps();

            $table->foreign('lead_form_id')->references('id')->on('lead_forms')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('client_id')->references('id')->on('clients')->nullOnDelete();
            $table->foreign('deal_id')->references('id')->on('deals')->nullOnDelete();
            $table->index(['user_id', 'created_at']);
            $table->index(['lead_form_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_form_submissions');
        Schema::dropIfExists('lead_forms');
    }
};
