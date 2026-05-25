@extends('admin.layouts.master')

@section('title', 'Quản lý video')

@push('styles')
<style>
    .video-card-shell {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .admin-upload-row {
        background: linear-gradient(135deg, rgba(0,70,171,.06), rgba(0,97,242,.03));
    }

    .admin-upload-row td:first-child {
        border-left: 4px solid #0046ab;
    }

    .filter-panel {
        background: linear-gradient(135deg, #f8fbff 0%, #ffffff 100%);
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 18px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
    }

    .filter-chip-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        border-radius: 999px;
        border: 1px solid #dbe4f0;
        background: #fff;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
        transition: .2s ease;
        text-decoration: none;
        box-shadow: 0 6px 18px rgba(15,23,42,.04);
    }

    .filter-chip:hover {
        transform: translateY(-1px);
        border-color: #93c5fd;
        color: #0046ab;
        box-shadow: 0 10px 22px rgba(0,70,171,.08);
    }

    .filter-chip.active {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        box-shadow: 0 12px 24px rgba(0,70,171,.18);
    }

    .filter-chip .chip-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 7px;
        border-radius: 999px;
        background: rgba(255,255,255,.22);
        font-size: 11px;
        font-weight: 800;
    }

    .video-thumb {
        width: 96px;
        height: 60px;
        object-fit: cover;
        border-radius: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
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
    .status-deleted {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
        border: 1px solid #ef444433;
    }
</style>
@endpush

@section('content')
<div class="video-card-shell">
    <div class="p-5 border-bottom border-slate-200 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Quản lý video</h1>
            <div class="text-slate-500">Duyệt, ẩn hoặc xóa video do quản trị viên đăng lên hệ thống</div>
        </div>
    </div>

    <div class="p-5 border-bottom border-slate-200">
        <div class="filter-panel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <div class="text-uppercase fw-bold text-primary small mb-1">Bộ lọc nhanh</div>
                    <div class="text-slate-500 small">Chuyển trạng thái video bằng một chạm</div>
                </div>
                <a href="{{ route('admin.videos.create') }}" class="btn btn-primary rounded-pill px-3">
                    <i class="fa-solid fa-circle-plus me-1"></i> Admin upload
                </a>
            </div>

            <div class="filter-chip-group mb-3">
                <a href="{{ route('admin.videos.index', request()->except('status')) }}" class="filter-chip {{ !request('status') ? 'active' : '' }}">
                    <i class="fa-solid fa-layer-group"></i> Tất cả
                    <span class="chip-count">{{ $videos->total() }}</span>
                </a>
                <a href="{{ route('admin.videos.index', array_merge(request()->except('status'), ['status' => 'admin_upload'])) }}" class="filter-chip {{ request('status') === 'admin_upload' ? 'active' : '' }}" style="background: linear-gradient(135deg, #1d4ed8, #2563eb); color: #fff; border-color: transparent; box-shadow: 0 14px 28px rgba(37,99,235,.24);">
                    <i class="fa-solid fa-user-shield"></i> Admin upload
                    <span class="chip-count" style="background: rgba(255,255,255,.22);">{{ $adminUploadCount ?? 0 }}</span>
                </a>
                <a href="{{ route('admin.videos.index', array_merge(request()->except('status'), ['status' => 'pending'])) }}" class="filter-chip {{ request('status') === 'pending' ? 'active' : '' }}">
                    <i class="fa-solid fa-clock"></i> Pending
                    <span class="chip-count">{{ $pendingCount ?? 0 }}</span>
                </a>
                <a href="{{ route('admin.videos.index', array_merge(request()->except('status'), ['status' => 'published'])) }}" class="filter-chip {{ request('status') === 'published' ? 'active' : '' }}">
                    <i class="fa-solid fa-circle-check"></i> Published
                    <span class="chip-count">{{ $publishedCount ?? 0 }}</span>
                </a>
                <a href="{{ route('admin.videos.index', array_merge(request()->except('status'), ['status' => 'hidden'])) }}" class="filter-chip {{ request('status') === 'hidden' ? 'active' : '' }}">
                    <i class="fa-solid fa-eye-slash"></i> Hidden
                    <span class="chip-count">{{ $hiddenCount ?? 0 }}</span>
                </a>
            </div>

            <form method="GET" action="{{ route('admin.videos.index') }}" class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-lg" placeholder="Tìm theo tiêu đề hoặc mô tả...">
                </div>
                <div class="col-md-4 d-grid">
                    <button class="btn btn-outline-primary btn-lg">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Video</th>
                    <th>Tiêu đề</th>
                    <th>Người đăng</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th class="text-end">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($videos as $video)
                    <tr class="{{ $video->uploaded_by_admin ? 'admin-upload-row' : '' }}">
                        <td>
                            @if($video->thumbnail_path)
                                <img src="{{ asset('storage/' . $video->thumbnail_path) }}" alt="thumbnail" class="video-thumb">
                            @else
                                <div class="video-thumb d-flex align-items-center justify-content-center text-slate-400">
                                    <i class="fa-regular fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold text-slate-900 mb-1">{{ $video->title }}</div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-1">
                                @if($video->uploaded_by_admin)
                                    <span class="badge rounded-pill text-bg-primary">
                                        <i class="fa-solid fa-user-shield me-1"></i> Admin upload
                                    </span>
                                @endif
                                <span class="badge rounded-pill text-bg-light text-slate-600 border">ID #{{ $video->id }}</span>
                            </div>
                            <div class="text-slate-500 small text-truncate" style="max-width: 360px;">
                                {{ $video->description ?? 'Không có mô tả' }}
                            </div>
                        </td>
                        <td>{{ $video->user->name ?? 'N/A' }}</td>
                        <td>
                            <span class="status-pill status-{{ $video->status }}">{{ $video->status }}</span>
                            @if($video->uploaded_by_admin)
                                <div class="mt-2">
                                    <span class="badge rounded-pill text-bg-primary px-2 py-1">
                                        <i class="fa-solid fa-shield-halved me-1"></i> Admin upload
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>{{ optional($video->created_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.videos.show', $video) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fa-regular fa-eye"></i>
                                </a>

                                @if($video->status !== 'published')
                                    <form action="{{ route('admin.videos.approve', $video) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-sm">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.videos.hide', $video) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="admin_note" value="Vi phạm chính sách">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="fa-solid fa-eye-slash"></i>
                                    </button>
                                </form>

                                <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa video này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-slate-500">Chưa có video nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4">
        {{ $videos->links() }}
    </div>
</div>
@endsection
