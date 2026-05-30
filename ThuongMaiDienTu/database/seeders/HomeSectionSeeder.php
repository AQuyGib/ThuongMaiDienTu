<?php

namespace Database\Seeders;

use App\Models\HomeSection;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder tạo dữ liệu mẫu cho các khung sản phẩm hiển thị trên Trang Chủ.
 *
 * Bảng liên quan:
 *  - home_sections: Chứa thông tin từng khung (tiêu đề, loại, danh mục, giới hạn, banner, thứ tự).
 *  - home_section_products: Bảng pivot liên kết khung loại 'manual' với các sản phẩm cụ thể.
 *
 * Các loại khung (type):
 *  - 'category': Tự động hiển thị sản phẩm mới nhất thuộc danh mục cha + con.
 *  - 'manual':   Admin tự chọn sản phẩm cụ thể gắn vào khung.
 *  - 'latest':   Hiển thị sản phẩm mới nhất trên toàn hệ thống.
 */
class HomeSectionSeeder extends Seeder
{
    public function run(): void
    {
        // Xóa sạch dữ liệu cũ (tắt ràng buộc khóa ngoại tạm thời)
        Schema::disableForeignKeyConstraints();
        \DB::table('home_section_products')->truncate();
        \DB::table('home_sections')->truncate();
        Schema::enableForeignKeyConstraints();

        // Đường dẫn gốc banner trong thư mục public
        $bannerPath = '/uploads/banners';

        // ========================================
        // 1. Khung "Điện thoại nổi bật" — loại category (category_id = 1: Điện thoại)
        // ========================================
        HomeSection::create([
            'title'          => 'ĐIỆN THOẠI NỔI BẬT',
            'type'           => 'category',
            'category_id'    => 1,
            'limit'          => 10,
            'sidebar_banner' => $bannerPath . '/banner_dien_thoai.png',
            'sidebar_link'   => '/products/dien-thoai',
            'order'          => 1,
            'status'         => true,
        ]);

        // ========================================
        // 2. Khung "Laptop cho mọi nhu cầu" — loại category (category_id = 2: Laptop)
        // ========================================
        HomeSection::create([
            'title'          => 'LAPTOP CHO MỌI NHU CẦU',
            'type'           => 'category',
            'category_id'    => 2,
            'limit'          => 10,
            'sidebar_banner' => $bannerPath . '/banner_laptop.png',
            'sidebar_link'   => '/products/laptop',
            'order'          => 2,
            'status'         => true,
        ]);

        // ========================================
        // 3. Khung "Tablet & iPad" — loại category (category_id = 3: Tablet)
        // ========================================
        HomeSection::create([
            'title'          => 'TABLET & iPAD',
            'type'           => 'category',
            'category_id'    => 3,
            'limit'          => 8,
            'sidebar_banner' => $bannerPath . '/banner_tablet.png',
            'sidebar_link'   => '/products/tablet',
            'order'          => 3,
            'status'         => true,
        ]);

        // ========================================
        // 4. Khung "Phụ kiện hot" — loại category (category_id = 6: Phụ kiện)
        // ========================================
        HomeSection::create([
            'title'          => 'PHỤ KIỆN HOT',
            'type'           => 'category',
            'category_id'    => 6,
            'limit'          => 8,
            'sidebar_banner' => $bannerPath . '/banner_phu_kien.png',
            'sidebar_link'   => '/products/phu-kien',
            'order'          => 4,
            'status'         => true,
        ]);

        // ========================================
        // 5. Khung "Sản phẩm mới nhất" — loại latest
        // ========================================
        HomeSection::create([
            'title'          => 'SẢN PHẨM MỚI NHẤT',
            'type'           => 'latest',
            'category_id'    => null,
            'limit'          => 10,
            'sidebar_banner' => $bannerPath . '/banner_san_pham_moi.png',
            'sidebar_link'   => '/products',
            'order'          => 5,
            'status'         => true,
        ]);

        // ========================================
        // 6. Khung "Lựa chọn của biên tập viên" — loại manual (admin tự chọn SP)
        // ========================================
        $manualSection = HomeSection::create([
            'title'          => 'LỰA CHỌN CỦA BIÊN TẬP VIÊN',
            'type'           => 'manual',
            'category_id'    => null,
            'limit'          => 10,
            'sidebar_banner' => $bannerPath . '/banner_editor_pick.png',
            'sidebar_link'   => '/products',
            'order'          => 6,
            'status'         => true,
        ]);

        // Gắn sản phẩm thủ công cho khung manual: lấy ngẫu nhiên 10 sản phẩm từ DB
        $manualProducts = Product::inRandomOrder()->take(10)->pluck('product_id');
        foreach ($manualProducts as $index => $productId) {
            $manualSection->products()->attach($productId, ['order' => $index]);
        }

        // ========================================
        // 7. Khung "Gia dụng & Smarthome" — loại category (category_id = 8)
        // ========================================
        HomeSection::create([
            'title'          => 'GIA DỤNG & SMARTHOME',
            'type'           => 'category',
            'category_id'    => 8,
            'limit'          => 8,
            'sidebar_banner' => $bannerPath . '/banner_gia_dung.png',
            'sidebar_link'   => '/products/gia-dung-smarthome',
            'order'          => 7,
            'status'         => true,
        ]);

        $this->command->info('HomeSectionSeeder: Đã tạo ' . HomeSection::count() . ' khung sản phẩm trang chủ (có banner).');
    }
}
