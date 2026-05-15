<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reward_catalog', function (Blueprint $table) {
            $table->string('image_path', 255)->nullable()->after('shipping_discount_amount');
            $table->string('thumbnail_path', 255)->nullable()->after('image_path');
            $table->unsignedBigInteger('max_per_user')->default(1)->after('stock');
            $table->unsignedBigInteger('min_rank_points')->default(0)->after('max_per_user');
        });
    }

    public function down(): void
    {
        Schema::table('reward_catalog', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'thumbnail_path', 'max_per_user', 'min_rank_points']);
        });
    }
};
