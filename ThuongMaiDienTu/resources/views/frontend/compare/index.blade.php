@extends('layouts.app')

@section('title', 'So sánh sản phẩm - Trải nghiệm mua sắm thông minh')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- KHU VỰC TIÊU ĐỀ & ĐIỀU KHIỂN BẢNG SO SÁNH -->
    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-xl shadow-blue-900/5 p-6 md:p-8 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 flex-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-widest">
                    <i class="fa-solid fa-scale-balanced animate-pulse"></i> So sánh sản phẩm
                </div>
                <h1 class="text-2xl md:text-4xl font-black text-gray-900 tracking-tight leading-tight">
                    Đối chiếu nhanh các sản phẩm đã lưu
                </h1>
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    <!-- Hiển thị số lượng sản phẩm đang có trong hàng chờ so sánh -->
                    <span id="compareMeta" class="flex items-center gap-2 bg-gray-50 px-3 py-1 rounded-lg border border-gray-100">
                        <i class="fa-solid fa-layer-group text-blue-500"></i> 
                        <span class="font-bold text-gray-700">0</span> sản phẩm
                    </span>
                    <!-- Nút chia sẻ liên kết danh sách so sánh -->
                    <button type="button" onclick="copyCompareLink()" class="group inline-flex items-center gap-2 text-blue-600 font-bold hover:text-blue-700 transition-colors bg-blue-50/50 px-3 py-1 rounded-lg">
                        <i class="fa-solid fa-share-nodes group-hover:rotate-12 transition-transform"></i> 
                        Chia sẻ liên kết
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 lg:justify-end">
                <!-- Nút bật/tắt ẩn đi những thông số giống nhau (Chỉ hiện các thuộc tính khác biệt) -->
                <div class="flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-2xl border border-gray-100 shadow-inner">
                    <span class="text-sm font-bold text-gray-600 leading-none">Chỉ hiện khác biệt</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="diffOnlyCheckbox" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
                
                <div class="flex items-center gap-2">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-white border border-gray-200 text-gray-700 text-sm font-bold hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm active:scale-95">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Quay lại
                    </a>
                    <!-- Nút xóa sạch giỏ hàng so sánh -->
                    <button type="button" id="compareClearAllBtn" class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-red-50 text-red-600 text-sm font-bold hover:bg-red-600 hover:text-white transition-all shadow-sm border border-red-100 active:scale-95">
                        <i class="fa-solid fa-trash-can mr-2"></i> Xóa tất cả
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- TRẠNG THÁI TRỐNG (EMPTY STATE): HIỂN THỊ KHI CHƯA CHỌN SẢN PHẨM NÀO -->
    <div id="compareEmptyState" class="hidden bg-white rounded-3xl border-2 border-dashed border-gray-200 p-16 text-center shadow-sm">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-blue-50 text-blue-500 mb-6 shadow-inner">
            <i class="fa-solid fa-scale-unbalanced text-4xl"></i>
        </div>
        <h2 class="text-2xl font-black text-gray-900 mb-3">Chưa có sản phẩm nào</h2>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">Hãy thêm ít nhất 2 sản phẩm để bắt đầu phân tích và so sánh các thông số kỹ thuật chi tiết.</p>
        <a href="{{ route('products.index') }}" class="inline-flex items-center px-8 py-4 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 active:scale-95">
            <i class="fa-solid fa-magnifying-glass mr-2"></i> Khám phá sản phẩm ngay
        </a>
    </div>

    <!-- BIỂU ĐỒ RADAR CHỈ SỐ MẠNH YẾU ĐA CHIỀU (RADAR POWER CHART) -->
    <div id="compareChartWrap" class="hidden bg-white rounded-[2rem] border border-gray-100 shadow-xl shadow-blue-900/5 p-8 mb-8 overflow-hidden relative">
        <div class="absolute top-0 left-0 w-2 h-full bg-blue-600"></div>
        <h2 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-3">
            <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <i class="fa-solid fa-chart-line"></i>
            </span>
            Phân tích sức mạnh đa chiều
        </h2>
        <div class="h-[450px] w-full flex justify-center items-center">
            <canvas id="compareRadarChart"></canvas>
        </div>
        <div class="mt-6 p-4 bg-yellow-50 rounded-2xl border border-yellow-100 flex items-start gap-3">
            <i class="fa-solid fa-circle-info text-yellow-600 mt-0.5"></i>
            <p class="text-sm text-yellow-800 leading-relaxed font-medium">
                Biểu đồ radar thể hiện các chỉ số tương đối giữa các sản phẩm được chọn. Điểm số được tính toán dựa trên cấu hình phần cứng và đánh giá thực tế từ người dùng.
            </p>
        </div>
    </div>

    <!-- BẢNG SO SÁNH THÔNG SỐ CHI TIẾT (TECHNICAL COMPARISON TABLE) -->
    <div id="compareTableWrap" class="hidden bg-white rounded-[2rem] border border-gray-100 shadow-2xl shadow-blue-900/10">
        <!-- Hiển thị bảng dạng ngang đầy đủ cho máy tính (Desktop View) -->
        <div class="hidden md:block overflow-x-auto custom-scrollbar rounded-[2rem]">
            <table class="min-w-full text-sm border-separate border-spacing-0">
                <!-- Javascript render tiêu đề và thông số cột tại đây -->
                <thead id="compareHead" class="bg-gray-50/50"></thead>
                <tbody id="compareBody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        
        <!-- Khung hiển thị dạng danh sách thẻ cho thiết bị di động (Mobile View) -->
        <div id="compareMobileCards" class="md:hidden space-y-6 p-4 bg-gray-50/50"></div>
    </div>
</div>

@push('styles')
<style>
    /* Thanh cuộn ngang (Horizontal scrollbar) cho bảng nhiều sản phẩm */
    .custom-scrollbar::-webkit-scrollbar {
        height: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Đảm bảo tiêu đề cột sản phẩm luôn cố định (Sticky) ở trên cùng khi cuộn dọc xem thông số */
    #compareHead th {
        position: sticky !important;
        top: 71px !important;
        z-index: 900 !important;
        background-color: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(12px) !important;
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    
    #compareHead th, #compareBody td, #compareBody th {
        border-right: 1px solid #f1f5f9;
    }
    #compareHead th:last-child, #compareBody td:last-child {
        border-right: none;
    }
    
    /* Làm nổi bật dòng đang trỏ chuột */
    .row-highlight {
        background-color: rgba(59, 130, 246, 0.03);
    }
</style>
@endpush

@push('scripts')
<!-- Nhúng thư viện ChartJS để vẽ biểu đồ radar so sánh cấu hình động -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Đánh dấu đây là trang so sánh sản phẩm để script so sánh dùng chung hoạt động
    window.__COMPARE_PAGE__ = true;
    // Đồng bộ ID các sản phẩm so sánh truyền từ controller xuống
    window.__SERVER_COMPARE_IDS__ = @json($serverCompareIds ?? []);
</script>
@endpush
@endsection
