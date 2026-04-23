<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->increments('item_id');
            $table->unsignedInteger('variant_id');
            $table->unsignedInteger('po_id');
            $table->string('imei_serial', 30)->unique();
            $table->string('warehouse_loc', 50)->nullable();
            $table->enum('status', ['In_Stock', 'Sold', 'Defective'])->default('In_Stock');
            $table->foreign('variant_id')->references('variant_id')->on('product_variants')->onDelete('restrict');
            $table->foreign('po_id')->references('po_id')->on('purchase_orders')->onDelete('restrict');
        });
    }
    public function down() { Schema::dropIfExists('inventory_items'); }
};