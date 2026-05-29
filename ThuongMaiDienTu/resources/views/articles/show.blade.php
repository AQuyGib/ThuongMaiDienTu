@extends('layouts.app')

@section('title', $article->title . ' - Lifestyle DIENMAY PRO')

@push('styles')
<style>
    /* Nền trang nhẹ cho giao diện đọc báo tin tức */
    body { background: #f4f7fb; }
    
    /* Vùng chứa (container) chính cho trang chi tiết bài viết */
    .article-shell { max-width: 1280px; margin: 0 auto; padding: 24px 16px 56px; }
    
    /* Header bài viết có màu gradient tối sang trọng */
    .hero { background: linear-gradient(135deg, #0f172a 0%, #0b4fd6 55%, #7c3aed 100%); color: #fff; border-radius: 32px; overflow: hidden; box-shadow: 0 30px 80px -35px rgba(15,23,42,.45); }
    
    /* Box chứa ảnh đại diện chính của bài viết sử dụng kính mờ */
    .card { background: rgba(255,255,255,.88); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,.75); box-shadow: 0 18px 50px -30px rgba(15,23,42,.2); }
    
    /* Bố cục Grid: phần bài viết chiếm 65% (bên trái), sidebar chiếm 35% (bên phải) */
    .article-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(300px, .65fr); gap: 24px; align-items: start; }
    @media (max-width: 1024px) { .article-grid { grid-template-columns: 1fr; } }
    
    /* Vùng chứa nội dung chính bài viết màu trắng nổi bật */
    .content { background: #fff; border-radius: 28px; padding: 28px; border: 1px solid #e2e8f0; box-shadow: 0 18px 50px -30px rgba(15,23,42,.22); }
    
    /* Từng bài viết phụ hiển thị ở sidebar */
    .sidebar-post { display: flex; gap: 12px; padding: 12px 0; text-decoration: none; border-bottom: 1px solid #eef2f7; }
    .sidebar-post:last-child { border-bottom: 0; }
    .sidebar-post:hover .sp-title { color: #2563eb; }
    
    /* Ảnh đại diện nhỏ của bài viết ở sidebar */
    .sp-img { width: 96px; height: 70px; border-radius: 16px; object-fit: cover; flex-shrink: 0; }
    .sp-title { font-size: 14px; font-weight: 800; color: #0f172a; line-height: 1.45; transition: .2s; }
    
    /* CSS định dạng các thẻ HTML (Rich Text) được sinh ra từ trình soạn thảo TinyMCE */
    .article-content { color: #334155; font-size: 16px; line-height: 1.9; }
    .article-content h1, .article-content h2, .article-content h3 { color: #0f172a; font-weight: 900; line-height: 1.25; margin: 1.4rem 0 .75rem; }
    .article-content h1 { font-size: 2.25rem; }
    .article-content h2 { font-size: 1.6rem; }
    .article-content h3 { font-size: 1.25rem; }
    .article-content p { margin: 0 0 1rem; }
    .article-content img { width: 100%; height: auto; border-radius: 20px; margin: 1rem 0; box-shadow: 0 16px 38px -24px rgba(15,23,42,.35); }
    
    /* Định dạng khối trích dẫn */
    .article-content blockquote { border-left: 4px solid #2563eb; padding: .9rem 1rem; background: #eff6ff; color: #1d4ed8; border-radius: 0 16px 16px 0; margin: 1.25rem 0; }
    
    /* Định dạng bảng dữ liệu bên trong bài viết */
    .article-content table { width: 100%; border-collapse: collapse; margin: 1rem 0; overflow: hidden; border-radius: 16px; }
    .article-content th, .article-content td { border: 1px solid #e2e8f0; padding: .8rem; text-align: left; }
    
    /* Nhãn (Badge) hiển thị các siêu dữ liệu bài viết (tác giả, ngày đăng, định dạng) */
    .meta-pill { display: inline-flex; align-items: center; gap: 8px; padding: 9px 12px; border-radius: 999px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.14); font-size: 12px; font-weight: 800; }
    
    /* Các nút chia sẻ nhanh lên MXH */
    .share-btn { width: 40px; height: 40px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; color: #fff; transition: .2s; }
</style>
@endpush

@section('content')
<div class="article-shell space-y-6">
    {{-- PHẦN BANNER GIỚI THIỆU BÀI VIẾT (HERO BANNER) --}}
    <section class="hero">
        <div class="p-6 md:p-10 grid lg:grid-cols-[1.15fr_.85fr] gap-8 items-end">
            {{-- Thông tin chi tiết tiêu đề, tác giả, ngày đăng của bài viết --}}
            <div class="space-y-5">
                <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 border border-white/10 text-white text-xs font-black uppercase tracking-[0.25em] w-fit">
                    <i class="fa-solid fa-arrow-left"></i> Quay về Lifestyle
                </a>
                <div class="flex flex-wrap gap-3">
                    <span class="meta-pill"><i class="fa-regular fa-calendar"></i>{{ $article->created_at->format('d/m/Y - H:i') }}</span>
                    <span class="meta-pill"><i class="fa-regular fa-user"></i>{{ $article->author->full_name ?? 'Ban biên tập' }}</span>
                    <span class="meta-pill">{{ $article->format_type }}</span>
                </div>
                <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-tight">{{ $article->title }}</h1>
                @if($article->summary)
                    <p class="text-slate-200 max-w-3xl text-base md:text-lg leading-relaxed">{{ $article->summary }}</p>
                @endif
                <div class="flex flex-wrap gap-3 pt-1">
                    <a href="#article-body" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-slate-900 font-black hover:-translate-y-0.5 transition">
                        <i class="fa-solid fa-book-open"></i> Đọc ngay
                    </a>
                    <div class="inline-flex items-center gap-3 px-5 py-3 rounded-2xl bg-white/10 border border-white/10 text-white font-bold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Bài viết nổi bật
                    </div>
                </div>
            </div>
            
            {{-- Ảnh đại diện chính của bài viết ở góc phải hero --}}
            <div class="card rounded-[28px] overflow-hidden">
                <img src="{{ $article->thumbnail ? asset($article->thumbnail) : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200' }}" alt="{{ $article->title }}" class="w-full h-full object-cover min-h-[320px] max-h-[420px]">
            </div>
        </div>
    </section>

    {{-- PHẦN THÂN TRANG CHIA GRID: NỘI DUNG CHÍNH & SIDEBAR BÀI VIẾT LIÊN QUAN --}}
    <div class="article-grid">
        {{-- KHU VỰC BÀI VIẾT CHI TIẾT --}}
        <main class="content" id="article-body">
            {{-- Thanh thông tin tác giả và nút chia sẻ nhanh --}}
            <div class="flex items-center justify-between gap-3 pb-5 border-b border-slate-100 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-xl">
                        {{ substr($article->author->full_name ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <div class="font-black text-slate-900">{{ $article->author->full_name ?? 'Ban biên tập DIENMAY PRO' }}</div>
                        <div class="text-sm text-slate-500">{{ $article->created_at->format('d/m/Y - H:i') }}</div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="#" class="share-btn bg-[#1877f2]"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="share-btn bg-slate-500"><i class="fa-solid fa-link"></i></a>
                </div>
            </div>

            {{-- Nội dung bài viết định dạng rich text từ database (Được tắt escape HTML để hiển thị cấu hình TinyMCE) --}}
            <article class="article-content prose prose-slate max-w-none">
                {!! $article->content !!}
            </article>

            {{-- LIÊN KẾT HỆ SINH THÁI (ECOSYSTEM): Hiển thị nếu bài viết này được đính kèm liên kết với một Đơn Sửa Chữa (Repair Ticket) --}}
            @if($relatedTicket)
                <section class="mt-8 rounded-[24px] p-6 bg-gradient-to-br from-blue-600 to-indigo-700 text-white relative overflow-hidden">
                    <div class="relative z-10 space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-[11px] font-black uppercase tracking-[0.25em] w-fit">Ecosystem</div>
                        <h2 class="text-2xl md:text-3xl font-black">Nhật ký Hồi sinh Thiết bị</h2>
                        <p class="text-white/80 leading-relaxed">Bài viết này nằm trong chuỗi series "Right to Repair" thực tế từ hệ thống DIENMAY PRO. Chuyên gia của chúng tôi đã trực tiếp xử lý trường hợp mã <strong>#{{ $relatedTicket->ticket_id }}</strong>.</p>
                        <a href="#" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-black hover:-translate-y-0.5 transition w-fit">
                            <i class="fa-solid fa-screwdriver-wrench"></i> Tra cứu đơn sửa chữa
                        </a>
                    </div>
                    <i class="fa-solid fa-gears absolute text-white/10 text-[160px] -right-4 -bottom-6"></i>
                </section>
            @endif
        </main>

        {{-- SIDEBAR BÊN PHẢI --}}
        <aside class="space-y-5">
            {{-- Widget hiển thị các bài viết liên quan (bài mới nhất) --}}
            <div class="card rounded-[24px] p-5">
                <div class="text-[11px] font-black uppercase tracking-[0.28em] text-slate-400">Sidebar</div>
                <h3 class="text-xl font-black text-slate-900 mt-2">Bài viết liên quan</h3>
                <div class="mt-4">
                    @foreach($recentArticles as $recent)
                        <a href="{{ route('articles.show', $recent->slug) }}" class="sidebar-post">
                            <img src="{{ $recent->thumbnail ? asset($recent->thumbnail) : 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=300' }}" alt="{{ $recent->title }}" class="sp-img">
                            <div class="min-w-0">
                                <div class="sp-title line-clamp-3">{{ $recent->title }}</div>
                                <div class="text-xs text-slate-500 mt-2">{{ $recent->created_at->format('d/m/Y') }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Widget Banner quảng cáo --}}
            <div class="card rounded-[24px] p-5 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=900" alt="Advertisement" class="w-full rounded-[20px] object-cover">
            </div>

            {{-- Widget hiển thị các tag nhãn của bài viết --}}
            <div class="card rounded-[24px] p-5">
                <div class="text-[11px] font-black uppercase tracking-[0.28em] text-slate-400">Tags</div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="px-3 py-2 rounded-full bg-slate-100 text-slate-600 text-xs font-black uppercase tracking-[0.2em]">{{ $article->format_type }}</span>
                    <span class="px-3 py-2 rounded-full bg-blue-50 text-blue-700 text-xs font-black uppercase tracking-[0.2em]">Lifestyle</span>
                    <span class="px-3 py-2 rounded-full bg-emerald-50 text-emerald-700 text-xs font-black uppercase tracking-[0.2em]">DIENMAY PRO</span>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection