<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('product_id');
            $table->unsignedInteger('category_id');
            $table->string('name', 150);
            $table->string('thumbnail', 255)->nullable();
            $table->string('seo_description', 255)->nullable();
            $table->unsignedBigInteger('base_price');
            $table->unsignedBigInteger('old_price')->nullable();
            $table->softDeletes('deleted_at');
            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('restrict');
        });
    }
    public function down() { Schema::dropIfExists('products'); }
};