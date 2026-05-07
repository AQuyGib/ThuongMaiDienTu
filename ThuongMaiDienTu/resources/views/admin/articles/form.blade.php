@extends('admin.layouts.master')
@section('title', $article->exists ? 'Cập nhật Bài viết' : 'Thêm Bài viết mới')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">{{ $article->exists ? 'Cập nhật Bài viết' : 'Thêm Bài viết mới' }}</h1>
        <a href="{{ route('admin.articles.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Quay lại danh sách
        </a>
    </div>

    @if($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
        <ul>
            @foreach($errors->all() as $error)
                <li>- {{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ $article->exists ? route('admin.articles.update', $article->article_id) : route('admin.articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($article->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Cột trái: Nội dung chính -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tab 1: General -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-4">1. Thông tin chung & Nội dung</h2>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Tiêu đề bài viết <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $article->title) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Mô tả ngắn (Summary)</label>
                        <textarea name="summary" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('summary', $article->summary) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2 flex justify-between items-center">
                            <span>Nội dung chi tiết (Shoppable Content) <span class="text-red-500">*</span></span>
                            <button type="button" class="text-sm bg-blue-50 text-blue-600 px-2 py-1 rounded border border-blue-200 hover:bg-blue-100" onclick="alert('Tính năng chèn sản phẩm sẽ tích hợp với Plugin Editor trong giai đoạn sau.')">
                                <i class="fa-solid fa-cart-plus mr-1"></i> Chèn thẻ Sản phẩm
                            </button>
                        </label>
                        <textarea name="content" id="content_editor" rows="15" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('content', $article->content) }}</textarea>
                    </div>
                </div>

                <!-- Tab 2: Ecosystem -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-4 text-blue-800">2. Nhật ký Hồi sinh (Ecosystem)</h2>
                    <p class="text-sm text-gray-600 mb-4">Liên kết bài viết này với một đơn sửa chữa thực tế để tạo case study cho khách hàng, hỗ trợ chiến lược Right to Repair.</p>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Mã Đơn Sửa Chữa (Repair Ticket ID)</label>
                        <input type="number" name="related_ticket_id" value="{{ old('related_ticket_id', $article->related_ticket_id) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="VD: 1">
                        <small class="text-gray-500 block mt-1 italic">Khi nhập ID này, bài viết sẽ tự động lấy dữ liệu từ module Sửa chữa.</small>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Cài đặt và Duyệt bài -->
            <div class="space-y-6">
                
                <!-- Cài đặt hiển thị -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-lg font-semibold border-b pb-2 mb-4">Cài đặt hiển thị</h2>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Định dạng hiển thị (Format)</label>
                        <select name="format_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="standard" {{ old('format_type', $article->format_type) === 'standard' ? 'selected' : '' }}>Standard (Tin tức thường)</option>
                            <option value="lookbook" {{ old('format_type', $article->format_type) === 'lookbook' ? 'selected' : '' }}>Lookbook (Băng chuyền ảnh)</option>
                            <option value="storytelling" {{ old('format_type', $article->format_type) === 'storytelling' ? 'selected' : '' }}>Storytelling (Parallax Kể chuyện)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Ảnh đại diện (Thumbnail)</label>
                        <input type="file" name="thumbnail_file" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        @if($article->thumbnail)
                            <div class="mt-3">
                                <p class="text-sm text-gray-500 mb-1">Ảnh hiện tại:</p>
                                <img src="{{ $article->thumbnail }}" alt="Thumbnail" class="w-full max-h-40 object-cover rounded-lg border border-gray-200">
                            </div>
                        @endif
                        <small class="text-gray-500 block mt-1">Chấp nhận JPG, PNG, WEBP (Tối đa 2MB).</small>
                    </div>
                </div>

                <!-- Tab 3: Gamification / Duyệt bài -->
                @if($article->exists && $article->author_type === 'customer')
                <div class="bg-white shadow-md rounded-lg p-6 border-2 border-orange-200">
                    <h2 class="text-lg font-semibold text-orange-600 border-b pb-2 mb-4"><i class="fas fa-gift"></i> UGC & Gamification</h2>
                    
                    <div class="mb-4">
                        <p class="text-sm font-bold">Trạng thái hiện tại: 
                            <span class="text-{{ $article->status === 'approved' ? 'green' : ($article->status === 'pending' ? 'yellow' : 'red') }}-600 uppercase">{{ $article->status }}</span>
                        </p>
                        <p class="text-sm">Tác giả: {{ $article->author->name ?? 'Unknown' }} (Customer)</p>
                    </div>

                    @if($article->status === 'pending')
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <p class="text-sm mb-2 text-gray-700">Hãy thưởng điểm để tri ân đóng góp của khách hàng này!</p>
                        <div class="flex space-x-2">
                            <input type="number" form="approve_form" name="points" value="" class="w-24 px-2 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-orange-500" placeholder="500">
                            <button type="submit" form="approve_form" class="bg-orange-500 hover:bg-orange-600 text-white font-bold py-1 px-3 rounded text-sm w-full transition-colors shadow-sm">
                                Duyệt & Cộng Điểm
                            </button>
                        </div>
                    </div>
                    @elseif($article->status === 'approved')
                    <p class="text-sm text-green-600 font-bold mt-2">Đã cộng {{ $article->reward_points_awarded }} điểm cho khách hàng.</p>
                    @endif
                </div>
                @endif

                <!-- Submit & Preview -->
                <div class="bg-white shadow-md rounded-lg p-6 flex flex-col gap-3">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-lg shadow transition-colors">
                        <i class="fas fa-save"></i> {{ $article->exists ? 'Cập nhật Bài viết' : 'Đăng Bài viết' }}
                    </button>
                    @if($article->exists)
                        <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg text-center shadow transition-colors block">
                            <i class="fa-solid fa-eye"></i> Xem trước bài viết
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </form>
    
    @if($article->exists && $article->author_type === 'customer' && $article->status === 'pending')
    <!-- Form ẩn để gọi route Approve -->
    <form id="approve_form" action="{{ route('admin.articles.approve', $article->article_id) }}" method="POST" class="hidden">
        @csrf
    </form>
    @endif
</div>

<!-- Tích hợp TinyMCE với API Key -->
<script src="https://cdn.tiny.cloud/1/89asu0xgqkmcokiuaz1g66ht63x9x3arxhyysxrboqlg0k5u/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#content_editor',
        height: 500,
        plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking save table directionality emoticons template',
        toolbar: 'undo redo | blocks | bold italic strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | forecolor backcolor | code',
        menubar: false
    });
</script>
@endsection
