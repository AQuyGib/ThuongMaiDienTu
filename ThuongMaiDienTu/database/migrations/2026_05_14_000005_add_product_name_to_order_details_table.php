<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            if (! Schema::hasColumn('order_details', 'product_name')) {
                $table->string('product_name', 255)->nullable()->after('item_id');
            }
            if (! Schema::hasColumn('order_details', 'quantity')) {
                $table->unsignedInteger('quantity')->default(1)->after('product_name');
            }
            if (! Schema::hasColumn('order_details', 'unit_price')) {
                $table->unsignedBigInteger('unit_price')->default(0)->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            foreach (['product_name', 'quantity', 'unit_price'] as $column) {
                if (Schema::hasColumn('order_details', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
