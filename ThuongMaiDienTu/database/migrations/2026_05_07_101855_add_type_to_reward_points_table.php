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
        Schema::table('reward_points', function (Blueprint $table) {
            $table->enum('type', ['earned', 'spent'])->default('earned')->after('points');
            $table->timestamps(); // Thêm luôn timestamps cho dễ theo dõi
        });
    }

    public function down(): void
    {
        Schema::table('reward_points', function (Blueprint $table) {
            $table->dropColumn(['type', 'created_at', 'updated_at']);
        });
    }
};
