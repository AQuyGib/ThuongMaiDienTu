<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('coupons_flash_sales', function (Blueprint $table) {
            $table->increments('promo_id');
            $table->enum('promo_type', ['Coupon', 'FlashSale']);
            $table->string('code', 50)->nullable();
            $table->unsignedBigInteger('discount_val');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
        });
    }
    public function down() { Schema::dropIfExists('coupons_flash_sales'); }
};