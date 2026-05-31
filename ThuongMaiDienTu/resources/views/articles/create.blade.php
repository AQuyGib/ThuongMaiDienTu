@extends('layouts.app')

@section('title', isset($article) && $article->exists ? 'Chỉnh sửa bài viết - DIENMAYPRO Lifestyle' : 'Đóng góp bài viết - DIENMAYPRO Lifestyle')

@push('styles')
<style>
    /* Thẻ card mờ ảo (glassmorphism) cho form soạn thảo */
    .glass-card { 
        background: rgba(255, 255, 255, 0.95); 
        backdrop-filter: blur(20px); 
        border: 1px solid rgba(226, 232, 240, 0.8); 
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.05); 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .glass-card:hover {
        box-shadow: 0 30px 50px -20px rgba(0, 0, 0, 0.08);
    }
    
    /* Đổi màu nhãn input khi người dùng click vào khu vực nhập liệu */
    .field-group:focus-within label {
        color: #d70018;
    }
    
    /* Vùng chứa tối màu (Dark mode) mô phỏng thiết bị thực tế */
    .preview-pane { 
        background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); 
    }
    .preview-shell { 
        box-shadow: 0 25px 60px -30px rgba(15, 23, 42, 0.3); 
    }
    
    /* Các nhãn chữ in hoa cực nhỏ mang phong cách công nghệ */
    .tiny-label { 
        letter-spacing: 0.25em; 
        font-size: 10px; 
        font-weight: 800; 
        text-transform: uppercase; 
        color: #94a3b8; 
    }
    
    /* Các nút chọn thiết bị Responsive (Desktop/Tablet/Mobile) */
    .device-btn {
        transition: all 0.2s ease;
    }
    .device-btn.active { 
        background: #d70018; 
        color: #fff; 
        box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
    }
    
    /* Cấu hình hiệu ứng co giãn mượt mà của khung preview responsive */
    .preview-frame { 
        transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s ease; 
    }
    .preview-viewport { 
        background: #f8fafc; 
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .preview-viewport::-webkit-scrollbar { 
        width: 6px; 
        height: 6px; 
    }
    .preview-viewport::-webkit-scrollbar-thumb { 
        background: #cbd5e1; 
        border-radius: 99px; 
    }
    
    /* Chiều cao tối thiểu của editor TinyMCE */
    #content_editor { 
        min-height: 450px; 
    }
    .tox-tinymce { 
        border-radius: 1.25rem !important; 
        border: 1px solid #e2e8f0 !important; 
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02) !important;
    }
    
    /* Định dạng HTML hiển thị bên trong khung Live Preview */
    .preview-content h1, .preview-content h2, .preview-content h3 { 
        color: #1e293b; 
        font-weight: 800; 
        line-height: 1.3; 
        margin: 1.5rem 0 0.75rem; 
    }
    .preview-content h1 { font-size: 1.8rem; }
    .preview-content h2 { font-size: 1.4rem; }
    .preview-content h3 { font-size: 1.2rem; }
    .preview-content p { 
        margin: 0 0 1.2rem; 
        color: #475569; 
        line-height: 1.8; 
    }
    .preview-content img { 
        width: 100%; 
        height: auto; 
        border-radius: 1rem; 
        margin: 1.2rem 0; 
        box-shadow: 0 10px 30px -15px rgba(0, 0, 0, 0.15); 
    }
    .preview-content blockquote { 
        border-left: 4px solid #d70018; 
        padding: 0.8rem 1.2rem; 
        background: #f8fafc; 
        color: #334155; 
        border-radius: 0 0.75rem 0.75rem 0; 
        margin: 1.2rem 0; 
        font-style: italic;
    }
    .preview-content ul, .preview-content ol { 
        padding-left: 1.5rem; 
        margin: 0 0 1.2rem; 
        color: #475569; 
    }
    .preview-content li {
        margin-bottom: 0.4rem;
    }
    .preview-content a { 
        color: #d70018; 
        text-decoration: underline; 
        font-weight: 600;
    }
    .article-surface { 
        background: #fff; 
    }
    .article-summary-box { 
        background: #f8fafc; 
        border-left: 4px solid #d70018; 
    }
    .article-title {
        font-family: 'Inter', sans-serif;
    }
    
    /* Xử lý Responsive ẩn Sidebar và phóng rộng khung bài viết khi màn hình nhỏ */
    @media (max-width: 768px) {
        .preview-frame { 
            width: 100% !important; 
            max-width: 100% !important; 
        }
        .preview-viewport { 
            padding: 10px !important; 
        }
        .article-layout { 
            display: block !important; 
        }
        .article-main { 
            width: 100% !important; 
        }
        .article-sidebar { 
            display: none !important; 
        }
    }
</style>
@endpush

