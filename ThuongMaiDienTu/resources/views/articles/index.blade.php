@extends('layouts.app')

@section('title', '24h Công Nghệ - DIENMAY PRO')

@push('styles')
<style>
    /* Tổng quan */
    body { background-color: #f8f9fa; }
    .news-container { max-width: 1200px; margin: 0 auto; padding: 20px 15px; }
    .section-title { font-size: 24px; font-weight: 800; text-transform: uppercase; color: #111827; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .section-title::before { content: ''; display: block; width: 4px; height: 24px; background: #d70018; border-radius: 4px; }

    /* Tags/Menu ngang */
    .news-tags { display: flex; gap: 10px; overflow-x: auto; margin-bottom: 25px; padding-bottom: 5px; }
    .news-tag { padding: 8px 16px; border-radius: 20px; background: #fff; border: 1px solid #e5e7eb; font-size: 13px; font-weight: 600; color: #4b5563; white-space: nowrap; transition: 0.2s; cursor: pointer; }
    .news-tag.active, .news-tag:hover { background: #d70018; color: #fff; border-color: #d70018; }

    /* Featured Top Section (Tạp chí) */
    .featured-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 40px; }
    @media (max-width: 768px) { .featured-grid { grid-template-columns: 1fr; } }
    
    .featured-card { position: relative; border-radius: 16px; overflow: hidden; background: #000; display: block; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .featured-card img { width: 100%; height: 100%; object-fit: cover; opacity: 0.8; transition: transform 0.5s; }
    .featured-card:hover img { transform: scale(1.05); opacity: 0.6; }
    
    .featured-content { position: absolute; bottom: 0; left: 0; right: 0; padding: 25px; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 100%); color: #fff; }
    .featured-badge { display: inline-block; padding: 4px 10px; background: #d70018; color: #fff; font-size: 11px; font-weight: 700; border-radius: 4px; margin-bottom: 10px; text-transform: uppercase; }
    .featured-title { font-size: 22px; font-weight: 800; line-height: 1.4; margin-bottom: 10px; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); }
    .featured-meta { font-size: 12px; color: #d1d5db; display: flex; align-items: center; gap: 15px; font-weight: 500; }
    
    .featured-main { height: 450px; }
    .featured-main .featured-title { font-size: 28px; }
    
    .featured-side { display: grid; grid-template-rows: 1fr 1fr; gap: 20px; }
    .featured-sub { height: 215px; }
    .featured-sub .featured-title { font-size: 16px; margin-bottom: 5px; }
    .featured-sub .featured-content { padding: 15px; }

    /* Latest News Grid */
    .latest-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    @media (max-width: 1024px) { .latest-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .latest-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .latest-grid { grid-template-columns: 1fr; } }
    
    .news-item { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.04); display: flex; flex-direction: column; transition: 0.3s; border: 1px solid transparent; }
    .news-item:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); border-color: #fca5a5; }
    .news-item-img { position: relative; padding: 10px 10px 0 10px; }
    .news-item-img img { width: 100%; aspect-ratio: 16/10; object-fit: cover; border-radius: 8px; }
    
    .news-item-content { padding: 15px; display: flex; flex-direction: column; flex: 1; }
    .news-item-title { font-size: 15px; font-weight: 700; color: #1f2937; line-height: 1.5; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .news-item:hover .news-item-title { color: #d70018; }
    
    .news-item-meta { display: flex; justify-content: space-between; align-items: center; margin-top: auto; font-size: 12px; color: #6b7280; font-weight: 500; }
    .author-info { display: flex; align-items: center; gap: 6px; }
    .author-info img { width: 20px; height: 20px; border-radius: 50%; }

</style>
@endpush

@section('content')
<div class="news-container">
    
    <div class="news-tags">
        <div class="news-tag active">Tất cả</div>
        <div class="news-tag">Đánh giá sản phẩm</div>
        <div class="news-tag">Mẹo vặt & Thủ thuật</div>
        <div class="news-tag">Sự kiện công nghệ</div>
        <div class="news-tag">Khuyến mãi</div>
        <div class="news-tag">Nhật ký Hồi sinh</div>
    </div>

    @if($featuredArticles->count() > 0)
    <div class="featured-grid">
        <!-- Bài lớn bên trái -->
        <a href="{{ route('articles.show', $featuredArticles[0]->slug) }}" class="featured-card featured-main">
            <img src="{{ $featuredArticles[0]->thumbnail ?? 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?w=800' }}" alt="{{ $featuredArticles[0]->title }}">
            <div class="featured-content">
                <span class="featured-badge">{{ $featuredArticles[0]->format_type }}</span>
                <h2 class="featured-title">{{ $featuredArticles[0]->title }}</h2>
                <div class="featured-meta">
                    <span><i class="fa-regular fa-clock"></i> {{ $featuredArticles[0]->created_at->diffForHumans() }}</span>
                    <span><i class="fa-regular fa-user"></i> {{ $featuredArticles[0]->author->full_name ?? 'Admin' }}</span>
                </div>
            </div>
        </a>

        <!-- 2 Bài nhỏ bên phải -->
        @if($featuredArticles->count() > 1)
        <div class="featured-side">
            @foreach($featuredArticles->skip(1)->take(2) as $article)
            <a href="{{ route('articles.show', $article->slug) }}" class="featured-card featured-sub">
                <img src="{{ $article->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400' }}" alt="{{ $article->title }}">
                <div class="featured-content">
                    <span class="featured-badge" style="background:#2563eb;">Mới Nhất</span>
                    <h3 class="featured-title">{{ $article->title }}</h3>
                    <div class="featured-meta">
                        <span>{{ $article->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    <h2 class="section-title">BÀI VIẾT MỚI NHẤT</h2>
    
    <div class="latest-grid">
        @foreach($latestArticles as $article)
            <a href="{{ route('articles.show', $article->slug) }}" class="news-item">
                <div class="news-item-img">
                    <img src="{{ $article->thumbnail ?? 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=400' }}" alt="{{ $article->title }}">
                </div>
                <div class="news-item-content">
                    <h3 class="news-item-title">{{ $article->title }}</h3>
                    <div class="news-item-meta">
                        <div class="author-info">
                            <i class="fa-solid fa-circle-user text-gray-400 text-lg"></i>
                            <span>{{ substr($article->author->full_name ?? 'Admin', 0, 15) }}</span>
                        </div>
                        <span>{{ $article->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div style="margin-top: 40px; display:flex; justify-content:center;">
        {{ $latestArticles->links() }}
    </div>

</div>
@endsection
