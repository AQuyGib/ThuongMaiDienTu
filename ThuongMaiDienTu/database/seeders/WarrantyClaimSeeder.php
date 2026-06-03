<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\WarrantyClaim;
use App\Models\User;

class WarrantyClaimSeeder extends Seeder
{
    public function run()
    {
        $items = InventoryItem::limit(3)->get();
        $user = User::first();

        if ($items->count() >= 1) {
            WarrantyClaim::create([
                'user_id' => $user ? $user->user_id : null,
                'imei_serial' => $items[0]->imei_serial,
                'customer_name' => 'Nguyễn Văn A',
                'customer_phone' => '0912345678',
                'customer_email' => 'nguyenvana@example.com',
                'claim_type' => 'warranty',
                'reason' => 'Màn hình bị sọc dọc sau 2 tuần sử dụng bình thường.',
                'status' => 'pending',
            ]);
        }

        if ($items->count() >= 2) {
            WarrantyClaim::create([
                'user_id' => $user ? $user->user_id : null,
                'imei_serial' => $items[1]->imei_serial,
                'customer_name' => 'Trần Thị B',
                'customer_phone' => '0987654321',
                'customer_email' => 'tranthib@example.com',
                'claim_type' => 'return',
                'reason' => 'Sản phẩm không đúng màu sắc như đơn hàng đặt mua.',
                'status' => 'approved',
                'admin_note' => 'Đã duyệt yêu cầu đổi trả hàng hoàn tiền.',
            ]);
        }

        if ($items->count() >= 3) {
            WarrantyClaim::create([
                'user_id' => $user ? $user->user_id : null,
                'imei_serial' => $items[2]->imei_serial,
                'customer_name' => 'Lê Văn C',
                'customer_phone' => '0905123456',
                'customer_email' => 'levanc@example.com',
                'claim_type' => 'exchange',
                'reason' => 'Muốn đổi sang dòng máy có dung lượng cao hơn.',
                'status' => 'rejected',
                'admin_note' => 'Cửa hàng không hỗ trợ đổi sang sản phẩm khác loại sau thời gian quy định.',
            ]);
        }
    }
}
