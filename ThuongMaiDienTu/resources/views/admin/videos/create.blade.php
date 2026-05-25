@extends('admin.layouts.master')

@section('title', 'Đăng video')

@push('styles')
<style>
    .video-upload-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .video-upload-header {
        padding: 22px 24px;
        background: linear-gradient(135deg, rgba(0,70,171,.04), rgba(0,97,242,.03));
        border-bottom: 1px solid #e2e8f0;
    }

    .file-chip, .validation-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }

    .file-chip { background: #eff6ff; color: #1d4ed8; }
    .validation-badge { background: #eff6ff; color: #1d4ed8; margin-top: 10px; }
    .validation-badge.invalid { background: #fef2f2; color: #b91c1c; }

    .progress-wrap { display:none; margin-top: 14px; }
    .progress-track { width:100%; height:12px; background:#e2e8f0; border-radius:999px; overflow:hidden; }
    .progress-bar { width:0%; height:100%; background:linear-gradient(135deg,#0046ab 0%,#0061f2 100%); }

    .toast-shell {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        min-width: 280px;
        max-width: 360px;
        padding: 14px 16px;
        border-radius: 14px;
        background: #0f172a;
        color: #fff;
        box-shadow: 0 18px 40px rgba(15,23,42,.25);
        display:flex;
        align-items:flex-start;
        gap:10px;
        transform: translateY(-10px);
        opacity: 0;
        pointer-events: none;
        transition: all .25s ease;
    }
    .toast-shell.show { opacity: 1; transform: translateY(0); }
</style>
@endpush

@section('content')
<div class="video-upload-card">
    <div class="video-upload-header d-flex justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h4 fw-bold mb-1">Đăng video mới</h1>
            <div class="text-slate-500">Admin đăng video để hiển thị trong Góc video. Video tối đa 20MB.</div>
        </div>
        <a href="{{ route('admin.videos.index') }}" class="btn btn-light">Quay lại</a>
    </div>

    <div class="p-4 p-md-5">
        <form id="adminVideoUploadForm" action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <div class="col-lg-6">
                    <label class="form-label fw-bold">Tiêu đề</label>
                    <input type="text" name="title" class="form-control form-control-lg" required>
                </div>
                <div class="col-lg-6">
                    <label class="form-label fw-bold">Thumbnail</label>
                    <input type="file" name="thumbnail" id="thumbnail" class="form-control form-control-lg" accept="image/*">
                    <div class="file-chip mt-2" id="thumbnailInfo"><i class="fa-regular fa-image"></i> Chưa chọn ảnh</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Tệp video</label>
                    <input type="file" name="video" id="video" class="form-control form-control-lg" accept=".mp4,.mkv,video/mp4,video/x-matroska" required>
                    <div class="file-chip mt-2" id="videoInfo"><i class="fa-solid fa-film"></i> Chưa chọn video</div>
                    <div class="validation-badge" id="videoValidationBadge"><i class="fa-solid fa-circle-info"></i><span>Chờ chọn video</span></div>
                </div>
            </div>

            <div class="progress-wrap" id="uploadProgressWrap">
                <div class="d-flex justify-content-between small fw-bold mb-2">
                    <span>Đang tải lên</span>
                    <span id="uploadProgressText">0%</span>
                </div>
                <div class="progress-track"><div id="uploadProgressBar" class="progress-bar"></div></div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.videos.index') }}" class="btn btn-light">Hủy</a>
                <button type="submit" id="uploadSubmitBtn" class="btn btn-primary">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> Đăng video (tối đa 20MB)
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('adminVideoUploadForm');
    const fileInput = document.getElementById('video');
    const thumbnailInput = document.getElementById('thumbnail');
    const submitBtn = document.getElementById('uploadSubmitBtn');
    const progressWrap = document.getElementById('uploadProgressWrap');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const videoInfo = document.getElementById('videoInfo');
    const thumbnailInfo = document.getElementById('thumbnailInfo');
    const videoValidationBadge = document.getElementById('videoValidationBadge');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const MAX_SIZE = 20 * 1024 * 1024;
    const ALLOWED_MIME = ['video/mp4', 'video/x-matroska'];

    function toast(message) {
        let el = document.getElementById('adminUploadToast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'adminUploadToast';
            el.className = 'toast-shell';
            el.innerHTML = '<div style="width:34px;height:34px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid fa-check"></i></div><div style="flex:1;"><div style="font-weight:800;font-size:14px;margin-bottom:2px;">Thành công</div><div id="adminUploadToastMsg" style="font-size:13px;line-height:1.5;color:#cbd5e1;"></div></div>';
            document.body.appendChild(el);
        }
        document.getElementById('adminUploadToastMsg').textContent = message;
        requestAnimationFrame(() => el.classList.add('show'));
        setTimeout(() => el.classList.remove('show'), 1200);
    }

    fileInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (videoInfo) videoInfo.innerHTML = file ? '<i class="fa-solid fa-film"></i> ' + file.name : '<i class="fa-solid fa-film"></i> Chưa chọn video';
        if (!videoValidationBadge) return;
        if (!file) {
            videoValidationBadge.classList.remove('invalid');
            videoValidationBadge.innerHTML = '<i class="fa-solid fa-circle-info"></i><span>Chờ chọn file video</span>';
            return;
        }
        const valid = ALLOWED_MIME.includes(file.type) && file.size <= MAX_SIZE;
        videoValidationBadge.classList.toggle('invalid', !valid);
        videoValidationBadge.innerHTML = valid ? '<i class="fa-solid fa-circle-check"></i><span>Video hợp lệ • ' + Math.round(file.size / 1024 / 1024 * 100) / 100 + ' MB</span>' : '<i class="fa-solid fa-triangle-exclamation"></i><span>' + (!ALLOWED_MIME.includes(file.type) ? 'Sai định dạng video.' : 'Video vượt quá 20MB.') + '</span>';
    });

    thumbnailInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (thumbnailInfo) thumbnailInfo.innerHTML = file ? '<i class="fa-regular fa-image"></i> ' + file.name : '<i class="fa-regular fa-image"></i> Chưa chọn ảnh';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const file = fileInput?.files?.[0];
        if (!file) return alert('Vui lòng chọn video.');
        if (file.size > MAX_SIZE) return alert('Video không được vượt quá 20MB.');
        if (!ALLOWED_MIME.includes(file.type)) return alert('Chỉ chấp nhận .mp4 hoặc .mkv.');

        const xhr = new XMLHttpRequest();
        const data = new FormData(form);
        submitBtn.disabled = true;
        progressWrap.style.display = 'block';
        xhr.open('POST', form.action, true);
        if (csrf) xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener('progress', function (event) {
            if (!event.lengthComputable) return;
            const percent = Math.round((event.loaded / event.total) * 100);
            progressBar.style.width = percent + '%';
            progressText.textContent = percent + '%';
        });

        xhr.onload = function () {
            submitBtn.disabled = false;
            if (xhr.status >= 200 && xhr.status < 300) {
                toast('Upload video thành công.');
                setTimeout(() => window.location.href = '{{ route('admin.videos.index') }}', 900);
                return;
            }
            alert('Upload thất bại, vui lòng thử lại.');
        };

        xhr.onerror = function () {
            submitBtn.disabled = false;
            alert('Không thể kết nối đến máy chủ.');
        };

        xhr.send(data);
    });
})();
</script>
@endpush
