@php
    $productCount = isset($products) ? $products->count() : 0;
@endphp

@if($productCount > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            @php
                $specs = is_array($product->specifications) ? $product->specifications : (json_decode($product->specifications ?? '[]', true) ?: []);
                $isOnCompare = in_array($product->product_id, $compareIds ?? [], true);
                $imageUrl = $product->thumbnail ?? 'https://loremflickr.com/400/400/technology?lock=' . $product->product_id;
            @endphp
            <div class="product-card group relative overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg" data-product-id="{{ $product->product_id }}">
                <div class="relative p-4">
                    <div class="absolute left-3 top-3 z-10 flex items-center gap-2">
                        <span class="compare-status-badge {{ $isOnCompare ? '' : 'hidden' }} inline-flex items-center gap-1.5 rounded-full bg-blue-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow-md shadow-blue-100">
                            <i class="fa-solid fa-check"></i>
                            <span>Đã so sánh</span>
                        </span>
                    </div>
                    <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="mx-auto h-40 w-full object-contain transition-transform duration-500 group-hover:scale-105" onerror="this.src='https://loremflickr.com/400/400/technology?lock={{ $product->product_id }}'; this.onerror=null;">
                    <div class="absolute right-3 top-3 flex flex-col gap-2 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                        <button class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white text-gray-600 shadow-md transition-all hover:bg-red-500 hover:text-white" title="Yêu thích" type="button">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" /></svg>
                        </button>
                        <button type="button" class="compare-card-btn inline-flex h-9 min-w-9 items-center justify-center gap-2 rounded-full bg-white px-3 text-xs font-semibold text-blue-600 shadow-md transition-all duration-200 hover:bg-blue-600 hover:text-white hover:-translate-y-0.5 active:scale-95 {{ $isOnCompare ? 'bg-blue-600 text-white ring-2 ring-blue-200' : '' }}" title="{{ $isOnCompare ? 'Đã so sánh' : 'So sánh' }}" data-product-id="{{ $product->product_id }}" data-compare-state-label="{{ $isOnCompare ? 'Đã so sánh' : 'So sánh' }}" onclick="event.preventDefault(); event.stopPropagation(); addToCompare({{ $product->product_id }})">
                            <span class="compare-card-btn-spinner hidden animate-spin"><i class="fa-solid fa-spinner"></i></span>
                            <svg class="compare-card-btn-icon h-4 w-4 {{ $isOnCompare ? 'text-white' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <span class="compare-card-btn-label hidden sm:inline">{{ $isOnCompare ? 'Đã so sánh' : 'So sánh' }}</span>
                        </button>
                    </div>
                </div>

                <div class="px-4 pb-4">
                    <div class="text-xs text-gray-500 mb-1">{{ $product->category->name ?? 'Sản phẩm' }}</div>
                    <h3 class="mb-2 min-h-[40px] text-base font-bold text-gray-800 line-clamp-2" title="{{ $product->name }}">{{ $product->name }}</h3>
                    @if(!empty($specs))
                        <div class="mb-3 flex flex-wrap gap-1.5">
                            @foreach(array_slice($specs, 0, 3, true) as $key => $value)
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-600">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <div class="text-lg font-extrabold text-red-600">{{ number_format($product->base_price, 0, ',', '.') }}đ</div>
                            @if(!empty($product->old_price))
                                <div class="text-xs text-gray-400 line-through">{{ number_format($product->old_price, 0, ',', '.') }}đ</div>
                            @endif
                        </div>
                        <a href="{{ route('product.detail', $product->product_id) }}" class="rounded-xl bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition-colors hover:bg-blue-100">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-500">
        Không có sản phẩm phù hợp với bộ lọc hiện tại.
    </div>
@endif
