@extends('admin.layouts.master')
@section('title', 'Quản lý Bài viết & Nội dung')

@section('content')
    <style>
        /* Custom Dropdown Styling */
        .filter-dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            margin-top: 0.5rem;
            width: 200px;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            z-index: 50;
        }

        .filter-dropdown-menu.show {
            display: block;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom Pagination Styling */
        .pagination {
            display: flex;
            gap: 0.5rem;
        }

        .pagination li {
            list-style: none;
        }

        .pagination li a,
        .pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background-color: white;
            border: 1px solid #f3f4f6;
            color: #4b5563;
            font-size: 0.875rem;
            font-weight: 700;
            transition: all 0.2s;
        }

        .pagination li.active span {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
            shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .pagination li a:hover {
            background-color: #eff6ff;
            color: #2563eb;
            border-color: #bfdbfe;
        }

        /* Hide default laravel pagination labels */
        .custom-pagination nav p {
            display: none !important;
        }
        .custom-pagination nav > div:first-child {
            display: none !important;
        }
    </style>

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Quản lý Bài viết & Nội dung</h1>

            <a href="{{ route('admin.articles.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow flex items-center transition-colors">
                <i class="fa-solid fa-plus mr-2"></i> Thêm Bài viết mới
            </a>
        </div>

        <!-- Hidden filter form -->
        <form id="headerFilterForm" action="{{ route('admin.articles.index') }}" method="GET" class="hidden">
            <input type="hidden" name="q" value="{{ request('q') }}">
            <input type="hidden" name="author_type" id="hidden_author_type" value="{{ request('author_type') }}">
            <input type="hidden" name="format_type" id="hidden_format_type" value="{{ request('format_type') }}">
            <input type="hidden" name="status" id="hidden_status" value="{{ request('status') }}">
        </form>

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white shadow-md rounded-xl overflow-hidden border border-gray-100">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th
                            class="px-5 py-4 border-b border-gray-200 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                            Nội dung bài viết
                        </th>

                        {{-- Lọc Tác giả --}}
                        <th
                            class="px-5 py-4 border-b border-gray-200 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                            <div class="relative inline-block text-left filter-container">
                                <button type="button"
                                    class="dropdown-trigger flex items-center gap-1.5 hover:text-blue-600 transition-colors py-1 px-2 rounded-md hover:bg-white border border-transparent hover:border-gray-200 shadow-none hover:shadow-sm">
                                    TÁC GIẢ <i
                                        class="fa-solid fa-chevron-down text-[9px] {{ request('author_type') ? 'text-blue-600' : 'text-gray-300' }}"></i>
                                </button>
                                <div class="filter-dropdown-menu">
                                    <div
                                        class="p-2 border-b border-gray-50 bg-gray-50/50 text-[10px] text-gray-400 font-bold px-4">
                                        LỌC THEO VAI TRÒ</div>
                                    <div class="py-1">
                                        <a href="{{ route('admin.articles.index', array_merge(request()->query(), ['author_type' => ''])) }}"
                                            class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 {{ !request('author_type') ? 'bg-blue-50 font-bold text-blue-600' : '' }}">
                                            Tất cả
                                            @if(!request('author_type')) <i class="fa-solid fa-check text-[10px]"></i>
                                            @endif
                                        </a>
                                        <a href="{{ route('admin.articles.index', array_merge(request()->query(), ['author_type' => 'admin'])) }}"
                                            class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 {{ request('author_type') === 'admin' ? 'bg-blue-50 font-bold text-blue-600' : '' }}">
                                            Admin
                                            @if(request('author_type') === 'admin') <i
                                            class="fa-solid fa-check text-[10px]"></i> @endif
                                        </a>
                                        <a href="{{ route('admin.articles.index', array_merge(request()->query(), ['author_type' => 'customer'])) }}"
                                            class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 {{ request('author_type') === 'customer' ? 'bg-blue-50 font-bold text-blue-600' : '' }}">
                                            Customer
                                            @if(request('author_type') === 'customer') <i
                                            class="fa-solid fa-check text-[10px]"></i> @endif
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Lọc Format --}}
                        <th
                            class="px-5 py-4 border-b border-gray-200 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                            <div class="relative inline-block text-left filter-container">
                                <button type="button"
                                    class="dropdown-trigger flex items-center gap-1.5 hover:text-blue-600 transition-colors py-1 px-2 rounded-md hover:bg-white border border-transparent hover:border-gray-200 shadow-none hover:shadow-sm">
                                    FORMAT <i
                                        class="fa-solid fa-chevron-down text-[9px] {{ request('format_type') ? 'text-blue-600' : 'text-gray-300' }}"></i>
                                </button>
                                <div class="filter-dropdown-menu">
                                    <div
                                        class="p-2 border-b border-gray-50 bg-gray-50/50 text-[10px] text-gray-400 font-bold px-4">
                                        LỌC ĐỊNH DẠNG</div>
                                    <div class="py-1">
                                        @php $formats = ['' => 'Tất cả', 'standard' => 'Standard', 'lookbook' => 'Lookbook', 'storytelling' => 'Storytelling']; @endphp
                                        @foreach($formats as $key => $label)
                                            <a href="{{ route('admin.articles.index', array_merge(request()->query(), ['format_type' => $key])) }}"
                                                class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 {{ request('format_type') == $key ? 'bg-blue-50 font-bold text-blue-600' : '' }}">
                                                {{ $label }}
                                                @if(request('format_type') == $key) <i
                                                class="fa-solid fa-check text-[10px]"></i> @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </th>

                        {{-- Lọc Trạng thái --}}
                        <th
                            class="px-5 py-4 border-b border-gray-200 text-left text-xs font-bold text-gray-500 uppercase tracking-widest">
                            <div class="relative inline-block text-left filter-container">
                                <button type="button"
                                    class="dropdown-trigger flex items-center gap-1.5 hover:text-blue-600 transition-colors py-1 px-2 rounded-md hover:bg-white border border-transparent hover:border-gray-200 shadow-none hover:shadow-sm">
                                    TRẠNG THÁI <i
                                        class="fa-solid fa-chevron-down text-[9px] {{ request('status') ? 'text-blue-600' : 'text-gray-300' }}"></i>
                                </button>
                                <div class="filter-dropdown-menu">
                                    <div
                                        class="p-2 border-b border-gray-50 bg-gray-50/50 text-[10px] text-gray-400 font-bold px-4">
                                        LỌC TRẠNG THÁI</div>
                                    <div class="py-1">
                                        @php $statuses = ['' => 'Tất cả', 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối']; @endphp
                                        @foreach($statuses as $key => $label)
                                            <a href="{{ route('admin.articles.index', array_merge(request()->query(), ['status' => $key])) }}"
                                                class="flex items-center justify-between px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 {{ request('status') == $key ? 'bg-blue-50 font-bold text-blue-600' : '' }}">
                                                {{ $label }}
                                                @if(request('status') == $key) <i class="fa-solid fa-check text-[10px]"></i>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </th>

                        <th
                            class="px-5 py-4 border-b border-gray-200 text-center text-xs font-bold text-gray-500 uppercase tracking-widest">
                            <div class="flex justify-center items-center gap-2">
                                HÀNH ĐỘNG
                                @if(request()->anyFilled(['author_type', 'format_type', 'status']))
                                    <a href="{{ route('admin.articles.index', ['q' => request('q')]) }}" title="Xóa tất cả lọc"
                                        class="text-red-500 hover:bg-red-50 p-1.5 rounded-full transition-all">
                                        <i class="fa-solid fa-filter-circle-xmark"></i>
                                    </a>
                                @endif
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($articles as $article)
                        <tr class="hover:bg-blue-50/30 transition-all group">
                            <td class="px-5 py-5 bg-white text-sm">
                                <div class="flex items-center">
                                    @if($article->thumbnail)
                                        <div class="flex-shrink-0"
                                            style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; overflow: hidden; border-radius: 0.75rem;">
                                            <img class="shadow-sm ring-1 ring-gray-100 group-hover:ring-blue-200 transition-all"
                                                style="width: 100%; height: 100%; object-fit: cover;"
                                                src="{{ asset($article->thumbnail) }}" alt="" />
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <p
                                            class="text-gray-900 font-bold leading-tight mb-1 group-hover:text-blue-700 transition-colors">
                                            {{ Str::limit($article->title, 70) }}</p>
                                        <div class="flex items-center gap-3">
                                            @if($article->related_ticket_id)
                                                <span
                                                    class="text-[10px] font-bold bg-blue-100/50 text-blue-700 px-2 py-0.5 rounded border border-blue-200/50">
                                                    REPAIR #{{ $article->related_ticket_id }}
                                                </span>
                                            @endif
                                            <span class="text-[11px] text-gray-400 font-medium flex items-center gap-1">
                                                <i class="fa-regular fa-calendar-check"></i>
                                                {{ $article->created_at->format('d/m/Y') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-5 bg-white text-sm">
                                <div class="flex flex-col gap-1">
                                    <span
                                        class="text-gray-900 font-bold whitespace-nowrap">{{ $article->author->full_name ?? 'N/A' }}</span>
                                    @if($article->author_type === 'admin')
                                        <span
                                            class="w-fit px-2 py-0.5 text-[9px] font-black uppercase tracking-tighter text-indigo-600 bg-indigo-50 rounded border border-indigo-100">ADMIN
                                            STAFF</span>
                                    @else
                                        <span
                                            class="w-fit px-2 py-0.5 text-[9px] font-black uppercase tracking-tighter text-emerald-600 bg-emerald-50 rounded border border-emerald-100">CUSTOMER
                                            HUB</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-5 bg-white text-sm text-center">
                                <span
                                    class="inline-block px-3 py-1 text-[11px] font-bold text-gray-500 bg-gray-50 border border-gray-100 rounded-lg uppercase tracking-tight">
                                    {{ $article->format_type }}
                                </span>
                            </td>
                            <td class="px-5 py-5 bg-white text-sm">
                                @if($article->status === 'approved')
                                    <div class="flex items-center gap-2 text-green-600">
                                        <div class="w-2 h-2 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]"></div>
                                        <span class="text-xs font-black uppercase tracking-wider">Đã duyệt</span>
                                    </div>
                                @elseif($article->status === 'pending')
                                    <div class="flex items-center gap-2 text-amber-500">
                                        <div class="w-2 h-2 rounded-full bg-amber-400 animate-ping absolute"></div>
                                        <div class="w-2 h-2 rounded-full bg-amber-400 relative"></div>
                                        <span class="text-xs font-black uppercase tracking-wider">Chờ xử lý</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-2 text-rose-500">
                                        <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                                        <span class="text-xs font-black uppercase tracking-wider">Từ chối</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-5 bg-white text-sm">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="{{ route('admin.articles.edit', $article->article_id) }}"
                                        class="flex items-center justify-center w-9 h-9 text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-xl transition-all shadow-sm border border-indigo-100"
                                        title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen-nib text-sm"></i>
                                    </a>

                                    <form action="{{ route('admin.articles.destroy', $article->article_id) }}" method="POST"
                                        class="inline" onsubmit="return confirm('Xóa bài viết này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="flex items-center justify-center w-9 h-9 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all shadow-sm border border-rose-100"
                                            title="Xóa">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                {{-- Nhóm thông tin bên trái --}}
                <div class="text-[12px] font-bold text-gray-700 uppercase tracking-tight">
                    Hiển thị {{ $articles->count() }} / {{ $articles->total() }} bài viết
                </div>

                {{-- Thanh phân trang bên phải --}}
                <div class="custom-pagination">
                    {{ $articles->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const triggers = document.querySelectorAll('.dropdown-trigger');

            triggers.forEach(trigger => {
                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const menu = this.nextElementSibling;

                    // Close other menus
                    document.querySelectorAll('.filter-dropdown-menu').forEach(m => {
                        if (m !== menu) m.classList.remove('show');
                    });

                    menu.classList.toggle('show');
                });
            });

            // Close when clicking outside
            document.addEventListener('click', function () {
                document.querySelectorAll('.filter-dropdown-menu').forEach(m => {
                    m.classList.remove('show');
                });
            });

            // Prevent closing when clicking inside menu
            document.querySelectorAll('.filter-dropdown-menu').forEach(menu => {
                menu.addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            });
        });
    </script>
@endsection