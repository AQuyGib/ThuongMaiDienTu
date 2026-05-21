{{-- resources/views/frontend/products/partials/reviews.blade.php --}}

@push('styles')
<style>
/* Reviews */
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
.avatar-admin { background: linear-gradient(135deg, #0046ab, #003380); }
.avatar-manager { background: linear-gradient(135deg, #16a34a, #15803d); }
.avatar-default { background: #94a3b8; }
.user-badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: 600; text-transform: uppercase; }
.badge-admin { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
.badge-manager { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
.review-stars { color: #f59e0b; font-size: 12px; margin-bottom: 8px; margin-left: 54px; }
.review-content { font-size: 15px; color: #444; line-height: 1.6; margin-left: 54px; }

/* Media Upload */
.review-media-upload { margin: 10px 0; }
.media-upload-label { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border: 2px dashed #d0d0d0; border-radius: 8px; cursor: pointer; font-size: 13px; color: #666; transition: 0.2s; background: #fafafa; }
.media-upload-label:hover { border-color: #0046ab; color: #0046ab; background: #eef2ff; }
.media-preview-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.media-preview-item { position: relative; width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; }
.media-preview-item img, .media-preview-item video { width: 100%; height: 100%; object-fit: cover; }
.media-preview-remove { position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.6); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; }

/* Review Media Display */
.review-media-display { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.review-media-thumb { width: 90px; height: 90px; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; cursor: pointer; position: relative; }
.review-media-thumb img { width: 100%; height: 100%; object-fit: cover; transition: 0.2s; }
.review-media-thumb:hover img { opacity: 0.85; }
.review-media-thumb .play-icon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.35); color: #fff; font-size: 24px; pointer-events: none; }

/* Review Replies */
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

/* Custom Confirm Modal */
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

/* Toast Notification */
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

/* Centered Toast */
#toast-center-container { position: fixed; top: 30%; left: 50%; transform: translate(-50%, -50%); z-index: 99999; pointer-events: none; }
.toast-center { background: rgba(0, 70, 171, 0.95); color: #fff; padding: 15px 35px; border-radius: 50px; font-weight: 600; font-size: 15px; box-shadow: 0 10px 30px rgba(0,70,171,0.3); opacity: 0; transform: translateY(20px); transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center; gap: 12px; white-space: nowrap; border: 1px solid rgba(255,255,255,0.2); }
.toast-center.active { opacity: 1; transform: translateY(0); }
.toast-center i { color: #fff; font-size: 20px; }

/* Media Lightbox */
#mediaLightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 99999; align-items: center; justify-content: center; cursor: pointer; }
#mediaLightbox.active { display: flex; }
#mediaLightbox img, #mediaLightbox video { max-width: 90%; max-height: 90%; border-radius: 12px; object-fit: contain; cursor: default; }
.lightbox-close { position: absolute; top: 20px; right: 25px; color: #fff; font-size: 28px; cursor: pointer; z-index: 100000; background: rgba(0,0,0,0.5); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
.lightbox-close:hover { background: rgba(255,255,255,0.2); }
.review-media-display { margin-left: 54px; }

/* Spinner */
.spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,.3); border-radius: 50%; border-top-color: #fff; animation: spin 0.8s linear infinite; vertical-align: middle; margin-right: 8px; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

<div class="pd-reviews">
    @php
        $avgRating = $avgRating ?? 5;
        $reviewCount = $reviewCount ?? 0;
        $reviews = $reviews ?? collect();
    @endphp
    <h2><i class="fa-solid fa-comments" style="color:#0046ab"></i> Đánh giá & Nhận xét</h2>
    <div class="review-stats">
        <div class="review-average">
            <h3 id="avgReviewScore">{{ round($avgRating, 1) }}/5</h3>
            <div class="stars" id="avgReviewStars">
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
    
    <div class="review-form" style="margin-bottom: 25px; background: #f9f9f9; padding: 20px; border-radius: 10px;">
        <h4 style="margin-bottom: 15px; font-size: 15px;">Viết đánh giá của bạn</h4>
        <div style="margin-bottom: 10px; display: flex; gap: 10px; color: #ccc; font-size: 20px; cursor: pointer;" id="starRatingContainer">
            <i class="fa-solid fa-star star-rating" data-val="1"></i>
            <i class="fa-solid fa-star star-rating" data-val="2"></i>
            <i class="fa-solid fa-star star-rating" data-val="3"></i>
            <i class="fa-solid fa-star star-rating" data-val="4"></i>
            <i class="fa-solid fa-star star-rating" data-val="5"></i>
        </div>
        <textarea id="reviewText" placeholder="Nhập đánh giá của bạn về sản phẩm này..." style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; resize: none;"></textarea>

        @if(!auth()->check())
        <input type="text" id="reviewAuthor" placeholder="Họ và tên của bạn *" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; outline: none;">
        @endif

        {{-- Upload ảnh/video --}}
        <div class="review-media-upload">
            <label class="media-upload-label" for="reviewMediaInput">
                <i class="fa-solid fa-photo-film"></i>
                Thêm ảnh / video
            </label>
            <input type="file" id="reviewMediaInput" multiple accept="image/*,video/*" style="display:none;" onchange="previewMediaFiles(this)">
        </div>
        <div class="media-preview-grid" id="mediaPreviewGrid"></div>

        <button type="button" onclick="submitReview()" class="btn-submit-review" style="background: #0046ab; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px;">Gửi đánh giá</button>
    </div>
    
    <div class="review-list">
        @if($reviewCount > 0)
            @foreach($reviews as $r)
                <div class="review-item" id="review-{{ $r->id }}">
                    <div class="review-user">
                        @php
                            $isReviewAdmin = $r->user && $r->user->role_id == 1;
                            $isReviewManager = $r->user && $r->user->role_id == 2;
                            $displayName = $r->author_name ?? ($r->user ? $r->user->full_name : 'Khách hàng');
                            $firstLetter = mb_substr($displayName, 0, 1, 'UTF-8');
                        @endphp
                        
                        <div class="user-avatar {{ $isReviewAdmin ? 'avatar-admin' : ($isReviewManager ? 'avatar-manager' : 'avatar-default') }}">
                            @if($isReviewAdmin || $isReviewManager)
                                <i class="fa-solid fa-bolt-lightning"></i>
                            @else
                                {{ $firstLetter }}
                            @endif
                        </div>

                        <div class="user-info">
                            <div class="user-name">
                                {{ $displayName }}
                                @if($isReviewAdmin)
                                    <span class="user-badge badge-admin">Admin</span>
                                @elseif($isReviewManager)
                                    <span class="user-badge badge-manager">Quản lý</span>
                                @endif
                                <span style="color:#94a3b8; font-size:12px; font-weight:400; margin-left:8px;">{{ $r->created_at->format('d/m/Y H:i') }}</span>
                                
                                @if(auth()->check() && in_array(auth()->user()->role_id, [1, 2]))
                                <button onclick="deleteReview({{ $r->id }})" title="Xóa đánh giá" class="btn-delete-review" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:14px; margin-left:10px;">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="review-stars">
                        @for($i=1; $i<=5; $i++)
                            <i class="{{ $i <= $r->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                        @endfor
                    </div>
                    <div class="review-content">{{ $r->content }}</div>
                    
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

                    <button class="reply-btn" onclick="toggleReplyForm({{ $r->id }})"><i class="fa-solid fa-reply"></i> Trả lời</button>

                    <div class="reply-form" id="replyForm-{{ $r->id }}">
                        @if(!auth()->check())
                        <input type="text" id="replyAuthor-{{ $r->id }}" placeholder="Họ và tên của bạn *" class="reply-author-input">
                        @endif
                        <textarea id="replyText-{{ $r->id }}" placeholder="Nhập câu trả lời..." class="reply-textarea"></textarea>
                        <div class="reply-actions">
                            <button type="button" onclick="toggleReplyForm({{ $r->id }})" class="btn-cancel">Hủy</button>
                            <button type="button" onclick="submitReply({{ $r->id }})" class="btn-submit-reply">Gửi trả lời</button>
                        </div>
                    </div>

                    <div class="replies-list" id="replies-{{ $r->id }}">
                        @foreach($r->replies as $index => $reply)
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

                                    <div class="user-info">
                                        <div class="user-name" style="font-size:14px;">
                                            {{ $replyName }}
                                            @if($isReplyAdmin)
                                                <span class="user-badge badge-admin">Admin</span>
                                            @elseif($isReplyManager)
                                                <span class="user-badge badge-manager">Quản lý</span>
                                            @endif
                                            <span style="color:#94a3b8; font-size:11px; font-weight:400; margin-left:8px;">{{ $reply->created_at->format('d/m/Y H:i') }}</span>
                                            
                                            @if(auth()->check() && in_array(auth()->user()->role_id, [1, 2]))
                                            <button onclick="deleteReview({{ $reply->id }})" class="btn-delete-reply" style="background:none; border:none; color:#ef4444; cursor:pointer; font-size:12px; margin-left:8px;">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="review-content-replied" style="margin-left:44px; font-size:14px;">{{ $reply->content }}</div>
                                <button class="reply-btn-nested" style="margin-left:44px;" onclick="replyToUser({{ $r->id }}, '{{ addslashes($replyName) }}', {{ $reply->id }})"><i class="fa-solid fa-reply"></i> Trả lời</button>
                            </div>
                        @endforeach
                    </div>
                    @if($r->replies->count() > 1)
                        <button class="btn-show-more-replies" onclick="showAllReplies({{ $r->id }}, this)">
                            <i class="fa-solid fa-chevron-down"></i> Xem thêm {{ $r->replies->count() - 1 }} phản hồi
                        </button>
                    @endif
                </div>
            @endforeach
        @else
            <p id="noReviewMsg" class="no-reviews">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
        @endif
    </div>
</div>

{{-- Modal cảnh báo/thông báo --}}
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

{{-- Modal xác nhận xóa --}}
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

{{-- Toast container --}}
<div id="toast-container"></div>
<div id="toast-center-container"></div>

{{-- Media Lightbox --}}
<div id="mediaLightbox" onclick="closeMediaLightbox()">
    <i class="fa-solid fa-xmark lightbox-close" onclick="closeMediaLightbox()"></i>
    <img id="lightboxImg" src="" alt="" style="display:none;" onclick="event.stopPropagation()">
    <video id="lightboxVideo" src="" controls style="display:none;" onclick="event.stopPropagation()"></video>
</div>

@push('scripts')
<script>
let currentRating = 5;
let selectedMediaFiles = [];

document.addEventListener('DOMContentLoaded', function() {
    // Kiểm tra thông báo từ sessionStorage sau khi reload trang
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

    const stars = document.querySelectorAll('.star-rating');
    stars.forEach(star => {
        star.addEventListener('click', function() {
            currentRating = this.getAttribute('data-val');
            stars.forEach(s => {
                if(s.getAttribute('data-val') <= currentRating) s.style.color = '#f59e0b';
                else s.style.color = '#ccc';
            });
        });
        // Init color
        if(star.getAttribute('data-val') <= currentRating) star.style.color = '#f59e0b';
    });
});

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

        // Kiểm tra dung lượng (tối đa 2MB do giới hạn máy chủ hiện tại)
        if (file.size > 2 * 1024 * 1024) {
            showAlert('Tệp quá lớn', `Tệp "${file.name}" vượt quá giới hạn 2MB. Vui lòng chọn tệp nhỏ hơn.`);
            return;
        }

        // Giới hạn video: chỉ 1 video
        if (isVideo) {
            const hasVideo = selectedMediaFiles.some(f => f && f.type.startsWith('video/'));
            if (hasVideo) {
                showAlert('Giới hạn video', 'Bạn chỉ có thể tải lên tối đa 1 video.');
                return;
            }
        }

        // Giới hạn ảnh: tối đa 5 ảnh
        if (isImage) {
            const imageCount = selectedMediaFiles.filter(f => f && f.type.startsWith('image/')).length;
            if (imageCount >= 5) {
                showAlert('Giới hạn ảnh', 'Bạn chỉ có thể tải lên tối đa 5 ảnh.');
                return;
            }
        }

        const fileIndex = selectedMediaFiles.length;
        selectedMediaFiles.push(file);
        
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
    input.value = ''; 
}

function removeMediaFile(index) {
    const item = document.getElementById('preview-item-' + index);
    if (item) item.remove();
    selectedMediaFiles[index] = null;
}

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
            throw new Error(data.message || `Lỗi hệ thống (${response.status})`);
        }
        return data;
    })
    .then(data => {
        if(data.success) {
            sessionStorage.setItem('review_toast', JSON.stringify({
                msg: 'DienMayPro cảm ơn quý khách đã gửi bình luận!',
                isCenter: true
            }));
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
        showAlert('Không thể gửi', 'Đã có lỗi xảy ra: ' + error.message);
    });
}

function toggleReplyForm(id) {
    const form = document.getElementById('replyForm-' + id);
    const repliesList = document.getElementById('replies-' + id);
    const isHidden = form.style.display === 'none' || form.style.display === '';

    if (isHidden) {
        // Trả về vị trí mặc định (trước danh sách câu trả lời)
        repliesList.parentNode.insertBefore(form, repliesList);
        form.style.display = 'block';
        const textarea = document.getElementById('replyText-' + id);
        textarea.value = '';
        textarea.focus();
    } else {
        form.style.display = 'none';
    }
}

function replyToUser(parentReviewId, userName, specificReplyId) {
    const form = document.getElementById('replyForm-' + parentReviewId);
    const specificReply = document.getElementById('review-' + specificReplyId);
    
    // Di chuyển khung reply xuống dưới comment cụ thể
    specificReply.parentNode.insertBefore(form, specificReply.nextSibling);
    
    form.style.display = 'block';
    const textarea = document.getElementById('replyText-' + parentReviewId);
    textarea.value = '@' + userName + ': ';
    textarea.focus();
}

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
    btn.remove();
}

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
    formData.append('rating', 5);
    if(authorName) formData.append('author_name', authorName);

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
            throw new Error(data.message || `Lỗi hệ thống (${response.status})`);
        }
        return data;
    })
    .then(data => {
        if(data.success) {
            sessionStorage.setItem('review_toast', JSON.stringify({
                msg: 'DienMayPro cảm ơn quý khách đã gửi bình luận!',
                isCenter: true
            }));
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
        showToastReview('Lỗi kết nối', 'Đã xảy ra lỗi: ' + err.message, 'error');
    });
}

// Cho phép nhấn Enter để gửi (Shift+Enter để xuống dòng)
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

/* ===== Toast Notification System ===== */
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

/* ===== Custom Confirmation System ===== */
let confirmCallback = null;
function showConfirm(title, message, callback) {
    document.getElementById('confirmTitle').innerText = title;
    document.getElementById('confirmMessage').innerText = message;
    document.getElementById('confirmModal').classList.add('active');
    confirmCallback = callback;
    
    // Reset button state
    const btn = document.getElementById('btnDoConfirm');
    btn.disabled = false;
    btn.innerHTML = 'Xác nhận xóa';
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('active');
    confirmCallback = null;
}

document.getElementById('btnDoConfirm').addEventListener('click', function() {
    if (confirmCallback) confirmCallback();
});

/* ===== Alert System ===== */
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

/* ===== Center Toast ===== */
function showCenterToast(message) {
    const container = document.getElementById('toast-center-container');
    const toast = document.createElement('div');
    toast.className = 'toast-center';
    toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${message}`;
    
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
</script>
@endpush
