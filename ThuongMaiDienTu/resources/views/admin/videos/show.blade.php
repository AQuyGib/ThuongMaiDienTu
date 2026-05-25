@extends('admin.layouts.master')

@section('title', 'Chi tiết video')

@push('styles')
<style>
    .video-detail-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .video-detail-header {
        padding: 22px 24px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, rgba(0,70,171,.04), rgba(0,97,242,.03));
    }

    .video-player {
        width: 100%;
        max-height: 520px;
        background: #000;
        border-radius: 16px;
        overflow: hidden;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1.3fr .7fr;
        gap: 24px;
    }

    .detail-meta {
        display: grid;
        gap: 12px;
    }

    .detail-meta-item {
        padding: 14px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fafcff;
        position: relative;
        overflow: hidden;
    }

    .detail-meta-item.admin-upload {
        background: linear-gradient(135deg, rgba(0,70,171,.06), rgba(0,97,242,.04));
        border-color: rgba(0,70,171,.18);
    }

    .meta-mini-label {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 8px;
        border-radius: 999px;
        background: rgba(0,70,171,.1);
        color: #0046ab;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 8px;
    }

    .detail-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: .05em;
    }

    .detail-value {
        margin-top: 4px;
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        word-break: break-word;
    }

    @media (max-width: 992px) {
        .detail-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="video-detail-card">
    <div class="video-detail-header d-flex justify-content-between align-items-start gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $video->title }}</h1>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                @if($video->uploaded_by_admin)
                    <span class="badge rounded-pill text-bg-primary">
                        <i class="fa-solid fa-user-shield me-1"></i> Admin upload
                    </span>
                @endif
                <span class="badge rounded-pill text-bg-light text-slate-600 border">ID #{{ $video->id }}</span>
            </div>
            <div class="text-slate-500">Chi tiết video và file tải lên</div>
        </div>
        <a href="{{ route('admin.videos.index') }}" class="btn btn-light rounded-3">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="p-6 p-lg-7">
        <div class="detail-grid">
            <div>
                @if($video->video_path)
                    <video class="video-player" controls preload="metadata">
                        <source src="{{ asset('storage/' . $video->video_path) }}">
                        Trình duyệt của bạn không hỗ trợ phát video.
                    </video>
                @endif

                <div class="mt-4">
                    <h2 class="h5 fw-bold mb-2">Mô tả</h2>
                    <div class="text-slate-600 leading-relaxed">
                        {{ $video->description ?: 'Không có mô tả.' }}
                    </div>
                </div>
            </div>

            <div class="detail-meta">
                <div class="detail-meta-item {{ $video->uploaded_by_admin ? 'admin-upload' : '' }}">
                    @if($video->uploaded_by_admin)
                        <div class="meta-mini-label">
                            <i class="fa-solid fa-user-shield"></i> Admin upload
                        </div>
                    @endif
                    <div class="detail-label">Người đăng</div>
                    <div class="detail-value">{{ $video->user->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-label">Trạng thái</div>
                    <div class="detail-value">
                        {{ $video->status }}
                        @if($video->uploaded_by_admin)
                            <div class="mt-2">
                                <span class="badge rounded-pill text-bg-primary px-2 py-1">
                                    <i class="fa-solid fa-shield-halved me-1"></i> Admin upload
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-label">Dung lượng</div>
                    <div class="detail-value">{{ $video->file_size ? number_format($video->file_size / 1024 / 1024, 2) . ' MB' : 'N/A' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-label">Mime type</div>
                    <div class="detail-value">{{ $video->mime_type ?? 'N/A' }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-label">Ngày tạo</div>
                    <div class="detail-value">{{ optional($video->created_at)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="detail-meta-item">
                    <div class="detail-label">Ghi chú admin</div>
                    <div class="detail-value">{{ $video->admin_note ?: 'Chưa có ghi chú.' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
