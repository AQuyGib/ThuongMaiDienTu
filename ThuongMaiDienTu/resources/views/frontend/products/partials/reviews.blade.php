{{-- resources/views/frontend/products/partials/reviews.blade.php --}}

@push('styles')
<script>
    // Phục hồi vị trí cuộn trang (scroll position) tức thời ngay khi HTML đang tải để tránh giật màn hình
    (function() {
        const scrollY = sessionStorage.getItem('scroll_y_position');
        if (scrollY !== null) {
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }
            window.scrollTo(0, parseInt(scrollY));
        }
    })();
</script>
<!-- CSS TÙY CHỈNH HỆ THỐNG ĐÁNH GIÁ (REVIEWS & RATING SYSTEM) CỦA DIENMAYPRO -->
<style>
.pd-reviews { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:24px; margin-bottom:24px; }
.pd-reviews h2 { font-size:18px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:8px; text-transform: uppercase;}
.review-stats { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
.review-average { text-align: center; }
.review-average h3 { font-size: 32px; color: #d70018; margin-bottom: 5px; }
.review-average .stars { color: #f59e0b; font-size: 14px; }
.review-item { padding: 15px 0; border-bottom: 1px solid #f5f5f5; }
.review-item:last-child { border-bottom: none; }
.review-user { font-weight: 600; font-size: 14px; margin-bottom: 5px; display: flex; align-items: center; gap: 12px; }
.user-info { display: flex; flex-direction: column; }
.user-name { font-size: 16px; font-weight: 700; color: #333; display: flex; align-items: center; gap: 8px; margin-bottom: 2px; }
.user-avatar { width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; flex-shrink: 0; font-size: 18px; text-transform: uppercase; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
/* Màu sắc avatar phân biệt vai trò tài khoản */
.avatar-admin { background: linear-gradient(135deg, #0046ab, #003380); }
.avatar-manager { background: linear-gradient(135deg, #16a34a, #15803d); }
.avatar-default { background: #94a3b8; }
.user-badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600; text-transform: uppercase; }
.badge-admin { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
.badge-manager { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.review-stars { color: #f59e0b; font-size: 12px; margin-bottom: 8px; margin-left: 54px; }
.review-content { font-size: 15px; color: #444; line-height: 1.6; margin-left: 54px; }

/* Tải lên phương tiện đính kèm đánh giá (Media Upload Preview) */
.review-media-upload { margin: 10px 0; }
.media-upload-label { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border: 2px dashed #d0d0d0; border-radius: 8px; cursor: pointer; font-size: 13px; color: #666; transition: 0.2s; background: #fafafa; }
.media-upload-label:hover { border-color: #0046ab; color: #0046ab; background: #eef2ff; }
.media-preview-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.media-preview-item { position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
.media-preview-item img, .media-preview-item video { width: 100%; height: 100%; object-fit: cover; }
.media-preview-remove { position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.6); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; }

/* Hiển thị thư viện ảnh/video của đánh giá */
.review-media-display { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.review-media-thumb { width: 90px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; cursor: pointer; position: relative; }
.review-media-thumb img { width: 100%; height: 100%; object-fit: cover; transition: 0.2s; }
.review-media-thumb:hover img { opacity: 0.85; }
.review-media-thumb .play-icon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.35); color: #fff; font-size: 24px; pointer-events: none; }

/* Phân hệ phản hồi đánh giá (Review Replies) */
.reply-btn { background: none; border: none; color: #0046ab; font-size: 13px; font-weight: 600; cursor: pointer; padding: 0; margin-top: 10px; display: inline-flex; align-items: center; gap: 5px; }
.reply-btn:hover { text-decoration: underline; }
.reply-form { margin-top: 10px; display: none; background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; }
.reply-textarea { width: 100%; height: 100px; padding: 12px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 15px; resize: vertical; font-size: 14px; outline: none; transition: 0.2s; display: block; }
.reply-textarea:focus { border-color: #0046ab; box-shadow: 0 0 0 3px rgba(0,70,171,0.1); }
.reply-author-input { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 12px; font-size: 14px; outline: none; }
.reply-actions { display: flex; justify-content: flex-end; gap: 10px; }
.btn-submit-reply { background: #0046ab; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-size: 14px; }
.btn-submit-reply:hover { background: #003380; transform: translateY(-1px); }
.reply-item { margin-top: 15px; padding: 15px; background: #f8fafc; border-radius: 8px; border-left: 3px solid #cbd5e1; }
.reply-hidden { display: none; }
.btn-show-more-replies { background: none; border: none; color: #666; font-size: 13px; font-weight: 600; cursor: pointer; padding: 10px 0; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; margin-left: 30px; }
.btn-show-more-replies:hover { color: #0046ab; }
.btn-show-more-replies i { transition: transform 0.3s; }
.btn-show-more-replies:hover i { transform: translateY(2px); }

/* Khung hộp thoại xác nhận tùy chỉnh (Custom Confirmation Modal) */
.confirm-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 10001; align-items: center; justify-content: center; backdrop-filter: blur(3px); }
.confirm-modal-overlay.active { display: flex; }
.confirm-modal { background: #fff; width: 90%; max-width: 400px; border-radius: 20px; padding: 25px; text-align: center; animation: modalScale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes modalScale { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.confirm-icon { width: 60px; height: 60px; background: #fee2e2; color: #e21033; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; }
.confirm-modal h4 { margin: 0 0 10px; font-size: 18px; color: #333; }
.confirm-modal p { color: #666; font-size: 14px; margin-bottom: 25px; line-height: 1.5; }
.confirm-actions { display: flex; gap: 12px; }
.confirm-actions button { flex: 1; padding: 12px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; border: none; }
.btn-cancel { background: #f1f5f9; color: #475569; }
.btn-cancel:hover { background: #e2e8f0; }
.btn-confirm-delete { background: #e21033; color: #fff; }
.btn-confirm-delete:hover { background: #b50d29; }

/* Hệ thống hiển thị thông báo Toast đẩy */
#toast-container { position: fixed; top: 20px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 10px; }
.toast { min-width: 280px; background: #fff; padding: 15px 20px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); display: flex; align-items: center; gap: 12px; transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55); border-left: 5px solid #0046ab; }
.toast.active { transform: translateX(0); }
.toast.success { border-left-color: #166534; }
.toast.error { border-left-color: #e21033; }
.toast.warning { border-left-color: #f59e0b; }
.toast-icon { width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.toast.success .toast-icon { background: #dcfce7; color: #166534; }
.toast.error .toast-icon { background: #fee2e2; color: #e21033; }
.toast.warning .toast-icon { background: #fef3c7; color: #f59e0b; }
.toast-content { flex: 1; }
.toast-title { font-weight: 700; font-size: 14px; margin-bottom: 2px; display: block; }
.toast-msg { font-size: 13px; color: #666; }

/* Toast trung tâm (Centered Toast) dành riêng cho kiểm duyệt bình luận */
#toast-center-container { position: fixed; top: 30%; left: 50%; transform: translate(-50%, -50%); z-index: 99999; pointer-events: none; }
.toast-center { background: rgba(0, 70, 171, 0.95); color: #fff; padding: 15px 35px; border-radius: 50px; font-weight: 600; font-size: 15px; box-shadow: 0 10px 30px rgba(0,70,171,0.3); opacity: 0; transform: translateY(20px); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center; gap: 12px; white-space: nowrap; border: 1px solid rgba(255,255,255,0.2); }
.toast-center.active { opacity: 1; transform: translateY(0); }
.toast-center i { color: #fff; font-size: 20px; }

/* Lightbox phóng to ảnh / video khi xem chi tiết */
#mediaLightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 99999; align-items: center; justify-content: center; cursor: pointer; }
#mediaLightbox.active { display: flex; }
#mediaLightbox img, #mediaLightbox video { max-width: 90%; max-height: 90%; border-radius: 12px; object-fit: contain; cursor: default; }
.lightbox-close { position: absolute; top: 20px; right: 25px; color: #fff; font-size: 28px; cursor: pointer; z-index: 100000; background: rgba(0,0,0,0.5); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
.lightbox-close:hover { background: rgba(255,255,255,0.2); }
.review-media-display { margin-left: 54px; }

/* Vòng xoay Spinner tải dữ liệu */
.spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,.3); border-radius: 50%; border-top-color: #fff; animation: spin 0.8s linear infinite; vertical-align: middle; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ===== KEBAB MENU (3 chấm dọc) ===== */
.review-kebab-wrapper { position: relative; margin-left: auto; }
.kebab-trigger { background: none; border: none; cursor: pointer; padding: 4px 8px; border-radius: 6px; color: #94a3b8; font-size: 16px; transition: 0.2s; line-height: 1; }
.kebab-trigger:hover { background: #f1f5f9; color: #475569; }
.kebab-dropdown { display: none; position: absolute; right: 0; top: 100%; margin-top: 4px; background: #fff; border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; min-width: 160px; z-index: 100; overflow: hidden; animation: kebabFade 0.15s ease; }
.kebab-dropdown.active { display: block; }
@keyframes kebabFade { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
.kebab-dropdown button { display: flex; align-items: center; gap: 10px; width: 100%; padding: 10px 16px; border: none; background: none; font-size: 13px; font-weight: 500; cursor: pointer; color: #334155; transition: 0.15s; }
.kebab-dropdown button:hover { background: #f8fafc; }
.kebab-dropdown button.kebab-delete { color: #ef4444; }
.kebab-dropdown button.kebab-delete:hover { background: #fef2f2; }
.kebab-dropdown button i { width: 16px; text-align: center; font-size: 13px; }
.kebab-dropdown .kebab-divider { height: 1px; background: #f1f5f9; margin: 2px 0; }

/* ===== EDIT REVIEW MODAL ===== */
.edit-review-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 10002; align-items: center; justify-content: center; backdrop-filter: blur(3px); }
.edit-review-overlay.active { display: flex; }
.edit-review-modal { background: #fff; width: 92%; max-width: 500px; border-radius: 16px; padding: 28px; animation: modalScale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.edit-review-modal h4 { font-size: 17px; font-weight: 700; margin: 0 0 18px; display: flex; align-items: center; gap: 10px; color: #1e293b; }
.edit-review-modal h4 i { color: #0046ab; }
.edit-star-container { display: flex; gap: 8px; font-size: 22px; color: #ccc; cursor: pointer; margin-bottom: 14px; }
.edit-star-container .fa-star.selected { color: #f59e0b; }
.edit-review-textarea { width: 100%; height: 110px; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 14px; resize: vertical; outline: none; transition: 0.2s; font-family: inherit; }
.edit-review-textarea:focus { border-color: #0046ab; box-shadow: 0 0 0 3px rgba(0,70,171,0.1); }
.edit-review-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px; }
.edit-review-actions button { padding: 10px 22px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; transition: 0.2s; }
</style>
@endpush

<div id="reviews-section" class="pd-reviews">
    @php
        // Đặt giá trị mặc định cho điểm số trung bình và số lượng đánh giá nếu controller không truyền sang
        $avgRating = $avgRating ?? 5;
        $reviewCount = $reviewCount ?? 0;
        $reviews = $reviews ?? collect();
    @endphp
    <h2><i class="fa-solid fa-comments" style="color:#0046ab"></i> Đánh giá & Nhận xét</h2>
    
    <!-- 1. KHỐI THỐNG KÊ ĐIỂM SỐ TRUNG BÌNH (RATING STATS OVERVIEW) -->
    <div class="review-stats">
        <div class="review-average">
            <h3 id="avgReviewScore">{{ round($avgRating, 1) }}/5</h3>
            <div class="stars" id="avgReviewStars">
                <!-- Render động các ngôi sao đã tô và chưa tô màu -->
                @for($i=1; $i<=5; $i++)
                    @if($i <= round($avgRating))
                        <i class="fa-solid fa-star" style="color:#f59e0b"></i>
                    @else
                        <i class="fa-regular fa-star" style="color:#ccc"></i>
                    @endif
                @endfor
            </div>
            <p style="font-size:12px; color:#666; margin-top:5px;" id="totalReviewCount">{{ $reviewCount }} đánh giá</p>
        </div>
        <div style="flex:1;">
            <p style="font-size:14px; color:#555;" id="reviewStatusText">
                @if($reviewCount > 0)
                    Đã có {{ $reviewCount }} đánh giá cho sản phẩm này.
                @else
                    Chưa có đánh giá nào. Hãy là người đầu tiên đánh giá sản phẩm này!
                @endif
            </p>
        </div>
    </div>
    
    <!-- 2. FORM ĐĂNG ĐÁNH GIÁ (Chỉ hiển thị cho thành viên đã đăng nhập hệ thống) -->
    @if(auth()->check())
    <div class="review-form" style="margin-bottom: 25px; background: #f9f9f9; padding: 20px; border-radius: 10px;">
        <h4 style="margin-bottom: 15px; font-size: 15px;">Viết đánh giá của bạn</h4>
        <!-- Khu vực chọn số sao đánh giá nhanh bằng tương tác Click -->
        <div style="margin-bottom: 10px; display: flex; gap: 10px; color: #ccc; font-size: 20px; cursor: pointer;" id="starRatingContainer">
            <i class="fa-solid fa-star star-rating" data-val="1"></i>
            <i class="fa-solid fa-star star-rating" data-val="2"></i>
            <i class="fa-solid fa-star star-rating" data-val="3"></i>
            <i class="fa-solid fa-star star-rating" data-val="4"></i>
            <i class="fa-solid fa-star star-rating" data-val="5"></i>
        </div>
        <textarea id="reviewText" placeholder="Nhập đánh giá của bạn về sản phẩm này..." style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; resize: none;"></textarea>

        <!-- Trình tải lên hình ảnh hoặc video thực tế đi kèm review -->
        <div class="review-media-upload">
            <label class="media-upload-label" for="reviewMediaInput">
                <i class="fa-solid fa-photo-film"></i>
                Thêm ảnh / video (tối đa 5 ảnh, video < 100MB)
            </label>
            <input type="file" id="reviewMediaInput" multiple accept="image/*,video/*" style="display:none;" onchange="previewMediaFiles(this)">
        </div>
        <!-- Vùng lưới hiển thị xem trước (preview) các tệp vừa chọn trước khi submit -->
        <div class="media-preview-grid" id="mediaPreviewGrid"></div>

        <button type="button" onclick="submitReview()" class="btn-submit-review" style="background: #0046ab; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px;">Gửi đánh giá</button>
    </div>
    @else
    <!-- Hộp gợi ý yêu cầu Đăng nhập để sử dụng tính năng đánh giá -->
    <div class="review-login-prompt" style="margin-bottom: 25px; background: #f8fafc; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
        <i class="fa-solid fa-lock" style="font-size: 24px; color: #64748b; margin-bottom: 12px; display: block;"></i>
        <p style="font-size: 15px; color: #334155; margin-bottom: 16px; font-weight: 500;">Vui lòng đăng nhập để viết đánh giá cho sản phẩm này.</p>
        <a href="{{ route('login') }}" class="btn-login-to-review" style="display: inline-block; background: #0046ab; color: #fff; text-decoration: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 14px; transition: 0.2s; box-shadow: 0 4px 12px rgba(0,70,171,0.2);">Đăng nhập ngay</a>
    </div>
    @endif
    
    <!-- 3. DANH SÁCH CÁC ĐÁNH GIÁ ĐÃ CÓ (REVIEWS LIST) -->
    <div class="review-list">
        @if($reviewCount > 0)
            @foreach($reviews as $r)
                <!-- Từng dòng đánh giá riêng lẻ -->
                <div class="review-item" id="review-{{ $r->id }}">
                    <div class="review-user">
                        @php
                            // Kiểm tra vai trò của người viết đánh giá (Admin, Manager, Khách hàng)
                            $isReviewAdmin = $r->user && $r->user->role_id == 1;
                            $isReviewManager = $r->user && $r->user->role_id == 2;
                            $displayName = $r->author_name ?? ($r->user ? $r->user->full_name : 'Khách hàng');
                            $firstLetter = mb_substr($displayName, 0, 1, 'UTF-8');
                        @endphp
                        
                        <!-- Avatar tròn nổi bật của người đánh giá -->
                        <div class="user-avatar {{ $isReviewAdmin ? 'avatar-admin' : ($isReviewManager ? 'avatar-manager' : 'avatar-default') }}">
                            @if($isReviewAdmin || $isReviewManager)
                                <i class="fa-solid fa-bolt-lightning"></i>
                            @else
                                {{ $firstLetter }}
                            @endif
                        </div>

                        <!-- Thông tin tên, nhãn quyền hạn (badge) và ngày giờ viết đánh giá -->
                        <div class="user-info" style="flex:1;">
                            <div class="user-name">
                                {{ $displayName }}
                                @if($isReviewAdmin)
                                    <span class="user-badge badge-admin">Admin</span>
                                @elseif($isReviewManager)
                                    <span class="user-badge badge-manager">Quản lý</span>
                                @endif
                                <span style="color:#94a3b8; font-size:12px; font-weight:400; margin-left:8px;">{{ $r->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        <!-- Menu 3 chấm dọc (Kebab Menu) cho chủ sở hữu hoặc Admin -->
                        @if(auth()->check() && (auth()->id() === $r->user_id || in_array(auth()->user()->role_id, [1, 2])))
                        <div class="review-kebab-wrapper">
                            <button type="button" class="kebab-trigger" onclick="toggleKebab(this, event)" title="Tùy chọn">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="kebab-dropdown">
                                @if(auth()->id() === $r->user_id)
                                <button type="button" class="kebab-edit-btn" data-id="{{ $r->id }}" data-rating="{{ $r->rating }}" data-content="{{ htmlspecialchars($r->content) }}" data-media="{{ json_encode($r->media ?? []) }}" data-is-reply="false" onclick="openEditReview(this)">
                                    <i class="fa-solid fa-pen"></i> Chỉnh sửa
                                </button>
                                <div class="kebab-divider"></div>
                                @endif
                                <button type="button" class="kebab-delete" onclick="deleteReview({{ $r->id }})">
                                    <i class="fa-solid fa-trash-can"></i> Xóa
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                    <!-- Số sao đánh giá -->
                    <div class="review-stars">
                        @for($i=1; $i<=5; $i++)
                            <i class="{{ $i <= $r->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                        @endfor
                    </div>
                    <!-- Nội dung bình luận chính -->
                    <div class="review-content">{{ $r->content }}</div>
                    
                    <!-- Danh sách hình ảnh / video đính kèm nếu có -->
                    @if(!empty($r->media))
                    <div class="review-media-display">
                        @foreach($r->media as $mediaUrl)
                            @php $isVideo = preg_match('/\.(mp4|mov|avi|mkv)$/i', $mediaUrl); @endphp
                            <div class="review-media-thumb" onclick="openMediaLightbox('{{ $mediaUrl }}', {{ $isVideo ? 'true' : 'false' }})">
                                @if($isVideo)
                                    <video src="{{ $mediaUrl }}"></video>
                                    <div class="play-icon"><i class="fa-solid fa-play"></i></div>
                                @else
                                    <img src="{{ $mediaUrl }}" loading="lazy">
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Nút thao tác phụ trợ: Phản hồi hoặc Báo cáo vi phạm -->
                    @if(auth()->check())
                    <div class="review-actions-bar" style="display: flex; gap: 15px; margin-top: 10px; margin-left: 54px;">
                        <button class="reply-btn" style="margin-top: 0;" onclick="toggleReplyForm({{ $r->id }})"><i class="fa-solid fa-reply"></i> Trả lời</button>
                        <button type="button" class="report-btn" style="background: none; border: none; color: #dc2626; font-size: 13px; font-weight: 600; cursor: pointer; padding: 0; display: inline-flex; align-items: center; gap: 5px;" onclick="reportReview({{ $r->id }})" title="Báo cáo đánh giá vi phạm">
                            <i class="fa-solid fa-flag"></i> Báo cáo
                        </button>
                    </div>

                    <!-- Khung nhập phản hồi (Reply Form) ẩn/hiện động bên dưới bình luận chính -->
                    <div class="reply-form" id="replyForm-{{ $r->id }}">
                        <textarea id="replyText-{{ $r->id }}" placeholder="Nhập câu trả lời..." class="reply-textarea"></textarea>
                        
                        <!-- Vùng hiển thị xem trước tệp đa phương tiện tải lên cho câu trả lời -->
                        <div class="media-preview-grid" id="replyMediaPreviewGrid-{{ $r->id }}" style="margin-bottom: 10px;"></div>
                        
                        <div class="reply-actions" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; width: 100%;">
                            <!-- Nút upload tệp đi kèm cho reply -->
                            <div>
                                <label class="media-upload-label" for="replyMediaInput-{{ $r->id }}" style="margin: 0; padding: 6px 12px; font-size: 12px;">
                                    <i class="fa-solid fa-photo-film"></i> Thêm ảnh/video
                                </label>
                                <input type="file" id="replyMediaInput-{{ $r->id }}" multiple accept="image/*,video/*" style="display:none;" onchange="previewReplyMediaFiles({{ $r->id }}, this)">
                            </div>
                            
                            <div style="display: flex; gap: 10px;">
                                <button type="button" onclick="toggleReplyForm({{ $r->id }})" class="btn-cancel">Hủy</button>
                                <button type="button" onclick="submitReply({{ $r->id }})" class="btn-submit-reply">Gửi trả lời</button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- DANH SÁCH CÁC CÂU PHẢN HỒI CON (REPLIES LIST) -->
                    <div class="replies-list" id="replies-{{ $r->id }}">
                        @foreach($r->replies as $index => $reply)
                            <!-- Ẩn các phản hồi từ vị trí số 2 trở đi để tối ưu diện tích trang, hiển thị qua nút xem thêm -->
                            <div class="reply-item {{ $index >= 1 ? 'reply-hidden' : '' }}" id="review-{{ $reply->id }}">
                                <div class="review-user">
                                    @php
                                        $isReplyAdmin = $reply->user && $reply->user->role_id == 1;
                                        $isReplyManager = $reply->user && $reply->user->role_id == 2;
                                        $replyName = $reply->author_name ?? ($reply->user ? $reply->user->full_name : 'Khách hàng');
                                        $replyLetter = mb_substr($replyName, 0, 1, 'UTF-8');
                                    @endphp

                                    <div class="user-avatar {{ $isReplyAdmin ? 'avatar-admin' : ($isReplyManager ? 'avatar-manager' : 'avatar-default') }}" style="width:32px; height:32px; font-size:14px;">
                                        @if($isReplyAdmin || $isReplyManager)
                                            <i class="fa-solid fa-bolt-lightning" style="font-size:16px;"></i>
                                        @else
                                            {{ $replyLetter }}
                                        @endif
                                    </div>

                                    <div class="user-info" style="flex:1;">
                                        <div class="user-name" style="font-size:14px;">
                                            {{ $replyName }}
                                            @if($isReplyAdmin)
                                                <span class="user-badge badge-admin">Admin</span>
                                            @elseif($isReplyManager)
                                                <span class="user-badge badge-manager">Quản lý</span>
                                            @endif
                                            <span style="color:#94a3b8; font-size:11px; font-weight:400; margin-left:8px;">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>

                                    @if(auth()->check() && (auth()->id() === $reply->user_id || in_array(auth()->user()->role_id, [1, 2])))
                                    <div class="review-kebab-wrapper">
                                        <button type="button" class="kebab-trigger" onclick="toggleKebab(this, event)" title="Tùy chọn" style="font-size:14px; padding:2px 6px;">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div class="kebab-dropdown">
                                            @if(auth()->id() === $reply->user_id)
                                            <button type="button" class="kebab-edit-btn" data-id="{{ $reply->id }}" data-rating="{{ $reply->rating }}" data-content="{{ htmlspecialchars($reply->content) }}" data-media="{{ json_encode($reply->media ?? []) }}" data-is-reply="true" onclick="openEditReview(this)">
                                                <i class="fa-solid fa-pen"></i> Chỉnh sửa
                                            </button>
                                            <div class="kebab-divider"></div>
                                            @endif
                                            <button type="button" class="kebab-delete" onclick="deleteReview({{ $reply->id }})">
                                                <i class="fa-solid fa-trash-can"></i> Xóa
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="review-content-replied" style="margin-left:44px; font-size:14px;">{{ $reply->content }}</div>
                                
                                <!-- Tệp đa phương tiện đính kèm câu phản hồi con -->
                                @if(!empty($reply->media))
                                <div class="review-media-display" style="margin-left: 44px; margin-top: 8px;">
                                    @foreach($reply->media as $mediaUrl)
                                        @php $isVideo = preg_match('/\.(mp4|mov|avi|mkv)$/i', $mediaUrl); @endphp
                                        <div class="review-media-thumb" style="width: 70px; height: 70px;" onclick="openMediaLightbox('{{ $mediaUrl }}', {{ $isVideo ? 'true' : 'false' }})">
                                            @if($isVideo)
                                                <video src="{{ $mediaUrl }}"></video>
                                                <div class="play-icon" style="font-size: 18px;"><i class="fa-solid fa-play"></i></div>
                                            @else
                                                <img src="{{ $mediaUrl }}" loading="lazy">
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                                
                                @if(auth()->check())
                                <div class="review-actions-bar" style="display: flex; gap: 15px; margin-top: 5px; margin-left: 44px;">
                                    <button class="reply-btn-nested" style="margin-left:0; background:none; border:none; color:#0046ab; font-size:12px; font-weight:600; cursor:pointer;" onclick="replyToUser({{ $r->id }}, '{{ addslashes($replyName) }}', {{ $reply->id }})"><i class="fa-solid fa-reply"></i> Trả lời</button>
                                    <button type="button" class="report-btn" style="background: none; border: none; color: #dc2626; font-size: 13px; font-weight: 600; cursor: pointer; padding: 0; display: inline-flex; align-items: center; gap: 5px;" onclick="reportReview({{ $reply->id }})" title="Báo cáo bình luận vi phạm">
                                        <i class="fa-solid fa-flag"></i> Báo cáo
                                    </button>
                                </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <!-- Nút bấm hiển thị toàn bộ phản hồi con bị ẩn -->
                    @if($r->replies->count() > 1)
                        <button class="btn-show-more-replies" onclick="showAllReplies({{ $r->id }}, this)">
                            <i class="fa-solid fa-chevron-down"></i> Xem thêm {{ $r->replies->count() - 1 }} phản hồi
                        </button>
                    @endif
                </div>
            @endforeach
        @else
            <p id="noReviewMsg" class="no-reviews" style="text-align: center; color: #94a3b8; padding: 30px; font-style: italic;">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
        @endif
    </div>
</div>

{{-- 4. CÁC HỘP THOẠI MODALS HỆ THỐNG (SYSTEM MODALS) --}}

{{-- Hộp thoại Cảnh báo / Thông báo (Alert Modal) --}}
<div class="confirm-modal-overlay" id="alertModal">
    <div class="confirm-modal">
        <div class="confirm-icon" id="alertIcon" style="background: #fef3c7; color: #f59e0b;">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <h4 id="alertTitle">Thông báo</h4>
        <p id="alertMessage">Vui lòng nhập nội dung!</p>
        <div class="confirm-actions">
            <button type="button" class="btn-confirm-delete" id="alertBtn" style="background: #0046ab;" onclick="closeAlertModal()">Đồng ý</button>
        </div>
    </div>
</div>

{{-- Hộp thoại Xác nhận hành động xóa / báo cáo (Confirm Modal) --}}
<div class="confirm-modal-overlay" id="confirmModal">
    <div class="confirm-modal">
        <div class="confirm-icon">
            <i class="fa-solid fa-trash-can"></i>
        </div>
        <h4 id="confirmTitle">Xác nhận xóa</h4>
        <p id="confirmMessage">Bạn có chắc chắn muốn xóa đánh giá này không? Thao tác này không thể hoàn tác.</p>
        <div class="confirm-actions">
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Hủy bỏ</button>
            <button type="button" id="btnDoConfirm" class="btn-confirm-delete">Xác nhận xóa</button>
        </div>
    </div>
</div>

{{-- Container phục vụ đẩy Toast thông báo --}}
<div id="toast-container"></div>
<div id="toast-center-container"></div>



{{-- Lightbox trình chiếu ảnh/video full màn hình --}}
<div id="mediaLightbox" onclick="closeMediaLightbox()">
    <i class="fa-solid fa-xmark lightbox-close" onclick="closeMediaLightbox()"></i>
    <img id="lightboxImg" src="" alt="" style="display:none;" onclick="event.stopPropagation()">
    <video id="lightboxVideo" src="" controls style="display:none;" onclick="event.stopPropagation()"></video>
</div>

@push('scripts')
<script>
// Phục hồi vị trí cuộn trang (scroll position) tức thời ngay khi HTML đang tải để tránh giật màn hình
(function() {
    const scrollY = sessionStorage.getItem('scroll_y_position');
    if (scrollY !== null) {
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        window.scrollTo(0, parseInt(scrollY));
    }
})();

// Biến toàn cục lưu trữ thông số Rating và File danh sách đăng tải
let currentRating = 5;
let editRating = 5;
let selectedMediaFiles = [];
let replyMediaFiles = {}; // Phục vụ lưu trữ tệp đính kèm theo từng comment cha riêng biệt

/* ===== KEBAB MENU (3 CHẤM DỌC) ===== */
function toggleKebab(btn, event) {
    const dropdown = btn.nextElementSibling;
    // Đóng tất cả kebab khác trước
    document.querySelectorAll('.kebab-dropdown.active').forEach(d => {
        if (d !== dropdown) d.classList.remove('active');
    });
    dropdown.classList.toggle('active');
    if (event) event.stopPropagation();
}

// Đóng kebab khi click ra ngoài
document.addEventListener('click', function(e) {
    if (!e.target.closest('.review-kebab-wrapper')) {
        document.querySelectorAll('.kebab-dropdown.active').forEach(d => d.classList.remove('active'));
    }
});

/* ===== INLINE EDIT REVIEW / REPLY ===== */
function openEditReview(btn) {
    const id = btn.getAttribute('data-id');
    const rating = parseInt(btn.getAttribute('data-rating') || '5');
    const content = btn.getAttribute('data-content');
    const isReply = btn.getAttribute('data-is-reply') === 'true';
    const mediaList = JSON.parse(btn.getAttribute('data-media') || '[]');

    // Đóng kebab menu
    document.querySelectorAll('.kebab-dropdown.active').forEach(d => d.classList.remove('active'));
    
    // Kiểm tra xem có đang sửa chính nó rồi không
    if (document.getElementById('inline-edit-' + id)) {
        return;
    }
    
    // Tìm container chứa đánh giá/bình luận
    const container = document.getElementById('review-' + id);
    if (!container) return;
    
    // Tìm phần tử hiển thị nội dung chữ
    const contentEl = container.querySelector('.review-content, .review-content-replied');
    if (!contentEl) return;
    
    // Tìm phần tử hiển thị sao (chỉ có ở review chính)
    const starsEl = container.querySelector('.review-stars');

    // Tìm phần tử hiển thị ảnh cũ tĩnh bên dưới bình luận/đánh giá
    const mediaEl = container.querySelector('.review-media-display');

    // Tìm thanh công cụ phản hồi / báo cáo
    const actionsBar = container.querySelector('.review-actions-bar');
    
    // Ẩn nội dung cũ
    contentEl.style.display = 'none';
    if (starsEl) starsEl.style.display = 'none';
    if (mediaEl) mediaEl.style.display = 'none';
    if (actionsBar) actionsBar.style.display = 'none';
    
    // Tạo form chỉnh sửa inline
    const editForm = document.createElement('div');
    editForm.className = 'inline-edit-form';
    editForm.id = 'inline-edit-' + id;
    editForm.style.marginTop = '10px';
    editForm.style.background = '#f8fafc';
    editForm.style.padding = '15px';
    editForm.style.borderRadius = '12px';
    editForm.style.border = '1px solid #e2e8f0';
    if (isReply) {
        editForm.style.marginLeft = '44px'; // Thụt lề bằng với avatar reply con
    } else {
        editForm.style.marginLeft = '54px'; // Thụt lề bằng với avatar review chính
    }
    
    let starsHtml = '';
    let currentInlineRating = rating;
    if (!isReply) {
        starsHtml = `
            <div class="inline-edit-stars" style="display: flex; gap: 8px; font-size: 20px; color: #ccc; cursor: pointer; margin-bottom: 12px;">
                ${[1, 2, 3, 4, 5].map(v => `
                    <i class="fa-solid fa-star ${v <= rating ? 'selected' : ''}" data-val="${v}" style="color: ${v <= rating ? '#f59e0b' : '#ccc'}"></i>
                `).join('')}
            </div>
        `;
    }
    
    // Render existing media files
    let existingMediaHtml = '';
    if (mediaList && mediaList.length > 0) {
        existingMediaHtml = `
            <div class="inline-edit-existing-media" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
                ${mediaList.map(url => {
                    const isVideo = /\.(mp4|mov|avi|mkv)$/i.test(url);
                    return `
                        <div class="media-preview-item" data-url="${url}" style="position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb;">
                            ${isVideo ? `<video src="${url}" style="width: 100%; height: 100%; object-fit: cover;"></video>` : `<img src="${url}" style="width: 100%; height: 100%; object-fit: cover;">`}
                            <button type="button" class="media-preview-remove" onclick="this.parentNode.remove()" style="position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.6); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center;">×</button>
                        </div>
                    `;
                }).join('')}
            </div>
        `;
    }

    const uploadInputId = 'inline-media-input-' + id;
    const previewGridId = 'inline-media-preview-' + id;
    let localSelectedFiles = []; // Lưu trữ file tải lên mới
    
    let uploadHtml = `
        <div class="review-media-upload" style="margin-bottom: 10px;">
            <label class="media-upload-label" for="${uploadInputId}" style="margin: 0; padding: 6px 12px; font-size: 12px; border: 1px dashed #ccc; border-radius: 6px; background: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-photo-film"></i> Thêm ảnh/video mới
            </label>
            <input type="file" id="${uploadInputId}" multiple accept="image/*,video/*" style="display:none;">
            <div class="media-preview-grid" id="${previewGridId}" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 8px;"></div>
        </div>
    `;
    
    editForm.innerHTML = `
        ${starsHtml}
        <textarea class="reply-textarea" style="margin-bottom: 10px; height: 80px; resize: none; width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; display: block;">${content}</textarea>
        ${existingMediaHtml}
        ${uploadHtml}
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <button type="button" class="btn-cancel" style="padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none;">Hủy</button>
            <button type="button" class="btn-submit-reply" style="padding: 8px 24px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none;">Lưu thay đổi</button>
        </div>
    `;
    
    // Chèn form chỉnh sửa ngay trước nội dung hiển thị cũ
    contentEl.parentNode.insertBefore(editForm, contentEl);
    
    // Tự động focus và di chuyển con trỏ xuống cuối textarea
    const textarea = editForm.querySelector('textarea');
    textarea.focus();
    const len = textarea.value.length;
    textarea.setSelectionRange(len, len);
    
    // Xử lý sự kiện tải lên tệp mới
    const fileInput = editForm.querySelector('#' + uploadInputId);
    const previewGrid = editForm.querySelector('#' + previewGridId);
    
    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        
        files.forEach(file => {
            if (file.type.startsWith('image/') && file.size > 5 * 1024 * 1024) {
                showToastReview('Lỗi file', `Kích thước ảnh "${file.name}" vượt quá 5MB.`, 'warning');
                return;
            }
            if (file.type.startsWith('video/') && file.size > 100 * 1024 * 1024) {
                showToastReview('Lỗi file', `Kích thước video "${file.name}" vượt quá 100MB.`, 'warning');
                return;
            }
            
            localSelectedFiles.push(file);
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'media-preview-item';
                previewItem.setAttribute('data-new', 'true');
                
                const isVid = file.type.startsWith('video/');
                if (isVid) {
                    previewItem.innerHTML = `
                        <video src="${e.target.result}"></video>
                        <button type="button" class="media-preview-remove">×</button>
                    `;
                } else {
                    previewItem.innerHTML = `
                        <img src="${e.target.result}">
                        <button type="button" class="media-preview-remove">×</button>
                    `;
                }
                
                previewItem.querySelector('.media-preview-remove').addEventListener('click', function() {
                    const idx = localSelectedFiles.indexOf(file);
                    if (idx > -1) {
                        localSelectedFiles.splice(idx, 1);
                     }
                    previewItem.remove();
                });
                
                previewGrid.appendChild(previewItem);
            }
            reader.readAsDataURL(file);
        });
        
        this.value = '';
    });

    // Xử lý sự kiện click đổi sao (nếu có)
    if (!isReply) {
        editForm.querySelectorAll('.inline-edit-stars i').forEach(star => {
            star.addEventListener('click', function() {
                currentInlineRating = parseInt(this.getAttribute('data-val'));
                editForm.querySelectorAll('.inline-edit-stars i').forEach(s => {
                    const val = parseInt(s.getAttribute('data-val'));
                    s.style.color = val <= currentInlineRating ? '#f59e0b' : '#ccc';
                    s.classList.toggle('selected', val <= currentInlineRating);
                });
            });
        });
    }
    
    // Xử lý nút Hủy
    editForm.querySelector('.btn-cancel').addEventListener('click', function() {
        editForm.remove();
        contentEl.style.display = '';
        if (starsEl) starsEl.style.display = '';
        if (mediaEl) mediaEl.style.display = '';
        if (actionsBar) actionsBar.style.display = '';
    });
    
    // Xử lý nút Lưu thay đổi
    editForm.querySelector('.btn-submit-reply').addEventListener('click', function() {
        const newContent = textarea.value.trim();
        if (!newContent) {
            showToastReview('Thiếu nội dung', 'Vui lòng nhập nội dung!', 'warning');
            return;
        }

        const retainedMedia = [];
        editForm.querySelectorAll('.inline-edit-existing-media .media-preview-item').forEach(item => {
            retainedMedia.push(item.getAttribute('data-url'));
        });

        // Validate số lượng ảnh (cũ + mới) không vượt quá 5
        const totalImages = retainedMedia.filter(url => !/\.(mp4|mov|avi|mkv)$/i.test(url)).length 
            + localSelectedFiles.filter(f => f.type.startsWith('image/')).length;
        if (totalImages > 5) {
            showToastReview('Lỗi', 'Bạn chỉ được phép lưu trữ tối đa 5 hình ảnh.', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('rating', currentInlineRating);
        formData.append('content', newContent);
        
        retainedMedia.forEach(url => {
            formData.append('retained_media[]', url);
        });
        
        localSelectedFiles.forEach(file => {
            formData.append('media[]', file);
        });
        
        const saveBtn = this;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang lưu...';
        
        fetch(`/reviews/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                const err = new Error(data.message || 'Lỗi hệ thống');
                err.status = response.status;
                throw err;
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                // Cập nhật các thuộc tính data trên nút Chỉnh sửa để lần sau mở form hiển thị đúng dữ liệu mới nhất
                btn.setAttribute('data-rating', currentInlineRating);
                btn.setAttribute('data-content', newContent);
                btn.setAttribute('data-media', JSON.stringify(data.media || []));

                // Cập nhật text content hiển thị trực tiếp
                contentEl.textContent = newContent;

                // Cập nhật sao hiển thị (chỉ với review chính)
                if (starsEl) {
                    starsEl.innerHTML = [1, 2, 3, 4, 5].map(v => 
                        `<i class="${v <= currentInlineRating ? 'fa-solid' : 'fa-regular'} fa-star"></i>`
                    ).join('');
                }

                // Cập nhật danh sách ảnh/video đính kèm
                let mediaContainer = container.querySelector('.review-media-display');
                const updatedMedia = data.media || [];
                if (updatedMedia.length > 0) {
                    if (!mediaContainer) {
                        mediaContainer = document.createElement('div');
                        mediaContainer.className = 'review-media-display';
                        contentEl.parentNode.insertBefore(mediaContainer, contentEl.nextSibling);
                    }
                    mediaContainer.innerHTML = updatedMedia.map(url => {
                        const isVideo = /\.(mp4|mov|avi|mkv)$/i.test(url);
                        return `
                            <div class="review-media-thumb" onclick="openMediaLightbox('${url}', ${isVideo})">
                                ${isVideo ? `<video src="${url}"></video><div class="play-icon"><i class="fa-solid fa-play"></i></div>` : `<img src="${url}" loading="lazy">`}
                            </div>
                        `;
                    }).join('');
                    mediaContainer.style.display = '';
                } else {
                    if (mediaContainer) mediaContainer.remove();
                }

                // Nếu bình luận chuyển sang chờ kiểm duyệt
                if (!data.is_approved) {
                    container.style.opacity = '0.6';
                    // Thêm badge chờ duyệt hoặc chỉnh badge cũ
                    let badge = container.querySelector('.user-badge');
                    if (badge) {
                        badge.outerHTML = '<span class="user-badge" style="background:#f59e0b; color:#fff;">Chờ duyệt</span>';
                    } else {
                        const userMeta = container.querySelector('.user-meta');
                        if (userMeta) {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'user-badge';
                            newBadge.style.background = '#f59e0b';
                            newBadge.style.color = '#fff';
                            newBadge.textContent = 'Chờ duyệt';
                            userMeta.appendChild(newBadge);
                        }
                    }
                    showToastReview('Chờ kiểm duyệt', data.message, 'warning');
                } else {
                    // Nếu được duyệt thẳng, đảm bảo bỏ độ mờ và badge Chờ duyệt nếu có
                    container.style.opacity = '1';
                    let badge = container.querySelector('.user-badge');
                    if (badge && badge.textContent === 'Chờ duyệt') {
                        badge.remove();
                    }
                    showToastReview('Thành công', data.message || 'Cập nhật thành công!', 'success');
                }

                // Đóng form edit và hiện lại nội dung tĩnh
                editForm.remove();
                contentEl.style.display = '';
                if (starsEl) starsEl.style.display = '';
                if (actionsBar) actionsBar.style.display = '';
            } else {
                saveBtn.disabled = false;
                saveBtn.innerHTML = 'Lưu thay đổi';
                showAlert('Lỗi', data.message || 'Không thể cập nhật.');
            }
        })
        .catch(err => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = 'Lưu thay đổi';
            const title = err.status === 403 ? 'Không có quyền' : 'Lỗi';
            showAlert(title, err.message);
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Kiểm tra và hiển thị các Toast thông báo lưu trữ tạm trong sessionStorage sau khi reload trang
    const reviewToast = sessionStorage.getItem('review_toast');
    if (reviewToast) {
        const data = JSON.parse(reviewToast);
        if (data.isCenter) {
            showCenterToast(data.msg);
        } else if (data.isModal) {
            showAlert(data.title, data.msg, 'success');
        } else {
            showToastReview(data.title, data.msg, data.type);
        }
        sessionStorage.removeItem('review_toast');
    }

    // Luôn kiểm tra cờ scroll lưu trữ tạm
    const scrollY = sessionStorage.getItem('scroll_y_position');
    if (scrollY !== null) {
        sessionStorage.removeItem('scroll_y_position');
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        window.scrollTo(0, parseInt(scrollY));
        setTimeout(() => {
            window.scrollTo(0, parseInt(scrollY));
        }, 50);
        setTimeout(() => {
            window.scrollTo(0, parseInt(scrollY));
        }, 150);
    } else if (sessionStorage.getItem('scroll_to_reviews') === 'true') {
        sessionStorage.removeItem('scroll_to_reviews');
        if ('scrollRestoration' in history) {
            history.scrollRestoration = 'manual';
        }
        setTimeout(() => {
            const reviewsSec = document.getElementById('reviews-section');
            if (reviewsSec) {
                reviewsSec.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 150);
    }

    // 2. Thiết lập tương tác và sự kiện cho hệ thống Chọn sao đánh giá
    const stars = document.querySelectorAll('.star-rating');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            currentRating = this.getAttribute('data-val');
            stars.forEach(s => {
                if(s.getAttribute('data-val') <= currentRating) s.style.color = '#f59e0b';
                else s.style.color = '#ccc';
            });
        });
        // Thiết lập màu sắc sao mặc định khi tải trang
        if(star.getAttribute('data-val') <= currentRating) star.style.color = '#f59e0b';
    });
});

/**
 * Hàm: previewMediaFiles
 * Công dụng: Kiểm tra hợp lệ các file ảnh/video vừa tải lên và hiển thị vùng preview.
 * Ràng buộc: Tối đa 5 ảnh, 1 video; dung lượng ảnh < 5MB, video < 100MB.
 */
function previewMediaFiles(input) {
    const grid = document.getElementById('mediaPreviewGrid');
    const files = Array.from(input.files);
    
    files.forEach(file => {
        const isVideo = file.type.startsWith('video/');
        const isImage = file.type.startsWith('image/');
        
        if (!isImage && !isVideo) {
            showAlert('Định dạng không hợp lệ', `Tệp "${file.name}" không phải ảnh hoặc video.`);
            return;
        }

        // Kiểm tra dung lượng tệp
        const limitSize = isVideo ? 100 * 1024 * 1024 : 5 * 1024 * 1024;
        if (file.size > limitSize) {
            const limitLabel = isVideo ? '100MB' : '5MB';
            showAlert('Tệp quá lớn', `Tệp "${file.name}" vượt quá giới hạn ${limitLabel}. Vui lòng chọn tệp nhỏ hơn.`);
            return;
        }

        // Giới hạn số lượng video: Chỉ được phép đính kèm 1 video
        if (isVideo) {
            const hasVideo = selectedMediaFiles.some(f => f && f.type.startsWith('video/'));
            if (hasVideo) {
                showAlert('Giới hạn video', 'Bạn chỉ có thể tải lên tối đa 1 video.');
                return;
            }
        }

        // Giới hạn số lượng hình ảnh: Tối đa 5 ảnh
        if (isImage) {
            const imageCount = selectedMediaFiles.filter(f => f && f.type.startsWith('image/')).length;
            if (imageCount >= 5) {
                showAlert('Giới hạn ảnh', 'Bạn chỉ có thể tải lên tối đa 5 ảnh.');
                return;
            }
        }

        const fileIndex = selectedMediaFiles.length;
        selectedMediaFiles.push(file);
        
        // Sử dụng FileReader để trích xuất DataURL render ảnh preview ngay lập tức
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'media-preview-item';
            div.id = 'preview-item-' + fileIndex;
            
            div.innerHTML = `
                ${isVideo ? `<video src="${e.target.result}"></video>` : `<img src="${e.target.result}">`}
                <button type="button" class="media-preview-remove" onclick="removeMediaFile(${fileIndex})"><i class="fa-solid fa-xmark"></i></button>
            `;
            grid.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
    input.value = ''; // Reset input để cho phép chọn lại cùng 1 file
}

/**
 * Hàm: removeMediaFile
 * Công dụng: Loại bỏ tệp khỏi danh sách tải lên và xóa thẻ giao diện preview tương ứng.
 */
function removeMediaFile(index) {
    const item = document.getElementById('preview-item-' + index);
    if (item) item.remove();
    selectedMediaFiles[index] = null;
}

/**
 * Hàm: previewReplyMediaFiles
 * Công dụng: Xử lý preview tệp tải lên riêng biệt cho khung nhập câu phản hồi (reply).
 */
function previewReplyMediaFiles(parentId, input) {
    const grid = document.getElementById('replyMediaPreviewGrid-' + parentId);
    const files = Array.from(input.files);
    
    if (!replyMediaFiles[parentId]) {
        replyMediaFiles[parentId] = [];
    }
    
    files.forEach(file => {
        const isVideo = file.type.startsWith('video/');
        const isImage = file.type.startsWith('image/');
        
        if (!isImage && !isVideo) {
            showAlert('Định dạng không hợp lệ', `Tệp "${file.name}" không phải ảnh hoặc video.`);
            return;
        }

        const limitSize = isVideo ? 100 * 1024 * 1024 : 5 * 1024 * 1024;
        if (file.size > limitSize) {
            const limitLabel = isVideo ? '100MB' : '5MB';
            showAlert('Tệp quá lớn', `Tệp "${file.name}" vượt quá giới hạn ${limitLabel}. Vui lòng chọn tệp nhỏ hơn.`);
            return;
        }

        if (isVideo) {
            const hasVideo = replyMediaFiles[parentId].some(f => f && f.type.startsWith('video/'));
            if (hasVideo) {
                showAlert('Giới hạn video', 'Bạn chỉ có thể tải lên tối đa 1 video.');
                return;
            }
        }

        const nonNullCount = replyMediaFiles[parentId].filter(Boolean).length;
        if (nonNullCount >= 5) {
            showAlert('Giới hạn tải lên', 'Bạn chỉ được tải lên tối đa 5 hình ảnh hoặc video.');
            return;
        }

        const index = replyMediaFiles[parentId].length;
        replyMediaFiles[parentId].push(file);
        
        const preview = document.createElement('div');
        preview.className = 'media-preview-item';
        preview.id = `reply-media-${parentId}-${index}`;
        
        if (isVideo) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            preview.appendChild(video);
        } else {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            preview.appendChild(img);
        }
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'media-preview-remove';
        removeBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
        removeBtn.onclick = function() {
            preview.remove();
            replyMediaFiles[parentId][index] = null;
        };
        preview.appendChild(removeBtn);
        grid.appendChild(preview);
    });
    
    input.value = '';
}

/**
 * Gửi đánh giá sản phẩm chính lên server qua AJAX.
 * - Kiểm tra tính hợp lệ của nội dung và thông tin người dùng.
 * - Đóng gói dữ liệu đánh giá và mảng file đa phương tiện (ảnh/video) vào FormData.
 * - Thực hiện fetch POST, xử lý phản hồi HTTP 403 đặc biệt khi người dùng bị cấm bình luận.
 * - Lưu trạng thái thông báo và vị trí scroll để hiển thị sau khi reload trang.
 */
function submitReview() {
    const textarea = document.getElementById('reviewText');
    const content = textarea.value.trim();
    const authorInput = document.getElementById('reviewAuthor');
    const authorName = authorInput ? authorInput.value.trim() : null;

    if(!content) { showAlert('Thiếu nội dung', 'Vui lòng nhập nội dung đánh giá của bạn!'); return; }
    if(authorInput && !authorName) { showAlert('Thiếu thông tin', 'Vui lòng nhập tên của bạn để gửi đánh giá!'); return; }

    const formData = new FormData();
    formData.append('product_id', '{{ $product->product_id }}');
    formData.append('rating', currentRating);
    formData.append('content', content);
    if(authorName) formData.append('author_name', authorName);
    
    // Đóng gói danh sách file đính kèm thực tế vào FormData
    selectedMediaFiles.filter(Boolean).forEach(file => {
        formData.append('media[]', file);
    });

    const btn = document.querySelector('.btn-submit-review');
    if(btn) { 
        btn.disabled = true; 
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...'; 
    }

    fetch('{{ route("reviews.store") }}', {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) {
            // Ném lỗi kèm mã status để xử lý phân biệt lỗi vi phạm (403) và lỗi kết nối/máy chủ
            const errObj = new Error(data.message || `Lỗi hệ thống (${response.status})`);
            errObj.status = response.status;
            throw errObj;
        }
        return data;
    })
    .then(data => {
        if(data.success) {
            // Lưu trạng thái để hiển thị thông báo trung tâm đẹp mắt sau khi trang tải lại
            sessionStorage.setItem('review_toast', JSON.stringify({
                msg: data.message || 'DienMayPro cảm ơn quý khách đã gửi bình luận!',
                isCenter: true
            }));
            sessionStorage.setItem('scroll_y_position', window.scrollY);
            location.reload(); 
        } else {
            if(btn) { 
                btn.disabled = false; 
                btn.innerHTML = 'Gửi đánh giá'; 
            }
            showAlert('Lỗi', data.message || 'Lỗi khi gửi đánh giá.');
        }
    })
    .catch(error => {
        if(btn) { 
            btn.disabled = false; 
            btn.innerHTML = 'Gửi đánh giá'; 
        }
        console.error('Submit review error:', error);
        // Hiển thị thông báo thích hợp dựa trên mã HTTP status (403: Bị cấm do từ khóa nhạy cảm / spam)
        const title = error.status === 403 ? 'Vi phạm' : 'Không thể gửi';
        const msg = error.status === 403 ? error.message : ('Đã có lỗi xảy ra: ' + error.message);
        showAlert(title, msg);
    });
}

/**
 * Hàm: toggleReplyForm
 * Công dụng: Ẩn hoặc hiển thị khung soạn phản hồi của từng đánh giá.
 */
function toggleReplyForm(id) {
    const form = document.getElementById('replyForm-' + id);
    const repliesList = document.getElementById('replies-' + id);
    const isHidden = form.style.display === 'none' || form.style.display === '';

    if (isHidden) {
        // Di chuyển khung form đặt trước danh sách các câu trả lời
        repliesList.parentNode.insertBefore(form, repliesList);
        form.style.display = 'block';
        const textarea = document.getElementById('replyText-' + id);
        textarea.value = '';
        textarea.focus();
    } else {
        form.style.display = 'none';
    }
}

/**
 * Hàm: replyToUser
 * Công dụng: Tự động điền tag tên tài khoản và di chuyển khung nhập phản hồi trực tiếp xuống dưới câu trả lời con.
 */
function replyToUser(parentReviewId, userName, specificReplyId) {
    const form = document.getElementById('replyForm-' + parentReviewId);
    const specificReply = document.getElementById('review-' + specificReplyId);
    
    // Di chuyển khung reply xuống dưới vị trí comment con tương ứng
    specificReply.parentNode.insertBefore(form, specificReply.nextSibling);
    
    form.style.display = 'block';
    const textarea = document.getElementById('replyText-' + parentReviewId);
    textarea.value = '@' + userName + ': ';
    textarea.focus();
}

/**
 * Hàm: showAllReplies
 * Công dụng: Hiển thị các câu trả lời phản hồi con đang bị ẩn kèm hiệu ứng chuyển động mượt mà.
 */
function showAllReplies(reviewId, btn) {
    const list = document.getElementById('replies-' + reviewId);
    const hiddenItems = list.querySelectorAll('.reply-hidden');
    hiddenItems.forEach(item => {
        item.classList.remove('reply-hidden');
        item.style.opacity = '0';
        item.style.display = 'block';
        setTimeout(() => {
            item.style.transition = '0.3s';
            item.style.opacity = '1';
        }, 10);
    });
    btn.remove(); // Xóa nút xem thêm sau khi hiển thị hết
}

/**
 * Gửi phản hồi (reply) cho một đánh giá cụ thể qua AJAX.
 * - Đóng gói nội dung, ID cha (parent_id), tên tác giả và các tệp đính kèm đi kèm reply.
 * - Thực hiện fetch POST đến route lưu đánh giá.
 * - Bắt mã HTTP 403 để xử lý trường hợp người dùng bị cấm bình luận/đánh giá.
 * 
 * @param {number} parentId ID của đánh giá cha được phản hồi.
 */
function submitReply(parentId) {
    const textarea = document.getElementById('replyText-' + parentId);
    const text = textarea.value.trim();
    const authorInput = document.getElementById('replyAuthor-' + parentId);
    const authorName = authorInput ? authorInput.value.trim() : null;

    if(!text) { showToastReview('Thiếu nội dung', 'Vui lòng nhập câu trả lời của bạn!', 'warning'); return; }
    
    const formData = new FormData();
    formData.append('product_id', '{{ $product->product_id }}');
    formData.append('parent_id', parentId);
    formData.append('content', text);
    formData.append('rating', 5); // Phản hồi mặc định 5 sao
    if(authorName) formData.append('author_name', authorName);

    // Lấy các file đính kèm riêng cho khung phản hồi hiện tại (parentId)
    if (replyMediaFiles[parentId]) {
        replyMediaFiles[parentId].filter(Boolean).forEach(file => {
            formData.append('media[]', file);
        });
    }

    const btn = document.querySelector(`#replyForm-${parentId} .btn-submit-reply`);
    if(btn) { 
        btn.disabled = true; 
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...'; 
    }

    fetch('{{ route("reviews.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json();
        if (!response.ok) {
            const errObj = new Error(data.message || `Lỗi hệ thống (${response.status})`);
            errObj.status = response.status;
            throw errObj;
        }
        return data;
    })
    .then(data => {
        if(data.success) {
            sessionStorage.setItem('review_toast', JSON.stringify({
                msg: data.message || 'DienMayPro cảm ơn quý khách đã gửi bình luận!',
                isCenter: true
            }));
            sessionStorage.setItem('scroll_y_position', window.scrollY);
            location.reload();
        } else {
            if(btn) { 
                btn.disabled = false; 
                btn.innerHTML = 'Gửi trả lời'; 
            }
            showToastReview('Lỗi', data.message || 'Lỗi khi gửi câu trả lời.', 'error');
        }
    })
    .catch(err => {
        if(btn) { 
            btn.disabled = false; 
            btn.innerHTML = 'Gửi trả lời'; 
        }
        // Hiển thị thông báo lỗi cụ thể (ví dụ 403: nội dung vi phạm chính sách kiểm duyệt)
        const title = err.status === 403 ? 'Vi phạm' : 'Lỗi kết nối';
        const msg = err.status === 403 ? err.message : ('Đã xảy ra lỗi: ' + err.message);
        showToastReview(title, msg, 'error');
    });
}

// Gắn phím Enter để đăng nhanh bài viết (Shift+Enter để tạo dòng mới bình thường)
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        const target = e.target;
        if (target.id === 'reviewText') {
            e.preventDefault();
            submitReview();
        } else if (target.id && target.id.startsWith('replyText-')) {
            e.preventDefault();
            const parentId = target.id.split('-')[1];
            submitReply(parentId);
        }
    }
});

/**
 * Hàm: deleteReview
 * Công dụng: Gửi yêu cầu DELETE AJAX để xóa đánh giá và thực hiện hiệu ứng ẩn mượt mà trước khi xóa thẻ khỏi DOM.
 */
function deleteReview(id) {
    showConfirm('Xác nhận xóa', 'Bạn có chắc chắn muốn xóa đánh giá này không?', function() {
        const btn = document.getElementById('btnDoConfirm');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Đang xóa...';

        fetch(`/reviews/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                const element = document.getElementById('review-' + id);
                if(element) {
                    element.style.opacity = '0';
                    element.style.transform = 'translateX(-20px)';
                    element.style.transition = '0.3s';
                    setTimeout(() => element.remove(), 300);
                }
                showToastReview('Thành công', 'Đã xóa đánh giá thành công!', 'success');
                closeConfirmModal();
            } else {
                showToastReview('Lỗi', data.message || 'Không thể xóa đánh giá.', 'error');
                closeConfirmModal();
            }
        })
        .catch(err => {
            showToastReview('Lỗi', 'Đã xảy ra lỗi kết nối!', 'error');
            closeConfirmModal();
        });
    });
}

/* ===== HỆ THỐNG TOAST THÔNG BÁO THÔNG THƯỜNG (TOAST REVIEW) ===== */
function showToastReview(title, message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icons = {
        success: 'fa-circle-check',
        error: 'fa-circle-xmark',
        warning: 'fa-triangle-exclamation'
    };

    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fa-solid ${icons[type]}"></i>
        </div>
        <div class="toast-content">
            <span class="toast-title">${title}</span>
            <span class="toast-msg">${message}</span>
        </div>
    `;
    
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('active'), 10);
    
    setTimeout(() => {
        toast.classList.remove('active');
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

/* ===== HỆ THỐNG HỘP THOẠI XÁC NHẬN CUSTOM (CONFIRMATION SYSTEM) ===== */
let confirmCallback = null;
function showConfirm(title, message, callback, btnText = 'Xác nhận xóa', theme = 'danger') {
    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmMessage').innerText = message;
    
    const iconContainer = document.querySelector('#confirmModal .confirm-icon');
    const btn = document.getElementById('btnDoConfirm');
    
    // Đổi giao diện confirm dựa trên theme truyền vào (Đỏ cho Xóa, Vàng cho Báo cáo)
    if (theme === 'warning') {
        if (iconContainer) {
            iconContainer.style.background = '#fef3c7';
            iconContainer.style.color = '#f59e0b';
            iconContainer.innerHTML = '<i class="fa-solid fa-flag"></i>';
        }
        if (btn) {
            btn.style.background = '#f59e0b';
            btn.style.borderColor = '#f59e0b';
        }
    } else {
        if (iconContainer) {
            iconContainer.style.background = '#fee2e2';
            iconContainer.style.color = '#e21033';
            iconContainer.innerHTML = '<i class="fa-solid fa-trash-can"></i>';
        }
        if (btn) {
            btn.style.background = '#e21033';
            btn.style.borderColor = '#e21033';
        }
    }

    document.getElementById('confirmModal').classList.add('active');
    confirmCallback = callback;
    
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = btnText;
    }
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('active');
    confirmCallback = null;
}

document.getElementById('btnDoConfirm').addEventListener('click', function() {
    if (confirmCallback) confirmCallback();
});

/* ===== HỘP THOẠI THÔNG BÁO CUSTOM (ALERT SYSTEM) ===== */
function showAlert(title, message, type = 'warning') {
    const icon = document.getElementById('alertIcon');
    const btn = document.getElementById('alertBtn');
    
    if (type === 'success') {
        icon.style.background = '#dcfce7';
        icon.style.color = '#166534';
        icon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
        btn.style.background = '#16a34a';
        btn.innerText = 'Tuyệt vời';
    } else {
        icon.style.background = '#fef3c7';
        icon.style.color = '#f59e0b';
        icon.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i>';
        btn.style.background = '#0046ab';
        btn.innerText = 'Đồng ý';
    }

    document.getElementById('alertTitle').innerText = title;
    document.getElementById('alertMessage').innerText = message;
    document.getElementById('alertModal').classList.add('active');
}

/* ===== TOAST THÔNG BÁO TRUNG TÂM (CENTER TOAST) ===== */
function showCenterToast(message) {
    const container = document.getElementById('toast-center-container');
    const toast = document.createElement('div');
    toast.className = 'toast-center';
    
    // Nếu nội dung thông báo chứa các từ khóa nhạy cảm / chờ duyệt, đổi sang màu vàng cảnh báo
    const isWarning = message.includes('chờ') || message.includes('nhạy cảm') || message.includes('kiểm duyệt');
    if (isWarning) {
        toast.style.background = 'rgba(245, 158, 11, 0.95)';
        toast.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> ${message}`;
    } else {
        toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${message}`;
    }
    
    container.appendChild(toast);
    setTimeout(() => toast.classList.add('active'), 10);
    
    setTimeout(() => {
        toast.classList.remove('active');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

function closeAlertModal() {
    document.getElementById('alertModal').classList.remove('active');
}

/* ===== LIGHTBOX HIỂN THỊ PHƯƠNG TIỆN (MEDIA LIGHTBOX SYSTEM) ===== */
function openMediaLightbox(url, isVideo) {
    const lb = document.getElementById('mediaLightbox');
    const img = document.getElementById('lightboxImg');
    const vid = document.getElementById('lightboxVideo');
    
    if(isVideo) {
        img.style.display = 'none';
        vid.style.display = 'block';
        vid.src = url;
    } else {
        vid.style.display = 'none';
        img.style.display = 'block';
        img.src = url;
    }
    lb.classList.add('active');
}

function closeMediaLightbox() {
    document.getElementById('mediaLightbox').classList.remove('active');
    document.getElementById('lightboxVideo').pause();
}

/**
 * Hàm: reportReview
 * Công dụng: Gửi yêu cầu POST báo cáo đánh giá vi phạm (spam/chửi bới/sai nội dung) bằng AJAX.
 */
function reportReview(id) {
    showConfirm(
        'Xác nhận báo cáo',
        'Bạn có chắc chắn muốn báo cáo đánh giá vi phạm này không?',
        function() {
            const btn = document.getElementById('btnDoConfirm');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span> Đang gửi...';
            }

            fetch(`/reviews/${id}/report`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || `Lỗi hệ thống (${response.status})`);
                }
                return data;
            })
            .then(data => {
                showToastReview('Báo cáo thành công', data.message, 'success');
                closeConfirmModal();
                sessionStorage.setItem('scroll_y_position', window.scrollY);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            })
            .catch(error => {
                showToastReview('Không thể báo cáo', error.message, 'error');
                closeConfirmModal();
            });
        },
        'Gửi báo cáo',
        'warning'
    );
}
</script>
@endpush
