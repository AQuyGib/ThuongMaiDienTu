<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromCollection, WithHeadings, WithMapping, Responsable
{
    public string $fileName = 'products-export.xlsx';

    public function __construct(protected array $filters = [])
    {
    }

    public function collection()
    {
        $query = Product::with(['category', 'variants'])
            ->orderByDesc('product_id');

        if (! empty($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (! empty($this->filters['keyword'])) {
            $keyword = $this->filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->orWhere('seo_description', 'like', '%' . $keyword . '%');
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'product_id',
            'name',
            'brand',
            'category_id',
            'category_name',
            'base_price',
            'old_price',
            'status',
            'seo_description',
            'description',
            'variants_count',
        ];
    }

    public function map($product): array
    {
        return [
            $product->product_id,
            $product->name,
            $product->brand,
            $product->category_id,
            $product->category?->name,
            $product->base_price,
            $product->old_price,
            (int) $product->status,
            $product->seo_description,
            $product->description,
            $product->variants->count(),
        ];
    }
}
