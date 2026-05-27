<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('wallet_points_earned')->default(0)->after('final_amount');
            $table->unsignedBigInteger('rank_points_earned')->default(0)->after('wallet_points_earned');
            $table->unsignedBigInteger('wallet_points_used')->default(0)->after('rank_points_earned');
            $table->enum('points_status', ['pending', 'processed', 'refunded', 'cancelled'])->default('pending')->after('wallet_points_used');
            $table->timestamp('points_processed_at')->nullable()->after('points_status');

            $table->index(['user_id', 'points_status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'points_status']);
            $table->dropColumn([
                'wallet_points_earned',
                'rank_points_earned',
                'wallet_points_used',
                'points_status',
                'points_processed_at',
            ]);
        });
    }
};
