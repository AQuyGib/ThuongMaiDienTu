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
 *  - Gồm 20 bản ghi thử nghiệm với đầy đủ các trạng thái và nghiệp vụ
 *  - Bao gồm các trạng thái: pending (chờ duyệt), approved (đã duyệt), rejected (từ chối)
 *  - Bao gồm các loại yêu cầu: warranty (bảo hành), return (đổi trả), exchange (đổi máy)
 *  - Mỗi claim liên kết với IMEI/Serial thực sự tồn tại trong bảng inventory_items
 *  - Một số claim có user_id (khách đăng nhập), một số để null (khách vãng lai)
 *  - Tích hợp các trường hoàn tiền: refund_method, bank_name, bank_account_number, bank_account_name
 */
class WarrantyClaimSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tối đa 25 inventory items để có đủ IMEI thực cho 20 bản ghi
        $items = InventoryItem::limit(25)->get();

        if ($items->isEmpty()) {
            $this->command->warn('WarrantyClaimSeeder: Không có inventory_items nào, bỏ qua seeder.');
            return;
        }

        // Lấy user đầu tiên (nếu có) để gán vào một số claim
        $user = User::first();

        // ─────────────────────────────────────────────
        // 20 DỮ LIỆU MẪU CHI TIẾT
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
            // 2. Đổi trả – đã được duyệt (Hoàn tiền qua Vietcombank)
            [
                'imei_index'    => 1,
                'customer_name' => 'Trần Thị Bình',
                'customer_phone'=> '0987654321',
                'customer_email'=> 'tranthib@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Sản phẩm giao không đúng màu sắc như tôi đã đặt trên website (đặt đen, nhận trắng).',
                'status'        => 'approved',
                'admin_note'    => 'Đã xác nhận lỗi giao hàng. Đã duyệt hoàn tiền 100% cho khách.',
                'refund_method' => 'bank_transfer',
                'refund_amount' => 15000000,
                'bank_name'     => 'Vietcombank',
                'bank_account_number' => '0011004123456',
                'bank_account_name'   => 'TRAN THI BINH',
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
            // 5. Đổi trả – đang chờ duyệt (Hoàn tiền mặt)
            [
                'imei_index'    => 4,
                'customer_name' => 'Hoàng Minh Đức',
                'customer_phone'=> '0967890123',
                'customer_email'=> 'hoanghmduc@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Máy bị lỗi camera trước từ khi mở hộp, hình ảnh bị mờ hoàn toàn.',
                'status'        => 'pending',
                'admin_note'    => null,
                'refund_method' => 'cash',
                'refund_amount' => 12500000,
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
            // 9. Đổi trả – đã duyệt (Hoàn tiền qua Techcombank)
            [
                'imei_index'    => min(8, $items->count() - 1),
                'customer_name' => 'Trịnh Quốc Minh',
                'customer_phone'=> '0922113344',
                'customer_email'=> 'trinhminh@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Máy bị treo logo khi khởi động, không vào được hệ thống. Mua được 3 ngày.',
                'status'        => 'approved',
                'admin_note' => 'Lỗi firmware xuất xưởng. Đã đổi máy mới cùng model và màu sắc cho khách.',
                'refund_method' => 'bank_transfer',
                'refund_amount' => 24500000,
                'bank_name'     => 'Techcombank',
                'bank_account_number' => '1903456789012',
                'bank_account_name'   => 'TRINH QUOC MINH',
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
                'use_user'      => true,
            ],
            // 11. Đổi trả – đang chờ duyệt (Hoàn tiền qua VietinBank)
            [
                'imei_index'    => min(10, $items->count() - 1),
                'customer_name' => 'Phan Thanh Hải',
                'customer_phone'=> '0915667788',
                'customer_email'=> 'hai.phan@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Không tương thích với thiết bị ngoại vi của tôi dù đã được nhân viên tư vấn là có.',
                'status'        => 'pending',
                'refund_method' => 'bank_transfer',
                'refund_amount' => 8700000,
                'bank_name'     => 'VietinBank',
                'bank_account_number' => '101004567890',
                'bank_account_name'   => 'PHAN THANH HAI',
            ],
            // 12. Bảo hành – đã duyệt
            [
                'imei_index'    => min(11, $items->count() - 1),
                'customer_name' => 'Đặng Hồng Hạnh',
                'customer_phone'=> '0981223344',
                'customer_email'=> 'hanh.dh@outlook.com',
                'claim_type'    => 'warranty',
                'reason'        => 'Cảm ứng bị loạn ở khu vực giữa màn hình khi máy nóng lên.',
                'status'        => 'approved',
                'admin_note'    => 'Đã nhận máy bảo hành, tiến hành thay thế màn hình mới chính hãng.',
            ],
            // 13. Đổi máy – đã duyệt
            [
                'imei_index'    => min(12, $items->count() - 1),
                'customer_name' => 'Lâm Minh Triết',
                'customer_phone'=> '0903334455',
                'customer_email'=> null,
                'claim_type'    => 'exchange',
                'reason'        => 'Sản phẩm phát sinh lỗi ổ cứng liên tục, đã bảo hành 2 lần vẫn lỗi.',
                'status'        => 'approved',
                'admin_note'    => 'Đã chấp nhận đổi sản phẩm mới cùng loại cho khách.',
            ],
            // 14. Đổi trả – đã từ chối (Hoàn tiền qua MB Bank)
            [
                'imei_index'    => min(13, $items->count() - 1),
                'customer_name' => 'Cao Việt Bách',
                'customer_phone'=> '0945998877',
                'customer_email'=> 'bach.cao@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Dùng thử thấy không thích thiết kế của máy nữa nên muốn trả lại.',
                'status'        => 'rejected',
                'admin_note'    => 'Từ chối do lý do trả hàng xuất phát từ nhu cầu cá nhân chủ quan, không phải lỗi kỹ thuật.',
                'refund_method' => 'bank_transfer',
                'refund_amount' => 19000000,
                'bank_name'     => 'MB Bank',
                'bank_account_number' => '970422123456789',
                'bank_account_name'   => 'CAO VIET BACH',
            ],
            // 15. Bảo hành – đang chờ duyệt
            [
                'imei_index'    => min(14, $items->count() - 1),
                'customer_name' => 'Đỗ Thùy Trang',
                'customer_phone'=> '0966442200',
                'customer_email'=> 'trangdo97@gmail.com',
                'claim_type'    => 'warranty',
                'reason'        => 'Face ID chập chờn, lúc nhận diện được lúc báo lỗi không khả dụng.',
                'status'        => 'pending',
            ],
            // 16. Đổi trả – đã duyệt (Hoàn tiền mặt)
            [
                'imei_index'    => min(15, $items->count() - 1),
                'customer_name' => 'Nguyễn Hoàng Nam',
                'customer_phone'=> '0912999000',
                'customer_email'=> 'nam.nguyenh@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Máy liên tục tự động khởi động lại khoảng 5-10 phút một lần dù chỉ lướt web.',
                'status'        => 'approved',
                'admin_note'    => 'Kỹ thuật viên xác nhận lỗi chạm nguồn trên bo mạch chính. Hoàn tiền mặt tại chi nhánh.',
                'refund_method' => 'cash',
                'refund_amount' => 13500000,
            ],
            // 17. Đổi máy – đã từ chối
            [
                'imei_index'    => min(16, $items->count() - 1),
                'customer_name' => 'Võ Minh Quân',
                'customer_phone'=> '0989332211',
                'customer_email'=> null,
                'claim_type'    => 'exchange',
                'reason'        => 'Muốn đổi máy vì người nhà không ưng ý màu sắc này nữa.',
                'status'        => 'rejected',
                'admin_note'    => 'Sản phẩm đã khui hộp và kích hoạt sử dụng, không hỗ trợ đổi máy do nhu cầu thẩm mỹ cá nhân.',
            ],
            // 18. Bảo hành – đã duyệt
            [
                'imei_index'    => min(17, $items->count() - 1),
                'customer_name' => 'Trương Khánh Quỳnh',
                'customer_phone'=> '0901239876',
                'customer_email'=> 'quynh.tk@yahoo.com',
                'claim_type'    => 'warranty',
                'reason'        => 'Cổng sạc lỏng lẻo, cắm sạc chập chờn và không nhận sạc nhanh.',
                'status'        => 'approved',
                'admin_note'    => 'Đã nhận máy bảo hành, kỹ thuật viên tiến hành vệ sinh và hàn lại chân sạc.',
            ],
            // 19. Đổi trả – đang chờ duyệt (Hoàn tiền mặt)
            [
                'imei_index'    => min(18, $items->count() - 1),
                'customer_name' => 'Phùng Tiến Đạt',
                'customer_phone'=> '0975661122',
                'customer_email'=> 'dat.pt@gmail.com',
                'claim_type'    => 'return',
                'reason'        => 'Màn hình bị ám vàng rất nặng so với các thiết bị cùng dòng trưng bày tại shop.',
                'status'        => 'pending',
                'refund_method' => 'cash',
                'refund_amount' => 11000000,
            ],
            // 20. Đổi máy – đang chờ xử lý
            [
                'imei_index'    => min(19, $items->count() - 1),
                'customer_name' => 'Lý Gia Hân',
                'customer_phone'=> '0931445566',
                'customer_email'=> 'han.lg@gmail.com',
                'claim_type'    => 'exchange',
                'reason'        => 'Cảm biến xoay màn hình bị đơ hoàn toàn không xoay ngang được khi xem video.',
                'status'        => 'pending',
            ],
        ];

        $created = 0;
        foreach ($samples as $sample) {
            $index = $sample['imei_index'];

            // Bỏ qua nếu không có inventory item tương ứng
            if (!isset($items[$index])) {
                continue;
            }

            $item = $items[$index];

            // 1. Đảm bảo trạng thái thiết bị là Sold
            $item->update(['status' => 'Sold']);

            // 2. Thiết lập thông số bảo hành mẫu cho IMEI này hợp lệ với logic nghiệp vụ
            $startDaysAgo = 10;
            $durationMonths = 12;
            $warrantyStatus = 'active';

            if ($index == 2) {
                // Lê Văn Cường - đổi trả bị từ chối do quá 30 ngày (ví dụ 45 ngày trước)
                $startDaysAgo = 45;
            } elseif ($index == 5) {
                // Vũ Thị Thanh Hoa - bảo hành đã duyệt từ 60 ngày trước
                $startDaysAgo = 60;
            } elseif ($index == 7) {
                // Đinh Thị Kim Liên - từ chối do hết hạn bảo hành
                $startDaysAgo = 400;
                $warrantyStatus = 'expired';
            } elseif ($index == 9) {
                // Bùi Thị Ngọc - bảo hành chờ duyệt từ 30 ngày trước
                $startDaysAgo = 30;
            }

            $startDate = \Carbon\Carbon::now()->subDays($startDaysAgo);
            $endDate = (clone $startDate)->addMonths($durationMonths);

            \App\Models\Warranty::updateOrCreate(
                ['item_id' => $item->item_id],
                [
                    'start_date'      => $startDate->toDateString(),
                    'end_date'        => $endDate->toDateString(),
                    'warranty_status' => $warrantyStatus,
                    'warranty_type'   => 'manufacturer',
                    'note'            => 'Bảo hành mẫu từ hệ thống seeder.',
                ]
            );

            // Kiểm tra xem claim đã tồn tại chưa (tránh duplicate khi seed nhiều lần)
            $exists = WarrantyClaim::where('imei_serial', $item->imei_serial)
                ->where('claim_type', $sample['claim_type'])
                ->exists();

            if ($exists) {
                continue;
            }

            WarrantyClaim::create([
                'user_id'        => (!empty($sample['use_user']) && $user) ? $user->user_id : null,
                'imei_serial'    => $item->imei_serial,
                'customer_name'  => $sample['customer_name'],
                'customer_phone' => $sample['customer_phone'],
                'customer_email' => $sample['customer_email'],
                'claim_type'     => $sample['claim_type'],
                'reason'         => $sample['reason'],
                'status'         => $sample['status'],
                'admin_note'     => $sample['admin_note'] ?? null,
                'refund_amount'  => $sample['refund_amount'] ?? null,
                'refund_method'  => $sample['refund_method'] ?? null,
                'bank_name'      => $sample['bank_name'] ?? null,
                'bank_account_number' => $sample['bank_account_number'] ?? null,
                'bank_account_name'   => $sample['bank_account_name'] ?? null,
            ]);

            $created++;
        }

        $this->command->info("WarrantyClaimSeeder: Đã tạo {$created} warranty claim mẫu.");
    }
}
