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
        Schema::create('product_cross_sells', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('cross_sell_id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Index để tăng tốc độ truy vấn
            $table->index(['product_id', 'sort_order']);
            
            // Foreign keys (Optional - tùy thuộc vào việc có dùng SoftDeletes hay không)
            // $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_cross_sells');
    }
};
