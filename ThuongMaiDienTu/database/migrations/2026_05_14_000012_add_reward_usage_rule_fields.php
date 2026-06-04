<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reward_catalog', function (Blueprint $table) {
            $table->boolean('requires_rank_check')->default(false)->after('min_rank_points');
        });
    }

    public function down(): void
    {
        Schema::table('reward_catalog', function (Blueprint $table) {
            $table->dropColumn('requires_rank_check');
        });
    }
};
