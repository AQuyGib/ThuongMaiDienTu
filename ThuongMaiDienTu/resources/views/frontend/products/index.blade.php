@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar Bộ Lọc -->
        <aside class="w-full md:w-1/4 bg-white p-6 rounded-lg shadow-sm border h-fit sticky top-20">
            <div class="flex items-center justify-between mb-6 border-b pb-2">
                <h2 class="text-xl font-bold">Bộ Lọc Nâng Cao</h2>
                <button type="button" id="reset-filters" class="text-xs text-blue-600 hover:underline">Xóa tất cả</button>
            </div>
            
            <form id="filter-form">
                <!-- Lọc theo Danh mục -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2 text-gray-700">Danh mục</label>
                    <select name="category_id" class="filter-input w-full p-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Lọc theo Giá -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2 text-gray-700">Khoảng giá (VNĐ)</label>
                    <div class="flex gap-2">
                        <input type="number" name="min_price" placeholder="Từ" class="price-input w-1/2 p-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                        <input type="number" name="max_price" placeholder="Đến" class="price-input w-1/2 p-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <!-- Lọc theo RAM -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2 text-gray-700">Dung lượng RAM</label>
                    <div class="space-y-2">
                        @foreach(['8GB', '16GB', '32GB', '64GB'] as $ram)
                            <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="checkbox" name="ram[]" value="{{ $ram }}" class="filter-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-600">{{ $ram }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Lọc theo ROM -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2 text-gray-700">Dung lượng ROM</label>
                    <div class="space-y-2">
                        @foreach(['128GB', '256GB', '512GB', '1TB'] as $rom)
                            <label class="flex items-center gap-2 cursor-pointer hover:text-blue-600 transition-colors">
                                <input type="checkbox" name="rom[]" value="{{ $rom }}" class="filter-checkbox w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-600">{{ $rom }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Sắp xếp -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2 text-gray-700">Sắp xếp theo</label>
                    <select name="sort" class="filter-input w-full p-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="newest">Mới nhất</option>
                        <option value="price_asc">Giá tăng dần</option>
                        <option value="price_desc">Giá giảm dần</option>
                        <option value="name_asc">Tên A-Z</option>
                        <option value="name_desc">Tên Z-A</option>
                    </select>
                </div>

                <!-- Tìm kiếm nhanh -->
                <div class="mb-6">
                    <label class="block font-semibold mb-2 text-gray-700">Tìm kiếm nhanh</label>
                    <input type="text" name="q" placeholder="Tên sản phẩm..." class="filter-input w-full p-2 border rounded-md focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </form>
        </aside>

        <!-- Danh sách sản phẩm -->
        <main class="w-full md:w-3/4">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Sản phẩm <span id="product-count" class="text-blue-600">...</span></h1>
                <div class="text-sm text-gray-500">Hiển thị 12 sản phẩm mỗi trang</div>
            </div>

            <div id="product-list-container" class="min-h-[600px]">
                @include('frontend.products.partials.product_grid')
            </div>
        </main>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/frontend/js/product-filter.js') }}"></script>
@endpush
@endsection
