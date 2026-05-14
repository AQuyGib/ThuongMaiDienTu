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
            $table->string('ram')->nullable();
            $table->string('rom')->nullable();
            $table->string('cpu')->nullable();
            $table->string('gpu')->nullable();
            $table->string('screen')->nullable();
            $table->string('os')->nullable();
            $table->string('camera')->nullable();
            $table->string('battery')->nullable();
            $table->string('sim')->nullable();
            $table->string('connection')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->json('specifications')->nullable();
            $table->integer('discount_percent')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('review_count')->default(0);
            $table->integer('view_count')->default(0);
            $table->integer('sold_count')->default(0);
            $table->boolean('status')->default(1);
            $table->boolean('hot_flag')->default(0);
            $table->softDeletes('deleted_at');


            $table->foreign('category_id')->references('category_id')->on('categories')->onDelete('restrict');
        });
    }
    public function down() { Schema::dropIfExists('products'); }
};