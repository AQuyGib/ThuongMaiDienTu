<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Cashbook;

class CashbookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Xóa dữ liệu cũ (nếu muốn)
        // Cashbook::truncate();

        $data = [
            [
                'type' => 'Income',
                'amount' => 15000000,
                'description' => 'Khách hàng thanh toán đơn hàng #1024',
                'created_at' => now()->subHours(2),
            ],
            [
                'type' => 'Expense',
                'amount' => 2500000,
                'description' => 'Thanh toán chi phí vận chuyển',
                'created_at' => now()->subHours(5),
            ],
            [
                'type' => 'Income',
                'amount' => 8000000,
                'description' => 'Thu tiền bảo hành máy lạnh Panasonic',
                'created_at' => now()->subDays(1),
            ],
            [
                'type' => 'Expense',
                'amount' => 1200000,
                'description' => 'Mua văn phòng phẩm',
                'created_at' => now()->subDays(2),
            ],
            [
                'type' => 'Income',
                'amount' => 5000000,
                'description' => 'Khách cọc tiền mua tủ lạnh Samsung',
                'created_at' => now()->subDays(3),
            ],
            [
                'type' => 'Income',
                'amount' => 10000000,
                'description' => 'Lãi tiết kiệm hàng tháng',
                'created_at' => now()->subDays(5),
            ],
            [
                'type' => 'Expense',
                'amount' => 10000000,
                'description' => 'Trả lương nhân viên',
                'created_at' => now()->subDays(10),
            ]
        ];

        foreach ($data as $item) {
            Cashbook::create($item);
        }
    }
}
