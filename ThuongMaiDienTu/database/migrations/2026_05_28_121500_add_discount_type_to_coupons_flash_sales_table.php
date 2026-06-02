<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('coupons_flash_sales', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons_flash_sales', 'discount_type')) {
                $table->string('discount_type', 20)->default('fixed')->after('code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons_flash_sales', function (Blueprint $table) {
            if (Schema::hasColumn('coupons_flash_sales', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
        });
    }
};
