<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->enum('order_type', ['Online', 'POS']);
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('shipping_fee')->default(0);
            $table->unsignedBigInteger('final_amount');
            $table->enum('payment_method', ['COD', 'VNPAY', 'MoMo', 'Cash_POS', 'Installment']);
            $table->string('shipping_partner', 50)->nullable();
            $table->string('tracking_code', 50)->nullable();
            $table->enum('status', ['Pending', 'Shipping', 'Delivered'])->default('Pending');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }
    public function down() { Schema::dropIfExists('orders'); }
};