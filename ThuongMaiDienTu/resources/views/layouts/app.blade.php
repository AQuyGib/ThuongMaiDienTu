<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hệ thống bán lẻ điện thoại di động, máy tính')</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="Hệ thống bán lẻ điện thoại, laptop, phụ kiện chính hãng, giá tốt nhất thị trường. Mua trả góp 0%, giao hàng nhanh toàn quốc.">
    <meta name="keywords" content="điện thoại, laptop, tablet, phụ kiện công nghệ, apple, samsung">
    <meta name="robots" content="index, follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.tsx', 'resources/js/compare.js'])
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #0046ab; /* Trả lại màu xanh dương chủ đạo */
            --primary-gradient: linear-gradient(135deg, #0046ab 0%, #0061f2 100%);
            --secondary-color: #d70018; /* Màu đỏ làm màu nhấn (accent) */
            --bg-color: #f8fafc;
            --text-color: #1e293b;
            --text-muted: #64748b;
            --white: #ffffff;
            --border-color: #e2e8f0;
            --glass: rgba(255, 255, 255, 0.7);
            --shadow-premium: 0 10px 40px -10px rgba(0, 70, 171, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(215, 0, 24, 0.02) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 70, 171, 0.02) 0px, transparent 50%);
            background-attachment: fixed;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* ============================
           TOP BAR
           ============================ */
        .top-bar {
            background-color: var(--hover-blue);
            color: var(--white);
            font-size: 12px;
            padding: 6px 0;
        }

        .top-bar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar-left,
        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .top-bar span {
            cursor: pointer;
            padding: 2px 8px;
            border-right: 1px solid rgba(255,255,255,0.3);
            transition: opacity 0.2s;
            white-space: nowrap;
        }

        .top-bar span:last-child {
            border-right: none;
        }

        .top-bar span:hover {
            opacity: 0.8;
        }

        .top-bar span i {
            margin-right: 4px;
        }

        /* ============================
           HEADER MAIN
           ============================ */
        .header-main {
            background-color: var(--primary-color);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        .header-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo {
            font-size: 22px;
            font-weight: 800;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .logo span {
            color: #00d2ff;
        }

        /* Nút Danh mục */
        .header-category-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 16px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            color: var(--white);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .header-category-btn:hover {
            background: rgba(255,255,255,0.25);
        }

        /* Nút Tỉnh thành */
        .header-province-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 10px 14px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            color: var(--white);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .header-province-btn:hover {
            background: rgba(255,255,255,0.25);
        }

        .search-bar {
            flex: 1;
            max-width: 420px;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 11px 45px 11px 18px;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .search-bar button {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 16px;
            cursor: pointer;
        }

        /* Search Suggestions */
        .search-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--white);
            border-radius: 0 0 12px 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            z-index: 1001;
            margin-top: 5px;
            display: none;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .search-suggestions.show {
            display: block;
        }

        .suggestion-group {
            padding: 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .suggestion-group:last-child {
            border-bottom: none;
        }

        .suggestion-header {
            padding: 8px 15px;
            font-size: 11px;
            font-weight: 700;
            color: #999;
            text-transform: uppercase;
            background: #f8f9fa;
        }

        .suggestion-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 15px;
            cursor: pointer;
            transition: 0.2s;
            border-bottom: 1px solid #f9f9f9;
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-item:hover {
            background: #f0f7ff;
        }

        .suggestion-item img {
            width: 40px;
            height: 40px;
            object-fit: contain;
            border-radius: 4px;
            background: #fff;
            border: 1px solid #eee;
        }

        .suggestion-info {
            flex: 1;
        }

        .suggestion-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-color);
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .suggestion-price {
            font-size: 12px;
            font-weight: 700;
            color: var(--secondary-color);
        }

        .suggestion-cat {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            font-size: 13px;
            color: #444;
            transition: 0.2s;
            font-weight: 500;
        }

        .suggestion-cat:hover {
            background: #f0f7ff;
            color: var(--primary-color);
        }

        .suggestion-cat i {
            color: #aaa;
            font-size: 12px;
        }

        .no-results {
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #999;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--white);
            font-size: 11px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 8px;
            transition: 0.2s;
            white-space: nowrap;
        }

        .action-item i {
            font-size: 18px;
            margin-bottom: 3px;
        }

        .action-item:hover {
            background-color: rgba(255,255,255,0.15);
        }

        /* User Dropdown */
        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--white);
            min-width: 150px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 8px 0;
            z-index: 1001;
            border: 1px solid var(--border-color);
        }

        .action-item.group:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a, .user-dropdown button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 8px 15px;
            color: var(--text-color);
            background: none;
            border: none;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: 0.2s;
        }

        .user-dropdown a:hover, .user-dropdown button:hover {
            background: #f0f7ff;
            color: var(--primary-color);
        }

        /* ============================
           MEGA MENU
           ============================ */
        .mega-menu-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 998;
        }

        .mega-menu-overlay.show {
            display: block;
        }

        .mega-menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 999;
            padding-top: 95px; /* dưới header */
            pointer-events: none;
        }

        .mega-menu.show {
            display: block;
        }

        .mega-menu-inner {
            pointer-events: auto;
            display: flex;
            background: var(--white);
            border-radius: 0 0 12px 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-height: calc(100vh - 120px);
            overflow: hidden;
            animation: megaSlideDown 0.25s ease;
        }

        @keyframes megaSlideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Cột trái mega menu */
        .mega-col-left {
            width: 230px;
            background: #f8f9fa;
            border-right: 1px solid var(--border-color);
            padding: 8px 0;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .mega-cat-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 16px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-color);
            cursor: pointer;
            transition: 0.15s;
            position: relative;
        }

        .mega-cat-item i:first-child {
            width: 18px;
            text-align: center;
            color: #888;
            font-size: 15px;
        }

        .mega-cat-item .mega-arrow {
            margin-left: auto;
            font-size: 10px;
            color: #bbb;
        }

        .mega-cat-item:hover,
        .mega-cat-item.active {
            background: var(--white);
            color: var(--primary-color);
        }

        .mega-cat-item:hover i:first-child,
        .mega-cat-item.active i:first-child {
            color: var(--primary-color);
        }

        /* Cột phải mega menu */
        .mega-col-right {
            flex: 1;
            padding: 20px 25px;
            overflow-y: auto;
        }

        .mega-detail-panel {
            display: none;
        }

        .mega-detail-panel.active {
            display: block;
        }

        .mega-section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .mega-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .mega-tag {
            display: inline-block;
            padding: 6px 14px;
            background: #f3f4f6;
            border: 1px solid var(--border-color);
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-color);
            transition: 0.2s;
        }

        .mega-tag:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: #f0f7ff;
        }

        .mega-tag.see-all {
            color: var(--primary-color);
            font-weight: 600;
            border-color: var(--primary-color);
            background: #f0f7ff;
        }

        /* ============================
           MODAL TỈNH THÀNH
           ============================ */
        .province-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(3px);
        }

        .province-modal-overlay.show {
            display: flex;
        }

        .province-modal {
            background: var(--white);
            border-radius: 14px;
            width: 580px;
            max-width: 95vw;
            max-height: 80vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            animation: modalPop 0.25s ease;
            display: flex;
            flex-direction: column;
        }

        @keyframes modalPop {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .province-modal-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .province-search-box {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            transition: 0.2s;
        }

        .province-search-box:focus-within {
            border-color: var(--primary-color);
        }

        .province-search-box i {
            color: #999;
        }

        .province-search-box input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .province-close-btn {
            padding: 10px 18px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .province-close-btn:hover {
            background: var(--primary-dark);
        }

        .province-hint {
            padding: 10px 20px;
            font-size: 13px;
            color: #888;
        }

        .province-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 0 20px 20px;
            gap: 0;
            overflow-y: auto;
            max-height: 450px;
        }

        .province-item {
            padding: 11px 14px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 6px;
            transition: 0.15s;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .province-item:hover {
            background: #f0f7ff;
            color: var(--primary-color);
        }

        .province-item.selected {
            color: var(--primary-color);
            font-weight: 600;
        }

        .province-item.selected i {
            color: var(--primary-color);
        }

        .province-item.hidden {
            display: none;
        }

        /* ============================
           FOOTER
           ============================ */
        .footer {
            background-color: var(--white);
            padding: 40px 0;
            margin-top: 50px;
            border-top: 1px solid var(--border-color);
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
        }

        .footer-col h4 {
            font-size: 16px;
            margin-bottom: 15px;
            color: var(--text-color);
            text-transform: uppercase;
        }

        .footer-col ul li {
            margin-bottom: 12px;
            font-size: 13px;
            color: #555;
            transition: 0.2s;
        }

        .footer-col ul li a:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-icons i {
            font-size: 28px;
            color: var(--primary-color);
            cursor: pointer;
            transition: 0.3s;
        }

        .social-icons i:hover {
            transform: translateY(-3px);
            color: var(--primary-dark);
        }
        /* ============================
           FLOATING COMPARE BAR
           ============================ */
        .compare-bar {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 10000;
            background: #fff; box-shadow: 0 -4px 24px rgba(0,0,0,.12);
            border-top: 2px solid #0046ab;
            animation: compareSlideUp .35s ease;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        .compare-bar-inner {
            max-width: 1200px; margin: 0 auto; padding: 12px 20px;
            display: flex; align-items: center; gap: 16px;
            pointer-events: auto;
        }
        .compare-bar.collapsed {
            left: auto; right: 20px; bottom: 85px; width: auto;
            border-radius: 12px; border: 2px solid #0046ab;
            box-shadow: 0 4px 20px rgba(0,0,0,.15);
        }
        .compare-bar.collapsed .compare-btn-clear:not(#compareCollapseBtn) {
            display: none;
        }
        @keyframes compareSlideUp {
            from { transform: translateY(100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .compare-slots { display: flex; gap: 12px; flex: 1; }
        .compare-slot {
            flex: 1; max-width: 280px; min-height: 56px;
            border-radius: 10px; overflow: hidden;
        }
        .compare-slot-empty {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            height: 56px; border: 2px dashed #d1d5db; border-radius: 10px;
            color: #9ca3af; font-size: 13px; font-weight: 600; cursor: pointer;
            transition: .2s;
        }
        .compare-slot-empty:hover { border-color: #0046ab; color: #0046ab; background: #f0f7ff; }
        .compare-slot-empty i { font-size: 18px; }
        .compare-slot-filled {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; background: #f8f9fa; border-radius: 10px;
            border: 1px solid #e5e7eb; position: relative;
        }
        .compare-slot-img { width: 40px; height: 40px; object-fit: contain; flex-shrink: 0; }
        .compare-slot-info { flex: 1; min-width: 0; }
        .compare-slot-name {
            display: block; font-size: 12px; font-weight: 600; color: #333;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .compare-slot-price {
            display: block; font-size: 12px; font-weight: 700; color: #d70018;
        }
        .compare-slot-remove {
            position: absolute; top: -4px; right: -4px; width: 22px; height: 22px;
            border-radius: 50%; border: none; background: #fee2e2; color: #d70018;
            cursor: pointer; font-size: 10px; transition: .2s;
            display: flex; align-items: center; justify-content: center;
        }
        .compare-slot-remove:hover { background: #d70018; color: #fff; }
        .compare-actions { display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
        .compare-btn-go {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; background: linear-gradient(135deg, #0046ab, #003380);
            color: #fff; border-radius: 8px; font-size: 13px; font-weight: 700;
            cursor: pointer; transition: .2s; text-decoration: none;
        }
        .compare-btn-go:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,70,171,.3); }
        .compare-count-badge {
            background: #d70018; color: #fff; font-size: 11px; font-weight: 700;
            padding: 1px 7px; border-radius: 10px; min-width: 20px; text-align: center;
        }
        .compare-btn-clear {
            padding: 6px 16px; background: none; border: 1px solid #d1d5db;
            border-radius: 6px; font-size: 12px; color: #888; cursor: pointer;
            transition: .2s;
        }
        .compare-btn-clear:hover { border-color: #d70018; color: #d70018; }

        /* Search Modal */
        .compare-search-modal {
            position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 10001;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(3px);
        }
        .compare-search-content {
            background: #fff; width: 520px; max-width: 95vw; border-radius: 14px;
            overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,.25);
            animation: modalPop .25s ease;
        }
        .compare-search-header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1px solid #e5e7eb;
        }
        .compare-search-header h3 {
            font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px;
        }
        .compare-search-header button {
            background: none; border: none; font-size: 20px; color: #888;
            cursor: pointer; transition: .2s; padding: 4px;
        }
        .compare-search-header button:hover { color: #d70018; }
        .compare-search-body { padding: 16px 20px; }
        .compare-search-body input {
            width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb;
            border-radius: 10px; font-size: 14px; outline: none; transition: .2s;
        }
        .compare-search-body input:focus { border-color: #0046ab; }
        .compare-search-results {
            max-height: 300px; overflow-y: auto; margin-top: 12px;
        }
        .compare-search-result-item {
            display: flex; align-items: center; gap: 12px; padding: 10px 12px;
            border-radius: 8px; cursor: pointer; transition: .15s;
        }
        .compare-search-result-item:hover { background: #f0f7ff; }
        .compare-search-result-item img {
            width: 44px; height: 44px; object-fit: contain; border-radius: 6px;
            border: 1px solid #f0f0f0; padding: 2px;
        }
        .compare-search-result-info { flex: 1; }
        .compare-search-result-name { font-size: 13px; font-weight: 600; color: #333; }
        .compare-search-result-price { font-size: 12px; font-weight: 700; color: #d70018; margin-top: 2px; }

        /* ============================
           RESPONSIVE STYLES
           ============================ */
        @media (max-width: 1200px) {
            .container { max-width: 100%; }
        }

        @media (max-width: 1024px) {
            .header-province-btn { display: none; } /* Ẩn bớt tỉnh thành trên tablet */
            .search-bar { max-width: 300px; }
            .logo { font-size: 18px; }
            .footer-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .top-bar { display: none; } /* Ẩn topbar trên mobile */
            .header-main { padding: 8px 0; }
            .header-content { flex-wrap: wrap; justify-content: space-between; }
            .logo { order: 1; font-size: 16px; }
            .header-actions { order: 2; gap: 0; }
            .search-bar { order: 3; flex: 0 0 100%; max-width: 100%; margin-top: 10px; }
            .header-category-btn { order: 4; display: none; } /* Ẩn nút danh mục header trên mobile */
            
            .action-item span { display: none; } /* Chỉ hiện icon cho actions trên mobile */
            .action-item i { font-size: 20px; margin-bottom: 0; }
            
            .footer-grid { grid-template-columns: 1fr; gap: 20px; }
            .mega-menu { padding-top: 110px; }
            .mega-col-left { width: 120px; }
            .mega-cat-item span { font-size: 11px; }
            .mega-cat-item i:first-child { display: none; }
        }

    </style>
    @stack('styles')
</head>
<body>
    @include('partials.header')

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Floating Compare Bar --}}
    @include('partials.compare-bar')



    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    <!-- ============================
         GLOBAL SCRIPTS (Mega Menu + Province Modal)
         ============================ -->
    <script>
        // ===== MEGA MENU =====
        const megaMenu = document.getElementById('megaMenu');
        const megaOverlay = document.getElementById('megaMenuOverlay');
        const catBtn = document.getElementById('categoryToggleBtn');

        if (catBtn && megaMenu && megaOverlay) {
            catBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                megaMenu.classList.toggle('show');
                megaOverlay.classList.toggle('show');
            });

            megaOverlay.addEventListener('click', () => {
                megaMenu.classList.remove('show');
                megaOverlay.classList.remove('show');
            });

            // Hover trên cột trái → đổi panel bên phải
            document.querySelectorAll('.mega-cat-item[data-cat]').forEach(item => {
                item.addEventListener('mouseenter', () => {
                    document.querySelectorAll('.mega-cat-item').forEach(i => i.classList.remove('active'));
                    document.querySelectorAll('.mega-detail-panel').forEach(p => p.classList.remove('active'));
                    item.classList.add('active');
                    const panel = document.querySelector(`.mega-detail-panel[data-panel="${item.dataset.cat}"]`);
                    if (panel) panel.classList.add('active');
                });
            });
        }

        // ===== PROVINCE MODAL =====
        const provOverlay = document.getElementById('provinceModalOverlay');
        const provBtn = document.getElementById('provinceToggleBtn');
        const provCloseBtn = document.getElementById('provinceCloseBtn');
        const provSearchInput = document.getElementById('provinceSearchInput');

        if (provBtn && provOverlay) {
            provBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                provOverlay.classList.add('show');
                // Đóng mega menu nếu đang mở
                if (megaMenu) megaMenu.classList.remove('show');
                if (megaOverlay) megaOverlay.classList.remove('show');
                setTimeout(() => provSearchInput && provSearchInput.focus(), 100);
            });

            provCloseBtn && provCloseBtn.addEventListener('click', () => {
                provOverlay.classList.remove('show');
            });

            provOverlay.addEventListener('click', (e) => {
                if (e.target === provOverlay) provOverlay.classList.remove('show');
            });

            // Chọn tỉnh thành
            document.querySelectorAll('.province-item').forEach(item => {
                item.addEventListener('click', () => {
                    document.querySelectorAll('.province-item').forEach(i => {
                        i.classList.remove('selected');
                        i.querySelector('i')?.remove();
                    });
                    item.classList.add('selected');
                    const check = document.createElement('i');
                    check.className = 'fa-solid fa-circle-check';
                    item.appendChild(check);
                    document.getElementById('selectedProvinceName').textContent = item.dataset.province;
                    setTimeout(() => provOverlay.classList.remove('show'), 200);
                });
            });

            // Tìm kiếm tỉnh thành
            provSearchInput && provSearchInput.addEventListener('input', (e) => {
                const q = e.target.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                document.querySelectorAll('.province-item').forEach(item => {
                    const name = item.dataset.province.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    item.classList.toggle('hidden', !name.includes(q));
                });
            });
        }

        // Đóng mega menu khi nhấn Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (megaMenu) megaMenu.classList.remove('show');
                if (megaOverlay) megaOverlay.classList.remove('show');
                if (provOverlay) provOverlay.classList.remove('show');
            }
        });
    </script>

    <!-- Compare Feature JS -->


    @stack('scripts')
</body>
</html>
