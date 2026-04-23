<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('order_details', function (Blueprint $table) {
            $table->increments('detail_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('item_id');
            $table->unsignedBigInteger('price');
            $table->foreign('order_id')->references('order_id')->on('orders')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('inventory_items')->onDelete('restrict');
        });
    }
    public function down() { Schema::dropIfExists('order_details'); }
};