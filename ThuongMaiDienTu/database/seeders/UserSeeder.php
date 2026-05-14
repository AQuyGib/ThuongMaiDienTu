<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Tạo 1 tài khoản Admin mặc định + 20 tài khoản Khách hàng ảo.
     */
    public function run(): void
    {
        // Admin mặc định
        User::updateOrCreate(
            ['email' => 'admin@dienmaypro.com.vn'],
            [
                'role_id'       => 1,
                'full_name'     => 'Quản Trị Viên',
                'password_hash' => Hash::make('admin123'),
                'member_tier'   => 'Vang',
                'status'        => 'Active',
            ]
        );

        // Quản lý mẫu
        User::updateOrCreate(
            ['email' => 'manager@dienmaypro.com.vn'],
            [
                'role_id'       => 2,
                'full_name'     => 'Nguyễn Quản Lý',
                'password_hash' => Hash::make('manager123'),
                'member_tier'   => 'Dong',
                'status'        => 'Active',
            ]
        );

        // 20 Khách hàng mẫu
        $customers = [
            ['full_name' => 'Trần Văn An',       'email' => 'an.tran@gmail.com',        'member_tier' => 'Dong'],
            ['full_name' => 'Nguyễn Thị Bình',   'email' => 'binh.nguyen@gmail.com',    'member_tier' => 'Bac'],
            ['full_name' => 'Lê Hoàng Cường',    'email' => 'cuong.le@gmail.com',       'member_tier' => 'Vang'],
            ['full_name' => 'Phạm Minh Đức',     'email' => 'duc.pham@gmail.com',       'member_tier' => 'Dong'],
            ['full_name' => 'Hoàng Thị Em',      'email' => 'em.hoang@gmail.com',       'member_tier' => 'Dong'],
            ['full_name' => 'Võ Thanh Phong',     'email' => 'phong.vo@gmail.com',       'member_tier' => 'Bac'],
            ['full_name' => 'Đặng Thị Giang',    'email' => 'giang.dang@gmail.com',     'member_tier' => 'Dong'],
            ['full_name' => 'Bùi Quốc Hải',      'email' => 'hai.bui@gmail.com',        'member_tier' => 'Dong'],
            ['full_name' => 'Trương Thị Ý',      'email' => 'y.truong@gmail.com',       'member_tier' => 'Vang'],
            ['full_name' => 'Lý Minh Khang',     'email' => 'khang.ly@gmail.com',       'member_tier' => 'Dong'],
            ['full_name' => 'Mai Anh Kiệt',      'email' => 'kiet.mai@gmail.com',       'member_tier' => 'Bac'],
            ['full_name' => 'Ngô Bảo Lâm',       'email' => 'lam.ngo@gmail.com',        'member_tier' => 'Dong'],
            ['full_name' => 'Đinh Thị Mỹ',       'email' => 'my.dinh@gmail.com',        'member_tier' => 'Dong'],
            ['full_name' => 'Huỳnh Hữu Nam',     'email' => 'nam.huynh@gmail.com',      'member_tier' => 'Dong'],
            ['full_name' => 'Cao Thị Oanh',      'email' => 'oanh.cao@gmail.com',       'member_tier' => 'Bac'],
            ['full_name' => 'Tô Đình Phúc',      'email' => 'phuc.to@gmail.com',        'member_tier' => 'Dong'],
            ['full_name' => 'Lưu Bích Quyên',    'email' => 'quyen.luu@gmail.com',      'member_tier' => 'Dong'],
            ['full_name' => 'Phan Đức Sơn',      'email' => 'son.phan@gmail.com',       'member_tier' => 'Vang'],
            ['full_name' => 'Vương Thị Trang',   'email' => 'trang.vuong@gmail.com',    'member_tier' => 'Dong'],
            ['full_name' => 'Đỗ Quang Uy',       'email' => 'uy.do@gmail.com',          'member_tier' => 'Dong'],
        ];

        foreach ($customers as $customer) {
            User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'role_id'       => 3,
                    'full_name'     => $customer['full_name'],
                    'password_hash' => Hash::make('123456'),
                    'member_tier'   => $customer['member_tier'],
                    'status'        => 'Active',
                ]
            );
        }
    }
}
