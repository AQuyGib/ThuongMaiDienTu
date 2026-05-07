@extends('admin.layouts.master')
@section('title', 'Quản lý Bài viết & Nội dung')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý Bài viết & Nội dung</h1>
        <a href="{{ route('admin.articles.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow">
            <i class="fa-solid fa-plus mr-1"></i> Thêm Bài viết mới
        </a>
    </div>

    {{-- Thông báo session đã có trong layout master nên có thể bỏ ở đây nếu muốn, 
         nhưng để chắc chắn ta cứ giữ hoặc xóa nếu layout đã cover --}}
    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tiêu đề</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tác giả</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Format</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach($articles as $article)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="flex items-center">
                            @if($article->thumbnail)
                            <div class="flex-shrink-0 w-10 h-10">
                                <img class="w-full h-full rounded" src="{{ asset('storage/' . $article->thumbnail) }}" alt="" />
                            </div>
                            @endif
                            <div class="ml-3">
                                <p class="text-gray-900 whitespace-no-wrap font-medium">{{ Str::limit($article->title, 50) }}</p>
                                @if($article->related_ticket_id)
                                    <span class="text-xs text-blue-500"><i class="fas fa-tools"></i> Liên kết đơn sửa chữa: #{{ $article->related_ticket_id }}</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 whitespace-no-wrap">{{ $article->author->name ?? 'Unknown' }}</p>
                        @if($article->author_type === 'admin')
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-200 rounded-full">Admin</span>
                        @else
                            <span class="inline-block px-2 py-1 text-xs font-semibold text-orange-800 bg-orange-200 rounded-full">Customer</span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <p class="text-gray-900 whitespace-no-wrap capitalize">{{ $article->format_type }}</p>
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        @if($article->status === 'approved')
                            <span class="relative inline-block px-3 py-1 font-semibold text-green-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-green-200 opacity-50 rounded-full"></span>
                                <span class="relative">Đã duyệt</span>
                            </span>
                        @elseif($article->status === 'pending')
                            <span class="relative inline-block px-3 py-1 font-semibold text-yellow-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-yellow-200 opacity-50 rounded-full"></span>
                                <span class="relative">Chờ duyệt</span>
                            </span>
                        @else
                            <span class="relative inline-block px-3 py-1 font-semibold text-red-900 leading-tight">
                                <span aria-hidden class="absolute inset-0 bg-red-200 opacity-50 rounded-full"></span>
                                <span class="relative">Từ chối</span>
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                        <div class="flex justify-center items-center space-x-2">
                            <a href="{{ route('admin.articles.edit', $article->article_id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-100 hover:bg-indigo-200 p-2 px-3 rounded-full transition-colors" title="Chỉnh sửa">
                               Sửa
                            </a>
                            
                            <form action="{{ route('admin.articles.destroy', $article->article_id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa bài viết này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-100 hover:bg-red-200 p-2 px-3 rounded-full transition-colors" title="Xóa">
                                   Xóa
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection
