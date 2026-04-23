<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hệ thống bán lẻ điện thoại di động, máy tính')</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Tông màu chủ đạo giống CellphoneS / Thế Giới Di Động */
            --primary-color: #0046ab; /* Màu xanh dương đặc trưng */
            --secondary-color: #d70018; /* Màu đỏ nổi bật cho flash sale, thẻ giảm giá */
            --bg-color: #f4f6f8;
            --text-color: #333333;
            --white: #ffffff;
            --border-color: #e5e7eb;
            --hover-blue: #003380;
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

        /* Top Bar */
        .top-bar {
            background-color: var(--hover-blue);
            color: var(--white);
            font-size: 13px;
            padding: 8px 0;
        }

        .top-bar .container {
            display: flex;
            justify-content: space-between;
        }

        .top-bar span {
            margin-right: 15px;
            cursor: pointer;
        }
        
        .top-bar span:hover {
            color: #ccc;
        }

        /* Header Main */
        .header-main {
            background-color: var(--primary-color);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .logo {
            font-size: 26px;
            font-weight: 800;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
        }

        .logo span {
            color: #00d2ff; /* Tạo điểm nhấn gradient-like ở logo */
        }

        .search-bar {
            flex: 1;
            max-width: 500px;
            position: relative;
        }

        .search-bar input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .search-bar button {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 16px;
            cursor: pointer;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--white);
            font-size: 12px;
            cursor: pointer;
            padding: 5px 12px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .action-item i {
            font-size: 20px;
            margin-bottom: 3px;
        }

        .action-item:hover {
            background-color: rgba(255,255,255,0.15);
        }

        /* Footer */
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
            color: var(--hover-blue);
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

    @stack('scripts')
</body>
</html>
