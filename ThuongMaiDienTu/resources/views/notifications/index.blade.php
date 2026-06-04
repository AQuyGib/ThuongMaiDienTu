@extends('layouts.app')

@section('title', 'Thông báo của tôi')

@section('content')
<!-- TRANG TRUNG TÂM THÔNG BÁO CỦA KHÁCH HÀNG (NOTIFICATION CENTER)
     Cho phép người dùng xem, lọc theo loại, khoảng thời gian và đánh dấu đã đọc thông báo bằng AJAX.
-->
<div class="container" style="padding: 32px 15px 56px;">
    <!-- KHỐI BANNER THÔNG TIN CHÍNH (HERO SECTION) -->
    <div class="notification-hero">
        <div>
            <p class="notification-hero-kicker">Trung tâm thông báo</p>
            <h1 class="notification-hero-title">Thông báo của tôi</h1>
            <p class="notification-hero-subtitle">Bạn có <span id="unreadCountText">{{ $unreadCount }}</span> thông báo chưa đọc. Xem nhanh, lọc nhanh và không bỏ lỡ ưu đãi quan trọng.</p>
        </div>
        <!-- Nút Đánh dấu tất cả thông báo đã đọc bằng yêu cầu AJAX gửi ngầm -->
        <div class="notification-hero-actions">
            <form method="POST" action="{{ route('notifications.read-all') }}" id="formReadAllNotifications">
                @csrf
                <button type="submit" class="btn-primary-soft">
                    <i class="fa-solid fa-check-double"></i>
                    Đánh dấu tất cả đã đọc
                </button>
            </form>
        </div>
    </div>

    <!-- NẠP BỘ LỌC THÔNG BÁO TỪ COMPONENT DÙNG CHUNG (PARTIALS) -->
    @include('partials.notification-filters', [
        'typeOptions' => [
            'promotion.auto' => 'Khuyến mãi tự động',
            'promotion.auto_updated' => 'Khuyến mãi cập nhật',
            'promotion.product_discount' => 'Giảm giá sản phẩm',
            'admin.manual_campaign' => 'Gửi tay',
            'order.created' => 'Đơn hàng mới',
            'order.status_updated' => 'Cập nhật đơn hàng',
            'admin.order.created' => 'Đơn hàng cho admin',
            'article.published' => 'Bài viết mới',
            'review.created' => 'Review mới',
            'inventory.low_stock' => 'Tồn kho thấp',
            'lucky_wheel.won' => 'Quay số trúng thưởng',
            'rewards.redeemed' => 'Đổi điểm thưởng',
            'repair_ticket.created' => 'Tiếp nhận sửa chữa',
            'repair_ticket.status_updated' => 'Cập nhật sửa chữa',
            'service_invoice.created' => 'Hóa đơn dịch vụ mới',
            'service_invoice.paid' => 'Thanh toán hóa đơn dịch vụ',
            'installment.created' => 'Đăng ký trả góp',
            'installment.payment_success' => 'Đóng tiền trả góp định kỳ',
            'installment.approved' => 'Hồ sơ trả góp được duyệt',
            'installment.rejected' => 'Hồ sơ trả góp bị từ chối',
        ],
        'filters' => [
            'type' => request('type'),
            'read' => request('read'),
            'recipient' => request('recipient'),
            'from' => request('from'),
            'to' => request('to'),
        ],
        'showRecipient' => false,
        'showDateRange' => true,
        'resetUrl' => route('notifications.index'),
    ])

    <!-- DANH SÁCH LƯỚI THÔNG BÁO -->
    <div class="notification-grid">
        @forelse($notifications as $notification)
            @php
                // Bản đồ ánh xạ cấu hình biểu tượng và màu sắc hiển thị phù hợp với từng phân loại thông báo
                $typeMeta = [
                    'promotion.auto' => ['icon' => 'fa-tags', 'color' => 'promo'],
                    'promotion.auto_updated' => ['icon' => 'fa-bullhorn', 'color' => 'promo'],
                    'promotion.product_discount' => ['icon' => 'fa-percent', 'color' => 'promo'],
                    'admin.manual_campaign' => ['icon' => 'fa-paper-plane', 'color' => 'info'],
                    'order.created' => ['icon' => 'fa-box', 'color' => 'order'],
                    'order.status_updated' => ['icon' => 'fa-truck-fast', 'color' => 'order'],
                    'admin.order.created' => ['icon' => 'fa-user-shield', 'color' => 'admin'],
                    'article.published' => ['icon' => 'fa-newspaper', 'color' => 'article'],
                    'review.created' => ['icon' => 'fa-star', 'color' => 'review'],
                    'inventory.low_stock' => ['icon' => 'fa-boxes-stacked', 'color' => 'warning'],
                    'lucky_wheel.won' => ['icon' => 'fa-clover', 'color' => 'lucky'],
                    'rewards.redeemed' => ['icon' => 'fa-gift', 'color' => 'reward'],
                    'repair_ticket.created' => ['icon' => 'fa-wrench', 'color' => 'repair'],
                    'repair_ticket.status_updated' => ['icon' => 'fa-screwdriver-wrench', 'color' => 'repair'],
                    'service_invoice.created' => ['icon' => 'fa-file-invoice', 'color' => 'invoice'],
                    'service_invoice.paid' => ['icon' => 'fa-file-invoice-dollar', 'color' => 'invoice'],
                    'installment.created' => ['icon' => 'fa-wallet', 'color' => 'installment'],
                    'installment.payment_success' => ['icon' => 'fa-check-circle', 'color' => 'installment'],
                    'installment.approved' => ['icon' => 'fa-circle-check', 'color' => 'order'],
                    'installment.rejected' => ['icon' => 'fa-circle-xmark', 'color' => 'promo'],
                ][$notification->type] ?? ['icon' => 'fa-bell', 'color' => 'default'];
            @endphp
            
            <!-- Mỗi thẻ thông báo sử dụng class is-unread nếu chưa đọc để tạo hiệu ứng viền đỏ và chấm xanh -->
            <article class="notification-card {{ $notification->read_at ? '' : 'is-unread' }}">
                <!-- Biểu tượng thông báo với màu sắc động tương ứng -->
                <div class="notification-icon notification-{{ $typeMeta['color'] }}">
                    <i class="fa-solid {{ $typeMeta['icon'] }}"></i>
                </div>
                
                <!-- Thân bài viết thông báo -->
                <div class="notification-body">
                    <div class="notification-head">
                        <div>
                            <h3 class="notification-title">{{ $notification->title }}</h3>
                            <div class="notification-meta">
                                <span>{{ $notification->type }}</span>
                                <span>{{ $notification->created_at?->format('H:i d/m/Y') }}</span>
                            </div>
                        </div>
                        <!-- Nhãn màu xanh thông báo "Mới" nếu chưa đọc -->
                        @unless($notification->read_at)
                            <span class="notification-badge">Mới</span>
                        @endunless
                    </div>
                    <!-- Nội dung chi tiết thông báo -->
                    <p class="notification-content">{{ $notification->content }}</p>
                    
                    <!-- Khối các nút hành động (Xem chi tiết URL hoặc Đánh dấu đã đọc qua AJAX) -->
                    <div class="notification-actions">
                        @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="btn-outline-soft">Xem chi tiết</a>
                        @endif
                        @unless($notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->notification_id) }}" class="form-mark-read-ajax">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-success-soft">Đã đọc</button>
                            </form>
                        @endunless
                    </div>
                </div>
            </article>
        @empty
            <!-- Trạng thái trống (Không tìm thấy thông báo nào khớp bộ lọc) -->
            <div class="notification-empty">
                <div class="notification-empty-icon"><i class="fa-regular fa-bell"></i></div>
                <h3>Chưa có thông báo nào</h3>
                <p>Khi có khuyến mãi, đơn hàng hoặc cập nhật mới, thông báo sẽ xuất hiện tại đây.</p>
            </div>
        @endforelse
    </div>

    <!-- Phân trang Bootstrap mặc định của Laravel -->
    <div style="margin-top:24px;">
        {{ $notifications->links() }}
    </div>
