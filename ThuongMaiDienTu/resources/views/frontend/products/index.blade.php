@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Bộ Lọc -->
        <aside class="w-full md:w-1/4 bg-white p-6 rounded-lg shadow-sm border h-fit">
            <h2 class="text-xl font-bold mb-6 border-b pb-2">Bộ Lọc Sản Phẩm</h2>
            
            <form id="filter-form">
                <!-- Lọc theo Danh mục -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2">Danh mục</label>
                    <select name="category_id" class="filter-checkbox w-full p-2 border rounded">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Lọc theo Giá -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2">Khoảng giá</label>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" placeholder="Từ" class="price-input w-1/2 p-2 border rounded">
                        <input type="number" name="max_price" placeholder="Đến" class="price-input w-1/2 p-2 border rounded">
                    </div>
                </div>

                <!-- Lọc theo RAM (Ví dụ) -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2">Dung lượng RAM</label>
                    <div class="space-y-2">
                        @foreach(['8GB', '16GB', '32GB', '64GB'] as $ram)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="ram[]" value="{{ $ram }}" class="filter-checkbox">
                                <span class="text-sm">{{ $ram }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Lọc theo ROM (Ví dụ) -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2">Dung lượng ROM</label>
                    <div class="space-y-2">
                        @foreach(['128GB', '256GB', '512GB', '1TB'] as $rom)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="rom[]" value="{{ $rom }}" class="filter-checkbox">
                                <span class="text-sm">{{ $rom }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="reset" class="w-full py-2 text-sm text-gray-500 hover:text-red-600 transition-colors">
                    Xóa tất cả bộ lọc
                </button>
            </form>
        </aside>

        <!-- Danh sách sản phẩm -->
        <main class="w-full md:w-3/4">
            <div id="product-list-container">
                @include('frontend.products.partials.product_grid')
            </div>
        </main>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/frontend/js/product-filter.js') }}"></script>
@endpush
@endsection
