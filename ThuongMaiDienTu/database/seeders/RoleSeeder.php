<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Tạo 4 vai trò mặc định: Admin, Customer, Quản lý, Nhân viên.
     */
    public function run(): void
    {
        $roles = [
            ['role_id' => 1, 'name' => 'Admin',      'description' => 'Quản trị viên hệ thống - toàn quyền'],
            ['role_id' => 2, 'name' => 'Customer',    'description' => 'Khách hàng'],
            ['role_id' => 3, 'name' => 'Quản lý',  'description' => 'Quản lý cửa hàng'],
            ['role_id' => 4, 'name' => 'Nhân viên',  'description' => 'Nhân viên bán hàng'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_id' => $role['role_id']],
                $role
            );
        }
    }
}
