@extends('admin.layouts.master')

@section('title', 'Quản lý bình luận & đánh giá')

@push('styles')
<style>
    .comments-card-shell {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        overflow: hidden;
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

    .media-thumb {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .media-thumb:hover {
        transform: scale(1.05);
    }

    .media-container {
        position: relative;
        display: inline-block;
    }

    .media-video-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.4);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        pointer-events: none;
    }

    .reply-box {
        background: #f8fafc;
        border-left: 3px solid #0046ab;
        border-radius: 6px;
        padding: 10px 15px;
        margin-top: 10px;
        font-size: 13px;
    }

    .avatar-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #fff;
        text-transform: uppercase;
        font-size: 14px;
    }

    .avatar-admin {
        background: linear-gradient(135deg, #0046ab, #0061f2);
    }

    .avatar-user {
        background: linear-gradient(135deg, #64748b, #94a3b8);
    }

    .bulk-action-bar {
        display: none;
        align-items: center;
        gap: 15px;
        padding: 14px 24px;
        background: linear-gradient(135deg, #fef2f2 0%, #fff1f2 100%);
        border: 1px solid #fecaca;
        border-radius: 12px;
        margin: 0 20px 15px;
        animation: slideDown 0.25s ease;
    }
    .bulk-action-bar.active { display: flex; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    .bulk-action-bar .selected-count { font-weight: 700; color: #dc2626; font-size: 14px; }
    .bulk-action-bar .btn-bulk-delete { background: linear-gradient(135deg, #dc2626, #b91c1c); color: #fff; border: none; padding: 8px 18px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; }
    .bulk-action-bar .btn-bulk-delete:hover { box-shadow: 0 6px 16px rgba(220,38,38,.3); transform: translateY(-1px); }
    .bulk-action-bar .btn-bulk-cancel { background: none; border: 1px solid #d1d5db; padding: 8px 14px; border-radius: 8px; font-size: 13px; cursor: pointer; color: #64748b; font-weight: 600; }
    .comment-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: #0046ab; }
</style>
@endpush

@section('content')
<div class="comments-card-shell">
    <div class="p-5 border-bottom border-slate-200 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Quản lý bình luận & đánh giá</h1>
            <div class="text-slate-500">Kiểm duyệt, phản hồi và xóa bình luận hoặc đánh giá tiêu cực từ khách hàng</div>
        </div>
    </div>

    <!-- Alert thông báo -->
    @if(session('success'))
        <div class="alert alert-success mx-5 mt-4 alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="p-5 border-bottom border-slate-200">
        <div class="filter-panel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <div class="text-uppercase fw-bold text-primary small mb-1">Phân loại</div>
                    <div class="text-slate-500 small">Chọn loại bình luận để kiểm duyệt</div>
                </div>
            </div>

            <div class="filter-chip-group mb-3">
                <a href="{{ route('admin.comments.index', ['tab' => 'reviews']) }}" class="filter-chip {{ $tab === 'reviews' ? 'active' : '' }}">
                    <i class="fa-solid fa-star"></i> Đánh giá sản phẩm
                    <span class="chip-count">{{ $totalReviews }}</span>
                </a>
                <a href="{{ route('admin.comments.index', ['tab' => 'video_comments']) }}" class="filter-chip {{ $tab === 'video_comments' ? 'active' : '' }}">
                    <i class="fa-solid fa-comment-dots"></i> Bình luận Góc video
                    <span class="chip-count">{{ $totalVideoComments }}</span>
                </a>
            </div>

            <!-- Form tìm kiếm -->
            <form method="GET" action="{{ route('admin.comments.index') }}" class="row g-3">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-6">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-lg" placeholder="Tìm theo nội dung, tên người dùng, sản phẩm/video...">
                </div>
                @if($tab === 'reviews')
                    <div class="col-md-3">
                        <select name="rating" class="form-select form-select-lg">
                            <option value="">Tất cả số sao</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                            @endfor
                        </select>
                    </div>
                @endif
                <div class="col-md-3 d-grid">
                    <button class="btn btn-outline-primary btn-lg">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($tab === 'reviews')
        <!-- Thanh hành động hàng loạt -->
        <div class="bulk-action-bar" id="bulkActionBarReviews">
            <span class="selected-count"><span id="selectedCountReviews">0</span> đánh giá được chọn</span>
            <button type="button" class="btn-bulk-delete" onclick="bulkDelete('reviews')"><i class="fa-solid fa-trash-can"></i> Xóa đã chọn</button>
            <button type="button" class="btn-bulk-cancel" onclick="clearSelection('reviews')">Bỏ chọn</button>
        </div>

        <!-- BẢNG ĐÁNH GIÁ SẢN PHẨM -->
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45px; padding-left: 20px;"><input type="checkbox" class="comment-checkbox" id="selectAllReviews" onchange="toggleSelectAll('reviews')"></th>
                        <th style="width: 70px;">ID</th>
                        <th style="width: 180px;">Người đánh giá</th>
                        <th style="width: 150px;">Sản phẩm</th>
                        <th style="width: 100px;">Điểm</th>
                        <th style="width: 120px;">Trạng thái</th>
                        <th>Nội dung đánh giá & Phản hồi</th>
                        <th style="width: 150px;">Tệp đính kèm</th>
                        <th style="width: 140px;">Ngày gửi</th>
                        <th class="text-end" style="padding-right: 20px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $item)
                        <tr>
                            <td style="padding-left: 20px;"><input type="checkbox" class="comment-checkbox review-checkbox" value="{{ $item->id }}" onchange="updateBulkBar('reviews')"></td>
                            <td class="fw-bold text-slate-500">#{{ $item->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle {{ $item->user && $item->user->role_id == 1 ? 'avatar-admin' : 'avatar-user' }}">
                                        {{ mb_substr($item->author_name ?? ($item->user ? $item->user->full_name : 'K'), 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-slate-900">{{ $item->author_name ?? ($item->user ? $item->user->full_name : 'Khách hàng') }}</div>
                                        <div class="text-slate-500 small" style="font-size: 11px;">
                                            {{ $item->user ? $item->user->email : 'Khách vãng lai' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($item->product)
                                    <a href="{{ url('/product/' . $item->product->product_id) }}" target="_blank" class="fw-bold text-decoration-none text-slate-800 text-truncate d-inline-block" style="max-width: 140px;" title="{{ $item->product->name }}">
                                        {{ $item->product->name }}
                                    </a>
                                @else
                                    <span class="text-muted small">Sản phẩm đã xóa</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="{{ $i <= $item->rating ? 'fa-solid' : 'fa-regular' }} fa-star" style="font-size: 11px;"></i>
                                    @endfor
                                </div>
                            </td>
                            <td>
                                @if($item->is_approved)
                                    <span class="badge bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i> Đã duyệt</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Chờ duyệt</span>
                                @endif

                                @if($item->report_count > 0)
                                    <div class="mt-1">
                                        <span class="badge bg-danger text-white" title="Số lượng báo cáo vi phạm"><i class="fa-solid fa-triangle-exclamation me-1"></i> Báo cáo: {{ $item->report_count }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="text-slate-900 mb-1" style="font-size: 14px;">{{ $item->content }}</div>
                                
                                <!-- Hiển thị câu trả lời hiện tại -->
                                @if($item->replies->count() > 0)
                                    <div class="mt-2 text-slate-500 small">Phản hồi:</div>
                                    @foreach($item->replies as $reply)
                                        <div class="reply-box">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-bold text-primary">
                                                    <i class="fa-solid fa-reply fa-flip-both me-1"></i>
                                                    {{ $reply->user && $reply->user->role_id == 1 ? 'Quản trị viên' : ($reply->author_name ?? ($reply->user ? $reply->user->full_name : 'Khách hàng')) }}
                                                </span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-slate-400" style="font-size: 10px;">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                                                    <form action="{{ route('admin.comments.reviews.destroy', $reply->id) }}" method="POST" class="d-inline action-confirm-form" data-message="Bạn có chắc chắn muốn xóa câu trả lời này?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-link text-danger p-0 m-0 btn-action-trigger" style="font-size:11px;" title="Xóa phản hồi">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="text-slate-700">{{ $reply->content }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if(!empty($item->media))
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($item->media as $url)
                                            @php $isVideo = preg_match('/\.(mp4|mov|avi|mkv)$/i', $url); @endphp
                                            <div class="media-container" 
                                                 data-bs-toggle="modal" 
                                                 data-bs-target="#mediaPreviewModal"
                                                 data-url="{{ $url }}"
                                                 data-video="{{ $isVideo ? 'true' : 'false' }}">
                                                @if($isVideo)
                                                    <video src="{{ $url }}" class="media-thumb"></video>
                                                    <div class="media-video-overlay"><i class="fa-solid fa-play"></i></div>
                                                @else
                                                    <img src="{{ $url }}" alt="review media" class="media-thumb">
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 small">Không có</span>
                                @endif
                            </td>
                            <td class="text-slate-500 small">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end" style="padding-right: 20px;">
                                <div class="btn-group gap-2">
                                    @if($item->report_count > 0)
                                        <form action="{{ route('admin.comments.reviews.clear-reports', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-sm rounded-3" title="Gỡ bỏ báo cáo vi phạm">
                                                <i class="fa-solid fa-shield-halved"></i> Bỏ báo cáo
                                            </button>
                                        </form>
                                    @endif
                                    @if(!$item->is_approved)
                                        <form action="{{ route('admin.comments.reviews.approve', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm rounded-3">
                                                <i class="fa-solid fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm rounded-3 btn-reply-trigger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#adminReplyModal"
                                            data-id="{{ $item->id }}" 
                                            data-type="review" 
                                            data-author="{{ $item->author_name ?? ($item->user ? $item->user->full_name : 'Khách hàng') }}" data-reply-url="{{ route('admin.comments.reviews.reply', $item->id) }}" 
                                            title="Phản hồi">
                                        <i class="fa-solid fa-reply"></i> Phản hồi
                                    </button>
                                    <form action="{{ route('admin.comments.reviews.destroy', $item->id) }}" method="POST" class="d-inline action-confirm-form" data-message="Bạn có chắc chắn muốn xóa đánh giá này? Tất cả các câu trả lời liên quan cũng sẽ bị xóa.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm rounded-3 btn-action-trigger" title="Xóa đánh giá">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-slate-500">Chưa có đánh giá nào thỏa mãn điều kiện.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $reviews->links() }}
        </div>
    @else
        <!-- Thanh hành động hàng loạt -->
        <div class="bulk-action-bar" id="bulkActionBarVideoComments">
            <span class="selected-count"><span id="selectedCountVideoComments">0</span> bình luận được chọn</span>
            <button type="button" class="btn-bulk-delete" onclick="bulkDelete('video-comments')"><i class="fa-solid fa-trash-can"></i> Xóa đã chọn</button>
            <button type="button" class="btn-bulk-cancel" onclick="clearSelection('video-comments')">Bỏ chọn</button>
        </div>

        <!-- BẢNG BÌNH LUẬN VIDEO -->
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 45px; padding-left: 20px;"><input type="checkbox" class="comment-checkbox" id="selectAllVideoComments" onchange="toggleSelectAll('video-comments')"></th>
                        <th style="width: 70px;">ID</th>
                        <th style="width: 200px;">Người bình luận</th>
                        <th style="width: 180px;">Video</th>
                        <th style="width: 120px;">Trạng thái</th>
                        <th>Nội dung bình luận & Phản hồi</th>
                        <th style="width: 140px;">Ngày gửi</th>
                        <th class="text-end" style="padding-right: 20px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($videoComments as $item)
                        <tr>
                            <td style="padding-left: 20px;"><input type="checkbox" class="comment-checkbox vc-checkbox" value="{{ $item->id }}" onchange="updateBulkBar('video-comments')"></td>
                            <td class="fw-bold text-slate-500">#{{ $item->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle {{ $item->user && $item->user->role_id == 1 ? 'avatar-admin' : 'avatar-user' }}">
                                        {{ mb_substr($item->user ? $item->user->full_name : 'K', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-slate-900">{{ $item->user ? $item->user->full_name : 'Khách hàng' }}</div>
                                        <div class="text-slate-500 small" style="font-size: 11px;">
                                            {{ $item->user ? $item->user->email : '' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($item->video)
                                    <a href="{{ route('videos.index') }}" target="_blank" class="fw-bold text-decoration-none text-slate-800 text-truncate d-inline-block" style="max-width: 170px;" title="{{ $item->video->title }}">
                                        {{ $item->video->title }}
                                    </a>
                                @else
                                    <span class="text-muted small">Video đã xóa</span>
                                @endif
                            </td>
                            <td>
                                @if($item->is_approved)
                                    <span class="badge bg-success text-white"><i class="fa-solid fa-circle-check me-1"></i> Đã duyệt</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i> Chờ duyệt</span>
                                @endif

                                @if($item->report_count > 0)
                                    <div class="mt-1">
                                        <span class="badge bg-danger text-white" title="Số lượng báo cáo vi phạm"><i class="fa-solid fa-triangle-exclamation me-1"></i> Báo cáo: {{ $item->report_count }}</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="text-slate-900 mb-1" style="font-size: 14px;">{{ $item->content }}</div>

                                <!-- Hiển thị câu trả lời hiện tại -->
                                @if($item->replies->count() > 0)
                                    <div class="mt-2 text-slate-500 small">Phản hồi:</div>
                                    @foreach($item->replies as $reply)
                                        <div class="reply-box">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="fw-bold text-primary">
                                                    <i class="fa-solid fa-reply fa-flip-both me-1"></i>
                                                    {{ $reply->user && $reply->user->role_id == 1 ? 'Quản trị viên' : ($reply->user ? $reply->user->full_name : 'Khách hàng') }}
                                                </span>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="text-slate-400" style="font-size: 10px;">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                                                    <form action="{{ route('admin.comments.video-comments.destroy', $reply->id) }}" method="POST" class="d-inline action-confirm-form" data-message="Bạn có chắc chắn muốn xóa câu trả lời này?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-link text-danger p-0 m-0 btn-action-trigger" style="font-size:11px;" title="Xóa phản hồi">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="text-slate-700">{{ $reply->content }}</div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-slate-500 small">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end" style="padding-right: 20px;">
                                <div class="btn-group gap-2">
                                    @if($item->report_count > 0)
                                        <form action="{{ route('admin.comments.video-comments.clear-reports', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-warning btn-sm rounded-3" title="Gỡ bỏ báo cáo vi phạm">
                                                <i class="fa-solid fa-shield-halved"></i> Bỏ báo cáo
                                            </button>
                                        </form>
                                    @endif
                                    @if(!$item->is_approved)
                                        <form action="{{ route('admin.comments.video-comments.approve', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm rounded-3">
                                                <i class="fa-solid fa-check"></i> Duyệt
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" 
                                            class="btn btn-outline-primary btn-sm rounded-3 btn-reply-trigger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#adminReplyModal"
                                            data-id="{{ $item->id }}" 
                                            data-type="video-comment" 
                                            data-author="{{ $item->user ? $item->user->full_name : 'Khách hàng' }}" data-reply-url="{{ route('admin.comments.video-comments.reply', $item->id) }}" 
                                            title="Phản hồi">
                                        <i class="fa-solid fa-reply"></i> Phản hồi
                                    </button>
                                    <form action="{{ route('admin.comments.video-comments.destroy', $item->id) }}" method="POST" class="d-inline action-confirm-form" data-message="Bạn có chắc chắn muốn xóa bình luận này? Tất cả các câu trả lời liên quan cũng sẽ bị xóa.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger btn-sm rounded-3 btn-action-trigger" title="Xóa bình luận">
                                            <i class="fa-solid fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-slate-500">Chưa có bình luận nào thỏa mãn điều kiện.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $videoComments->links() }}
        </div>
    @endif
</div>

<!-- Hidden forms cho bulk delete -->
<form id="bulkDeleteReviewsForm" method="POST" action="{{ route('admin.comments.reviews.bulk-delete') }}" style="display:none;">
    @csrf
</form>
<form id="bulkDeleteVideoCommentsForm" method="POST" action="{{ route('admin.comments.video-comments.bulk-delete') }}" style="display:none;">
    @csrf
</form>

<!-- Modal xem trước hình ảnh/video -->
<div class="modal fade" id="mediaPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <img id="previewModalImage" src="" class="img-fluid rounded-3" style="max-height: 80vh; display: none;">
                <video id="previewModalVideo" src="" controls class="img-fluid rounded-3" style="max-height: 80vh; width: 100%; display: none;"></video>
            </div>
        </div>
    </div>
</div>

<!-- Modal viết phản hồi của Admin -->
<div class="modal fade" id="adminReplyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="adminReplyForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="replyModalTitle">Gửi phản hồi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-slate-600" id="replyTargetText"></div>
                    <div class="mb-3">
                        <label for="replyContent" class="form-label fw-bold text-slate-800">Nội dung câu trả lời</label>
                        <textarea class="form-control" id="replyContent" name="content" rows="4" required placeholder="Nhập câu trả lời của quản trị viên..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane me-1"></i> Gửi phản hồi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Xử lý xác nhận xóa qua SweetAlert
    document.querySelectorAll('.btn-action-trigger').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            const message = form.getAttribute('data-message') || 'Bạn có chắc chắn muốn thực hiện hành động này?';

            Swal.fire({
                title: 'Xác nhận xóa?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Xử lý điền dữ liệu vào modal phản hồi
    const replyModalEl = document.getElementById('adminReplyModal');
    if (replyModalEl) {
        replyModalEl.addEventListener('show.bs.modal', function (event) {
            const triggerEl = event.relatedTarget;
            if (!triggerEl) return;

            const id = triggerEl.getAttribute('data-id');
            const type = triggerEl.getAttribute('data-type');
            const author = triggerEl.getAttribute('data-author');
            const replyUrl = triggerEl.getAttribute('data-reply-url');

            const replyContent = document.getElementById('replyContent');
            const replyModalTitle = document.getElementById('replyModalTitle');
            const replyTargetText = document.getElementById('replyTargetText');
            const replyForm = document.getElementById('adminReplyForm');

            replyContent.value = '';
            
            if (type === 'review') {
                replyModalTitle.innerText = 'Phản hồi đánh giá sản phẩm';
                replyTargetText.innerHTML = `Đang viết câu trả lời cho đánh giá của <strong>${author}</strong> (ID: #${id})`;
            } else {
                replyModalTitle.innerText = 'Phản hồi bình luận Góc video';
                replyTargetText.innerHTML = `Đang viết câu trả lời cho bình luận của <strong>${author}</strong> (ID: #${id})`;
            }
            
            replyForm.setAttribute('action', replyUrl);

            setTimeout(() => replyContent.focus(), 500);
        });
    }

    // Xử lý điền dữ liệu xem trước Media
    const mediaModalEl = document.getElementById('mediaPreviewModal');
    if (mediaModalEl) {
        mediaModalEl.addEventListener('show.bs.modal', function (event) {
            const triggerEl = event.relatedTarget;
            const url = triggerEl.getAttribute('data-url');
            const isVideo = triggerEl.getAttribute('data-video') === 'true';

            const img = document.getElementById('previewModalImage');
            const vid = document.getElementById('previewModalVideo');

            if (isVideo) {
                img.style.display = 'none';
                vid.style.display = 'block';
                vid.src = url;
            } else {
                vid.style.display = 'none';
                img.style.display = 'block';
                img.src = url;
            }
        });

        // Dừng video khi modal bị đóng
        mediaModalEl.addEventListener('hidden.bs.modal', function () {
            const vid = document.getElementById('previewModalVideo');
            vid.pause();
            vid.src = '';
        });
    }
});

// === Bulk Delete Functions ===
function toggleSelectAll(type) {
    const checkboxClass = type === 'reviews' ? '.review-checkbox' : '.vc-checkbox';
    const selectAllId = type === 'reviews' ? 'selectAllReviews' : 'selectAllVideoComments';
    const isChecked = document.getElementById(selectAllId).checked;
    
    document.querySelectorAll(checkboxClass).forEach(cb => cb.checked = isChecked);
    updateBulkBar(type);
}

function updateBulkBar(type) {
    const checkboxClass = type === 'reviews' ? '.review-checkbox' : '.vc-checkbox';
    const barId = type === 'reviews' ? 'bulkActionBarReviews' : 'bulkActionBarVideoComments';
    const countId = type === 'reviews' ? 'selectedCountReviews' : 'selectedCountVideoComments';
    const selectAllId = type === 'reviews' ? 'selectAllReviews' : 'selectAllVideoComments';

    const checked = document.querySelectorAll(checkboxClass + ':checked');
    const total = document.querySelectorAll(checkboxClass);
    
    document.getElementById(countId).textContent = checked.length;
    
    const bar = document.getElementById(barId);
    if (checked.length > 0) {
        bar.classList.add('active');
    } else {
        bar.classList.remove('active');
    }

    // Cập nhật trạng thái "chọn tất cả"
    document.getElementById(selectAllId).checked = checked.length === total.length && total.length > 0;
}

function clearSelection(type) {
    const checkboxClass = type === 'reviews' ? '.review-checkbox' : '.vc-checkbox';
    const selectAllId = type === 'reviews' ? 'selectAllReviews' : 'selectAllVideoComments';

    document.querySelectorAll(checkboxClass).forEach(cb => cb.checked = false);
    document.getElementById(selectAllId).checked = false;
    updateBulkBar(type);
}

function bulkDelete(type) {
    const checkboxClass = type === 'reviews' ? '.review-checkbox' : '.vc-checkbox';
    const formId = type === 'reviews' ? 'bulkDeleteReviewsForm' : 'bulkDeleteVideoCommentsForm';
    const label = type === 'reviews' ? 'đánh giá' : 'bình luận';

    const checked = document.querySelectorAll(checkboxClass + ':checked');
    if (checked.length === 0) return;

    Swal.fire({
        title: `Xóa ${checked.length} ${label}?`,
        text: `Bạn có chắc chắn muốn xóa ${checked.length} ${label} đã chọn? Tất cả phản hồi liên quan cũng sẽ bị xóa. Thao tác này không thể hoàn tác.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Đồng ý xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById(formId);
            // Xóa input ids cũ
            form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
            // Thêm input ids mới
            checked.forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                form.appendChild(input);
            });
            form.submit();
        }
    });
}
</script>
@endpush
