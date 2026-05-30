@extends('admin.layouts.master')
@section('title', $article->exists ? 'Cập nhật Bài viết' : 'Thêm Bài viết mới')
@section('page-title', $article->exists ? 'Cập nhật Bài viết' : 'Thêm Bài viết mới')

@section('content')
<div class="space-y-6">
    <style>
        /* Card mờ ảo (glassmorphism) cho giao diện nhập liệu */
        .glass-card { background: rgba(255,255,255,.9); backdrop-filter: blur(16px); border: 1px solid rgba(226,232,240,.8); box-shadow: 0 18px 50px -30px rgba(15,23,42,.28); }
        .field:focus-within { transform: translateY(-1px); }
        
        /* Giao diện khung preview (Dark mode) mô phỏng thiết bị thực tế */
        .preview-pane { background: linear-gradient(180deg, rgba(15,23,42,.96), rgba(15,23,42,.88)); }
        .preview-shell { box-shadow: 0 30px 80px -35px rgba(15,23,42,.55); }
        .chip { box-shadow: 0 10px 20px -14px rgba(37,99,235,.5); }
        
        /* Nhãn in hoa siêu nhỏ mang phong cách công nghệ */
        .tiny-label { letter-spacing: .28em; font-size: 10px; font-weight: 900; text-transform: uppercase; color: #94a3b8; }
        
        /* Nút chọn thiết bị mô phỏng responsive */
        .device-btn.active { background: #0f172a; color: #fff; }
        .preview-frame { transition: width .28s ease, height .28s ease, transform .28s ease; }
        .preview-viewport { background: #f4f6f8; }
        .preview-viewport::-webkit-scrollbar { width: 6px; height: 6px; }
        .preview-viewport::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
        
        /* Cấu hình ẩn bớt sidebar khi ở kích thước mobile */
        .preview-frame[data-device="mobile"] .article-layout {
            display: block;
        }
        .preview-frame[data-device="mobile"] .article-sidebar {
            display: none;
        }
        .preview-frame[data-device="mobile"] .article-main {
            width: 100%;
        }
        #content_editor { min-height: 460px; }
        .tox-tinymce { border-radius: 1.5rem !important; border: 1px solid #e2e8f0 !important; }
        .tox .tox-editor-container { border-radius: 1.5rem !important; }
        
        /* Responsive CSS cho khung preview di động */
        @media (max-width: 768px) {
            .preview-frame { width: 100% !important; max-width: 100% !important; transform: none !important; }
            .preview-viewport { padding: 12px !important; }
            .preview-shell { border-radius: 1.25rem !important; }
            .preview-viewport .preview-frame { min-width: 0 !important; }
            .article-layout { display: block !important; }
            .article-main { width: 100% !important; }
            .article-sidebar { display: none !important; }
            .article-surface .max-w-\[1200px\] { padding-left: 14px !important; padding-right: 14px !important; }
            .article-title { font-size: 22px !important; line-height: 1.25 !important; word-break: break-word; }
            .article-meta { align-items: flex-start !important; }
            .meta-right { flex-shrink: 0; }
            .article-summary-box { border-radius: 0 1rem 1rem 0 !important; }
            .article-content { font-size: 15px !important; line-height: 1.75 !important; }
            .breadcrumb { font-size: 12px !important; gap: 6px !important; }
        }
        
        /* CSS phong cách hiển thị nội dung Rich Text của preview */
        .preview-content h1, .preview-content h2, .preview-content h3 { color: #111827; font-weight: 900; line-height: 1.25; margin: 1.25rem 0 .75rem; }
        .preview-content h1 { font-size: 2rem; }
        .preview-content h2 { font-size: 1.5rem; }
        .preview-content h3 { font-size: 1.2rem; }
        .preview-content p { margin: 0 0 1rem; color: #334155; line-height: 1.85; }
        .preview-content img { width: 100%; height: auto; border-radius: 1rem; margin: 1rem 0; box-shadow: 0 14px 35px -22px rgba(15,23,42,.4); }
        .preview-content blockquote { border-left: 4px solid #d70018; padding: .75rem 1rem; background: #f3f4f6; color: #374151; border-radius: 0 .75rem .75rem 0; margin: 1rem 0; }
        .preview-content ul, .preview-content ol { padding-left: 1.25rem; margin: 0 0 1rem; color: #334155; }
        .preview-content table { width: 100%; border-collapse: collapse; margin: 1rem 0; overflow: hidden; border-radius: 1rem; }
        .preview-content th, .preview-content td { border: 1px solid #e2e8f0; padding: .75rem; text-align: left; }
        .preview-content iframe, .preview-content video { width: 100%; min-height: 320px; border-radius: 1rem; margin: 1rem 0; }
        .preview-content a { color: #d70018; text-decoration: underline; }
        .preview-content hr { border: 0; border-top: 1px solid #e2e8f0; margin: 1.5rem 0; }
        .article-surface { background: #fff; }
        .article-summary-box { background: #f3f4f6; border-left: 4px solid #d70018; }
        .article-content figure { margin: 1rem 0; }
        .article-content figcaption { margin-top: .5rem; font-size: 13px; color: #6b7280; text-align: center; }
        .article-content img { max-width: 100%; }
        .article-content blockquote { border-left: 4px solid #d70018; background: #f3f4f6; padding: .85rem 1rem; border-radius: 0 .75rem .75rem 0; color: #374151; font-style: normal; }
    </style>

    {{-- KHỐI HEADER TRANG QUẢN TRỊ --}}
    <div class="rounded-[2rem] overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white shadow-2xl">
        <div class="p-6 md:p-8 flex flex-col xl:flex-row xl:items-end justify-between gap-6">
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[11px] font-bold tracking-[0.3em] uppercase border border-white/10">Content Composer</div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight">{{ $article->exists ? 'Cập nhật bài viết' : 'Tạo bài viết mới' }}</h1>
                    <p class="mt-3 text-slate-300 max-w-2xl leading-relaxed">Viết nội dung, điều chỉnh ảnh đại diện và quan sát kết quả ngay bên cạnh với preview HTML gần giống trang public.</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white/10 border border-white/10 text-white font-bold hover:bg-white/15 transition">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <button form="articleForm" type="submit" id="saveButton" class="inline-flex items-center gap-3 px-5 py-3 rounded-2xl bg-white text-slate-900 font-black shadow-lg hover:-translate-y-0.5 transition">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    {{ $article->exists ? 'Cập nhật' : 'Đăng bài' }}
                </button>
            </div>
        </div>
    </div>

    {{-- PHẦN THÔNG BÁO LỖI VALIDATION --}}
    @if($errors->any())
        <div class="glass-card rounded-[1.5rem] p-4 border border-rose-100 text-rose-700 bg-rose-50/70">
            <ul class="space-y-1 text-sm font-medium">
                @foreach($errors->all() as $error)
                    <li><i class="fa-solid fa-circle-exclamation mr-2"></i>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM THÊM / CẬP NHẬT BÀI VIẾT --}}
    <form id="articleForm" action="{{ $article->exists ? route('admin.articles.update', $article->article_id) : route('admin.articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($article->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            
            {{-- CỘT TRÁI: KHU VỰC THÔNG TIN BÀI VIẾT VÀ SOẠN THẢO --}}
            <div class="xl:col-span-7 space-y-6">
                <div class="glass-card rounded-[2rem] p-6 md:p-7 space-y-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="tiny-label">01 — Nội dung cốt lõi</div>
                            <h2 class="mt-2 text-xl font-black text-slate-900">Soạn bài viết</h2>
                        </div>
                        <span class="chip inline-flex items-center gap-2 px-3 py-2 rounded-full bg-blue-50 text-blue-700 text-xs font-black uppercase tracking-[0.2em]"><span class="w-2 h-2 rounded-full bg-blue-500"></span>Live sync</span>
                    </div>

                    <div class="grid gap-5">
                        {{-- Tiêu đề --}}
                        <div class="field">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Tiêu đề bài viết <span class="text-rose-500">*</span></label>
                            <input id="titleInput" type="text" name="title" value="{{ old('title', $article->title) }}" class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 bg-slate-50/80 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 transition" placeholder="Nhập tiêu đề hấp dẫn..." required>
                        </div>
                        
                        {{-- Tóm tắt ngắn --}}
                        <div class="field">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mô tả ngắn</label>
                            <textarea id="summaryInput" name="summary" rows="4" class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 bg-slate-50/80 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 transition" placeholder="Tóm tắt ngắn gọn nội dung bài viết...">{{ old('summary', $article->summary) }}</textarea>
                        </div>
                        
                        {{-- Trình soạn thảo TinyMCE --}}
                        <div class="field">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <label class="block text-sm font-bold text-slate-700">Nội dung chi tiết <span class="text-rose-500">*</span></label>
                                <button type="button" class="text-xs font-black uppercase tracking-[0.2em] text-blue-600 bg-blue-50 px-3 py-2 rounded-full hover:bg-blue-100 transition" id="insertSampleBtn">Chèn mẫu</button>
                            </div>
                            <textarea id="content_editor" name="content" rows="16" class="w-full px-4 py-3.5 rounded-[1.5rem] border border-slate-200 bg-slate-50/80 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 transition">{{ old('content', $article->content) }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- CẤU HÌNH LIÊN KẾT HỆ SINH THÁI VÀ ĐỊNH DẠNG --}}
                <div class="glass-card rounded-[2rem] p-6 md:p-7 space-y-5">
                    <div class="tiny-label">02 — Cấu hình xuất bản</div>
                    <h2 class="text-xl font-black text-slate-900">Thiết lập hiển thị</h2>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div class="field">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Định dạng</label>
                            <select id="formatInput" name="format_type" class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 bg-slate-50/80 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 transition">
                                <option value="standard" {{ old('format_type', $article->format_type) === 'standard' ? 'selected' : '' }}>Standard</option>
                                <option value="lookbook" {{ old('format_type', $article->format_type) === 'lookbook' ? 'selected' : '' }}>Lookbook</option>
                                <option value="storytelling" {{ old('format_type', $article->format_type) === 'storytelling' ? 'selected' : '' }}>Storytelling</option>
                            </select>
                        </div>
                        {{-- Cho phép liên kết bài viết tới một mã đơn sửa chữa thiết bị để hiển thị module Nhật ký Hồi sinh Thiết bị --}}
                        <div class="field">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mã đơn sửa chữa liên kết</label>
                            <input id="ticketInput" type="number" name="related_ticket_id" value="{{ old('related_ticket_id', $article->related_ticket_id) }}" class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 bg-slate-50/80 outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 transition" placeholder="VD: 1">
                        </div>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: THUMBNAIL, DUYỆT BÀI UGC & CỘNG ĐIỂM THÀNH VIÊN, HÀNH ĐỘNG --}}
            <div class="xl:col-span-5 space-y-6">
                {{-- Khối quản lý ảnh Thumbnail đại diện --}}
                <div class="glass-card rounded-[2rem] p-6 md:p-7 space-y-5">
                    <div class="tiny-label">03 — Hình ảnh</div>
                    <h2 class="text-xl font-black text-slate-900">Thumbnail & trạng thái</h2>

                    <div class="space-y-4">
                        <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50/80 p-4">
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <div>
                                    <div class="text-sm font-black text-slate-900">Ảnh đại diện</div>
                                    <div class="text-xs text-slate-500 mt-1">Dùng để render ngay lên preview</div>
                                </div>
                                <label for="thumbnailInput" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-900 text-white text-xs font-black uppercase tracking-[0.2em] cursor-pointer hover:bg-blue-700 transition">
                                    <i class="fa-solid fa-upload"></i> Chọn ảnh
                                </label>
                            </div>
                            <input id="thumbnailInput" type="file" name="thumbnail_file" accept="image/*" class="hidden">
                            <div class="overflow-hidden rounded-[1.5rem] bg-slate-100 aspect-[16/10]">
                                <img id="thumbnailPreview" src="{{ $article->thumbnail ? asset($article->thumbnail) : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200' }}" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DUYỆT BÀI VÀ CỘNG ĐIỂM THƯỞNG: Chỉ hiển thị khi sửa một bài viết được gửi từ phía khách hàng (UGC) --}}
                @if($article->exists && $article->author_type === 'customer')
                    <div class="glass-card rounded-[2rem] p-6 md:p-7 border border-amber-100 space-y-4">
                        <div class="tiny-label">04 — UGC & Gamification</div>
                        <h2 class="text-xl font-black text-slate-900">Duyệt bài & điểm thưởng</h2>
                        <div class="text-sm text-slate-600 space-y-1">
                            <p><span class="font-bold">Trạng thái hiện tại:</span> {{ $article->status }}</p>
                            <p><span class="font-bold">Khách hàng viết bài:</span> {{ $article->author->full_name ?? 'Không xác định' }}</p>
                        </div>

                        {{-- Nếu bài viết đang ở trạng thái chờ duyệt --}}
                        @if($article->status === 'pending')
                            <div class="rounded-[1.5rem] bg-amber-50 border border-amber-100 p-4 space-y-3">
                                <p class="text-sm font-medium text-slate-700">Duyệt bài và tiến hành cộng điểm thưởng tích lũy cho thành viên.</p>
                                <div class="flex gap-3">
                                    {{-- Ô nhập số điểm thưởng --}}
                                    <input type="number" form="approve_form" name="points" class="w-28 px-4 py-3 rounded-2xl border border-slate-200 bg-white outline-none focus:ring-4 focus:ring-amber-500/10" placeholder="500">
                                    <button type="submit" form="approve_form" class="flex-1 rounded-2xl bg-amber-500 text-white font-black hover:bg-amber-600 transition px-4 py-3">Duyệt & Cộng điểm</button>
                                </div>
                            </div>
                        {{-- Nếu bài viết đã được duyệt thành công trước đó --}}
                        @elseif($article->status === 'approved')
                            <div class="rounded-[1.5rem] bg-emerald-50 border border-emerald-100 p-4 text-emerald-700 font-semibold">
                                Đã duyệt thành công và đã cộng {{ $article->reward_points_awarded }} điểm vào ví tích điểm của khách hàng.
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Khối nút hành động nhanh lưu trữ/xem trước --}}
                <div class="glass-card rounded-[2rem] p-6 md:p-7 space-y-4 sticky top-6">
                    <div class="tiny-label">05 — Xuất bản</div>
                    <h2 class="text-xl font-black text-slate-900">Hành động nhanh</h2>
                    <button type="submit" class="w-full rounded-2xl bg-blue-600 text-white font-black py-3.5 hover:bg-blue-700 hover:-translate-y-0.5 transition shadow-lg shadow-blue-200/60">
                        <i class="fa-solid fa-paper-plane mr-2"></i>{{ $article->exists ? 'Cập nhật bài viết' : 'Đăng bài viết' }}
                    </button>
                    <a href="{{ route('articles.show', $article->slug ?: '#') }}" target="_blank" class="w-full rounded-2xl bg-slate-900 text-white font-black py-3.5 text-center block hover:bg-slate-800 transition">
                        <i class="fa-solid fa-eye mr-2"></i>Xem trang Lifestyle
                    </a>
                    <p class="text-xs text-slate-500 leading-relaxed">Nội dung sẽ được render tự động trên khung preview bên cạnh theo thời gian thực.</p>
                </div>
            </div>
        </div>
    </form>

    {{-- Form phụ dùng để submit request duyệt bài viết UGC --}}
    @if($article->exists && $article->author_type === 'customer' && $article->status === 'pending')
        <form id="approve_form" action="{{ route('admin.articles.approve', $article->article_id) }}" method="POST" class="hidden">@csrf</form>
    @endif

    {{-- PHẦN REAL-TIME LIVE PREVIEW MÔ PHỎNG HIỂN THỊ TRÊN CÁC DÒNG THIẾT BỊ --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-7">
            <div class="glass-card rounded-[2rem] p-5 md:p-6">
                <div class="flex items-center justify-between mb-4 gap-3">
                    <div>
                        <div class="tiny-label">Real-time Visualization</div>
                        <h3 class="mt-2 text-xl font-black text-slate-900">Bài đăng sẽ hiển thị như sau</h3>
                    </div>
                    {{-- Bộ nút chuyển chế độ xem thiết bị --}}
                    <div class="flex items-center gap-2 rounded-full bg-slate-100 p-1 border border-slate-200">
                        <button type="button" class="device-btn active px-4 py-2 rounded-full text-xs font-black uppercase tracking-[0.2em]" data-device="desktop">Desktop</button>
                        <button type="button" class="device-btn px-4 py-2 rounded-full text-xs font-black uppercase tracking-[0.2em]" data-device="tablet">Tablet</button>
                        <button type="button" class="device-btn px-4 py-2 rounded-full text-xs font-black uppercase tracking-[0.2em]" data-device="mobile">Mobile</button>
                    </div>
                </div>
                
                {{-- Giả lập thiết bị --}}
                <div class="preview-shell preview-pane rounded-[2rem] overflow-hidden border border-slate-700">
                    <div class="p-4 border-b border-white/10 flex items-center justify-between text-white/70 text-xs font-bold uppercase tracking-[0.25em]">
                        <span>Lifestyle preview</span>
                        <div class="flex items-center gap-2">
                            <span id="previewBadge">standard</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                    </div>
                    
                    {{-- Bề mặt giả lập trang public --}}
                    <div class="preview-viewport p-4 md:p-6 text-white overflow-auto">
                        <div id="previewFrame" class="preview-frame mx-auto rounded-[1.75rem] overflow-hidden bg-white shadow-2xl">
                            <div class="bg-[#d70018] text-white px-5 py-3 flex items-center justify-between text-[10px] font-black uppercase tracking-[0.28em]">
                                <span id="previewDeviceLabel">Desktop preview</span>
                                <span class="flex items-center gap-2 text-white/80"><span class="w-2 h-2 rounded-full bg-white"></span> Live</span>
                            </div>
                            
                            <div class="article-surface bg-white">
                                <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-4 lg:py-5">
                                    <div class="breadcrumb text-[13px] text-slate-500 mb-5 flex items-center gap-2 flex-wrap">
                                        <a href="#" class="text-[#d70018] font-semibold">Trang chủ</a>
                                        <i class="fa-solid fa-angle-right text-[10px]"></i>
                                        <a href="#" class="text-[#d70018] font-semibold">Lifestyle</a>
                                        <i class="fa-solid fa-angle-right text-[10px]"></i>
                                        <span class="truncate max-w-xs text-slate-400" id="previewBreadcrumb">{{ $article->title ?: 'Bài viết' }}</span>
                                    </div>
 
                                    <div class="article-layout grid xl:grid-cols-[minmax(0,800px)_320px] gap-8">
                                        <div class="article-main min-w-0">
                                            {{-- Live tiêu đề --}}
                                            <h1 class="article-title text-[28px] md:text-[36px] font-black leading-tight text-slate-900 mb-4" id="previewTitle">{{ $article->title ?: 'Tiêu đề bài viết của bạn sẽ xuất hiện ở đây' }}</h1>
 
                                            <div class="article-meta flex items-center justify-between border-b border-slate-200 pb-4 mb-5 gap-4 flex-wrap">
                                                <div class="meta-left flex items-center gap-3 min-w-0">
                                                    <div class="author-avatar w-10 h-10 rounded-full bg-[#d70018] text-white flex items-center justify-center font-black">A</div>
                                                    <div class="meta-info-text min-w-0">
                                                        <div class="author-name text-sm font-bold text-slate-700" id="previewAuthor">Ban biên tập Sforum</div>
                                                        <div class="post-date text-xs text-slate-500" id="previewDate">{{ now()->format('d/m/Y - H:i') }}</div>
                                                    </div>
                                                </div>
                                                <div class="meta-right flex gap-2">
                                                    <span class="share-btn share-fb w-9 h-9 rounded-full bg-[#1877f2] text-white flex items-center justify-center"><i class="fa-brands fa-facebook-f"></i></span>
                                                    <span class="share-btn share-link w-9 h-9 rounded-full bg-slate-500 text-white flex items-center justify-center"><i class="fa-solid fa-link"></i></span>
                                                </div>
                                            </div>
 
                                            {{-- Live tóm tắt ngắn --}}
                                            <div class="article-summary-box rounded-r-2xl p-4 md:p-5 mb-6 text-slate-700 font-semibold leading-relaxed text-[15px]" id="previewSummary">{{ $article->summary ?: 'Mô tả ngắn giúp người xem hiểu bài viết nhanh hơn.' }}</div>
 
                                            {{-- Live nội dung HTML chi tiết --}}
                                            <div class="article-content text-[16px] leading-[1.8] text-[#333] overflow-hidden" id="previewContent">{!! $article->content ? $article->content : '<p>Phần nội dung chi tiết sẽ được render ở đây sau khi bạn nhập nội dung.</p>' !!}</div>
 
                                            {{-- Live liên kết sửa chữa --}}
                                            <div id="previewEcosystem" class="ecosystem-banner mt-8 rounded-[1.5rem] p-5 md:p-6 bg-gradient-to-r from-[#1e3a8a] to-[#3b82f6] text-white overflow-hidden relative">
                                                <div class="ecosystem-title text-lg font-black mb-2 flex items-center gap-2">
                                                    <i class="fa-solid fa-screwdriver-wrench"></i>
                                                    <span>Nhật ký Hồi sinh Thiết bị</span>
                                                </div>
                                                <p class="text-sm leading-relaxed text-white/90" id="previewEcosystemText">Bài viết sẽ hiển thị theo phong cách public hoàn chỉnh, bao gồm khối ecosystem nếu có liên kết ticket.</p>
                                            </div>
 
                                            <div class="mt-6 flex items-center gap-2 flex-wrap text-xs text-slate-600">
                                                <i class="fa-solid fa-tags text-slate-400"></i>
                                                <span class="px-3 py-1 bg-slate-100 rounded-full font-bold uppercase" id="previewFormatChip">{{ $article->format_type ?: 'standard' }}</span>
                                                <span class="px-3 py-1 bg-slate-100 rounded-full font-bold uppercase">Công nghệ</span>
                                            </div>
                                        </div>
 
                                        <aside class="article-sidebar space-y-5 hidden xl:block">
                                            <div class="sidebar-widget bg-white rounded-[1.5rem] border border-slate-200 p-5 shadow-sm">
                                                <div class="widget-title text-sm font-black uppercase tracking-[0.2em] flex items-center gap-2 mb-4">
                                                    <span class="w-1 h-4 bg-[#d70018] rounded-full"></span> Tin mới nhất
                                                </div>
                                                <div id="previewSidebarList" class="space-y-4"></div>
                                            </div>
                                            <div class="rounded-[1.5rem] overflow-hidden border border-slate-200 bg-slate-100">
                                                <img src="{{ $recentArticles->first()->thumbnail ?? 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600' }}" alt="Ad" class="w-full h-56 object-cover">
                                            </div>
                                        </aside>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Khối hướng dẫn soạn thảo bài viết --}}
        <div class="xl:col-span-5">
            <div class="glass-card rounded-[2rem] p-5 md:p-6 space-y-4">
                <div class="tiny-label">Hướng dẫn</div>
                <h3 class="text-lg font-black text-slate-900">Tối ưu trải nghiệm viết bài</h3>
                <ul class="space-y-3 text-sm text-slate-600 leading-relaxed">
                    <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-black">1</span> Tiêu đề, mô tả và nội dung sẽ cập nhật ngay sang preview khi gõ.</li>
                    <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-black">2</span> Ảnh thumbnail được thay đổi tức thì bằng <span class="font-bold">FileReader</span>.</li>
                    <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-black">3</span> Preview hiển thị HTML gần giống bài đăng thật, bao gồm ảnh chèn trong nội dung.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ĐOẠN MÃ JAVASCRIPT HỖ TRỢ TINYMCE VÀ ĐỒNG BỘ HOÁ LIVE PREVIEW --}}
<script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    // Khởi tạo TinyMCE
    tinymce.init({
        selector: '#content_editor',
        height: 460,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace wordcount visualblocks code fullscreen insertdatetime media table emoticons',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image media | forecolor backcolor | code',
        menubar: false,
        content_style: 'body { font-family: Inter, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.75; } img { max-width: 100%; height: auto; }',
        file_picker_types: 'image',
        // Tích hợp upload ảnh chuyển đổi sang Base64
        file_picker_callback: function (cb, value, meta) {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = function () {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function () {
                    const id = 'blobid' + (new Date()).getTime();
                    const blobCache = tinymce.activeEditor.editorUpload.blobCache;
                    const base64 = reader.result.split(',')[1];
                    const blobInfo = blobCache.create(id, file, base64);
                    blobCache.add(blobInfo);
                    cb(blobInfo.blobUri(), { title: file.name });
                    syncPreview();
                };
                reader.readAsDataURL(file);
            };
            input.click();
        },
        setup: function (editor) {
            editor.on('change keyup setcontent nodechange', function () {
                syncPreview();
            });
        }
    });

    // Các biến DOM
    const titleInput = document.getElementById('titleInput');
    const summaryInput = document.getElementById('summaryInput');
    const formatInput = document.getElementById('formatInput');
    const ticketInput = document.getElementById('ticketInput');
    const thumbnailInput = document.getElementById('thumbnailInput');
    const previewTitle = document.getElementById('previewTitle');
    const previewSummary = document.getElementById('previewSummary');
    const previewFormatChip = document.getElementById('previewFormatChip');
    const previewBadge = document.getElementById('previewBadge');
    const previewHero = document.getElementById('thumbnailPreview');
    const previewDate = document.getElementById('previewDate');
    const previewContent = document.getElementById('previewContent');
    const previewFrame = document.getElementById('previewFrame');
    const previewDeviceLabel = document.getElementById('previewDeviceLabel');
    const deviceButtons = document.querySelectorAll('.device-btn');
    const insertSampleBtn = document.getElementById('insertSampleBtn');
    const sidebarArticles = {!! $sidebarArticlesJson ?? '[]' !!};

    let currentDevice = 'desktop';

    const PUBLIC_TEMPLATE = {
        fallbackThumbnail: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200',
        fallbackSidebarImage: 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=400',
        fallbackAdImage: 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600',
        authorName: 'Ban biên tập Sforum',
        authorInitial: 'A',
        category: 'Công nghệ',
    };

    // Chuẩn hóa phong cách ảnh trong nội dung bài viết
    function sanitizeAndDecorateContent(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(`<div>${html || ''}</div>`, 'text/html');
        const wrapper = doc.body.firstElementChild;
        if (!wrapper) return '<p>Phần nội dung chi tiết sẽ được render ở đây sau khi bạn nhập nội dung.</p>';

        wrapper.querySelectorAll('img').forEach((img) => {
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.borderRadius = '16px';
            img.style.display = 'block';
        });

        return wrapper.innerHTML.trim() || '<p>Phần nội dung chi tiết sẽ được render ở đây sau khi bạn nhập nội dung.</p>';
    }

    // Đổi giao diện view thiết bị
    function applyDeviceView(device) {
        currentDevice = device;
        deviceButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.device === device));

        const config = {
            desktop: { width: '100%', transform: 'none', label: 'Desktop preview' },
            tablet: { width: '768px', transform: 'none', label: 'Tablet preview' },
            mobile: { width: '390px', transform: 'none', label: 'Mobile preview' },
        }[device];

        previewFrame.style.width = config.width;
        previewFrame.style.maxWidth = '100%';
        previewFrame.style.transform = config.transform;
        previewFrame.style.transformOrigin = 'top center';
        previewFrame.dataset.device = device;
        previewDeviceLabel.textContent = config.label;
    }

    // Render danh sách tin sidebar giả lập
    function renderSidebarArticles() {
        const container = document.getElementById('previewSidebarList');
        if (!container) return;

        const items = (sidebarArticles && sidebarArticles.length ? sidebarArticles : [
            { title: 'Bài viết đang được dựng preview theo layout public thực tế.', slug: '#', thumbnail: PUBLIC_TEMPLATE.fallbackSidebarImage },
            { title: 'Khối sidebar được giữ đồng bộ với trang chi tiết lifestyle.', slug: '#', thumbnail: 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=400' },
            { title: 'Nội dung thật từ database sẽ tự động thay thế placeholder.', slug: '#', thumbnail: 'https://images.unsplash.com/photo-1522199755839-a2bacb67c546?w=400' },
            { title: 'Preview sẽ đồng bộ với bài viết gần nhất của hệ thống.', slug: '#', thumbnail: 'https://images.unsplash.com/photo-1484417894907-623942c8ee29?w=400' },
            { title: 'Sidebar hiển thị tối đa 5 bài viết gần nhất.', slug: '#', thumbnail: 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=400' },
        ]).slice(0, 5);

        container.innerHTML = items.map((item) => `
            <a href="${item.slug || '#'}" target="_blank" class="sidebar-post flex gap-3">
                <div class="w-24 h-16 rounded-xl bg-slate-100 overflow-hidden flex-shrink-0">
                    <img src="${item.thumbnail}" class="w-full h-full object-cover" alt="${item.title}">
                </div>
                <h4 class="text-sm font-semibold text-slate-700 leading-snug line-clamp-3">${item.title}</h4>
            </a>
        `).join('');
    }

    // Đồng bộ form dữ liệu sang live preview HTML
    function syncPreview() {
        const formatMap = { standard: 'Standard', lookbook: 'Lookbook', storytelling: 'Storytelling' };
        const title = titleInput?.value?.trim() || 'Tiêu đề bài viết của bạn sẽ xuất hiện ở đây';
        const summary = summaryInput?.value?.trim() || 'Mô tả ngắn giúp người xem hiểu bài viết nhanh hơn.';
        const format = formatInput?.value || 'standard';
        const ticket = ticketInput?.value || '—';
        const editor = tinymce.get('content_editor');
        const html = editor ? editor.getContent() : '';
        const contentHtml = sanitizeAndDecorateContent(html);
        const hasTicket = ticket && ticket !== '—';
        const heroSrc = previewHero?.src || PUBLIC_TEMPLATE.fallbackThumbnail;

        previewTitle.textContent = title;
        previewSummary.textContent = summary;
        previewFormatChip.textContent = format;
        previewBadge.textContent = format;
        previewDate.textContent = new Date().toLocaleDateString('vi-VN') + ' - ' + new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('previewBreadcrumb').textContent = title;
        document.getElementById('previewAuthor').textContent = PUBLIC_TEMPLATE.authorName;
        document.querySelector('.author-avatar').textContent = PUBLIC_TEMPLATE.authorInitial;
        previewHero.src = heroSrc;
        previewContent.innerHTML = contentHtml;
        document.getElementById('previewEcosystem').style.display = hasTicket ? 'block' : 'none';
        document.getElementById('previewEcosystemText').innerHTML = hasTicket
            ? `Bài viết này nằm trong chuỗi series "Right to Repair" thực tế từ hệ thống DIENMAY PRO. Chuyên gia của chúng tôi đã trực tiếp xử lý trường hợp mã <strong>#${ticket}</strong>.`
            : 'Bài viết sẽ hiển thị theo phong cách public hoàn chỉnh, bao gồm khối ecosystem nếu có liên kết ticket.';
        renderSidebarArticles();
    }

    // Preview thumbnail tức thì qua FileReader
    function handleThumbnailPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => { previewHero.src = e.target.result; };
        reader.readAsDataURL(file);
    }

    // Lắng nghe sự kiện
    thumbnailInput?.addEventListener('change', (event) => handleThumbnailPreview(event.target.files?.[0]));
    titleInput?.addEventListener('input', syncPreview);
    summaryInput?.addEventListener('input', syncPreview);
    formatInput?.addEventListener('change', syncPreview);
    ticketInput?.addEventListener('input', syncPreview);

    deviceButtons.forEach(btn => btn.addEventListener('click', () => applyDeviceView(btn.dataset.device)));

    // Nút chèn nhanh bài mẫu
    insertSampleBtn?.addEventListener('click', () => {
        const editor = tinymce.get('content_editor');
        if (editor) {
            editor.setContent(`
                <h2>Tại sao bài viết này nổi bật?</h2>
                <p>Hãy mô tả điểm chính, kèm ảnh, CTA và điểm nhấn để người đọc dễ nắm bắt.</p>
                <p><strong>Mẹo:</strong> Chia nội dung thành từng khối nhỏ, có tiêu đề phụ để tăng khả năng đọc trên mobile.</p>
                <figure>
                    <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200" alt="Sample image">
                    <figcaption>Hình minh họa được hiển thị ngay trong preview.</figcaption>
                </figure>
                <blockquote>Preview sẽ render HTML gần giống bài đăng thật, gồm cả heading, ảnh và blockquote.</blockquote>
            `);
            syncPreview();
        }
    });

    // Khởi tạo khi load trang
    document.addEventListener('DOMContentLoaded', () => {
        applyDeviceView('desktop');
        renderSidebarArticles();
        syncPreview();
    });
</script>
@endsection