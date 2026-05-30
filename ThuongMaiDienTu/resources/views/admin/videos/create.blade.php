@extends('admin.layouts.master')

@section('title', 'Đăng video')

@push('styles')
<style>
    .video-upload-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .video-upload-header {
        padding: 24px 32px;
        background: linear-gradient(135deg, rgba(0,70,171,.03), rgba(0,97,242,.01));
        border-bottom: 1px solid #e2e8f0;
    }

    .dropzone-inner {
        position: relative;
        border-color: #cbd5e1 !important;
        border-width: 2px !important;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: #f8fafc;
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
    }

    .dropzone-inner:hover {
        border-color: #0046ab !important;
        background-color: #f0f7ff;
    }

    .dropzone-inner.has-preview * {
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    .dropzone-inner.has-preview:hover * {
        opacity: 1;
        background: rgba(255, 255, 255, 0.92);
        border-radius: 12px;
        padding: 8px;
    }

    .source-tab {
        border: none;
        background: transparent;
        color: #64748b;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        padding: 10px 16px;
        transition: all 0.25s ease;
    }

    .source-tab.active {
        background-color: #0046ab !important;
        color: #fff !important;
        box-shadow: 0 8px 20px rgba(0, 70, 171, 0.15);
    }

    .form-control-premium {
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.25s ease;
        background-color: #f8fafc;
    }

    .form-control-premium:focus {
        border-color: #0046ab;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(0, 70, 171, 0.1);
        outline: none;
    }

    .file-chip, .validation-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .file-chip { background: #f0f7ff; color: #0046ab; }
    .validation-badge { background: #f0f7ff; color: #0046ab; }
    .validation-badge.invalid { background: #fef2f2; color: #b91c1c; }

    .progress-wrap { display:none; margin-top: 20px; }
    .progress-track { width:100%; height:12px; background:#e2e8f0; border-radius:999px; overflow:hidden; }
    .progress-bar { width:0%; height:100%; background:linear-gradient(135deg,#0046ab 0%,#0061f2 100%); }

    .btn-premium-primary {
        background: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
        color: #fff;
        border: none;
        border-radius: 14px;
        padding: 12px 28px;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(0, 70, 171, 0.2);
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-premium-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px rgba(0, 70, 171, 0.3);
        color: #fff;
    }

    .btn-premium-light {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 28px;
        font-weight: 700;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-premium-light:hover {
        background: #e2e8f0;
        color: #1e293b;
    }

    @media (min-width: 992px) {
        .col-lg-6.border-end-lg {
            border-right: 1px solid #e2e8f0 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="video-upload-card">
    <div class="video-upload-header d-flex justify-content-between align-items-center gap-3">
        <div>
            <h1 class="h4 fw-bold mb-1">Đăng video mới</h1>
            <div class="text-slate-500">Đăng tải thước phim trải nghiệm hoặc liên kết từ YouTube lên Góc video.</div>
        </div>
        <a href="{{ route('admin.videos.index') }}" class="btn btn-light rounded-3 px-3"><i class="fa-solid fa-arrow-left me-1"></i> Quay lại</a>
    </div>

    <div class="p-4 p-md-5">
        <form id="adminVideoUploadForm" action="{{ route('admin.videos.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-4 g-lg-5">
                <!-- LEFT COLUMN: Media Source & Uploads -->
                <div class="col-lg-6 pe-lg-5 col-lg-6 border-end-lg">
                    <!-- Image Upload Area -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-slate-800 fs-6 mb-2">1. Ảnh đại diện (Thumbnail)</label>
                        <div class="dropzone-area" id="thumbnailDropzone">
                            <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="d-none">
                            <div class="dropzone-inner text-center border border-dashed rounded-4 cursor-pointer">
                                <i class="fa-regular fa-image text-primary fs-4 mb-1"></i>
                                <div class="fw-bold text-slate-800 text-xs" id="thumbnailInfo">Nhấn vào đây để chọn ảnh</div>
                                <div class="text-slate-400" style="font-size: 10px;">Hỗ trợ JPG, PNG, WEBP tối đa 2MB</div>
                            </div>
                        </div>
                    </div>

                    <!-- Video Upload Area -->
                    <div>
                        <label class="form-label fw-bold text-slate-800 fs-6 mb-2">2. Nguồn phát Video</label>
                        
                        <!-- Source switcher tabs -->
                        <div class="d-flex bg-slate-100 p-1 rounded-3 mb-3 border border-slate-200">
                            <button type="button" id="tab-mp4" class="btn btn-sm flex-fill source-tab active">
                                <i class="fa-solid fa-file-video me-1"></i> Tệp MP4 nội bộ
                            </button>
                            <button type="button" id="tab-youtube" class="btn btn-sm flex-fill source-tab">
                                <i class="fa-brands fa-youtube me-1"></i> Đường dẫn YouTube
                            </button>
                        </div>

                        <!-- Panel: Local File Upload -->
                        <div id="panel-mp4" class="video-source-panel">
                            <div class="dropzone-area" id="videoDropzone">
                                <input type="file" name="video" id="video" accept=".mp4,.mkv,video/mp4,video/x-matroska" class="d-none">
                                <div class="dropzone-inner text-center border border-dashed rounded-4 cursor-pointer">
                                    <i class="fa-solid fa-cloud-arrow-up text-primary fs-4 mb-1"></i>
                                    <div class="fw-bold text-slate-800 text-xs" id="videoInfo">Nhấn để chọn tệp video</div>
                                    <div class="text-slate-400 mb-1" style="font-size: 10px;">Định dạng MP4, MKV tối đa 100MB</div>
                                    <div class="validation-badge d-inline-flex py-1 px-2" id="videoValidationBadge" style="font-size: 9px; border-radius: 6px;">
                                        <i class="fa-solid fa-circle-info me-1"></i><span>Chờ chọn video</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel: YouTube Link -->
                        <div id="panel-youtube" class="video-source-panel d-none">
                            <div class="p-3 border rounded-4 bg-slate-50 d-flex flex-column justify-content-center" style="height: 120px; border-style: dashed !important; border-color: #cbd5e1 !important; border-width: 2px !important;">
                                <label class="form-label fw-bold small text-slate-600 mb-1">Đường dẫn YouTube</label>
                                <div class="input-group mb-1">
                                    <span class="input-group-text bg-white border-end-0 text-danger rounded-start-3" style="border: 1px solid #cbd5e1; padding: 4px 8px;"><i class="fa-brands fa-youtube fs-6"></i></span>
                                    <input type="text" name="youtube_url" id="youtube_url" class="form-control form-control-premium border-start-0 rounded-end-3 py-1.5 px-3 text-xs" placeholder="https://www.youtube.com/watch?v=..." value="{{ old('youtube_url') }}">
                                </div>
                                <div class="text-slate-400" style="font-size: 10px; line-height: 1.2;">
                                    Tự động quét ảnh đại diện từ YouTube nếu bỏ trống ảnh ở trên.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: Metadata & Settings -->
                <div class="col-lg-6 ps-lg-5 d-flex flex-column justify-content-between">
                    <div>
                        <label class="form-label fw-bold text-slate-800 fs-6 mb-3">3. Thông tin mô tả</label>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-slate-600 small">Tiêu đề video <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-premium" placeholder="Nhập tiêu đề giới thiệu video..." value="{{ old('title') }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-slate-600 small">Danh mục sản phẩm</label>
                            <select name="category_id" class="form-select form-control-premium">
                                <option value="">-- Chọn danh mục sản phẩm --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}" {{ old('category_id') == $category->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <input type="hidden" name="duration" id="auto_duration" value="0:00">

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-600 small">Mô tả chi tiết</label>
                            <textarea name="description" class="form-control form-control-premium" rows="4" placeholder="Viết một vài dòng mô tả ngắn về nội dung video này..." style="resize: none;">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Progress Info -->
            <div class="progress-wrap" id="uploadProgressWrap">
                <div class="d-flex justify-content-between small fw-bold mb-2">
                    <span><i class="fa-solid fa-spinner fa-spin me-1"></i> Đang tải lên hệ thống...</span>
                    <span id="uploadProgressText">0%</span>
                </div>
                <div class="progress-track"><div id="uploadProgressBar" class="progress-bar"></div></div>
            </div>

            <!-- Form actions buttons -->
            <div class="d-flex justify-content-end gap-2 mt-5 pt-4 border-top">
                <a href="{{ route('admin.videos.index') }}" class="btn-premium-light">Hủy bỏ</a>
                <button type="submit" id="uploadSubmitBtn" class="btn-premium-primary">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Đăng video
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

    const MAX_SIZE = 100 * 1024 * 1024;
    const ALLOWED_MIME = ['video/mp4', 'video/x-matroska'];

    // Dropzone elements click triggers
    document.getElementById('thumbnailDropzone')?.addEventListener('click', function() {
        thumbnailInput?.click();
    });
    document.getElementById('videoDropzone')?.addEventListener('click', function() {
        fileInput?.click();
    });

    // Source Switcher logic
    const tabMp4 = document.getElementById('tab-mp4');
    const tabYoutube = document.getElementById('tab-youtube');
    const panelMp4 = document.getElementById('panel-mp4');
    const panelYoutube = document.getElementById('panel-youtube');
    const youtubeInput = document.getElementById('youtube_url');

    tabMp4?.addEventListener('click', function() {
        tabMp4.classList.add('active');
        tabYoutube.classList.remove('active');
        panelMp4.classList.remove('d-none');
        panelYoutube.classList.add('d-none');
    });

    tabYoutube?.addEventListener('click', function() {
        tabYoutube.classList.add('active');
        tabMp4.classList.remove('active');
        panelYoutube.classList.remove('d-none');
        panelMp4.classList.add('d-none');
    });

    // Live Thumbnail preview and description logic
    thumbnailInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        const inner = document.getElementById('thumbnailDropzone').querySelector('.dropzone-inner');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                inner.style.backgroundImage = `url(${e.target.result})`;
                inner.classList.add('has-preview');
            };
            reader.readAsDataURL(file);
            if (thumbnailInfo) thumbnailInfo.textContent = 'Ảnh đã chọn: ' + file.name;
        } else {
            inner.style.backgroundImage = 'none';
            inner.classList.remove('has-preview');
            if (thumbnailInfo) thumbnailInfo.textContent = 'Nhấn vào đây để chọn ảnh';
        }
    });

    // Local Video file detail and validation logic
    fileInput?.addEventListener('change', function () {
        const file = this.files?.[0];
        if (videoInfo) videoInfo.innerHTML = file ? 'Tệp đã chọn: ' + file.name : 'Nhấn để chọn tệp video';
        if (!videoValidationBadge) return;
        if (!file) {
            videoValidationBadge.classList.remove('invalid');
            videoValidationBadge.innerHTML = '<i class="fa-solid fa-circle-info me-1"></i><span>Chờ chọn video</span>';
            return;
        }
        const valid = ALLOWED_MIME.includes(file.type) && file.size <= MAX_SIZE;
        videoValidationBadge.classList.toggle('invalid', !valid);
        videoValidationBadge.innerHTML = valid 
            ? '<i class="fa-solid fa-circle-check me-1"></i><span>Video hợp lệ • ' + Math.round(file.size / 1024 / 1024 * 100) / 100 + ' MB</span>' 
            : '<i class="fa-solid fa-triangle-exclamation me-1"></i><span>' + (!ALLOWED_MIME.includes(file.type) ? 'Chỉ hỗ trợ .mp4 hoặc .mkv.' : 'Tệp quá 100MB.') + '</span>';

        if (valid) {
            const videoEl = document.createElement('video');
            videoEl.preload = 'metadata';
            videoEl.onloadedmetadata = function() {
                window.URL.revokeObjectURL(videoEl.src);
                const duration = videoEl.duration;
                const minutes = Math.floor(duration / 60);
                const seconds = Math.floor(duration % 60);
                const formatted = minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
                const autoDurationInput = document.getElementById('auto_duration');
                if (autoDurationInput) autoDurationInput.value = formatted;
            };
            videoEl.src = URL.createObjectURL(file);
        }
    });

    // Form Submit handling
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const file = fileInput?.files?.[0];
        const isYoutubeActive = tabYoutube.classList.contains('active');
        const youtubeUrl = youtubeInput?.value || '';

        // If local file is selected and active, but empty
        if (!isYoutubeActive && !file) {
            return Swal.fire({
                icon: 'error',
                title: 'Thiếu tệp tin',
                text: 'Vui lòng chọn tệp video (.mp4, .mkv) để tải lên.',
                confirmButtonColor: '#0046ab'
            });
        }

        // If YouTube tab is active, but URL is empty
        if (isYoutubeActive && youtubeUrl.trim() === '') {
            return Swal.fire({
                icon: 'error',
                title: 'Thiếu đường dẫn',
                text: 'Vui lòng điền liên kết YouTube phát từ xa.',
                confirmButtonColor: '#0046ab'
            });
        }

        if (!isYoutubeActive && file) {
            if (file.size > MAX_SIZE) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Tệp quá lớn',
                    text: 'Video không được vượt quá 100MB.',
                    confirmButtonColor: '#0046ab'
                });
            }
            if (!ALLOWED_MIME.includes(file.type)) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Sai định dạng',
                    text: 'Chỉ chấp nhận định dạng .mp4 hoặc .mkv.',
                    confirmButtonColor: '#0046ab'
                });
            }
        }

        // Prepare AJAX request
        const xhr = new XMLHttpRequest();
        const data = new FormData(form);
        
        // If youtube path is active, clear local file upload in the payload
        if (isYoutubeActive) {
            data.delete('video');
        } else {
            // If local path is active, clear youtube url
            data.delete('youtube_url');
        }

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
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: 'Đăng tải video hoàn tất!',
                    timer: 1500,
                    showConfirmButton: false
                });
                setTimeout(() => window.location.href = '{{ route('admin.videos.index') }}', 1500);
                return;
            }

            let errorMsg = 'Upload thất bại, vui lòng thử lại.';
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.errors) {
                    errorMsg = Object.values(response.errors).flat().join('<br>');
                } else if (response.message) {
                    errorMsg = response.message;
                }
            } catch(e) {}

            Swal.fire({
                icon: 'error',
                title: 'Lỗi upload',
                html: errorMsg,
                confirmButtonColor: '#0046ab'
            });
        };

        xhr.onerror = function () {
            submitBtn.disabled = false;
            Swal.fire({
                icon: 'error',
                title: 'Lỗi kết nối',
                text: 'Không thể kết nối đến máy chủ.',
                confirmButtonColor: '#0046ab'
            });
        };

        xhr.send(data);
    });
})();
</script>
@endpush
