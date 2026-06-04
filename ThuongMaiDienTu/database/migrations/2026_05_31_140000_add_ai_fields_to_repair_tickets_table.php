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
        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->string('device_image', 255)->nullable()->after('issue_desc');
            $table->boolean('ai_diagnosed')->default(0)->after('device_image');
            $table->string('ai_fault_type', 50)->nullable()->after('ai_diagnosed');
            $table->text('ai_probable_causes')->nullable()->after('ai_fault_type');
            $table->text('ai_risk_warnings')->nullable()->after('ai_probable_causes');
            $table->text('ai_replacement_parts')->nullable()->after('ai_risk_warnings');
            $table->unsignedBigInteger('ai_estimated_cost_min')->nullable()->after('ai_replacement_parts');
            $table->unsignedBigInteger('ai_estimated_cost_max')->nullable()->after('ai_estimated_cost_min');
            $table->string('ai_complexity_level', 50)->nullable()->after('ai_estimated_cost_max');
            $table->text('ai_recommended_skills')->nullable()->after('ai_complexity_level');
            $table->text('ai_dispatch_reason')->nullable()->after('ai_recommended_skills');
            $table->dateTime('ai_diagnosed_at')->nullable()->after('ai_dispatch_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repair_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'device_image',
                'ai_diagnosed',
                'ai_fault_type',
                'ai_probable_causes',
                'ai_risk_warnings',
                'ai_replacement_parts',
                'ai_estimated_cost_min',
                'ai_estimated_cost_max',
                'ai_complexity_level',
                'ai_recommended_skills',
                'ai_dispatch_reason',
                'ai_diagnosed_at'
            ]);
        });
    }
};
