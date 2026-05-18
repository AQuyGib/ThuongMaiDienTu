@extends('layouts.app')

@section('title', 'Lifestyle - DIENMAY PRO')

@push('styles')
<style>
    body { background: #f4f7fb; }
    .lifestyle-shell { max-width: 1280px; margin: 0 auto; padding: 24px 16px 56px; }
    .hero { background: linear-gradient(135deg, #0f172a 0%, #0b4fd6 55%, #7c3aed 100%); color: #fff; border-radius: 32px; overflow: hidden; box-shadow: 0 30px 80px -35px rgba(15,23,42,.45); }
    .hero-grid { display: grid; grid-template-columns: 1.2fr .8fr; gap: 24px; align-items: center; }
    @media (max-width: 900px) { .hero-grid { grid-template-columns: 1fr; } }
    .pill { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px; background: rgba(255,255,255,.12); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,.14); font-size: 12px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
    .featured-main { position: relative; border-radius: 28px; overflow: hidden; min-height: 520px; background: #0f172a; }
    .featured-side { display: grid; gap: 16px; }
    .card { background: rgba(255,255,255,.82); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,.7); box-shadow: 0 20px 50px -30px rgba(15,23,42,.2); transition: .25s ease; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 26px 60px -30px rgba(37,99,235,.25); }
    .article-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
    @media (max-width: 1100px) { .article-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 768px) { .article-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px) { .article-grid { grid-template-columns: 1fr; } }
    .article-item { overflow: hidden; border-radius: 24px; background: #fff; border: 1px solid #e2e8f0; transition: .25s ease; }
    .article-item:hover { transform: translateY(-4px); box-shadow: 0 18px 40px -28px rgba(15,23,42,.28); }
    .tag { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border-radius: 999px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .18em; }
    .tag-primary { background: #dbeafe; color: #1d4ed8; }
    .tag-warning { background: #fef3c7; color: #b45309; }
    .tag-success { background: #dcfce7; color: #15803d; }
    .tag-filter.active { background: #0f172a; color: #fff; }
</style>
@endpush

@section('content')
@php
    $activeTag = request('tag');
    $tagFilters = [
        '' => 'Tất cả',
        'standard' => 'Standard',
        'lifestyle' => 'Lifestyle',
        'dienmay-pro' => 'DIENMAY PRO',
    ];
@endphp
<div class="lifestyle-shell space-y-6">
    <section class="hero">
        <div class="p-6 md:p-10 hero-grid">
            <div class="space-y-6">
                <div class="pill"><i class="fa-solid fa-wand-magic-sparkles"></i> Lifestyle / Articles</div>
                <div>
                    <h1 class="text-3xl md:text-5xl font-black tracking-tight leading-tight">Khám phá bài viết, mẹo vặt và câu chuyện công nghệ</h1>
                    <p class="mt-4 text-slate-200 max-w-2xl leading-relaxed">Trang lifestyle được thiết kế như một tạp chí số: nổi bật bài mới nhất, phân tầng nội dung rõ ràng và giúp người đọc đi từ cảm hứng đến hành động chỉ trong vài thao tác.</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('articles.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white text-slate-900 font-black hover:-translate-y-0.5 transition">
                        <i class="fa-solid fa-pen-to-square"></i> Viết bài mới
                    </a>
                    <a href="#latest-section" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white/10 border border-white/15 text-white font-black hover:bg-white/15 transition">
                        <i class="fa-solid fa-arrow-down"></i> Xem bài mới nhất
                    </a>
                </div>
            </div>
            <div class="featured-side">
                @if($featuredArticles->count() > 0)
                    <a href="{{ route('articles.show', $featuredArticles[0]->slug) }}" class="featured-main block">
                        <img src="{{ $featuredArticles[0]->thumbnail ? asset($featuredArticles[0]->thumbnail) : 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=1200' }}" class="absolute inset-0 w-full h-full object-cover" alt="{{ $featuredArticles[0]->title }}">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/35 to-transparent"></div>
                        <div class="absolute inset-x-0 bottom-0 p-6 md:p-8">
                            <span class="tag tag-warning">Featured</span>
                            <h2 class="mt-4 text-2xl md:text-4xl font-black leading-tight">{{ $featuredArticles[0]->title }}</h2>
                            <p class="mt-3 text-slate-200 line-clamp-3">{{ $featuredArticles[0]->summary }}</p>
                        </div>
                    </a>
                    @if($featuredArticles->count() > 1)
                        <div class="grid sm:grid-cols-2 gap-4">
                            @foreach($featuredArticles->skip(1)->take(2) as $article)
                                <a href="{{ route('articles.show', $article->slug) }}" class="card rounded-[24px] overflow-hidden block">
                                    <img src="{{ $article->thumbnail ? asset($article->thumbnail) : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800' }}" class="w-full h-44 object-cover" alt="{{ $article->title }}">
                                    <div class="p-4">
                                        <span class="tag tag-primary">Mới nhất</span>
                                        <h3 class="mt-3 font-black text-slate-900 line-clamp-2">{{ $article->title }}</h3>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex flex-col gap-4">
            <div class="flex items-end justify-between gap-4 flex-wrap">
                <div>
                    <div class="text-[11px] font-black uppercase tracking-[0.35em] text-slate-400">Tags</div>
                    <h2 class="text-2xl md:text-3xl font-black text-slate-900 mt-2">Lọc bài viết theo tag</h2>
                </div>
                <div class="text-sm text-slate-500 font-medium hidden md:block">Chọn tag để lọc đúng nhóm nội dung bạn quan tâm.</div>
            </div>
            <div class="flex flex-wrap gap-3">
                @foreach($tagFilters as $key => $label)
                    <a href="{{ route('articles.index', array_filter(['tag' => $key], fn($v) => $v !== '' && $v !== null)) }}" class="tag tag-filter {{ $activeTag === $key || (!$activeTag && $key === '') ? 'active' : 'tag-primary' }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="latest-section" class="space-y-4">
        <div class="flex items-end justify-between gap-4">
            <div>
                <div class="text-[11px] font-black uppercase tracking-[0.35em] text-slate-400">New stories</div>
                <h2 class="text-2xl md:text-3xl font-black text-slate-900 mt-2">Bài viết mới nhất</h2>
            </div>
            <div class="text-sm text-slate-500 font-medium hidden md:block">Bài viết đã duyệt và bài của bạn sẽ được hiển thị tại đây.</div>
        </div>

        <div class="article-grid">
            @foreach($latestArticles as $article)
                <article class="article-item">
                    <a href="{{ route('articles.show', $article->slug) }}" class="block">
                        <div class="relative">
                            <img src="{{ $article->thumbnail ? asset($article->thumbnail) : 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=800' }}" class="w-full aspect-[4/3] object-cover" alt="{{ $article->title }}">
                            <div class="absolute top-4 left-4 flex gap-2">
                                <span class="tag tag-primary">{{ $article->format_type }}</span>
                                @if($article->status === 'pending')
                                    <span class="tag tag-warning">Chờ duyệt</span>
                                @endif
                            </div>
                        </div>
                        <div class="p-4 space-y-3">
                            <h3 class="font-black text-slate-900 leading-snug line-clamp-2">{{ $article->title }}</h3>
                            <p class="text-sm text-slate-500 line-clamp-3">{{ $article->summary }}</p>
                            <div class="flex items-center justify-between text-xs text-slate-500 pt-2 border-t border-slate-100">
                                <span class="inline-flex items-center gap-2"><i class="fa-regular fa-user"></i> {{ $article->author->full_name ?? 'Khách hàng' }}</span>
                                <span>{{ $article->created_at->format('d/m/Y') }}</span>
                            </div>
                            @auth
                                @if($article->author_id === Auth::id() && $article->author_type === 'customer')
                                    <div class="flex gap-2 pt-2">
                                        <a href="{{ route('articles.edit', $article->article_id) }}" class="flex-1 text-center px-3 py-2 rounded-xl bg-blue-50 text-blue-700 text-xs font-black uppercase tracking-[0.2em]">Sửa</a>
                                        <form action="{{ route('articles.destroy', $article->article_id) }}" method="POST" class="flex-1" onsubmit="return confirm('Bạn có chắc muốn rút lại bài viết này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="w-full px-3 py-2 rounded-xl bg-rose-50 text-rose-600 text-xs font-black uppercase tracking-[0.2em]">Xóa</button>
                                        </form>
                                    </div>
                                @endif
                            @endauth
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

        <div class="pt-4 flex justify-center">
            {{ $latestArticles->links() }}
        </div>
    </section>
</div>
@endsection