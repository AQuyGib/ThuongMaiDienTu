<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($products as $product)
        <div
            class="product-card group relative bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-2 border border-gray-100">
            <!-- Badges -->
            <div class="absolute top-3 left-3 z-10 flex flex-col gap-2">
                @if($product->discount_percent > 0)
                    <span class="bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-sm">
                        -{{ $product->discount_percent }}%
                    </span>
                @endif
                <span class="bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-sm">
                    Trả góp 0%
                </span>
            </div>

            <!-- Image Container -->
            <div class="relative h-48 overflow-hidden bg-gray-100">
                <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    onerror="this.src='https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&q=80'; this.onerror=null;">
                <!-- Quick Actions -->
                <div
                    class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <button
                        class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-red-500 hover:text-white transition-colors"
                        title="Yêu thích">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                        </svg>
                    </button>
                    <button
                        class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-blue-500 hover:text-white transition-colors"
                        title="So sánh">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Product Info -->
            <div class="p-4">
                <!-- Category -->
                <div class="text-xs text-gray-500 mb-1">
                    {{ $product->category->name ?? 'Sản phẩm' }}
                </div>

                <!-- Product Name -->
                <h3 class="text-base font-bold text-gray-800 mb-2 line-clamp-2 min-h-[40px]" title="{{ $product->name }}">
                    {{ $product->name }}
                </h3>

                <!-- Specifications Highlight -->
                <div class="flex flex-wrap gap-1 mb-3">
                    @php
                        // Lấy cấu hình highlights từ danh mục
                        $filterConfig = $product->category->filter_config ?? [];
                        if (is_string($filterConfig)) $filterConfig = json_decode($filterConfig, true);
                        
                        $highlightConfig = $filterConfig['highlights'] ?? [];
                        
                        // Parse JSON specifications của sản phẩm
                        $specs = is_string($product->specifications) ? json_decode($product->specifications, true) : ($product->specifications ?? []);
                        if (!is_array($specs)) $specs = [];

                        $highlights = [];
                        
                        if (!empty($highlightConfig)) {
                            // Render theo cấu hình của Admin (VD: ['ram' => 'RAM: ', 'cpu' => 'CPU: '])
                            foreach ($highlightConfig as $key => $prefix) {
                                if (isset($specs[$key])) {
                                    $val = is_array($specs[$key]) ? implode(', ', $specs[$key]) : $specs[$key];
                                    $highlights[] = $prefix . $val;
                                }
                            }
                        } else {
                            // Fallback nếu danh mục chưa cấu hình highlights
                            if (isset($specs['ram'])) $highlights[] = 'RAM: ' . (is_array($specs['ram']) ? implode(',', $specs['ram']) : $specs['ram']);
                            if (isset($specs['rom'])) $highlights[] = 'ROM: ' . (is_array($specs['rom']) ? implode(',', $specs['rom']) : $specs['rom']);
                        }
                    @endphp

                    @foreach($highlights as $hl)
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded border border-gray-200">{{ $hl }}</span>
                    @endforeach
                </div>

                <!-- Price -->
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-lg font-bold text-red-600">
                        {{ number_format($product->base_price, 0, ',', '.') }} ₫
                    </span>
                    @if($product->old_price && $product->old_price > $product->base_price)
                        <span class="text-sm text-gray-400 line-through">
                            {{ number_format($product->old_price, 0, ',', '.') }} ₫
                        </span>
                    @endif
                </div>

                <!-- Promotions -->
                <div class="flex flex-wrap gap-1 mb-3">
                    @if($product->discount_percent > 0)
                        <span class="text-xs text-green-600 font-medium">
                            Giảm {{ $product->discount_percent }}%
                        </span>
                    @endif
                    <span class="text-xs text-green-600 font-medium">
                        Sinh viên giảm thêm
                    </span>
                </div>

                <!-- Rating -->
                <div class="flex items-center gap-1 mb-3">
                    <div class="flex text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($product->rating ?? 0))
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24">
                                    <path
                                        d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                                </svg>
                            @else
                                <svg class="w-3 h-3 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                                </svg>
                            @endif
                        @endfor
                    </div>
                    <span class="text-xs text-gray-500">
                        ({{ $product->review_count ?? 0 }})
                    </span>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <a href="{{ route('product.detail', $product->product_id) }}"
                        class="flex-1 bg-blue-600 text-white text-center py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                        Xem chi tiết
                    </a>
                    <button
                        class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                        Mua ngay
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full text-center py-12">
            <div class="inline-block p-4 rounded-full bg-gray-100 mb-3">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <p class="text-gray-500 text-lg">Không tìm thấy sản phẩm nào phù hợp với bộ lọc.</p>
            <p class="text-gray-400 text-sm mt-1">Hãy thử thay đổi bộ lọc để tìm sản phẩm phù hợp</p>
        </div>
    @endforelse
</div>

<div class="mt-8">
    {{ $products->links() }}
</div>