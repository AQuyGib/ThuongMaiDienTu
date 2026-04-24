<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Tạo 3 vai trò mặc định: Admin, Quản lý, Khách hàng.
     */
    public function run(): void
    {
        $roles = [
            ['role_id' => 1, 'name' => 'Admin',      'description' => 'Quản trị viên hệ thống - toàn quyền'],
            ['role_id' => 2, 'name' => 'Quản lý',    'description' => 'Quản lý cửa hàng - xử lý đơn hàng, sản phẩm'],
            ['role_id' => 3, 'name' => 'Khách hàng',  'description' => 'Người dùng mua hàng trên website'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_id' => $role['role_id']],
                $role
            );
        }
    }
}
