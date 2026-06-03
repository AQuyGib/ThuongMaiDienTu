<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->json('tags')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->integer('seo_score')->nullable();
            $table->integer('ai_quality_score')->nullable();
            $table->string('ai_moderation_verdict', 50)->nullable();
            $table->json('ai_analysis')->nullable();
            $table->boolean('ai_checked')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'tags',
                'seo_title',
                'seo_description',
                'seo_keywords',
                'seo_score',
                'ai_quality_score',
                'ai_moderation_verdict',
                'ai_analysis',
                'ai_checked'
            ]);
        });
    }
};
