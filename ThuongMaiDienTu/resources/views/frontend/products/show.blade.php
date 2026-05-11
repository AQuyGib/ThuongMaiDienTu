@extends('layouts.app')

@section('content')
@php
    $variants = $product->variants->map(function ($variant) {
        return [
            'id' => $variant->product_variant_id ?? $variant->id ?? null,
            'label' => trim(collect([$variant->ram ?? null, $variant->color ?? null, $variant->storage ?? null])->filter()->join(' / ')),
            'price' => (float) ($variant->price ?? 0),
            'price_diff' => (float) ($variant->price_diff ?? $variant->extra_price ?? 0),
            'ram' => $variant->ram ?? null,
            'color' => $variant->color ?? null,
            'storage' => $variant->storage ?? null,
        ];
    })->values();
@endphp
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-sm border">
            <h1 class="text-2xl font-bold mb-4 text-gray-800">{{ $product->name }}</h1>
            <div class="flex items-center gap-2 mb-4">
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">{{ $product->category->name }}</span>
            </div>
            <div class="text-3xl font-bold text-red-600 mb-2" id="product-final-price" data-base-price="{{ (float) $product->base_price }}">{{ number_format($product->base_price, 0, ',', '.') }} đ</div>
            <div class="text-sm text-gray-500 mb-6" id="product-price-note">Giá sẽ tự động cập nhật theo biến thể được chọn.</div>

            @if($variants->isNotEmpty())
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Chọn biến thể</label>
                        <select id="variant-select" class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                            @foreach($variants as $variant)
                                <option value="{{ $variant['id'] }}" data-price-diff="{{ $variant['price_diff'] }}" data-price="{{ $variant['price'] }}">{{ $variant['label'] ?: ('Biến thể #' . $variant['id']) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm border">
            <div class="mb-6">
                <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" class="w-full max-w-md mx-auto rounded-lg shadow-sm">
            </div>
            <div class="prose max-w-none text-gray-700">
                <h3 class="text-lg font-semibold mb-2">Mô tả sản phẩm</h3>
                <p>{{ $product->description ?? 'Không có mô tả chi tiết cho sản phẩm này.' }}</p>
            </div>
            <div class="mt-8 flex gap-4">
                <a href="{{ route('products.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Quay lại danh sách</a>
                <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Thêm vào giỏ hàng</button>
            </div>
        </div>
    </div>
</div>

@if($variants->isNotEmpty())
<script>
    window.__PRODUCT_VARIANTS__ = @json($variants);
    window.__PRODUCT_BASE_PRICE__ = {{ (float) $product->base_price }};
    document.addEventListener('DOMContentLoaded', function () {
        const priceEl = document.getElementById('product-final-price');
        const select = document.getElementById('variant-select');
        if (!priceEl || !select) return;

        const formatPrice = (value) => new Intl.NumberFormat('vi-VN').format(Math.max(0, Math.round(value))) + ' đ';
        const updatePrice = () => {
            const option = select.selectedOptions[0];
            const diff = parseFloat(option?.dataset.priceDiff || '0');
            const price = parseFloat(option?.dataset.price || window.__PRODUCT_BASE_PRICE__ || '0');
            priceEl.textContent = formatPrice(price > 0 ? price : (window.__PRODUCT_BASE_PRICE__ + diff));
        };

        select.addEventListener('change', updatePrice);
        updatePrice();
    });
</script>
@endif
@endsection