</div>

@push('styles')
<!-- CSS STYLESHEET TÙY CHỈNH CHO GIAO DIỆN TRANG THÔNG BÁO CHUYÊN NGHIỆP -->
<style>
.notification-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 100%);
    color: #fff;
    border-radius: 28px;
    padding: 28px;
    display: flex;
    justify-content: space-between;
    gap: 20px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.18);
    margin-bottom: 22px;
}
.notification-hero-kicker {
    text-transform: uppercase;
    letter-spacing: .18em;
    font-size: 11px;
    opacity: .75;
    font-weight: 800;
}
.notification-hero-title {
    font-size: 34px;
    font-weight: 900;
    margin-top: 8px;
}
.notification-hero-subtitle {
    margin-top: 10px;
    color: rgba(255,255,255,.82);
    max-width: 720px;
}

/* Kiểu thiết kế các nút phong cách bo mềm (Soft buttons) */
.btn-primary-soft, .btn-outline-soft, .btn-success-soft {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 14px;
    font-weight: 800;
    transition: .2s ease;
}
.btn-primary-soft { background: #fff; color: #0f172a; border: none; cursor: pointer; }
.btn-primary-soft:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(255,255,255,.2); }
.btn-outline-soft { border: 1px solid #cbd5e1; color: #475569; background: transparent; text-decoration: none; font-size: 13px; }
.btn-outline-soft:hover { background: #f8fafc; color: #0f172a; }
.btn-success-soft { background: #dcfce7; color: #15803d; border: none; cursor: pointer; font-size: 13px; }
.btn-success-soft:hover { background: #bbf7d0; }

/* Grid chứa danh sách card thông báo */
.notification-grid { display: grid; gap: 16px; margin-top: 24px; }

/* Chi tiết thẻ card thông báo (Card style) */
.notification-card {
    background: #fff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    padding: 20px;
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 16px;
    transition: .2s ease;
    position: relative;
    overflow: hidden;
}
.notification-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -10px rgba(0,0,0,0.08); }
.notification-card.is-unread { border-left: 4px solid #3b82f6; background: #fafcff; }

/* Vùng hiển thị biểu tượng thông báo phân loại */
.notification-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.notification-promo { background: #fef2f2; color: #ef4444; }
.notification-info { background: #eff6ff; color: #3b82f6; }
.notification-order { background: #f0fdf4; color: #22c55e; }
.notification-admin { background: #faf5ff; color: #a855f7; }
.notification-article { background: #f6f8fa; color: #475569; }
.notification-review { background: #fffbeb; color: #f59e0b; }
.notification-warning { background: #fff7ed; color: #f97316; }
.notification-default { background: #f8fafc; color: #64748b; }
.notification-lucky { background: #fdf2f8; color: #db2777; }
.notification-reward { background: #faf5ff; color: #7c3aed; }
.notification-points { background: #fffbeb; color: #d97706; }
.notification-repair { background: #f0f9ff; color: #0284c7; }
.notification-invoice { background: #ecfdf5; color: #059669; }
.notification-installment { background: #eef2ff; color: #4f46e5; }

.notification-body { display: flex; flex-direction: column; gap: 8px; }
.notification-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
.notification-title { font-size: 16px; font-weight: 800; color: #0f172a; }
.notification-meta { display: flex; gap: 12px; font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; }
.notification-badge { background: #ef4444; color: #fff; font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 6px; border-radius: 6px; letter-spacing: 0.05em; }
.notification-content { font-size: 14px; color: #475569; line-height: 1.6; }
.notification-actions { display: flex; gap: 10px; margin-top: 6px; }

/* Trạng thái trống */
.notification-empty {
    background:#fff; border:1px dashed #cbd5e1; border-radius:24px; text-align:center; padding:48px 24px; color:#64748b;
}
.notification-empty-icon {
    width:72px; height:72px; margin:0 auto 16px; border-radius:50%; background:#eff6ff; color:#2563eb;
    display:flex; align-items:center; justify-content:center; font-size:28px;
}
@media (max-width: 768px) {
    .notification-hero { flex-direction: column; }
    .notification-hero-title { font-size: 28px; }
    .notification-card { grid-template-columns: 1fr; }
}
</style>
@endpush

<!-- ============================================================
     JAVASCRIPT BẤT ĐỒNG BỘ (AJAX) XỬ LÝ THAO TÁC ĐỌC THÔNG BÁO
     ============================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. XỬ LÝ NÚT "ĐÃ ĐỌC" CỦA TỪNG THÔNG BÁO RIÊNG LẺ
    const markReadForms = document.querySelectorAll('.form-mark-read-ajax');
    markReadForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const actionUrl = this.getAttribute('action');
            const card = this.closest('.notification-card');
            
            // Gửi ngầm yêu cầu PATCH lên server để cập nhật trạng thái read_at
            fetch(actionUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('Network response was not ok.');
            })
            .then(data => {
                // Xóa bỏ kiểu viền sáng xanh (unread) của thẻ card trên giao diện
                if (card) {
                    card.classList.remove('is-unread');
                    const badge = card.querySelector('.notification-badge');
                    if (badge) {
                        badge.remove(); // Xóa tag nhãn đỏ "Mới"
                    }
                }
                // Xóa bỏ form nút bấm "Đã đọc" vì thông báo đã được ghi nhận đã đọc thành công
                this.remove();
                
                // Giảm số đếm chưa đọc trên trang hiện tại
                const unreadCountText = document.getElementById('unreadCountText');
                if (unreadCountText) {
                    let currentCount = parseInt(unreadCountText.textContent) || 0;
                    if (currentCount > 0) {
                        currentCount--;
                        unreadCountText.textContent = currentCount;
                    }
                }
                
                // Giảm số đếm chưa đọc trên quả chuông Notification Badge của Header chính
                const headerBadge = document.getElementById('notificationBadge');
                if (headerBadge) {
                    let headerCount = parseInt(headerBadge.textContent) || 0;
                    if (headerCount > 0) {
                        headerCount--;
                        headerBadge.textContent = headerCount;
                        // Ẩn badge đi nếu số thông báo chưa đọc về 0
                        if (headerCount === 0) {
                            headerBadge.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Lỗi khi đánh dấu đã đọc:', error);
            });
        });
    });

    // 2. XỬ LÝ NÚT "ĐÁNH DẤU TẤT CẢ ĐÃ ĐỌC" CỦA BANNER HERO
    const readAllForm = document.getElementById('formReadAllNotifications');
    if (readAllForm) {
        readAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const actionUrl = this.getAttribute('action');

            // Gửi ngầm yêu cầu POST lên server để cập nhật tất cả thông báo của user
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('Network response was not ok.');
            })
            .then(data => {
                // Xóa toàn bộ class chưa đọc và tag "Mới" của tất cả thông báo đang hiển thị
                const unreadCards = document.querySelectorAll('.notification-card.is-unread');
                unreadCards.forEach(card => {
                    card.classList.remove('is-unread');
                    const badge = card.querySelector('.notification-badge');
                    if (badge) {
                        badge.remove();
                    }
                });

                // Xóa hết tất cả các form bấm "Đã đọc" của từng thẻ card
                const individualForms = document.querySelectorAll('.form-mark-read-ajax');
                individualForms.forEach(form => form.remove());

                // Reset số đếm chưa đọc trên trang hiện tại về 0
                const unreadCountText = document.getElementById('unreadCountText');
                if (unreadCountText) {
                    unreadCountText.textContent = '0';
                }

                // Reset và ẩn Badge số lượng thông báo của Header chính
                const headerBadge = document.getElementById('notificationBadge');
                if (headerBadge) {
                    headerBadge.textContent = '0';
                    headerBadge.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Lỗi khi đánh dấu tất cả đã đọc:', error);
            });
        });
    }
});
</script>
@endsection
