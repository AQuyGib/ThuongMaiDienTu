<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Thêm cột ram & image_url vào bảng product_variants
     * - ram: dung lượng RAM (VD: 8GB, 12GB)
     * - image_url: URL ảnh riêng cho biến thể
     */
    public function up() {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->string('ram', 20)->nullable()->after('color');
            $table->string('image_url', 500)->nullable()->after('extra_price');
        });
    }

    public function down() {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['ram', 'image_url']);
        });
    }
};
