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
.review-user { font-weight: 600; font-size: 14px; margin-bottom: 5px; display: flex; align-items: center; gap: 8px; }
.review-user span { background: #16a34a; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px; font-weight: normal;}
.review-stars { color: #f59e0b; font-size: 12px; margin-bottom: 8px; }
.review-content { font-size: 14px; color: #444; }

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
.reply-form { margin-top: 10px; display: none; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; }
.reply-item { margin-top: 15px; padding: 15px; background: #f8fafc; border-radius: 8px; border-left: 3px solid #cbd5e1; }
</style>
@endpush

<div class="pd-reviews">
    <h2><i class="fa-solid fa-comments" style="color:#0046ab"></i> Đánh giá & Nhận xét</h2>
    <div class="review-stats">
        <div class="review-average">
            <h3 id="avgReviewScore">{{ $avgRating }}/5</h3>
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

        <button type="button" onclick="submitReview()" style="background: #0046ab; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; margin-top: 10px;">Gửi đánh giá</button>
    </div>
    
    <div class="review-list">
        @if($reviewCount > 0)
            @foreach($reviews as $r)
                <div class="review-item" id="review-{{ $r->id }}">
                    <div class="review-user">
                        <span>
                            {{ $r->author_name ?? ($r->user ? $r->user->full_name : 'Khách hàng') }}
                            @if($r->user && $r->user->role_id == 1)
                                <span class="badge-admin">Admin</span>
                            @elseif($r->user && $r->user->role_id == 2)
                                <span class="badge-manager">Quản lý</span>
                            @endif
                        </span>
                        @if(auth()->check() && in_array(auth()->user()->role_id, [1, 2]))
                        <button onclick="deleteReview({{ $r->id }})" title="Xóa đánh giá" class="btn-delete-review">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        @endif
                    </div>
                    <div class="review-stars">
                        @for($i=1; $i<=5; $i++)
                            <i class="{{ $i <= $r->rating ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                        @endfor
                        <span class="review-date">{{ $r->created_at->format('d/m/Y H:i') }}</span>
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
                        @foreach($r->replies as $reply)
                            <div class="reply-item" id="review-{{ $reply->id }}">
                                <div class="review-user">
                                    <span>
                                        <i class="fa-solid fa-turn-up fa-rotate-90"></i> 
                                        {{ $reply->author_name ?? ($reply->user ? $reply->user->full_name : 'Khách hàng') }}
                                        @if($reply->user && $reply->user->role_id == 1)
                                            <span class="badge-admin">Admin</span>
                                        @elseif($reply->user && $reply->user->role_id == 2)
                                            <span class="badge-manager">Quản lý</span>
                                        @endif
                                    </span>
                                    @if(auth()->check() && in_array(auth()->user()->role_id, [1, 2]))
                                    <button onclick="deleteReview({{ $reply->id }})" class="btn-delete-reply">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    @endif
                                </div>
                                <div class="review-date-replied">{{ $reply->created_at->format('d/m/Y H:i') }}</div>
                                <div class="review-content-replied">{{ $reply->content }}</div>
                                <button class="reply-btn-nested" onclick="replyToUser({{ $r->id }}, '{{ addslashes($reply->author_name ?? ($reply->user ? $reply->user->full_name : 'Khách hàng')) }}')"><i class="fa-solid fa-reply"></i> Trả lời</button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <p id="noReviewMsg" class="no-reviews">Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
let currentRating = 5;
let selectedMediaFiles = [];

document.addEventListener('DOMContentLoaded', function() {
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
        if (selectedMediaFiles.length >= 5) return;
        selectedMediaFiles.push(file);
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'media-preview-item';
            const isVideo = file.type.startsWith('video/');
            
            div.innerHTML = `
                ${isVideo ? `<video src="${e.target.result}"></video>` : `<img src="${e.target.result}">`}
                <button type="button" class="media-preview-remove" onclick="removeMediaFile(this, ${selectedMediaFiles.length - 1})"><i class="fa-solid fa-xmark"></i></button>
            `;
            grid.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
    input.value = ''; 
}

function removeMediaFile(btn, index) {
    btn.parentElement.remove();
    selectedMediaFiles.splice(index, 1);
}

function submitReview() {
    const textarea = document.getElementById('reviewText');
    const content = textarea.value.trim();
    const authorInput = document.getElementById('reviewAuthor');
    const authorName = authorInput ? authorInput.value.trim() : null;

    if(!content) { alert('Vui lòng nhập nội dung đánh giá!'); return; }
    if(authorInput && !authorName) { alert('Vui lòng nhập tên của bạn!'); return; }

    const formData = new FormData();
    formData.append('product_id', '{{ $product->product_id }}');
    formData.append('rating', currentRating);
    formData.append('content', content);
    if(authorName) formData.append('author_name', authorName);
    
    selectedMediaFiles.forEach(file => {
        formData.append('media[]', file);
    });

    fetch('{{ route("reviews.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload(); 
        } else {
            alert(data.message || 'Lỗi khi gửi đánh giá.');
        }
    });
}

function toggleReplyForm(id) {
    const form = document.getElementById('replyForm-' + id);
    form.style.display = (form.style.display === 'block') ? 'none' : 'block';
}

function replyToUser(parentReviewId, userName) {
    toggleReplyForm(parentReviewId);
    const textarea = document.getElementById('replyText-' + parentReviewId);
    textarea.value = '@' + userName + ': ';
    textarea.focus();
}

function submitReply(parentId) {
    const text = document.getElementById('replyText-' + parentId).value.trim();
    const authorInput = document.getElementById('replyAuthor-' + parentId);
    const authorName = authorInput ? authorInput.value.trim() : null;

    if(!text) { alert('Vui lòng nhập câu trả lời!'); return; }
    
    fetch('{{ route("reviews.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            product_id: '{{ $product->product_id }}',
            parent_id: parentId,
            content: text,
            author_name: authorName,
            rating: 5
        })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) location.reload();
    });
}

function deleteReview(id) {
    if(!confirm('Bạn có chắc chắn muốn xóa?')) return;
    
    fetch(`/reviews/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            document.getElementById('review-' + id).remove();
            showToast('Đã xóa thành công');
        }
    });
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
