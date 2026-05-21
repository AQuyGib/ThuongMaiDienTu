@extends('layouts.app')

@section('title', 'Thông báo của tôi')

@section('content')
<div class="container" style="padding: 32px 15px 56px;">
    <div class="notification-hero">
        <div>
            <p class="notification-hero-kicker">Trung tâm thông báo</p>
            <h1 class="notification-hero-title">Thông báo của tôi</h1>
            <p class="notification-hero-subtitle">Bạn có <span id="unreadCountText">{{ $unreadCount }}</span> thông báo chưa đọc. Xem nhanh, lọc nhanh và không bỏ lỡ ưu đãi quan trọng.</p>
        </div>
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

    <div class="notification-grid">
        @forelse($notifications as $notification)
            @php
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
                ][$notification->type] ?? ['icon' => 'fa-bell', 'color' => 'default'];
            @endphp
            <article class="notification-card {{ $notification->read_at ? '' : 'is-unread' }}">
                <div class="notification-icon notification-{{ $typeMeta['color'] }}">
                    <i class="fa-solid {{ $typeMeta['icon'] }}"></i>
                </div>
                <div class="notification-body">
                    <div class="notification-head">
                        <div>
                            <h3 class="notification-title">{{ $notification->title }}</h3>
                            <div class="notification-meta">
                                <span>{{ $notification->type }}</span>
                                <span>{{ $notification->created_at?->format('H:i d/m/Y') }}</span>
                            </div>
                        </div>
                        @unless($notification->read_at)
                            <span class="notification-badge">Mới</span>
                        @endunless
                    </div>
                    <p class="notification-content">{{ $notification->content }}</p>
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
            <div class="notification-empty">
                <div class="notification-empty-icon"><i class="fa-regular fa-bell"></i></div>
                <h3>Chưa có thông báo nào</h3>
                <p>Khi có khuyến mãi, đơn hàng hoặc cập nhật mới, thông báo sẽ xuất hiện tại đây.</p>
            </div>
        @endforelse
    </div>

    <div style="margin-top:24px;">
        {{ $notifications->links() }}
    </div>
</div>

@push('styles')
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
.btn-primary-soft, .btn-outline-soft, .btn-success-soft {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 14px;
    font-weight: 800;
    transition: .2s ease;
}
.btn-primary-soft { background: #fff; color: #0f172a; }
.btn-primary-soft:hover { transform: translateY(-1px); }
.btn-outline-soft { border: 1px solid #dbe3f0; color: #0f172a; background: #fff; }
.btn-outline-soft:hover { background: #f8fafc; }
.btn-success-soft { border: none; background: #16a34a; color: #fff; }
.btn-success-soft:hover { background: #15803d; }
.notification-grid {
    display: grid;
    gap: 14px;
    margin-top: 22px;
}
.notification-card {
    display: grid;
    grid-template-columns: 64px 1fr;
    gap: 16px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 22px;
    padding: 18px;
    box-shadow: 0 8px 24px rgba(15,23,42,.05);
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
}
.notification-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 18px 40px rgba(15,23,42,.08);
}
.notification-card.is-unread {
    border-color: #bfd6ff;
    background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
}
.notification-icon {
    width: 64px; height: 64px; border-radius: 18px; display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #fff;
}
.notification-promo { background: linear-gradient(135deg, #7c3aed, #db2777); }
.notification-info { background: linear-gradient(135deg, #2563eb, #06b6d4); }
.notification-order { background: linear-gradient(135deg, #0f766e, #14b8a6); }
.notification-admin { background: linear-gradient(135deg, #111827, #4f46e5); }
.notification-article { background: linear-gradient(135deg, #f59e0b, #ef4444); }
.notification-review { background: linear-gradient(135deg, #10b981, #059669); }
.notification-warning { background: linear-gradient(135deg, #f97316, #f59e0b); }
.notification-default { background: linear-gradient(135deg, #64748b, #94a3b8); }
.notification-head { display:flex; justify-content: space-between; gap: 12px; align-items:flex-start; }
.notification-title { font-size: 18px; font-weight: 900; color: #0f172a; }
.notification-meta { display:flex; gap: 14px; flex-wrap:wrap; margin-top: 6px; font-size: 12px; color: #94a3b8; font-weight: 700; }
.notification-content { margin-top: 12px; color: #475569; line-height: 1.75; }
.notification-actions { display:flex; gap: 10px; flex-wrap:wrap; margin-top: 16px; }
.notification-badge {
    display:inline-flex; align-items:center; padding: 6px 10px; border-radius: 999px; background: #dbeafe; color:#1d4ed8; font-size: 12px; font-weight: 800;
}
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Xử lý nút "Đã đọc" của từng thông báo riêng lẻ
    const markReadForms = document.querySelectorAll('.form-mark-read-ajax');
    markReadForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const actionUrl = this.getAttribute('action');
            const card = this.closest('.notification-card');
            
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
                if (card) {
                    card.classList.remove('is-unread');
                    const badge = card.querySelector('.notification-badge');
                    if (badge) {
                        badge.remove();
                    }
                }
                this.remove();
                
                const unreadCountText = document.getElementById('unreadCountText');
                if (unreadCountText) {
                    let currentCount = parseInt(unreadCountText.textContent) || 0;
                    if (currentCount > 0) {
                        currentCount--;
                        unreadCountText.textContent = currentCount;
                    }
                }
                
                const headerBadge = document.getElementById('notificationBadge');
                if (headerBadge) {
                    let headerCount = parseInt(headerBadge.textContent) || 0;
                    if (headerCount > 0) {
                        headerCount--;
                        headerBadge.textContent = headerCount;
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

    // 2. Xử lý nút "Đánh dấu tất cả đã đọc"
    const readAllForm = document.getElementById('formReadAllNotifications');
    if (readAllForm) {
        readAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const actionUrl = this.getAttribute('action');

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
                const unreadCards = document.querySelectorAll('.notification-card.is-unread');
                unreadCards.forEach(card => {
                    card.classList.remove('is-unread');
                    const badge = card.querySelector('.notification-badge');
                    if (badge) {
                        badge.remove();
                    }
                });

                const individualForms = document.querySelectorAll('.form-mark-read-ajax');
                individualForms.forEach(form => form.remove());

                const unreadCountText = document.getElementById('unreadCountText');
                if (unreadCountText) {
                    unreadCountText.textContent = '0';
                }

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
