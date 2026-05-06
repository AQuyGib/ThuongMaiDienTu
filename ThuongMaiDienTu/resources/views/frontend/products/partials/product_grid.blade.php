<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @forelse($products as $product)
        <div class="product-card border rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
            <div class="relative h-48 mb-4">
                <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-contain">
            </div>
            <h3 class="text-lg font-semibold mb-2 truncate">{{ $product->name }}</h3>
            <p class="text-sm text-gray-500 mb-2">{{ $product->category->name }}</p>
            <div class="flex items-center justify-between mb-4">
                <span class="text-red-600 font-bold">{{ number_format($product->base_price, 0, ',', '.') }} đ</span>
                @if($product->old_price > $product->base_price)
                    <span class="text-sm text-gray-400 line-through">{{ number_format($product->old_price, 0, ',', '.') }} đ</span>
                @endif
            </div>
            <a href="{{ route('product.detail', $product->product_id) }}" class="block text-center bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors">
                Xem chi tiết
            </a>
        </div>
    @empty
        <div class="col-span-full text-center py-10">
            <p class="text-gray-500">Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>
        </div>
    @endforelse
</div>

<div class="mt-8">
    {{ $products->links() }}
</div>
