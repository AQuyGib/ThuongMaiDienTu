@extends('layouts.app')

@section('title', 'So sánh sản phẩm')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 md:p-6 mb-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-1">
                <p class="text-sm font-medium text-blue-600">So sánh sản phẩm</p>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Đối chiếu nhanh các sản phẩm đã lưu</h1>
                    <button type="button" onclick="copyCompareLink()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-600 text-xs font-semibold hover:bg-blue-100 transition-colors">
                        <i class="fa-solid fa-share-nodes"></i>
                        Chia sẻ
                    </button>
                </div>
                <p id="compareMeta" class="text-sm text-gray-500">0 sản phẩm</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 items-center">
                <label class="relative inline-flex items-center cursor-pointer mr-2">
                    <input type="checkbox" id="diffOnlyCheckbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700">Chỉ hiện khác biệt</span>
                </label>
                <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200 transition-colors">
                    Quay lại danh sách
                </a>
                <button type="button" id="compareClearAllBtn" class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition-colors">
                    Xóa toàn bộ
                </button>
            </div>
        </div>
    </div>

    <div id="compareEmptyState" class="bg-white rounded-2xl border border-dashed border-gray-200 p-10 text-center shadow-sm">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 mb-4">
            <i class="fa-solid fa-scale-balanced text-2xl"></i>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Chưa có sản phẩm nào để so sánh</h2>
        <p class="text-gray-500 mb-6">Hãy bấm nút So sánh trên danh sách sản phẩm để thêm vào danh sách này.</p>
        <a href="{{ route('products.index') }}" class="inline-flex items-center px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-colors">
            Khám phá sản phẩm
        </a>
    </div>

    <div id="compareTableWrap" class="hidden bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead id="compareHead" class="bg-gray-50"></thead>
                <tbody id="compareBody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>
        <div id="compareMobileCards" class="md:hidden space-y-4 p-4"></div>
    </div>
</div>

@push('scripts')
<script>
    window.__COMPARE_PAGE__ = true;
    window.__SERVER_COMPARE_IDS__ = @json($serverCompareIds ?? []);
</script>
@endpush
@endsection
