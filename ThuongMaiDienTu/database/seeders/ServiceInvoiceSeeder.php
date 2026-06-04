<?php

namespace Database\Seeders;

use App\Models\ServiceInvoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ServiceInvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy admin hoặc manager làm người tạo
        $creator = User::whereIn('role_id', [1, 2])->first();
        $creatorId = $creator ? $creator->user_id : null;

        // Một số dịch vụ mẫu
        $services = [
            [
                'name' => 'Thay màn hình iPhone 14 Pro Max',
                'desc' => 'Thay màn hình OLED zin máy chính hãng, bảo hành cảm ứng 6 tháng',
                'price' => 6500000,
            ],
            [
                'name' => 'Thay pin iPhone 13 Pro',
                'desc' => 'Thay pin Pisen dung lượng siêu cao, bảo hành 12 tháng',
                'price' => 950000,
            ],
            [
                'name' => 'Ép kính Samsung S23 Ultra',
                'desc' => 'Ép kính Gorilla Glass Victus mới, keo OCA chuẩn nhà máy',
                'price' => 1800000,
            ],
            [
                'name' => 'Sửa nguồn Macbook Air M1',
                'desc' => 'Thay IC nguồn, khắc phục lỗi chập nguồn sạc không lên',
                'price' => 2500000,
            ],
            [
                'name' => 'Vệ sinh & Tra keo tản nhiệt Laptop Gaming',
                'desc' => 'Vệ sinh quạt gió, tra keo tản nhiệt MX-4 cao cấp',
                'price' => 300000,
            ],
            [
                'name' => 'Thay bàn phím Dell XPS 13',
                'desc' => 'Thay bàn phím chuẩn US OEM, có led nền',
                'price' => 1200000,
            ],
            [
                'name' => 'Cài đặt hệ điều hành & Phần mềm văn phòng',
                'desc' => 'Cài Windows 11 Pro, Office 2021 bản quyền và các tiện ích cơ bản',
                'price' => 250000,
            ],
            [
                'name' => 'Thay camera sau iPad Pro 11 inch',
                'desc' => 'Thay cụm camera sau zin bóc máy',
                'price' => 1500000,
            ],
        ];

        // Lấy danh sách khách hàng để lấy thông tin gán cho hóa đơn
        $customers = User::where('role_id', 3)->get();

        // 1. Tạo các hóa đơn đã thanh toán (paid) và đã phát hành (issued)
        $statuses = ['paid', 'paid', 'paid', 'issued', 'draft', 'cancelled'];
        
        for ($i = 1; $i <= 12; $i++) {
            $customer = $customers->isNotEmpty() ? $customers->random() : null;
            $service = $services[array_rand($services)];
            
            $subtotal = $service['price'];
            $vatRate = rand(0, 1) ? 10 : 8; // 8% hoặc 10% VAT
            $taxAmount = (int) round(($subtotal * $vatRate) / 100);
            $discountAmount = rand(0, 5) === 0 ? rand(5, 20) * 10000 : 0; // 20% cơ hội giảm giá từ 50k - 200k
            $totalAmount = max(0, $subtotal + $taxAmount - $discountAmount);

            $status = $statuses[array_rand($statuses)];
            $issuedDate = null;
            if ($status !== 'draft') {
                $issuedDate = Carbon::now()->subDays(rand(1, 30))->toDateString();
            }

            // Tạo mã IMEI ngẫu nhiên
            $imei = '86' . rand(1000000000000, 9999999999999);

            // Sinh mã hóa đơn mẫu
            $dateStr = $issuedDate ? Carbon::parse($issuedDate)->format('Ymd') : Carbon::now()->format('Ymd');
            $invoiceNo = 'INV-' . $dateStr . '-' . Str::upper(Str::random(6));

            ServiceInvoice::create([
                'invoice_no' => $invoiceNo,
                'customer_name' => $customer ? $customer->full_name : 'Khách vãng lai ' . $i,
                'customer_phone' => $customer ? $customer->phone_number : '09' . rand(10000000, 99999999),
                'customer_email' => $customer ? $customer->email : 'khach' . $i . '@gmail.com',
                'imei_serial' => $imei,
                'service_name' => $service['name'],
                'description' => $service['desc'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => $status,
                'issued_date' => $issuedDate,
                'created_by' => $creatorId,
                'created_at' => $issuedDate ? Carbon::parse($issuedDate)->subHours(rand(1, 5)) : Carbon::now(),
                'updated_at' => $issuedDate ? Carbon::parse($issuedDate) : Carbon::now(),
            ]);
        }
    }
}
