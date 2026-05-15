<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flash_sales', function (Blueprint $table) {
            $table->increments('flash_sale_id');
            $table->string('name', 150);
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'start_at', 'end_at']);
        });

        Schema::create('flash_sale_products', function (Blueprint $table) {
            $table->increments('flash_sale_product_id');
            $table->unsignedInteger('flash_sale_id');
            $table->unsignedInteger('product_id');
            $table->unsignedBigInteger('sale_price');
            $table->unsignedInteger('stock_limit')->default(0);
            $table->unsignedInteger('sold_quantity')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('flash_sale_id')
                ->references('flash_sale_id')
                ->on('flash_sales')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('product_id')
                ->on('products')
                ->onDelete('cascade');

            $table->unique(['flash_sale_id', 'product_id']);
            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flash_sale_products');
        Schema::dropIfExists('flash_sales');
    }
};