@section('content')
<div class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    
    {{-- ĐẦU TRANG DASHBOARD - THÔNG TIN TỔNG QUAN VÀ THAO TÁC NHANH --}}
    <div class="rounded-3xl overflow-hidden bg-gradient-to-br from-slate-950 via-red-950 to-rose-950 text-white shadow-2xl relative">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_120%,rgba(215,0,24,0.15),transparent_50%)]"></div>
        <div class="p-8 md:p-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[10px] font-black tracking-[0.25em] uppercase border border-white/10 text-rose-300">
                    <i class="fa-solid fa-feather-pointed"></i> Community Writer
                </div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        {{ isset($article) && $article->exists ? 'Cập nhật câu chuyện của bạn' : 'Chia sẻ câu chuyện của bạn' }}
                    </h1>
                    <p class="mt-3 text-slate-300 max-w-2xl leading-relaxed text-sm md:text-base">
                        Hãy viết bài đánh giá, chia sẻ kinh nghiệm, thủ thuật công nghệ hoặc nhật ký sửa chữa thiết bị. Bài viết được duyệt sẽ mang lại điểm thưởng thành viên vô cùng hấp dẫn!
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white/10 border border-white/10 text-white font-bold hover:bg-white/15 transition text-sm">
                    <i class="fa-solid fa-arrow-left"></i> Lifestyle Hub
                </a>
                {{-- Nút Submit liên kết trực tiếp với Form nhập liệu qua thuộc tính form="articleForm" --}}
                <button form="articleForm" type="submit" id="saveButton" class="inline-flex items-center gap-3 px-6 py-3 rounded-2xl bg-white text-slate-900 font-black shadow-lg hover:-translate-y-0.5 transition text-sm">
                    <i class="fa-solid fa-paper-plane text-rose-600"></i>
                    {{ isset($article) && $article->exists ? 'Cập nhật bài viết' : 'Gửi bài kiểm duyệt' }}
                </button>
            </div>
        </div>
    </div>

    {{-- HIỂN THỊ CÁC LỖI VALIDATE CỦA BACKEND (NẾU CÓ) --}}
    @if($errors->any())
        <div class="glass-card rounded-2xl p-5 border border-rose-100 text-rose-700 bg-rose-50/70 animate-shake">
            <h3 class="font-bold mb-2 flex items-center gap-2"><i class="fa-solid fa-triangle-exclamation"></i> Có lỗi xảy ra, vui lòng kiểm tra lại:</h3>
            <ul class="space-y-1 text-sm font-medium pl-6 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM SOẠN THẢO BÀI VIẾT --}}
    <form id="articleForm" action="{{ isset($article) && $article->exists ? route('articles.update', $article->article_id) : route('articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($article) && $article->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">
            
            {{-- CỘT TRÁI: SOẠN THẢO CÁC TRƯỜNG THÔNG TIN CHÍNH --}}
            <div class="xl:col-span-8 space-y-8">
                <div class="glass-card rounded-[2rem] p-6 md:p-8 space-y-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="tiny-label">Nội dung cốt lõi</div>
                            <h2 class="mt-2 text-xl font-extrabold text-slate-900">Soạn thảo nội dung</h2>
                        </div>
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-black uppercase tracking-[0.15em] border border-rose-100">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>Live sync
                        </span>
                    </div>

                    <div class="grid gap-6">
                        {{-- Tiêu đề bài viết --}}
                        <div class="field-group">
                            <label class="block text-sm font-bold text-slate-700 mb-2 transition">Tiêu đề bài viết <span class="text-rose-500">*</span></label>
                            <input id="titleInput" type="text" name="title" value="{{ old('title', $article->title ?? '') }}" class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 outline-none focus:border-rose-400 focus:bg-white focus:ring-4 focus:ring-rose-500/10 transition text-slate-800 font-semibold" placeholder="Nhập tiêu đề hấp dẫn và thu hút..." required>
                        </div>
                        
                        {{-- Tóm tắt ngắn --}}
                        <div class="field-group">
                            <label class="block text-sm font-bold text-slate-700 mb-2 transition">Tóm tắt ngắn (Mô tả)</label>
                            <textarea id="summaryInput" name="summary" rows="3" class="w-full px-4 py-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 outline-none focus:border-rose-400 focus:bg-white focus:ring-4 focus:ring-rose-500/10 transition text-slate-600 text-sm leading-relaxed" placeholder="Tóm tắt ngắn gọn nội dung bài viết của bạn trong 2-3 câu...">{{ old('summary', $article->summary ?? '') }}</textarea>
                        </div>
                        
                        {{-- Trình soạn thảo chi tiết (TinyMCE) --}}
                        <div class="field-group">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <label class="block text-sm font-bold text-slate-700 transition">Nội dung chi tiết <span class="text-rose-500">*</span></label>
                                {{-- Nút chèn nhanh một bài viết mẫu có sẵn cấu trúc để người dùng tham khảo --}}
                                <button type="button" class="text-xs font-black uppercase tracking-[0.15em] text-rose-600 bg-rose-50 border border-rose-100 px-3.5 py-1.5 rounded-full hover:bg-rose-100 transition" id="insertSampleBtn">
                                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Chèn mẫu nhanh
                                </button>
                            </div>
                            <textarea id="content_editor" name="content" class="w-full">{{ old('content', $article->content ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CỘT PHẢI: THIẾT LẬP THUMBNAIL ẢNH ĐẠI DIỆN VÀ HÀNH ĐỘNG --}}
            <div class="xl:col-span-4 space-y-8">
                {{-- Khu vực tải ảnh Thumbnail bài viết --}}
                <div class="glass-card rounded-[2rem] p-6 md:p-7 space-y-5">
                    <div class="tiny-label">Hình ảnh hiển thị</div>
                    <h2 class="text-lg font-extrabold text-slate-900">Ảnh đại diện</h2>

                    <div class="space-y-4">
                        <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-4 transition hover:border-rose-300">
                            <div class="flex items-center justify-between gap-4 mb-4">
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-700 truncate">Chọn tập tin ảnh</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">JPEG, PNG, WEBP (Tối đa 2MB)</div>
                                </div>
                                <label for="thumbnailInput" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-slate-900 hover:bg-rose-600 text-white text-[10px] font-black uppercase tracking-[0.15em] cursor-pointer transition flex-shrink-0">
                                    <i class="fa-solid fa-cloud-arrow-up"></i> Tải ảnh
                                </label>
                            </div>
                            {{-- Input file ẩn để tùy biến giao diện tải ảnh --}}
                            <input id="thumbnailInput" type="file" name="thumbnail_file" accept="image/*" class="hidden">
                            {{-- Preview hình ảnh đại diện đã tải lên hoặc ảnh cũ trong DB --}}
                            <div class="overflow-hidden rounded-xl bg-slate-100 aspect-[16/10] relative group">
                                <img id="thumbnailPreview" src="{{ (isset($article) && $article->thumbnail) ? asset($article->thumbnail) : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200' }}" alt="Preview" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Khối Trợ lý AI & SEO --}}
                <div class="glass-card rounded-[2rem] p-6 md:p-7 space-y-5">
                    <div class="flex items-center justify-between">
                        <div class="tiny-label">AI Optimization</div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-red-50 text-red-600 text-[9px] font-black uppercase tracking-wider border border-red-100 animate-pulse">
                            <i class="fa-solid fa-sparkles"></i> Active
                        </span>
                    </div>
                    <h2 class="text-lg font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-wand-magic-sparkles text-red-600"></i> Trợ lý AI & SEO
                    </h2>
                    
                    <button type="button" id="btnAiAnalyze" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-900 text-white font-extrabold py-3.5 hover:bg-slate-800 hover:-translate-y-0.5 transition shadow-lg text-sm">
                        <i class="fa-solid fa-microchip"></i>
                        <span>Phân tích & Tối ưu SEO</span>
                    </button>

                    {{-- Khung hiển thị kết quả phân tích AI --}}
                    <div id="aiAnalysisResult" class="hidden space-y-4 pt-3 border-t border-slate-100 text-slate-700 text-xs">
                        {{-- Thước đo điểm chất lượng, điểm SEO & điểm thưởng đề xuất --}}
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 text-center">
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-1">Chất lượng</div>
                                <div class="text-base font-black text-indigo-600" id="aiQualityScore">--</div>
                                <div class="text-[8px] text-slate-400 mt-1">Đánh giá chung</div>
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 text-center">
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-1">Điểm SEO</div>
                                <div class="text-base font-black text-emerald-600" id="aiSeoScore">--</div>
                                <div class="text-[8px] text-slate-400 mt-1">Độ chuẩn SEO</div>
                            </div>
                            <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-100 text-center">
                                <div class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mb-1">Điểm thưởng</div>
                                <div class="text-base font-black text-amber-600" id="aiRewardPoints">--</div>
                                <div class="text-[8px] text-slate-400 mt-1">AI đề xuất</div>
                            </div>
                        </div>

                        {{-- Kết quả kiểm duyệt --}}
                        <div class="p-3 rounded-2xl border" id="aiVerdictBox">
                            <div class="flex items-center gap-2 font-bold mb-1">
                                <i id="aiVerdictIcon" class="fa-solid"></i>
                                <span id="aiVerdictLabel">--</span>
                            </div>
                            <p id="aiVerdictReason" class="text-[11px] text-slate-500 leading-relaxed"></p>
                        </div>

                        {{-- Thẻ Hashtag gợi ý --}}
                        <div class="space-y-1.5">
                            <div class="font-bold text-slate-800">Hashtag AI gợi ý:</div>
                            <div class="flex flex-wrap gap-1.5" id="aiTagsContainer"></div>
                        </div>

                        {{-- Tiêu đề đề xuất --}}
                        <div class="space-y-1 bg-rose-50/30 p-3 rounded-2xl border border-rose-100/50">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">Tiêu đề chuẩn SEO:</span>
                                <button type="button" id="btnApplyAiTitle" class="text-[10px] text-rose-600 font-black uppercase hover:underline">Áp dụng</button>
                            </div>
                            <div id="aiSuggestedTitle" class="text-[11px] text-slate-600 italic leading-snug">--</div>
                        </div>

                        {{-- Mô tả meta đề xuất --}}
                        <div class="space-y-1 bg-blue-50/30 p-3 rounded-2xl border border-blue-100/50">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800">Mô tả Meta SEO:</span>
                                <button type="button" id="btnApplyAiSummary" class="text-[10px] text-blue-600 font-black uppercase hover:underline">Áp dụng</button>
                            </div>
                            <div id="aiSuggestedSummary" class="text-[11px] text-slate-600 italic leading-relaxed">--</div>
                        </div>

                        {{-- Phân tích từ khóa --}}
                        <div class="space-y-1.5">
                            <div class="font-bold text-slate-800">Mật độ từ khóa chính:</div>
                            <div class="space-y-1" id="aiKeywordsContainer"></div>
                        </div>

                        {{-- Lời khuyên tối ưu --}}
                        <div class="space-y-1.5">
                            <div class="font-bold text-slate-800">Lời khuyên chuẩn SEO:</div>
                            <ul class="list-disc pl-4 space-y-1 text-slate-600 text-[11px]" id="aiTipsContainer"></ul>
                        </div>
                    </div>
                </div>

                {{-- Khối nút hành động gửi bài viết --}}
                <div class="glass-card rounded-[2rem] p-6 md:p-7 space-y-4 sticky top-24">
                    <div class="tiny-label">Xuất bản bài viết</div>
                    <h2 class="text-lg font-extrabold text-slate-900">Thao tác nhanh</h2>
                    
                    <button type="submit" class="w-full rounded-2xl bg-[#d70018] text-white font-extrabold py-3.5 hover:bg-[#b00014] hover:-translate-y-0.5 transition shadow-lg shadow-rose-100">
                        <i class="fa-solid fa-paper-plane mr-2"></i>{{ isset($article) && $article->exists ? 'Cập nhật ngay' : 'Gửi bài duyệt' }}
                    </button>
                    
                    {{-- Nếu bài viết đang sửa đã ở trạng thái đã được duyệt, cung cấp link xem trực tiếp bài đăng --}}
                    @if(isset($article) && $article->exists && $article->status === 'approved')
                        <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="w-full rounded-2xl bg-slate-900 text-white font-extrabold py-3.5 text-center block hover:bg-slate-800 transition text-sm">
                            <i class="fa-solid fa-eye mr-2"></i> Xem bài viết thực tế
                        </a>
                    @endif
                    
                    <p class="text-[11px] text-slate-400 leading-relaxed text-center">
                        Nội dung bài viết sẽ được tự động hiển thị mô phỏng trên khu vực Preview ở phía dưới.
                    </p>
                </div>
            </div>
        </div>
    </form>

    {{-- KHU VỰC LIVE PREVIEW TRỰC QUAN TRÊN NHIỀU THIẾT BỊ --}}
    <div class="space-y-6 mt-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="tiny-label">Real-time Visualization</div>
                <h2 class="text-2xl font-extrabold text-slate-900">Mô phỏng hiển thị bài viết</h2>
                <p class="text-xs text-slate-500 mt-1">Quan sát giao diện hiển thị thực tế trên các dòng thiết bị khác nhau.</p>
            </div>
            {{-- Bộ chọn chuyển đổi qua lại kích thước khung preview --}}
            <div class="flex items-center gap-1 rounded-full bg-slate-100 p-1 border border-slate-200 w-fit">
                <button type="button" class="device-btn active px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.15em]" data-device="desktop">Desktop</button>
                <button type="button" class="device-btn px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.15em]" data-device="tablet">Tablet</button>
                <button type="button" class="device-btn px-4 py-2 rounded-full text-[10px] font-black uppercase tracking-[0.15em]" data-device="mobile">Mobile</button>
            </div>
        </div>

        <div class="preview-shell preview-pane rounded-3xl overflow-hidden border border-slate-800">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between text-white/60 text-xs font-black uppercase tracking-[0.2em]">
                <span>Mô phỏng giao diện bài viết</span>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 bg-rose-500/20 text-rose-400 rounded-md text-[9px] border border-rose-500/30">STANDARD</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
            </div>
            
            {{-- Khung cuộn chứa bài viết --}}
            <div class="preview-viewport p-6 overflow-auto max-h-[700px]">
                <div id="previewFrame" class="preview-frame mx-auto rounded-3xl overflow-hidden bg-white shadow-2xl">
                    <div class="bg-[#d70018] text-white px-5 py-3.5 flex items-center justify-between text-[10px] font-black uppercase tracking-[0.2em] select-none">
                        <span id="previewDeviceLabel">Desktop View</span>
                        <span class="flex items-center gap-1.5 text-white/80"><span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span> Live Preview</span>
                    </div>
                    
                    {{-- Bề mặt giả lập trang tin tức Lifestyle public --}}
                    <div class="article-surface bg-white text-slate-800">
                        <div class="max-w-[1200px] mx-auto px-6 py-6 md:py-8">
                            
                            {{-- Breadcrumbs giả lập --}}
                            <div class="breadcrumb text-xs text-slate-500 mb-6 flex items-center gap-2 flex-wrap font-semibold">
                                <a href="#" class="text-[#d70018] hover:underline">Trang chủ</a>
                                <i class="fa-solid fa-angle-right text-[9px]"></i>
                                <a href="#" class="text-[#d70018] hover:underline">Lifestyle</a>
                                <i class="fa-solid fa-angle-right text-[9px]"></i>
                                <span class="truncate max-w-xs text-slate-400" id="previewBreadcrumb">Bài viết</span>
                            </div>

                            {{-- Grid chia luồng bài viết chính và sidebar --}}
                            <div class="article-layout grid xl:grid-cols-[minmax(0,1fr)_300px] gap-8">
                                <div class="article-main min-w-0">
                                    {{-- Live tiêu đề --}}
                                    <h1 class="article-title text-2xl md:text-3xl font-black leading-tight text-slate-900 mb-4" id="previewTitle">
                                        Tiêu đề bài viết của bạn sẽ xuất hiện ở đây
                                    </h1>

                                    {{-- Live tác giả, avatar, ngày đăng --}}
                                    <div class="article-meta flex items-center justify-between border-b border-slate-100 pb-4 mb-6 gap-4 flex-wrap">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-full bg-[#d70018] text-white flex items-center justify-center font-black shadow-md shadow-rose-100">
                                                {{ substr(Auth::user()->full_name ?? 'G', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-sm font-bold text-slate-800" id="previewAuthor">
                                                    {{ Auth::user()->full_name ?? 'Tác giả khách' }}
                                                </div>
                                                <div class="text-[10px] text-slate-400 mt-0.5" id="previewDate">
                                                    {{ now()->format('d/m/Y - H:i') }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex gap-1.5">
                                            <span class="w-8 h-8 rounded-full bg-[#1877f2] text-white flex items-center justify-center text-xs"><i class="fa-brands fa-facebook-f"></i></span>
                                            <span class="w-8 h-8 rounded-full bg-slate-500 text-white flex items-center justify-center text-xs"><i class="fa-solid fa-link"></i></span>
                                        </div>
                                    </div>

                                    {{-- Live tóm tắt ngắn --}}
                                    <div class="article-summary-box rounded-r-2xl p-4 md:p-5 mb-6 text-slate-600 font-semibold leading-relaxed text-sm bg-slate-50/50" id="previewSummary">
                                        Tóm tắt bài viết hấp dẫn sẽ hiển thị ở đây.
                                    </div>

                                    {{-- Live nội dung HTML chi tiết --}}
                                    <div class="article-content preview-content text-[15px] leading-relaxed text-slate-700" id="previewContent">
                                        <p>Nội dung chi tiết sẽ xuất hiện ở đây sau khi bạn bắt đầu soạn thảo nội dung.</p>
                                    </div>

                                    {{-- Footer Tags bài viết --}}
                                    <div class="mt-8 flex items-center gap-2 flex-wrap text-xs text-slate-500 border-t border-slate-100 pt-6">
                                        <i class="fa-solid fa-tags text-slate-300"></i>
                                        <span class="px-3 py-1 bg-slate-100 rounded-full font-bold uppercase">STANDARD</span>
                                        <span class="px-3 py-1 bg-slate-100 rounded-full font-bold uppercase">Lifestyle</span>
                                        <span class="px-3 py-1 bg-slate-100 rounded-full font-bold uppercase">DIENMAYPRO</span>
                                    </div>
                                </div>

                                {{-- Sidebar mô phỏng --}}
                                <aside class="article-sidebar space-y-6 hidden xl:block">
                                    <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm space-y-4">
                                        <div class="text-xs font-black uppercase tracking-[0.28em] text-slate-400 flex items-center gap-2">
                                            <span class="w-1 h-3.5 bg-[#d70018] rounded-full"></span> Tin tức mới nhất
                                        </div>
                                        <div id="previewSidebarList" class="space-y-4"></div>
                                    </div>
                                    
                                    <div class="rounded-2xl overflow-hidden border border-slate-100 shadow-sm">
                                        <img src="https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=600" alt="Ad" class="w-full h-48 object-cover">
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

{{-- TÍCH HỢP JAVASCRIPT ĐỒNG BỘ TINYMCE VÀ RENDER LIVE VIEWPORT --}}
<script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    // Khởi tạo trình soạn thảo TinyMCE
    tinymce.init({
        selector: '#content_editor',
        height: 480,
        plugins: 'advlist autolink lists link image charmap preview anchor searchreplace wordcount visualblocks code fullscreen insertdatetime media table emoticons',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist | link image media | forecolor backcolor | emoticons | code',
        menubar: false,
        placeholder: 'Bắt đầu câu chuyện tuyệt vời của bạn tại đây...',
        content_style: 'body { font-family: Inter, Helvetica, Arial, sans-serif; font-size: 16px; line-height: 1.8; color: #334155; } img { max-width: 100%; height: auto; border-radius: 12px; }',
        file_picker_types: 'image',
        // Định cấu hình tải ảnh trực tiếp bằng cách chuyển thành mã nhúng Base64
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
                    syncPreview(); // Cập nhật preview sau khi chèn ảnh
                };
                reader.readAsDataURL(file);
            };
            input.click();
        },
        setup: function (editor) {
            // Lắng nghe các sự kiện thay đổi nội dung để đồng bộ sang preview
            editor.on('change keyup setcontent nodechange', function () {
                syncPreview();
            });
        }
    });

    // Lấy các tham chiếu phần tử DOM
    const titleInput = document.getElementById('titleInput');
    const summaryInput = document.getElementById('summaryInput');
    const thumbnailInput = document.getElementById('thumbnailInput');
    const previewTitle = document.getElementById('previewTitle');
    const previewSummary = document.getElementById('previewSummary');
    const previewHero = document.getElementById('thumbnailPreview');
    const previewDate = document.getElementById('previewDate');
    const previewContent = document.getElementById('previewContent');
    const previewFrame = document.getElementById('previewFrame');
    const previewDeviceLabel = document.getElementById('previewDeviceLabel');
    const deviceButtons = document.querySelectorAll('.device-btn');
    const insertSampleBtn = document.getElementById('insertSampleBtn');

    let currentDevice = 'desktop';

    // Đường dẫn ảnh fallback khi dữ liệu trống
    const PUBLIC_TEMPLATE = {
        fallbackThumbnail: 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200',
        fallbackSidebarImage: 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=400',
    };

    // Hàm chuẩn hóa và trang trí các thẻ HTML bên trong nội dung (ví dụ bo tròn góc ảnh)
    function sanitizeAndDecorateContent(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(`<div>${html || ''}</div>`, 'text/html');
        const wrapper = doc.body.firstElementChild;
        if (!wrapper) return '<p>Phần nội dung chi tiết sẽ được render ở đây sau khi bạn nhập nội dung.</p>';

        wrapper.querySelectorAll('img').forEach((img) => {
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.borderRadius = '12px';
            img.style.display = 'block';
            img.style.margin = '1rem auto';
        });

        return wrapper.innerHTML.trim() || '<p>Phần nội dung chi tiết sẽ được render ở đây sau khi bạn nhập nội dung.</p>';
    }

    // Hàm xử lý kích thước khung preview responsive khi nhấn nút
    function applyDeviceView(device) {
        currentDevice = device;
        deviceButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.device === device));

        const config = {
            desktop: { width: '100%', label: 'Desktop View' },
            tablet: { width: '768px', label: 'Tablet View' },
            mobile: { width: '380px', label: 'Mobile View' },
        }[device];

        previewFrame.style.width = config.width;
        previewFrame.style.maxWidth = '100%';
        previewFrame.dataset.device = device;
        previewDeviceLabel.textContent = config.label;
    }

    // Vẽ các bài viết liên quan giả lập ở sidebar
    function renderSidebarArticles() {
        const container = document.getElementById('previewSidebarList');
        if (!container) return;

        const items = [
            { title: 'Top 5 điện thoại tầm trung đáng mua nhất 2026', thumbnail: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400' },
            { title: 'Trải nghiệm tự sửa loa bluetooth tại nhà bằng bộ dụng cụ chuyên dụng', thumbnail: 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400' },
            { title: 'Hướng dẫn nâng cấp RAM laptop gaming cực kỳ đơn giản', thumbnail: 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=400' },
        ];

        container.innerHTML = items.map((item) => `
            <a href="#" class="flex gap-3 items-center group">
                <div class="w-16 h-12 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                    <img src="${item.thumbnail}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="${item.title}">
                </div>
                <h4 class="text-xs font-semibold text-slate-700 leading-snug line-clamp-2 group-hover:text-[#d70018] transition">${item.title}</h4>
            </a>
        `).join('');
    }

    // Đồng bộ nội dung form sang khung Preview
    function syncPreview() {
        const title = titleInput?.value?.trim() || 'Tiêu đề bài viết của bạn sẽ xuất hiện ở đây';
        const summary = summaryInput?.value?.trim() || 'Mô tả ngắn giúp người xem hiểu bài viết nhanh hơn.';
        const editor = tinymce.get('content_editor');
        const html = editor ? editor.getContent() : '';
        const contentHtml = sanitizeAndDecorateContent(html);
        const heroSrc = previewHero?.src || PUBLIC_TEMPLATE.fallbackThumbnail;

        previewTitle.textContent = title;
        previewSummary.textContent = summary;
        previewDate.textContent = new Date().toLocaleDateString('vi-VN') + ' - ' + new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        document.getElementById('previewBreadcrumb').textContent = title;
        previewHero.src = heroSrc;
        previewContent.innerHTML = contentHtml;
    }

    // Xử lý đọc file ảnh đại diện bằng FileReader
    function handleThumbnailPreview(file) {
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => { 
            previewHero.src = e.target.result; 
        };
        reader.readAsDataURL(file);
    }

    // Gắn sự kiện thay đổi dữ liệu
    thumbnailInput?.addEventListener('change', (event) => handleThumbnailPreview(event.target.files?.[0]));
    titleInput?.addEventListener('input', syncPreview);
    summaryInput?.addEventListener('input', syncPreview);

    deviceButtons.forEach(btn => btn.addEventListener('click', () => applyDeviceView(btn.dataset.device)));

    // Chèn dữ liệu mẫu khi người dùng nhấn nút "Chèn mẫu nhanh"
    insertSampleBtn?.addEventListener('click', () => {
        const editor = tinymce.get('content_editor');
        if (editor) {
            editor.setContent(`
                <h2>Tại sao chiếc Laptop của bạn lại chạy chậm dần?</h2>
                <p>Sau một thời gian sử dụng, hầu hết người dùng đều cảm thấy chiếc máy tính xách tay của mình không còn mượt mà như lúc mới mua. Dưới đây là 3 nguyên nhân chính và giải pháp khắc phục cực kỳ nhanh chóng.</p>
                
                <h3>1. Ổ cứng quá đầy hoặc bị phân mảnh</h3>
                <p>Khi dung lượng lưu trữ còn dưới 10%, hệ điều hành sẽ gặp khó khăn khi tạo file tạm bộ nhớ đệm (virtual memory). Hãy dọn dẹp các thư mục Downloads và xóa bớt ứng dụng không dùng đến.</p>
                
                <figure>
                    <img src="https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=800" alt="Bảo dưỡng phần cứng máy tính">
                    <figcaption style="font-size: 12px; color: #64748b; text-align: center; margin-top: 6px;">Vệ sinh bụi bẩn định kỳ giúp tản nhiệt tốt hơn và tối ưu hóa hiệu năng.</figcaption>
                </figure>
                
                <h3>2. Keo tản nhiệt bị khô dẫn đến quá nhiệt</h3>
                <p>Nhiệt độ CPU tăng cao sẽ kích hoạt cơ chế Thermal Throttling làm giảm hiệu năng hệ thống để bảo vệ phần cứng. Việc định kỳ vệ sinh bụi bẩn và tra keo tản nhiệt sau 1-2 năm là vô cùng cần thiết.</p>
                
                <blockquote>Lời khuyên từ chuyên gia: Nếu máy tính của bạn đã sử dụng trên 3 năm, việc nâng cấp lên ổ cứng SSD và bổ sung thêm RAM tối thiểu lên 16GB sẽ giúp chiếc máy tính chạy nhanh hơn từ 3-5 lần một cách rõ rệt.</blockquote>
            `);
            syncPreview();
        }
    });

    // --- TÍCH HỢP TRỢ LÝ AI & SEO ---
    const btnAiAnalyze = document.getElementById('btnAiAnalyze');
    const aiAnalysisResult = document.getElementById('aiAnalysisResult');
    const aiQualityScore = document.getElementById('aiQualityScore');
    const aiSeoScore = document.getElementById('aiSeoScore');
    const aiVerdictBox = document.getElementById('aiVerdictBox');
    const aiVerdictIcon = document.getElementById('aiVerdictIcon');
    const aiVerdictLabel = document.getElementById('aiVerdictLabel');
    const aiVerdictReason = document.getElementById('aiVerdictReason');
    const aiTagsContainer = document.getElementById('aiTagsContainer');
    const aiSuggestedTitle = document.getElementById('aiSuggestedTitle');
    const aiSuggestedSummary = document.getElementById('aiSuggestedSummary');
    const aiKeywordsContainer = document.getElementById('aiKeywordsContainer');
    const aiTipsContainer = document.getElementById('aiTipsContainer');
    const btnApplyAiTitle = document.getElementById('btnApplyAiTitle');
    const btnApplyAiSummary = document.getElementById('btnApplyAiSummary');

    let aiDataCache = null;

    function renderAiResult(data) {
        aiDataCache = data;

        // Cập nhật điểm chất lượng & điểm SEO & điểm thưởng đề xuất
        aiQualityScore.textContent = data.quality_score + '/100';
        aiSeoScore.textContent = data.seo.seo_score + '/100';
        const aiRewardPoints = document.getElementById('aiRewardPoints');
        if (aiRewardPoints) {
            aiRewardPoints.textContent = '+' + (data.recommended_reward_points ?? 20) + 'đ';
        }

        // Cập nhật kết quả kiểm duyệt
        aiVerdictBox.className = 'p-3 rounded-2xl border';
        if (data.moderation_verdict === 'approved') {
            aiVerdictBox.classList.add('bg-emerald-50', 'border-emerald-100', 'text-emerald-800');
            aiVerdictIcon.className = 'fa-solid fa-circle-check text-emerald-600';
            aiVerdictLabel.textContent = 'An toàn - Đủ điều kiện duyệt';
        } else if (data.moderation_verdict === 'flagged') {
            aiVerdictBox.classList.add('bg-amber-50', 'border-amber-100', 'text-amber-800');
            aiVerdictIcon.className = 'fa-solid fa-triangle-exclamation text-amber-600';
            aiVerdictLabel.textContent = 'Cần xem xét - Chờ kiểm duyệt';
        } else {
            aiVerdictBox.classList.add('bg-rose-50', 'border-rose-100', 'text-rose-800');
            aiVerdictIcon.className = 'fa-solid fa-circle-xmark text-rose-600';
            aiVerdictLabel.textContent = 'Vi phạm chính sách nội dung';
        }
        aiVerdictReason.textContent = data.moderation_reason;

        // Cập nhật tags gợi ý
        aiTagsContainer.innerHTML = '';
        if (data.tags && data.tags.length > 0) {
            data.tags.forEach(tag => {
                const badge = document.createElement('span');
                badge.className = 'px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-wider cursor-pointer hover:bg-slate-200 transition';
                badge.textContent = tag;
                aiTagsContainer.appendChild(badge);
            });
        } else {
            aiTagsContainer.innerHTML = '<span class="text-slate-400 italic">Không có tag gợi ý.</span>';
        }

        // Tiêu đề & Tóm tắt đề xuất
        aiSuggestedTitle.textContent = data.seo.title_suggestion || titleInput.value.trim();
        aiSuggestedSummary.textContent = data.seo.meta_description_suggestion || summaryInput.value.trim();

        // Mật độ từ khóa
        aiKeywordsContainer.innerHTML = '';
        if (data.seo.keywords_analysis && data.seo.keywords_analysis.length > 0) {
            data.seo.keywords_analysis.forEach(kw => {
                const row = document.createElement('div');
                row.className = 'flex justify-between text-[11px] text-slate-600';
                row.innerHTML = `<span><strong>${kw.keyword}</strong></span> <span>${kw.count} lần (${kw.density})</span>`;
                aiKeywordsContainer.appendChild(row);
            });
        } else {
            aiKeywordsContainer.innerHTML = '<div class="text-slate-400 italic">Không trích xuất được từ khóa.</div>';
        }

        // Lời khuyên tối ưu
        aiTipsContainer.innerHTML = '';
        if (data.seo.optimization_tips && data.seo.optimization_tips.length > 0) {
            data.seo.optimization_tips.forEach(tip => {
                const li = document.createElement('li');
                li.textContent = tip;
                aiTipsContainer.appendChild(li);
            });
        } else {
            aiTipsContainer.innerHTML = '<li>Bài viết đã được tối ưu hóa rất tốt!</li>';
        }

        // Hiển thị phần kết quả
        aiAnalysisResult.classList.remove('hidden');
    }

    btnAiAnalyze?.addEventListener('click', async () => {
        const title = titleInput.value.trim();
        const summary = summaryInput.value.trim();
        const editor = tinymce.get('content_editor');
        const content = editor ? editor.getContent() : '';

        if (!title || !content) {
            Swal.fire({
                icon: 'warning',
                title: 'Thiếu thông tin',
                text: 'Vui lòng điền Tiêu đề và Nội dung bài viết trước khi chạy phân tích AI.',
                confirmButtonColor: '#d70018',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl'
                }
            });
            return;
        }

        // Đổi trạng thái nút bấm thành Loading
        btnAiAnalyze.disabled = true;
        const originalBtnHtml = btnAiAnalyze.innerHTML;
        btnAiAnalyze.innerHTML = `<i class="fa-solid fa-spinner animate-spin"></i> <span>Đang phân tích...</span>`;

        try {
            const response = await fetch('{{ route("articles.ai-assist") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ title, summary, content })
            });

            if (!response.ok) {
                throw new Error('Yêu cầu phân tích AI thất bại.');
            }

            const data = await response.json();
            renderAiResult(data);

        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Lỗi phân tích AI',
                text: 'Có lỗi xảy ra trong quá trình gọi AI phân tích bài viết. Vui lòng thử lại.',
                confirmButtonColor: '#d70018',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl'
                }
            });
        } finally {
            btnAiAnalyze.disabled = false;
            btnAiAnalyze.innerHTML = originalBtnHtml;
        }
    });

    btnApplyAiTitle?.addEventListener('click', () => {
        if (aiDataCache && aiDataCache.seo.title_suggestion) {
            titleInput.value = aiDataCache.seo.title_suggestion;
            syncPreview();
            
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Đã áp dụng tiêu đề đề xuất chuẩn SEO!'
            });
        }
    });

    btnApplyAiSummary?.addEventListener('click', () => {
        if (aiDataCache && aiDataCache.seo.meta_description_suggestion) {
            summaryInput.value = aiDataCache.seo.meta_description_suggestion;
            syncPreview();
            
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Đã áp dụng tóm tắt đề xuất chuẩn SEO!'
            });
        }
    });

    // Khởi tạo trang ban đầu
    document.addEventListener('DOMContentLoaded', () => {
        applyDeviceView('desktop');
        renderSidebarArticles();
        syncPreview();

        // Nạp và hiển thị kết quả phân tích AI có sẵn (cho trường hợp chỉnh sửa bài viết)
        @if(isset($article) && $article->exists && $article->ai_checked)
            const preloadedData = @json($article->ai_analysis);
            if (preloadedData) {
                renderAiResult(preloadedData);
            }
        @endif
    });
</script>
@endsection
