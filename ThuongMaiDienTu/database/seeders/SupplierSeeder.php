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
                'name' => 'Samsung Việt Nam',
                'phone' => '0901234567',
                'email' => 'contact@samsung.vn',
                'address' => 'Lô I-11, Đường D1, Khu Công Nghệ Cao, TP. Thủ Đức, TP.HCM',
            ],
            [
                'name' => 'Apple Authorized Distributor',
                'phone' => '0912345678',
                'email' => 'sales@appledist.vn',
                'address' => '123 Nguyễn Huệ, Quận 1, TP.HCM',
            ],
            [
                'name' => 'Xiaomi Việt Nam',
                'phone' => '0987654321',
                'email' => 'support@xiaomi.vn',
                'address' => '45 Lê Lợi, Quận 1, TP.HCM',
            ],
            [
                'name' => 'LG Electronics VN',
                'phone' => '0903456789',
                'email' => 'info@lgvn.vn',
                'address' => 'KCN Biên Hòa 2, Đồng Nai',
            ],
            [
                'name' => 'Panasonic Việt Nam',
                'phone' => '0934567890',
                'email' => 'contact@panasonic.vn',
                'address' => 'Tầng 8, Tòa nhà Saigon Centre, TP.HCM',
            ],
            [
                'name' => 'Sony Việt Nam',
                'phone' => '0945678901',
                'email' => 'hello@sony.vn',
                'address' => '235 Nguyễn Văn Cừ, Quận 5, TP.HCM',
            ],
            [
                'name' => 'Toshiba Asia',
                'phone' => '0961234789',
                'email' => 'sales@toshiba.asia',
                'address' => 'Khu công nghiệp VSIP, Bình Dương',
            ],
            [
                'name' => 'Acer Distribution',
                'phone' => '0972345891',
                'email' => 'admin@acerdist.vn',
                'address' => '88 Phạm Văn Đồng, Hà Nội',
            ],
            [
                'name' => 'Lenovo Partner VN',
                'phone' => '0981234789',
                'email' => 'partner@lenovo.vn',
                'address' => '12 Trần Hưng Đạo, Đà Nẵng',
            ],
            [
                'name' => 'HP Việt Nam',
                'phone' => '0909876543',
                'email' => 'contact@hpvn.vn',
                'address' => '51 Trường Chinh, Hà Nội',
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
