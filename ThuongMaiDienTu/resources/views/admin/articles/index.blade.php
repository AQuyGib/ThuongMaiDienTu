@extends('admin.layouts.master')
@section('title', 'Quản lý Bài viết & Nội dung')
@section('page-title', 'Quản lý Bài viết & Nội dung')

@section('content')

<div class="space-y-6">
    <style>
        /* Card mờ ảo (glassmorphism) cho giao diện quản trị */
        .glass-card { background: rgba(255,255,255,.88); backdrop-filter: blur(18px); border: 1px solid rgba(255,255,255,.65); box-shadow: 0 20px 60px -28px rgba(15, 23, 42, .25); }
        .stat-card { transition: .25s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 18px 40px -28px rgba(37,99,235,.35); }
        .article-card { transition: .25s ease; }
        .article-card:hover { transform: translateY(-3px); }
        
        /* Trạng thái active của các chip lọc nhanh bài viết */
        .filter-chip.active { background: #2563eb; color: #fff; border-color: #2563eb; }
        
        /* Ẩn bớt các thành phần phân trang thừa mặc định của Tailwind để làm gọn UI */
        .custom-pagination nav p, .custom-pagination nav > div:first-child { display: none !important; }
        .custom-pagination svg { width: 18px; height: 18px; }
    </style>

    {{-- KHỐI HEADER GIỚI THIỆU TRANG VÀ NÚT TẠO MỚI BÀI VIẾT --}}
    <div class="rounded-[2rem] overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white shadow-2xl">
        <div class="p-6 md:p-8 flex flex-col xl:flex-row xl:items-end justify-between gap-6">
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[11px] font-bold tracking-[0.3em] uppercase border border-white/10">Article Studio</div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight">Quản lý bài viết hiện đại</h1>
                    <p class="mt-3 text-slate-300 max-w-2xl leading-relaxed">Theo dõi bài đăng theo trạng thái, lọc nhanh theo tác giả/format và tạo nội dung mới với trải nghiệm xem trước trực quan.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if(App\Models\Article::where('author_type', 'customer')->where('status', 'pending')->where('ai_checked', 1)->where('ai_moderation_verdict', 'approved')->exists())
                    <form id="bulk-approve-form" action="{{ route('admin.articles.bulk-approve-ai') }}" method="POST">
                        @csrf
                        <button type="button" id="btn-bulk-approve" class="inline-flex items-center gap-3 px-5 py-3 rounded-2xl bg-emerald-600 text-white font-black shadow-lg shadow-emerald-950/20 hover:-translate-y-0.5 transition">
                            <i class="fa-solid fa-wand-magic-sparkles animate-pulse"></i>
                            Duyệt bài đạt chuẩn AI hàng loạt
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center gap-3 px-5 py-3 rounded-2xl bg-white text-slate-900 font-black shadow-lg shadow-black/10 hover:-translate-y-0.5 transition">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Tạo bài viết
                </a>
            </div>
        </div>
    </div>

    {{-- PHẦN THÔNG TIN THỐNG KÊ (KPI CARD) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-5 gap-4">
        <div class="glass-card stat-card rounded-[1.75rem] p-5">
            <div class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Tổng bài viết</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="glass-card stat-card rounded-[1.75rem] p-5">
            <div class="text-[11px] font-black uppercase tracking-[0.3em] text-blue-500">Đã quét AI</div>
            <div class="mt-3 text-3xl font-black text-blue-600">{{ number_format($stats['ai_checked']) }}</div>
        </div>
        <div class="glass-card stat-card rounded-[1.75rem] p-5">
            <div class="text-[11px] font-black uppercase tracking-[0.3em] text-emerald-500">Đã duyệt</div>
            <div class="mt-3 text-3xl font-black text-emerald-600">{{ number_format($stats['approved']) }}</div>
        </div>
        <div class="glass-card stat-card rounded-[1.75rem] p-5">
            <div class="text-[11px] font-black uppercase tracking-[0.3em] text-amber-500">Chờ duyệt</div>
            <div class="mt-3 text-3xl font-black text-amber-600">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="glass-card stat-card rounded-[1.75rem] p-5">
            <div class="text-[11px] font-black uppercase tracking-[0.3em] text-rose-500">Từ chối</div>
            <div class="mt-3 text-3xl font-black text-rose-600">{{ number_format($stats['rejected']) }}</div>
        </div>
    </div>

    {{-- KHỐI CONTAINER CHÍNH CHỨA BỘ LỌC VÀ BẢNG DANH SÁCH BÀI VIẾT --}}
    <div class="glass-card rounded-[2rem] p-5 md:p-6">
        
        {{-- Khối tìm kiếm bài viết --}}
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-black text-slate-900">Bộ lọc & thao tác nhanh</h2>
                <p class="text-sm text-slate-500 mt-1">Tìm đúng bài viết trong vài giây.</p>
            </div>
            <form action="{{ route('admin.articles.index') }}" method="GET" class="w-full xl:max-w-xl">
                <div class="flex items-center gap-3">
                    <div class="flex-1 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tiêu đề, tác giả..." class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 bg-white outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-400">
                    </div>
                    <button class="px-5 py-3 rounded-2xl bg-slate-900 text-white font-black hover:bg-blue-700 transition">Tìm</button>
                </div>
            </form>
        </div>

        {{-- Các chip lọc nhanh bài đăng theo trạng thái --}}
        <div class="flex flex-wrap gap-3 mb-5">
            @php
                $filters = [
                    '' => 'Tất cả',
                    'pending' => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    'rejected' => 'Từ chối',
                    'ai_checked' => 'Đã quét AI',
                ];
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.articles.index', array_filter(array_merge(request()->except('page'), ['status' => $key]), fn($v) => $v !== '' && $v !== null)) }}" class="filter-chip inline-flex items-center px-4 py-2 rounded-full border text-sm font-bold transition {{ request('status') === $key || (!request('status') && $key === '') ? 'active' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-blue-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Bảng hiển thị thông tin bài viết --}}
        <div class="overflow-x-auto rounded-[1.75rem] border border-slate-100 bg-white">
            <table class="min-w-full">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-[0.25em] text-slate-400">
                    <tr>
                        <th class="px-6 py-4 text-left">Nội dung</th>
                        <th class="px-6 py-4 text-left">Tác giả</th>
                        <th class="px-6 py-4 text-center">Format</th>
                        <th class="px-6 py-4 text-center">Kiểm duyệt AI</th>
                        <th class="px-6 py-4 text-left">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($articles as $article)
                        <tr class="article-card hover:bg-blue-50/40">
                            {{-- Cột nội dung chính (Ảnh thumbnail, Tiêu đề, Ngày đăng, Ticket sửa chữa liên kết nếu có) --}}
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-100 shrink-0 ring-1 ring-slate-100">
                                        <img src="{{ $article->thumbnail ? asset($article->thumbnail) : 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?w=400' }}" class="w-full h-full object-cover" alt="{{ $article->title }}">
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="text-slate-900 font-black line-clamp-2 hover:text-blue-600 transition">{{ $article->title }}</a>
                                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 font-semibold"><i class="fa-regular fa-calendar"></i> {{ $article->created_at->format('d/m/Y') }}</span>
                                            @if($article->related_ticket_id)
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold">#{{ $article->related_ticket_id }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            {{-- Cột thông tin tác giả bài viết (Phân loại Admin/Khách hàng viết bài) --}}
                            <td class="px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <span class="font-bold text-slate-900">{{ $article->author->full_name ?? 'N/A' }}</span>
                                    <span class="inline-flex w-fit px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-[0.2em] {{ $article->author_type === 'admin' ? 'bg-indigo-50 text-indigo-600' : 'bg-emerald-50 text-emerald-600' }}">{{ $article->author_type === 'admin' ? 'Admin' : 'Customer' }}</span>
                                </div>
                            </td>
                            
                            {{-- Cột phân loại format layout bài viết --}}
                            <td class="px-6 py-5 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold uppercase">{{ $article->format_type }}</span>
                            </td>
                            
                            {{-- Cột kiểm duyệt AI --}}
                            <td class="px-6 py-5">
                                <div class="flex flex-col items-center gap-1">
                                    @if($article->ai_checked)
                                        @if($article->ai_moderation_verdict === 'approved')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                                                <i class="fa-solid fa-circle-check text-emerald-500"></i> AI: An toàn
                                            </span>
                                        @elseif($article->ai_moderation_verdict === 'flagged')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-[10px] font-black uppercase tracking-wider border border-amber-100 animate-pulse">
                                                <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> AI: Xem xét
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 text-[10px] font-black uppercase tracking-wider border border-rose-100">
                                                <i class="fa-solid fa-circle-xmark text-rose-500"></i> AI: Vi phạm
                                            </span>
                                        @endif
                                        <div class="text-[10px] text-slate-400 mt-0.5 text-center leading-normal">
                                            Chất lượng: <strong class="text-slate-700">{{ $article->ai_quality_score ?? 0 }}%</strong><br>
                                            SEO: <strong class="text-slate-700">{{ $article->seo_score ?? 0 }}%</strong>
                                        </div>
                                        <button type="button" class="btn-show-ai-report mt-1 text-[10px] font-black uppercase tracking-wider text-blue-600 hover:text-blue-800 hover:underline flex items-center gap-0.5" 
                                                data-title="{{ e($article->title) }}"
                                                data-verdict="{{ $article->ai_moderation_verdict }}"
                                                data-quality-score="{{ $article->ai_quality_score }}"
                                                data-seo-score="{{ $article->seo_score }}"
                                                data-analysis="{{ json_encode($article->ai_analysis) }}"
                                                data-tags="{{ json_encode($article->tags) }}">
                                            <i class="fa-solid fa-wand-magic-sparkles text-rose-500 animate-pulse"></i> Xem phân tích
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Chưa quét AI</span>
                                    @endif
                                </div>
                            </td>
                            
                            {{-- Cột trạng thái kiểm duyệt bài viết --}}
                            <td class="px-6 py-5">
                                @if($article->status === 'approved')
                                    <span class="inline-flex items-center gap-2 text-emerald-600 font-black uppercase text-xs tracking-[0.2em]"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Đã duyệt</span>
                                @elseif($article->status === 'pending')
                                    <span class="inline-flex items-center gap-2 text-amber-600 font-black uppercase text-xs tracking-[0.2em]"><span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>Chờ duyệt</span>
                                @else
                                    <span class="inline-flex items-center gap-2 text-rose-600 font-black uppercase text-xs tracking-[0.2em]"><span class="w-2 h-2 rounded-full bg-rose-500"></span>Từ chối</span>
                                @endif
                            </td>
                            
                            {{-- Cột các nút thao tác quản trị nhanh --}}
                            <td class="px-6 py-5">
                                <div class="flex justify-center items-center gap-2">
                                    {{-- Nút sửa --}}
                                    <a href="{{ route('admin.articles.edit', $article->article_id) }}" class="w-10 h-10 inline-flex items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" title="Chỉnh sửa"><i class="fa-solid fa-pen-nib"></i></a>
                                    {{-- Nút xem chi tiết --}}
                                    <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="w-10 h-10 inline-flex items-center justify-center rounded-2xl bg-slate-100 text-slate-600 hover:bg-slate-900 hover:text-white transition" title="Xem trước"><i class="fa-solid fa-eye"></i></a>
                                    {{-- Form xóa bài viết --}}
                                    <form action="{{ route('admin.articles.destroy', $article->article_id) }}" method="POST" class="delete-article-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn-delete-article w-10 h-10 inline-flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-slate-500">
                                <i class="fa-regular fa-newspaper text-4xl text-slate-300"></i>
                                <div class="mt-4 font-bold">Chưa có bài viết nào</div>
                                <div class="text-sm mt-1">Bắt đầu bằng cách tạo bài viết đầu tiên của bạn.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Phân trang của bảng danh sách quản lý --}}
        <div class="mt-5 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500">
            <div class="font-semibold">Hiển thị {{ $articles->count() }} / {{ $articles->total() }} bài viết</div>
            <div class="custom-pagination">{{ $articles->withQueryString()->links() }}</div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportButtons = document.querySelectorAll('.btn-show-ai-report');
        reportButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const title = this.dataset.title;
                const verdict = this.dataset.verdict;
                const qualityScore = parseInt(this.dataset.qualityScore) || 0;
                const seoScore = parseInt(this.dataset.seoScore) || 0;
                
                let analysis = {};
                try {
                    analysis = JSON.parse(this.dataset.analysis || '{}');
                } catch(e) {
                    console.error("JSON parse analysis failed", e);
                }

                let tags = [];
                try {
                    tags = JSON.parse(this.dataset.tags || '[]');
                } catch(e) {
                    console.error("JSON parse tags failed", e);
                }

                // Xây dựng giao diện hiển thị báo cáo AI
                let verdictBadge = '';
                if (verdict === 'approved') {
                    verdictBadge = `<span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 text-xs font-black uppercase tracking-wider border border-emerald-100">
                        <i class="fa-solid fa-circle-check"></i> An toàn - Đủ điều kiện duyệt
                    </span>`;
                } else if (verdict === 'flagged') {
                    verdictBadge = `<span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-amber-50 text-amber-800 text-xs font-black uppercase tracking-wider border border-amber-100">
                        <i class="fa-solid fa-triangle-exclamation"></i> Cần xem xét - Chờ kiểm duyệt
                    </span>`;
                } else {
                    verdictBadge = `<span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full bg-rose-50 text-rose-800 text-xs font-black uppercase tracking-wider border border-rose-100">
                        <i class="fa-solid fa-circle-xmark"></i> Vi phạm chính sách nội dung
                    </span>`;
                }

                let spamHtml = '';
                if (analysis.spam_check) {
                    const isSpam = analysis.spam_check.is_spam ? 'Có' : 'Không';
                    const spamClass = analysis.spam_check.is_spam ? 'text-rose-600 font-bold' : 'text-emerald-600 font-bold';
                    spamHtml = `
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-start space-y-1">
                            <div class="text-[10px] uppercase font-black tracking-wider text-slate-400">Kiểm tra Spam</div>
                            <div class="text-sm ${spamClass}">${isSpam} (${analysis.spam_check.spam_score || 0}/100)</div>
                            <div class="text-[11px] text-slate-500 leading-relaxed">${analysis.spam_check.reason || ''}</div>
                        </div>
                    `;
                }

                let plagiarismHtml = '';
                if (analysis.plagiarism_check) {
                    const isPlag = analysis.plagiarism_check.is_copied ? 'Có' : 'Không';
                    const plagClass = analysis.plagiarism_check.is_copied ? 'text-rose-600 font-bold' : 'text-emerald-600 font-bold';
                    plagiarismHtml = `
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-start space-y-1">
                            <div class="text-[10px] uppercase font-black tracking-wider text-slate-400">Độ trùng lặp (Đạo văn)</div>
                            <div class="text-sm ${plagClass}">${isPlag} (Tỷ lệ: ${analysis.plagiarism_check.similarity_score || 0}%)</div>
                            <div class="text-[11px] text-slate-500 leading-relaxed">${analysis.plagiarism_check.reason || ''}</div>
                        </div>
                    `;
                }

                let sensitiveHtml = '';
                if (analysis.sensitive_content_check) {
                    const isSens = analysis.sensitive_content_check.has_sensitive_words ? 'Có' : 'Không';
                    const sensClass = analysis.sensitive_content_check.has_sensitive_words ? 'text-rose-600 font-bold' : 'text-emerald-600 font-bold';
                    sensitiveHtml = `
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-start space-y-1">
                            <div class="text-[10px] uppercase font-black tracking-wider text-slate-400">Từ ngữ nhạy cảm</div>
                            <div class="text-sm ${sensClass}">${isSens}</div>
                            <div class="text-[11px] text-slate-500 leading-relaxed">${analysis.sensitive_content_check.reason || ''}</div>
                        </div>
                    `;
                }

                let tagsHtml = '';
                if (tags && tags.length > 0) {
                    tagsHtml = tags.map(tag => `<span class="px-2.5 py-1 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-[10px] font-black uppercase tracking-wider">#${tag}</span>`).join(' ');
                } else {
                    tagsHtml = '<span class="text-slate-400 italic">Không có tag</span>';
                }

                const contentHtml = `
                    <div class="space-y-4 text-slate-700 text-start">
                        <div class="text-center pb-2 border-b border-slate-100">
                            <h4 class="font-extrabold text-slate-900 text-base leading-snug">${title}</h4>
                            <div class="mt-2">${verdictBadge}</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div class="bg-gradient-to-br from-slate-950 to-slate-900 text-white p-3 rounded-2xl">
                                <div class="text-[10px] font-black uppercase tracking-wider text-slate-400">Điểm chất lượng</div>
                                <div class="text-2xl font-black mt-1">${qualityScore}/100</div>
                            </div>
                            <div class="bg-gradient-to-br from-blue-950 to-indigo-950 text-white p-3 rounded-2xl">
                                <div class="text-[10px] font-black uppercase tracking-wider text-blue-300">Điểm SEO</div>
                                <div class="text-2xl font-black mt-1">${seoScore}/100</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h5 class="font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-1"><i class="fa-solid fa-shield-halved text-rose-600"></i> Báo cáo kiểm duyệt AI</h5>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                ${spamHtml || '<div class="text-slate-400 italic">Không có dữ liệu spam</div>'}
                                ${plagiarismHtml || '<div class="text-slate-400 italic">Không có dữ liệu đạo văn</div>'}
                                ${sensitiveHtml || '<div class="text-slate-400 italic">Không có dữ liệu từ nhạy cảm</div>'}
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h5 class="font-bold text-slate-900 text-xs uppercase tracking-wider flex items-center gap-1"><i class="fa-solid fa-tags text-blue-600"></i> Hashtag tự động (Tags)</h5>
                            <div class="flex flex-wrap gap-2 p-3 bg-slate-50 rounded-2xl border border-slate-200">
                                ${tagsHtml}
                            </div>
                        </div>
                    </div>
                `;

                Swal.fire({
                    title: '<span class="text-lg font-black text-slate-900 flex items-center gap-2"><i class="fa-solid fa-robot text-rose-600 animate-bounce"></i> Nhật ký kiểm duyệt AI</span>',
                    html: contentHtml,
                    width: '700px',
                    showCloseButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Đóng lại',
                    confirmButtonColor: '#0f172a',
                    customClass: {
                        popup: 'rounded-[2rem] border border-slate-100 shadow-2xl',
                        confirmButton: 'rounded-2xl px-6 py-2.5 font-extrabold text-sm'
                    }
                });
            });
        });

        // Xử lý xác nhận Duyệt hàng loạt bài viết đạt chuẩn AI
        const btnBulkApprove = document.getElementById('btn-bulk-approve');
        if (btnBulkApprove) {
            btnBulkApprove.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '<span class="text-lg font-black text-slate-900">Duyệt hàng loạt?</span>',
                    html: '<p class="text-sm text-slate-600">Tất cả các bài viết chờ duyệt <strong>đạt chuẩn AI</strong> sẽ được phê duyệt và cộng điểm tích lũy ví tương ứng cho tác giả.</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Đồng ý, duyệt ngay!',
                    cancelButtonText: 'Hủy bỏ',
                    customClass: {
                        popup: 'rounded-[2rem] border border-slate-100 p-6',
                        confirmButton: 'rounded-2xl px-5 py-2.5 font-extrabold text-sm',
                        cancelButton: 'rounded-2xl px-5 py-2.5 font-extrabold text-sm'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('bulk-approve-form').submit();
                    }
                });
            });
        }

        // Xử lý xác nhận Xóa bài viết
        const deleteButtons = document.querySelectorAll('.btn-delete-article');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                Swal.fire({
                    title: '<span class="text-lg font-black text-slate-900">Xóa bài viết?</span>',
                    html: '<p class="text-sm text-slate-600">Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa bài viết này?</p>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Đồng ý, xóa ngay!',
                    cancelButtonText: 'Hủy bỏ',
                    customClass: {
                        popup: 'rounded-[2rem] border border-slate-100 p-6',
                        confirmButton: 'rounded-2xl px-5 py-2.5 font-extrabold text-sm',
                        cancelButton: 'rounded-2xl px-5 py-2.5 font-extrabold text-sm'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection