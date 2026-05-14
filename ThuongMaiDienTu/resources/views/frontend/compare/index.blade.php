@extends('layouts.app')

@section('title', 'So sánh sản phẩm - Trải nghiệm mua sắm thông minh')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    {{-- Header Section --}}
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
                    <span id="compareMeta" class="flex items-center gap-2 bg-gray-50 px-3 py-1 rounded-lg border border-gray-100">
                        <i class="fa-solid fa-layer-group text-blue-500"></i> 
                        <span class="font-bold text-gray-700">0</span> sản phẩm
                    </span>
                    <button type="button" onclick="copyCompareLink()" class="group inline-flex items-center gap-2 text-blue-600 font-bold hover:text-blue-700 transition-colors bg-blue-50/50 px-3 py-1 rounded-lg">
                        <i class="fa-solid fa-share-nodes group-hover:rotate-12 transition-transform"></i> 
                        Chia sẻ liên kết
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-4 lg:justify-end">
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
                    <button type="button" id="compareClearAllBtn" class="inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-red-50 text-red-600 text-sm font-bold hover:bg-red-600 hover:text-white transition-all shadow-sm border border-red-100 active:scale-95">
                        <i class="fa-solid fa-trash-can mr-2"></i> Xóa tất cả
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty State --}}
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

    {{-- Radar Chart Section --}}
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

    {{-- Comparison Table --}}
    <div id="compareTableWrap" class="hidden bg-white rounded-[2rem] border border-gray-100 shadow-2xl shadow-blue-900/10">
        <div class="hidden md:block overflow-x-auto custom-scrollbar rounded-[2rem]">
            <table class="min-w-full text-sm border-separate border-spacing-0">
                <thead id="compareHead" class="bg-gray-50/50"></thead>
                <tbody id="compareBody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        {{-- Mobile View --}}
        <div id="compareMobileCards" class="md:hidden space-y-6 p-4 bg-gray-50/50"></div>
    </div>
</div>

@push('styles')
<style>
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
    
    #compareTableWrap {
        /* overflow: hidden; */ /* Tạm ẩn để test sticky */
    }

    #compareHead th {
        position: sticky !important;
        top: 71px !important;
        z-index: 900 !important;
        background-color: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(12px) !important;
        transition: all 0.3s ease;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    
    /* Đảm bảo border vẫn đẹp khi dùng border-separate */
    #compareHead th, #compareBody td, #compareBody th {
        border-right: 1px solid #f1f5f9;
    }
    #compareHead th:last-child, #compareBody td:last-child {
        border-right: none;
    }
    
    .row-highlight {
        background-color: rgba(59, 130, 246, 0.03);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    window.__COMPARE_PAGE__ = true;
    window.__SERVER_COMPARE_IDS__ = @json($serverCompareIds ?? []);
</script>
@endpush
@endsection

