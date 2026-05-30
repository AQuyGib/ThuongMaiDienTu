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
        Schema::table('products', function (Blueprint $table) {
            $table->integer('safe_stock')->default(5)->after('base_price');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('safe_stock')->default(5)->after('extra_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('safe_stock');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('safe_stock');
        });
    }
};
