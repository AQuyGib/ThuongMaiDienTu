<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Mở rộng enum type thêm 'Compare' cho tính năng so sánh sản phẩm
        DB::statement("ALTER TABLE wishlists_recently_viewed MODIFY COLUMN type ENUM('Wishlist', 'Viewed', 'Compare')");
    }

    public function down(): void
    {
        // Xóa các record Compare trước khi thu hẹp enum
        DB::table('wishlists_recently_viewed')->where('type', 'Compare')->delete();
        DB::statement("ALTER TABLE wishlists_recently_viewed MODIFY COLUMN type ENUM('Wishlist', 'Viewed')");
    }
};
