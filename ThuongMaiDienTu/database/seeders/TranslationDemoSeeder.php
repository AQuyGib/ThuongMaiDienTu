<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Database\Seeder;

class TranslationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::create([
            'name' => 'Điện thoại',
            'description' => 'Danh mục điện thoại chính hãng',
            'seo_description' => 'Điện thoại giá tốt, chính hãng',
            'slug' => 'dien-thoai',
            'sort_order' => 1,
            'is_active' => 1,
        ]);

        $category->translations()->updateOrCreate([
            'locale' => 'en'
        ], [
            'name' => 'Smartphones',
            'description' => 'Official smartphone category',
            'seo_description' => 'Best price official smartphones',
        ]);

        $attribute = Attribute::create([
            'name' => 'Màu sắc',
            'description' => 'Thuộc tính màu sắc',
            'slug' => 'mau-sac',
            'is_active' => 1,
        ]);

        $attribute->translations()->updateOrCreate([
            'locale' => 'en'
        ], [
            'name' => 'Color',
            'description' => 'Color attribute',
        ]);

        $page = Page::create([
            'title' => 'Giới thiệu',
            'excerpt' => 'Giới thiệu ngắn',
            'content' => 'Nội dung giới thiệu về công ty',
            'meta_title' => 'Trang giới thiệu',
            'meta_description' => 'Thông tin giới thiệu công ty',
            'slug' => 'gioi-thieu',
            'is_active' => 1,
        ]);

        $page->translations()->updateOrCreate([
            'locale' => 'en'
        ], [
            'title' => 'About Us',
            'excerpt' => 'Short introduction',
            'content' => 'Company introduction content',
            'meta_title' => 'About page',
            'meta_description' => 'Company introduction information',
        ]);

        $product = Product::create([
            'name' => 'iPhone 15 Pro',
            'description' => 'Điện thoại cao cấp mới nhất',
            'seo_description' => 'iPhone 15 Pro chính hãng',
            'category_id' => $category->category_id,
            'slug' => 'iphone-15-pro',
            'base_price' => 29990000,
            'discount_percent' => 10,
            'is_active' => 1,
        ]);

        $product->translations()->updateOrCreate([
            'locale' => 'en'
        ], [
            'name' => 'iPhone 15 Pro',
            'description' => 'The latest premium smartphone',
            'seo_description' => 'Official iPhone 15 Pro',
        ]);
    }
}
