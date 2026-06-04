@php
    $error_message = $errors->first('login_error') ?: $errors->first();
    $success_message = session('success');
    $active_tab = session('active_tab', 'login');
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.login_register_title') }}</title>
    <!-- Favicon (Logo Sét của Web) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 384 512' fill='%230046ab'><path d='M0 256L0 288c0 17.7 14.3 32 32 32l104.7 0L88.9 455c-6.8 17.1 5.8 36 24.2 36c11.3 0 21.6-6 26.8-15.6l176-320c9-16.3-.2-36.4-18.9-36.4l-123.8 0L222.1 57c6.8-17.1-5.8-36-24.2-36c-11.3 0-21.6 6-26.8 15.6L1.1 228.3C-.2 230.9 0 233.9 0 236.9v19.1z'/></svg>">
    <!-- Font system: Outfit (UI) + Space Grotesk (Headlines) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome for Premium Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- KHÁI QUÁT CẤU HÌNH MÀU (CSS VARIABLES) - CHỦ ĐỀ CÔNG NGHỆ ĐỎ XANH --- */
        :root {
            /* Màu chủ đạo Công nghệ (Đỏ & Xanh) */
            --tech-red: #ef4444;
            --tech-blue: #3b82f6;
            --tech-dark: #0f172a;
            
            --primary: #3b82f6; /* Nút chính màu Xanh */
            --primary-hover: #2563eb;
            --secondary: #ef4444; /* Màu phụ màu Đỏ */
            
            /* Màu chữ */
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --text-light: #ffffff;
            
            /* Màu nền & Viền form */
            --bg-form: #ffffff;
            --bg-input: #f8fafc;
            --border-input: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

        /* Tự động chức năng số đếm lên */
        @property --count-num { syntax: '<integer>'; initial-value: 0; inherits: false; }

        body {
            /* Hình ảnh nền ban đầu */
            background-color: #0f172a;
            background-image: url('{{ asset('assets/img/background_login_register.avif') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
            
            /* Lớp phủ mờ nhẹ */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        /* --- CẤU TRÚC CHÍNH BỐ CỤC CHIA ĐÔI --- */
        .main-wrapper {
            width: 100%; max-width: 1100px;
            height: 680px;
            background: var(--bg-form);
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            display: flex;
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        /* --- BẢNG TRÁI: CHÀO MỪNG PREMIUM E-COMMERCE --- */
        .visual-panel {
            width: 45%;
            position: relative;
            background: linear-gradient(150deg, #c0392b 0%, #96281b 35%, #1a2a6c 100%);
            display: flex; flex-direction: column;
            justify-content: flex-end; align-items: stretch;
            padding: 0; text-align: left; color: var(--text-light);
            overflow: hidden;
            /* Layer shadow sang trong */
            box-shadow: inset -1px 0 0 rgba(255,255,255,0.08);
        }

        /* Các vòng trang trí nền ánh sáng */
        .bg-glow {
            position: absolute; border-radius: 50%; pointer-events: none;
            filter: blur(60px);
        }
        .bg-glow-1 { width: 280px; height: 280px; top: -60px; right: -60px; background: rgba(239,68,68,0.5); animation: glowFloat1 8s ease-in-out infinite; }
        .bg-glow-2 { width: 220px; height: 220px; bottom: 60px; left: -80px; background: rgba(59,130,246,0.4); animation: glowFloat2 10s ease-in-out infinite; }
        .bg-glow-3 { width: 160px; height: 160px; top: 40%; right: 30px; background: rgba(255,255,255,0.08); animation: glowFloat1 6s ease-in-out infinite reverse; }

        @keyframes glowFloat1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(20px, 15px) scale(1.1); } }
        @keyframes glowFloat2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-15px, -20px) scale(1.15); } }

        /* Pattern chấm bi nền */
        .bg-dots {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-image: radial-gradient(rgba(255,255,255,0.08) 1.5px, transparent 1.5px);
            background-size: 24px 24px;
            pointer-events: none;
        }

        /* Nội dung chào mừng */
        .welcome-content {
            position: relative; z-index: 10;
            padding: 40px 42px 36px;
            display: flex; flex-direction: column; height: 100%;
            justify-content: space-between;
        }

        /* Logo / thương hiệu bên trái trên cùng */
        .vp-brand {
            font-size: 20px; font-weight: 800; letter-spacing: 0.5px;
            display: flex; align-items: center; gap: 8px;
        }
        .vp-brand-dot { width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block; }
        .vp-brand-dot-2 { background: #3b82f6; }

        /* Phần tiêu đề chính giữa */
        .vp-main { flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 24px 0; }
        .vp-tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px; padding: 5px 14px; font-size: 12px; font-weight: 600;
            margin-bottom: 18px; width: fit-content;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        .vp-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #86efac; animation: pulse 1.5s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.4); } }

        .vp-title {
            font-family: 'Space Grotesk', 'Outfit', sans-serif;
            font-size: 38px; font-weight: 800; line-height: 1.15; margin-bottom: 14px;
            animation: slideUp 0.8s ease 0.3s both;
        }
        .vp-title span { color: #fca5a5; }
        .vp-desc {
            font-size: 14px; font-weight: 400; color: rgba(255,255,255,0.75); line-height: 1.7;
        }

        /* Stats row */
        .vp-stats {
            display: flex; gap: 0;
            border-top: 1px solid rgba(255,255,255,0.15);
            padding-top: 24px;
        }
        .vp-stat {
            flex: 1; text-align: center; padding: 0 12px;
            border-right: 1px solid rgba(255,255,255,0.15);
        }
        .vp-stat:last-child { border-right: none; }
        .vp-stat-num { font-size: 22px; font-weight: 800; margin-bottom: 2px; }
        .vp-stat-num span { color: #fca5a5; }
        .vp-stat-label { font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: 0.5px; }


        /* --- BẢNG PHẢI: KHU VỰC FORM --- */
        .form-panel {
            width: 55%;
            padding: 40px 60px;
            background: var(--bg-form);
            display: flex; flex-direction: column; justify-content: center;
        }

        /* Brand title: dùng Space Grotesk cho headings nổi bật hơn */
        .form-header { text-align: center; margin-bottom: 30px; animation: fadeSlideDown 0.6s ease both; }
        .brand-title {
            font-family: 'Space Grotesk', 'Outfit', sans-serif;
            font-size: 38px; font-weight: 800; color: var(--tech-dark); letter-spacing: -1.5px; margin-bottom: 4px;
        }
        .brand-title .highlight {
            color: transparent;
            background: linear-gradient(90deg, var(--tech-blue), var(--tech-red));
            -webkit-background-clip: text;
        }
        .brand-slogan { font-size: 14px; color: var(--text-muted); font-weight: 500; }

        /* Tabs với hiệu ứng underline trượt */
        .tabs { display: flex; gap: 30px; margin-bottom: 28px; justify-content: center; position: relative; }
        .tab { font-size: 18px; font-weight: 700; color: #cbd5e1; cursor: pointer; transition: all 0.3s; padding-bottom: 6px; border-bottom: 2px solid transparent; position: relative; }
        .tab.active { color: var(--tech-dark); border-bottom: 2px solid var(--tech-blue); }
        .tab:hover:not(.active) { color: var(--text-muted); }
        /* Micro-animation khi chuyển tab */
        .tab.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 2px; background: var(--tech-blue); animation: underlineSlide 0.3s ease; }
        @keyframes underlineSlide { from { transform: scaleX(0); } to { transform: scaleX(1); } }

        .form-group { margin-bottom: 20px; position: relative; animation: fadeSlideUp 0.5s ease both; }
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        
        /* Eye toggle cho mật khẩu */
        .input-wrapper { position: relative; }
        .input-wrapper .form-control { padding-right: 46px; }
        .eye-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #94a3b8; transition: color 0.2s; background: none; border: none; padding: 0;
        }
        .eye-toggle:hover { color: var(--tech-blue); }
        
        .form-control {
            width: 100%; padding: 14px 16px; 
            background: var(--bg-input);
            border: 1px solid var(--border-input);
            border-radius: 12px; font-size: 15px; color: var(--tech-dark); font-weight: 500;
            transition: all 0.3s ease;
        }
        .form-control::placeholder { color: #94a3b8; font-weight: 400; }
        .form-control:focus { 
            outline: none; 
            background: var(--bg-form);
            border-color: var(--tech-blue); 
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); 
        }

        /* Ẩn con mắt mặc định của trình duyệt (Edge) để tránh 2 con mắt */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        .forgot-link { font-size: 13px; color: var(--text-muted); text-decoration: none; font-weight: 600; transition: color 0.3s; }
        .forgot-link:hover { color: var(--tech-blue); text-decoration: underline; }

        /* Nút chức năng chính */
        .btn-submit {
            width: 100%; padding: 15px; 
            background: linear-gradient(90deg, var(--tech-blue), #2563eb);
            color: var(--text-light); border: none; border-radius: 12px; 
            font-size: 16px; font-weight: 700; cursor: pointer; 
            margin-top: 5px; transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);
        }
        .btn-submit:hover { 
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(59, 130, 246, 0.4);
            background: linear-gradient(90deg, #2563eb, #1d4ed8);
        }
        .btn-submit:active { transform: translateY(0); }

        .btn-submit-red { 
            background: linear-gradient(90deg, var(--tech-red), #dc2626); 
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3); 
        }
        .btn-submit-red:hover { 
            background: linear-gradient(90deg, #dc2626, #b91c1c); 
            box-shadow: 0 12px 25px rgba(239, 68, 68, 0.4); 
        }

        .divider { 
            display: flex; align-items: center; text-align: center; 
            margin: 25px 0 20px 0; color: var(--text-muted); font-size: 13px; font-weight: 500;
        }
        .divider::before, .divider::after { content: ''; flex: 1; border-bottom: 1px solid var(--border-input); }
        .divider:not(:empty)::before { margin-right: 1em; }
        .divider:not(:empty)::after { margin-left: 1em; }


        /* Nút Sign in with Google */
        .btn-google {
            width: 100%; padding: 13px 20px;
            background: #ffffff;
            border: 1px solid #dadce0;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            text-decoration: none;
            font-size: 15px; font-weight: 600; color: #3c4043;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        .btn-google:hover {
            background: #f8fafc;
            border-color: #c5c8cc;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-1px);
        }
        
        .alert { padding: 10px; border-radius: 10px; margin-bottom: 15px; font-size: 14px; text-align: center; font-weight: 600; }
        .alert-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;}
        .alert-success { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;}
        
        .hidden { display: none !important; }

        /* Form animations */
        .form-view { animation: fadeIn 0.4s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateX(10px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeSlideDown { from { opacity: 0; transform: translateY(-16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeSlideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }

        /* Hiệu ứng wrapper cứ khi load */
        .main-wrapper { animation: wrapperAppear 0.7s cubic-bezier(0.22,1,0.36,1) both; }
        @keyframes wrapperAppear { from { opacity: 0; transform: scale(0.96) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }

        /* Btn ripple effect */
        .btn-submit { position: relative; overflow: hidden; }
        .btn-submit::after { content: ''; position: absolute; inset: 0; background: rgba(255,255,255,0.15); opacity: 0; transition: opacity 0.3s; }
        .btn-submit:active::after { opacity: 1; }

        /* --- FORM TOP ACTIONS BAR (TỐI GIẢN & CAO CẤP) --- */
        .form-top-actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            border-bottom: 1px dashed rgba(226, 232, 240, 0.8);
            padding-bottom: 20px;
        }

        .form-back-home-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            padding: 9px 18px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 14px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.03);
            position: relative;
            overflow: hidden;
        }

        .form-back-home-link i {
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.3s;
            font-size: 11px;
            color: #94a3b8;
        }

        .form-back-home-link:hover {
            color: var(--tech-blue);
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
        }

        .form-back-home-link:hover i {
            transform: translateX(-4px);
            color: var(--tech-blue);
        }

        .form-lang-switcher {
            position: relative;
        }

        .form-lang-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            padding: 9px 18px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 14px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.03);
        }

        .form-lang-btn .lang-flag-current {
            font-size: 14px;
            line-height: 1;
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .form-lang-btn i:last-child {
            font-size: 8px;
            color: #94a3b8;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .form-lang-btn:hover {
            color: var(--tech-blue);
            background: rgba(59, 130, 246, 0.05);
            border-color: rgba(59, 130, 246, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.1);
        }

        .form-lang-btn:hover .lang-flag-current {
            transform: scale(1.15) rotate(5deg);
        }
        
        .form-lang-btn:hover i:last-child {
            transform: translateY(2px);
            color: var(--tech-blue);
        }

        .form-lang-dropdown {
            display: block;
            visibility: hidden;
            opacity: 0;
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            z-index: 1100;
            min-width: 170px;
            transform: translateY(12px) scale(0.95);
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            padding: 6px;
        }

        .form-lang-dropdown.show {
            visibility: visible;
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .form-lang-option {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            font-size: 13px;
            color: #475569;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.25s ease;
            font-weight: 600;
            position: relative;
        }

        .form-lang-option .lang-flag {
            font-size: 16px;
            transition: transform 0.2s ease;
        }

        .form-lang-option:hover {
            background: rgba(59, 130, 246, 0.05);
            color: var(--tech-blue);
            transform: translateX(4px);
        }

        .form-lang-option:hover .lang-flag {
            transform: scale(1.2);
        }

        .form-lang-option.active {
            background: rgba(59, 130, 246, 0.08);
            color: var(--tech-blue);
            font-weight: 700;
        }

        .form-lang-option .active-indicator {
            margin-left: auto;
            color: var(--tech-blue);
            font-weight: bold;
            font-size: 12px;
            background: rgba(59, 130, 246, 0.1);
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }



        /* ================= RESPONSIVE SYSTEM (MOBILE, TABLET, DESKTOP) ================= */
        
        /* 1. Màn hình máy tính bảng (Tablets - Dưới 1024px) */
        @media (max-width: 1024px) {
            .main-wrapper {
                max-width: 92%;
                height: 660px;
            }
            .visual-panel {
                width: 40%;
            }
            .form-panel {
                width: 60%;
                padding: 40px 45px;
            }
            .vp-title {
                font-size: 32px;
            }
            .brand-title {
                font-size: 34px;
            }
        }

        /* 2. Màn hình di động & Máy tính bảng dọc (Mobile & Small Tablets - Dưới 768px) */
        @media (max-width: 768px) {
            body {
                overflow-y: auto;
                height: auto;
                min-height: 100vh;
                padding: 24px 12px;
                display: flex;
                align-items: center; /* Căn giữa form theo chiều dọc khi màn hình đủ dài */
            }
            
            .main-wrapper {
                max-width: 100%;
                height: auto;
                min-height: unset;
                flex-direction: column;
                border-radius: 20px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
            }
            
            /* Ẩn hoàn toàn bảng chào mừng cồng kềnh trên điện thoại */
            .visual-panel {
                display: none;
            }
            
            .form-panel {
                width: 100%;
                padding: 30px 24px 35px; /* Giảm padding top vì actions bar đã nằm inline */
                min-height: 520px;
                justify-content: flex-start;
            }
            
            .form-header {
                margin-top: 10px;
                margin-bottom: 24px;
            }
            
            /* Định vị lại thanh top actions trên di động */
            .form-top-actions-bar {
                margin-bottom: 20px;
                padding-bottom: 12px;
            }

            .form-back-home-link, .form-lang-btn {
                padding: 7px 14px;
                font-size: 12px;
                border-radius: 10px;
            }
            
            /* Form Đăng ký: Chuyển 2 trường password & confirm password từ hàng ngang thành hàng dọc */
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-row .form-group {
                width: 100%;
            }
        }
        
        /* 3. Màn hình điện thoại siêu nhỏ (Dưới 480px) */
        @media (max-width: 480px) {
            body {
                padding: 16px 8px;
            }
            .form-panel {
                padding: 24px 16px 30px; /* Giảm padding top cho mobile siêu nhỏ */
            }
            .brand-title {
                font-size: 30px;
            }
            .brand-slogan {
                font-size: 12px;
            }
            .tab {
                font-size: 16px;
                padding-bottom: 4px;
            }
            .form-control {
                padding: 12px 14px;
                font-size: 14px;
                border-radius: 10px;
            }
            .btn-submit {
                padding: 13px;
                font-size: 15px;
                border-radius: 10px;
            }
            .btn-google {
                padding: 11px 16px;
                font-size: 14px;
                border-radius: 10px;
            }
            .alert {
                font-size: 13px;
                padding: 8px;
            }
        }

        /* --- PREMIUM TOAST NOTIFICATION SYSTEM --- */
        .toast-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }

        .toast-card {
            pointer-events: auto;
            width: 350px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 16px;
            padding: 16px 20px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
            display: flex;
            align-items: center;
            gap: 14px;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .toast-card.show {
            transform: translateX(0);
        }

        .toast-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }

        .toast-error .toast-icon {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .toast-success .toast-icon {
            background: rgba(34, 197, 94, 0.1);
            color: #22c55e;
        }

        .toast-content {
            flex-grow: 1;
        }

        .toast-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--tech-dark);
            margin-bottom: 2px;
        }

        .toast-message {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .toast-close {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 16px;
            padding: 4px;
            transition: color 0.2s;
            margin-left: auto;
        }

        .toast-close:hover {
            color: var(--tech-dark);
        }

        /* Thanh progress bar chạy ngầm */
        .toast-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            width: 100%;
            background: #e2e8f0;
        }

        .toast-progress-bar {
            height: 100%;
            width: 100%;
            transition: width 3.5s linear;
        }

        .toast-error .toast-progress-bar {
            background: #ef4444;
        }

        .toast-success .toast-progress-bar {
            background: #22c55e;
        }

        /* --- PREMIUM MODAL OVERLAY --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .modal-overlay.show .modal-card {
            transform: scale(1);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--tech-dark);
            font-family: 'Space Grotesk', sans-serif;
            margin: 0;
        }

        .modal-close-icon {
            background: none;
            border: none;
            font-size: 24px;
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.2s;
            padding: 0;
            line-height: 1;
        }

        .modal-close-icon:hover {
            color: var(--tech-red);
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
        }

        .modal-body h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--tech-dark);
            margin-top: 18px;
            margin-bottom: 8px;
        }

        .modal-body p {
            margin-bottom: 12px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            background: #f8fafc;
        }

        .modal-btn-close {
            padding: 10px 20px;
            background: var(--tech-dark);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .modal-btn-close:hover {
            opacity: 0.9;
        }

    </style>
</head>
<body>
    
    <div class="main-wrapper">
        <!-- VISUAL PANEL (Trái): Premium E-commerce Welcome -->
        <div class="visual-panel">
            <canvas id="particle-canvas"></canvas>
            <div class="bg-glow bg-glow-1"></div>
            <div class="bg-glow bg-glow-2"></div>
            <div class="bg-glow bg-glow-3"></div>
            <div class="bg-dots"></div>

            <div class="welcome-content">
                <!-- Logo trên cùng -->
                <div class="vp-brand">
                    <span class="vp-brand-dot"></span>
                    <span class="vp-brand-dot vp-brand-dot-2"></span>
                    DienMayPro
                </div>

                <!-- Nội dung chính -->
                <div class="vp-main">
                    <div class="vp-tag">
                        <span class="vp-tag-dot"></span>
                        {{ __('ui.active_badge') }}
                    </div>
                    <h2 class="vp-title">{{ __('ui.smart_shopping_title') }}<br><span>{{ __('ui.smart_shopping_highlight') }}</span></h2>
                    <p class="vp-desc">
                        {!! __('ui.banner_features_desc') !!}
                    </p>
                </div>

                <!-- Thống kê dưới cùng -->
                <div class="vp-stats">
                    <div class="vp-stat">
                        <div class="vp-stat-num">10K<span>+</span></div>
                        <div class="vp-stat-label">{{ __('ui.stat_products') }}</div>
                    </div>
                    <div class="vp-stat">
                        <div class="vp-stat-num">98<span>%</span></div>
                        <div class="vp-stat-label">{{ __('ui.stat_satisfaction') }}</div>
                    </div>
                    <div class="vp-stat">
                        <div class="vp-stat-num">2H<span>+</span></div>
                        <div class="vp-stat-label">{{ __('ui.stat_delivery') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FORM PANEL (Phải) -->
        <div class="form-panel" style="position: relative;">

            <!-- Form Top Actions Bar (Căn chỉnh ngay ngắn trên đầu Form Panel) -->
            <div class="form-top-actions-bar">
                <!-- Nút Quay lại Trang chủ -->
                <a href="{{ route('home') }}" class="form-back-home-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>{{ __('ui.home_btn') }}</span>
                </a>

                <!-- Trình chọn ngôn ngữ -->
                <div class="form-lang-switcher" id="loginLangSwitcher">
                    <button class="form-lang-btn" id="loginLangToggleBtn">
                        <span class="lang-flag-current">{{ app()->getLocale() === 'en' ? '🇺🇸' : '🇻🇳' }}</span>
                        <span>{{ app()->getLocale() === 'en' ? 'EN' : 'VI' }}</span>
                        <i class="fa-solid fa-chevron-down" style="font-size: 8px; margin-left: 2px;"></i>
                    </button>
                    <div class="form-lang-dropdown" id="loginLangDropdown">
                        <a href="{{ route('locale.switch', 'vi') }}" class="form-lang-option {{ app()->getLocale() === 'vi' ? 'active' : '' }}">
                            <span class="lang-flag">🇻🇳</span>
                            <span>Tiếng Việt</span>
                            @if(app()->getLocale() === 'vi')
                                <span class="active-indicator">✓</span>
                            @endif
                        </a>
                        <a href="{{ route('locale.switch', 'en') }}" class="form-lang-option {{ app()->getLocale() === 'en' ? 'active' : '' }}">
                            <span class="lang-flag">🇺🇸</span>
                            <span>English</span>
                            @if(app()->getLocale() === 'en')
                                <span class="active-indicator">✓</span>
                            @endif
                        </a>
                    </div>
                </div>
            </div>

            <!-- Đưa tên thương hiệu lên trên -->
            <div class="form-header">
                <h2 class="brand-title">DienMay<span class="highlight">Pro</span></h2>
                <p class="brand-slogan">{{ __('ui.welcome_back_slogan') }}</p>
            </div>

            <!-- Hiển thị lỗi hệ thống được chuyển sang dạng Toast nổi ở góc màn hình qua JS phía dưới -->

            <style>
                @keyframes shake {
                    0%, 100% { transform: translateX(0); }
                    25% { transform: translateX(-5px); }
                    75% { transform: translateX(5px); }
                }
            </style>
            <div class="tabs">
                <div class="tab" id="tabLogin">{{ __('ui.login_tab') }}</div>
                <div class="tab" id="tabRegister">{{ __('ui.register_tab') }}</div>
            </div>

            @if($success_message)
                <div class="alert alert-success">{{ $success_message }}</div>
            @endif

            <!-- Form Đăng nhập -->
            <div id="formLoginView" class="form-view">
                <form method="POST" action="{{ route('login.post') }}" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="email">{{ __('ui.email_label') }}</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="{{ __('ui.placeholder_email') }}">
                    </div>

                    <div class="form-group">
                        <label for="password">{{ __('ui.password_label') }}</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-control" required minlength="8" placeholder="{{ __('ui.placeholder_password') }}">
                            <button type="button" class="eye-toggle" onclick="togglePassword('password', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                        <div class="login-options" style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px; margin-bottom: 8px;">
                            <label class="remember-me" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); font-weight: 600; cursor: pointer; text-transform: none; letter-spacing: 0;">
                                <input type="checkbox" name="remember" id="remember" style="width: 16px; height: 16px; border-radius: 4px; border: 1px solid var(--border-input); cursor: pointer; accent-color: var(--tech-blue);">
                                <span>{{ __('ui.remember_me') }}</span>
                            </label>
                            <a href="{{ route('password.request') }}" class="forgot-link">{{ __('ui.forgot_password_link') }}</a>
                        </div>
                    </div>

                    <button type="submit" name="login_submit" class="btn-submit">{{ __('ui.login_btn') }}</button>
                </form>

                <div class="divider">{{ __('ui.or_divider') }}</div>

                <a href="{{ route('social.login', 'google') }}" class="btn-google">
                    <svg width="20" height="20" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    <span>{{ __('ui.login_google') }}</span>
                </a>
            </div>
            
            <!-- Form Đăng ký -->
            <div id="formRegisterView" class="hidden form-view">
                <form method="POST" action="{{ route('register.post') }}" novalidate>
                    @csrf
                    <div class="form-group">
                        <label for="full_name">{{ __('ui.full_name_label') }}</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" value="{{ old('full_name') }}" required placeholder="{{ __('ui.placeholder_fullname') }}">
                    </div>

                    <div class="form-group">
                        <label for="reg_email">{{ __('ui.email_label') }}</label>
                        <input type="email" id="reg_email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="{{ __('ui.placeholder_email') }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label for="reg_password">{{ __('ui.password_label') }}</label>
                            <div class="input-wrapper">
                                <input type="password" id="reg_password" name="password" class="form-control" required minlength="8" placeholder="{{ __('ui.placeholder_password') }}">
                                <button type="button" class="eye-toggle" onclick="togglePassword('reg_password', this)">
                                    <svg id="eye-reg" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            <!-- Password Strength Meter -->
                            <div class="password-strength-container" style="margin-top: 6px;">
                                <div class="strength-bar-wrapper" style="height: 4px; width: 100%; background: #e2e8f0; border-radius: 2px; overflow: hidden; position: relative;">
                                    <div id="strength-bar" style="height: 100%; width: 0%; transition: width 0.3s, background-color 0.3s; border-radius: 2px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                                    <span id="strength-text" style="font-size: 11px; font-weight: 600; color: #94a3b8;"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label for="password_confirmation">{{ __('ui.confirm_password_label') }}</label>
                            <div class="input-wrapper">
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" placeholder="{{ __('ui.placeholder_password') }}">
                                <button type="button" class="eye-toggle" onclick="togglePassword('password_confirmation', this)">
                                    <svg id="eye-confirm" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Đồng ý Điều khoản & Chính sách -->
                    <div class="form-group" style="margin-top: 15px; margin-bottom: 15px;">
                        <label class="terms-label" style="display: inline-flex; align-items: flex-start; gap: 8px; font-size: 13px; color: var(--text-muted); font-weight: 500; cursor: pointer; text-transform: none; letter-spacing: 0;">
                            <input type="checkbox" id="accept_terms" required style="width: 16px; height: 16px; margin-top: 2px; border-radius: 4px; accent-color: var(--tech-red); cursor: pointer;">
                            <span style="line-height: 1.4;">
                                {!! __('ui.accept_terms_text', ['terms' => '<a href="#" style="color: var(--tech-red); text-decoration: none; font-weight: 600;">'.__('ui.terms_link').'</a>', 'privacy' => '<a href="#" style="color: var(--tech-red); text-decoration: none; font-weight: 600;">'.__('ui.privacy_link').'</a>']) !!}
                            </span>
                        </label>
                    </div>

                    <button type="submit" name="register_submit" class="btn-submit btn-submit-red">{{ __('ui.reg_btn') }}</button>
                </form>
            </div>

        </div>
    </div>

    <!-- MODAL ĐIỀU KHOẢN VÀ CHÍNH SÁCH -->
    <div id="termsModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalTitle" class="modal-title"></h3>
                <button id="modalCloseBtn" class="modal-close-icon">&times;</button>
            </div>
            <div id="modalContent" class="modal-body">
                <!-- Content injected dynamically -->
            </div>
            <div class="modal-footer">
                <button id="modalOkBtn" class="modal-btn-close">{{ app()->getLocale() === 'en' ? 'Close' : 'Đóng lại' }}</button>
            </div>
        </div>
    </div>

    <script>
        /* ===== CANVAS PARTICLE SYSTEM ===== */
        const canvas = document.getElementById('particle-canvas');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function resizeCanvas() {
            const panel = canvas.parentElement;
            canvas.width = panel.offsetWidth;
            canvas.height = panel.offsetHeight;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        class Particle {
            constructor() { this.reset(); }
            reset() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;
                this.r = Math.random() * 1.8 + 0.4;
                this.vx = (Math.random() - 0.5) * 0.4;
                this.vy = (Math.random() - 0.5) * 0.4 - 0.2;
                this.alpha = Math.random() * 0.5 + 0.1;
                this.color = Math.random() > 0.5 ? '255,100,100' : '100,160,255';
            }
            update() {
                this.x += this.vx; this.y += this.vy;
                if (this.y < -5 || this.x < -5 || this.x > canvas.width + 5) this.reset();
            }
            draw() {
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
                ctx.fillStyle = `rgba(${this.color},${this.alpha})`;
                ctx.fill();
            }
        }

        // Khởi tạo 80 hạt
        // Khởi tạo danh sách 80 hạt (particles) ban đầu
        for (let i = 0; i < 80; i++) particles.push(new Particle());

        // Hàm vẽ các đường nối giữa các hạt khi chúng ở gần nhau
        function drawConnections() {
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx*dx + dy*dy);
                    
                    // Nếu khoảng cách giữa 2 hạt nhỏ hơn 70px thì vẽ đường nối
                    if (dist < 70) {
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        // Độ đậm nhạt của đường nối phụ thuộc vào khoảng cách (càng gần càng rõ)
                        ctx.strokeStyle = `rgba(255,255,255,${0.06 * (1 - dist/70)})`;
                        ctx.lineWidth = 0.5;
                        ctx.stroke();
                    }
                }
            }
        }

        // Hàm vòng lặp chính để xử lý hiệu ứng hoạt họa (Animation Loop)
        function animateParticles() {
            // Xóa toàn bộ canvas để chuẩn bị vẽ khung hình mới
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            // Vẽ các đường liên kết giữa các hạt
            drawConnections();
            
            // Cập nhật vị trí và vẽ từng hạt một
            particles.forEach(p => { p.update(); p.draw(); });
            
            // Yêu cầu trình duyệt gọi lại hàm này ở khung hình tiếp theo
            requestAnimationFrame(animateParticles);
        }
        
        // Bắt đầu chạy hiệu ứng
        animateParticles();

        /* ===== PASSWORD TOGGLE ===== */
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            // Icon mắt mở / mắt gạch
            btn.innerHTML = isHidden
                ? `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
        }

        /* ===== CHUYỂN ĐỔI TAB ĐĂNG NHẬP / ĐĂNG KÝ ===== */
        // Lấy các phần tử DOM của nút Tab
        const tabLogin = document.getElementById('tabLogin');
        const tabRegister = document.getElementById('tabRegister');
        
        // Lấy các phần tử DOM của Form hiển thị
        const formLoginView = document.getElementById('formLoginView');
        const formRegisterView = document.getElementById('formRegisterView');

        // Hàm hiển thị Form Đăng nhập
        function showLogin() {
            tabLogin.classList.add('active'); // Làm nổi bật tab Đăng nhập
            tabRegister.classList.remove('active'); // Tắt nổi bật tab Đăng ký
            formLoginView.classList.remove('hidden'); // Hiện form Đăng nhập
            formRegisterView.classList.add('hidden'); // Ẩn form Đăng ký
        }

        // Hàm hiển thị Form Đăng ký
        function showRegister() {
            tabRegister.classList.add('active'); // Làm nổi bật tab Đăng ký
            tabLogin.classList.remove('active'); // Tắt nổi bật tab Đăng nhập
            formRegisterView.classList.remove('hidden'); // Hiện form Đăng ký
            formLoginView.classList.add('hidden'); // Ẩn form Đăng nhập
        }

        // Gắn sự kiện click cho các tab
        tabLogin.addEventListener('click', showLogin);
        tabRegister.addEventListener('click', showRegister);

        // Lấy trạng thái tab đang active từ backend (mặc định là 'login')
        const currentActiveTab = '{{ $active_tab }}';
        
        // Hiển thị tab tương ứng khi vừa load trang
        if (currentActiveTab === 'register') {
            showRegister();
        } else {
            showLogin();
        }

        /* ===== LANGUAGE SWITCHER DROPDOWN ===== */
        const loginLangBtn = document.getElementById('loginLangToggleBtn');
        const loginLangDropdown = document.getElementById('loginLangDropdown');
        if (loginLangBtn && loginLangDropdown) {
            loginLangBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                loginLangDropdown.classList.toggle('show');
            });
            document.addEventListener('click', function(e) {
                if (!loginLangBtn.contains(e.target) && !loginLangDropdown.contains(e.target)) {
                    loginLangDropdown.classList.remove('show');
                }
            });
        }

        /* ===== HIGH-END TOAST NOTIFICATION SYSTEM ===== */
        // Tạo container chứa Toast nếu chưa có
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }

        function showToast(title, message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast-card toast-${type}`;
            
            const iconHtml = type === 'success' 
                ? '<i class="fa-solid fa-circle-check"></i>' 
                : '<i class="fa-solid fa-circle-exclamation"></i>';
                
            toast.innerHTML = `
                <div class="toast-icon">${iconHtml}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close">&times;</button>
                <div class="toast-progress">
                    <div class="toast-progress-bar"></div>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            // Trigger animation slide in
            setTimeout(() => {
                toast.classList.add('show');
                const bar = toast.querySelector('.toast-progress-bar');
                if (bar) bar.style.width = '0%';
            }, 10);
            
            // Tự động đóng sau 3.5 giây
            const timer = setTimeout(() => {
                closeToast(toast);
            }, 3500);
            
            // Bấm nút đóng
            toast.querySelector('.toast-close').addEventListener('click', () => {
                clearTimeout(timer);
                closeToast(toast);
            });
        }

        function closeToast(toast) {
            toast.classList.remove('show');
            toast.addEventListener('transitionend', () => {
                toast.remove();
            });
        }

        /* ===== CLIENT-SIDE VALIDATION & SPAM PREVENTION ===== */
        const formLogin = document.querySelector('#formLoginView form');
        const formRegister = document.querySelector('#formRegisterView form');
        
        if (formLogin) {
            formLogin.addEventListener('submit', function(e) {
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                const submitBtn = formLogin.querySelector('button[type="submit"]');
                
                // Chặn nhấn liên tiếp khi đang xử lý
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return;
                }
                
                // Kiểm định độ dài mật khẩu phía Client
                if (passwordInput.value.length < 8) {
                    e.preventDefault();
                    showToast(
                        "{{ app()->getLocale() === 'en' ? 'Validation Error' : 'Lỗi kiểm định' }}",
                        "{{ app()->getLocale() === 'en' ? 'Password must be at least 8 characters.' : 'Mật khẩu phải từ 8 ký tự trở lên.' }}",
                        'error'
                    );
                    passwordInput.focus();
                    return;
                }
                
                // Kích hoạt trạng thái đang tải & vô hiệu hóa nút bấm
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.innerHTML = `<span style="display:inline-flex; align-items:center; gap:8px;"><i class="fa-solid fa-spinner fa-spin"></i> {{ app()->getLocale() === 'en' ? 'Processing...' : 'Đang xử lý...' }}</span>`;
            });
        }
        
        if (formRegister) {
            formRegister.addEventListener('submit', function(e) {
                const nameInput = document.getElementById('full_name');
                const emailInput = document.getElementById('reg_email');
                const passwordInput = document.getElementById('reg_password');
                const confirmInput = document.getElementById('password_confirmation');
                const acceptTerms = document.getElementById('accept_terms');
                const submitBtn = formRegister.querySelector('button[type="submit"]');
                
                // Chặn nhấn liên tiếp khi đang xử lý
                if (submitBtn.disabled) {
                    e.preventDefault();
                    return;
                }

                // Kiểm tra định dạng Gmail
                if (emailInput) {
                    const emailValue = emailInput.value.trim();
                    const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/i;
                    if (!gmailRegex.test(emailValue)) {
                        e.preventDefault();
                        showToast(
                            "{{ app()->getLocale() === 'en' ? 'Validation Error' : 'Lỗi kiểm định' }}",
                            "{{ __('ui.error_email_gmail_only') }}",
                            'error'
                        );
                        emailInput.focus();
                        return;
                    }
                }
                
                // Kiểm tra chấp nhận điều khoản dịch vụ
                if (acceptTerms && !acceptTerms.checked) {
                    e.preventDefault();
                    showToast(
                        "{{ app()->getLocale() === 'en' ? 'Terms Required' : 'Yêu cầu điều khoản' }}",
                        "{{ app()->getLocale() === 'en' ? 'You must agree to the Terms of Service and Privacy Policy.' : 'Bạn phải đồng ý với Điều khoản dịch vụ và Chính sách bảo mật.' }}",
                        'error'
                    );
                    acceptTerms.focus();
                    return;
                }
                
                // Kiểm tra trùng khớp mật khẩu ngay trên Client
                if (passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    showToast(
                        "{{ app()->getLocale() === 'en' ? 'Password Mismatch' : 'Mật khẩu không khớp' }}",
                        "{{ app()->getLocale() === 'en' ? 'The password confirmation does not match.' : 'Mật khẩu xác nhận không khớp.' }}",
                        'error'
                    );
                    confirmInput.focus();
                    return;
                }
                
                if (passwordInput.value.length < 8) {
                    e.preventDefault();
                    showToast(
                        "{{ app()->getLocale() === 'en' ? 'Validation Error' : 'Lỗi kiểm định' }}",
                        "{{ app()->getLocale() === 'en' ? 'Password must be at least 8 characters.' : 'Mật khẩu phải từ 8 ký tự trở lên.' }}",
                        'error'
                    );
                    passwordInput.focus();
                    return;
                }
                
                // Kích hoạt trạng thái đang tải & vô hiệu hóa nút bấm
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'not-allowed';
                submitBtn.innerHTML = `<span style="display:inline-flex; align-items:center; gap:8px;"><i class="fa-solid fa-spinner fa-spin"></i> {{ app()->getLocale() === 'en' ? 'Processing...' : 'Đang xử lý...' }}</span>`;
            });
        }

        /* ===== PASSWORD STRENGTH METER REAL-TIME ===== */
        const regPasswordInput = document.getElementById('reg_password');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        
        if (regPasswordInput && strengthBar && strengthText) {
            regPasswordInput.addEventListener('input', function() {
                const val = regPasswordInput.value;
                let score = 0;
                
                if (!val) {
                    strengthBar.style.width = '0%';
                    strengthText.innerText = '';
                    return;
                }
                
                // Các tiêu chí đánh giá
                if (val.length >= 8) score++;
                if (/[A-Z]/.test(val)) score++;
                if (/[a-z]/.test(val)) score++;
                if (/[0-9]/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;
                
                let width = '20%';
                let color = '#ef4444'; // Đỏ (Yếu)
                let text = "{{ app()->getLocale() === 'en' ? 'Weak' : 'Mật khẩu yếu' }}";
                
                if (val.length < 8) {
                    width = '20%';
                    color = '#ef4444';
                    text = "{{ app()->getLocale() === 'en' ? 'Too short (min 8 chars)' : 'Mật khẩu quá ngắn (tối thiểu 8 ký tự)' }}";
                } else if (score <= 2) {
                    width = '40%';
                    color = '#f97316'; // Cam (Trung bình)
                    text = "{{ app()->getLocale() === 'en' ? 'Medium strength' : 'Mật khẩu trung bình' }}";
                } else if (score === 3 || score === 4) {
                    width = '75%';
                    color = '#22c55e'; // Xanh lá (Mạnh)
                    text = "{{ app()->getLocale() === 'en' ? 'Strong' : 'Mật khẩu mạnh' }}";
                } else if (score >= 5) {
                    width = '100%';
                    color = '#3b82f6'; // Xanh dương (Rất mạnh)
                    text = "{{ app()->getLocale() === 'en' ? 'Very strong' : 'Mật khẩu cực kỳ an toàn' }}";
                }
                
                strengthBar.style.width = width;
                strengthBar.style.backgroundColor = color;
                strengthText.innerText = text;
                strengthText.style.color = color;
            });
        }

        // Tự động kích hoạt Toast nổi hiển thị lỗi từ Backend (Laravel errors bag & session error)
        @if($errors->any())
            @foreach ($errors->all() as $error)
                showToast(
                    "{{ app()->getLocale() === 'en' ? 'System Error' : 'Lỗi hệ thống' }}",
                    "{{ $error }}",
                    'error'
                );
            @endforeach
        @endif
        @if(session('error'))
            showToast(
                "{{ app()->getLocale() === 'en' ? 'System Error' : 'Lỗi hệ thống' }}",
                "{{ session('error') }}",
                'error'
            );
        @endif

        /* ===== MODAL TERMINOLOGY & PRIVACY POPUP ===== */
        const termsModal = document.getElementById('termsModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        const modalCloseBtn = document.getElementById('modalCloseBtn');
        const modalOkBtn = document.getElementById('modalOkBtn');

        const locale = "{{ app()->getLocale() }}";

        const documentTexts = {
            vi: {
                terms: {
                    title: "Điều Khoản Dịch Vụ - DienMayPro",
                    html: `
                        <p>Chào mừng bạn đến với <strong>Điện Máy Pro</strong>. Khi bạn đăng ký và sử dụng dịch vụ của chúng tôi, bạn đồng ý tuân thủ các điều khoản sau:</p>
                        <h4>1. Tài khoản & Bảo mật</h4>
                        <p>Bạn có trách nhiệm cung cấp thông tin cá nhân chính xác (Họ tên, Email, Số điện thoại) và tự bảo mật thông tin đăng nhập cá nhân của mình.</p>
                        <h4>2. Quy định Mua bán & Thanh toán</h4>
                        <p>Chúng tôi cam kết cung cấp các sản phẩm điện máy chính hãng. Các giao dịch mua hàng, hoàn tiền, thanh toán bằng ví, hoặc thẻ tín dụng phải tuân thủ đúng quy trình kiểm duyệt bảo mật.</p>
                        <h4>3. Chính sách Trả góp AI</h4>
                        <p>Khách hàng tham gia mua trả góp sẽ được hệ thống AI tự động đánh giá hồ sơ tín dụng dựa trên thứ hạng thành viên (Bronze, Silver, Gold, Diamond) và lịch sử giao dịch sạch.</p>
                        <h4>4. Quyền sở hữu trí tuệ</h4>
                        <p>Toàn bộ tài nguyên mã nguồn, hình ảnh, văn bản và logo đăng tải trên website thuộc quyền sở hữu trí tuệ của Điện Máy Pro. Nghiêm cấm mọi hành vi sao chép trái phép.</p>
                    `
                },
                privacy: {
                    title: "Chính Sách Bảo Mật - DienMayPro",
                    html: `
                        <p><strong>Điện Máy Pro</strong> cam kết bảo vệ tuyệt đối quyền riêng tư và thông tin cá nhân của khách hàng:</p>
                        <h4>1. Thu thập dữ liệu cá nhân</h4>
                        <p>Chúng tôi chỉ thu thập thông tin cần thiết phục vụ cho việc tạo tài khoản, xác thực mã OTP, đặt lịch hẹn sửa chữa thiết bị (IMEI/Serial), bảo hành sản phẩm và giao nhận đơn hàng.</p>
                        <h4>2. Sử dụng thông tin của bạn</h4>
                        <p>Thông tin cá nhân được sử dụng để liên hệ trực tiếp thông báo tiến độ hóa đơn, xử lý yêu cầu đổi điểm thưởng thành voucher, hoặc hỗ trợ định mức duyệt tín dụng trả góp AI.</p>
                        <h4>3. Lưu trữ & Mã hóa bảo mật</h4>
                        <p>Mật khẩu của bạn được mã hóa một chiều (Bcrypt/Hash) trên cơ sở dữ liệu. Mọi kết nối API được bảo vệ bằng hệ thống token Laravel Sanctum để ngăn chặn các cuộc tấn công đánh cắp phiên.</p>
                        <h4>4. Chia sẻ dữ liệu cho bên thứ ba</h4>
                        <p>Chúng tôi cam kết không bán, không chuyển nhượng dữ liệu cá nhân của bạn cho bất kỳ doanh nghiệp nào khác, ngoại trừ trường hợp được yêu cầu chính thức từ cơ quan pháp luật có thẩm quyền.</p>
                    `
                }
            },
            en: {
                terms: {
                    title: "Terms of Service - DienMayPro",
                    html: `
                        <p>Welcome to <strong>DienMayPro</strong>. By registering and utilizing our platform, you agree to the following terms and regulations:</p>
                        <h4>1. Account & Security</h4>
                        <p>You must provide accurate personal details and are fully responsible for maintaining the confidentiality of your login credentials.</p>
                        <h4>2. Purchase & Payment Regulations</h4>
                        <p>We pledge to deliver genuine electronic products. All online purchases and refund processes must comply with our strict verification standards.</p>
                        <h4>3. AI Installment Evaluation</h4>
                        <p>Credit limits and installment approval plans are dynamically evaluated by our heuristic AI algorithms based on your purchase history and membership tier.</p>
                        <h4>4. Intellectual Property Rights</h4>
                        <p>All graphical assets, logo designs, core scripts, and layouts are the exclusive property of DienMayPro. Unauthorized duplication is strictly prohibited.</p>
                    `
                },
                privacy: {
                    title: "Privacy Policy - DienMayPro",
                    html: `
                        <p>At <strong>DienMayPro</strong>, we are committed to safeguarding your personal data and privacy:</p>
                        <h4>1. Information Collection</h4>
                        <p>We collect essential information required to create accounts, verify 2FA OTP codes, record repair bookings (IMEI/Serial), and process payments.</p>
                        <h4>2. Utilization of Data</h4>
                        <p>Your details are used strictly for order tracking, repair progress emails, loyalty tier points logging, and personalized AI credit assessment.</p>
                        <h4>3. Encryption and Security</h4>
                        <p>All user passwords are securely hashed before database entry. Server requests are authenticated using Laravel Sanctum to block session hijacking.</p>
                        <h4>4. Third-Party Disclosures</h4>
                        <p>We do not sell, trade, or distribute your personal identity records to outer organizations, except when required under legal compliance orders.</p>
                    `
                }
            }
        };

        function openModal(docType) {
            const docData = documentTexts[locale] || documentTexts['vi'];
            const doc = docData[docType];
            if (doc) {
                modalTitle.innerText = doc.title;
                modalContent.innerHTML = doc.html;
                termsModal.classList.add('show');
            }
        }

        function closeModal() {
            termsModal.classList.remove('show');
        }

        termsModal.addEventListener('click', function(e) {
            if (e.target === termsModal) {
                closeModal();
            }
        });

        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
        if (modalOkBtn) modalOkBtn.addEventListener('click', closeModal);

        // Đăng ký sự kiện click trực tiếp cho các liên kết Điều khoản và Chính sách
        const termsLinks = document.querySelectorAll('.terms-label a');
        termsLinks.forEach((link, idx) => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (idx === 0) {
                    openModal('terms');
                } else {
                    openModal('privacy');
                }
            });
        });
    </script>
</body>
</html>