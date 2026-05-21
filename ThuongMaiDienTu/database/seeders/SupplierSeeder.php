<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Công ty TNHH Apple Việt Nam',
                'phone' => '18001127',
                'email' => 'contact@apple.com.vn',
                'address' => 'Tòa nhà Ngôi Nhà Đức, 33 Lê Duẩn, Quận 1, TP. HCM',
            ],
            [
                'name' => 'Công ty TNHH Samsung Electronics Việt Nam',
                'phone' => '1800588889',
                'email' => 'support@samsung.com.vn',
                'address' => 'KCN Yên Phong, Yên Trung, Yên Phong, Bắc Ninh',
            ],
            [
                'name' => 'Nhà phân phối FPT Retail',
                'phone' => '18006601',
                'email' => 'fptretail@fpt.com.vn',
                'address' => '261 - 263 Khánh Hội, Phường 5, Quận 4, TP. HCM',
            ],
            [
                'name' => 'Công ty Cổ phần Thế Giới Di Động',
                'phone' => '18001060',
                'email' => 'lienhe@thegioididong.com',
                'address' => '128 Trần Quang Khải, Phường Tân Định, Quận 1, TP. HCM',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(
                ['name' => $supplier['name']],
                $supplier
            );
        }
    }
}
