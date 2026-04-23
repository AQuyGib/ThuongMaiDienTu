<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->increments('spec_id');
            $table->unsignedInteger('product_id');
            $table->string('cpu_chip', 255)->nullable();
            $table->string('ram_capacity', 255)->nullable();
            $table->string('battery', 50)->nullable();
            $table->string('screen_size', 100)->nullable();
            $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }
    public function down() { Schema::dropIfExists('product_specifications'); }
};