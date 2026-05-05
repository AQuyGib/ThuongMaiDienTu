<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
<<<<<<< HEAD
use App\Models\Role;
=======
use Illuminate\Support\Facades\DB;
>>>>>>> origin/xuanhoa/Phanquyen

class RoleSeeder extends Seeder
{
    /**
     * Tạo các vai trò mặc định: Admin, Quản lý, Khách hàng, Nhân viên.
     */
    public function run(): void
    {
        $roles = [
            ['role_id' => 1, 'name' => 'Admin',      'description' => 'Quản trị viên hệ thống - toàn quyền'],
            ['role_id' => 2, 'name' => 'Quản lý',    'description' => 'Quản lý cửa hàng - xử lý đơn hàng, sản phẩm'],
            ['role_id' => 3, 'name' => 'Khách hàng', 'description' => 'Người dùng mua hàng trên website'],
            ['role_id' => 4, 'name' => 'Nhân viên',  'description' => 'Nhân viên bán hàng'],
        ];

        foreach ($roles as $role) {
            \App\Models\Role::updateOrCreate(
                ['role_id' => $role['role_id']],
                $role
            );
        }
    }
}
