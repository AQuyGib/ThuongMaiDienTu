@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <div class="w-full md:w-1/3">
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <h1 class="text-2xl font-bold mb-4 text-gray-800">{{ $product->name }}</h1>
                <div class="flex items-center gap-2 mb-4">
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">
                        {{ $product->category->name }}
                    </span>
                </div>
                <div class="text-3xl font-bold text-red-600 mb-6">
                    {{ number_format($product->base_price, 0, ',', '.') }} đ
                </div>
            </div>
        </div>
        
        <div class="w-full md:w-2/3 bg-white p-6 rounded-lg shadow-sm border">
            <div class="mb-6">
                <img src="{{ asset('uploads/products/' . $product->image) }}" alt="{{ $product->name }}" class="w-full max-w-md mx-auto rounded-lg shadow-sm">
            </div>
            <div class="prose max-w-none text-gray-700">
                <h3 class="text-lg font-semibold mb-2">Mô tả sản phẩm</h3>
                <p>{{ $product->description ?? 'Không có mô tả chi tiết cho sản phẩm này.' }}</p>
            </div>
            <div class="mt-8 flex gap-4">
                <a href="{{ route('products.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Quay lại danh sách
                </a>
                <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Thêm vào giỏ hàng
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
