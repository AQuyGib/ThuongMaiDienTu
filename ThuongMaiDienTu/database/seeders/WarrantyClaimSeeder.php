<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\WarrantyClaim;
use App\Models\User;

/**
 * WarrantyClaimSeeder
 *
 * Tạo dữ liệu mẫu đa dạng cho bảng warranty_claims:
 *  - Bao gồm các trạng thái: pending, approved, rejected
 *  - Bao gồm các loại yêu cầu: warranty (bảo hành), return (đổi trả), exchange (đổi máy)
 *  - Mỗi claim liên kết với IMEI/Serial thực sự tồn tại trong bảng inventory_items
 *  - Một số claim có user_id (khách đăng nhập), một số để null (khách vãng lai)
 */
class WarrantyClaimSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tối đa 10 inventory items để có đủ IMEI thực
        $items = InventoryItem::limit(10)->get();

        if ($items->isEmpty()) {
            $this->command->warn('WarrantyClaimSeeder: Không có inventory_items nào, bỏ qua seeder.');
            return;
        }

        // Lấy user đầu tiên (nếu có) để gán vào một số claim
        $user = User::first();

        // ─────────────────────────────────────────────
        // Dữ liệu mẫu – mỗi phần tử là 1 warranty claim
        // ─────────────────────────────────────────────
        $samples = [
            // 1. Bảo hành – đang chờ duyệt
            [
                'imei_index'    => 0,
                'customer_name' => 'Nguyễn Văn An',
                'customer_phone'=> '0912345678',
                'customer_email'=> 'nguyenvanan@gmail.com',
                'claim_type'    => 'warranty',
                'reason'        => 'Màn hình bị sọc dọc sau 2 tuần sử dụng bình thường, không va đập.',
                'status'        => 'pending',
                'admin_note'    => null,
            ],
            // 2. Đổi trả – đã được duyệt
            [
                'imei_index'    => 1,
                'customer_name' => 'Trần Thị Bình',
                'customer_phone'=> '0987654321',
                'customer_email'=> 'tranthib@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Sản phẩm giao không đúng màu sắc như tôi đã đặt trên website (đặt đen, nhận trắng).',
                'status'        => 'approved',
                'admin_note'    => 'Đã xác nhận lỗi giao hàng. Đã duyệt hoàn tiền 100% cho khách.',
            ],
            // 3. Đổi máy – đã từ chối
            [
                'imei_index'    => 2,
                'customer_name' => 'Lê Văn Cường',
                'customer_phone'=> '0905123456',
                'customer_email'=> null,
                'claim_type'    => 'exchange',
                'reason'        => 'Muốn đổi sang dòng máy có RAM cao hơn, sẵn sàng bù thêm tiền.',
                'status'        => 'rejected',
                'admin_note'    => 'Chính sách không hỗ trợ đổi sang sản phẩm khác loại sau 14 ngày.',
            ],
            // 4. Bảo hành – chờ duyệt (khách vãng lai, không có email)
            [
                'imei_index'    => 3,
                'customer_name' => 'Phạm Thị Dung',
                'customer_phone'=> '0934567890',
                'customer_email'=> null,
                'claim_type'    => 'warranty',
                'reason'        => 'Pin xuống nhanh bất thường, chỉ sau 1 ngày sạc đã hết pin dù sử dụng nhẹ.',
                'status'        => 'pending',
                'admin_note'    => null,
            ],
            // 5. Đổi trả – đang chờ duyệt
            [
                'imei_index'    => 4,
                'customer_name' => 'Hoàng Minh Đức',
                'customer_phone'=> '0967890123',
                'customer_email'=> 'hoanghmduc@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Máy bị lỗi camera trước từ khi mở hộp, hình ảnh bị mờ hoàn toàn.',
                'status'        => 'pending',
                'admin_note'    => null,
            ],
            // 6. Bảo hành – đã duyệt với ghi chú kỹ thuật
            [
                'imei_index'    => min(5, $items->count() - 1),
                'customer_name' => 'Vũ Thị Thanh Hoa',
                'customer_phone'=> '0911222333',
                'customer_email'=> 'vuhoathanh@outlook.com',
                'claim_type'    => 'warranty',
                'reason'        => 'Loa ngoài phát ra tiếng rè khi bật volume trên 50%, thử trên nhiều ứng dụng đều bị.',
                'status'        => 'approved',
                'admin_note'    => 'Kỹ thuật viên xác nhận lỗi bo mạch loa. Đã tiếp nhận, dự kiến trả máy trong 5-7 ngày làm việc.',
            ],
            // 7. Đổi máy – đang chờ xử lý
            [
                'imei_index'    => min(6, $items->count() - 1),
                'customer_name' => 'Ngô Quang Huy',
                'customer_phone'=> '0944556677',
                'customer_email'=> 'ngoquanghuy99@gmail.com',
                'claim_type'    => 'exchange',
                'reason'        => 'Máy bị lỗi WiFi không kết nối được từ hôm mua về, đã thử reset nhưng không được.',
                'status'        => 'pending',
                'admin_note'    => null,
            ],
            // 8. Bảo hành – từ chối do hết hạn
            [
                'imei_index'    => min(7, $items->count() - 1),
                'customer_name' => 'Đinh Thị Kim Liên',
                'customer_phone'=> '0977334455',
                'customer_email'=> null,
                'claim_type'    => 'warranty',
                'reason'        => 'Màn hình bị chết điểm ảnh ở góc trên bên phải.',
                'status'        => 'rejected',
                'admin_note'    => 'Sản phẩm đã vượt quá thời hạn bảo hành 12 tháng. Hỗ trợ sửa chữa theo giá phụ tùng.',
            ],
            // 9. Đổi trả – đã duyệt (đổi máy mới cùng model)
            [
                'imei_index'    => min(8, $items->count() - 1),
                'customer_name' => 'Trịnh Quốc Minh',
                'customer_phone'=> '0922113344',
                'customer_email'=> 'trinhminh@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Máy bị treo logo khi khởi động, không vào được hệ thống. Mua được 3 ngày.',
                'status'        => 'approved',
                'admin_note'    => 'Lỗi firmware xuất xưởng. Đã đổi máy mới cùng model và màu sắc cho khách.',
            ],
            // 10. Bảo hành – chờ duyệt (khách đăng nhập hệ thống)
            [
                'imei_index'    => min(9, $items->count() - 1),
                'customer_name' => 'Bùi Thị Ngọc',
                'customer_phone'=> '0933221100',
                'customer_email'=> $user ? $user->email : 'buingoc@gmail.com',
                'claim_type'    => 'warranty',
                'reason'        => 'Nút nguồn bị kẹt, không bật/tắt được, phải cắm sạc mới vào được máy.',
                'status'        => 'pending',
                'admin_note'    => null,
                'use_user'      => true, // gán user_id nếu có user
            ],
        ];

        $created = 0;
        foreach ($samples as $sample) {
            $index = $sample['imei_index'];

            // Bỏ qua nếu không có inventory item tương ứng
            if (!isset($items[$index])) {
                continue;
            }

            // Kiểm tra xem claim đã tồn tại chưa (tránh duplicate khi seed nhiều lần)
            $exists = WarrantyClaim::where('imei_serial', $items[$index]->imei_serial)
                ->where('claim_type', $sample['claim_type'])
                ->exists();

            if ($exists) {
                continue;
            }

            WarrantyClaim::create([
                'user_id'        => (!empty($sample['use_user']) && $user) ? $user->user_id : null,
                'imei_serial'    => $items[$index]->imei_serial,
                'customer_name'  => $sample['customer_name'],
                'customer_phone' => $sample['customer_phone'],
                'customer_email' => $sample['customer_email'],
                'claim_type'     => $sample['claim_type'],
                'reason'         => $sample['reason'],
                'status'         => $sample['status'],
                'admin_note'     => $sample['admin_note'],
            ]);

            $created++;
        }

        $this->command->info("WarrantyClaimSeeder: Đã tạo {$created} warranty claim mẫu.");
    }
}
