@extends('layouts.app')

@section('title', 'Video của tôi')

@push('styles')
<style>
    .video-list-page {
        padding: 44px 0 64px;
    }

    .video-list-shell {
        max-width: 1180px;
        margin: 0 auto;
    }

    .video-list-hero {
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        color: #fff;
        border-radius: 22px;
        padding: 26px 28px;
        box-shadow: var(--shadow-premium);
        margin-bottom: 22px;
    }

    .video-list-hero h1 {
        font-size: 26px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .video-list-hero p {
        font-size: 14px;
        opacity: .92;
    }

    .video-filter-bar,
    .video-table-card {
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 18px;
        box-shadow: var(--shadow-premium);
        overflow: hidden;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: capitalize;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.35);
    }
    .status-pending {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        color: #92400e;
        border: 1px solid #f59e0b33;
    }
    .status-published {
        background: linear-gradient(135deg, #dcfce7, #bbf7d0);
        color: #166534;
        border: 1px solid #22c55e33;
    }
    .status-hidden {
        background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
        color: #334155;
        border: 1px solid #94a3b833;
    }

    .video-thumb {
        width: 110px;
        height: 70px;
        border-radius: 12px;
        object-fit: cover;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
    }

    .empty-state {
        padding: 50px 24px;
        text-align: center;
        color: #64748b;
    }

    @media (max-width: 768px) {
        .video-list-page { padding: 18px 0 34px; }
        .video-list-hero { padding: 20px; border-radius: 18px; }
        .video-list-hero h1 { font-size: 22px; }
    }
</style>
@endpush

@section('content')
<div class="video-list-page">
    <div class="video-list-shell container">
        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        <div class="video-list-hero">
            <h1>Video của tôi</h1>
            <p>Theo dõi trạng thái video đã tải lên: <strong>pending</strong>, <strong>published</strong>, <strong>hidden</strong>.</p>
        </div>

        <div class="video-filter-bar p-4 mb-4">
            <form method="GET" action="{{ route('videos.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                        <option value="published" @selected(request('status') === 'published')>Published</option>
                        <option value="hidden" @selected(request('status') === 'hidden')>Hidden</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('videos.create') }}" class="btn btn-primary w-100">
                        <i class="fa-solid fa-circle-plus me-1"></i> Upload video mới
                    </a>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-outline-secondary w-100" type="submit">
                        <i class="fa-solid fa-filter me-1"></i> Lọc
                    </button>
                </div>
            </form>
        </div>

        <div class="video-table-card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Video</th>
                            <th>Tiêu đề</th>
                            <th>Mô tả</th>
                            <th>Trạng thái</th>
                            <th>Ngày tải lên</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($videos as $video)
                            <tr>
                                <td>
                                    @if($video->thumbnail_path)
                                        <img src="{{ asset('storage/' . $video->thumbnail_path) }}" alt="thumb" class="video-thumb">
                                    @else
                                        <div class="video-thumb d-flex align-items-center justify-content-center text-slate-400">
                                            <i class="fa-regular fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $video->title }}</div>
                                    <div class="text-muted small">{{ number_format(($video->file_size ?? 0) / 1024 / 1024, 2) }} MB</div>
                                </td>
                                <td class="text-muted" style="max-width: 320px;">{{ $video->description ?: 'Không có mô tả' }}</td>
                                <td>
                                    <span class="status-pill status-{{ $video->status }}">
                                        <i class="fa-solid fa-circle"></i> {{ $video->status }}
                                    </span>
                                </td>
                                <td>{{ optional($video->created_at)->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-video fa-2x mb-3 text-primary"></i>
                                        <h3 class="h5 fw-bold text-slate-900">Bạn chưa có video nào</h3>
                                        <p class="mb-3">Hãy upload video đầu tiên để bắt đầu.</p>
                                        <a href="{{ route('videos.create') }}" class="btn btn-primary">
                                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Upload video
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 p-md-4">
                {{ $videos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
