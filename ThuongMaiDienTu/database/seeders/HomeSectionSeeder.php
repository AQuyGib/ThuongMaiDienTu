<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomeSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dienThoai = \App\Models\Category::where('name', 'Điện thoại')->first();
        $laptop = \App\Models\Category::where('name', 'Laptop')->first();
        
        \App\Models\HomeSection::create([
            'title' => 'ĐIỆN THOẠI NỔI BẬT NHẤT',
            'type' => 'category',
            'category_id' => $dienThoai->category_id ?? null,
            'limit' => 10,
            'sidebar_banner' => 'https://images.unsplash.com/photo-1616348436168-de43ad0db179?w=400',
            'order' => 1,
            'status' => true
        ]);

        \App\Models\HomeSection::create([
            'title' => 'LAPTOP GIÁ SỐC',
            'type' => 'category',
            'category_id' => $laptop->category_id ?? null,
            'limit' => 10,
            'sidebar_banner' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400',
            'order' => 2,
            'status' => true
        ]);
    }
}
