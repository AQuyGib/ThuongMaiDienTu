@extends('layouts.app')

@section('title', 'Trang Cá Nhân')

@push('styles')
<style>
    body {
        background-color: #f4f6f8;
    }
    .profile-container {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 20px;
        margin: 30px auto 50px;
        align-items: start;
    }
    
    /* ===== M-Member Card (Sidebar Top) ===== */
    .member-card {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }
    .member-card-header {
        background: #0046ab; /* Xanh DienMayPro */
        color: #fff;
        padding: 20px;
        position: relative;
    }
    .member-card-header::after {
        content: '';
        position: absolute;
        bottom: -20px; left: 0; right: 0;
        height: 40px;
        background: #fff;
        border-radius: 50% 50% 0 0 / 100% 100% 0 0;
    }
    .member-user-info {
        display: flex;
        align-items: center;
        gap: 12px;
        position: relative;
        z-index: 2;
    }
    .member-avatar {
        width: 48px;
        height: 48px;
        background: #fff;
        color: #0046ab;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 20px;
    }
    .member-details h3 {
        font-size: 16px;
        margin: 0;
        font-weight: 700;
    }
    .member-details p {
        font-size: 13px;
        margin: 0;
        opacity: 0.9;
    }
    
    .member-stats {
        padding: 0 20px 20px;
        background: #fff;
        position: relative;
        z-index: 2;
    }
    .stat-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    .stat-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    .stat-item span {
        font-size: 12px;
        color: #777;
    }
    .stat-item strong {
        font-size: 16px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .stat-progress {
        background: #f1f5f9;
        border-radius: 8px;
        padding: 12px;
        font-size: 12px;
        color: #555;
        margin-bottom: 15px;
    }
    .stat-progress .progress-bar {
        height: 6px;
        background: #ddd;
        border-radius: 3px;
        margin: 8px 0;
        overflow: hidden;
    }
    .stat-progress .progress-fill {
        height: 100%;
        background: #0046ab; /* Xanh DienMayPro */
        width: {{ $spendNeeded > 0 ? min(100, ($totalSpent / ($totalSpent + $spendNeeded)) * 100) : 100 }}%;
    }
    .member-channel {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8f9fa;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 12px;
        color: #555;
    }

    /* ===== Sidebar Menu ===== */
    .profile-nav {
        background: #fff;
        border-radius: 16px;
        padding: 10px 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .profile-nav-item {
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        color: #444;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
    }
    .profile-nav-item:hover, .profile-nav-item.active {
        background: #eef2ff;
        color: #0046ab;
    }
    .profile-nav-item i {
        width: 20px;
        text-align: center;
        font-size: 16px;
    }
    .nav-divider {
        height: 1px;
        background: #eee;
        margin: 5px 20px;
    }
    
    /* ===== Modal S-Student ===== */
    .student-modal-overlay {
        display: none;
        position: fixed; inset: 0; background: rgba(0,0,0,0.6);
        z-index: 10000; align-items: center; justify-content: center;
    }
    .student-modal-overlay.active { display: flex; }
    .student-modal {
        background: #fff; width: 90%; max-width: 450px;
        border-radius: 16px; overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .student-modal-header {
        background: #e21033; color: #fff; padding: 15px 20px;
        display: flex; justify-content: space-between; align-items: center;
    }
    .student-modal-header h3 { margin: 0; font-size: 16px; font-weight: bold; }
    .student-modal-body { padding: 20px; }
    .upload-box {
        border: 2px dashed #ccc; border-radius: 12px;
        padding: 30px; text-align: center; cursor: pointer;
        background: #f9fafb; transition: 0.2s; margin-bottom: 15px;
    }
    .upload-box:hover { border-color: #0046ab; background: #eef2ff; }
    .upload-box i { font-size: 30px; color: #0046ab; margin-bottom: 10px; }
    .upload-box p { margin: 0; font-size: 14px; color: #555; font-weight: 600; }
    
    /* Overrides for Business Modal (Red Theme) */
    #businessModal .upload-box:hover { border-color: #e21033; background: #fff1f2; }
    #businessModal .upload-box i { color: #e21033; }
    #businessModal .student-modal-body a { color: #e21033 !important; }
    #businessModal .btn-outline:hover { background: #e21033; color: #fff !important; }
    #businessModal .btn-update:hover { background: #b50d29; }
    .student-modal-footer {
        padding: 15px 20px; border-top: 1px solid #eee;
        display: flex; gap: 10px; justify-content: flex-end;
    }
    
    .btn-logout {
        width: 100%;
        background: transparent;
        color: #444;
        border: none;
        padding: 12px 20px;
        text-align: left;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 15px;
        transition: 0.2s;
    }
    .btn-logout:hover {
        background: #fcf1f3;
        color: #e21033;
    }

    /* ===== Main Content Area ===== */
    .profile-content {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .profile-tab { display: none; }
    .profile-tab.active { display: block; }
    
    /* Dashboard Cards */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .dash-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .dash-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .dash-card-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin: 0;
    }
    .dash-card-header a {
        font-size: 13px;
        color: #0046ab;
        text-decoration: none;
    }
    .dash-empty {
        text-align: center;
        padding: 20px 0;
        color: #777;
    }
    .dash-empty img {
        width: 100px;
        opacity: 0.5;
        margin-bottom: 10px;
    }
    .btn-outline {
        display: inline-block;
        border: 1px solid #0046ab;
        color: #0046ab;
        padding: 8px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-top: 10px;
        transition: 0.2s;
    }
    .btn-outline:hover {
        background: #0046ab;
        color: #fff;
    }

    /* Banners */
    .promo-banners {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }
    .promo-banner {
        flex: 1;
        background: linear-gradient(135deg, #0046ab, #002255);
        color: #fff;
        padding: 20px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .promo-banner:nth-child(2) {
        background: linear-gradient(135deg, #e21033, #990a22);
    }
    .promo-banner h4 { margin: 0 0 5px; font-size: 16px; }
    .promo-banner p { margin: 0; font-size: 12px; opacity: 0.8; }
    .promo-banner .btn-banner {
        background: #fff;
        color: #333;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: bold;
        text-decoration: none;
    }

    /* Account Info Cards */
    .account-cards-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    .acc-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .acc-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .acc-card-header h3 {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin: 0;
    }
    .acc-card-content {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .acc-info-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        border-bottom: 1px dashed #eee;
        padding-bottom: 8px;
    }
    .acc-info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .acc-info-label {
        color: #777;
    }
    .acc-info-value {
        color: #333;
        font-weight: 600;
        text-align: right;
    }

    /* Info Tab Form */
    .info-form-wrap {
        background: #fff;
        padding: 30px;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .info-form-wrap h3 { margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 8px; }
    .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; transition: 0.2s; }
    .form-control:focus { border-color: #0046ab; box-shadow: 0 0 0 3px rgba(0,70,171,0.1); }
    .form-control[readonly] { background: #f1f5f9; color: #888; cursor: not-allowed; }
    .btn-update { background: #0046ab; color: #fff; border: none; padding: 12px 25px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s; }
    .btn-update:hover { background: #003380; }

    /* Orders Tab */
    .order-table { width: 100%; border-collapse: collapse; }
    .order-table th, .order-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; font-size: 14px; }
    .order-table th { background: #f9fafb; font-weight: 600; color: #555; }
    .status-badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .status-pending { background: #fef08a; color: #854d0e; }
    .status-completed { background: #dcfce7; color: #166534; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }

    /* ===== Validation UI ===== */
    .form-control.is-invalid {
        border-color: #e21033 !important;
        background-color: #fff1f2 !important;
    }
    .upload-box.is-invalid {
        border-color: #e21033 !important;
        background-color: #fff1f2 !important;
    }
    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(226, 16, 51, 0.1) !important;
    }

    /* ===== Toast Notification ===== */
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .toast {
        min-width: 280px;
        background: #fff;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        transform: translateX(120%);
        transition: transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        border-left: 5px solid #0046ab;
    }
    .toast.active { transform: translateX(0); }
    .toast.success { border-left-color: #166534; }
    .toast.error { border-left-color: #e21033; }
    .toast.warning { border-left-color: #f59e0b; }
    .toast-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .toast.success .toast-icon { background: #dcfce7; color: #166534; }
    .toast.error .toast-icon { background: #fee2e2; color: #e21033; }
    .toast.warning .toast-icon { background: #fef3c7; color: #f59e0b; }
    .toast-content { flex: 1; }
    .toast-title { font-weight: 700; font-size: 14px; margin-bottom: 2px; display: block; }
    .toast-msg { font-size: 13px; color: #666; }

    /* ===== Custom Confirm Modal ===== */
    .confirm-modal-overlay {
        display: none;
        position: fixed; inset: 0; background: rgba(0,0,0,0.4);
        z-index: 10001; align-items: center; justify-content: center;
        backdrop-filter: blur(3px);
    }
    .confirm-modal-overlay.active { display: flex; }
    .confirm-modal {
        background: #fff; width: 90%; max-width: 400px;
        border-radius: 20px; padding: 25px; text-align: center;
        animation: modalScale 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes modalScale { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .confirm-icon {
        width: 60px; height: 60px; background: #fee2e2; color: #e21033;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px; font-size: 24px;
    }
    .confirm-modal h4 { margin: 0 0 10px; font-size: 18px; color: #333; }
    .confirm-modal p { color: #666; font-size: 14px; margin-bottom: 25px; line-height: 1.5; }
    .confirm-actions { display: flex; gap: 12px; }
    .confirm-actions button { flex: 1; padding: 12px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; border: none; }
    .btn-cancel { background: #f1f5f9; color: #475569; }
    .btn-cancel:hover { background: #e2e8f0; }
    .btn-confirm-delete { background: #e21033; color: #fff; }
    .btn-confirm-delete:hover { background: #b50d29; }

    /* Loading Spinner */
    .spinner {
        display: inline-block;
        width: 18px; height: 18px;
        border: 2px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s linear infinite;
        vertical-align: middle;
        margin-right: 8px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    

    /* ===== Wishlist Grid ===== */
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }
    .wishlist-item {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        border: 1px solid #eee;
        position: relative;
    }
    .wishlist-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .wishlist-item-img {
        position: relative;
        height: 200px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px;
        overflow: hidden;
    }
    .wishlist-item-img img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }
    .remove-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.9);
        border: none;
        color: #666;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: 0.2s;
    }
    .remove-btn:hover {
        background: #d70018;
        color: #fff;
    }
    .wishlist-item-info {
        padding: 15px;
    }
    .wishlist-item-name {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #333;
        text-decoration: none;
        margin-bottom: 8px;
        height: 40px;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.4;
    }
    .wishlist-item-name:hover {
        color: #0046ab;
    }
    .wishlist-item-price {
        font-size: 16px;
        font-weight: 700;
        color: #d70018;
        margin-bottom: 12px;
    }
    .btn-add-cart-wishlist {
        width: 100%;
        padding: 10px;
        background: #0046ab;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-add-cart-wishlist:hover {
        background: #003580;
    }

    /* ===== Membership & Promo Tab ===== */
    .tier-card {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        color: #fff;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        position: relative;
        overflow: hidden;
    }
    .tier-card::after {
        content: '\f554';
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        right: -20px;
        bottom: -20px;
        font-size: 120px;
        opacity: 0.1;
        transform: rotate(-15deg);
    }
    .tier-name { font-size: 24px; font-weight: 800; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
    .tier-badge { font-size: 12px; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    
    .progress-container { margin-top: 20px; }
    .progress-label { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px; opacity: 0.9; }
    .progress-bar-bg { background: rgba(255,255,255,0.1); height: 8px; border-radius: 10px; overflow: hidden; }
    .progress-bar-fill { background: #0046ab; height: 100%; border-radius: 10px; transition: width 1s ease-in-out; }

    .benefit-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 25px; }
    .benefit-item { background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid #e2e8f0; }
    .benefit-item i { color: #0046ab; margin-bottom: 10px; font-size: 20px; display: block; }
    .benefit-item h5 { margin: 0 0 5px; font-size: 14px; color: #333; }
    .benefit-item p { margin: 0; font-size: 12px; color: #666; line-height: 1.4; }

    .voucher-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px; margin-top: 20px; }
    .voucher-card {
        background: #fff;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        display: flex;
        overflow: hidden;
        transition: 0.3s;
    }
    .voucher-card:hover { border-color: #0046ab; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .voucher-left {
        width: 80px;
        background: #0046ab;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .voucher-right { padding: 15px; flex: 1; position: relative; }
    .voucher-code { font-family: monospace; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 700; color: #334155; font-size: 12px; }
    .voucher-title { font-size: 14px; font-weight: 700; color: #333; margin: 8px 0 4px; }
    .voucher-expiry { font-size: 11px; color: #94a3b8; }
    .btn-copy-voucher {
        position: absolute; right: 15px; top: 15px;
        background: none; border: 1px solid #0046ab; color: #0046ab;
        padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer;
    }
    .btn-copy-voucher:hover { background: #0046ab; color: #fff; }

</style>
@endpush

@section('content')
<div class="container">
    <nav class="breadcrumb" style="margin-top: 20px;">
        <a href="{{ route('home') }}"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <i class="fa-solid fa-angle-right" style="font-size:10px;color:#bbb"></i>
        <span>M-Member</span>
    </nav>

    <div class="profile-container">
        <!-- ===== SIDEBAR ===== -->
        <div>
            <!-- Thẻ thành viên -->
            <div class="member-card">
                <div class="member-card-header">
                    <div class="member-user-info">
                        <div class="member-avatar">
                            {{ substr(explode(' ', $user->full_name)[0], 0, 1) }}
                        </div>
                        <div class="member-details">
                            <h3>{{ $user->full_name }}</h3>
                            <p>{{ $user->phone_number ?? '039*****96' }}</p>
                        </div>
                    </div>
                </div>
                <div class="member-stats">
                    <div class="stat-row">
                        <div class="stat-item">
                            <span>Tổng số đơn hàng</span>
                            <strong><i class="fa-solid fa-basket-shopping" style="color: #0046ab;"></i> {{ $totalOrders }}</strong>
                        </div>
                        <div class="stat-item" style="text-align: right; align-items: flex-end;">
                            <span>Tổng tiền tích lũy</span>
                            <strong style="color: #0046ab;">{{ number_format($totalSpent, 0, ',', '.') }}đ</strong>
                        </div>
                    </div>
                    
                    <div class="stat-progress">
                        <div style="display: flex; justify-content: space-between;">
                            <span>Hạng hiện tại: <strong>{{ $currentTier }}</strong></span>
                            @if($spendNeeded > 0)
                                <span style="color: #0046ab; font-weight: 600;">Lên hạng {{ $nextTier }}</span>
                            @endif
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $tierProgress }}%;"></div>
                        </div>
                        @if($spendNeeded > 0)
                            <div style="font-size: 11px; text-align: center;">Cần chi tiêu thêm <strong style="color: #0046ab;">{{ number_format($spendNeeded, 0, ',', '.') }}đ</strong> để lên hạng {{ $nextTier }}</div>
                        @else
                            <div style="font-size: 11px; text-align: center; color: #166534;">Bạn đã đạt hạng cao nhất!</div>
                        @endif
                    </div>
                    
                    <div class="member-channel">
                        <span>Bạn đang ở kênh thành viên</span>
                        <strong style="color: #0046ab;"><i class="fa-solid fa-shield-halved"></i> DienMayPro</strong>
                    </div>
                </div>
            </div>

            <!-- Menu Điều hướng -->
            <div class="profile-nav">
                <div class="profile-nav-item active" onclick="switchTab('dashboard-tab', this)">
                    <i class="fa-solid fa-house-user"></i> Tổng quan
                </div>
                <div class="profile-nav-item" onclick="switchTab('orders-tab', this)">
                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử mua hàng
                </div>
                <div class="profile-nav-item" onclick="switchTab('info-tab', this)">
                    <i class="fa-solid fa-user-pen"></i> Thông tin tài khoản
                </div>
                <div class="profile-nav-item" onclick="switchTab('wishlist-tab', this)">
                    <i class="fa-solid fa-heart"></i> Danh sách yêu thích
                </div>

                <div class="nav-divider"></div>
                <div class="profile-nav-item" onclick="switchTab('promo-tab', this)">
                    <i class="fa-solid fa-ticket"></i> Hạng thành viên & Ưu đãi
                </div>
                <div class="profile-nav-item" onclick="switchTab('login-history-tab', this)">
                    <i class="fa-solid fa-shield-halved"></i> Lịch sử đăng nhập
                </div>
                <div class="nav-divider"></div>
                <form action="{{ route('logout') ?? '/logout' }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-logout">
                        <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>

        <!-- ===== MAIN CONTENT ===== -->
        <div class="profile-content">
            
            <!-- TAB TỔNG QUAN -->
            <div id="dashboard-tab" class="profile-tab active">
                
                <!-- Banners Khuyến mãi -->
                <div class="promo-banners">
                    <div class="promo-banner">
                        <div>
                            <h4>D-Student & D-Teacher</h4>
                            <p>Nhận thêm ưu đãi tới 700k/sản phẩm</p>
                        </div>
                        <a href="#" class="btn-banner" onclick="event.preventDefault(); openStudentModal();">Đăng ký ngay</a>
                    </div>
                    <div class="promo-banner">
                        <div>
                            <h4>D-Business</h4>
                            <p>Nhận ưu đãi đặc quyền cho doanh nghiệp!</p>
                        </div>
                        <a href="#" class="btn-banner" onclick="event.preventDefault(); openBusinessModal();">Đăng ký ngay</a>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <!-- Đơn hàng gần đây -->
                    <div class="dash-card">
                        <div class="dash-card-header">
                            <h3>Đơn hàng gần đây</h3>
                            <a href="#" onclick="switchTab('orders-tab', document.querySelectorAll('.profile-nav-item')[1])">Xem tất cả</a>
                        </div>
                        @if($orders->count() > 0)
                            <div style="font-size: 13px; color: #555;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                                    <strong>#{{ $orders->first()->order_id }}</strong>
                                    <span style="color: #e21033; font-weight: bold;">{{ number_format($orders->first()->final_amount ?? 0, 0, ',', '.') }}đ</span>
                                </div>
                                <a href="#" class="btn-outline" style="width: 100%; text-align: center;" onclick="switchTab('orders-tab', document.querySelectorAll('.profile-nav-item')[1])">Xem lịch sử</a>
                            </div>
                        @else
                            <div class="dash-empty">
                                <i class="fa-solid fa-box-open" style="font-size: 40px; color: #ddd; margin-bottom: 10px;"></i>
                                <p style="font-size: 13px;">Bạn chưa có đơn hàng nào gần đây? Hãy bắt đầu mua sắm ngay nào!</p>
                                <a href="{{ route('cart.index') }}" class="btn-outline">Mua sắm ngay</a>
                            </div>
                        @endif
                    </div>

                    <!-- Ưu đãi của bạn -->
                    <div class="dash-card">
                        <div class="dash-card-header">
                            <h3>Ưu đãi của bạn</h3>
                            <a href="#" onclick="switchTab('promo-tab', document.querySelectorAll('.profile-nav-item')[4])">Xem tất cả</a>
                        </div>
                        <div class="dash-empty">
                            <i class="fa-solid fa-ticket" style="font-size: 40px; color: #ddd; margin-bottom: 10px;"></i>
                            <p style="font-size: 13px;">Bạn chưa có ưu đãi nào.</p>
                            <a href="{{ route('home') }}" class="btn-outline">Xem sản phẩm</a>
                        </div>
                    </div>

                    <!-- Sổ địa chỉ -->
                    <div class="dash-card" style="grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div style="width: 40px; height: 40px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0046ab; font-size: 18px;">
                                <i class="fa-solid fa-location-dot"></i>
                            </div>
                            <div>
                                @if($user->addresses->count() > 0)
                                    <h3 style="margin: 0 0 5px; font-size: 15px;">{{ $user->addresses->first()->name ?? $user->full_name }} <span class="status-badge" style="background:#bae6fd; color:#0369a1; font-size:10px; padding: 2px 6px;">{{ $user->addresses->first()->type }}</span></h3>
                                    <p style="margin: 0; font-size: 13px; color: #777;">{{ $user->addresses->first()->street }}, {{ $user->addresses->first()->ward }}, {{ $user->addresses->first()->district }}, {{ $user->addresses->first()->city }}</p>
                                @else
                                    <h3 style="margin: 0 0 5px; font-size: 15px;">Thêm địa chỉ để đặt đơn hàng nhanh hơn</h3>
                                    <p style="margin: 0; font-size: 13px; color: #777;">Địa chỉ của bạn đang trống.</p>
                                @endif
                            </div>
                        </div>
                        <a href="#" class="btn-outline" onclick="switchTab('info-tab', document.querySelectorAll('.profile-nav-item')[2])">Quản lý địa chỉ</a>
                    </div>
                </div>
            </div>

            <!-- TAB LỊCH SỬ MUA HÀNG -->
            <div id="orders-tab" class="profile-tab">
                <div class="info-form-wrap">
                    <h3>Lịch Sử Đơn Hàng</h3>
                    @if($orders->count() > 0)
                        <div style="overflow-x: auto;">
                            <table class="order-table">
                                <thead>
                                    <tr>
                                        <th>Mã Đơn</th>
                                        <th>Ngày Đặt</th>
                                        <th>Tổng Tiền</th>
                                        <th>Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td><strong>#{{ $order->order_id }}</strong></td>
                                        <td>Không xác định</td>
                                        <td style="color: #e21033; font-weight: bold;">{{ number_format($order->final_amount ?? 0, 0, ',', '.') }}đ</td>
                                        <td>
                                            @if($order->status == 'Pending')
                                                <span class="status-badge status-pending">Đang xử lý</span>
                                            @elseif($order->status == 'Delivered')
                                                <span class="status-badge status-completed">Thành công</span>
                                            @elseif($order->status == 'Shipping')
                                                <span class="status-badge" style="background:#bae6fd; color:#0369a1;">Đang giao</span>
                                            @else
                                                <span class="status-badge status-cancelled">{{ $order->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="dash-empty" style="padding: 50px 0;">
                            <i class="fa-solid fa-box-open" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                            <p>Bạn chưa có đơn hàng nào.</p>
                            <a href="{{ route('cart.index') }}" class="btn-outline">Tiếp tục mua sắm</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB THÔNG TIN TÀI KHOẢN -->
            <div id="info-tab" class="profile-tab">
                <div class="account-cards-grid">
                    
                    <!-- Thông tin cá nhân -->
                    <div class="acc-card" style="grid-column: 1 / -1;">
                        <div class="acc-card-header">
                            <h3>Thông tin cá nhân</h3>
                            <a href="#" style="color: #0046ab; font-size: 13px; font-weight: 600;" onclick="event.preventDefault(); document.getElementById('viewProfileInfo').style.display = 'none'; document.getElementById('editProfileForm').style.display = 'block'; this.style.display = 'none';">Sửa</a>
                        </div>
                        
                        @if(session('success'))
                            <div style="background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-weight: 600; font-size: 13px;">
                                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                            </div>
                        @endif

                        <div class="acc-card-content" id="viewProfileInfo">
                            <div class="acc-info-row">
                                <span class="acc-info-label">Họ và tên:</span>
                                <span class="acc-info-value">{{ $user->full_name }}</span>
                            </div>
                            <div class="acc-info-row">
                                <span class="acc-info-label">Giới tính:</span>
                                <span class="acc-info-value">{{ $user->gender ?? '-' }}</span>
                            </div>
                            <div class="acc-info-row">
                                <span class="acc-info-label">Ngày sinh:</span>
                                <span class="acc-info-value">{{ $user->dob ? \Carbon\Carbon::parse($user->dob)->format('d/m/Y') : '-' }}</span>
                            </div>
                            <div class="acc-info-row">
                                <span class="acc-info-label">Số điện thoại:</span>
                                <span class="acc-info-value">{{ $user->phone_number ?? '-' }}</span>
                            </div>
                            <div class="acc-info-row">
                                <span class="acc-info-label">Email:</span>
                                <span class="acc-info-value">{{ $user->email }}</span>
                            </div>
                            <div class="acc-info-row">
                                <span class="acc-info-label">Địa chỉ mặc định:</span>
                                <span class="acc-info-value">{{ $user->address ?? '-' }}</span>
                            </div>
                            <div class="acc-info-row">
                                <span class="acc-info-label">Ngày tham gia:</span>
                                <span class="acc-info-value">{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Không rõ' }}</span>
                            </div>
                        </div>

                        <!-- Form ẩn để cập nhật (Khi bấm Sửa) -->
                        <form id="editProfileForm" action="{{ route('profile.update') }}" method="POST" style="display: none; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                            @csrf
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Họ và tên</label>
                                <input type="text" name="full_name" class="form-control" value="{{ $user->full_name }}" required>
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Địa chỉ Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" readonly style="background: #f1f5f9; cursor: not-allowed;">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Giới tính</label>
                                <select name="gender" class="form-control">
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam" {{ $user->gender == 'Nam' ? 'selected' : '' }}>Nam</option>
                                    <option value="Nữ" {{ $user->gender == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                    <option value="Khác" {{ $user->gender == 'Khác' ? 'selected' : '' }}>Khác</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Ngày sinh</label>
                                <input type="date" name="dob" class="form-control" value="{{ $user->dob }}">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Số điện thoại</label>
                                <input type="tel" name="phone_number" class="form-control" value="{{ $user->phone_number }}" placeholder="Nhập số điện thoại">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Địa chỉ mặc định</label>
                                <input type="text" name="address" class="form-control" value="{{ $user->address }}" placeholder="Nhập địa chỉ của bạn">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Ngày tham gia</label>
                                <input type="text" class="form-control" value="{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Không rõ' }}" readonly style="background: #f1f5f9; cursor: not-allowed;">
                            </div>
                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                <button type="button" class="btn-outline" onclick="document.getElementById('editProfileForm').style.display='none'; document.getElementById('viewProfileInfo').style.display='flex'; document.querySelector('.acc-card-header a').style.display='block';">Hủy</button>
                                <button type="submit" class="btn-update" style="margin-top: 10px; padding: 8px 20px; background: #0046ab;">Lưu</button>
                            </div>
                        </form>
                    </div>

                    <!-- Sổ địa chỉ -->
                    <div class="acc-card" style="grid-column: 1 / -1;">
                        <div class="acc-card-header">
                            <h3>Địa chỉ</h3>
                            <a href="#" style="color: #0046ab; font-size: 13px; font-weight: 600;" onclick="event.preventDefault(); openAddressModal();">+ Thêm địa chỉ</a>
                        </div>
                        @if($user->addresses->count() > 0)
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; margin-top: 15px;">
                                @foreach($user->addresses as $address)
                                <div style="border: 1px solid #eee; border-radius: 12px; padding: 15px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                        <strong style="font-size: 14px; color: #333;">
                                            {{ $address->name ?? $user->full_name }}
                                            @if($address->is_default)
                                                <span style="color: #e21033; font-size: 11px; margin-left: 5px;">[Mặc định]</span>
                                            @endif
                                        </strong>
                                        <span class="status-badge" style="background:#bae6fd; color:#0369a1; font-size:10px; padding: 2px 6px;">{{ $address->type }}</span>
                                    </div>
                                    <p style="font-size: 13px; color: #666; margin: 0 0 10px 0; line-height: 1.5;">
                                        {{ $address->street }}<br>
                                        {{ $address->ward }}, {{ $address->district }}, {{ $address->city }}
                                    </p>
                                    <div style="display: flex; gap: 10px;">
                                        <button class="btn-outline" style="padding: 4px 10px; font-size: 12px; margin: 0;" onclick="editAddress({{ $address->id }}, '{{ $address->city }}', '{{ $address->district }}', '{{ $address->ward }}', '{{ $address->street }}', '{{ $address->name }}', '{{ $address->type }}', {{ $address->is_default ? 'true' : 'false' }})">
                                            <i class="fa-solid fa-pen-to-square"></i> Sửa
                                        </button>
                                        <button class="btn-outline" style="padding: 4px 10px; font-size: 12px; margin: 0;" onclick="deleteAddress({{ $address->id }})">
                                            <i class="fa-solid fa-trash-can"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="dash-empty" style="padding: 15px 0;">
                                <i class="fa-solid fa-location-dot" style="font-size: 30px; color: #ddd; margin-bottom: 10px;"></i>
                                <p style="font-size: 13px;">Bạn chưa có địa chỉ nào được tạo</p>
                            </div>
                        @endif
                    </div>

                    <!-- Mật khẩu -->
                    <div class="acc-card" style="grid-column: 1 / -1;">
                        <div class="acc-card-header">
                            <h3>Mật khẩu</h3>
                            <a href="#" id="btnShowPasswordForm" style="color: #0046ab; font-size: 13px; font-weight: 600;" onclick="event.preventDefault(); document.getElementById('viewPasswordInfo').style.display = 'none'; document.getElementById('updatePasswordForm').style.display = 'block'; this.style.display = 'none';">Cập nhật</a>
                        </div>
                        
                        @if(session('password_success'))
                            <div style="background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-weight: 600; font-size: 13px;">
                                <i class="fa-solid fa-circle-check"></i> {{ session('password_success') }}
                            </div>
                        @endif
                        @if($errors->has('current_password') || $errors->has('new_password'))
                            <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-weight: 600; font-size: 13px;">
                                <i class="fa-solid fa-circle-xmark"></i> Có lỗi xảy ra khi cập nhật mật khẩu. Vui lòng kiểm tra lại.
                            </div>
                        @endif

                        <div class="acc-info-row" style="border: none;" id="viewPasswordInfo">
                            <span class="acc-info-label">Cập nhật lần cuối lúc:</span>
                            <span class="acc-info-value">{{ $user->password_changed_at ? \Carbon\Carbon::parse($user->password_changed_at)->format('d/m/Y H:i') : 'Chưa cập nhật' }}</span>
                        </div>

                        <!-- Form cập nhật mật khẩu -->
                        <form id="updatePasswordForm" action="{{ route('profile.password.update') }}" method="POST" style="display: {{ ($errors->has('current_password') || $errors->has('new_password')) ? 'block' : 'none' }}; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                            @csrf
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Mật khẩu hiện tại</label>
                                <input type="password" name="current_password" class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" required placeholder="Nhập mật khẩu hiện tại">
                                @error('current_password')
                                    <span style="color: #e21033; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Mật khẩu mới</label>
                                <input type="password" name="new_password" class="form-control {{ $errors->has('new_password') ? 'is-invalid' : '' }}" required placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)">
                                @error('new_password')
                                    <span style="color: #e21033; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Xác nhận mật khẩu mới</label>
                                <input type="password" name="new_password_confirmation" class="form-control" required placeholder="Nhập lại mật khẩu mới">
                            </div>
                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                <button type="button" class="btn-outline" onclick="document.getElementById('updatePasswordForm').style.display='none'; document.getElementById('viewPasswordInfo').style.display='flex'; document.getElementById('btnShowPasswordForm').style.display='block';">Hủy</button>
                                <button type="submit" class="btn-update" style="margin-top: 10px; padding: 8px 20px; background: #0046ab;">Cập nhật mật khẩu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TAB DANH SÁCH YÊU THÍCH -->
            <div id="wishlist-tab" class="profile-tab">
                <div class="info-form-wrap">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h3 style="margin: 0;">Danh sách yêu thích ({{ count($wishlistItems) }})</h3>
                        @if(count($wishlistItems) > 0)
                            <form action="{{ route('wishlist.clear') }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa toàn bộ danh sách yêu thích?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-outline" style="color: #d70018; border-color: #d70018;">
                                    <i class="fa-solid fa-trash-can"></i> Xóa tất cả
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(count($wishlistItems) > 0)
                        <div class="wishlist-grid">
                            @foreach($wishlistItems as $item)
                                @if($item->product)
                                    <div class="wishlist-item" id="wishlist-item-{{ $item->id }}">
                                        <div class="wishlist-item-img">
                                            <a href="{{ route('product.show', $item->product->product_id) }}">
                                                <img src="{{ $item->product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=300' }}" alt="{{ $item->product->name }}">
                                            </a>
                                            <button class="remove-btn" onclick="removeFromWishlist({{ $item->id }})" title="Xóa khỏi yêu thích">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        <div class="wishlist-item-info">
                                            <a href="{{ route('product.show', $item->product->product_id) }}" class="wishlist-item-name">{{ $item->product->name }}</a>
                                            <div class="wishlist-item-price">{{ number_format($item->product->base_price, 0, ',', '.') }}đ</div>
                                            <button class="btn-add-cart-wishlist" onclick="addToCart('{{ $item->product->product_id }}')">
                                                <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
                                            </button>
                                            <div style="margin-top: 10px; text-align: center;">
                                                <a href="javascript:void(0)" onclick="addToCompare('{{ $item->product->product_id }}')" 
                                                   style="font-size: 11px; color: #666; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 5px; font-weight: 500;"
                                                   onmouseover="this.style.color='#0046ab'" onmouseout="this.style.color='#666'">
                                                    <i class="fa-solid fa-scale-balanced"></i> So sánh
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="dash-empty" style="padding: 50px 0;">
                            <i class="fa-solid fa-heart-crack" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                            <p>Danh sách yêu thích của bạn đang trống.</p>
                            <a href="{{ route('home') }}" class="btn-outline">Khám phá sản phẩm</a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- CÁC TAB KHÁC -->


            <div id="promo-tab" class="profile-tab">
                <div class="info-form-wrap">
                    <h3 style="margin-bottom: 25px;">Hạng Thành Viên & Đặc Quyền</h3>
                    
                    @php
                        $percent = $tierProgress;
                    @endphp

                    <!-- Tier Card -->
                    <div class="tier-card">
                        <div class="tier-name">
                            Hạng {{ $currentTier }} <span class="tier-badge">Thành viên thân thiết</span>
                        </div>
                        <div style="font-size: 14px; opacity: 0.8;">Tổng chi tiêu tích lũy: <strong>{{ number_format($totalSpent, 0, ',', '.') }}đ</strong></div>
                        
                        <div class="progress-container">
                            <div class="progress-label">
                                <span>Tiến trình nâng hạng {{ $nextTier }}</span>
                                <span>{{ number_format($totalSpent, 0, ',', '.') }} / {{ number_format($totalSpent + $spendNeeded, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: {{ $percent }}%;"></div>
                            </div>
                            @if($spendNeeded > 0)
                                <div style="font-size: 12px; margin-top: 10px; opacity: 0.8;">
                                    <i class="fa-solid fa-circle-info"></i> Bạn cần chi tiêu thêm <strong>{{ number_format($spendNeeded, 0, ',', '.') }}đ</strong> để lên hạng {{ $nextTier }}.
                                </div>
                            @else
                                <div style="font-size: 12px; margin-top: 10px; opacity: 0.8;">
                                    <i class="fa-solid fa-crown"></i> Chúc mừng! Bạn đã đạt hạng cao nhất.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Benefits -->
                    <h4 style="font-size: 16px; margin: 30px 0 15px;">Đặc quyền của bạn</h4>
                    <div class="benefit-grid">
                        <div class="benefit-item">
                            <i class="fa-solid fa-truck-fast"></i>
                            <h5>Miễn phí vận chuyển</h5>
                            <p>Tối đa 3 đơn hàng/tháng cho hạng {{ $currentTier }}.</p>
                        </div>
                        <div class="benefit-item">
                            <i class="fa-solid fa-coins"></i>
                            <h5>Tích lũy điểm x2</h5>
                            <p>Nhận gấp đôi điểm thưởng cho mỗi hóa đơn mua sắm.</p>
                        </div>
                        <div class="benefit-item">
                            <i class="fa-solid fa-headset"></i>
                            <h5>Hỗ trợ ưu tiên</h5>
                            <p>Đường dây nóng riêng biệt xử lý yêu cầu trong 1h.</p>
                        </div>
                        <div class="benefit-item">
                            <i class="fa-solid fa-cake-candles"></i>
                            <h5>Quà tặng sinh nhật</h5>
                            <p>Voucher giảm giá 20% trong tháng sinh nhật của bạn.</p>
                        </div>
                    </div>

                    <!-- Vouchers -->
                    <h4 style="font-size: 16px; margin: 40px 0 15px;">Mã giảm giá khả dụng</h4>
                    <div class="voucher-grid">
                        <div class="voucher-card">
                            <div class="voucher-left"><i class="fa-solid fa-percent"></i></div>
                            <div class="voucher-right">
                                <span class="voucher-code">DIENMAYNEW</span>
                                <button class="btn-copy-voucher" onclick="copyVoucher('DIENMAYNEW', this)">Sao chép</button>
                                <div class="voucher-title">Giảm 50k cho đơn từ 500k</div>
                                <div class="voucher-expiry">HSD: 31/12/2026</div>
                            </div>
                        </div>
                        <div class="voucher-card">
                            <div class="voucher-left" style="background: #e21033;"><i class="fa-solid fa-truck"></i></div>
                            <div class="voucher-right">
                                <span class="voucher-code">FREESHIP26</span>
                                <button class="btn-copy-voucher" onclick="copyVoucher('FREESHIP26', this)">Sao chép</button>
                                <div class="voucher-title">Miễn phí vận chuyển toàn quốc</div>
                                <div class="voucher-expiry">HSD: 01/06/2026</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="login-history-tab" class="profile-tab">
                <div class="info-form-wrap">
                    <h3>Lịch Sử Đăng Nhập</h3>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Thời Gian</th>
                                <th>Địa chỉ IP</th>
                                <th>Trình Duyệt/Thiết Bị</th>
                                <th>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($loginHistories as $history)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($history->login_at)->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $history->ip_address }}</td>
                                <td title="{{ $history->user_agent }}">
                                    {{ $history->device_display }}
                                </td>
                                <td>
                                    @if($loop->first)
                                        <span class="status-badge status-completed" style="background: #dcfce7; color: #166534;">Đang dùng</span>
                                    @else
                                        <span class="status-badge" style="background: #f1f5f9; color: #64748b;">Đã lưu</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 30px; color: #999;">
                                    Chưa có dữ liệu lịch sử đăng nhập.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Đăng ký S-Student -->
<div id="studentModal" class="student-modal-overlay">
    <div class="student-modal">
        <div class="student-modal-header" style="background: #0046ab;">
            <h3>Đăng ký mới D-Student/D-Teacher</h3>
            <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 18px;" onclick="closeStudentModal()"></i>
        </div>
        <div class="student-modal-body">
            <div style="font-size: 13px; color: #555; margin-bottom: 15px;">
                Vui lòng nhập thông tin và tải lên ảnh chụp Thẻ sinh viên/Giáo viên của bạn để xác minh.
            </div>

            <form id="studentRegistrationForm">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Họ và tên *</label>
                    <input type="text" id="studentFullName" class="form-control" value="{{ $user->full_name }}" style="padding: 10px 15px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Trường ĐH/CĐ/THPT *</label>
                    <input type="text" id="schoolName" class="form-control" placeholder="Nhập tên trường của bạn" required style="padding: 10px 15px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Mã Sinh viên/Giáo viên *</label>
                    <input type="text" id="studentId" class="form-control" placeholder="Nhập mã thẻ" required style="padding: 10px 15px;">
                </div>
                
                <input type="file" id="studentCardUpload" style="display: none;" accept="image/*" onchange="previewStudentCard(this)">
                
                <div class="upload-box" onclick="document.getElementById('studentCardUpload').click()" style="padding: 20px;">
                    <div id="uploadPlaceholder">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 24px; margin-bottom: 5px;"></i>
                        <p>Tải ảnh thẻ</p>
                    </div>
                    <img id="uploadPreview" src="" alt="Preview" style="display:none; max-width:100%; max-height:150px; border-radius:8px; margin:0 auto;">
                </div>
                
                <div style="font-size: 12px; color: #888; text-align: center; font-style: italic; margin-top: 10px;">
                    * Nếu thẻ sinh viên không có niên khóa, vui lòng bổ sung thêm hình ảnh xác minh thời gian học.
                </div>
            </form>
        </div>
        <div class="student-modal-footer">
            <button type="button" class="btn-outline" style="margin-top: 0;" onclick="closeStudentModal()">Đóng</button>
            <button type="button" id="studentSubmitBtn" class="btn-update" style="margin-top: 0; padding: 8px 20px;" onclick="submitStudentRegistration()">Đăng ký</button>
        </div>
    </div>
</div>

<!-- Modal Đăng ký S-Business -->
<div id="businessModal" class="student-modal-overlay">
    <div class="student-modal" style="max-width: 500px;">
        <div class="student-modal-header" style="background: #e21033;">
            <h3>Đăng ký mới D-Business</h3>
            <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 18px;" onclick="closeBusinessModal()"></i>
        </div>
        <div class="student-modal-body" style="max-height: 70vh; overflow-y: auto;">
            <div style="font-size: 13px; color: #555; margin-bottom: 15px;">
                Vui lòng cung cấp thông tin doanh nghiệp và tải lên các giấy tờ cần thiết.
            </div>

            <form id="businessRegistrationForm">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Tên Doanh nghiệp *</label>
                    <input type="text" id="companyName" class="form-control" placeholder="Ví dụ: Công ty TNHH DienMayPro" required style="padding: 10px 15px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Mã số Thuế *</label>
                    <input type="text" id="taxId" class="form-control" placeholder="Nhập mã số thuế" required style="padding: 10px 15px;">
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label style="font-size: 13px; font-weight: 600; color: #555; margin: 0;">1. Giấy phép kinh doanh *</label>
                </div>
                <input type="file" id="businessLicenseUpload" style="display: none;" accept="image/*,.pdf" onchange="previewFile(this, 'licensePlaceholder', 'licensePreview')">
                <div class="upload-box" onclick="document.getElementById('businessLicenseUpload').click()" style="padding: 15px; margin-bottom: 20px;">
                    <div id="licensePlaceholder">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 20px; margin-bottom: 5px;"></i>
                        <p style="font-size: 12px;">Tải lên Giấy phép kinh doanh</p>
                    </div>
                    <img id="licensePreview" src="" alt="Preview" style="display:none; max-width:100%; max-height:100px; border-radius:8px; margin:0 auto;">
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <label style="font-size: 13px; font-weight: 600; color: #555; margin: 0;">2. Giấy ủy quyền *</label>
                </div>
                <input type="file" id="authorizationUpload" style="display: none;" accept="image/*,.pdf" onchange="previewFile(this, 'authPlaceholder', 'authPreview')">
                <div class="upload-box" onclick="document.getElementById('authorizationUpload').click()" style="padding: 15px;">
                    <div id="authPlaceholder">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 20px; margin-bottom: 5px;"></i>
                        <p style="font-size: 12px;">Tải lên Giấy ủy quyền</p>
                    </div>
                    <img id="authPreview" src="" alt="Preview" style="display:none; max-width:100%; max-height:100px; border-radius:8px; margin:0 auto;">
                </div>
            </form>
        </div>
        <div class="student-modal-footer">
            <button type="button" class="btn-outline" style="margin-top: 0; border-color: #e21033; color: #e21033;" onclick="closeBusinessModal()">Đóng</button>
            <button type="button" id="businessSubmitBtn" class="btn-update" style="margin-top: 0; padding: 8px 20px; background: #e21033;" onclick="submitBusinessRegistration()">Đăng ký</button>
        </div>
    </div>
</div>

<!-- Modal Thêm Địa Chỉ -->
<div id="addressModal" class="student-modal-overlay">
    <div class="student-modal" style="max-width: 500px;">
        <div class="student-modal-header" style="background: #0046ab;">
            <h3 id="addressModalTitle">Thêm địa chỉ mới</h3>
            <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 18px;" onclick="closeAddressModal()"></i>
        </div>
        <div class="student-modal-body" style="max-height: 70vh; overflow-y: auto;">
            <form id="addAddressForm">
                <input type="hidden" id="addrId" value="">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Tỉnh/Thành phố *</label>
                    <select id="addrCity" class="form-control" required style="padding: 10px 15px;">
                        <option value="">Đang tải danh sách Tỉnh/Thành phố...</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Quận/Huyện *</label>
                    <select id="addrDistrict" class="form-control" required style="padding: 10px 15px;" disabled>
                        <option value="">Chọn Quận/Huyện</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Phường/Xã *</label>
                    <select id="addrWard" class="form-control" required style="padding: 10px 15px;" disabled>
                        <option value="">Chọn Phường/Xã</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Địa chỉ (Số nhà, tên đường) *</label>
                    <input type="text" id="addrStreet" class="form-control" placeholder="Ví dụ: 123 Đường ABC" required style="padding: 10px 15px;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Tên gợi nhớ</label>
                    <input type="text" id="addrName" class="form-control" placeholder="Nguyễn Văn A" style="padding: 10px 15px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 10px;">Loại địa chỉ</label>
                    <div style="display: flex; gap: 20px;">
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 13px; cursor: pointer;">
                            <input type="radio" name="addrType" value="Nhà" checked> Nhà
                        </label>
                        <label style="display: flex; align-items: center; gap: 5px; font-size: 13px; cursor: pointer;">
                            <input type="radio" name="addrType" value="Văn phòng"> Văn phòng
                        </label>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #333; cursor: pointer;">
                        <input type="checkbox" id="addrIsDefault" style="width: 16px; height: 16px;">
                        Đặt làm địa chỉ mặc định
                    </label>
                </div>
            </form>
        </div>
        <div class="student-modal-footer">
            <button type="button" class="btn-outline" style="margin-top: 0; border-color: #0046ab; color: #0046ab;" onclick="closeAddressModal()">Đóng</button>
            <button type="button" id="addressSubmitBtn" class="btn-update" style="margin-top: 0; padding: 8px 20px; background: #0046ab;" onclick="submitAddress()">Lưu địa chỉ</button>
        </div>
    </div>
</div>

</div>

<!-- Container thông báo Toast -->
<div id="toast-container"></div>

<!-- Modal xác nhận xóa custom -->
<div id="confirmModal" class="confirm-modal-overlay">
    <div class="confirm-modal">
        <div class="confirm-icon">
            <i class="fa-solid fa-trash-can"></i>
        </div>
        <h4 id="confirmTitle">Xác nhận xóa</h4>
        <p id="confirmMessage">Bạn có chắc chắn muốn xóa địa chỉ này không? Thao tác này không thể hoàn tác.</p>
        <div class="confirm-actions">
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Hủy bỏ</button>
            <button type="button" id="btnDoConfirm" class="btn-confirm-delete">Xác nhận xóa</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function switchTab(tabId, element) {
        document.querySelectorAll('.profile-nav-item').forEach(item => {
            item.classList.remove('active');
        });
        
        if(element) element.classList.add('active');

        document.querySelectorAll('.profile-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        document.getElementById(tabId).classList.add('active');
    }

    /* ===== Toast Notification System ===== */
    function showToast(title, message, type = 'success') {
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
        
        // Trình duyệt cần một chút thời gian để render trước khi thêm class active
        setTimeout(() => toast.classList.add('active'), 10);
        
        // Tự động xóa sau 4 giây
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
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.remove('active');
        confirmCallback = null;
    }

    document.getElementById('btnDoConfirm').addEventListener('click', function() {
        if (confirmCallback) {
            this.disabled = true;
            this.innerHTML = '<span class="spinner"></span> Đang xóa...';
            confirmCallback();
        }
    });

    function setBtnLoading(btnId, isLoading, text = 'Đang lưu...') {
        const btn = document.getElementById(btnId);
        if (isLoading) {
            btn.disabled = true;
            btn.dataset.oldText = btn.innerText;
            btn.innerHTML = `<span class="spinner"></span> ${text}`;
        } else {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.oldText || 'Lưu';
        }
    }


    function openStudentModal() {
        document.getElementById('studentModal').classList.add('active');
    }

    function closeStudentModal() {
        document.getElementById('studentModal').classList.remove('active');
        // Reset form
        document.getElementById('studentCardUpload').value = '';
        document.getElementById('uploadPreview').style.display = 'none';
        document.getElementById('uploadPreview').src = '';
        document.getElementById('uploadPlaceholder').style.display = 'block';
    }

    function previewStudentCard(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('uploadPlaceholder').style.display = 'none';
                document.getElementById('uploadPreview').src = e.target.result;
                document.getElementById('uploadPreview').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearValidationErrors(formId) {
        const form = document.getElementById(formId);
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function highlightInvalidField(id, isUploadBox = false) {
        const el = isUploadBox ? document.getElementById(id).parentElement : document.getElementById(id);
        el.classList.add('is-invalid');
    }

    function submitStudentRegistration() {
        clearValidationErrors('studentRegistrationForm');
        const sName = document.getElementById('studentFullName');
        const school = document.getElementById('schoolName');
        const sId = document.getElementById('studentId');
        const file = document.getElementById('studentCardUpload');
        
        let hasError = false;

        if(!sName.value) { highlightInvalidField('studentFullName'); hasError = true; }
        if(!school.value) { highlightInvalidField('schoolName'); hasError = true; }
        if(!sId.value) { highlightInvalidField('studentId'); hasError = true; }
        if(!file.files[0]) { highlightInvalidField('studentCardUpload', true); hasError = true; }

        if(hasError) {
            showToast('Lỗi nhập liệu', 'Vui lòng hoàn thiện các trường còn thiếu!', 'warning');
            return;
        }
        
        setBtnLoading('studentSubmitBtn', true, 'Đang gửi...');
        
        setTimeout(() => {
            showToast('Thành công', 'Gửi yêu cầu đăng ký thành công! Chúng tôi sẽ duyệt hồ sơ trong 24h.');
            closeStudentModal();
            document.getElementById('studentRegistrationForm').reset();
            setBtnLoading('studentSubmitBtn', false);
        }, 1500);
    }

    // --- Business Modal ---
    function openBusinessModal() {
        document.getElementById('businessModal').classList.add('active');
    }

    function closeBusinessModal() {
        document.getElementById('businessModal').classList.remove('active');
        document.getElementById('businessRegistrationForm').reset();
        
        ['businessLicenseUpload', 'authorizationUpload'].forEach(id => document.getElementById(id).value = '');
        
        document.getElementById('licensePreview').style.display = 'none';
        document.getElementById('licensePlaceholder').style.display = 'block';
        
        document.getElementById('authPreview').style.display = 'none';
        document.getElementById('authPlaceholder').style.display = 'block';
    }

    function previewFile(input, placeholderId, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(placeholderId).style.display = 'none';
                document.getElementById(previewId).src = e.target.result;
                document.getElementById(previewId).style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function submitBusinessRegistration() {
        clearValidationErrors('businessRegistrationForm');
        const cName = document.getElementById('companyName');
        const tId = document.getElementById('taxId');
        const file1 = document.getElementById('businessLicenseUpload');
        const file2 = document.getElementById('authorizationUpload');
        
        let hasError = false;

        if(!cName.value) { highlightInvalidField('companyName'); hasError = true; }
        if(!tId.value) { highlightInvalidField('taxId'); hasError = true; }
        if(!file1.files[0]) { highlightInvalidField('businessLicenseUpload', true); hasError = true; }
        if(!file2.files[0]) { highlightInvalidField('authorizationUpload', true); hasError = true; }

        if(hasError) {
            showToast('Lỗi nhập liệu', 'Vui lòng cung cấp đầy đủ thông tin và hồ sơ doanh nghiệp!', 'warning');
            return;
        }
        
        setBtnLoading('businessSubmitBtn', true, 'Đang gửi...');

        setTimeout(() => {
            showToast('Thành công', 'Gửi yêu cầu đăng ký Doanh nghiệp thành công! Chúng tôi sẽ liên hệ sớm nhất.');
            closeBusinessModal();
            setBtnLoading('businessSubmitBtn', false);
        }, 2000);
    }

    // --- Address Modal ---
    function openAddressModal() {
        document.getElementById('addressModal').classList.add('active');
        if (document.getElementById('addrCity').options.length <= 1) {
            fetchProvincesData();
        }
    }

    function closeAddressModal() {
        document.getElementById('addressModal').classList.remove('active');
        document.getElementById('addAddressForm').reset();
        document.getElementById('addrId').value = '';
        document.getElementById('addressModalTitle').innerText = 'Thêm địa chỉ mới';
        document.getElementById('addressSubmitBtn').innerText = 'Lưu địa chỉ';
        document.getElementById('addrDistrict').innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        document.getElementById('addrDistrict').disabled = true;
        document.getElementById('addrWard').innerHTML = '<option value="">Chọn Phường/Xã</option>';
        document.getElementById('addrWard').disabled = true;
    }

    // Fetch API Data (Stable Version)
    function fetchProvincesData() {
        fetch('https://esgoo.net/api-tinhthanh/1/0.htm')
            .then(res => res.json())
            .then(data => {
                if(data.error === 0) {
                    populateCities(data.data);
                }
            })
            .catch(err => console.error('Error fetching provinces:', err));
    }

    function populateCities(data) {
        const citySelect = document.getElementById('addrCity');
        citySelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';
        data.forEach(city => {
            let option = document.createElement('option');
            option.value = city.full_name;
            option.dataset.code = city.id;
            option.textContent = city.full_name;
            citySelect.appendChild(option);
        });
    }

    document.getElementById('addrCity').addEventListener('change', function() {
        const cityCode = this.options[this.selectedIndex].dataset.code;
        const districtSelect = document.getElementById('addrDistrict');
        const wardSelect = document.getElementById('addrWard');
        
        districtSelect.innerHTML = '<option value="">Đang tải Quận/Huyện...</option>';
        districtSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        wardSelect.disabled = true;

        if (cityCode) {
            fetch('https://esgoo.net/api-tinhthanh/2/' + cityCode + '.htm')
                .then(res => res.json())
                .then(data => {
                    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                    if(data.error === 0 && data.data) {
                        data.data.forEach(dist => {
                            let option = document.createElement('option');
                            option.value = dist.full_name;
                            option.dataset.code = dist.id;
                            option.textContent = dist.full_name;
                            districtSelect.appendChild(option);
                        });
                        districtSelect.disabled = false;
                    }
                })
                .catch(err => {
                    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
                });
        } else {
            districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        }
    });

    document.getElementById('addrDistrict').addEventListener('change', function() {
        const distCode = this.options[this.selectedIndex].dataset.code;
        const wardSelect = document.getElementById('addrWard');
        
        wardSelect.innerHTML = '<option value="">Đang tải Phường/Xã...</option>';
        wardSelect.disabled = true;

        if (distCode) {
            fetch('https://esgoo.net/api-tinhthanh/3/' + distCode + '.htm')
                .then(res => res.json())
                .then(data => {
                    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                    if(data.error === 0 && data.data) {
                        data.data.forEach(ward => {
                            let option = document.createElement('option');
                            option.value = ward.full_name;
                            option.textContent = ward.full_name;
                            wardSelect.appendChild(option);
                        });
                        wardSelect.disabled = false;
                    }
                })
                .catch(err => {
                    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                });
        } else {
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        }
    });

    function submitAddress() {
        clearValidationErrors('addAddressForm');
        const id = document.getElementById('addrId').value;
        const city = document.getElementById('addrCity');
        const district = document.getElementById('addrDistrict');
        const ward = document.getElementById('addrWard');
        const street = document.getElementById('addrStreet');
        const name = document.getElementById('addrName').value;
        const type = document.querySelector('input[name="addrType"]:checked').value;
        const isDefault = document.getElementById('addrIsDefault').checked;
        
        let hasError = false;
        if(!city.value) { highlightInvalidField('addrCity'); hasError = true; }
        if(!district.value) { highlightInvalidField('addrDistrict'); hasError = true; }
        if(!ward.value) { highlightInvalidField('addrWard'); hasError = true; }
        if(!street.value) { highlightInvalidField('addrStreet'); hasError = true; }

        if(hasError) {
            showToast('Lỗi nhập liệu', 'Vui lòng điền đầy đủ các thông tin bắt buộc!', 'warning');
            return;
        }

        setBtnLoading('addressSubmitBtn', true);
        const url = id ? `/profile/address/${id}` : '{{ route('profile.address.store') }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                city: city.value,
                district: district.value,
                ward: ward.value,
                street: street.value,
                name: name,
                type: type,
                is_default: isDefault
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                const action = id ? 'Cập nhật' : 'Thêm mới';
                sessionStorage.setItem('profile_toast', JSON.stringify({
                    title: 'Thành công',
                    msg: `${action} địa chỉ thành công!`,
                    type: 'success'
                }));
                window.location.href = '?tab=info-tab';
            } else {
                setBtnLoading('addressSubmitBtn', false);
                showToast('Lỗi hệ thống', data.error || 'Không thể lưu địa chỉ lúc này.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            setBtnLoading('addressSubmitBtn', false);
            showToast('Lỗi kết nối', 'Vui lòng kiểm tra lại đường truyền mạng.', 'error');
        });
    }

    function editAddress(id, city, district, ward, street, name, type, isDefault) {
        // ... (existing editAddress setup)
        document.getElementById('addressModalTitle').innerText = 'Cập nhật địa chỉ';
        document.getElementById('addressSubmitBtn').innerText = 'Cập nhật';
        document.getElementById('addrId').value = id;
        document.getElementById('addrStreet').value = street;
        document.getElementById('addrName').value = name;
        document.getElementById('addrIsDefault').checked = isDefault;
        document.querySelector(`input[name="addrType"][value="${type}"]`).checked = true;

        openAddressModal();

        // ... (rest of dropdown population logic)
        setTimeout(() => {
            const citySelect = document.getElementById('addrCity');
            for(let i=0; i<citySelect.options.length; i++) {
                if(citySelect.options[i].value === city) { citySelect.selectedIndex = i; break; }
            }
            citySelect.dispatchEvent(new Event('change'));
            setTimeout(() => {
                const distSelect = document.getElementById('addrDistrict');
                for(let i=0; i<distSelect.options.length; i++) {
                    if(distSelect.options[i].value === district) { distSelect.selectedIndex = i; break; }
                }
                distSelect.dispatchEvent(new Event('change'));
                setTimeout(() => {
                    const wardSelect = document.getElementById('addrWard');
                    for(let i=0; i<wardSelect.options.length; i++) {
                        if(wardSelect.options[i].value === ward) { wardSelect.selectedIndex = i; break; }
                    }
                }, 500);
            }, 500);
        }, 500);
    }

    function copyVoucher(code, btn) {
        navigator.clipboard.writeText(code).then(() => {
            const originalText = btn.innerText;
            btn.innerText = 'Đã chép!';
            btn.style.background = '#0046ab';
            btn.style.color = '#fff';
            
            showToast('Thành công', `Đã sao chép mã: ${code}`, 'success');
            
            setTimeout(() => {
                btn.innerText = originalText;
                btn.style.background = 'none';
                btn.style.color = '#0046ab';
            }, 2000);
        });
    }

    function addToCart(productId) {
        fetch('{{ route('cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: 1
            })
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                showToast('Thành công', 'Đã thêm sản phẩm vào giỏ hàng!', 'success');
                // Cập nhật số lượng giỏ hàng trên header nếu có
                const badge = document.getElementById('headerCartBadge');
                if(badge) {
                    badge.innerText = data.cart_count;
                    badge.style.display = 'block';
                }
            } else {
                showToast('Lỗi', 'Không thể thêm vào giỏ hàng.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
        });
    }

    function removeFromWishlist(id) {
        showConfirm('Xóa khỏi yêu thích', 'Bạn muốn bỏ sản phẩm này khỏi danh sách yêu thích?', function() {
            fetch(`/wishlist/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast('Đã xóa', 'Đã bỏ sản phẩm khỏi danh sách yêu thích.', 'success');
                    const item = document.getElementById(`wishlist-item-${id}`);
                    if(item) {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            item.remove();
                            // Reload if empty
                            const grid = document.querySelector('.wishlist-grid');
                            if(grid && grid.querySelectorAll('.wishlist-item').length === 0) {
                                window.location.reload();
                            }
                        }, 300);
                    }
                    closeConfirmModal();
                } else {
                    closeConfirmModal();
                    showToast('Lỗi', data.error || 'Không thể thực hiện thao tác này.', 'error');
                }
            })
            .catch(err => {
                closeConfirmModal();
                showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
            });
        });
    }

    function clearWishlist() {
        showConfirm('Xóa tất cả', 'Bạn có chắc chắn muốn xóa toàn bộ danh sách yêu thích?', function() {
            fetch('{{ route('wishlist.clear') }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast('Thành công', 'Đã xóa toàn bộ danh sách yêu thích.', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    closeConfirmModal();
                    showToast('Lỗi', data.error || 'Không thể xóa danh sách lúc này.', 'error');
                }
            })
            .catch(err => {
                closeConfirmModal();
                showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
            });
        });
    }


    function deleteAddress(id) {
        showConfirm('Xóa địa chỉ', 'Bạn có chắc chắn muốn xóa địa chỉ này? Thao tác này không thể hoàn tác.', function() {
            fetch(`/profile/address/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    sessionStorage.setItem('profile_toast', JSON.stringify({
                        title: 'Đã xóa',
                        msg: 'Địa chỉ đã được gỡ khỏi sổ địa chỉ của bạn.',
                        type: 'success'
                    }));
                    window.location.href = '?tab=info-tab';
                } else {
                    closeConfirmModal();
                    showToast('Lỗi', 'Không thể xóa địa chỉ này.', 'error');
                }
            })
            .catch(err => {
                closeConfirmModal();
                showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
            });
        });
    }

    // Initialize Active Tab & Notifications
    document.addEventListener('DOMContentLoaded', function() {
        // Kiểm tra toast từ sessionStorage (được set trước khi reload trang)
        const toastData = sessionStorage.getItem('profile_toast');
        if (toastData) {
            const data = JSON.parse(toastData);
            showToast(data.title, data.msg, data.type);
            sessionStorage.removeItem('profile_toast');
        }

        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab) {
            let index = 0;
            switch(tab) {
                case 'orders-tab': index = 1; break;
                case 'info-tab': index = 2; break;
                case 'wishlist-tab': index = 3; break;
                case 'promo-tab': index = 4; break;
                case 'login-history-tab': index = 5; break;
            }
            switchTab(tab, document.querySelectorAll('.profile-nav-item')[index]);
        }
    });
</script>
@endpush
