@extends('layouts.app')

@section('title', 'Tạo thông báo khuyến mãi / thủ công')

@section('content')
<div class="container" style="padding: 28px 15px 50px; max-width: 1100px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; margin-bottom:20px;">
        <div>
            <h1 style="font-size:28px; font-weight:800; color:#111827;">Tạo thông báo khuyến mãi / thủ công</h1>
            <p style="margin-top:6px; color:#6b7280;">Gửi thông báo tới khách hàng, admin hoặc toàn bộ hệ thống.</p>
        </div>
        <a href="{{ route('notifications.index') }}" style="padding:10px 16px; border-radius:10px; background:#fff; border:1px solid #d1d5db; font-weight:700; color:#111827;">Xem thông báo của tôi</a>
    </div>

    <div style="display:grid; grid-template-columns: 1.2fr .8fr; gap:20px;">
        <form method="POST" action="{{ route('admin.notifications.store') }}" style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:20px; box-shadow:0 6px 24px rgba(0,0,0,.05);">
            @csrf
            <div style="display:grid; gap:14px;">
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:6px;">Đối tượng nhận</label>
                    <select name="target" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:10px;">
                        <option value="all">Tất cả người dùng</option>
                        <option value="users">Khách hàng</option>
                        <option value="admins">Admin / nhân sự nội bộ</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:6px;">Tiêu đề</label>
                    <input name="title" type="text" placeholder="Ví dụ: Flash Sale 50% hôm nay" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:10px;">
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:6px;">Nội dung</label>
                    <textarea name="content" rows="5" placeholder="Mô tả nội dung thông báo..." style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:10px;"></textarea>
                </div>
                <div>
                    <label style="display:block; font-weight:700; margin-bottom:6px;">Đường dẫn hành động</label>
                    <input name="action_url" type="text" placeholder="/products hoặc URL đầy đủ" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:10px;">
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px;">Sản phẩm liên quan</label>
                        <select name="product_id" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:10px;">
                            <option value="">-- Không chọn --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->product_id }}">#{{ $product->product_id }} - {{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-weight:700; margin-bottom:6px;">Mã KM / flash sale</label>
                        <select name="promo_id" style="width:100%; padding:12px 14px; border:1px solid #d1d5db; border-radius:10px;">
                            <option value="">-- Không chọn --</option>
                            @foreach($promoItems as $promo)
                                <option value="{{ $promo->promo_id }}">#{{ $promo->promo_id }} - {{ $promo->promo_type }} @if($promo->code) ({{ $promo->code }}) @endif</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" style="padding:12px 18px; border:none; border-radius:10px; background:#0046ab; color:#fff; font-weight:800; cursor:pointer; width:max-content;">Gửi thông báo</button>
            </div>
        </form>

        <div style="display:grid; gap:16px; align-content:start;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:18px;">
                <h3 style="font-size:16px; font-weight:800; margin-bottom:10px;">Gợi ý mẫu nhanh</h3>
                <div style="display:grid; gap:10px; font-size:13px; color:#4b5563; line-height:1.5;">
                    <div style="padding:12px; border-radius:12px; background:#f8fafc;">• Flash Sale: giảm sốc theo khung giờ, gắn link tới danh mục.</div>
                    <div style="padding:12px; border-radius:12px; background:#f8fafc;">• Coupon: thông báo mã giảm giá mới cho khách hàng.</div>
                    <div style="padding:12px; border-radius:12px; background:#f8fafc;">• Thủ công: admin chủ động gửi thông báo riêng theo chiến dịch.</div>
                </div>
            </div>

            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:18px;">
                <h3 style="font-size:16px; font-weight:800; margin-bottom:10px;">Lưu ý vận hành</h3>
                <ul style="padding-left:18px; color:#4b5563; font-size:13px; line-height:1.7;">
                    <li>Nên dùng nội dung ngắn gọn, có CTA rõ ràng.</li>
                    <li>Chỉ gửi cho đúng nhóm người dùng để tránh spam.</li>
                    <li>Nên gắn `action_url` tới trang sản phẩm hoặc trang khuyến mãi.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
