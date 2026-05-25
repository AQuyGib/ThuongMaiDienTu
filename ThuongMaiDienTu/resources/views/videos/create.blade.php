@extends('layouts.app')

@section('title', 'Tải Lên Video')

@push('styles')
<style>
    /* Styling tokens */
    :root {
        --glass-bg: rgba(255, 255, 255, 0.85);
        --glass-border: rgba(226, 232, 240, 0.8);
        --shadow-premium: 0 15px 35px -10px rgba(0, 70, 171, 0.1);
        --primary-gradient: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
    }

    .video-upload-page {
        padding: 50px 0 90px;
        background: radial-gradient(at 0% 0%, rgba(0, 70, 171, 0.03) 0px, transparent 50%),
                    radial-gradient(at 100% 100%, rgba(215, 0, 24, 0.01) 0px, transparent 50%);
        min-height: 100vh;
    }

    .upload-card {
        max-width: 900px;
        margin: 0 auto;
        background: var(--glass-bg);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        box-shadow: var(--shadow-premium);
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .upload-header {
        padding: 30px 35px;
        background: var(--primary-gradient);
        color: #fff;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .upload-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: rgba(255,255,255,0.1);
    }

    .upload-header-text h1 {
        font-size: 26px;
        font-weight: 850;
        margin-bottom: 6px;
        letter-spacing: -0.02em;
    }

    .upload-header-text p {
        font-size: 14px;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .btn-back {
        padding: 10px 20px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-back:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateX(-3px);
    }

    .upload-body {
        padding: 35px;
    }

    /* Grid Layout */
    .upload-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 750;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .form-group input[type="text"],
    .form-group textarea {
        width: 100%;
        border: 1.5px solid #cbd5e1;
        border-radius: 14px;
        padding: 12px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #0f172a;
        outline: none;
        background: #fff;
        transition: all 0.2s ease;
    }

    .form-group input[type="text"]:focus,
    .form-group textarea:focus {
        border-color: #0046ab;
        box-shadow: 0 0 0 4px rgba(0, 70, 171, 0.08);
    }

    .form-group textarea {
        min-height: 140px;
        resize: vertical;
    }

    /* Drag Drop / File Box */
    .drop-zone {
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 18px;
        padding: 24px;
        text-align: center;
        transition: all 0.25s ease;
        position: relative;
        cursor: pointer;
    }

    .drop-zone:hover {
        border-color: #0046ab;
        background: rgba(0, 70, 171, 0.01);
    }

    .drop-zone-icon {
        font-size: 32px;
        color: #64748b;
        margin-bottom: 12px;
        transition: color 0.2s ease;
    }

    .drop-zone:hover .drop-zone-icon {
        color: #0046ab;
    }

    .file-input-hidden {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        z-index: 5;
    }

    .file-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        background: #eff6ff;
        color: #0046ab;
        margin-top: 10px;
        box-shadow: 0 2px 8px rgba(0, 70, 171, 0.05);
    }

    .drop-zone-help {
        font-size: 12px;
        color: #64748b;
        margin-top: 8px;
        line-height: 1.5;
    }

    /* Thumbnail Preview Widget */
    .file-preview-card {
        display: none;
        margin-top: 15px;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }

    .file-preview-card.show {
        display: block;
    }

    .preview-image-wrapper {
        aspect-ratio: 16 / 9;
        width: 100%;
        background: #f1f5f9;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .preview-image-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .preview-fallback {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        color: #94a3b8;
        font-size: 13px;
        font-weight: 600;
    }

    .preview-metadata {
        padding: 14px;
        font-size: 12px;
        color: #475569;
        border-top: 1px solid #f1f5f9;
        background: #f8fafc;
        display: grid;
        gap: 6px;
    }

    .preview-metadata span {
        font-weight: 750;
        color: #1e293b;
    }

    /* Video validation badge */
    .validation-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        background: #f0fdf4;
        color: #166534;
        margin-top: 12px;
        border: 1px solid #bbf7d0;
        width: 100%;
    }

    .validation-badge.invalid {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    /* Progress bar */
    .progress-wrap {
        display: none;
        margin-top: 25px;
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 20px;
        border-radius: 18px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        font-size: 13px;
        font-weight: 750;
        color: #1e293b;
    }

    .progress-track {
        height: 10px;
        background: #e2e8f0;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-bar {
        width: 0%;
        height: 100%;
        background: var(--primary-gradient);
        border-radius: 999px;
        transition: width 0.2s ease;
    }

    /* Actions */
    .action-row {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 16px;
        margin-top: 30px;
        border-top: 1px solid #e2e8f0;
        padding-top: 25px;
    }

    .btn-action {
        padding: 12px 28px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 750;
        cursor: pointer;
        transition: all 0.25s ease;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-action-primary {
        background: var(--primary-gradient);
        color: #fff;
        box-shadow: 0 8px 20px rgba(0, 70, 171, 0.15);
    }

    .btn-action-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(0, 70, 171, 0.25);
    }

    .btn-action-primary:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-action-secondary {
        background: #e2e8f0;
        color: #334155;
    }

    .btn-action-secondary:hover {
        background: #cbd5e1;
    }

    /* Media queries */
    @media (max-width: 768px) {
        .upload-card {
            border-radius: 20px;
        }

        .upload-header {
            padding: 20px 24px;
        }

        .upload-header-text h1 {
            font-size: 22px;
        }

        .upload-body {
            padding: 24px;
        }

        .upload-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }

        .action-row {
            flex-direction: column-reverse;
            gap: 10px;
        }

        .btn-action {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@section('content')
<div class="video-upload-page">
    <div class="container">
        <div class="upload-card">
            <!-- Header -->
            <div class="upload-header">
                <div class="upload-header-text">
                    <h1>Tải Lên Video Trải Nghiệm</h1>
                    <p>Chia sẻ video đánh giá, unbox hoặc hướng dẫn sử dụng sản phẩm công nghệ của bạn.</p>
                </div>
                <a href="{{ route('videos.index') }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
            </div>

            <!-- Body -->
            <div class="upload-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4 border-0 shadow-sm" style="border-radius: 12px; background-color: #fef2f2; color: #991b1b;">
                        <ul class="mb-0 ps-3 fw-semibold" style="font-size: 13px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="videoUploadForm" action="{{ route('videos.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="redirect_to" id="redirectTo" value="{{ route('videos.index') }}">

                    <div class="upload-grid">
                        <!-- Left Column (Metadata & Thumbnail) -->
                        <div>
                            <div class="form-group">
                                <label for="title">Tiêu đề video</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" placeholder="Ví dụ: Đánh giá chi tiết iPhone 15 Pro Max sau 6 tháng" required>
                            </div>

                            <div class="form-group">
                                <label>Ảnh Thumbnail (Ảnh thu nhỏ)</label>
                                <div class="drop-zone">
                                    <i class="fa-regular fa-image drop-zone-icon"></i>
                                    <div><strong>Nhấn để chọn ảnh</strong> hoặc kéo thả vào đây</div>
                                    <div class="drop-zone-help">Hỗ trợ định dạng JPG, PNG, WEBP. Tối đa 2MB.</div>
                                    <input type="file" name="thumbnail" id="thumbnail" class="file-input-hidden" accept="image/*">
                                </div>
                                <span class="file-chip" id="thumbnailInfo">
                                    <i class="fa-regular fa-image"></i> Chưa chọn ảnh
                                </span>

                                <!-- Preview Widget -->
                                <div class="file-preview-card" id="thumbnailPreviewCard">
                                    <div class="preview-image-wrapper">
                                        <img id="thumbnailPreviewImg" alt="Thumbnail preview" style="display:none;">
                                        <div id="thumbnailPreviewFallback" class="preview-fallback">
                                            <i class="fa-regular fa-image" style="font-size: 24px;"></i> Preview Thumbnail
                                        </div>
                                    </div>
                                    <div class="preview-metadata">
                                        <div>Tên file: <span id="thumbnailName">-</span></div>
                                        <div>Kích thước: <span id="thumbnailSize">-</span></div>
                                        <div>Định dạng: <span id="thumbnailType">-</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column (Description & Video File) -->
                        <div>
                            <div class="form-group">
                                <label for="description">Mô tả nội dung</label>
                                <textarea name="description" id="description" placeholder="Mô tả ngắn gọn về nội dung video, các điểm chính được nhắc đến..." required>{{ old('description') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label>Tệp tin Video</label>
                                <div class="drop-zone" style="background: rgba(0, 70, 171, 0.01); border-color: #bcd0ea;">
                                    <i class="fa-solid fa-cloud-arrow-up drop-zone-icon" style="color: #0046ab;"></i>
                                    <div><strong>Nhấn để chọn tệp video</strong> hoặc kéo thả vào đây</div>
                                    <div class="drop-zone-help">Chấp nhận định dạng MP4 hoặc MKV. Dung lượng tối đa 20MB.</div>
                                    <input type="file" name="video" id="video" class="file-input-hidden" accept=".mp4,.mkv,video/mp4,video/x-matroska" required>
                                </div>
                                <span class="file-chip" id="videoInfo">
                                    <i class="fa-solid fa-film"></i> Chưa chọn video
                                </span>

                                <div class="validation-badge invalid" id="videoValidationBadge">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>Chờ chọn tệp video...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress bar wrapper -->
                    <div class="progress-wrap" id="uploadProgressWrap">
                        <div class="progress-header">
                            <span>Đang tải video lên máy chủ...</span>
                            <span id="uploadProgressText">0%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-bar" id="uploadProgressBar"></div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="action-row">
                        <a href="{{ route('videos.index') }}" class="btn-action btn-action-secondary">Hủy bỏ</a>
                        <button type="submit" class="btn-action btn-action-primary" id="uploadSubmitBtn">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Tải Lên Video
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
            videoValidationBadge.classList.add('invalid');
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
            : '<i class="fa-solid fa-triangle-exclamation"></i><span>' + (!isTypeValid ? 'Định dạng video không được hỗ trợ.' : 'Dung lượng file vượt quá giới hạn 20MB.') + '</span>';

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
            alert('Vui lòng chọn file video.');
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
            submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải Lên Video';
            alert('Tải lên thất bại, vui lòng thử lại.');
        };

        xhr.onerror = function () {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Tải Lên Video';
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
