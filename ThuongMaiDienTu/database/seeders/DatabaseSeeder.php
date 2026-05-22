<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed dữ liệu mẫu cho toàn bộ ứng dụng.
     * Thứ tự chạy: Roles trước -> Users sau (do khóa ngoại role_id).
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SupplierSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ProductDetailSeeder::class,
        ]);
    }
}
