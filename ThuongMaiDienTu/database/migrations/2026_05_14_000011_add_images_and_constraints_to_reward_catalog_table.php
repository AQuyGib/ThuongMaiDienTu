<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reward_catalog', function (Blueprint $table) {
            // Kiểm tra trước khi thêm để tránh lỗi Duplicate column
            if (!Schema::hasColumn('reward_catalog', 'max_per_user')) {
                $table->unsignedBigInteger('max_per_user')->default(1)->after('stock');
            }
            if (!Schema::hasColumn('reward_catalog', 'min_rank_points')) {
                $table->unsignedBigInteger('min_rank_points')->default(0)->after('max_per_user');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reward_catalog', function (Blueprint $table) {
            $table->dropColumn(['max_per_user', 'min_rank_points']);
        });
    }
};
