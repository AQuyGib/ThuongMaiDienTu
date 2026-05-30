@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- KHỐI 1: Đường dẫn điều hướng (Breadcrumb)
             Giúp người dùng dễ dàng định vị vị trí hiện tại trong danh mục sản phẩm.
        -->
        <nav class="flex items-center gap-2 text-sm md:text-base text-gray-600 mt-3 md:mt-5 mb-4 font-medium">
            <a href="{{ route('home') }}" class="flex items-center gap-1.5 hover:text-red-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Trang chủ
            </a>
            <span class="text-gray-300 font-normal">/</span>
            @if($currentCategory)
                <span class="text-gray-800 font-semibold">{{ $currentCategory->name }}</span>
            @else
                <span class="text-gray-800 font-semibold">Tất cả sản phẩm</span>
            @endif
        </nav>

        <!-- KHỐI 2: Thanh công cụ lọc ngang (Horizontal Filter Bar)
             Chứa toàn bộ giao diện bộ lọc nhanh, bộ lọc nâng cao, lọc tuần hoàn và sắp xếp.
        -->
        <div class="mb-8 space-y-6">
            <!-- Container bộ lọc chính -->
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex flex-col gap-4">
                    <!-- Danh sách các nút kích hoạt bộ lọc -->
                    <div
                        class="flex flex-nowrap md:flex-wrap gap-2 items-center overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
                        <!-- Nút kích hoạt Modal/Popup bộ lọc chung -->
                        <button type="button"
                            class="filter-trigger px-4 py-2 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition-all duration-200 flex items-center gap-2 shadow-md hover:shadow-blue-200 active:scale-95 whitespace-nowrap"
                            data-filter="filter">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.414a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            Bộ lọc
                        </button>

                        <!-- Các nút lọc nhanh đặc tính cơ bản: Sẵn hàng, Hàng mới, Giá, Hãng, Nhu cầu -->
                        <button type="button"
                            class="filter-trigger px-4 py-2 bg-gray-50 text-gray-600 rounded-xl font-medium text-sm hover:bg-white hover:text-red-600 hover:border-red-100 border border-transparent hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                            data-filter="stock">Sẵn hàng</button>
                        <button type="button"
                            class="filter-trigger px-4 py-2 bg-gray-50 text-gray-600 rounded-xl font-medium text-sm hover:bg-white hover:text-red-600 hover:border-red-100 border border-transparent hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                            data-filter="new">Hàng mới về</button>
                        <button type="button"
                            class="filter-trigger px-4 py-2 bg-gray-50 text-gray-600 rounded-xl font-medium text-sm hover:bg-white hover:text-red-600 hover:border-red-100 border border-transparent hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                            data-filter="price">Xem theo giá</button>
                        <button type="button"
                            class="filter-trigger px-4 py-2 bg-gray-50 text-gray-600 rounded-xl font-medium text-sm hover:bg-white hover:text-red-600 hover:border-red-100 border border-transparent hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                            data-filter="brand">Hãng sản xuất</button>
                        <button type="button"
                            class="filter-trigger px-4 py-2 bg-gray-50 text-gray-600 rounded-xl font-medium text-sm hover:bg-white hover:text-red-600 hover:border-red-100 border border-transparent hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                            data-filter="usage">Nhu cầu sử dụng</button>

                        <!-- Phân vùng inject các nút lọc đặc tính động (Dynamic Filters) bằng Javascript 
                             Ví dụ: Danh mục laptop sẽ tự sinh RAM, CPU; Điện thoại tự sinh Dung lượng, v.v.
                        -->
                        <div id="dynamic-filter-triggers"
                            class="flex flex-nowrap md:flex-wrap gap-2 items-center overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
                        </div>

                        <!-- Các bộ lọc nhanh đặc quyền DienMayPro (Nhu cầu, Dễ sửa chữa, Thân thiện môi trường) -->
                        <div
                            class="flex flex-nowrap md:flex-wrap gap-2 items-center overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
                            <span class="text-xs font-bold text-gray-400 uppercase mr-2 whitespace-nowrap">Gợi ý nhanh:</span>
                            <button type="button"
                                class="quick-filter-btn px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 hover:text-blue-800 rounded-xl font-medium text-sm hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                                data-name="needs" data-value="gaming">🎮 Chơi mượt Genshin</button>
                            <button type="button"
                                class="quick-filter-btn px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 hover:text-blue-800 rounded-xl font-medium text-sm hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                                data-name="needs" data-value="student">🎓 Học Web Dev</button>

                            <span
                                class="text-xs font-bold text-gray-400 uppercase ml-4 mr-2 whitespace-nowrap hidden md:inline">Kinh tế tuần hoàn:</span>
                            <button type="button"
                                class="quick-filter-btn px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 hover:text-blue-800 rounded-xl font-medium text-sm hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                                data-name="high_repairability" data-value="1">🔧 Dễ sửa chữa (9-10đ)</button>
                            <button type="button"
                                class="quick-filter-btn px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 hover:text-blue-800 rounded-xl font-medium text-sm hover:shadow-sm transition-all duration-200 whitespace-nowrap"
                                data-name="eco_friendly" data-value="1">🌱 Thân thiện môi trường</button>
                        </div>
                    </div>
                </div>

                <!-- Thẻ chứa các Tag bộ lọc đang hoạt động (Active Filters) 
                     Được Javascript cập nhật và hiển thị danh sách các nhãn lọc người dùng đang chọn.
                -->
                <div id="active-filters" class="flex flex-wrap items-center gap-2 py-3">
                    <button type="button" id="clear-all-filters"
                        class="text-xs font-medium text-gray-400 hover:text-red-600 transition-colors underline ml-2 hidden">Bỏ chọn tất cả</button>
                </div>

                <!-- Danh sách các nút sắp xếp thứ tự hiển thị (Phổ biến, Hot, Giá tăng/giảm) -->
                <div
                    class="flex flex-nowrap md:flex-wrap gap-2 items-center pt-4 border-t border-gray-100 overflow-x-auto pb-2 md:pb-0 scrollbar-hide">
                    <span class="text-xs font-bold text-gray-400 uppercase mr-2 whitespace-nowrap">Sắp xếp:</span>
                    <button type="button"
                        class="sort-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-gray-200 text-gray-600 hover:border-red-200 hover:text-red-600 transition-all duration-200 whitespace-nowrap"
                        data-sort="newest">Phổ biến</button>
                    <button type="button"
                        class="sort-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-gray-200 text-gray-600 hover:border-red-200 hover:text-red-600 transition-all duration-200 whitespace-nowrap"
                        data-sort="promo">Khuyến mãi HOT</button>
                    <button type="button"
                        class="sort-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-gray-200 text-gray-600 hover:border-red-200 hover:text-red-600 transition-all duration-200 whitespace-nowrap"
                        data-sort="price_asc">Giá Thấp - Cao</button>
                    <button type="button"
                        class="sort-btn px-3 py-1.5 rounded-lg text-xs font-medium bg-white border border-gray-200 text-gray-600 hover:border-red-200 hover:text-red-600 transition-all duration-200 whitespace-nowrap"
                        data-sort="price_desc">Giá Cao - Thấp</button>
                </div>
            </div>

            <!-- Form ẩn thu thập tham số lọc (Hidden Form)
                 Sử dụng làm bối cảnh thu thập dữ liệu lọc để gửi yêu cầu AJAX bất đồng bộ lên Server-side.
            -->
            <form id="filter-form" class="hidden">
                <input type="hidden" name="category_id" id="filter-category-id">
                <input type="hidden" name="min_price" id="filter-min-price">
                <input type="hidden" name="max_price" id="filter-max-price">
                <input type="hidden" name="sort" id="filter-sort" value="newest">
                <input type="hidden" name="q" id="filter-q">
                <div id="dynamic-filter-inputs"></div>
                <div id="quick-filter-inputs"></div>
            </form>

            <!-- Khung chứa các cửa sổ nhỏ (Popups) hiển thị chi tiết khi bấm vào từng tiêu chí lọc -->
            <div id="filter-popups-container"></div>

            <!-- KHỐI 3: Danh sách sản phẩm (Product Grid)
                 Hiển thị tiêu đề danh mục, số lượng kết quả và lưới sản phẩm thực tế.
            -->
            <div class="flex flex-col gap-6">
                <!-- Thanh thống kê kết quả lọc -->
                <div
                    class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-50 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" width="20" height="20"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-800">
                                @if($currentCategory)
                                    {{ $currentCategory->name }} — Hiển thị <span id="product-count" class="text-red-600">{{ $products->total() }}</span> sản phẩm
                                @else
                                    Hiển thị <span id="product-count" class="text-red-600">{{ $products->total() }}</span> sản phẩm
                                @endif
                            </h1>
                        </div>
                    </div>
                    <!-- Nhãn giới hạn phân trang mặc định -->
                    <div
                        class="flex items-center gap-2 text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-full border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" width="16" height="16" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h-1v-4h-1m4 4h1v4h1m-1 4h-1v-4h-1" />
                        </svg>
                        <span class="font-medium">Hiển thị 12 sản phẩm/trang</span>
                    </div>
                </div>

                <!-- Lưới hiển thị danh sách sản phẩm động (Cập nhật bất đồng bộ qua AJAX) -->
                <div id="product-list-container" class="min-h-[600px] transition-all duration-500">
                    @include('frontend.products.partials.product_grid')
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                // Gán các biến danh mục ban đầu từ Laravel Controller sang Window global để file JS ngoài đọc và xử lý
                window.__INITIAL_CATEGORY_ID = '{{ $currentCategory->category_id ?? '' }}';
                window.__INITIAL_CATEGORY_NAME = '{{ $currentCategory->name ?? '' }}';
            </script>
            <!-- Tải tệp xử lý AJAX và cập nhật DOM động cho bộ lọc nâng cao -->
            <script src="{{ asset('assets/frontend/js/product-filter.js') }}"></script>
        @endpush
@endsection