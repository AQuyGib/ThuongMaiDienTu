<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('sold_count');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('extra_price');
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->increments('movement_id');
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('variant_id')->nullable();
            $table->unsignedInteger('order_id')->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->unsignedInteger('reference_id')->nullable();
            $table->enum('type', ['sale', 'restock', 'adjustment', 'import', 'return']);
            $table->integer('quantity_change');
            $table->integer('before_stock');
            $table->integer('after_stock');
            $table->string('note', 255)->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('product_id')->on('products')->nullOnDelete();
            $table->foreign('variant_id')->references('variant_id')->on('product_variants')->nullOnDelete();
            $table->foreign('order_id')->references('order_id')->on('orders')->nullOnDelete();
            $table->foreign('created_by')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('stock');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};
