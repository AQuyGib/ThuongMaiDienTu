<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Sử dụng DB::statement vì Laravel không hỗ trợ cập nhật enum trực tiếp qua Blueprint một cách tốt nhất trên mọi DB
        DB::statement("ALTER TABLE users MODIFY COLUMN member_tier ENUM('Dong', 'Bac', 'Vang', 'KimCuong') DEFAULT 'Dong'");
    }

    public function down(): void
    {
        // Khi quay lại, có thể có data 'KimCuong', cần cân nhắc trước khi chạy down
        DB::statement("ALTER TABLE users MODIFY COLUMN member_tier ENUM('Dong', 'Bac', 'Vang') DEFAULT 'Dong'");
    }
};
