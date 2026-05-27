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
        $category = Category::updateOrCreate([
            'slug' => 'dien-thoai'
        ], [
            'name' => 'Điện thoại',
            'description' => 'Danh mục điện thoại chính hãng',
            'seo_description' => 'Điện thoại giá tốt, chính hãng',
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

        $attribute = Attribute::updateOrCreate([
            'slug' => 'mau-sac'
        ], [
            'name' => 'Màu sắc',
            'description' => 'Thuộc tính màu sắc',
            'is_active' => 1,
        ]);

        $attribute->translations()->updateOrCreate([
            'locale' => 'en'
        ], [
            'name' => 'Color',
            'description' => 'Color attribute',
        ]);

        $page = Page::updateOrCreate([
            'slug' => 'gioi-thieu'
        ], [
            'title' => 'Giới thiệu',
            'excerpt' => 'Giới thiệu ngắn',
            'content' => 'Nội dung giới thiệu về công ty',
            'meta_title' => 'Trang giới thiệu',
            'meta_description' => 'Thông tin giới thiệu công ty',
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

        $product = Product::updateOrCreate([
            'slug' => 'iphone-15-pro'
        ], [
            'name' => 'iPhone 15 Pro',
            'description' => 'Điện thoại cao cấp mới nhất',
            'seo_description' => 'iPhone 15 Pro chính hãng',
            'category_id' => $category->category_id,
            'base_price' => 29990000,
            'discount_percent' => 10,
            'status' => 1,
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
