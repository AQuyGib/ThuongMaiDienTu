<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy danh mục đã seed từ CategorySeeder
        $catDongHo    = Category::where('name', 'Đồng hồ thông minh')->first();
        $catTivi      = Category::where('name', 'Tivi, Màn hình')->first();
        $catGiaDung   = Category::where('name', 'Gia dụng, Smarthome')->first();

        // Danh mục con
        $catIPhone    = Category::where('name', 'iPhone')->first();
        $catSamsung   = Category::where('name', 'Samsung')->first();
        $catXiaomi    = Category::where('name', 'Xiaomi')->first();
        $catMacBook   = Category::where('name', 'MacBook')->first();
        $catGaming    = Category::where('name', 'Laptop Gaming')->first();
        $catVanPhong  = Category::where('name', 'Laptop Văn phòng')->first();
        $catiPad      = Category::where('name', 'iPad')->first();
        $catGalaxyTab = Category::where('name', 'Samsung Galaxy Tab')->first();
        $catOppo      = Category::where('name', 'OPPO')->first();
        $catTaiNghe   = Category::where('name', 'Tai nghe')->first();
        $catLoa       = Category::where('name', 'Loa Bluetooth')->first();
        $catSacDuPhong = Category::where('name', 'Sạc dự phòng')->first();
        $catOpLung    = Category::where('name', 'Ốp lưng, bao da')->first();
        $catCapSac    = Category::where('name', 'Cáp sạc')->first();

        foreach ([
            'Đồng hồ thông minh' => $catDongHo,
            'Tivi, Màn hình' => $catTivi,
            'Gia dụng, Smarthome' => $catGiaDung,
            'iPhone' => $catIPhone,
            'Samsung' => $catSamsung,
            'Xiaomi' => $catXiaomi,
            'MacBook' => $catMacBook,
            'Laptop Gaming' => $catGaming,
            'Laptop Văn phòng' => $catVanPhong,
            'iPad' => $catiPad,
            'Samsung Galaxy Tab' => $catGalaxyTab,
            'OPPO' => $catOppo,
            'Tai nghe' => $catTaiNghe,
            'Loa Bluetooth' => $catLoa,
            'Sạc dự phòng' => $catSacDuPhong,
            'Ốp lưng, bao da' => $catOpLung,
            'Cáp sạc' => $catCapSac,
        ] as $categoryName => $category) {
            if (! $category) {
                throw new \RuntimeException("Thiếu danh mục seed: {$categoryName}");
            }
        }

        $products = [
            // ===== ĐIỆN THOẠI - iPhone (4 sản phẩm) =====
            [
                'category_id' => $catIPhone->category_id,
                'name' => 'iPhone 15 Pro Max 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=400&q=80',

                'base_price' => 34990000,
                'old_price' => 35990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Super Retina XDR OLED, 6.7 inch, 2796x1290, 120Hz',
                    'Chip' => 'Apple A17 Pro 6 nhân',
                    'RAM' => '8GB',
                    'Bộ nhớ trong' => '256GB',
                    'Camera sau' => '48MP + 12MP Ultra Wide + 12MP Telephoto 5x',
                    'Camera trước' => '12MP TrueDepth',
                    'Pin' => '4422 mAh, sạc nhanh USB-C, MagSafe 15W',
                    'Hệ điều hành' => 'iOS 17',
                    'Kết nối' => '5G, Wi-Fi 6E, Bluetooth 5.3, NFC',
                    'Chống nước' => 'IP68',
                    'Chất liệu' => 'Khung Titanium, mặt kính Ceramic Shield',
                    'Trọng lượng' => '221g',
                ]),

            ],
            [
                'category_id' => $catIPhone->category_id,
                'name' => 'iPhone 15 128GB Chính hãng VN/A',
                'thumbnail' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=400&q=80',

                'base_price' => 22990000,
                'old_price' => 24990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Super Retina XDR OLED, 6.1 inch, 2556x1179',
                    'Camera sau' => '48MP (f/1.8) + 12MP Ultra Wide (f/2.2)',
                    'Camera trước' => '12MP (f/1.9)',
                    'Pin' => '3349 mAh, sạc nhanh USB-C',
                    'Trọng lượng' => '171g',
                    'Chất liệu' => 'Aluminum frame, glass front/back',
                ]),

            ],
            [
                'category_id' => $catIPhone->category_id,
                'name' => 'iPhone 14 128GB Chính hãng',
                'thumbnail' => 'https://images.unsplash.com/photo-1591337676887-a217a6c7e2e4?w=400&q=80',

                'base_price' => 17990000,
                'old_price' => 19990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Super Retina XDR OLED, 6.1 inch, 2532x1170',
                    'Camera sau' => '12MP (f/1.8) + 12MP Ultra Wide (f/2.2)',
                    'Camera trước' => '12MP (f/1.9)',
                    'Pin' => '3279 mAh, sạc nhanh USB-C',
                    'Trọng lượng' => '172g',
                    'Chất liệu' => 'Aluminum frame, glass front/back',
                ]),

            ],
            [
                'category_id' => $catIPhone->category_id,
                'name' => 'iPhone 13 128GB Chính hãng',
                'thumbnail' => 'https://images.unsplash.com/photo-1624353365286-3f280127ca1d?w=400&q=80',

                'base_price' => 15990000,
                'old_price' => 17990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Super Retina XDR OLED, 6.1 inch, 2532x1170',
                    'Camera sau' => '12MP (f/1.8) + 12MP Ultra Wide (f/2.2)',
                    'Camera trước' => '12MP (f/1.9)',
                    'Pin' => '3227 mAh, sạc nhanh USB-C',
                    'Trọng lượng' => '173g',
                    'Chất liệu' => 'Aluminum frame, glass front/back',
                ]),

            ],

            // ===== ĐIỆN THOẠI - Samsung (4 sản phẩm) =====
            [
                'category_id' => $catSamsung->category_id,
                'name' => 'Samsung Galaxy S24 Ultra 5G 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400&q=80',

                'base_price' => 33990000,
                'old_price' => 36990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Dynamic AMOLED 2X, 6.8 inch, 3120x1440, 120Hz',
                    'Camera sau' => '200MP (f/1.7) + 12MP Ultra Wide + 10MP Telephoto + 10MP Telephoto',
                    'Camera trước' => '12MP (f/2.2)',
                    'Pin' => '5000 mAh, sạc nhanh 45W',
                    'Trọng lượng' => '232g',
                    'Chất liệu' => 'Titanium frame, Gorilla Glass Victus 2',
                ]),

            ],
            [
                'category_id' => $catSamsung->category_id,
                'name' => 'Samsung Galaxy Z Fold5 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&q=80',

                'base_price' => 40990000,
                'old_price' => 41990000,

                'specifications' => json_encode([
                    'Màn hình chính' => 'Folded Dynamic AMOLED 2X, 7.6 inch, 2176x1512',
                    'Màn hình phụ' => 'Dynamic AMOLED 2X, 6.2 inch, 904x2316',
                    'Camera sau' => '50MP (f/1.8) + 12MP Ultra Wide + 10MP Telephoto',
                    'Camera trước' => '10MP (f/2.2)',
                    'Pin' => '4400 mAh, sạc nhanh 25W',
                    'Trọng lượng' => '253g',
                    'Chất liệu' => 'Titanium frame, Gorilla Glass Victus 2',
                ]),

            ],
            [
                'category_id' => $catSamsung->category_id,
                'name' => 'Samsung Galaxy A55 5G 128GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?w=400&q=80',

                'base_price' => 9990000,
                'old_price' => 10990000,

                'specifications' => json_encode([
                    'Màn hình' => 'AMOLED, 6.6 inch, 1080x2340, 120Hz',
                    'Camera sau' => '50MP (f/1.8) + 12MP Ultra Wide + 10MP Telephoto',
                    'Camera trước' => '32MP (f/2.2)',
                    'Pin' => '5000 mAh, sạc nhanh 25W',
                    'Trọng lượng' => '230g',
                    'Chất liệu' => 'Plastic frame, Gorilla Glass 5',
                    'Chống nước' => 'IP67',
                ]),

            ],
            [
                'category_id' => $catSamsung->category_id,
                'name' => 'Samsung Galaxy S23 FE 128GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&q=80',

                'base_price' => 13990000,
                'old_price' => 15990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Dynamic AMOLED 2X, 6.4 inch, 1080x2340, 120Hz',
                    'Camera sau' => '50MP (f/1.8) + 12MP Ultra Wide + 8MP Telephoto',
                    'Camera trước' => '10MP (f/2.2)',
                    'Pin' => '4500 mAh, sạc nhanh 25W',
                    'Trọng lượng' => '180g',
                    'Chất liệu' => 'Aluminum frame, Gorilla Glass 5',
                ]),

            ],

            // ===== ĐIỆN THOẠI - Xiaomi (4 sản phẩm) =====
            [
                'category_id' => $catXiaomi->category_id,
                'name' => 'Xiaomi 14 Ultra 5G 512GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400&q=80',

                'base_price' => 23990000,
                'old_price' => 25990000,

                'specifications' => json_encode([
                    'Màn hình' => 'AMOLED, 6.73 inch, 2K (2960x1440), 120Hz',
                    'Camera sau' => '50MP Leica (f/1.63) + 50MP Ultra Wide + 50MP Telephoto + 50MP Telephoto',
                    'Camera trước' => '32MP (f/2.0)',
                    'Pin' => '5300 mAh, sạc nhanh 90W',
                    'Trọng lượng' => '220g',
                    'Chất liệu' => 'Titanium frame, Gorilla Glass Victus 2',
                ]),

            ],
            [
                'category_id' => $catXiaomi->category_id,
                'name' => 'Xiaomi Redmi Note 13 Pro 5G',
                'thumbnail' => 'https://images.unsplash.com/photo-1556656793-08538906a9f8?w=400&q=80',

                'base_price' => 7990000,
                'old_price' => 8990000,

                'specifications' => json_encode([
                    'Màn hình' => 'AMOLED, 6.67 inch, 1080x2400, 120Hz',
                    'Camera sau' => '200MP (f/1.7) + 8MP Ultra Wide + 2MP Macro',
                    'Camera trước' => '16MP (f/2.0)',
                    'Pin' => '5100 mAh, sạc nhanh 120W',
                    'Trọng lượng' => '178g',
                    'Chất liệu' => 'Aluminum frame, Gorilla Glass 5',
                ]),
            ],
            [
                'category_id' => $catXiaomi->category_id,
                'name' => 'Xiaomi 13T Pro 5G 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=400&q=80',

                'base_price' => 14990000,
                'old_price' => 16990000,

                'specifications' => json_encode([
                    'Màn hình' => 'AMOLED, 6.67 inch, 2K (2400x1080), 144Hz',
                    'Camera sau' => '50MP (f/1.9) + 8MP Ultra Wide + 5MP Telephoto',
                    'Camera trước' => '20MP (f/2.0)',
                    'Pin' => '5000 mAh, sạc nhanh 120W',
                    'Trọng lượng' => '203g',
                    'Chất liệu' => 'Aluminum frame, Gorilla Glass 5',
                ]),
            ],

            // ===== ĐIỆN THOẠI - OPPO (2 sản phẩm) =====
            [
                'category_id' => $catOppo->category_id,
                'name' => 'OPPO Find X6 Pro 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1574944985070-8f3ebc6b79d2?w=400&q=80',

                'base_price' => 19990000,
                'old_price' => 21990000,

                'specifications' => json_encode([
                    'Màn hình' => 'AMOLED, 6.82 inch, 2K (3168x1440), 120Hz',
                    'Camera sau' => '50MP Hasselblad (f/1.7) + 50MP Ultra Wide + 50MP Telephoto',
                    'Camera trước' => '32MP (f/2.0)',
                    'Pin' => '5000 mAh, sạc nhanh 100W',
                    'Trọng lượng' => '219g',
                    'Chất liệu' => 'Aluminum frame, Gorilla Glass 5',
                ]),

            ],
            [
                'category_id' => $catOppo->category_id,
                'name' => 'OPPO Reno11 F 5G 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1585060544812-6b45742d762f?w=400&q=80',

                'base_price' => 8490000,
                'old_price' => 8990000,

                'specifications' => json_encode([
                    'Màn hình' => 'AMOLED, 6.7 inch, 1080x2412, 120Hz',
                    'Camera sau' => '64MP (f/1.7) + 8MP Ultra Wide + 2MP Macro',
                    'Camera trước' => '32MP (f/2.0)',
                    'Pin' => '4700 mAh, sạc nhanh 80W',
                    'Trọng lượng' => '172g',
                    'Chất liệu' => 'Aluminum frame, Gorilla Glass 5',
                ]),

            ],

            // ===== LAPTOP - MacBook (2 sản phẩm) =====
            [
                'category_id' => $catMacBook->category_id,
                'name' => 'MacBook Air 15 inch M3 2024 8GB/256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=400&q=80',

                'base_price' => 32990000,
                'old_price' => 35990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Liquid Retina IPS, 15.3 inch, 2880x1864, 120Hz',
                    'Camera' => '1080p FaceTime HD',
                    'Pin' => '78.6 Wh, sử dụng lên đến 18 giờ',
                    'Trọng lượng' => '1.51 kg',
                    'Chất liệu' => 'Aluminum unibody',
                    'Cổng kết nối' => '2x Thunderbolt 4, 3.5mm headphone',
                ]),

            ],
            [
                'category_id' => $catMacBook->category_id,
                'name' => 'MacBook Pro 14 inch M3 Pro 2024 18GB/512GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?w=400&q=80',

                'base_price' => 49990000,
                'old_price' => 52990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Liquid Retina XDR, 14.2 inch, 3024x1964, 120Hz',
                    'Camera' => '1080p FaceTime HD',
                    'Pin' => '70 Wh, sử dụng lên đến 22 giờ',
                    'Trọng lượng' => '1.61 kg',
                    'Chất liệu' => 'Aluminum unibody',
                    'Cổng kết nối' => '3x Thunderbolt 4, HDMI, SDXC, 3.5mm headphone, MagSafe 3',
                ]),

            ],

            // ===== LAPTOP - Gaming (2 sản phẩm) =====
            [
                'category_id' => $catGaming->category_id,
                'name' => 'ASUS ROG Strix G16 2024 i7-14700HX RTX4060',
                'thumbnail' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=400&q=80',

                'base_price' => 32990000,
                'old_price' => 36990000,

                'specifications' => json_encode([
                    'Màn hình' => 'IPS, 16 inch, 2560x1600, 165Hz',
                    'Camera' => '720p HD',
                    'Pin' => '90 Wh, sử dụng lên đến 8 giờ',
                    'Trọng lượng' => '2.3 kg',
                    'Chất liệu' => 'Plastic/Metal hybrid',
                    'Cổng kết nối' => 'USB-C, USB-A, HDMI 2.1, Ethernet, 3.5mm',
                ]),

            ],
            [
                'category_id' => $catGaming->category_id,
                'name' => 'MSI Katana 15 B13VFK i7-13650HX RTX4060',
                'thumbnail' => 'https://images.unsplash.com/photo-1588776814555-5f437317611f?w=400&q=80',

                'base_price' => 31990000,
                'old_price' => 35990000,

                'specifications' => json_encode([
                    'Màn hình' => 'IPS, 15.6 inch, 1920x1080, 144Hz',
                    'Camera' => '720p HD',
                    'Pin' => '90 Wh, sử dụng lên đến 7 giờ',
                    'Trọng lượng' => '2.2 kg',
                    'Chất liệu' => 'Plastic/Metal hybrid',
                    'Cổng kết nối' => 'USB-C, USB-A, HDMI 2.1, Ethernet, 3.5mm',
                ]),

            ],

            // ===== LAPTOP - Văn phòng (1 sản phẩm) =====
            [
                'category_id' => $catVanPhong->category_id,
                'name' => 'Dell XPS 15 2024 Core i7/16GB/512GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1531297572550-8cc3df7a0f69?w=400&q=80',

                'base_price' => 38490000,
                'old_price' => 41990000,

                'specifications' => json_encode([
                    'Màn hình' => 'OLED, 15.6 inch, 3840x2400, 60Hz',
                    'Camera' => '1080p HD',
                    'Pin' => '86 Wh, sử dụng lên đến 12 giờ',
                    'Trọng lượng' => '1.92 kg',
                    'Chất liệu' => 'Aluminum/Carbon fiber',
                    'Cổng kết nối' => '2x Thunderbolt 4, 2x USB-C, 3.5mm',
                ]),

            ],

            // ===== TABLET - iPad (2 sản phẩm) =====
            [
                'category_id' => $catiPad->category_id,
                'name' => 'iPad Pro M4 11 inch 2024 WiFi 256GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=400&q=80',

                'base_price' => 28990000,
                'old_price' => 30990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Liquid Retina XDR Mini-LED, 11 inch, 2388x1668, 120Hz',
                    'Camera sau' => '12MP Wide (f/1.8) + 10MP Ultra Wide (f/2.2)',
                    'Camera trước' => '12MP Ultra Wide (f/2.2)',
                    'Pin' => '7513 mAh, sử dụng lên đến 10 giờ',
                    'Trọng lượng' => '470g',
                    'Chất liệu' => 'Aluminum unibody',
                    'Hỗ trợ Apple Pencil' => 'Gen 2, magnetic charging',
                ]),

            ],
            [
                'category_id' => $catiPad->category_id,
                'name' => 'iPad Air M2 13 inch 2024 WiFi 128GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=400&q=80',

                'base_price' => 18990000,
                'old_price' => 19990000,

                'specifications' => json_encode([
                    'Màn hình' => 'Liquid Retina IPS, 13 inch, 2768x2048, 120Hz',
                    'Camera sau' => '12MP Wide (f/1.8) + 12MP Ultra Wide (f/2.2)',
                    'Camera trước' => '12MP Ultra Wide (f/2.2)',
                    'Pin' => '7089 mAh, sử dụng lên đến 10 giờ',
                    'Trọng lượng' => '461g',
                    'Chất liệu' => 'Aluminum unibody',
                    'Hỗ trợ Apple Pencil' => 'Gen 2, magnetic charging',
                ]),

            ],
            [
                'category_id' => $catGalaxyTab->category_id,
                'name' => 'Samsung Galaxy Tab S9 FE 128GB',
                'thumbnail' => 'https://images.unsplash.com/photo-1561154464-82e9aab32f4d?w=400&q=80',

                'base_price' => 9990000,
                'old_price' => 11990000,

                'specifications' => json_encode([
                    'Màn hình' => 'LCD, 10.9 inch, 2560x1600, 120Hz',
                    'Camera sau' => '13MP (f/1.9) + 5MP Ultra Wide (f/2.2)',
                    'Camera trước' => '8MP (f/2.0)',
                    'Pin' => '7040 mAh, sử dụng lên đến 13 giờ',
                    'Trọng lượng' => '503g',
                    'Chất liệu' => 'Plastic frame, Gorilla Glass 5',
                    'Hỗ trợ S Pen' => 'Có, lưu trong máy',
                ]),

            ],

            // ===== ÂM THANH (3 sản phẩm) =====
            [
                'category_id' => $catTaiNghe->category_id,
                'name' => 'Apple AirPods Pro 2 USB-C 2024',
                'thumbnail' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&q=80',

                'base_price' => 5590000,
                'old_price' => 6990000,

                'specifications' => json_encode([
                    'Loa' => 'Driver lực từ lớn',
                    'Micro' => '3 micro',
                    'Khử ồn' => 'Chủ động + Truyền dẫn',
                    'Pin' => '24 giờ (với hộp), 6 giờ (mỗi lần sạc)',
                    'Sạc' => 'USB-C, Qi wireless',
                    'Chống nước' => 'IPX4',
                ]),

            ],
            [
                'category_id' => $catTaiNghe->category_id,
                'name' => 'Sony WH-1000XM5 Chống ồn',
                'thumbnail' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=400&q=80',

                'base_price' => 7490000,
                'old_price' => 8490000,

                'specifications' => json_encode([
                    'Loa' => '30mm driver',
                    'Micro' => '4 micro',
                    'Khử ồn' => 'HD Noise Canceling Processor QN1',
                    'Pin' => '30 giờ (khử ồn bật), 38 giờ (tắt)',
                    'Sạc' => 'USB-C, Quick charging 3 giờ/10 phút',
                    'Chống nước' => 'Không',
                ]),

            ],
            [
                'category_id' => $catLoa->category_id,
                'name' => 'JBL Charge 5 Loa Bluetooth chống nước',
                'thumbnail' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400&q=80',

                'base_price' => 3190000,
                'old_price' => 3990000,

                'specifications' => json_encode([
                    'Loa' => '2x 20W',
                    'Micro' => 'Không',
                    'Khử ồn' => 'Không',
                    'Pin' => '20 giờ, sạc nhanh USB-C',
                    'Chống nước' => 'IP67',
                    'Kết nối' => 'JBL PartyBoost, Bluetooth 5.1',
                ]),

            ],

            // ===== ĐỒNG HỒ THÔNG MINH (3 sản phẩm) =====
            [
                'category_id' => $catDongHo->category_id,
                'name' => 'Apple Watch Series 9 GPS 45mm',
                'thumbnail' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=400&q=80',

                'base_price' => 10990000,
                'old_price' => 12490000,

                'specifications' => json_encode([
                    'Màn hình' => 'LTPO OLED, 1.78 inch, 450x352',
                    'Camera' => 'Không',
                    'Pin' => '18 giờ, sạc nhanh 45 phút/80%',
                    'Chống nước' => '50m (WR50)',
                    'Cảm biến' => 'Heart rate, ECG, SpO2, Temperature',
                ]),

            ],
            [
                'category_id' => $catDongHo->category_id,
                'name' => 'Samsung Galaxy Watch 6 Classic 47mm',
                'thumbnail' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&q=80',

                'base_price' => 8990000,
                'old_price' => 9990000,

                'specifications' => json_encode([
                    'Màn hình' => 'AMOLED, 1.4 inch, 480x480',
                    'Camera' => 'Không',
                    'Pin' => '40 giờ (thông thường), 80 giờ (tiết kiệm)',
                    'Chống nước' => '5ATM + IP68',
                    'Cảm biến' => 'Heart rate, ECG, SpO2, Body Composition',
                ]),

            ],
            [
                'category_id' => $catDongHo->category_id,
                'name' => 'Apple Watch Ultra 2 GPS + Cellular 49mm',
                'thumbnail' => 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=400&q=80',

                'base_price' => 21990000,
                'old_price' => 23990000,

                'specifications' => json_encode([
                    'Màn hình' => 'LTPO OLED, 1.92 inch, 512x394',
                    'Camera' => 'Không',
                    'Pin' => '36 giờ, sạc nhanh 45 phút/80%',
                    'Chống nước' => '100m (WR100)',
                    'Cảm biến' => 'Heart rate, ECG, SpO2, Temperature, Barometer',
                ]),

            ],

            // ===== PHỤ KIỆN (3 sản phẩm) =====
            [
                'category_id' => $catSacDuPhong->category_id,
                'name' => 'Sạc dự phòng Anker 20000mAh 65W',
                'thumbnail' => 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=400&q=80',

                'base_price' => 1290000,
                'old_price' => 1590000,

                'specifications' => json_encode([
                    'Dung lượng' => '20000 mAh',
                    'Công suất' => '65W (USB-C), 18W (USB-A)',
                    'Cổng ra' => '2x USB-C, 2x USB-A',
                    'Cổng vào' => '1x USB-C',
                    'Trọng lượng' => '420g',
                    'Bảo hành' => '18 tháng',
                ]),

            ],
            [
                'category_id' => $catCapSac->category_id,
                'name' => 'Cáp sạc nhanh USB-C to Lightning 2m',
                'thumbnail' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=400&q=80',

                'base_price' => 390000,
                'old_price' => 490000,

                'specifications' => json_encode([
                    'Loại cổng A' => 'USB-C',
                    'Loại cổng B' => 'Lightning',
                    'Dòng sạc' => '3A',
                    'Chức năng' => 'Sạc nhanh, đồng bộ dữ liệu',
                    'Chứng nhận' => 'MFi certified',
                    'Độ dài' => '2 mét',
                ]),

            ],
            [
                'category_id' => $catOpLung->category_id,
                'name' => 'Ốp lưng MagSafe iPhone 15 Pro Max',
                'thumbnail' => 'https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?w=400&q=80',

                'base_price' => 890000,
                'old_price' => 1190000,

                'specifications' => json_encode([
                    'Chất liệu' => 'Silicone/Polycarbonate',
                    'Tính năng' => 'MagSafe, sạc không dây',
                    'Bảo vệ' => 'Chống sốc, trầy xước',
                    'Trọng lượng' => '22g',
                    'Màu sắc' => 'Đen, Trắng, Xanh, Đỏ',
                ]),

            ],

            // ===== TIVI, MÀN HÌNH (2 sản phẩm) =====
            [
                'category_id' => $catTivi->category_id,
                'name' => 'Samsung Smart TV 4K 55 inch QA55Q80C',
                'thumbnail' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=400&q=80',

                'base_price' => 19990000,
                'old_price' => 24990000,

                'specifications' => json_encode([
                    'Màn hình' => 'QLED, 55 inch, 4K UHD (3840x2160)',
                    'Độ sáng' => '1000 nits',
                    'Tần số quét' => '120Hz',
                    'Hệ điều hành' => 'Tizen OS',
                    'Cổng kết nối' => 'HDMI 2.1 (2x), USB 3.0, Optical, Ethernet',
                    'Âm thanh' => '20W, Q-Symphony',
                ]),

            ],
            [
                'category_id' => $catTivi->category_id,
                'name' => 'LG OLED 65 inch C3 4K Smart TV',
                'thumbnail' => 'https://images.unsplash.com/photo-1567690187548-f07b1d7bf5a9?w=400&q=80',

                'base_price' => 35990000,
                'old_price' => 42990000,

                'specifications' => json_encode([
                    'Màn hình' => 'OLED, 65 inch, 4K UHD (3840x2160)',
                    'Tần số quét' => '120Hz',
                    'Hệ điều hành' => 'webOS 24',
                    'Cổng kết nối' => 'HDMI 2.1 (4x), eARC, USB 2.0, Ethernet',
                    'Âm thanh' => '40W, AI Sound Pro',
                    'Tính năng' => 'AI Picture, Game Mode Pro',
                ]),

            ],

            // ===== GIA DỤNG, SMARTHOME (2 sản phẩm) =====
            [
                'category_id' => $catGiaDung->category_id,
                'name' => 'Robot hút bụi Xiaomi Vacuum X20 Pro',
                'thumbnail' => 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=400&q=80',

                'base_price' => 8990000,
                'old_price' => 11990000,

                'specifications' => json_encode([
                    'Công suất hút' => '7000Pa',
                    'Pin' => '5200 mAh, sử dụng 180 phút',
                    'Quét bản đồ' => 'Laser 360 độ, LiDAR',
                    'Tính năng' => 'Hút + lau, tự động hút nước, AI obstacle avoidance',
                    'Điều khiển' => 'App Xiaomi Home',
                ]),

            ],
            [
                'category_id' => $catGiaDung->category_id,
                'name' => 'Máy lọc không khí Samsung AX60R5080WD',
                'thumbnail' => 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=400&q=80',

                'base_price' => 6490000,
                'old_price' => 7990000,

                'specifications' => json_encode([
                    'Công suất lọc' => '65m²',
                    'Bộ lọc' => 'True HEPA H13, Activated Carbon',
                    'Tính năng' => 'Diệt khuẩn H13, lọc PM0.3, cảm biến bụi',
                    'Điều khiển' => 'App SmartThings, remote',
                    'Độ ồn' => '24-52 dB',
                ]),

            ],
        ];

        foreach ($products as $product) {
            $product['slug'] = Str::slug($product['name']);
            Product::create($this->normalizeProductData($product));
        }
    }

    private function normalizeProductData(array $product): array
    {
        $specifications = json_decode($product['specifications'], true) ?: [];
        $productName = $product['name'];

        $common = [
            'Thương hiệu' => $this->detectBrand($productName),
            'Tình trạng' => 'Hàng mới 100%, nguyên seal',
            'Bảo hành' => $this->detectWarranty($productName),
            'Phù hợp' => $this->detectUseCase($productName),
            'Điểm nổi bật' => $this->detectHighlight($productName, $specifications),
        ];

        $product['specifications'] = json_encode($common + $specifications, JSON_UNESCAPED_UNICODE);

        return $product;
    }

    private function detectBrand(string $productName): string
    {
        $brands = ['iPhone' => 'Apple', 'iPad' => 'Apple', 'MacBook' => 'Apple', 'Apple Watch' => 'Apple', 'AirPods' => 'Apple', 'Samsung' => 'Samsung', 'Xiaomi' => 'Xiaomi', 'OPPO' => 'OPPO', 'ASUS' => 'ASUS', 'MSI' => 'MSI', 'Dell' => 'Dell', 'Sony' => 'Sony', 'JBL' => 'JBL', 'Anker' => 'Anker', 'LG' => 'LG'];

        foreach ($brands as $keyword => $brand) {
            if (str_contains($productName, $keyword)) {
                return $brand;
            }
        }

        return 'TechZone';
    }

    private function detectWarranty(string $productName): string
    {
        if (str_contains($productName, 'MacBook') || str_contains($productName, 'Laptop') || str_contains($productName, 'Dell') || str_contains($productName, 'ASUS') || str_contains($productName, 'MSI')) {
            return '24 tháng chính hãng';
        }

        if (str_contains($productName, 'Cáp') || str_contains($productName, 'Ốp lưng')) {
            return '12 tháng';
        }

        return '12 tháng chính hãng';
    }

    private function detectUseCase(string $productName): string
    {
        if (str_contains($productName, 'iPhone') || str_contains($productName, 'Galaxy') || str_contains($productName, 'Xiaomi') || str_contains($productName, 'OPPO')) {
            return 'Chụp ảnh, quay video, giải trí, làm việc di động';
        }

        if (str_contains($productName, 'MacBook') || str_contains($productName, 'Dell')) {
            return 'Học tập, văn phòng, thiết kế, lập trình';
        }

        if (str_contains($productName, 'ROG') || str_contains($productName, 'MSI')) {
            return 'Gaming, đồ họa, dựng video, tác vụ hiệu năng cao';
        }

        if (str_contains($productName, 'iPad') || str_contains($productName, 'Tab')) {
            return 'Ghi chú, học online, vẽ sáng tạo, xem phim';
        }

        if (str_contains($productName, 'AirPods') || str_contains($productName, 'Sony')) {
            return 'Nghe nhạc, họp online, chống ồn khi di chuyển';
        }

        if (str_contains($productName, 'JBL')) {
            return 'Nghe nhạc ngoài trời, du lịch, tiệc nhóm';
        }

        if (str_contains($productName, 'Watch')) {
            return 'Theo dõi sức khỏe, luyện tập, nhận thông báo';
        }

        if (str_contains($productName, 'TV')) {
            return 'Giải trí gia đình, xem phim 4K, chơi game console';
        }

        if (str_contains($productName, 'Robot') || str_contains($productName, 'lọc không khí')) {
            return 'Tự động hóa nhà thông minh, chăm sóc không gian sống';
        }

        return 'Sử dụng hằng ngày, nâng cấp trải nghiệm công nghệ';
    }

    private function detectHighlight(string $productName, array $specifications): string
    {
        if (isset($specifications['Camera sau'])) {
            return 'Camera chất lượng cao, hiệu năng ổn định, pin dùng cả ngày';
        }

        if (isset($specifications['Cổng kết nối']) && (str_contains($productName, 'MacBook') || str_contains($productName, 'Dell') || str_contains($productName, 'ASUS') || str_contains($productName, 'MSI'))) {
            return 'Màn hình sắc nét, hiệu năng mạnh, nhiều cổng kết nối cho công việc';
        }

        if (isset($specifications['Khử ồn'])) {
            return 'Âm thanh rõ, kết nối nhanh, tối ưu trải nghiệm nghe gọi';
        }

        if (isset($specifications['Cảm biến'])) {
            return 'Theo dõi sức khỏe toàn diện, thiết kế bền bỉ, nhiều chế độ luyện tập';
        }

        if (isset($specifications['Công suất hút']) || isset($specifications['Công suất lọc'])) {
            return 'Tự động hóa tiện lợi, điều khiển qua app, phù hợp nhà thông minh';
        }

        if (isset($specifications['Dung lượng']) || isset($specifications['Dòng sạc'])) {
            return 'Sạc nhanh, nhỏ gọn, tương thích nhiều thiết bị';
        }

        return 'Thiết kế hiện đại, dễ sử dụng, phù hợp nhu cầu phổ thông';
    }
}
