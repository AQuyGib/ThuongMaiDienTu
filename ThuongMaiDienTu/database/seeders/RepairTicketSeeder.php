<?php

namespace Database\Seeders;

use App\Models\RepairTicket;
use App\Models\ServiceInvoice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RepairTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách khách hàng và kỹ thuật viên
        $customers = User::where('role_id', 3)->get();
        $technicians = User::whereIn('role_id', [1, 2, 4])->get();

        if ($technicians->isEmpty()) {
            return;
        }

        // Danh sách lỗi mẫu để mô tả sinh động
        $issues = [
            'Màn hình bị sọc dọc màu xanh lá, không phản hồi cảm ứng',
            'Pin tụt rất nhanh, sạc điện thoại nóng ran và báo pin ảo',
            'Loa trong bị rè lớn khi nghe cuộc gọi, loa ngoài bình thường',
            'Điện thoại bị rơi vào nước, bật không lên nguồn',
            'Camera sau bị mờ, không lấy nét được ở cự ly gần',
            'Cổng sạc USB-C bị lỏng, lúc nhận sạc lúc không',
            'Vỏ nhôm máy bị móp méo nặng đè vào pin bên trong',
            'Không kết nối được Wifi và Bluetooth, nút bật bị mờ xám',
        ];

        $serviceTemplates = [
            ['name' => 'Thay màn hình iPhone 14 Pro Max', 'fee' => 6500000],
            ['name' => 'Thay pin iPhone 13 Pro', 'fee' => 950000],
            ['name' => 'Ép kính Samsung S23 Ultra', 'fee' => 1800000],
            ['name' => 'Sửa nguồn Macbook Air M1', 'fee' => 2500000],
            ['name' => 'Vệ sinh & Tra keo tản nhiệt Laptop Gaming', 'fee' => 300000],
            ['name' => 'Thay bàn phím Dell XPS 13', 'fee' => 1200000],
            ['name' => 'Thay camera sau iPad Pro 11 inch', 'fee' => 1500000],
        ];

        // Lấy các hóa đơn dịch vụ đã tạo để gán cho các phiếu sửa chữa hoàn thành (Done)
        $invoices = ServiceInvoice::all();
        $invoiceIndex = 0;

        // 1. Tạo các phiếu sửa chữa đã hoàn thành và có liên kết hóa đơn
        // Ta sẽ liên kết 6 phiếu đầu tiên với 6 hóa đơn ngẫu nhiên
        $numLinked = min(6, $invoices->count());
        for ($i = 0; $i < $numLinked; $i++) {
            $invoice = $invoices[$i];
            
            // Tìm user tương ứng nếu khớp email/phone hoặc lấy ngẫu nhiên
            $user = User::where('phone_number', $invoice->customer_phone)->first() 
                ?? User::where('email', $invoice->customer_email)->first()
                ?? ($customers->isNotEmpty() ? $customers->random() : null);

            $technician = $technicians->random();
            $scheduled = Carbon::parse($invoice->created_at)->addDays(rand(1, 3));

            RepairTicket::create([
                'user_id' => $user ? $user->user_id : null,
                'technician_id' => $technician->user_id,
                'imei_serial' => $invoice->imei_serial ?? ('IMEI-' . rand(100000, 999999)),
                'issue_desc' => $issues[array_rand($issues)],
                'schedule_date' => $scheduled,
                'estimated_cost' => $invoice->subtotal,
                'status' => 'Done',
                'customer_name' => $invoice->customer_name,
                'customer_phone' => $invoice->customer_phone ?? '09' . rand(10000000, 99999999),
                'customer_address' => $user ? ($user->address ?? 'Hà Nội, Việt Nam') : 'Hà Nội, Việt Nam',
                'customer_email' => $invoice->customer_email,
                'customer_source' => array_rand(array_flip(['Facebook', 'Website', 'Hotline', 'Walk-in'])),
                'service_name' => $invoice->service_name,
                'service_fee' => $invoice->subtotal,
                'invoice_no' => $invoice->invoice_no,
                'invoiced_at' => $invoice->created_at,
                'created_at' => $invoice->created_at,
                'updated_at' => $scheduled,
            ]);
            
            $invoiceIndex++;
        }

        // 2. Tạo một số phiếu đã hoàn thành nhưng CHƯA xuất hóa đơn
        for ($i = 0; $i < 3; $i++) {
            $customer = $customers->isNotEmpty() ? $customers->random() : null;
            $technician = $technicians->random();
            $service = $serviceTemplates[array_rand($serviceTemplates)];
            $createdDate = Carbon::now()->subDays(rand(1, 10));

            RepairTicket::create([
                'user_id' => $customer ? $customer->user_id : null,
                'technician_id' => $technician->user_id,
                'imei_serial' => 'IMEI-' . rand(10000000, 99999999),
                'issue_desc' => $issues[array_rand($issues)],
                'schedule_date' => $createdDate->copy()->addDays(rand(1, 2)),
                'estimated_cost' => $service['fee'],
                'status' => 'Done',
                'customer_name' => $customer ? $customer->full_name : 'Khách vãng lai A' . $i,
                'customer_phone' => $customer ? $customer->phone_number : '09' . rand(10000000, 99999999),
                'customer_address' => 'Hải Phòng, Việt Nam',
                'customer_email' => $customer ? $customer->email : 'customer.done.' . $i . '@gmail.com',
                'customer_source' => 'Website',
                'service_name' => $service['name'],
                'service_fee' => $service['fee'],
                'invoice_no' => null,
                'invoiced_at' => null,
                'created_at' => $createdDate,
                'updated_at' => $createdDate->copy()->addDays(rand(1, 2)),
            ]);
        }

        // 3. Tạo các phiếu sửa chữa đang xử lý (Received, Checking, Under_Repair, Waiting_Parts)
        $progressStatuses = ['Received', 'Checking', 'Under_Repair', 'Waiting_Parts'];
        
        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->isNotEmpty() ? $customers->random() : null;
            $technician = $technicians->random();
            $status = $progressStatuses[$i % count($progressStatuses)];
            
            $service = null;
            $fee = 0;
            $estCost = 0;
            
            if ($status !== 'Received') {
                $service = $serviceTemplates[array_rand($serviceTemplates)];
                $estCost = $service['fee'];
            }

            $createdDate = Carbon::now()->subDays(rand(0, 5));

            RepairTicket::create([
                'user_id' => $customer ? $customer->user_id : null,
                'technician_id' => $technician->user_id,
                'imei_serial' => 'IMEI-' . rand(10000000, 99999999),
                'issue_desc' => $issues[array_rand($issues)],
                'schedule_date' => $createdDate->copy()->addDays(rand(2, 5)),
                'estimated_cost' => $estCost,
                'status' => $status,
                'customer_name' => $customer ? $customer->full_name : 'Khách vãng lai B' . $i,
                'customer_phone' => $customer ? $customer->phone_number : '09' . rand(10000000, 99999999),
                'customer_address' => 'TP. Hồ Chí Minh, Việt Nam',
                'customer_email' => $customer ? $customer->email : 'customer.prog.' . $i . '@gmail.com',
                'customer_source' => array_rand(array_flip(['Facebook', 'Website', 'Hotline', 'Walk-in'])),
                'service_name' => $service ? $service['name'] : null,
                'service_fee' => 0,
                'invoice_no' => null,
                'invoiced_at' => null,
                'created_at' => $createdDate,
                'updated_at' => $createdDate,
            ]);
        }
    }
}
