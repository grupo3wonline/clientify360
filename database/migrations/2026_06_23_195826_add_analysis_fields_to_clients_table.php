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
            $table->text('strengths')->nullable()->after('score_label');
            $table->text('weaknesses')->nullable()->after('strengths');
            $table->text('opportunities')->nullable()->after('weaknesses');
            $table->boolean('needs_website')->default(false)->after('opportunities');
            $table->boolean('needs_system')->default(false)->after('needs_website');
            $table->boolean('needs_marketing')->default(false)->after('needs_system');
            $table->boolean('needs_ecommerce')->default(false)->after('needs_marketing');
            $table->boolean('needs_seo')->default(false)->after('needs_ecommerce');
            $table->boolean('needs_social_media')->default(false)->after('needs_seo');
            $table->string('business_segment', 100)->nullable()->after('needs_social_media');
            $table->string('company_size', 30)->nullable()->after('business_segment');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table): void {
            $table->dropColumn([
                'strengths', 'weaknesses', 'opportunities',
                'needs_website', 'needs_system', 'needs_marketing',
                'needs_ecommerce', 'needs_seo', 'needs_social_media',
                'business_segment', 'company_size',
            ]);
        });
    }
};
