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
    
    /* Layout 2 cột cho Form Đăng ký sửa chữa trực tuyến */
    .grid-2-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .grid-2-cols {
            grid-template-columns: 1fr;
        }
    }
    .form-section-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px;
    }
    .form-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 8px;
    }
    .form-section-title i {
        color: #0046ab;
    }

    /* Stepper Tiến độ cho Modal Theo dõi sửa chữa */
    .stepper {
        display: flex;
        flex-direction: column;
        position: relative;
        padding-left: 30px;
        margin-top: 20px;
    }
    .stepper::before {
        content: '';
        position: absolute;
        left: 9px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background: #e2e8f0;
        z-index: 1;
    }
    .step-item {
        position: relative;
        padding-bottom: 25px;
        z-index: 2;
    }
    .step-item.completed:last-child {
        padding-bottom: 0;
    }
    .step-item:last-child {
        padding-bottom: 0;
    }
    .step-icon {
        position: absolute;
        left: -30px;
        top: 2px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: #cbd5e1;
        transition: 0.3s;
    }
    .step-item.active .step-icon {
        border-color: #0046ab;
        background: #eef2ff;
        color: #0046ab;
        box-shadow: 0 0 0 3px rgba(0, 70, 171, 0.15);
    }
    .step-item.completed .step-icon {
        border-color: #166534;
        background: #dcfce7;
        color: #166534;
    }
    .step-content {
        padding-left: 10px;
    }
    .step-title {
        font-size: 14px;
        font-weight: 700;
        color: #475569;
        margin: 0 0 4px;
        transition: 0.3s;
    }
    .step-item.active .step-title {
        color: #0046ab;
    }
    .step-item.completed .step-title {
        color: #166534;
    }
    .step-desc {
        font-size: 12px;
        color: #64748b;
        line-height: 1.5;
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
    .profile-rewards-link {
        display: block;
        margin: 0 20px 12px;
        padding: 12px 14px;
        border-radius: 14px;
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #fff;
        text-decoration: none;
        font-weight: 700;
        box-shadow: 0 12px 30px rgba(79,70,229,.18);
    }
    .profile-rewards-link small { display:block; opacity:.85; font-weight:500; }
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
    .order-details-row {
        background: #f8fafc;
        border-top: none;
    }
    .btn-claim-action {
        transition: all 0.2s ease-in-out;
    }
    .btn-claim-action:hover {
        transform: translateY(-1px);
        filter: brightness(0.95);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
    }
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
        transition: transform 0.3s, box-shadow 0.3s, opacity 0.3s;
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

                    <div class="stat-row" style="margin-top: 10px; border-top: 1px dashed #eee; padding-top: 10px; margin-bottom: 15px;">
                        <div class="stat-item">
                            <span>Điểm tiêu dùng hiện có</span>
                            <strong><i class="fa-solid fa-coins" style="color: #eab308;"></i> {{ number_format($walletPoints) }}</strong>
                        </div>
                        <div class="stat-item" style="text-align: right; align-items: flex-end;">
                            <span>Điểm tích lũy hạng</span>
                            <strong style="color: #8b5cf6;"><i class="fa-solid fa-star" style="color: #8b5cf6;"></i> {{ number_format($rankPoints) }}</strong>
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
                <!-- Menu Đặt lịch & Lịch sử sửa chữa trực tuyến của khách hàng -->
                <div class="profile-nav-item" onclick="switchTab('repair-tab', this)">
                    <i class="fa-solid fa-screwdriver-wrench"></i> Lịch sử & Đặt lịch sửa chữa
                </div>

                <div class="nav-divider"></div>
                <a href="{{ route('rewards.index') }}" class="profile-rewards-link">
                    Trang đổi thưởng
                    <small>Mở catalog voucher, quà tặng và vòng quay may mắn</small>
                </a>
                <div class="profile-nav-item" onclick="switchTab('promo-tab', this)">
                    <i class="fa-solid fa-ticket"></i> Hạng thành viên & Ưu đãi
                </div>
                <div class="profile-nav-item" onclick="switchTab('login-history-tab', this)">
                    <i class="fa-solid fa-shield-halved"></i> Lịch sử đăng nhập
                </div>
                <div class="profile-nav-item">
                    <a href="{{ route('rewards.history') }}" class="w-full flex items-center gap-4 text-inherit" style="text-decoration: none; color: inherit;">
                        <i class="fa-solid fa-gift"></i> Lịch sử đổi thưởng
                    </a>
                </div>
                <a href="{{ route('warranty.index') }}" class="profile-nav-item" style="text-decoration:none;">
                    <i class="fa-solid fa-magnifying-glass"></i> Tra cứu bảo hành
                </a>
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
                                        <th>Hành Động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                    <tr>
                                        <td><strong>#{{ $order->order_code ?? $order->order_id }}</strong></td>
                                        <td>{{ $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') : 'Không xác định' }}</td>
                                        <td style="color: #e21033; font-weight: bold;">{{ number_format($order->final_amount ?? 0, 0, ',', '.') }}đ</td>
                                        <td>
                                            @if($order->status == 'Pending')
                                                @if(strtoupper($order->payment_method ?? '') != 'COD' && strtoupper($order->payment_method ?? '') != 'CASH_POS')
                                                    <span class="status-badge" style="background:#fef3c7; color:#d97706;">Chờ thanh toán</span>
                                                @else
                                                    <span class="status-badge status-pending">Đang xử lý</span>
                                                @endif
                                            @elseif($order->status == 'BaoCK')
                                                <span class="status-badge" style="background:#e0e7ff; color:#4338ca;">Chờ duyệt tiền</span>
                                            @elseif($order->status == 'Delivered')
                                                <span class="status-badge status-completed">Thành công</span>
                                            @elseif($order->status == 'Shipping')
                                                <span class="status-badge" style="background:#bae6fd; color:#0369a1;">Đang giao</span>
                                            @else
                                                <span class="status-badge status-cancelled">Đã hủy</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn-expand" onclick="toggleOrderDetails({{ $order->order_id }})" style="background: none; border: none; color: #0046ab; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                                <i class="fa-solid fa-chevron-down" id="icon-{{ $order->order_id }}"></i> Chi tiết
                                            </button>
                                        </td>
                                    </tr>
                                    <tr id="details-{{ $order->order_id }}" class="order-details-row" style="display: none; background: #f8fafc;">
                                        <td colspan="5" style="padding: 0;">
                                            <div style="padding: 20px 24px;">

                                                {{-- === HEADER: Mã đơn + Trạng thái === --}}
                                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 8px;">
                                                    <div>
                                                        <span style="font-size: 15px; font-weight: 800; color: #0f172a;">Đơn hàng #{{ $order->order_code ?? $order->order_id }}</span>
                                                        <span style="font-size: 11px; color: #94a3b8; margin-left: 8px;">
                                                            @if(strtolower($order->order_type ?? '') == 'counter')
                                                                Mua tại quầy
                                                            @else
                                                                Đặt hàng Online
                                                            @endif
                                                        </span>
                                                    </div>
                                                    @if($order->status == 'Delivered')
                                                        <span style="background: #dcfce7; color: #15803d; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;"><i class="fa-solid fa-circle-check"></i> Giao hàng thành công</span>
                                                    @elseif($order->status == 'Shipping')
                                                        <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;"><i class="fa-solid fa-truck"></i> Đang giao hàng</span>
                                                    @elseif($order->status == 'Pending')
                                                        @if(strtoupper($order->payment_method ?? '') != 'COD' && strtoupper($order->payment_method ?? '') != 'CASH_POS')
                                                            <span style="background: #fef3c7; color: #b45309; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;"><i class="fa-solid fa-clock"></i> Chờ thanh toán</span>
                                                        @else
                                                            <span style="background: #fef3c7; color: #b45309; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;"><i class="fa-solid fa-clock"></i> Đang xử lý</span>
                                                        @endif
                                                    @elseif($order->status == 'BaoCK')
                                                        @if(strtoupper($order->payment_method ?? '') != 'COD' && strtoupper($order->payment_method ?? '') != 'CASH_POS')
                                                            <span style="background: #e0e7ff; color: #4338ca; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;"><i class="fa-solid fa-hourglass-half"></i> Chờ duyệt thanh toán</span>
                                                        @else
                                                            <span style="background: #e0e7ff; color: #4338ca; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;"><i class="fa-solid fa-check-double"></i> Đã xác nhận</span>
                                                        @endif
                                                    @else
                                                        <span style="background: #fee2e2; color: #dc2626; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700;"><i class="fa-solid fa-ban"></i> Đã hủy</span>
                                                    @endif
                                                </div>

                                                {{-- === TIMELINE TRẠNG THÁI === --}}
                                                @php
                                                    $isCash = in_array(strtoupper($order->payment_method ?? ''), ['COD', 'CASH_POS']);
                                                    
                                                    // Step 1: Đặt hàng
                                                    $step1State = 'done';
                                                    
                                                    // Step 2: Xác nhận / Thanh toán
                                                    if (in_array($order->status, ['Shipping', 'Delivered']) || ($order->payment_status ?? '') == 'paid') {
                                                        $step2State = 'done';
                                                    } elseif (!$isCash && $order->status == 'BaoCK') {
                                                        $step2State = 'pending';
                                                    } elseif ($isCash && $order->status == 'Pending') {
                                                        $step2State = 'pending';
                                                    } else {
                                                        $step2State = 'todo';
                                                    }
                                                    
                                                    // Step 3: Đang giao
                                                    if ($order->status == 'Delivered') {
                                                        $step3State = 'done';
                                                    } elseif ($order->status == 'Shipping') {
                                                        $step3State = 'pending';
                                                    } else {
                                                        $step3State = 'todo';
                                                    }
                                                    
                                                    // Step 4: Hoàn thành
                                                    $step4State = ($order->status == 'Delivered') ? 'done' : 'todo';
                                                    
                                                    $steps = [
                                                        [
                                                            'label' => 'Đặt hàng',
                                                            'icon' => 'fa-cart-shopping',
                                                            'state' => $step1State,
                                                            'date' => $order->created_at
                                                        ],
                                                        [
                                                            'label' => $isCash ? 'Xác nhận' : 'Thanh toán',
                                                            'icon' => $isCash ? 'fa-clipboard-check' : 'fa-credit-card',
                                                            'state' => $step2State,
                                                            'date' => null
                                                        ],
                                                        [
                                                            'label' => 'Đang giao',
                                                            'icon' => 'fa-truck-fast',
                                                            'state' => $step3State,
                                                            'date' => null
                                                        ],
                                                        [
                                                            'label' => 'Hoàn thành',
                                                            'icon' => 'fa-circle-check',
                                                            'state' => $step4State,
                                                            'date' => $order->delivered_at
                                                        ],
                                                    ];
                                                    $isCancelled = !in_array($order->status, ['Pending','BaoCK','Shipping','Delivered']);
                                                @endphp
                                                @if(!$isCancelled)
                                                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 22px; padding: 16px 12px; background: #fff; border-radius: 12px; border: 1px solid #e2e8f0;">
                                                    @foreach($steps as $i => $step)
                                                        @php
                                                            if ($step['state'] == 'done') {
                                                                $bgColor = '#0046ab';
                                                                $color = '#fff';
                                                                $iconClass = $step['icon'];
                                                            } elseif ($step['state'] == 'pending') {
                                                                $bgColor = '#fef3c7';
                                                                $color = '#d97706';
                                                                $iconClass = 'fa-circle-notch fa-spin';
                                                            } else {
                                                                $bgColor = '#e2e8f0';
                                                                $color = '#94a3b8';
                                                                $iconClass = $step['icon'];
                                                            }
                                                        @endphp
                                                        <div style="display: flex; flex-direction: column; align-items: center; flex: 1; position: relative;">
                                                            <div style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; background: {{ $bgColor }}; color: {{ $color }};">
                                                                <i class="fa-solid {{ $iconClass }}"></i>
                                                            </div>
                                                            <span style="font-size: 10px; font-weight: 700; margin-top: 6px; color: {{ $step['state'] != 'todo' ? '#0f172a' : '#94a3b8' }}; text-align: center;">{{ $step['label'] }}</span>
                                                            @if($step['date'])
                                                                <span style="font-size: 9px; color: #94a3b8; margin-top: 2px;">{{ \Carbon\Carbon::parse($step['date'])->format('d/m/Y') }}</span>
                                                            @endif
                                                        </div>
                                                        @if($i < count($steps) - 1)
                                                            @php
                                                                $nextStep = $steps[$i+1];
                                                                $lineBg = ($nextStep['state'] != 'todo') ? '#0046ab' : '#e2e8f0';
                                                            @endphp
                                                            <div style="flex: 1; height: 3px; margin-top: 15px; border-radius: 2px; background: {{ $lineBg }};"></div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                                @endif

                                                {{-- === 2 CỘT: Thông tin nhận hàng + Thanh toán === --}}
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                                                    {{-- Thông tin nhận hàng --}}
                                                    <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 14px 16px;">
                                                        <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                                            <i class="fa-solid fa-location-dot" style="color: #0046ab;"></i> Thông tin nhận hàng
                                                        </div>
                                                        <div style="font-size: 12px; color: #334155; line-height: 1.8;">
                                                            <div><strong>Người nhận:</strong> {{ $order->customer_name ?? 'N/A' }}</div>
                                                            <div><strong>Số điện thoại:</strong> {{ $order->customer_phone ?? 'N/A' }}</div>
                                                            <div><strong>Địa chỉ:</strong> {{ $order->shipping_address ?? 'N/A' }}</div>
                                                            @if($order->note)
                                                                <div style="margin-top: 4px; padding: 6px 10px; background: #fefce8; border-radius: 6px; font-size: 11px; color: #854d0e;">
                                                                    <i class="fa-solid fa-note-sticky"></i> <strong>Ghi chú:</strong> {{ $order->note }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    {{-- Thông tin thanh toán --}}
                                                    <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 14px 16px;">
                                                        <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                                            <i class="fa-solid fa-credit-card" style="color: #0046ab;"></i> Thanh toán & Vận chuyển
                                                        </div>
                                                        <div style="font-size: 12px; color: #334155; line-height: 1.8;">
                                                            <div><strong>Phương thức:</strong>
                                                                @if(strtoupper($order->payment_method ?? '') == 'COD')
                                                                    <span style="background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700;">COD</span> Thanh toán khi nhận hàng
                                                                @else
                                                                    <span style="background:#dbeafe; color:#1e40af; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700;">{{ strtoupper($order->payment_method ?? 'N/A') }}</span>
                                                                @endif
                                                            </div>
                                                            <div><strong>Trạng thái TT:</strong>
                                                                @if(($order->payment_status ?? '') == 'paid')
                                                                    <span style="color: #15803d; font-weight: 700;">✓ Đã thanh toán</span>
                                                                @elseif($order->status == 'BaoCK')
                                                                    <span style="color: #4338ca; font-weight: 700;">Đã báo chuyển khoản (Chờ duyệt)</span>
                                                                @elseif(in_array(strtoupper($order->payment_method ?? ''), ['COD', 'CASH_POS']))
                                                                    <span style="color: #475569; font-weight: 700;">Thanh toán khi nhận hàng (COD)</span>
                                                                @else
                                                                    <span style="color: #b45309; font-weight: 700;">Chờ thanh toán</span>
                                                                    @if($order->status != 'Cancelled')
                                                                        <div style="margin-top: 6px;">
                                                                            <a href="{{ route('cart.qr', ['order_id' => $order->order_id]) }}" style="display: inline-flex; align-items: center; gap: 6px; background: #0046ab; color: #fff; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 10px; font-weight: 700; transition: background 0.2s;" onmouseover="this.style.background='#003399'" onmouseout="this.style.background='#0046ab'">
                                                                                <i class="fa-solid fa-qrcode"></i> Thanh toán ngay
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                            @if($order->shipping_partner)
                                                                <div><strong>Đơn vị vận chuyển:</strong> {{ $order->shipping_partner }}</div>
                                                            @endif
                                                            @if($order->tracking_code)
                                                                <div><strong>Mã vận đơn:</strong> <span style="font-family: monospace; color: #0046ab; font-weight: 700;">{{ $order->tracking_code }}</span></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- === DANH SÁCH SẢN PHẨM === --}}
                                                <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 14px 16px; margin-bottom: 16px;">
                                                    <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                                                        <i class="fa-solid fa-box" style="color: #0046ab;"></i> Sản phẩm trong đơn hàng ({{ $order->details->count() }} sản phẩm)
                                                    </div>
                                                    <div style="display: flex; flex-direction: column; gap: 12px;">
                                                        @foreach($order->details as $detail)
                                                            @php
                                                                $variant = $detail->inventoryItem->variant ?? null;
                                                                $product = $variant->product ?? null;
                                                                $image = null;
                                                                if ($product) {
                                                                    $thumb = $product->thumbnail;
                                                                    if ($thumb && \Illuminate\Support\Str::startsWith($thumb, 'http')) {
                                                                        $image = $thumb;
                                                                    } else {
                                                                        $rawImages = $product->images;
                                                                        if ($rawImages) {
                                                                            $arr = is_string($rawImages) ? json_decode($rawImages, true) : $rawImages;
                                                                            $first = is_array($arr) && count($arr) > 0 ? $arr[0] : null;
                                                                            if ($first && \Illuminate\Support\Str::startsWith($first, 'http')) { $image = $first; }
                                                                            elseif ($first) { $image = asset('storage/' . ltrim($first, '/')); }
                                                                        }
                                                                        if (!$image) { $image = $thumb ? asset('uploads/products/' . $thumb) : null; }
                                                                    }
                                                                }
                                                                $productName = $detail->product_name ?? ($product->name ?? 'Sản phẩm không xác định');
                                                                if ($variant && $variant->label) { $productName .= ' - ' . $variant->label; }
                                                                $item = $detail->inventoryItem;
                                                                $canClaimWarranty = $item ? $item->canClaimWarranty($order) : false;
                                                                $canClaimReturn = $item ? $item->canClaimReturn($order) : false;
                                                            @endphp
                                                            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; gap: 12px; flex-wrap: wrap;">
                                                                <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 250px;">
                                                                    <img src="{{ $image ?? 'https://via.placeholder.com/56x56?text=SP' }}" style="width: 56px; height: 56px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0;" onerror="this.src='/images/no-image.png'">
                                                                    <div>
                                                                        <div style="font-weight: 700; color: #0f172a; font-size: 13px;">{{ $productName }}</div>
                                                                        @if($item && $item->imei_serial)
                                                                            <div style="font-size: 10px; color: #64748b; font-family: monospace; margin-top: 3px; background: #f1f5f9; display: inline-block; padding: 2px 8px; border-radius: 4px;">
                                                                                IMEI: <strong style="color: #0f172a;">{{ $item->imei_serial }}</strong>
                                                                            </div>
                                                                        @endif
                                                                        <div style="font-size: 12px; color: #64748b; margin-top: 3px;">SL: 1 × <strong style="color: #e21033;">{{ number_format($detail->price ?? 0, 0, ',', '.') }}đ</strong></div>
                                                                    </div>
                                                                </div>
                                                                @if($order->status == 'Delivered' && $item && $item->imei_serial)
                                                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                                        @if($canClaimWarranty)
                                                                            <button type="button" onclick="triggerProfileClaimModal('{{ $item->imei_serial }}', '{{ addslashes($productName) }}', 'warranty')" style="background: #0046ab; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 10px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                                                                <i class="fa-solid fa-shield-halved"></i> Bảo hành
                                                                            </button>
                                                                        @endif
                                                                        @if($canClaimReturn)
                                                                            <button type="button" onclick="triggerProfileClaimModal('{{ $item->imei_serial }}', '{{ addslashes($productName) }}', 'return')" style="background: #f59e0b; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 10px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                                                                <i class="fa-solid fa-rotate-left"></i> Đổi trả
                                                                            </button>
                                                                        @endif
                                                                        @if(!$canClaimWarranty)
                                                                            <button type="button" onclick="triggerProfileRepairModal('{{ $item->imei_serial }}', '{{ addslashes($productName) }}')" style="background: #64748b; color: #fff; border: none; padding: 6px 12px; border-radius: 6px; font-size: 10px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                                                                <i class="fa-solid fa-screwdriver-wrench"></i> Sửa chữa
                                                                            </button>
                                                                        @endif
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                {{-- === BẢNG TỔNG TIỀN === --}}
                                                <div style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 14px 16px;">
                                                    <div style="font-size: 12px; font-weight: 800; color: #0f172a; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                                        <i class="fa-solid fa-receipt" style="color: #0046ab;"></i> Chi tiết thanh toán
                                                    </div>
                                                    <div style="font-size: 12px; color: #475569; line-height: 2;">
                                                        <div style="display: flex; justify-content: space-between;"><span>Tạm tính:</span><span>{{ number_format($order->total_amount ?? 0, 0, ',', '.') }}đ</span></div>
                                                        <div style="display: flex; justify-content: space-between;"><span>Phí vận chuyển:</span><span>{{ ($order->shipping_fee ?? 0) > 0 ? number_format($order->shipping_fee, 0, ',', '.') . 'đ' : 'Miễn phí' }}</span></div>
                                                        @if(($order->discount_amount ?? 0) > 0)
                                                            <div style="display: flex; justify-content: space-between; color: #15803d;"><span>Giảm giá:</span><span>-{{ number_format($order->discount_amount, 0, ',', '.') }}đ</span></div>
                                                        @endif
                                                        @if(($order->wallet_points_used ?? 0) > 0)
                                                            <div style="display: flex; justify-content: space-between; color: #b45309;"><span>Điểm đã dùng:</span><span>-{{ number_format($order->wallet_points_used, 0, ',', '.') }}đ</span></div>
                                                        @endif
                                                        <div style="display: flex; justify-content: space-between; border-top: 2px solid #0046ab; padding-top: 8px; margin-top: 6px; font-size: 14px; font-weight: 800; color: #e21033;">
                                                            <span>Tổng thanh toán:</span>
                                                            <span>{{ number_format($order->final_amount ?? 0, 0, ',', '.') }}đ</span>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
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
                    @php
                        $hasProfileError = $errors->has('full_name') || $errors->has('phone_number') || $errors->has('gender') || $errors->has('dob') || $errors->has('address') || $errors->has('no_change');
                    @endphp
                    <div class="acc-card" style="grid-column: 1 / -1;">
                        <div class="acc-card-header">
                            <h3>Thông tin cá nhân</h3>
                            <a href="#" style="color: #0046ab; font-size: 13px; font-weight: 600;{{ $hasProfileError ? ' display: none;' : '' }}" onclick="event.preventDefault(); document.getElementById('viewProfileInfo').style.display = 'none'; document.getElementById('editProfileForm').style.display = 'block'; this.style.display = 'none';">Sửa</a>
                        </div>
                        
                        @if(session('success'))
                            <div style="background: #dcfce7; color: #166534; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-weight: 600; font-size: 13px;">
                                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                            </div>
                        @endif

                        <div class="acc-card-content" id="viewProfileInfo" style="{{ $hasProfileError ? 'display: none;' : 'display: flex;' }}">
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
                        <form id="editProfileForm" action="{{ route('profile.update') }}" method="POST" style="{{ $hasProfileError ? 'display: block;' : 'display: none;' }} margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                            @csrf
                            @if($errors->has('no_change'))
                                <div style="background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-weight: 600; font-size: 13px;">
                                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first('no_change') }}
                                </div>
                            @endif
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Họ và tên</label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $user->full_name) }}" required>
                                @error('full_name')
                                    <span style="color: #e21033; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Địa chỉ Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $user->email }}" readonly style="background: #f1f5f9; cursor: not-allowed;">
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Giới tính</label>
                                <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                                    <option value="">Chọn giới tính</option>
                                    <option value="Nam" {{ old('gender', $user->gender) == 'Nam' ? 'selected' : '' }}>Nam</option>
                                    <option value="Nữ" {{ old('gender', $user->gender) == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                    <option value="Khác" {{ old('gender', $user->gender) == 'Khác' ? 'selected' : '' }}>Khác</option>
                                </select>
                                @error('gender')
                                    <span style="color: #e21033; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Ngày sinh</label>
                                <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" value="{{ old('dob', $user->dob) }}">
                                @error('dob')
                                    <span style="color: #e21033; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Số điện thoại</label>
                                <input type="tel" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}" placeholder="Nhập số điện thoại">
                                @error('phone_number')
                                    <span style="color: #e21033; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Địa chỉ mặc định</label>
                                <input type="text" name="address" class="form-control @error('address') is-invalid @enderror" value="{{ old('address', $user->address) }}" placeholder="Nhập địa chỉ của bạn">
                                @error('address')
                                    <span style="color: #e21033; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Ngày tham gia</label>
                                <input type="text" class="form-control" value="{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Không rõ' }}" readonly style="background: #f1f5f9; cursor: not-allowed;">
                            </div>
                            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                <button type="button" class="btn-outline" onclick="resetProfileForm(); document.getElementById('editProfileForm').style.display='none'; document.getElementById('viewProfileInfo').style.display='flex'; document.querySelector('.acc-card-header a').style.display='block';">Hủy</button>
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
                                            {{ $address->name ?: $user->full_name }}
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

                    <!-- Xác thực 2 bước (2FA) -->
                    <div class="acc-card" style="grid-column: 1 / -1;">
                        <div class="acc-card-header">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <h3>Xác thực 2 bước (2FA)</h3>
                                @if($user->two_factor_secret)
                                    <span class="status-badge status-completed" style="font-size: 11px; padding: 2px 8px;">Đã bật <i class="fa-solid fa-shield-check"></i></span>
                                @else
                                    <span class="status-badge" style="background:#f1f5f9; color:#64748b; font-size: 11px; padding: 2px 8px;">Chưa bật</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="acc-info-row" style="border: none; flex-direction: column; gap: 15px; align-items: flex-start;">
                            <p style="font-size: 13px; color: #555; margin: 0; line-height: 1.5;">Bảo vệ tài khoản của bạn bằng cách thêm một lớp bảo mật bổ sung. Khi đăng nhập, bạn sẽ cần nhập mã xác minh từ ứng dụng Authenticator ngoài mật khẩu của bạn.</p>
                            
                            <div style="display: flex; gap: 10px;">
                                @if($user->is_2fa_enabled || $user->two_factor_secret)
                                    <a href="{{ route('security') }}" class="btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">Cấu hình lại</a>
                                @else
                                    <a href="{{ route('security') }}" class="btn-update" style="background: #0046ab; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fa-solid fa-shield-halved" style="margin-right: 5px;"></i> Thiết lập 2FA ngay
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB DANH SÁCH YÊU THÍCH -->
            <div id="wishlist-tab" class="profile-tab">
                <div class="info-form-wrap">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h3 style="margin: 0;">Danh sách yêu thích (<span id="wishlist-title-count">{{ count($wishlist) }}</span>)</h3>
                        <div id="wishlist-clear-btn-wrapper">
                            @if(count($wishlist) > 0)
                                <button type="button" class="btn-outline" style="color: #d70018; border-color: #d70018;" onclick="clearWishlist()">
                                    <i class="fa-solid fa-trash-can"></i> Xóa tất cả
                                </button>
                            @endif
                        </div>
                    </div>

                    <div id="wishlist-content-area">
                        @if(count($wishlist) > 0)
                            <div class="wishlist-grid">
                                @foreach($wishlist as $item)
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
            </div>

            <!-- TAB ĐẶT LỊCH VÀ LỊCH SỬ SỬA CHỮA -->
            <div id="repair-tab" class="profile-tab">
                <div class="info-form-wrap">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                        <h3 style="margin: 0; border: none; padding: 0;">Lịch Sử & Đặt Lịch Sửa Chữa</h3>
                        <button type="button" class="btn-update" style="margin: 0; background: #0046ab;" onclick="openRepairModal()">
                            <i class="fa-solid fa-circle-plus"></i> Đặt lịch sửa chữa mới
                        </button>
                    </div>

                    @if(session('repair_success'))
                        <div style="background: #dcfce7; color: #166534; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-circle-check"></i> {{ session('repair_success') }}
                        </div>
                    @endif

                    @if($repairTickets->count() > 0)
                        <div style="overflow-x: auto;">
                            <table class="order-table">
                                <thead>
                                    <tr>
                                        <th>Mã Phiếu</th>
                                        <th>Mã IMEI / Serial</th>
                                        <th>Lỗi mô tả</th>
                                        <th>Trạng Thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($repairTickets as $ticket)
                                    <tr>
                                        <td><strong>#RT-{{ $ticket->ticket_id }}</strong></td>
                                        <td><span style="font-family: monospace;">{{ $ticket->imei_serial }}</span></td>
                                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $ticket->issue_desc }}">
                                            {{ $ticket->issue_desc }}
                                        </td>
                                        <td>
                                            @if($ticket->status === 'Received')
                                                <span class="status-badge" style="background:#f1f5f9; color:#64748b;">Đã tiếp nhận</span>
                                            @elseif($ticket->status === 'Checking')
                                                <span class="status-badge" style="background:#bae6fd; color:#0369a1;">Kiểm tra & báo giá</span>
                                            @elseif($ticket->status === 'Under_Repair')
                                                <span class="status-badge" style="background:#e0e7ff; color:#4338ca;">Đang sửa chữa</span>
                                            @elseif($ticket->status === 'Waiting_Parts')
                                                <span class="status-badge" style="background:#fef3c7; color:#d97706;">Chờ linh kiện</span>
                                            @elseif($ticket->status === 'Done')
                                                <span class="status-badge" style="background:#dcfce7; color:#166534;">Hoàn thành</span>
                                            @else
                                                <span class="status-badge" style="background:#f1f5f9; color:#64748b;">{{ $ticket->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <!-- Lưu dữ liệu JSON của ticket để đổ vào stepper modal tracking -->
                                            <button class="btn-outline" style="padding: 6px 12px; font-size: 12px; margin: 0;"
                                                    data-ticket="{{ json_encode($ticket->load('technician')) }}" onclick="viewProgress(this)">
                                                <i class="fa-solid fa-eye"></i> Chi tiết
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="dash-empty" style="padding: 50px 0;">
                            <i class="fa-solid fa-screwdriver-wrench" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                            <p>Bạn chưa đăng ký lịch hẹn sửa chữa trực tuyến nào.</p>
                            <button type="button" class="btn-outline" onclick="openRepairModal()">Đặt lịch sửa chữa ngay</button>
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

                    <!-- Vouchers & Quà đã đổi -->
                    <h4 style="font-size: 16px; margin: 40px 0 15px;"><i class="fa-solid fa-gift"></i> Ưu đãi & Quà tặng đã đổi của bạn</h4>
                    @if($redemptions->count() > 0 || $spins->where('status', 'won')->whereNotNull('reward_id')->count() > 0)
                        <div class="voucher-grid">
                            {{-- Quà từ Catalog Đổi Thưởng --}}
                            @foreach($redemptions as $redemption)
                                @php
                                    $reward = json_decode($redemption->reward_snapshot, true);
                                    $isExpired = $redemption->expires_at ? \Carbon\Carbon::parse($redemption->expires_at)->isPast() : false;
                                    $statusLabel = 'Khả dụng';
                                    $statusColor = '#166534';
                                    
                                    if ($redemption->status === 'cancelled') {
                                        $statusLabel = 'Đã hủy';
                                        $statusColor = '#991b1b';
                                    } elseif ($isExpired) {
                                        $statusLabel = 'Hết hạn';
                                        $statusColor = '#64748b';
                                    } elseif ($redemption->status === 'pending') {
                                        $statusLabel = 'Chờ duyệt';
                                        $statusColor = '#d97706';
                                    }
                                @endphp
                                <div class="voucher-card" style="{{ $redemption->status === 'cancelled' || $isExpired ? 'opacity: 0.6;' : '' }}">
                                    <div class="voucher-left" style="background: {{ $redemption->status === 'cancelled' || $isExpired ? '#64748b' : '#0046ab' }};">
                                        <i class="fa-solid fa-percent"></i>
                                    </div>
                                    <div class="voucher-right">
                                        <span class="voucher-code">{{ $redemption->redemption_code }}</span>
                                        <button class="btn-copy-voucher" onclick="copyVoucher('{{ $redemption->redemption_code }}', this)">Sao chép</button>
                                        <div class="voucher-title" style="font-weight: 600; margin-bottom: 2px;">{{ $reward['name'] ?? 'Mã ưu đãi' }}</div>
                                        <div style="font-size: 11px; margin-bottom: 5px;">
                                            Trạng thái: <strong style="color: {{ $statusColor }};">{{ $statusLabel }}</strong>
                                        </div>
                                        <div class="voucher-expiry">HSD: {{ $redemption->expires_at ? \Carbon\Carbon::parse($redemption->expires_at)->format('d/m/Y') : 'Không giới hạn' }}</div>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Quà trúng từ Vòng Quay May Mắn --}}
                            @foreach($spins as $spin)
                                @if($spin->status === 'won' && $spin->reward_id)
                                    @php
                                        $prize = json_decode($spin->result_snapshot, true);
                                        $isExpired = $spin->expires_at ? \Carbon\Carbon::parse($spin->expires_at)->isPast() : false;
                                        $statusLabel = 'Trúng thưởng';
                                        $statusColor = '#166534';

                                        if ($isExpired) {
                                            $statusLabel = 'Hết hạn';
                                            $statusColor = '#64748b';
                                        }
                                    @endphp
                                    <div class="voucher-card" style="{{ $isExpired ? 'opacity: 0.6;' : '' }}">
                                        <div class="voucher-left" style="background: {{ $isExpired ? '#64748b' : '#e21033' }};">
                                            <i class="fa-solid fa-gift"></i>
                                        </div>
                                        <div class="voucher-right">
                                            <span class="voucher-code">{{ $spin->spin_code }}</span>
                                            <button class="btn-copy-voucher" onclick="copyVoucher('{{ $spin->spin_code }}', this)">Sao chép</button>
                                            <div class="voucher-title" style="font-weight: 600; margin-bottom: 2px;">{{ $prize['name'] ?? 'Phần quà Vòng quay' }}</div>
                                            <div style="font-size: 11px; margin-bottom: 5px;">
                                                Nguồn: <strong style="color: {{ $statusColor }};">{{ $statusLabel }}</strong>
                                            </div>
                                            <div class="voucher-expiry">HSD: {{ $spin->expires_at ? \Carbon\Carbon::parse($spin->expires_at)->format('d/m/Y') : 'Không giới hạn' }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="dash-empty" style="padding: 30px 0; border: 1px dashed #eee; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                            <i class="fa-solid fa-gift" style="font-size: 40px; color: #ddd; margin-bottom: 10px;"></i>
                            <p style="font-size: 13px; color: #666; margin: 0 0 10px 0;">Bạn chưa có ưu đãi nào được đổi.</p>
                            <a href="{{ route('rewards.index') }}" class="btn-outline" style="margin: 0; padding: 6px 15px; font-size: 12px;">Đổi quà ngay</a>
                        </div>
                    @endif

                    <!-- Lịch sử tích/tiêu điểm -->
                    <h4 style="font-size: 16px; margin: 40px 0 15px;"><i class="fa-solid fa-clock-rotate-left"></i> Lịch sử biến động điểm</h4>
                    @if($pointTransactions->count() > 0)
                        <div style="overflow-x: auto;">
                            <table class="order-table">
                                <thead>
                                    <tr>
                                        <th>Thời Gian</th>
                                        <th>Loại Điểm</th>
                                        <th>Giao Dịch</th>
                                        <th>Số Điểm</th>
                                        <th>Nội Dung</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pointTransactions as $trans)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($trans->created_at)->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($trans->point_type === 'wallet')
                                                <span class="status-badge" style="background: #fef3c7; color: #d97706; font-size: 11px;"><i class="fa-solid fa-coins"></i> Tiêu dùng</span>
                                            @else
                                                <span class="status-badge" style="background: #f5f3ff; color: #7c3aed; font-size: 11px;"><i class="fa-solid fa-star"></i> Tích lũy hạng</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($trans->action === 'earn')
                                                <span style="color: #166534; font-weight: bold;">Cộng điểm</span>
                                            @elseif($trans->action === 'use')
                                                <span style="color: #991b1b; font-weight: bold;">Trừ điểm</span>
                                            @elseif($trans->action === 'refund')
                                                <span style="color: #0369a1; font-weight: bold;">Hoàn điểm</span>
                                            @else
                                                <span style="color: #555;">{{ $trans->action }}</span>
                                            @endif
                                        </td>
                                        <td style="font-weight: bold; font-size: 14px;">
                                            @if($trans->points > 0 && in_array($trans->action, ['earn', 'refund']))
                                                <span style="color: #166534;">+{{ number_format($trans->points) }}</span>
                                            @elseif($trans->points > 0 && $trans->action === 'use')
                                                <span style="color: #b91c1c;">-{{ number_format($trans->points) }}</span>
                                            @else
                                                <span style="color: #555;">{{ ($trans->points > 0 ? '+' : '') . number_format($trans->points) }}</span>
                                            @endif
                                        </td>
                                        <td style="font-size: 13px;">{{ $trans->description }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="dash-empty" style="padding: 20px 0; border: 1px dashed #eee; border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                            <i class="fa-solid fa-list-check" style="font-size: 30px; color: #ddd; margin-bottom: 10px;"></i>
                            <p style="font-size: 13px; color: #666; margin: 0;">Chưa có lịch sử biến động điểm.</p>
                        </div>
                    @endif
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
                    <input type="text" id="addrStreet" class="form-control" placeholder="Ví dụ: 123 Đường ABC" required style="padding: 10px 15px;" maxlength="150">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #555; margin-bottom: 5px;">Tên gợi nhớ</label>
                    <input type="text" id="addrName" class="form-control" placeholder="Nguyễn Văn A" style="padding: 10px 15px;" maxlength="50">
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
            <button type="button" class="btn-outline" style="margin-top: 0;" onclick="closeAddressModal()">Đóng</button>
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

<!-- Modal Đăng Ký Lịch Sửa Chữa Trực Tuyến -->
<div id="repairModal" class="student-modal-overlay">
    <div class="student-modal" style="max-width: 680px; width: 95%;">
        <div class="student-modal-header" style="background: #0046ab;">
            <h3>Đăng ký lịch hẹn sửa chữa trực tuyến</h3>
            <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 18px;" onclick="closeRepairModal()"></i>
        </div>
        <div class="student-modal-body" style="max-height: 75vh; overflow-y: auto;">
            <form id="repairRegistrationForm" action="{{ route('profile.repair-tickets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid-2-cols">
                    <!-- SECTION 1: CONTACT INFO -->
                    <div class="form-section-card">
                        <div class="form-section-title">
                            <i class="fa-solid fa-id-card"></i> Thông tin liên hệ
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label>Họ và tên *</label>
                            <input type="text" name="customer_name" id="repCustomerName" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name', $user->full_name) }}" required maxlength="50">
                            @error('customer_name')
                                <div style="color: #e21033; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label>Số điện thoại *</label>
                            <input type="text" name="customer_phone" id="repCustomerPhone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone', $user->phone_number) }}" placeholder="Ví dụ: 0392345678" required maxlength="10">
                            @error('customer_phone')
                                <div style="color: #e21033; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label>Địa chỉ email</label>
                            <input type="email" name="customer_email" id="repCustomerEmail" class="form-control @error('customer_email') is-invalid @enderror" value="{{ old('customer_email', $user->email) }}" placeholder="VD: customer@gmail.com" maxlength="100">
                            @error('customer_email')
                                <div style="color: #e21033; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Địa chỉ liên hệ</label>
                            <input type="text" name="customer_address" id="repCustomerAddress" class="form-control @error('customer_address') is-invalid @enderror" value="{{ old('customer_address', $user->address) }}" placeholder="Nhập số nhà, tên đường, phường/xã..." maxlength="150">
                            @error('customer_address')
                                <div style="color: #e21033; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- SECTION 2: DEVICE & SCHEDULE INFO -->
                    <div class="form-section-card">
                        <div class="form-section-title">
                            <i class="fa-solid fa-screwdriver-wrench"></i> Thông tin sửa chữa
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label>Số IMEI / Serial *</label>
                            <input type="text" name="imei_serial" id="repImeiSerial" class="form-control @error('imei_serial') is-invalid @enderror" value="{{ old('imei_serial') }}" placeholder="Nhập số IMEI hoặc Serial máy" required maxlength="50">
                            @error('imei_serial')
                                <div style="color: #e21033; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label>Ngày hẹn mang máy tới *</label>
                            <input type="date" name="schedule_date" id="repScheduleDate" class="form-control @error('schedule_date') is-invalid @enderror" value="{{ old('schedule_date') }}" min="{{ date('Y-m-d') }}" required>
                            @error('schedule_date')
                                <div style="color: #e21033; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label>Mô tả tình trạng lỗi *</label>
                            <textarea name="issue_desc" id="repIssueDesc" class="form-control @error('issue_desc') is-invalid @enderror" rows="4" placeholder="Mô tả chi tiết tình trạng máy lỗi và linh kiện cần thay thế..." style="resize: none;" required maxlength="500">{{ old('issue_desc') }}</textarea>
                            @error('issue_desc')
                                <div style="color: #e21033; font-size: 11px; margin-top: 4px;">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Ảnh chụp tình trạng / lỗi (AI Vision)</label>
                            <input type="file" name="device_image" id="repDeviceImage" accept="image/*" class="form-control" onchange="previewRepairImage(this)">
                            <div id="repairImagePreviewWrap" style="display: none; margin-top: 8px; text-align: center; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 5px; position: relative;">
                                <img id="repairImagePreview" src="" style="max-height: 120px; border-radius: 6px;">
                                <button type="button" onclick="removeRepairImage()" style="position: absolute; top: 5px; right: 5px; background: rgba(226, 16, 51, 0.8); color: white; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 11px;"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI DIAGNOSIS INTERACTIVE SECTION -->
                <div style="margin-top: 20px; border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                    <button type="button" id="btnAIDiagnose" class="btn-update" style="margin: 0; width: 100%; background: linear-gradient(135deg, #7c3aed, #0046ab); display: flex; align-items: center; justify-content: center; gap: 8px; font-weight: 600; border: none; box-shadow: 0 4px 6px rgba(124, 58, 237, 0.25);">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> ✨ Phân tích & Chẩn đoán lỗi bằng AI
                    </button>

                    <!-- AI Diagnosis Report Card -->
                    <div id="aiDiagnosisReport" style="display: none; background: rgba(243, 244, 246, 0.75); border: 1px solid #7c3aed; border-radius: 12px; padding: 15px; margin-top: 15px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); backdrop-filter: blur(4px);">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 10px;">
                            <strong style="color: #7c3aed; font-size: 14px; display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-robot"></i> KẾT QUẢ CHẨN ĐOÁN & TƯ VẤN AI</strong>
                            <span id="aiComplexityBadge" class="status-badge" style="font-size: 11px; background: #f3e8ff; color: #7c3aed; padding: 2px 8px; border-radius: 12px; font-weight: 600;">Trung bình</span>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 10px; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 4px;">
                                <span><strong>Loại lỗi dự đoán:</strong></span>
                                <strong id="aiFaultType" style="color: #1e293b;">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 4px;">
                                <span><strong>Giải pháp / Linh kiện dự kiến:</strong></span>
                                <strong id="aiReplacementParts" style="color: #1e293b;">-</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 4px; align-items: center;">
                                <span><strong>Khoảng giá dự toán:</strong></span>
                                <strong id="aiEstimatedCostRange" style="color: #e21033; font-size: 15px;">-</strong>
                            </div>
                            
                            <!-- Cảnh báo rủi ro -->
                            <div id="aiRiskWarningsBox" style="background: #fff5f5; border-left: 4px solid #f87171; border-radius: 4px; padding: 8px; margin-top: 5px;">
                                <strong style="color: #c53030; font-size: 12px; display: flex; align-items: center; gap: 4px;"><i class="fa-solid fa-circle-exclamation"></i> CẢNH BÁO RỦI RO:</strong>
                                <ul id="aiRiskWarningsList" style="margin: 4px 0 0 15px; padding: 0; color: #9b2c2c; font-size: 12px; list-style-type: disc;">
                                </ul>
                            </div>

                            <!-- Nguyên nhân -->
                            <div style="margin-top: 5px;">
                                <strong>Nguyên nhân có thể xảy ra:</strong>
                                <ul id="aiProbableCausesList" style="margin: 4px 0 0 15px; padding: 0; color: #475569; list-style-type: circle; line-height: 1.4;">
                                </ul>
                            </div>

                            <!-- Phân công kỹ thuật viên -->
                            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px; margin-top: 8px; display: flex; align-items: flex-start; gap: 10px;">
                                <i class="fa-solid fa-user-gear" style="font-size: 20px; color: #0284c7; margin-top: 2px;"></i>
                                <div>
                                    <strong style="color: #1e3a8a;">Kỹ thuật viên phụ trách đề xuất:</strong> <span id="aiAssignedTech" style="color: #0284c7; font-weight: bold;">-</span>
                                    <p id="aiDispatchReason" style="margin: 4px 0 0 0; font-size: 12px; color: #4b5563; font-style: italic; line-height: 1.4;"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for AI diagnostic results -->
                <input type="hidden" name="ai_diagnose_token" id="hidAiDiagnoseToken">
                <input type="hidden" name="ai_diagnosed" id="hidAiDiagnosed" value="0">
                <input type="hidden" name="ai_fault_type" id="hidAiFaultType">
                <input type="hidden" name="ai_probable_causes" id="hidAiProbableCauses">
                <input type="hidden" name="ai_risk_warnings" id="hidAiRiskWarnings">
                <input type="hidden" name="ai_replacement_parts" id="hidAiReplacementParts">
                <input type="hidden" name="ai_estimated_cost_min" id="hidAiEstimatedCostMin">
                <input type="hidden" name="ai_estimated_cost_max" id="hidAiEstimatedCostMax">
                <input type="hidden" name="ai_complexity_level" id="hidAiComplexityLevel">
                <input type="hidden" name="ai_recommended_skills" id="hidAiRecommendedSkills">
                <input type="hidden" name="ai_dispatch_reason" id="hidAiDispatchReason">
                <input type="hidden" name="assigned_technician_id" id="hidAssignedTechnicianId">

                <div style="font-size: 11px; color: #64748b; font-style: italic; margin-top: 15px;">
                    * Lưu ý: Quý khách vui lòng mang thiết bị đến cửa hàng theo đúng ngày hẹn để được nhân viên hỗ trợ kiểm tra nhanh nhất.
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                    <button type="button" class="btn-outline" style="margin: 0;" onclick="closeRepairModal()">Hủy bỏ</button>
                    <button type="submit" id="repairSubmitBtn" class="btn-update" style="margin: 0; background: #0046ab;">Đăng ký lịch hẹn</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Theo Dõi Tiến Trình Sửa Chữa -->
<div id="trackingModal" class="student-modal-overlay">
    <div class="student-modal" style="max-width: 520px; width: 95%;">
        <div class="student-modal-header" style="background: #0046ab;">
            <h3>{{ __('ui.repair_title', ['id' => '']) }}<span id="track-id"></span></h3>
            <i class="fa-solid fa-xmark" style="cursor: pointer; font-size: 18px;" onclick="closeTrackingModal()"></i>
        </div>
        <div class="student-modal-body" style="max-height: 75vh; overflow-y: auto;">
            <!-- Tóm tắt thông tin thiết bị -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                    <div><span style="color: #64748b;">{{ __('ui.repair_imei') }}</span> <strong id="track-imei" style="font-family: monospace;">-</strong></div>
                    <div><span id="track-date-label" style="color: #64748b;">{{ __('ui.repair_date_received_label') }}</span> <strong id="track-date">-</strong></div>
                    <div style="grid-column: 1 / -1;"><span style="color: #64748b;">{{ __('ui.repair_error_desc') }}</span> <span id="track-desc">-</span></div>
                    <div style="grid-column: 1 / -1;"><span style="color: #64748b;">{{ __('ui.repair_technician') }}</span> <strong id="track-tech" style="color: #0046ab;">{{ __('ui.repair_tech_assigning') }}</strong></div>
                </div>
            </div>

            <!-- Ảnh chụp thiết bị thực tế -->
            <div id="track-image-container" style="display: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; margin-bottom: 20px; text-align: center;">
                <span style="color: #64748b; font-size: 13px; display: block; margin-bottom: 8px; text-align: left; font-weight: 600;"><i class="fa-regular fa-image"></i> Hình ảnh thiết bị:</span>
                <img id="track-device-image" src="" style="max-height: 180px; border-radius: 8px; border: 1px solid #cbd5e1; display: inline-block;">
            </div>

            <!-- Bản tin tư vấn & Chẩn đoán AI -->
            <div id="track-ai-report" style="display: none; background: rgba(124, 58, 237, 0.06); border: 1px solid rgba(124, 58, 237, 0.2); border-radius: 12px; padding: 15px; margin-bottom: 20px; font-size: 13px;">
                <div style="color: #7c3aed; font-weight: bold; margin-bottom: 8px; display: flex; align-items: center; gap: 5px;"><i class="fa-solid fa-robot"></i> Chẩn đoán & Khuyến nghị từ AI</div>
                <div style="display: grid; gap: 6px;">
                    <div><span style="color: #64748b;">Chẩn đoán loại lỗi:</span> <strong id="track-ai-fault-type" style="color: #1e293b;">-</strong></div>
                    <div><span style="color: #64748b;">Linh kiện/Giải pháp dự kiến:</span> <strong id="track-ai-parts" style="color: #1e293b;">-</strong></div>
                    <div><span style="color: #64748b;">Chi phí dự toán:</span> <strong id="track-ai-cost" style="color: #e21033;">-</strong></div>
                    <div id="track-ai-warnings-box" style="background: #fff5f5; border-left: 3px solid #ef4444; padding: 8px; border-radius: 4px; margin-top: 5px;">
                        <div style="color: #b91c1c; font-weight: bold; font-size: 12px; margin-bottom: 4px;"><i class="fa-solid fa-circle-exclamation"></i> Khuyến cáo phòng ngừa từ AI:</div>
                        <ul id="track-ai-warnings" style="margin: 0 0 0 15px; padding: 0; color: #7f1d1d; font-size: 12px; list-style-type: disc;"></ul>
                    </div>
                </div>
            </div>

            <!-- Stepper Tiến Trình Trực Quan -->
            <div class="stepper">
                <!-- Bước 1: Tiếp nhận -->
                <div class="step-item" id="step-received">
                    <div class="step-icon">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div class="step-content">
                        <h4 class="step-title">{{ __('ui.repair_step_received') }}</h4>
                        <div class="step-desc" id="step-received-desc">
                            {{ __('ui.repair_step_received_desc') }}
                        </div>
                    </div>
                </div>

                <!-- Bước 2: Kiểm tra & Báo giá -->
                <div class="step-item" id="step-checking">
                    <div class="step-icon">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <div class="step-content">
                        <h4 class="step-title">{{ __('ui.repair_step_checking') }}</h4>
                        <div class="step-desc" id="step-checking-desc">
                            {{ __('ui.repair_step_checking_desc') }}
                        </div>
                    </div>
                </div>

                <!-- Bước 3: Đang sửa chữa -->
                <div class="step-item" id="step-repairing">
                    <div class="step-icon">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <div class="step-content">
                        <h4 class="step-title">{{ __('ui.repair_step_repairing') }}</h4>
                        <div class="step-desc" id="step-repairing-desc">
                            {{ __('ui.repair_step_repairing_desc') }}
                        </div>
                    </div>
                </div>

                <!-- Bước 4: Hoàn thành -->
                <div class="step-item" id="step-done">
                    <div class="step-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="step-content">
                        <h4 class="step-title">{{ __('ui.repair_step_done') }}</h4>
                        <div class="step-desc" id="step-done-desc">
                            {{ __('ui.repair_step_done_desc') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Gửi Yêu Cầu Đổi Trả -->
<div id="claimModal" class="student-modal-overlay">
    <div class="student-modal" style="max-width: 480px; width: 95%; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden;">
        <div class="student-modal-header" id="claimModalHeader" style="background: #f59e0b; padding: 12px 18px; flex-shrink: 0;">
            <h3 id="claimModalTitle" style="font-size: 16px; margin: 0; font-weight: 700; color: #ffffff;">Gửi yêu cầu đổi trả sản phẩm</h3>
            <i class="fa-solid fa-xmark" id="claimModalCloseIcon" style="cursor: pointer; font-size: 18px; color: #ffffff;" onclick="closeProfileClaimModal()"></i>
        </div>
        <div class="student-modal-body" style="padding: 16px; overflow-y: auto; max-height: calc(90vh - 60px);">
            <form id="claimForm" onsubmit="submitProfileClaim(event)" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 12px;">
                @csrf
                
                <!-- Hộp thông tin sản phẩm và IMEI tinh gọn -->
                <div style="background: #f8fafc; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <span style="font-weight: 700; color: #475569;">Sản phẩm:</span>
                        <span style="color: #1e293b;" id="modalProductNameDisplay"></span>
                    </div>
                    <div style="display: flex; gap: 6px; align-items: center; margin-top: 6px; padding-top: 6px; border-top: 1px dashed #e2e8f0;">
                        <span style="font-weight: 700; color: #475569;">IMEI:</span>
                        <span style="color: #0f172a; font-family: monospace; font-weight: 600;" id="modalImeiDisplay"></span>
                    </div>
                </div>

                <!-- Các input ẩn chứa giá trị để gửi form -->
                <input type="hidden" id="modalProductName">
                <input type="hidden" id="modalImei" name="imei_serial">

                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Loại yêu cầu</label>
                    <select id="modalClaimType" name="claim_type" class="form-control" style="width: 100%; padding: 8px 12px; font-size: 13px; height: auto;" required>
                        <option value="return">Đổi trả hàng hoàn tiền</option>
                        <option value="exchange">Đổi máy mới/khách</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Họ tên</label>
                        <input type="text" id="modalCustomerName" name="customer_name" value="{{ $user->full_name }}" class="form-control" style="width: 100%; padding: 8px 12px; font-size: 13px; height: auto;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Số điện thoại</label>
                        <input type="text" id="modalCustomerPhone" name="customer_phone" value="{{ $user->phone_number }}" class="form-control" style="width: 100%; padding: 8px 12px; font-size: 13px; height: auto;" required>
                    </div>
                </div>
                
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Email liên hệ</label>
                    <input type="email" id="modalCustomerEmail" name="customer_email" value="{{ $user->email }}" class="form-control" style="width: 100%; padding: 8px 12px; font-size: 13px; height: auto;">
                </div>
                
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">Lý do yêu cầu</label>
                    <textarea id="modalReason" name="reason" rows="2" placeholder="Mô tả cụ thể lỗi thiết bị hoặc lý do..." class="form-control" style="width: 100%; padding: 8px 12px; font-size: 13px; resize: none; height: auto;" required></textarea>
                </div>
                
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 4px;">
                        Hình ảnh hoặc Video minh họa <span style="font-weight: normal; color: #94a3b8;">(Tối đa 20MB)</span>
                    </label>
                    <input type="file" id="modalMediaFile" name="media_file" accept="image/*,video/*" class="form-control" style="width: 100%; padding: 6px 10px; font-size: 12px; height: auto;">
                </div>
                <!-- Refund Method (Only shown for return) -->
                <div id="refundMethodSection" style="display: none; flex-direction: column; gap: 4px;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Phương thức nhận tiền hoàn</label>
                    <select id="modalRefundMethod" name="refund_method" class="form-control" style="width:100%;padding:8px 12px;font-size:13px;outline:none;background:#fff;height:auto;">
                        <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                        <option value="cash">Tiền mặt tại cửa hàng</option>
                    </select>
                </div>
                <!-- Bank Details (Only shown for return) -->
                <div id="bankDetailsSection" style="display: none; border-top: 1px dashed #e2e8f0; padding-top: 12px; margin-top: 4px; flex-direction: column; gap: 10px;">
                    <h4 style="font-size: 13px; font-weight: 700; color: #d97706; margin: 0; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-building-columns"></i> Thông tin nhận tiền hoàn
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Ngân hàng</label>
                            <input type="text" id="modalBankName" name="bank_name" placeholder="VD: Vietcombank" class="form-control" style="width:100%;padding:8px 12px;font-size:13px;height:auto;">
                        </div>
                        <div>
                            <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Số tài khoản</label>
                            <input type="text" id="modalBankAccountNumber" name="bank_account_number" placeholder="VD: 1023456789" class="form-control" style="width:100%;padding:8px 12px;font-size:13px;height:auto;">
                        </div>
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Tên chủ tài khoản</label>
                        <input type="text" id="modalBankAccountName" name="bank_account_name" placeholder="VD: NGUYEN VAN A" class="form-control" style="width:100%;padding:8px 12px;font-size:13px;height:auto;text-transform: uppercase;">
                    </div>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 4px; padding-top: 12px; border-top: 1px solid #f1f5f9; flex-shrink: 0;">
                    <button type="button" class="btn-outline" style="margin-top:0; padding: 8px 16px; font-size: 13px;" onclick="closeProfileClaimModal()">Hủy</button>
                    <button type="submit" class="btn-update" id="btnSubmitClaim" style="margin-top:0; background: #f59e0b; padding: 8px 16px; font-size: 13px;">Gửi yêu cầu</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // ============================================================
    // DỮ LIỆU BAN ĐẦU CỦA PROFILE KHÁCH HÀNG (DÙNG ĐỂ CHECK THAY ĐỔI)
    // ============================================================
    const originalProfileData = {
        full_name: @json($user->full_name),
        gender: @json($user->gender ?? ''),
        dob: @json($user->dob ?? ''),
        phone_number: @json($user->phone_number ?? ''),
        address: @json($user->address ?? '')
    };

    /**
     * Khôi phục form cập nhật thông tin cá nhân về trạng thái ban đầu.
     * Xóa sạch các class báo lỗi validation viền đỏ (is-invalid) cũ.
     */
    function resetProfileForm() {
        const form = document.getElementById('editProfileForm');
        if (form) {
            form.querySelector('[name="full_name"]').value = originalProfileData.full_name;
            form.querySelector('[name="gender"]').value = originalProfileData.gender;
            form.querySelector('[name="dob"]').value = originalProfileData.dob;
            form.querySelector('[name="phone_number"]').value = originalProfileData.phone_number;
            form.querySelector('[name="address"]').value = originalProfileData.address;
            
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('span[style*="color: #e21033"]').forEach(el => el.remove());
            const noChangeAlert = form.querySelector('div[style*="background: #fee2e2"]');
            if (noChangeAlert) noChangeAlert.remove();
        }
    }

    /**
     * CHUYỂN TẢI GIAO DIỆN TAB ĐỘNG (TAB SWITCHER SYSTEM)
     * Ẩn/hiện các tab tương ứng và đồng bộ hóa tham số 'tab' vào URL search params của trình duyệt
     * để người dùng reload trang không bị mất tab hiện tại.
     */
    function switchTab(tabId, element) {
        document.querySelectorAll('.profile-nav-item').forEach(item => {
            item.classList.remove('active');
        });
        
        if(element) element.classList.add('active');

        document.querySelectorAll('.profile-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        document.getElementById(tabId).classList.add('active');

        // Đồng bộ hóa tab vào URL search params của trình duyệt
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.replaceState({}, '', url);
    }

    // ============================================================
    // HỆ THỐNG TOAST THÔNG BÁO TỰ TẠO (TOAST SYSTEM)
    // ============================================================
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

    // ============================================================
    // HỘP THOẠI XÁC NHẬN TÙY CHỈNH (CUSTOM CONFIRMATION MODAL SYSTEM)
    // ============================================================
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
        const btn = document.getElementById('btnDoConfirm');
        btn.disabled = false;
        btn.innerHTML = 'Xác nhận xóa';
    }

    document.getElementById('btnDoConfirm').addEventListener('click', function() {
        if (confirmCallback) {
            this.disabled = true;
            this.innerHTML = '<span class="spinner"></span> Đang xóa...';
            confirmCallback();
        }
    });

    /**
     * Bật/tắt trạng thái chờ loading cho nút bấm để tránh double click submit.
     */
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

    // ============================================================
    // MODAL ĐĂNG KÝ HỌC SINH/SINH VIÊN (S-STUDENT PROGRAM REGISTRATION)
    // ============================================================
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

    // Hiển thị ảnh xem trước (Preview) của thẻ học sinh khi upload
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

    /**
     * Xóa sạch các trạng thái lỗi validation cũ của form
     *
     * Hàm này xóa class 'is-invalid' của các thẻ input/select bị lỗi và 
     * loại bỏ tất cả các thẻ thông báo lỗi màu đỏ (invalid-feedback-custom) 
     * để khôi phục giao diện form sạch sẽ trước khi thực hiện lượt validate mới.
     *
     * @param {string} formId - ID của thẻ form cần xóa lỗi
     */
    function clearValidationErrors(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback-custom').forEach(el => el.remove());
        }
    }

    /**
     * Đánh dấu ô nhập liệu bị lỗi và hiển thị thông báo bên dưới
     *
     * Hàm này thêm class 'is-invalid' vào ô nhập liệu để hiện viền đỏ, 
     * đồng thời tạo động một thẻ span chứa thông báo lỗi chèn ngay phía dưới ô nhập liệu đó.
     *
     * @param {string} id - ID của ô nhập liệu (input/select) bị lỗi
     * @param {string} message - Nội dung thông báo lỗi cần hiển thị
     * @param {boolean} isUploadBox - Cờ đánh dấu nếu đây là khung upload file (cần viền đỏ cho container cha)
     */
    function highlightInvalidField(id, message = '', isUploadBox = false) {
        const el = isUploadBox ? document.getElementById(id).parentElement : document.getElementById(id);
        if (el) {
            el.classList.add('is-invalid');
            if (message) {
                // Kiểm tra xem đã tồn tại thẻ báo lỗi cũ dưới ô này chưa
                let errorSpan = el.parentNode.querySelector('.invalid-feedback-custom');
                if (!errorSpan) {
                    errorSpan = document.createElement('span');
                    errorSpan.className = 'invalid-feedback-custom';
                    errorSpan.style.color = '#e21033';
                    errorSpan.style.fontSize = '11px';
                    errorSpan.style.display = 'block';
                    errorSpan.style.marginTop = '4px';
                    el.parentNode.appendChild(errorSpan);
                }
                errorSpan.innerText = message;
            }
        }
    }

    // Submit thông tin đăng ký S-Student lên hệ thống (phía client giả lập xử lý)
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

    // ============================================================
    // MODAL ĐĂNG KÝ DOANH NGHIỆP (M-BUSINESS REGISTRATION SYSTEM)
    // ============================================================
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

    // Submit đăng ký thành viên doanh nghiệp (giả lập)
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

    // ============================================================
    // MODAL SỔ ĐỊA CHỈ & HÀNH CHÍNH QUẬN HUYỆN (ADDRESS BOOK SYSTEM)
    // ============================================================
    const isEnglish = {{ App::getLocale() === 'en' ? 'true' : 'false' }};

    function openAddressModal() {
        document.getElementById('addressModal').classList.add('active');
        clearValidationErrors('addAddressForm');
        if (document.getElementById('addrCity').options.length <= 1) {
            // Tải danh sách tỉnh thành lần đầu tiên mở modal
            fetchProvincesData();
        }
    }

    function closeAddressModal() {
        document.getElementById('addressModal').classList.remove('active');
        document.getElementById('addAddressForm').reset();
        document.getElementById('addrId').value = '';
        clearValidationErrors('addAddressForm');
        
        // Reset lại dropdown
        document.getElementById('addrCity').selectedIndex = 0;
        document.getElementById('addrDistrict').innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        document.getElementById('addrDistrict').disabled = true;
        document.getElementById('addrWard').innerHTML = '<option value="">Chọn Phường/Xã</option>';
        document.getElementById('addrWard').disabled = true;
    }

    let vnData = [];



    /**
     * TẢI DANH SÁCH TỈNH THÀNH (PROVINCES FETCH API)
     * Sử dụng thư viện dữ liệu tĩnh Github cho tốc độ tải cực nhanh.
     */
    function fetchProvincesData() {
        fetch('https://raw.githubusercontent.com/kenzouno1/DiaGioiHanhChinhVN/master/data.json')
            .then(res => res.json())
            .then(data => {
                vnData = data;
                populateCities(data);
            })
            .catch(err => console.error('Error fetching provinces:', err));
    }

    // Whitelist 34 tỉnh thành hợp lệ cho phép áp dụng theo Nghị quyết 202/2025/QH15
    const provinceIdMap = {
        '79': 'TP. Hồ Chí Minh', '01': 'TP. Hà Nội', '31': 'TP. Hải Phòng', '48': 'TP. Đà Nẵng', '92': 'TP. Cần Thơ', '46': 'TP. Huế',
        '89': 'An Giang', '27': 'Bắc Ninh', '96': 'Cà Mau', '04': 'Cao Bằng',
        '66': 'Đắk Lắk', '11': 'Điện Biên', '75': 'Đồng Nai', '87': 'Đồng Tháp',
        '64': 'Gia Lai', '42': 'Hà Tĩnh', '33': 'Hưng Yên', '56': 'Khánh Hòa',
        '12': 'Lai Châu', '68': 'Lâm Đồng', '20': 'Lạng Sơn', '10': 'Lào Cai',
        '40': 'Nghệ An', '37': 'Ninh Bình', '25': 'Phú Thọ', '51': 'Quảng Ngãi',
        '22': 'Quảng Ninh', '45': 'Quảng Trị', '14': 'Sơn La', '72': 'Tây Ninh',
        '19': 'Thái Nguyên', '38': 'Thanh Hóa', '08': 'Tuyên Quang', '86': 'Vĩnh Long'
    };

    function populateCities(data) {
        const citySelect = document.getElementById('addrCity');
        citySelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';
        data.forEach(city => {
            // Lọc chỉ lấy các tỉnh thành nằm trong whitelist
            if (provinceIdMap.hasOwnProperty(city.Id)) {
                let displayName = provinceIdMap[city.Id];
                let option = document.createElement('option');
                option.value = displayName;
                option.dataset.code = city.Id;
                option.textContent = displayName;
                citySelect.appendChild(option);
            }
        });
    }

    // Lắng nghe sự kiện thay đổi Tỉnh/Thành để tải danh sách Quận/Huyện tương ứng
    document.getElementById('addrCity').addEventListener('change', function() {
        const cityCode = this.options[this.selectedIndex].dataset.code;
        const districtSelect = document.getElementById('addrDistrict');
        const wardSelect = document.getElementById('addrWard');
        
        districtSelect.innerHTML = '<option value="">Đang tải Quận/Huyện...</option>';
        districtSelect.disabled = true;
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        wardSelect.disabled = true;

        if (cityCode && vnData.length > 0) {
            const city = vnData.find(c => c.Id === cityCode);
            districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
            if(city && city.Districts) {
                city.Districts.forEach(dist => {
                    let option = document.createElement('option');
                    let displayName = dist.Name;
                    option.value = displayName;
                    option.dataset.code = dist.Id;
                    option.textContent = displayName;
                    districtSelect.appendChild(option);
                });
                districtSelect.disabled = false;
            }
        } else {
            districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        }
    });

    // Lắng nghe sự kiện thay đổi Quận/Huyện để tải danh sách Phường/Xã
    document.getElementById('addrDistrict').addEventListener('change', function() {
        const distCode = this.options[this.selectedIndex].dataset.code;
        const cityCode = document.getElementById('addrCity').options[document.getElementById('addrCity').selectedIndex]?.dataset?.code;
        const wardSelect = document.getElementById('addrWard');
        
        wardSelect.innerHTML = '<option value="">Đang tải Phường/Xã...</option>';
        wardSelect.disabled = true;

        if (distCode && cityCode && vnData.length > 0) {
            const city = vnData.find(c => c.Id === cityCode);
            if(city && city.Districts) {
                const dist = city.Districts.find(d => d.Id === distCode);
                wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
                if(dist && dist.Wards) {
                    dist.Wards.forEach(ward => {
                        let option = document.createElement('option');
                        let displayName = ward.Name;
                        option.value = displayName;
                        option.textContent = displayName;
                        wardSelect.appendChild(option);
                    });
                    wardSelect.disabled = false;
                }
            }
        } else {
            wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        }
    });

    /**
     * THỰC THI GỬI THÔNG TIN ĐỊA CHỈ LÊN SERVER QUA AJAX
     * Validate dữ liệu trống phía client.
     * AJAX POST/PUT lên controller xử lý lưu trữ địa chỉ.
     * Bắt lỗi validation (Status 422) từ Laravel Validator và ánh xạ xuống giao diện.
     */
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
        if(!city.value) { highlightInvalidField('addrCity', 'Vui lòng chọn Tỉnh/Thành phố.'); hasError = true; }
        if(!district.value) { highlightInvalidField('addrDistrict', 'Vui lòng chọn Quận/Huyện.'); hasError = true; }
        if(!ward.value) { highlightInvalidField('addrWard', 'Vui lòng chọn Phường/Xã.'); hasError = true; }
        if(!street.value) { highlightInvalidField('addrStreet', 'Vui lòng nhập địa chỉ (Số nhà, tên đường).'); hasError = true; }

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
                'Accept': 'application/json',
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
        .then(res => {
            return res.json().then(data => {
                if (!res.ok) {
                    let errorMsg = data.error || data.message;
                    // Xử lý lỗi validation từ Laravel Validator
                    if (data.errors) {
                        clearValidationErrors('addAddressForm');
                        for (const key in data.errors) {
                            let fieldId = '';
                            if (key === 'city') fieldId = 'addrCity';
                            else if (key === 'district') fieldId = 'addrDistrict';
                            else if (key === 'ward') fieldId = 'addrWard';
                            else if (key === 'street') fieldId = 'addrStreet';
                            else if (key === 'name') fieldId = 'addrName';
                            
                            if (fieldId && data.errors[key][0]) {
                                highlightInvalidField(fieldId, data.errors[key][0]);
                            }
                        }
                        const firstKey = Object.keys(data.errors)[0];
                        if (firstKey && data.errors[firstKey][0]) {
                            errorMsg = data.errors[firstKey][0];
                        }
                    }
                    return { success: false, error: errorMsg };
                }
                return data;
            });
        })
        .then(data => {
            if(data.success) {
                const action = id ? 'Cập nhật' : 'Thêm mới';
                // Lưu trạng thái toast để sau khi tải lại trang sẽ hiển thị thông báo
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

    /**
     * MỞ FORM CẬP NHẬT ĐỊA CHỈ (EDIT ADDRESS LOADER)
     * Đổ dữ liệu cũ vào form.
     * Gọi API lấy Tỉnh thành, Quận huyện, Phường xã và tự động chọn đúng Option cũ
     * nhờ hàm `normalize` lọc dấu tiếng Việt để so khớp tương đối chính xác.
     */
    function editAddress(id, city, district, ward, street, name, type, isDefault) {
        document.getElementById('addressModalTitle').innerText = 'Cập nhật địa chỉ';
        document.getElementById('addressSubmitBtn').innerText = 'Cập nhật';
        document.getElementById('addrId').value = id;
        document.getElementById('addrStreet').value = street;
        document.getElementById('addrName').value = name;
        document.getElementById('addrIsDefault').checked = isDefault;
        document.querySelector(`input[name="addrType"][value="${type}"]`).checked = true;

        openAddressModal();

        setTimeout(() => {
            const citySelect = document.getElementById('addrCity');
            
            // Hàm chuẩn hóa chuỗi tiếng Việt để so sánh chuỗi tương đối chính xác giữa các locale ngôn ngữ
            const normalize = (name) => {
                if (!name) return "";
                return name.toLowerCase()
                           .replace(/^(tỉnh|thành\s+phố|tp\.)\s+/i, "")
                           .replace(/^(quận|huyện|thị\s+xã)\s+/i, "")
                           .replace(/^(phường|xã|thị\s+trấn)\s+/i, "")
                           .replace(/thừa thiên huế/i, "huế")
                           .replace(/\s+(city|province|district|town|ward|commune)$/i, "")
                           .normalize("NFD").replace(/[\u0300-\u036f]/g, "") // Khử các ký tự dấu tiếng Việt
                           .trim();
            };

            const normalizedCity = normalize(city);

            // Tìm và select Tỉnh/Thành
            for(let i=0; i<citySelect.options.length; i++) {
                if(normalize(citySelect.options[i].value) === normalizedCity) { 
                    citySelect.selectedIndex = i; 
                    break; 
                }
            }
            citySelect.dispatchEvent(new Event('change'));
            
            // Tìm và select Quận/Huyện sau 500ms
            setTimeout(() => {
                const distSelect = document.getElementById('addrDistrict');
                const normalizedDist = normalize(district);
                for(let i=0; i<distSelect.options.length; i++) {
                    if(normalize(distSelect.options[i].value) === normalizedDist) { distSelect.selectedIndex = i; break; }
                }
                distSelect.dispatchEvent(new Event('change'));
                
                // Tìm và select Phường/Xã sau 500ms nữa
                setTimeout(() => {
                    const wardSelect = document.getElementById('addrWard');
                    const normalizedWard = normalize(ward);
                    for(let i=0; i<wardSelect.options.length; i++) {
                        if(normalize(wardSelect.options[i].value) === normalizedWard) { wardSelect.selectedIndex = i; break; }
                    }
                }, 500);
            }, 500);
        }, 500);
    }

    // ============================================================
    // CÁC HÀNH ĐỘNG AJAX TƯƠNG TÁC KHÁC TRÊN PROFILE
    // ============================================================

    // Copy mã voucher vào bộ nhớ tạm Clipboard kèm phản hồi giao diện
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

    // Thêm sản phẩm vào giỏ hàng qua AJAX POST
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
            if(data.status === 'success' || data.success) {
                showToast('Thành công', data.message || 'Đã thêm sản phẩm vào giỏ hàng!', 'success');
                const badge = document.getElementById('headerCartBadge');
                if(badge && data.cart_count !== undefined) {
                    badge.innerText = data.cart_count;
                    badge.style.display = data.cart_count > 0 ? 'block' : 'none';
                }
            } else {
                showToast('Lỗi', data.message || 'Không thể thêm vào giỏ hàng.', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
        });
    }

    // Cập nhật số lượng sản phẩm hiển thị trên tab yêu thích
    function updateWishlistCount(count) {
        const countEl = document.getElementById('wishlist-title-count');
        if (countEl) countEl.textContent = count;
    }

    // Xóa một sản phẩm khỏi danh sách yêu thích qua AJAX DELETE
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
                closeConfirmModal();
                if(data.success) {
                    showToast('Đã xóa', 'Đã bỏ sản phẩm khỏi danh sách yêu thích.', 'success');
                    const item = document.getElementById(`wishlist-item-${id}`);
                    if(item) {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            item.remove();
                            const remaining = document.querySelectorAll('.wishlist-item').length;
                            updateWishlistCount(remaining);
                            if(remaining === 0) {
                                const clearBtnWrapper = document.getElementById('wishlist-clear-btn-wrapper');
                                if (clearBtnWrapper) clearBtnWrapper.innerHTML = '';
                                const contentArea = document.getElementById('wishlist-content-area');
                                if (contentArea) {
                                    contentArea.innerHTML = `
                                        <div class="dash-empty" style="padding: 50px 0;">
                                            <i class="fa-solid fa-heart-crack" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                                            <p>Danh sách yêu thích của bạn đang trống.</p>
                                            <a href="{{ route('home') }}" class="btn-outline">Khám phá sản phẩm</a>
                                        </div>
                                    `;
                                }
                            }
                        }, 300);
                    }
                } else {
                    showToast('Lỗi', data.error || 'Không thể thực hiện thao tác này.', 'error');
                }
            })
            .catch(err => {
                closeConfirmModal();
                showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
            });
        });
    }

    // Xóa toàn bộ danh sách sản phẩm yêu thích (Clear Wishlist)
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
                closeConfirmModal();
                if(data.success) {
                    showToast('Thành công', 'Đã xóa toàn bộ danh sách yêu thích.', 'success');
                    const items = document.querySelectorAll('.wishlist-item');
                    items.forEach(item => {
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.8)';
                    });
                    setTimeout(() => {
                        items.forEach(item => item.remove());
                        updateWishlistCount(0);
                        const clearBtnWrapper = document.getElementById('wishlist-clear-btn-wrapper');
                        if (clearBtnWrapper) clearBtnWrapper.innerHTML = '';
                        const contentArea = document.getElementById('wishlist-content-area');
                        if (contentArea) {
                            contentArea.innerHTML = `
                                <div class="dash-empty" style="padding: 50px 0;">
                                    <i class="fa-solid fa-heart-crack" style="font-size: 50px; color: #ddd; margin-bottom: 15px;"></i>
                                    <p>Danh sách yêu thích của bạn đang trống.</p>
                                    <a href="{{ route('home') }}" class="btn-outline">Khám phá sản phẩm</a>
                                </div>
                            `;
                        }
                    }, 300);
                } else {
                    showToast('Lỗi', data.error || 'Không thể xóa danh sách lúc này.', 'error');
                }
            })
            .catch(err => {
                closeConfirmModal();
                showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
            });
        });
    }

    // Thêm nhanh vào giỏ hàng từ trang Wishlist kèm feedback loading trên nút
    function addToCartFromWishlist(btn, productId) {
        const originalHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Đang thêm...';

        fetch('{{ route("cart.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success' || data.success) {
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Đã thêm!';
                showToast('Thành công', data.message || 'Đã thêm vào giỏ hàng!', 'success');
                setTimeout(() => { btn.innerHTML = originalHTML; }, 2000);
            } else {
                btn.innerHTML = originalHTML;
                showToast('Lỗi', data.message || 'Không thể thêm vào giỏ hàng.', 'error');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHTML;
            showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
        });
    }

    // Xóa một địa chỉ khỏi sổ địa chỉ giao nhận qua AJAX DELETE
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
                closeConfirmModal();
                if(data.success) {
                    sessionStorage.setItem('profile_toast', JSON.stringify({
                        title: 'Đã xóa',
                        msg: 'Địa chỉ đã được gỡ khỏi sổ địa chỉ của bạn.',
                        type: 'success'
                    }));
                    window.location.href = '?tab=info-tab';
                } else {
                    showToast('Lỗi', 'Không thể xóa địa chỉ này.', 'error');
                }
            })
            .catch(err => {
                closeConfirmModal();
                showToast('Lỗi', 'Lỗi kết nối máy chủ.', 'error');
            });
        });
    }

    // ============================================================
    // TIẾN TRÌNH THEO DÕI SỬA CHỮA / BẢO HÀNH (REPAIR TRACKING SYSTEM)
    // ============================================================
    function openRepairModal() {
        document.getElementById('repairModal').classList.add('active');
    }
    function closeRepairModal() {
        document.getElementById('repairModal').classList.remove('active');
        document.getElementById('repairRegistrationForm').reset();
        removeRepairImage();
        document.getElementById('aiDiagnosisReport').style.display = 'none';
        document.getElementById('hidAiDiagnosed').value = '0';
        document.getElementById('hidAiDiagnoseToken').value = '';
        
        // Reset readonly attribute from IMEI input
        const imeiInput = document.getElementById('repImeiSerial');
        if (imeiInput) {
            imeiInput.removeAttribute('readonly');
            imeiInput.style.background = '';
        }
    }
    function closeTrackingModal() {
        document.getElementById('trackingModal').classList.remove('active');
    }

    // Xử lý ảnh chụp xem trước
    function previewRepairImage(input) {
        const previewWrap = document.getElementById('repairImagePreviewWrap');
        const previewImg = document.getElementById('repairImagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewWrap.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeRepairImage() {
        const fileInput = document.getElementById('repDeviceImage');
        const previewWrap = document.getElementById('repairImagePreviewWrap');
        const previewImg = document.getElementById('repairImagePreview');
        if (fileInput) fileInput.value = '';
        if (previewImg) previewImg.src = '';
        if (previewWrap) previewWrap.style.display = 'none';
    }

    // AJAX Gọi AI chẩn đoán lỗi & dispatch
    document.addEventListener('DOMContentLoaded', function() {
        const btnAIDiagnose = document.getElementById('btnAIDiagnose');
        if (btnAIDiagnose) {
            btnAIDiagnose.addEventListener('click', function() {
                const issueDescInput = document.getElementById('repIssueDesc');
                const issueDesc = issueDescInput.value.trim();

                if (!issueDesc || issueDesc.length < 10) {
                    showToast('Cảnh báo', 'Vui lòng điền mô tả lỗi chi tiết hơn (tối thiểu 10 ký tự) để AI phân tích chính xác.', 'warning');
                    issueDescInput.focus();
                    return;
                }

                const originalHTML = btnAIDiagnose.innerHTML;
                btnAIDiagnose.disabled = true;
                btnAIDiagnose.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang phân tích và chẩn đoán lỗi...';

                const formData = new FormData();
                formData.append('issue_desc', issueDesc);
                
                const fileInput = document.getElementById('repDeviceImage');
                if (fileInput && fileInput.files && fileInput.files[0]) {
                    formData.append('device_image', fileInput.files[0]);
                }

                fetch('{{ route("profile.repair-tickets.ai-diagnose") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => {
                    if (!res.ok) {
                        return res.json().then(err => { throw new Error(err.error || err.message || 'Lỗi hệ thống'); });
                    }
                    return res.json();
                })
                .then(data => {
                    btnAIDiagnose.disabled = false;
                    btnAIDiagnose.innerHTML = originalHTML;

                    document.getElementById('aiDiagnosisReport').style.display = 'block';
                    document.getElementById('aiFaultType').innerText = data.ai_fault_type;
                    document.getElementById('aiReplacementParts').innerText = data.ai_replacement_parts;
                    
                    const costMin = new Intl.NumberFormat('vi-VN').format(data.ai_estimated_cost_min);
                    const costMax = new Intl.NumberFormat('vi-VN').format(data.ai_estimated_cost_max);
                    document.getElementById('aiEstimatedCostRange').innerText = `${costMin} đ - ${costMax} đ`;

                    const compBadge = document.getElementById('aiComplexityBadge');
                    compBadge.innerText = data.ai_complexity_level;
                    if (data.ai_complexity_level === 'Dễ') {
                        compBadge.style.background = '#dcfce7';
                        compBadge.style.color = '#15803d';
                    } else if (data.ai_complexity_level === 'Khó') {
                        compBadge.style.background = '#fee2e2';
                        compBadge.style.color = '#b91c1c';
                    } else {
                        compBadge.style.background = '#f3e8ff';
                        compBadge.style.color = '#7c3aed';
                    }

                    const riskList = document.getElementById('aiRiskWarningsList');
                    riskList.innerHTML = '';
                    if (data.ai_risk_warnings && data.ai_risk_warnings.length > 0) {
                        data.ai_risk_warnings.forEach(risk => {
                            const li = document.createElement('li');
                            li.innerText = risk;
                            riskList.appendChild(li);
                        });
                        document.getElementById('aiRiskWarningsBox').style.display = 'block';
                    } else {
                        document.getElementById('aiRiskWarningsBox').style.display = 'none';
                    }

                    const causeList = document.getElementById('aiProbableCausesList');
                    causeList.innerHTML = '';
                    if (data.ai_probable_causes && data.ai_probable_causes.length > 0) {
                        data.ai_probable_causes.forEach(cause => {
                            const li = document.createElement('li');
                            li.innerText = cause;
                            causeList.appendChild(li);
                        });
                    }

                    document.getElementById('aiAssignedTech').innerText = data.technician_name;
                    document.getElementById('aiDispatchReason').innerText = data.ai_dispatch_reason;

                    document.getElementById('hidAiDiagnosed').value = '1';
                    document.getElementById('hidAiDiagnoseToken').value = data.diagnose_token;
                    document.getElementById('hidAiFaultType').value = data.ai_fault_type;
                    document.getElementById('hidAiProbableCauses').value = JSON.stringify(data.ai_probable_causes);
                    document.getElementById('hidAiRiskWarnings').value = JSON.stringify(data.ai_risk_warnings);
                    document.getElementById('hidAiReplacementParts').value = data.ai_replacement_parts;
                    document.getElementById('hidAiEstimatedCostMin').value = data.ai_estimated_cost_min;
                    document.getElementById('hidAiEstimatedCostMax').value = data.ai_estimated_cost_max;
                    document.getElementById('hidAiComplexityLevel').value = data.ai_complexity_level;
                    document.getElementById('hidAiRecommendedSkills').value = JSON.stringify(data.ai_recommended_skills);
                    document.getElementById('hidAiDispatchReason').value = data.ai_dispatch_reason;
                    document.getElementById('hidAssignedTechnicianId').value = data.assigned_technician_id;

                    showToast('Thành công', 'AI đã chẩn đoán lỗi thiết bị và đề xuất kỹ thuật viên phù hợp!', 'success');
                })
                .catch(err => {
                    btnAIDiagnose.disabled = false;
                    btnAIDiagnose.innerHTML = originalHTML;
                    showToast('Lỗi', err.message || 'Lỗi kết nối máy chủ khi gọi AI chẩn đoán.', 'error');
                });
            });
        }
    });

    /**
     * ĐỌC VÀ HIỂN THỊ TIẾN TRÌNH SỬA CHỮA THIẾT BỊ LÊN TRỤC BẬC THANG (STEPPER PROGRESS)
     * Đọc JSON dữ liệu phiếu sửa chữa đính trên nút.
     * Điền thông tin mã phiếu, số IMEI, lỗi mô tả, ngày hẹn và kỹ thuật viên nhận máy.
     * So sánh trạng thái ('Received', 'Checking', 'Under_Repair', 'Waiting_Parts', 'Done') 
     * để gán các class active/completed cho các mốc tương ứng trên sơ đồ Stepper.
     */
    function viewProgress(btn) {
        const ticket = JSON.parse(btn.getAttribute('data-ticket'));
        
        document.getElementById('track-id').innerText = ticket.ticket_id;
        document.getElementById('track-imei').innerText = ticket.imei_serial;
        document.getElementById('track-desc').innerText = ticket.issue_desc;
        
        // Cập nhật ảnh chụp thiết bị trong tracking modal
        const trackImgContainer = document.getElementById('track-image-container');
        const trackImg = document.getElementById('track-device-image');
        if (ticket.device_image) {
            trackImg.src = '/' + ticket.device_image;
            trackImgContainer.style.display = 'block';
        } else {
            trackImg.src = '';
            trackImgContainer.style.display = 'none';
        }

        // Cập nhật chẩn đoán AI trong tracking modal
        const trackAiReport = document.getElementById('track-ai-report');
        if (ticket.ai_diagnosed) {
            document.getElementById('track-ai-fault-type').innerText = ticket.ai_fault_type || 'Phần cứng';
            document.getElementById('track-ai-parts').innerText = ticket.ai_replacement_parts || 'Cần kiểm tra trực tiếp';
            
            const costRange = (ticket.ai_estimated_cost_min && ticket.ai_estimated_cost_max)
                ? `${new Intl.NumberFormat('vi-VN').format(ticket.ai_estimated_cost_min)} đ - ${new Intl.NumberFormat('vi-VN').format(ticket.ai_estimated_cost_max)} đ`
                : 'Chưa ước lượng';
            document.getElementById('track-ai-cost').innerText = costRange;

            const warningsList = document.getElementById('track-ai-warnings');
            warningsList.innerHTML = '';
            const warnings = typeof ticket.ai_risk_warnings === 'string' ? JSON.parse(ticket.ai_risk_warnings) : ticket.ai_risk_warnings;
            if (warnings && warnings.length > 0) {
                warnings.forEach(w => {
                    const li = document.createElement('li');
                    li.innerText = w;
                    warningsList.appendChild(li);
                });
                document.getElementById('track-ai-warnings-box').style.display = 'block';
            } else {
                document.getElementById('track-ai-warnings-box').style.display = 'none';
            }
            trackAiReport.style.display = 'block';
        } else {
            trackAiReport.style.display = 'none';
        }
        
        const scheduleDate = new Date(ticket.schedule_date);
        const day = String(scheduleDate.getDate()).padStart(2, '0');
        const month = String(scheduleDate.getMonth() + 1).padStart(2, '0');
        const year = scheduleDate.getFullYear();
        const formattedDate = `${day}/${month}/${year}`;
        
        // Cập nhật nhãn ngày nhận/ngày trả tương ứng theo trạng thái
        const dateLabel = document.getElementById('track-date-label');
        if (['Under_Repair', 'Waiting_Parts', 'Done'].includes(ticket.status)) {
            dateLabel.innerText = @json(__('ui.repair_date_return_label'));
        } else {
            dateLabel.innerText = @json(__('ui.repair_date_received_label'));
        }
        document.getElementById('track-date').innerText = formattedDate;
        
        let techName = ticket.technician ? ticket.technician.full_name : @json(__('ui.repair_tech_assigning'));
        if (techName === 'Quản Trị Viên') {
            techName = @json(app()->getLocale() === 'en' ? 'Administrator' : 'Quản Trị Viên');
        }
        document.getElementById('track-tech').innerText = techName;
        
        // Reset sạch các trạng thái của các bước mốc trước đó
        const steps = ['step-received', 'step-checking', 'step-repairing', 'step-done'];
        steps.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('active', 'completed');
            }
        });
        
        // Khôi phục mô tả gốc của các mốc
        document.getElementById('step-received-desc').innerHTML = @json(__('ui.repair_step_received_desc'));
        document.getElementById('step-checking-desc').innerHTML = @json(__('ui.repair_step_checking_desc'));
        document.getElementById('step-repairing-desc').innerHTML = @json(__('ui.repair_step_repairing_desc'));
        document.getElementById('step-done-desc').innerHTML = @json(__('ui.repair_step_done_desc'));
        
        const status = ticket.status;
        if (status === 'Received') {
            document.getElementById('step-received').classList.add('active');
        } else if (status === 'Checking') {
            document.getElementById('step-received').classList.add('completed');
            document.getElementById('step-checking').classList.add('active');
            
            // Hiện chi phí dự kiến nếu có trong bước báo giá
            const costHtml = ticket.estimated_cost > 0 
                ? `<div style="margin-top:5px; color:#0369a1; font-weight:600;"><i class="fa-solid fa-calculator"></i> ` + @json(__('ui.repair_step_checking_cost')) + ` ${new Intl.NumberFormat('vi-VN').format(ticket.estimated_cost)} đ</div>`
                : '';
            document.getElementById('step-checking-desc').innerHTML = @json(__('ui.repair_step_checking_progress')) + costHtml;
        } else if (status === 'Under_Repair' || status === 'Waiting_Parts') {
            document.getElementById('step-received').classList.add('completed');
            document.getElementById('step-checking').classList.add('completed');
            document.getElementById('step-repairing').classList.add('active');
            
            if (status === 'Waiting_Parts') {
                document.getElementById('step-repairing-desc').innerHTML = @json(__('ui.repair_step_repairing_waiting'));
            } else {
                document.getElementById('step-repairing-desc').innerHTML = @json(__('ui.repair_step_repairing_tech')).replace(':name', techName);
            }
        } else if (status === 'Done') {
            document.getElementById('step-received').classList.add('completed');
            document.getElementById('step-checking').classList.add('completed');
            document.getElementById('step-repairing').classList.add('completed');
            document.getElementById('step-done').classList.add('completed');
            
            let doneDetails = '';
            if (ticket.service_name) {
                doneDetails += `<div style="margin-top: 5px; font-weight: 600; color: #1e293b;">` + @json(__('ui.repair_step_done_service')) + ` ${ticket.service_name}</div>`;
            }
            if (ticket.service_fee > 0) {
                doneDetails += `<div style="margin-top: 2px; color: #166534; font-weight: 700;">` + @json(__('ui.repair_step_done_fee')) + ` ${new Intl.NumberFormat('vi-VN').format(ticket.service_fee)} đ</div>`;
            }
            if (ticket.invoice_no) {
                doneDetails += `<div style="margin-top: 2px; font-size:11px; color:#64748b;">` + @json(__('ui.repair_step_done_invoice')) + ` ${ticket.invoice_no}</div>`;
            }
            document.getElementById('step-done-desc').innerHTML = @json(__('ui.repair_step_done_delivered')) + doneDetails;
        }
        
        document.getElementById('trackingModal').classList.add('active');
    }

    // ============================================================
    // KHỞI CHẠY KHI TẢI XONG TRANG (DOM CONTENT LOADED INITIALIZATIONS)
    // ============================================================
    document.addEventListener('DOMContentLoaded', function() {
        // Kiểm tra xem dữ liệu cập nhật profile có gì thay đổi so với DB không trước khi submit
        const editForm = document.getElementById('editProfileForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const currentFullName = this.querySelector('[name="full_name"]').value.trim();
                const currentGender = this.querySelector('[name="gender"]').value;
                const currentDob = this.querySelector('[name="dob"]').value;
                const currentPhoneNumber = this.querySelector('[name="phone_number"]').value.trim();
                const currentAddress = this.querySelector('[name="address"]').value.trim();

                const norm = val => val || '';

                if (currentFullName === norm(originalProfileData.full_name) &&
                    currentGender === norm(originalProfileData.gender) &&
                    currentDob === norm(originalProfileData.dob) &&
                    currentPhoneNumber === norm(originalProfileData.phone_number) &&
                    currentAddress === norm(originalProfileData.address)) {
                    
                    e.preventDefault();
                    showToast('Thông báo', 'Không có thông tin nào thay đổi so với dữ liệu cũ!', 'warning');
                }
            });
        }

        // Tự động kiểm tra hiển thị Toast thông báo lưu từ sessionStorage (nếu có)
        const toastData = sessionStorage.getItem('profile_toast');
        if (toastData) {
            const data = JSON.parse(toastData);
            showToast(data.title, data.msg, data.type);
            sessionStorage.removeItem('profile_toast');
        }

        // Tự động chuyển đến Tab tương ứng dựa trên tham số 'tab' trên thanh địa chỉ URL
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        
        if (tab) {
            let index = 0;
            switch(tab) {
                case 'orders-tab': index = 1; break;
                case 'info-tab': index = 2; break;
                case 'wishlist-tab': index = 3; break;
                case 'repair-tab': index = 4; break;
                case 'promo-tab': index = 5; break;
                case 'login-history-tab': index = 6; break;
            }
            const navItems = document.querySelectorAll('.profile-nav-item');
            if (navItems[index]) {
                switchTab(tab, navItems[index]);
            }
        }

        const action = urlParams.get('action');
        if (action === 'repair') {
            const imei = urlParams.get('imei');
            const product = urlParams.get('product');
            if (imei) {
                triggerProfileRepairModal(imei, product || '');
            }
        }

        // Tự động mở lại modal đăng ký bảo hành/sửa chữa nếu có lỗi validation từ Server Laravel trả về
        @if($errors->has('customer_name') || $errors->has('customer_phone') || $errors->has('customer_email') || $errors->has('imei_serial') || $errors->has('issue_desc') || $errors->has('schedule_date'))
            openRepairModal();
        @endif
    });

    // ============================================================
    // DỊCH VỤ HẬU MÃI: ĐỔI TRẢ & BẢO HÀNH TRONG TAB ĐƠN HÀNG (POST-PURCHASE TAB INTEGRATION)
    // ============================================================
    function toggleOrderDetails(orderId) {
        const detailsRow = document.getElementById('details-' + orderId);
        const caretIcon = document.getElementById('icon-' + orderId);
        
        if (detailsRow.style.display === 'none') {
            detailsRow.style.display = 'table-row';
            caretIcon.classList.remove('fa-chevron-down');
            caretIcon.classList.add('fa-chevron-up');
        } else {
            detailsRow.style.display = 'none';
            caretIcon.classList.remove('fa-chevron-up');
            caretIcon.classList.add('fa-chevron-down');
        }
    }

    function triggerProfileRepairModal(imei, productName) {
        // Đóng modal sửa chữa nếu đang mở, reset form
        closeRepairModal();
        
        // Điền IMEI/Serial
        const imeiInput = document.getElementById('repImeiSerial');
        if (imeiInput) {
            imeiInput.value = imei;
            imeiInput.setAttribute('readonly', 'readonly');
            imeiInput.style.background = '#f8fafc';
        }
        
        // Gợi ý mô tả lỗi gắn liền với sản phẩm
        const descInput = document.getElementById('repIssueDesc');
        if (descInput) {
            descInput.value = "Yêu cầu sửa chữa/bảo hành cho sản phẩm: " + productName + "\nTình trạng lỗi chi tiết: ";
        }
        
        // Mở modal sửa chữa
        openRepairModal();
    }

    function triggerProfileClaimModal(imei, productName, type) {
        document.getElementById('modalImei').value = imei;
        document.getElementById('modalProductName').value = productName;
        
        // Cập nhật hiển thị tinh gọn
        document.getElementById('modalProductNameDisplay').textContent = productName;
        document.getElementById('modalImeiDisplay').textContent = imei;
        
        // Cấu hình động các tùy chọn loại yêu cầu và giao diện
        const claimTypeSelect = document.getElementById('modalClaimType');
        const header = document.getElementById('claimModalHeader');
        const title = document.getElementById('claimModalTitle');
        const submitBtn = document.getElementById('btnSubmitClaim');
        
        if (type === 'warranty') {
            claimTypeSelect.innerHTML = '<option value="warranty">Bảo hành sửa chữa (Miễn phí)</option>';
            header.style.background = '#0046ab';
            title.textContent = 'Gửi yêu cầu bảo hành chính hãng';
            submitBtn.style.background = '#0046ab';
            submitBtn.textContent = 'Gửi yêu cầu bảo hành';
        } else {
            claimTypeSelect.innerHTML = `
                <option value="return">Đổi trả hàng hoàn tiền</option>
                <option value="exchange">Đổi máy mới/khách</option>
            `;
            header.style.background = '#f59e0b';
            title.textContent = 'Gửi yêu cầu đổi trả sản phẩm';
            submitBtn.style.background = '#f59e0b';
            submitBtn.textContent = 'Gửi yêu cầu đổi trả';
        }
        claimTypeSelect.value = type;
        
        document.getElementById('modalReason').value = '';

        const refMethod = document.getElementById('modalRefundMethod');
        if (refMethod) refMethod.value = 'bank_transfer';
        
        // Cập nhật hiển thị ngân hàng
        toggleBankFields();

        document.getElementById('claimModal').classList.add('active');
    }

    function closeProfileClaimModal() {
        document.getElementById('claimModal').classList.remove('active');
        document.getElementById('claimForm').reset();
    }

    function submitProfileClaim(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitClaim');
        const oldText = btn.innerHTML;
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...';
        
        const mediaInput = document.getElementById('modalMediaFile');
        if (mediaInput && mediaInput.files.length > 0) {
            const file = mediaInput.files[0];
            if (file.size > 20 * 1024 * 1024) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tệp quá lớn',
                    text: 'Dung lượng hình ảnh hoặc video minh họa không được vượt quá 20MB.',
                    confirmButtonColor: '#ef4444'
                });
                btn.disabled = false;
                btn.innerHTML = oldText;
                return;
            }
        }
        
        const formElement = document.getElementById('claimForm');
        const formData = new FormData(formElement);
        
        fetch('/warranty/claim', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(r => r.json().then(data => ({ status: r.status, body: data })))
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = oldText;
            
            if (res.status !== 200) {
                let errorMsg = res.body.message || 'Đã có lỗi xảy ra.';
                if (res.status === 419 || errorMsg === 'CSRF token mismatch.') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Phiên làm việc hết hạn',
                        text: 'Phiên làm việc của bạn đã hết hạn. Vui lòng tải lại trang để tiếp tục.',
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Tải lại trang'
                    }).then(() => {
                        window.location.reload();
                    });
                    return;
                }
                if (res.body.errors) {
                    errorMsg = Object.values(res.body.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi gửi yêu cầu',
                    html: errorMsg,
                    confirmButtonColor: '#ef4444'
                });
            } else {
                closeProfileClaimModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: res.body.message,
                    confirmButtonColor: '#0046ab'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = oldText;
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại.',
                confirmButtonColor: '#ef4444'
            });
        });
    }

    function toggleBankFields() {
        const sel = document.getElementById('modalClaimType');
        const refundMethodSection = document.getElementById('refundMethodSection');
        const bankSection = document.getElementById('bankDetailsSection');
        if (!sel) return;

        const refundMethodSelect = document.getElementById('modalRefundMethod');
        const isReturn = (sel.value === 'return');

        if (refundMethodSection) {
            refundMethodSection.style.display = isReturn ? 'flex' : 'none';
        }

        if (bankSection) {
            const inputs = bankSection.querySelectorAll('input');
            const isBankTransfer = isReturn && (refundMethodSelect ? refundMethodSelect.value === 'bank_transfer' : true);

            if (isBankTransfer) {
                bankSection.style.display = 'flex';
                inputs.forEach(input => input.setAttribute('required', 'true'));
            } else {
                bankSection.style.display = 'none';
                inputs.forEach(input => {
                    input.removeAttribute('required');
                    input.value = '';
                });
            }
        }
    }
    document.getElementById('modalClaimType').addEventListener('change', toggleBankFields);
    const refMethodEl = document.getElementById('modalRefundMethod');
    if (refMethodEl) {
        refMethodEl.addEventListener('change', toggleBankFields);
    }
</script>
@endpush
