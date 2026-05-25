@extends('layouts.app')

@section('title', 'Upload Video')

@push('styles')
<style>
    .video-upload-page {
        padding: 48px 0 64px;
    }

    .video-upload-card {
        max-width: 860px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 20px;
        box-shadow: var(--shadow-premium);
        overflow: hidden;
    }

    .video-upload-header {
        padding: 24px 28px;
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        color: #fff;
    }

    .video-upload-header h1 {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .video-upload-header p {
        font-size: 14px;
        opacity: 0.92;
    }

    .video-upload-body {
        padding: 28px;
    }

    .video-upload-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .video-upload-field {
        margin-bottom: 18px;
    }

    .video-upload-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 700;
        color: var(--text-color);
    }

    .video-upload-field input,
    .video-upload-field textarea {
        width: 100%;
        border: 1px solid #dbe4f0;
        border-radius: 14px;
        padding: 13px 15px;
        font-size: 14px;
        outline: none;
        background: #fff;
        transition: .2s ease;
    }

    .video-upload-field input:focus,
    .video-upload-field textarea:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(0, 70, 171, 0.08);
    }

    .field-error {
        margin-top: 8px;
        font-size: 12px;
        color: #b91c1c;
        font-weight: 600;
    }

    .inline-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08) !important;
    }

    .file-preview-card {
        display: none;
        margin-top: 14px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #dbe4f0;
        background: #fff;
    }

    .file-preview-card.show { display: block; }

    .file-preview-thumb {
        width: 100%;
        aspect-ratio: 16 / 9;
        object-fit: cover;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0f172a;
        font-weight: 700;
        font-size: 13px;
    }

    .file-preview-meta {
        padding: 12px 14px;
        font-size: 12px;
        color: #475569;
        display: grid;
        gap: 4px;
    }

    .validation-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        margin-top: 10px;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .validation-badge.invalid {
        background: #fef2f2;
        color: #b91c1c;
    }

    .file-input-row {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .file-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 12px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 12px;
        font-weight: 700;
    }

    .video-upload-field textarea {
        min-height: 130px;
        resize: vertical;
    }

    .video-help {
        margin-top: 8px;
        font-size: 12px;
        color: var(--text-muted);
        line-height: 1.6;
    }

    .video-file-box {
        border: 1.5px dashed #bcd0ea;
        border-radius: 16px;
        padding: 18px;
        background: #f8fbff;
    }

    .progress-wrap {
        display: none;
        margin-top: 14px;
    }

    .progress-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .progress-track {
        width: 100%;
        height: 12px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-bar {
        width: 0%;
        height: 100%;
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        border-radius: inherit;
        transition: width .15s ease;
    }

    .upload-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .btn-upload {
        border: none;
        border-radius: 12px;
        padding: 12px 22px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s ease;
    }

    .btn-primary-upload {
        background: var(--primary-gradient);
        color: #fff;
    }

    .btn-primary-upload:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(0, 70, 171, .18);
    }

    .btn-secondary-upload {
        background: #e2e8f0;
        color: #334155;
    }

    .preview-note {
        margin-top: 10px;
        font-size: 12px;
        color: #64748b;
    }

    @media (max-width: 768px) {
        .video-upload-page { padding: 18px 0 34px; }
        .video-upload-body, .video-upload-header { padding-left: 18px; padding-right: 18px; }
        .video-upload-grid { grid-template-columns: 1fr; }
        .upload-actions { flex-direction: column-reverse; }
        .btn-upload { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="video-upload-page">
    <div class="container">
        <div class="video-upload-card">
            <div class="video-upload-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h1>Video của tôi</h1>
                    <p>Danh sách video đã tải lên và trạng thái duyệt.</p>
                </div>
                <a href="{{ route('admin.videos.create') }}" class="btn btn-light btn-sm fw-bold">
                    <i class="fa-solid fa-circle-plus me-1"></i> Upload nội bộ
                </a>
            </div>

            <div class="video-upload-body">
                @if(session('success'))
                    <div class="alert alert-success mb-4">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="videoUploadForm" action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="redirect_to" id="redirectTo" value="{{ route('videos.index') }}">

                    <div class="video-upload-grid">
                        <div>
                            <div class="video-upload-field">
                                <label for="title">Tiêu đề video</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="Nhập tiêu đề video" required>
                            </div>

                            <div class="video-upload-field">
                                <label for="thumbnail">Thumbnail</label>
                                <div class="video-file-box">
                                    <div class="file-input-row">
                                        <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
                                        <span class="file-chip" id="thumbnailInfo"><i class="fa-regular fa-image"></i> Chưa chọn ảnh</span>
                                    </div>
                                    <div class="preview-note">Ảnh gợi ý: JPG, PNG, WEBP. Dung lượng tối đa 2MB.</div>
                                    <div class="file-preview-card" id="thumbnailPreviewCard">
                                        <img id="thumbnailPreviewImg" alt="Thumbnail preview" class="file-preview-thumb" style="display:none;">
                                        <div id="thumbnailPreviewFallback" class="file-preview-thumb"><i class="fa-regular fa-image me-2"></i> Preview thumbnail</div>
                                        <div class="file-preview-meta">
                                            <div><strong>Tên:</strong> <span id="thumbnailName">-</span></div>
                                            <div><strong>Kích thước:</strong> <span id="thumbnailSize">-</span></div>
                                            <div><strong>Loại:</strong> <span id="thumbnailType">-</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="video-upload-field">
                                <label for="description">Mô tả</label>
                                <textarea name="description" id="description" placeholder="Mô tả ngắn về nội dung video">{{ old('description') }}</textarea>
                            </div>

                            <div class="video-upload-field">
                                <label for="video">Tệp video</label>
                                <div class="video-file-box">
                                    <div class="file-input-row">
                                        <input type="file" name="video" id="video" accept=".mp4,.mkv,video/mp4,video/x-matroska" required>
                                        <span class="file-chip" id="videoInfo"><i class="fa-solid fa-film"></i> Chưa chọn video</span>
                                    </div>
                                    <div class="video-help">
                                        Hỗ trợ .mp4 / .mkv. Nếu file vượt quá 20MB, hệ thống sẽ báo lỗi trước khi tải lên.
                                    </div>
                                    <div class="validation-badge" id="videoValidationBadge">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <span>Chờ chọn file video</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="progress-wrap" id="uploadProgressWrap">
                        <div class="progress-label">
                            <span>Đang tải lên</span>
                            <span id="uploadProgressText">0%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar" id="uploadProgressBar"></div>
                        </div>
                    </div>

                    <div class="upload-actions">
                        <a href="{{ url()->previous() }}" class="btn-upload btn-secondary-upload">Quay lại</a>
                        <button type="submit" class="btn-upload btn-primary-upload" id="uploadSubmitBtn">
                            <i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải lên video
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('videoUploadForm');
    if (!form) return;

    const fileInput = document.getElementById('video');
    const thumbnailInput = document.getElementById('thumbnail');
    const submitBtn = document.getElementById('uploadSubmitBtn');
    const progressWrap = document.getElementById('uploadProgressWrap');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const videoInfo = document.getElementById('videoInfo');
    const thumbnailInfo = document.getElementById('thumbnailInfo');
    const videoValidationBadge = document.getElementById('videoValidationBadge');
    const thumbnailPreviewCard = document.getElementById('thumbnailPreviewCard');
    const thumbnailPreviewImg = document.getElementById('thumbnailPreviewImg');
    const thumbnailPreviewFallback = document.getElementById('thumbnailPreviewFallback');
    const thumbnailName = document.getElementById('thumbnailName');
    const thumbnailSize = document.getElementById('thumbnailSize');
    const thumbnailType = document.getElementById('thumbnailType');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const MAX_SIZE = 20 * 1024 * 1024;
    const ALLOWED_MIME = ['video/mp4', 'video/x-matroska'];
    const ALLOWED_THUMB_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    function formatBytes(bytes) {
        if (!bytes && bytes !== 0) return '-';
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        let value = bytes;
        while (value >= 1024 && i < units.length - 1) {
            value /= 1024;
            i++;
        }
        return value.toFixed(value >= 10 || i === 0 ? 0 : 1) + ' ' + units[i];
    }

    function setFieldError(input, isInvalid) {
        if (!input) return;
        input.classList.toggle('inline-error', isInvalid);
    }

    function updateVideoValidation(file) {
        if (!videoValidationBadge) return;

        if (!file) {
            videoValidationBadge.classList.remove('invalid');
            videoValidationBadge.innerHTML = '<i class="fa-solid fa-circle-info"></i><span>Chờ chọn file video</span>';
            setFieldError(fileInput, false);
            return;
        }

        const isTypeValid = ALLOWED_MIME.includes(file.type);
        const isSizeValid = file.size <= MAX_SIZE;
        const isValid = isTypeValid && isSizeValid;

        videoValidationBadge.classList.toggle('invalid', !isValid);
        videoValidationBadge.innerHTML = isValid
            ? '<i class="fa-solid fa-circle-check"></i><span>Video hợp lệ: ' + file.name + ' • ' + formatBytes(file.size) + '</span>'
            : '<i class="fa-solid fa-triangle-exclamation"></i><span>' + (!isTypeValid ? 'Sai định dạng video.' : 'Video vượt quá 20MB.') + '</span>';

        setFieldError(fileInput, !isValid);
    }

    fileInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (videoInfo) videoInfo.innerHTML = file
            ? '<i class="fa-solid fa-film"></i> ' + file.name
            : '<i class="fa-solid fa-film"></i> Chưa chọn video';
        updateVideoValidation(file);
    });

    thumbnailInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (thumbnailInfo) thumbnailInfo.innerHTML = file
            ? '<i class="fa-regular fa-image"></i> ' + file.name
            : '<i class="fa-regular fa-image"></i> Chưa chọn ảnh';

        if (!thumbnailPreviewCard || !thumbnailPreviewImg || !thumbnailPreviewFallback) return;

        if (!file) {
            thumbnailPreviewCard.classList.remove('show');
            thumbnailPreviewImg.style.display = 'none';
            thumbnailPreviewFallback.style.display = 'flex';
            return;
        }

        const isTypeValid = ALLOWED_THUMB_TYPES.includes(file.type);
        const isSizeValid = file.size <= (2 * 1024 * 1024);
        const isValid = isTypeValid && isSizeValid;

        if (thumbnailName) thumbnailName.textContent = file.name;
        if (thumbnailSize) thumbnailSize.textContent = formatBytes(file.size);
        if (thumbnailType) thumbnailType.textContent = file.type || 'unknown';

        if (!isValid) {
            thumbnailPreviewCard.classList.add('show');
            thumbnailPreviewImg.style.display = 'none';
            thumbnailPreviewFallback.style.display = 'flex';
            thumbnailPreviewFallback.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-2"></i> Ảnh không hợp lệ';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            thumbnailPreviewImg.src = e.target.result;
            thumbnailPreviewImg.style.display = 'block';
            thumbnailPreviewFallback.style.display = 'none';
            thumbnailPreviewCard.classList.add('show');
        };
        reader.readAsDataURL(file);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const file = fileInput?.files?.[0];
        if (!file) {
            updateVideoValidation(null);
            alert('Vui lòng chọn video.');
            return;
        }

        if (file.size > MAX_SIZE) {
            updateVideoValidation(file);
            alert('Video không được vượt quá 20MB.');
            return;
        }

        if (!ALLOWED_MIME.includes(file.type)) {
            updateVideoValidation(file);
            alert('Chỉ chấp nhận video định dạng .mp4 hoặc .mkv.');
            return;
        }

        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Đang tải lên...';
        progressWrap.style.display = 'block';
        progressBar.style.width = '0%';
        progressText.textContent = '0%';

        xhr.open('POST', form.action, true);
        if (csrf) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
        }
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.upload.addEventListener('progress', function (event) {
            if (!event.lengthComputable) return;
            const percent = Math.round((event.loaded / event.total) * 100);
            progressBar.style.width = percent + '%';
            progressText.textContent = percent + '%';
        });

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                try {
                    const response = JSON.parse(xhr.responseText || '{}');
                    const toastMessage = response?.message || 'Upload video thành công.';
                    const redirectTo = document.getElementById('redirectTo')?.value || '{{ route('videos.index') }}';
                    showToast(toastMessage);
                    setTimeout(() => {
                        window.location.href = redirectTo;
                    }, 900);
                } catch (error) {
                    window.location.href = '{{ route('videos.index') }}';
                }
                return;
            }

            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải lên video';
            alert('Upload thất bại, vui lòng thử lại.');
        };

        xhr.onerror = function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải lên video';
            alert('Không thể kết nối đến máy chủ.');
        };

        function showToast(message) {
            let toast = document.getElementById('uploadSuccessToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'uploadSuccessToast';
                toast.style.cssText = 'position:fixed;top:20px;right:20px;z-index:99999;min-width:280px;max-width:360px;padding:14px 16px;border-radius:14px;background:#0f172a;color:#fff;box-shadow:0 18px 40px rgba(15,23,42,.25);display:flex;align-items:flex-start;gap:10px;transform:translateY(-10px);opacity:0;transition:all .25s ease;';
                toast.innerHTML = '<div style="width:34px;height:34px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid fa-check"></i></div><div style="flex:1;"><div style="font-weight:800;font-size:14px;margin-bottom:2px;">Thành công</div><div style="font-size:13px;line-height:1.5;color:#cbd5e1;" id="uploadSuccessToastMessage"></div></div>';
                document.body.appendChild(toast);
            }
            const messageEl = document.getElementById('uploadSuccessToastMessage');
            if (messageEl) messageEl.textContent = message;
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
            }, 750);
        }

        xhr.send(formData);
    });
})();
</script>
@endpush
