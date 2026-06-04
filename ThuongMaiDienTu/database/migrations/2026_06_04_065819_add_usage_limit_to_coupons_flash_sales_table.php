<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons_flash_sales', function (Blueprint $table) {
            $table->unsignedInteger('usage_limit')->nullable()->after('end_time')->comment('Giới hạn lượt dùng, null = không giới hạn');
            $table->unsignedInteger('times_used')->default(0)->after('usage_limit')->comment('Số lượt đã dùng');
        });
    }

    public function down(): void
    {
        Schema::table('coupons_flash_sales', function (Blueprint $table) {
            $table->dropColumn(['usage_limit', 'times_used']);
        });
    }
};

