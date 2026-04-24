<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->increments('po_id');
            $table->unsignedInteger('supplier_id');
            $table->unsignedBigInteger('total_cost');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers')->onDelete('restrict');
        });
    }
    public function down() { Schema::dropIfExists('purchase_orders'); }
};