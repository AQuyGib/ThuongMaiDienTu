@extends('admin.layouts.master')

@section('title', 'Chi tiết video')

@push('styles')
<style>
    .video-detail-card {
        background: #fff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .video-detail-header {
        padding: 24px 32px;
        border-bottom: 1px solid #e2e8f0;
        background: linear-gradient(135deg, rgba(0,70,171,.03), rgba(0,97,242,.01));
    }

    .video-player-container {
        position: relative;
        background: #090d16;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        border: 1px solid #e2e8f0;
    }

    .video-player {
        width: 100%;
        aspect-ratio: 16/9;
        display: block;
        background: #000;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: 1.3fr .7fr;
        gap: 32px;
    }

    .info-section {
        background: #fafcff;
        border: 1px solid #e6f0fa;
        border-radius: 20px;
        padding: 24px;
    }

    .detail-meta {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    @media (max-width: 1200px) {
        .detail-meta {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 992px) {
        .detail-grid { 
            grid-template-columns: 1fr; 
            gap: 24px;
        }
        .detail-meta {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .detail-meta {
            grid-template-columns: 1fr;
        }
    }

    .detail-meta-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        transition: all 0.25s ease;
    }

    .detail-meta-item:hover {
        border-color: #bfdbfe;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.03);
    }

    .meta-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .meta-text-wrapper {
        min-width: 0;
    }

    .detail-label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: .05em;
    }

    .detail-value {
        margin-top: 2px;
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        word-break: break-word;
    }

    .thumbnail-preview-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 16px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.01);
    }

    .thumbnail-img {
        width: 100%;
        aspect-ratio: 16/9;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid #f1f5f9;
    }

    /* Colors and styles for meta fields */
    .bg-light-blue { background: #e0f2fe; color: #0369a1; }
    .bg-light-green { background: #dcfce7; color: #15803d; }
    .bg-light-purple { background: #f3e8ff; color: #7e22ce; }
    .bg-light-orange { background: #ffedd5; color: #c2410c; }
    .bg-light-teal { background: #ccfbf1; color: #0f766e; }
    .bg-light-rose { background: #ffe4e6; color: #be123c; }
    .bg-light-violet { background: #ede9fe; color: #6d28d9; }
    .bg-light-amber { background: #fef3c7; color: #b45309; }

    .btn-premium-primary {
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        color: #fff !important;
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 700;
        box-shadow: 0 8px 16px rgba(0, 70, 171, 0.15);
        transition: all 0.25s ease;
    }

    .btn-premium-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 20px rgba(0, 70, 171, 0.25);
    }

    .btn-premium-light {
        background: #f8fafc;
        color: #475569 !important;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 700;
        transition: all 0.25s ease;
    }

    .btn-premium-light:hover {
        background: #f1f5f9;
        color: #1e293b !important;
    }
</style>
@endpush

@section('content')
<div class="video-detail-card">
    <div class="video-detail-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1 d-flex align-items-center gap-2">
                Chi tiết video
                <span class="badge bg-blue-50 text-primary border border-blue-100 rounded-pill fs-7 py-1 px-2.5">ID #{{ $video->id }}</span>
            </h1>
            <div class="text-slate-500">Xem và quản lý tệp tin đa phương tiện</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.videos.edit', $video) }}" class="btn-premium-primary">
                <i class="fa-solid fa-pen-to-square me-2"></i> Sửa video
            </a>
            <a href="{{ route('admin.videos.index') }}" class="btn-premium-light">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="p-4 p-md-5">
        <div class="detail-grid">
            <!-- Left Side: Player & Content -->
            <div class="d-flex flex-column gap-4">
                <div class="video-player-container">
                    @if($video->video_path)
                        <video class="video-player" controls preload="metadata" poster="{{ $video->thumbnail_url }}">
                            <source src="{{ route('videos.stream', $video) }}">
                            Trình duyệt của bạn không hỗ trợ phát video HTML5.
                        </video>
                    @elseif($video->youtube_url)
                        <iframe class="video-player border-0" src="{{ $video->youtube_url }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    @else
                        <div class="video-player d-flex align-items-center justify-content-center text-slate-400 flex-column gap-2">
                            <i class="fa-solid fa-video-slash fs-1"></i>
                            <span class="fw-semibold">Không tìm thấy nguồn video</span>
                        </div>
                    @endif
                </div>

                <div class="info-section">
                    <div class="mb-3">
                        <span class="badge bg-light-blue py-1.5 px-3 rounded-pill fw-bold text-xs uppercase mb-2 d-inline-block">
                            <i class="fa-solid fa-folder-open me-1"></i> {{ $video->category_name ?? $video->category ?? 'Mặc định' }}
                        </span>
                        <h2 class="h4 fw-bold text-slate-800 mb-2">{{ $video->title }}</h2>
                    </div>
                    <hr class="my-3 border-slate-200">
                    <div>
                        <h3 class="fs-6 fw-bold text-slate-500 mb-2">MÔ TẢ CHI TIẾT</h3>
                        <div class="text-slate-600 fs-6 leading-relaxed" style="white-space: pre-line;">
                            {{ $video->description ?: 'Không có mô tả chi tiết cho video này.' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Details Metadata -->
            <div class="d-flex flex-column gap-4">
                <!-- Thumbnail Preview Block -->
                <div class="thumbnail-preview-card">
                    <h3 class="fs-7 fw-bold text-slate-500 mb-3 uppercase tracking-wider"><i class="fa-regular fa-image me-1"></i> Ảnh đại diện (Thumbnail)</h3>
                    @if($video->thumbnail_url)
                        <img src="{{ $video->thumbnail_url }}" alt="Thumbnail preview" class="thumbnail-img">
                    @else
                        <div class="thumbnail-img d-flex align-items-center justify-content-center bg-slate-50 text-slate-400 flex-column gap-1">
                            <i class="fa-regular fa-image fs-3"></i>
                            <span class="small fw-semibold">Không có ảnh đại diện</span>
                        </div>
                    @endif
                </div>

                <!-- Stats & Info Grid -->
                <div class="detail-meta">
                    <div class="detail-meta-item">
                        <div class="meta-icon-wrapper bg-light-blue">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div class="meta-text-wrapper">
                            <div class="detail-label">Người đăng</div>
                            <div class="detail-value">{{ $video->user->full_name ?? $video->user->name ?? 'Hệ thống' }}</div>
                        </div>
                    </div>

                    <div class="detail-meta-item">
                        <div class="meta-icon-wrapper bg-light-green">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="meta-text-wrapper">
                            <div class="detail-label">Trạng thái</div>
                            <div class="detail-value text-capitalize">{{ $video->status ?? 'published' }}</div>
                        </div>
                    </div>

                    <div class="detail-meta-item">
                        <div class="meta-icon-wrapper bg-light-purple">
                            <i class="fa-solid fa-hard-drive"></i>
                        </div>
                        <div class="meta-text-wrapper">
                            <div class="detail-label">Dung lượng</div>
                            <div class="detail-value">{{ $video->file_size ? number_format($video->file_size / 1024 / 1024, 2) . ' MB' : 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="detail-meta-item">
                        <div class="meta-icon-wrapper bg-light-orange">
                            <i class="fa-solid fa-file-video"></i>
                        </div>
                        <div class="meta-text-wrapper">
                            <div class="detail-label">Mime type</div>
                            <div class="detail-value">{{ $video->mime_type ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="detail-meta-item">
                        <div class="meta-icon-wrapper bg-light-teal">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <div class="meta-text-wrapper">
                            <div class="detail-label">Lượt xem</div>
                            <div class="detail-value">{{ number_format($video->views ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="detail-meta-item">
                        <div class="meta-icon-wrapper bg-light-rose">
                            <i class="fa-solid fa-heart"></i>
                        </div>
                        <div class="meta-text-wrapper">
                            <div class="detail-label">Lượt thích</div>
                            <div class="detail-value">{{ number_format($video->likes ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="detail-meta-item">
                        <div class="meta-icon-wrapper bg-light-amber">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="meta-text-wrapper">
                            <div class="detail-label">Danh mục</div>
                            <div class="detail-value">{{ $video->category_name ?? $video->category ?? 'Không phân loại' }}</div>
                        </div>
                    </div>

                    <div class="detail-meta-item">
                        <div class="meta-icon-wrapper bg-light-violet">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                        <div class="meta-text-wrapper">
                            <div class="detail-label">Ngày đăng</div>
                            <div class="detail-value">{{ optional($video->created_at)->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section for Admin -->
        <div class="video-detail-comments mt-5">
            <div class="info-section">
                <h3 class="fs-6 fw-bold text-slate-800 mb-4 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-comments text-primary"></i> Quản lý bình luận
                    <span class="badge bg-slate-100 text-slate-600 rounded-pill fs-7 py-1 px-2.5">{{ $video->comments->count() }}</span>
                </h3>

                @if($video->comments->isEmpty())
                    <div class="py-4 text-center text-slate-400 fs-7">
                        <i class="fa-regular fa-comment-dots fs-3 mb-2 d-block"></i>
                        Không có bình luận nào trên video này.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle table-hover fs-7">
                            <thead>
                                <tr class="text-slate-500">
                                    <th style="width: 200px;">Người gửi</th>
                                    <th>Nội dung bình luận</th>
                                    <th style="width: 150px;">Thời gian</th>
                                    <th style="width: 100px;" class="text-end">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($video->comments as $comment)
                                    <tr style="{{ $comment->parent_id ? 'background-color: #fafbfc;' : '' }}">
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($comment->parent_id)
                                                    <i class="fa-solid fa-reply fa-rotate-180 text-slate-400 ms-2 me-1"></i>
                                                @endif
                                                <div>
                                                    <div class="fw-bold text-slate-800">{{ $comment->user->full_name ?? $comment->user->name ?? 'Người dùng' }}</div>
                                                    <span class="badge bg-light-blue text-xs rounded-pill" style="font-size: 10px;">ID #{{ $comment->user_id }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-slate-600" style="word-break: break-word; white-space: pre-line;">
                                            @if($comment->parent_id)
                                                <span class="badge bg-slate-200 text-slate-600 me-1" style="font-size: 10px;">Phản hồi bình luận #{{ $comment->parent_id }}</span>
                                            @endif
                                            {{ $comment->content }}
                                        </td>
                                        <td class="text-slate-500">{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.videos.comments.destroy', $comment) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận/phản hồi này? Thao tác này không thể hoàn tác.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" style="width: 32px; height: 32px; padding: 0;" title="Xóa bình luận">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
