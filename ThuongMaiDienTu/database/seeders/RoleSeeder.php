<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->whereIn('role_id', [1, 2, 3, 4])->delete();
        DB::table('roles')->insert([
            ['role_id' => 1, 'name' => 'Admin', 'description' => 'Quản trị viên hệ thống'],
            ['role_id' => 2, 'name' => 'Customer', 'description' => 'Khách hàng'],
            ['role_id' => 3, 'name' => 'Quản lý', 'description' => 'Quản lý cửa hàng'],
            ['role_id' => 4, 'name' => 'Nhân viên', 'description' => 'Nhân viên bán hàng'],
        ]);
    }
}
