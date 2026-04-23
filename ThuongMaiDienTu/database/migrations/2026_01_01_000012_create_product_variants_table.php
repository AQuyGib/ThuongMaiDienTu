<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->increments('variant_id');
            $table->unsignedInteger('product_id');
            $table->string('color', 30)->nullable();
            $table->string('rom_capacity', 20)->nullable();
            $table->unsignedBigInteger('extra_price')->default(0);
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('product_variants'); }
};