@extends('admin.layouts.master')
@section('title', 'Quản lý Bài viết & Nội dung')
@section('page-title', 'Quản lý Bài viết & Nội dung')

@section('content')
@php
    $stats = [
        'total' => $articles->total(),
        'approved' => $articles->where('status', 'approved')->count(),
        'pending' => $articles->where('status', 'pending')->count(),
        'rejected' => $articles->where('status', 'rejected')->count(),
    ];
@endphp

<div class="space-y-6">
    <style>
        .glass-card { background: rgba(255,255,255,.88); backdrop-filter: blur(18px); border: 1px solid rgba(255,255,255,.65); box-shadow: 0 20px 60px -28px rgba(15, 23, 42, .25); }
        .stat-card { transition: .25s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 18px 40px -28px rgba(37,99,235,.35); }
        .article-card { transition: .25s ease; }
        .article-card:hover { transform: translateY(-3px); }
        .filter-chip.active { background: #2563eb; color: #fff; border-color: #2563eb; }
        .custom-pagination nav p, .custom-pagination nav > div:first-child { display: none !important; }
        .custom-pagination svg { width: 18px; height: 18px; }
    </style>

    <div class="rounded-[2rem] overflow-hidden bg-gradient-to-br from-slate-950 via-blue-950 to-indigo-950 text-white shadow-2xl">
        <div class="p-6 md:p-8 flex flex-col xl:flex-row xl:items-end justify-between gap-6">
            <div class="max-w-3xl space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-[11px] font-bold tracking-[0.3em] uppercase border border-white/10">Article Studio</div>
                <div>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight">Quản lý bài viết hiện đại</h1>
                    <p class="mt-3 text-slate-300 max-w-2xl leading-relaxed">Theo dõi bài đăng theo trạng thái, lọc nhanh theo tác giả/format và tạo nội dung mới với trải nghiệm xem trước trực quan.</p>
                </div>
            </div>
            <a href="{{ route('admin.articles.create') }}" class="inline-flex items-center gap-3 px-5 py-3 rounded-2xl bg-white text-slate-900 font-black shadow-lg shadow-black/10 hover:-translate-y-0.5 transition">
                <i class="fa-solid fa-pen-to-square"></i>
                Tạo bài viết
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="glass-card stat-card rounded-[1.75rem] p-5">
            <div class="text-[11px] font-black uppercase tracking-[0.3em] text-slate-400">Tổng bài viết</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ number_format($stats['total']) }}</div>
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

    <div class="glass-card rounded-[2rem] p-5 md:p-6">
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

        <div class="flex flex-wrap gap-3 mb-5">
            @php
                $filters = [
                    '' => 'Tất cả',
                    'pending' => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    'rejected' => 'Từ chối',
                ];
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.articles.index', array_filter(array_merge(request()->except('page'), ['status' => $key]), fn($v) => $v !== '' && $v !== null)) }}" class="filter-chip inline-flex items-center px-4 py-2 rounded-full border text-sm font-bold transition {{ request('status') === $key || (!request('status') && $key === '') ? 'active' : 'bg-white text-slate-600 border-slate-200 hover:border-blue-300 hover:text-blue-600' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="overflow-x-auto rounded-[1.75rem] border border-slate-100 bg-white">
            <table class="min-w-full">
                <thead class="bg-slate-50/80 text-[11px] uppercase tracking-[0.25em] text-slate-400">
                    <tr>
                        <th class="px-6 py-4 text-left">Nội dung</th>
                        <th class="px-6 py-4 text-left">Tác giả</th>
                        <th class="px-6 py-4 text-center">Format</th>
                        <th class="px-6 py-4 text-left">Trạng thái</th>
                        <th class="px-6 py-4 text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($articles as $article)
                        <tr class="article-card hover:bg-blue-50/40">
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
                            <td class="px-6 py-5">
                                <div class="flex flex-col gap-1">
                                    <span class="font-bold text-slate-900">{{ $article->author->full_name ?? 'N/A' }}</span>
                                    <span class="inline-flex w-fit px-2 py-1 rounded-full text-[10px] font-black uppercase tracking-[0.2em] {{ $article->author_type === 'admin' ? 'bg-indigo-50 text-indigo-600' : 'bg-emerald-50 text-emerald-600' }}">{{ $article->author_type === 'admin' ? 'Admin' : 'Customer' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-bold uppercase">{{ $article->format_type }}</span>
                            </td>
                            <td class="px-6 py-5">
                                @if($article->status === 'approved')
                                    <span class="inline-flex items-center gap-2 text-emerald-600 font-black uppercase text-xs tracking-[0.2em]"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>Đã duyệt</span>
                                @elseif($article->status === 'pending')
                                    <span class="inline-flex items-center gap-2 text-amber-600 font-black uppercase text-xs tracking-[0.2em]"><span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>Chờ duyệt</span>
                                @else
                                    <span class="inline-flex items-center gap-2 text-rose-600 font-black uppercase text-xs tracking-[0.2em]"><span class="w-2 h-2 rounded-full bg-rose-500"></span>Từ chối</span>
                                @endif
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="{{ route('admin.articles.edit', $article->article_id) }}" class="w-10 h-10 inline-flex items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" title="Chỉnh sửa"><i class="fa-solid fa-pen-nib"></i></a>
                                    <a href="{{ route('articles.show', $article->slug) }}" target="_blank" class="w-10 h-10 inline-flex items-center justify-center rounded-2xl bg-slate-100 text-slate-600 hover:bg-slate-900 hover:text-white transition" title="Xem trước"><i class="fa-solid fa-eye"></i></a>
                                    <form action="{{ route('admin.articles.destroy', $article->article_id) }}" method="POST" onsubmit="return confirm('Xóa bài viết này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-10 h-10 inline-flex items-center justify-center rounded-2xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                <i class="fa-regular fa-newspaper text-4xl text-slate-300"></i>
                                <div class="mt-4 font-bold">Chưa có bài viết nào</div>
                                <div class="text-sm mt-1">Bắt đầu bằng cách tạo bài viết đầu tiên của bạn.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-500">
            <div class="font-semibold">Hiển thị {{ $articles->count() }} / {{ $articles->total() }} bài viết</div>
            <div class="custom-pagination">{{ $articles->withQueryString()->links() }}</div>
        </div>
    </div>
</div>
@endsection