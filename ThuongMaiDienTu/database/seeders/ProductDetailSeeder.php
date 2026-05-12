<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;

class ProductDetailSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::with('category')->get();

        foreach ($products as $product) {
            $catName = $product->category->name ?? '';

            if ($this->isPhoneCategory($catName)) {
                $this->seedPhoneDetail($product);
                continue;
            }

            if ($this->isLaptopCategory($catName)) {
                $this->seedLaptopDetail($product);
                continue;
            }

            if ($this->isTabletCategory($catName)) {
                $this->seedTabletDetail($product);
                continue;
            }

            if ($this->isAudioCategory($catName)) {
                $this->seedAudioDetail($product);
                continue;
            }

            if ($this->isWatchCategory($catName)) {
                $this->seedWatchDetail($product);
                continue;
            }

            if ($this->isTvCategory($catName)) {
                $this->seedTvDetail($product);
                continue;
            }

            $this->seedDefaultDetail($product);
        }
    }

    private function isPhoneCategory(string $catName): bool
    {
        return in_array($catName, ['iPhone', 'Samsung', 'Xiaomi', 'OPPO'], true);
    }

    private function isLaptopCategory(string $catName): bool
    {
        return in_array($catName, ['MacBook', 'Laptop Gaming', 'Laptop Văn phòng'], true);
    }

    private function isTabletCategory(string $catName): bool
    {
        return in_array($catName, ['iPad', 'Samsung Galaxy Tab'], true);
    }

    private function isAudioCategory(string $catName): bool
    {
        return in_array($catName, ['Tai nghe', 'Loa Bluetooth'], true);
    }

    private function isWatchCategory(string $catName): bool
    {
        return $catName === 'Đồng hồ thông minh';
    }

    private function isTvCategory(string $catName): bool
    {
        return in_array($catName, ['Tivi, Màn hình'], true);
    }

    private function seedPhoneDetail(Product $product): void
    {
        ProductSpecification::firstOrCreate(
            ['product_id' => $product->product_id],
            [
                'cpu_chip' => str_contains($product->name, 'iPhone') ? 'Apple A17 Pro' : 'Snapdragon 8 Gen 3',
                'ram_capacity' => '12 GB',
                'battery' => '5000 mAh',
                'screen_size' => '6.7 inch',
            ]
        );

        $variants = [
            ['color' => 'Đen', 'rom' => '128GB', 'ram' => '8GB', 'extra_price' => 0],
            ['color' => 'Trắng', 'rom' => '256GB', 'ram' => '12GB', 'extra_price' => 2000000],
            ['color' => 'Xanh', 'rom' => '512GB', 'ram' => '12GB', 'extra_price' => 4000000],
        ];
        $images = [
            'Đen' => 'https://images.unsplash.com/photo-1591337676887-a4b1f410c598?w=500&q=80',
            'Trắng' => 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?w=500&q=80',
            'Xanh' => 'https://images.unsplash.com/photo-1512054502232-10a0a035d672?w=500&q=80',
        ];
        $cpuChip = str_contains($product->name, 'iPhone') ? 'Apple A17 Pro' : 'Snapdragon 8 Gen 3';
        $gpuChip = str_contains($product->name, 'iPhone') ? 'Apple GPU 6-core' : 'Adreno 750';

        foreach ($variants as $variant) {
            ProductVariant::firstOrCreate([
                'product_id' => $product->product_id,
                'color' => $variant['color'],
                'rom_capacity' => $variant['rom'],
            ], [
                'ram' => $variant['ram'],
                'cpu_chip' => $cpuChip,
                'gpu_chip' => $gpuChip,
                'extra_price' => $variant['extra_price'],
                'image_url' => $images[$variant['color']] ?? null,
            ]);
        }
    }

    private function seedLaptopDetail(Product $product): void
    {
        ProductSpecification::firstOrCreate(
            ['product_id' => $product->product_id],
            [
                'cpu_chip' => str_contains($product->name, 'MacBook') ? 'Apple M3 Pro' : 'Intel Core i7-14700HX',
                'ram_capacity' => '16 GB',
                'battery' => '70 Wh',
                'screen_size' => '14 inch',
            ]
        );

        $variants = str_contains($product->name, 'MacBook')
            ? [
                ['color' => 'Xám không gian', 'rom' => '512GB SSD', 'cpu' => 'Apple M3', 'gpu' => 'Apple GPU 10-core', 'ram' => '16GB', 'extra_price' => 0],
                ['color' => 'Bạc', 'rom' => '1TB SSD', 'cpu' => 'Apple M3 Pro', 'gpu' => 'Apple GPU 14-core', 'ram' => '32GB', 'extra_price' => 5000000],
            ]
            : [
                ['color' => 'Xám không gian', 'rom' => '512GB SSD', 'cpu' => 'Intel Core i5-14500HX', 'gpu' => 'NVIDIA GeForce RTX 4050', 'ram' => '16GB', 'extra_price' => 2500000],
                ['color' => 'Bạc', 'rom' => '1TB SSD', 'cpu' => 'Intel Core i7-14700HX', 'gpu' => 'NVIDIA GeForce RTX 4060', 'ram' => '32GB', 'extra_price' => 5000000],
            ];
        $images = [
            'Xám không gian' => 'https://images.unsplash.com/photo-1517336714460-45788a1f27e1?w=500&q=80',
            'Bạc' => 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=500&q=80',
        ];

        foreach ($variants as $variant) {
            ProductVariant::firstOrCreate([
                'product_id' => $product->product_id,
                'color' => $variant['color'],
                'rom_capacity' => $variant['rom'],
                'cpu_chip' => $variant['cpu'],
                'gpu_chip' => $variant['gpu'],
            ], [
                'ram' => $variant['ram'],
                'extra_price' => $variant['extra_price'],
                'image_url' => $images[$variant['color']] ?? null,
            ]);
        }
    }

    private function seedTabletDetail(Product $product): void
    {
        ProductSpecification::firstOrCreate(
            ['product_id' => $product->product_id],
            [
                'cpu_chip' => str_contains($product->name, 'iPad') ? 'Apple M2' : 'Snapdragon 8 Gen 2',
                'ram_capacity' => '8 GB',
                'battery' => '8000 mAh',
                'screen_size' => '11 inch',
            ]
        );

        $variants = str_contains($product->name, 'iPad')
            ? [
                ['color' => 'Xám', 'rom' => '64GB', 'cpu' => 'Apple M2', 'gpu' => 'Apple GPU 10-core', 'extra_price' => 0],
                ['color' => 'Hồng', 'rom' => '256GB', 'cpu' => 'Apple M2 Pro', 'gpu' => 'Apple GPU 12-core', 'extra_price' => 3000000],
            ]
            : [
                ['color' => 'Xám', 'rom' => '64GB', 'cpu' => 'Snapdragon 8 Gen 2', 'gpu' => 'Adreno 740', 'extra_price' => 0],
                ['color' => 'Xanh lam', 'rom' => '256GB', 'cpu' => 'Snapdragon 8 Gen 2', 'gpu' => 'Adreno 740', 'extra_price' => 3000000],
            ];
        $images = [
            'Xám' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500&q=80',
            'Hồng' => 'https://images.unsplash.com/photo-1527698266440-12104e498b76?w=500&q=80',
            'Xanh lam' => 'https://images.unsplash.com/photo-1588702546850-8b5e28a55639?w=500&q=80',
        ];

        foreach ($variants as $variant) {
            ProductVariant::firstOrCreate([
                'product_id' => $product->product_id,
                'color' => $variant['color'],
                'rom_capacity' => $variant['rom'],
                'cpu_chip' => $variant['cpu'],
                'gpu_chip' => $variant['gpu'],
            ], [
                'ram' => '8GB',
                'extra_price' => $variant['extra_price'],
                'image_url' => $images[$variant['color']] ?? null,
            ]);
        }
    }

    private function seedAudioDetail(Product $product): void
    {
        ProductSpecification::firstOrCreate(
            ['product_id' => $product->product_id],
            [
                'cpu_chip' => 'Bluetooth 5.3',
                'ram_capacity' => 'N/A',
                'battery' => '30 Giờ',
                'screen_size' => 'N/A',
            ]
        );

        $variants = [
            ['color' => 'Đen', 'extra_price' => 0],
            ['color' => 'Trắng', 'extra_price' => 0],
        ];
        $images = [
            'Đen' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&q=80',
            'Trắng' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=500&q=80',
        ];

        foreach ($variants as $variant) {
            ProductVariant::firstOrCreate([
                'product_id' => $product->product_id,
                'color' => $variant['color'],
            ], [
                'ram' => null,
                'rom_capacity' => null,
                'extra_price' => $variant['extra_price'],
                'image_url' => $images[$variant['color']] ?? null,
            ]);
        }
    }

    private function seedWatchDetail(Product $product): void
    {
        ProductSpecification::firstOrCreate(
            ['product_id' => $product->product_id],
            [
                'cpu_chip' => 'S9 SiP',
                'ram_capacity' => '1 GB',
                'battery' => '18 Giờ',
                'screen_size' => '1.9 inch',
            ]
        );

        $variants = [
            ['color' => '41mm', 'extra_price' => 0],
            ['color' => '45mm', 'extra_price' => 1000000],
        ];
        $images = [
            '41mm' => 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=500&q=80',
            '45mm' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=500&q=80',
        ];

        foreach ($variants as $variant) {
            ProductVariant::firstOrCreate([
                'product_id' => $product->product_id,
                'color' => $variant['color'],
            ], [
                'ram' => null,
                'rom_capacity' => null,
                'extra_price' => $variant['extra_price'],
                'image_url' => $images[$variant['color']] ?? null,
            ]);
        }
    }

    private function seedTvDetail(Product $product): void
    {
        ProductSpecification::firstOrCreate(
            ['product_id' => $product->product_id],
            [
                'cpu_chip' => 'Bộ xử lý 4K',
                'ram_capacity' => '2 GB',
                'battery' => 'Cắm điện',
                'screen_size' => '55 inch 4K',
            ]
        );

        $variants = [
            ['color' => '55 inch', 'extra_price' => 0],
            ['color' => '65 inch', 'extra_price' => 4000000],
        ];

        foreach ($variants as $variant) {
            ProductVariant::firstOrCreate([
                'product_id' => $product->product_id,
                'color' => $variant['color'],
            ], [
                'ram' => null,
                'rom_capacity' => null,
                'extra_price' => $variant['extra_price'],
                'image_url' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=500&q=80',
            ]);
        }
    }

    private function seedDefaultDetail(Product $product): void
    {
        ProductSpecification::firstOrCreate(
            ['product_id' => $product->product_id],
            [
                'cpu_chip' => 'Tiêu chuẩn',
                'ram_capacity' => 'N/A',
                'battery' => 'Tiêu chuẩn',
                'screen_size' => 'N/A',
            ]
        );

        ProductVariant::firstOrCreate([
            'product_id' => $product->product_id,
            'color' => 'Tiêu chuẩn',
        ], [
            'ram' => null,
            'rom_capacity' => null,
            'cpu_chip' => null,
            'gpu_chip' => null,
            'extra_price' => 0,
            'image_url' => 'https://images.unsplash.com/photo-1468495244122-4a69b0fa8480?w=500&q=80',
        ]);
    }
}
