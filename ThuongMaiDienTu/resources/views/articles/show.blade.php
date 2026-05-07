@extends('layouts.app')

@section('title', $article->title . ' - 24h Công Nghệ')

@push('styles')
<style>
    body { background-color: #f4f6f8; }
    .article-container { max-width: 1200px; margin: 0 auto; padding: 20px 15px; display: grid; grid-template-columns: 800px 1fr; gap: 30px; }
    @media (max-width: 1024px) { .article-container { grid-template-columns: 1fr; } }
    
    /* Breadcrumb */
    .breadcrumb { font-size: 13px; color: #6b7280; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    .breadcrumb a { color: #d70018; text-decoration: none; font-weight: 500; }
    
    /* Main Content */
    .article-main { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
    .article-title { font-size: 28px; font-weight: 800; line-height: 1.4; color: #111827; margin-bottom: 15px; }
    
    /* Meta Info */
    .article-meta { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 20px; }
    .meta-left { display: flex; align-items: center; gap: 10px; }
    .author-avatar { width: 36px; height: 36px; border-radius: 50%; background: #d70018; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; }
    .meta-info-text .author-name { font-weight: 600; color: #374151; font-size: 14px; }
    .meta-info-text .post-date { font-size: 12px; color: #6b7280; }
    .meta-right { display: flex; gap: 10px; }
    .share-btn { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; transition: 0.2s; }
    .share-fb { background: #1877f2; }
    .share-fb:hover { background: #166fe5; }
    .share-link { background: #6b7280; }
    
    /* Summary */
    .article-summary { background: #f3f4f6; border-left: 4px solid #d70018; padding: 15px 20px; border-radius: 0 8px 8px 0; font-weight: 600; color: #374151; line-height: 1.6; margin-bottom: 25px; font-size: 15px; }
    
    /* Content Styling */
    .article-content { font-size: 16px; line-height: 1.8; color: #333; }
    .article-content p { margin-bottom: 20px; }
    .article-content h2 { font-size: 22px; font-weight: 700; color: #111827; margin: 30px 0 15px 0; }
    .article-content h3 { font-size: 18px; font-weight: 700; color: #111827; margin: 25px 0 15px 0; }
    .article-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 15px auto; display: block; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .article-content ul { list-style-type: disc; margin-left: 20px; margin-bottom: 20px; }
    .article-content li { margin-bottom: 10px; }

    /* Ecosystem Banner */
    .ecosystem-banner { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 12px; padding: 25px; color: #fff; margin-top: 40px; position: relative; overflow: hidden; }
    .ecosystem-title { font-size: 20px; font-weight: 800; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
    .ecosystem-btn { display: inline-block; background: #fff; color: #1e3a8a; font-weight: 700; padding: 10px 20px; border-radius: 20px; margin-top: 15px; text-decoration: none; transition: 0.3s; font-size: 14px; }
    .ecosystem-btn:hover { background: #f3f4f6; transform: translateY(-2px); }

    /* Sidebar */
    .article-sidebar { display: flex; flex-direction: column; gap: 20px; }
    .sidebar-widget { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
    .widget-title { font-size: 16px; font-weight: 800; text-transform: uppercase; color: #111827; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .widget-title::before { content: ''; display: block; width: 4px; height: 16px; background: #d70018; border-radius: 2px; }
    
    .sidebar-post { display: flex; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f3f4f6; text-decoration: none; transition: 0.2s; }
    .sidebar-post:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .sidebar-post:hover .sp-title { color: #d70018; }
    .sp-img { width: 100px; height: 65px; border-radius: 6px; object-fit: cover; flex-shrink: 0; }
    .sp-title { font-size: 14px; font-weight: 600; color: #374151; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin: 0; }

</style>
@endpush

@section('content')
<div class="article-container">
    
    <!-- CỘT TRÁI: NỘI DUNG CHÍNH -->
    <div class="article-main">
        
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="/Home"><i class="fa-solid fa-house"></i></a>
            <i class="fa-solid fa-angle-right" style="font-size:10px;"></i>
            <a href="{{ route('articles.index') }}">Tin tức</a>
            <i class="fa-solid fa-angle-right" style="font-size:10px;"></i>
            <span class="truncate max-w-xs">{{ $article->title }}</span>
        </div>

        <h1 class="article-title">{{ $article->title }}</h1>

        <div class="article-meta">
            <div class="meta-left">
                <div class="author-avatar">
                    {{ substr($article->author->full_name ?? 'A', 0, 1) }}
                </div>
                <div class="meta-info-text">
                    <div class="author-name">{{ $article->author->full_name ?? 'Ban biên tập Sforum' }}</div>
                    <div class="post-date">{{ $article->created_at->format('d/m/Y - H:i') }}</div>
                </div>
            </div>
            <div class="meta-right">
                <a href="#" class="share-btn share-fb"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="#" class="share-btn share-link"><i class="fa-solid fa-link"></i></a>
            </div>
        </div>

        @if($article->summary)
            <div class="article-summary">
                {{ $article->summary }}
            </div>
        @endif

        <!-- Nội dung -->
        <div class="article-content">
            {!! $article->content !!}
        </div>

        <!-- Ecosystem Box -->
        @if($relatedTicket)
        <div class="ecosystem-banner">
            <div class="ecosystem-title">
                <i class="fa-solid fa-screwdriver-wrench"></i>
                Nhật ký Hồi sinh Thiết bị
            </div>
            <p style="font-size: 14px; line-height: 1.6; opacity: 0.9;">Bài viết này nằm trong chuỗi series "Right to Repair" thực tế từ hệ thống DIENMAY PRO. Chuyên gia của chúng tôi đã trực tiếp xử lý trường hợp mã <strong>#{{ $relatedTicket->ticket_id }}</strong>.</p>
            <a href="#" class="ecosystem-btn">Tra cứu đơn sửa chữa này</a>
            <i class="fa-solid fa-gears absolute text-white opacity-10 text-8xl -right-5 -bottom-5"></i>
        </div>
        @endif

        <!-- Tags -->
        <div style="margin-top: 30px; display: flex; gap: 10px; align-items: center;">
            <i class="fa-solid fa-tags text-gray-400"></i>
            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase">{{ $article->format_type }}</span>
            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs font-bold rounded-full uppercase">Công nghệ</span>
        </div>

    </div>

    <!-- CỘT PHẢI: SIDEBAR TẠP CHÍ -->
    <div class="article-sidebar">
        
        <div class="sidebar-widget">
            <div class="widget-title">Tin mới nhất</div>
            <div>
                @foreach($recentArticles as $recent)
                <a href="{{ route('articles.show', $recent->slug) }}" class="sidebar-post">
                    <img src="{{ $recent->thumbnail ?? 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=150' }}" alt="{{ $recent->title }}" class="sp-img">
                    <h4 class="sp-title">{{ $recent->title }}</h4>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Banner Quảng Cáo Demo -->
        <div class="sidebar-widget" style="padding: 0; overflow: hidden; background: transparent; box-shadow: none;">
            <img src="https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=400" alt="Banner Quản cáo" style="width: 100%; border-radius: 12px;">
        </div>

    </div>

</div>
@endsection
