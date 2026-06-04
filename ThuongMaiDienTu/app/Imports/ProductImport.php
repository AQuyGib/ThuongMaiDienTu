<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $sku = trim((string) ($row['sku'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $brand = trim((string) ($row['brand'] ?? ''));
            $categoryName = trim((string) ($row['category'] ?? ''));

            if ($name === '' || $categoryName === '') {
                continue;
            }

            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['slug' => Str::slug($categoryName) . '-' . uniqid()]
            );

            $product = null;
            if ($sku !== '') {
                $product = Product::where('sku', $sku)->first();
            }

            $data = [
                'category_id' => $category->category_id,
                'name' => $name,
                'brand' => $brand !== '' ? $brand : null,
                'base_price' => (int) ($row['base_price'] ?? 0),
                'old_price' => ! empty($row['old_price']) ? (int) $row['old_price'] : null,
                'status' => isset($row['status']) ? (int) $row['status'] : 1,
                'seo_description' => $row['seo_description'] ?? null,
                'description' => $row['description'] ?? null,
                'thumbnail' => $row['thumbnail'] ?? null,
            ];

            if ($product) {
                $product->update($data);
            } else {
                Product::create(array_merge($data, [
                    'sku' => $sku !== '' ? $sku : null,
                ]));
            }
        }
    }
}
