@extends('layouts.app')

@section('title', 'Góc Video Trải Nghiệm Công Nghệ')

@push('styles')
<style>
    /* ============================================================
     * PREMIUM LIGHT MODE - MINIMAL & CLEAN VIDEO PORTAL SYSTEM
     * ============================================================
     */
    .video-page-shell {
        background: radial-gradient(at 0% 0%, rgba(224, 242, 254, 0.5) 0px, transparent 50%),
                    radial-gradient(at 100% 0%, rgba(243, 232, 255, 0.5) 0px, transparent 50%),
                    #f8fafc;
        color: #1e293b;
        position: relative;
        min-height: 100vh;
    }

    .video-hero {
        background: linear-gradient(135deg, #f0f7ff 0%, #e0f2fe 100%);
        border: 1px solid rgba(0, 70, 171, 0.1);
        border-radius: 32px;
        overflow: hidden;
        box-shadow: 0 15px 35px -15px rgba(0, 70, 171, 0.08);
        position: relative;
    }

    /* Bat dau xem ngay button style */
    .btn-start-watch {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 9999px;
        background: #0046ab;
        border: 2px solid #0046ab;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 15px rgba(0, 70, 171, 0.2);
        cursor: pointer;
        text-decoration: none !important;
    }

    .btn-start-watch:hover {
        background: #003380;
        border-color: #003380;
        color: #ffffff !important;
        box-shadow: 0 10px 25px rgba(0, 70, 171, 0.35);
        transform: translateY(-2px);
    }
    
    .btn-start-watch i {
        color: #ffffff;
        transition: all 0.3s ease;
    }
    
    .btn-start-watch:hover i {
        transform: scale(1.15) rotate(15deg);
    }

    /* Quay lai mua sam button style */
    .btn-back-to-shop {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 9999px;
        border: 2px solid #ff7a00;
        background: transparent;
        color: #ff7a00;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 4px 10px rgba(255, 122, 0, 0.05);
        cursor: pointer;
        text-decoration: none !important;
    }

    .btn-back-to-shop:hover {
        background: #ff7a00;
        color: #ffffff !important;
        border-color: #ff7a00;
        box-shadow: 0 10px 20px rgba(255, 122, 0, 0.2);
        transform: translateY(-2px);
    }
    
    .btn-back-to-shop i {
        color: #ff7a00;
        transition: all 0.3s ease;
    }
    
    .btn-back-to-shop:hover i {
        color: #ffffff !important;
        transform: translateX(-4px);
    }

    /* Ambient soft glow inside hero */
    .video-hero::before {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(0, 70, 171, 0.05) 0%, transparent 70%);
        top: -100px;
        right: -50px;
        pointer-events: none;
    }

    .video-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 26px;
        box-shadow: 0 10px 30px -15px rgba(0, 70, 171, 0.04);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .video-card:hover {
        transform: translateY(-6px);
        border-color: rgba(0, 70, 171, 0.25);
        box-shadow: 0 20px 40px -15px rgba(0, 70, 171, 0.12);
    }

    .video-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(0, 70, 171, 0.06);
        border: 1px solid rgba(0, 70, 171, 0.1);
        color: #0046ab;
    }

    .video-thumb {
        position: relative;
        aspect-ratio: 16 / 9;
        overflow: hidden;
        border-radius: 18px;
        background: #f1f5f9;
        border: 1px solid rgba(0, 70, 171, 0.05);
    }

    .video-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .video-card:hover .video-thumb img {
        transform: scale(1.06);
    }

    .video-play-overlay {
        position: absolute;
        inset: 0;
        display: grid;
        place-items: center;
        background: rgba(15, 23, 42, 0.15);
        transition: background 0.4s ease;
    }

    .video-card:hover .video-play-overlay {
        background: rgba(15, 23, 42, 0.3);
    }

    /* Pulsing glowing play button */
    .video-play-btn {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: #ffffff;
        color: #0046ab;
        box-shadow: 0 10px 25px -10px rgba(0, 70, 171, 0.25);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        transform: scale(0.9);
        position: relative;
    }

    .video-play-btn i {
        margin-left: 4px;
    }

    .video-play-btn::after {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        border: 2px solid rgba(0, 70, 171, 0.4);
        opacity: 0;
        transform: scale(0.8);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .video-card:hover .video-play-btn {
        transform: scale(1);
        background: #0046ab;
        color: #ffffff;
        box-shadow: 0 15px 30px -5px rgba(0, 70, 171, 0.35);
    }

    .video-card:hover .video-play-btn::after {
        opacity: 1;
        transform: scale(1.18);
        animation: ring-pulse-glow 1.8s infinite;
    }

    @keyframes ring-pulse-glow {
        0% {
            transform: scale(1);
            opacity: 0.9;
        }
        100% {
            transform: scale(1.45);
            opacity: 0;
        }
    }

    /* EXPANDED VIDEO PLAYER CINEMA FRAME (Widescreen Cinema sizing) */
    .video-player-frame {
        position: relative;
        aspect-ratio: 16 / 9;
        min-height: 400px; /* Shrunk down as requested */
        border-radius: 24px;
        overflow: hidden;
        background: #000000;
        box-shadow: 0 20px 50px -15px rgba(0, 70, 171, 0.15);
        border: 1px solid rgba(0, 70, 171, 0.08);
    }
    
    @media (max-width: 1024px) {
        .video-player-frame {
            min-height: 320px;
        }
    }
    @media (max-width: 768px) {
        .video-player-frame {
            min-height: 220px;
        }
    }

    .video-playlist-container {
        border: 1px solid rgba(226, 232, 240, 0.8);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(24px);
        border-radius: 28px;
        box-shadow: 0 10px 35px -15px rgba(0, 70, 171, 0.05);
    }

    .playlist-scroll {
        max-height: 480px;
        overflow-y: auto;
    }

    .playlist-item {
        border: 1px solid rgba(226, 232, 240, 0.5);
        background: rgba(255, 255, 255, 0.5);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        border-left: 3px solid transparent;
    }

    .playlist-item:hover {
        background: rgba(0, 70, 171, 0.02);
        border-color: rgba(0, 70, 171, 0.1);
        transform: translateX(4px);
    }

    .playlist-item.active {
        background: linear-gradient(90deg, #f0f7ff 0%, rgba(255, 255, 255, 0.5) 100%);
        border-color: rgba(0, 70, 171, 0.2);
        border-left-color: #0046ab;
        box-shadow: 0 4px 20px -5px rgba(0, 70, 171, 0.08);
    }

    .playlist-item.active h4 {
        color: #0046ab !important;
    }

    .playlist-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .playlist-scroll::-webkit-scrollbar-track {
        background: transparent;
    }
    .playlist-scroll::-webkit-scrollbar-thumb {
        background: rgba(0, 70, 171, 0.15);
        border-radius: 10px;
    }
    .playlist-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 70, 171, 0.3);
    }

    /* Custom Thin Scrollbar for Category Filter */
    .custom-scrollbar::-webkit-scrollbar {
        height: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.02);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(0, 70, 171, 0.15);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(0, 70, 171, 0.3);
    }
    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 70, 171, 0.15) rgba(0, 0, 0, 0.02);
    }

    /* Neon Soundwave Audio Equalizer */
    .equalizer-container {
        display: none;
        align-items: flex-end;
        gap: 2.5px;
        width: 14px;
        height: 12px;
    }

    .playlist-item.active .equalizer-container {
        display: inline-flex;
    }

    .eq-bar {
        width: 2.5px;
        background-color: #0046ab;
        border-radius: 1px;
        animation: eq-bounce 0.8s ease-in-out infinite alternate;
        transform-origin: bottom;
    }

    .eq-bar-1 { height: 100%; animation-delay: 0.1s; }
    .eq-bar-2 { height: 60%; animation-delay: 0.3s; }
    .eq-bar-3 { height: 80%; animation-delay: 0.5s; }

    @keyframes eq-bounce {
        0% {
            transform: scaleY(0.25);
        }
        100% {
            transform: scaleY(1);
        }
    }

    /* High-tech pill switcher */
    .source-switcher-btn {
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        padding-bottom: 6px;
    }

    .source-switcher-btn::after {
        content: '';
        position: absolute;
        width: 4px;
        height: 4px;
        background-color: currentColor;
        border-radius: 50%;
        bottom: 2px;
        left: 50%;
        transform: translateX(-50%) scale(0);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .source-switcher-btn.active::after {
        transform: translateX(-50%) scale(1);
    }

    /* Heart POP action */
    @keyframes heart-pop-scale {
        0% { transform: scale(1); }
        50% { transform: scale(1.45); }
        100% { transform: scale(1); }
    }

    .heart-pop {
        animation: heart-pop-scale 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    /* Text details overrides */
    .text-secondary {
        color: #ffcf00 !important;
    }
    .bg-secondary {
        background-color: #ffcf00 !important;
    }
    .text-primary {
        color: #0046ab !important;
    }
    .text-danger {
        color: #ef4444 !important;
    }

    /* Grid configuration utilities */
    .grid {
        display: grid;
    }
    .grid-cols-1 {
        grid-template-columns: repeat(1, minmax(0, 1fr));
    }
    .gap-4 { gap: 1rem; }
    .gap-6 { gap: 1.5rem; }
    .gap-8 { gap: 2rem; }
    
    @media (min-width: 640px) {
        .sm\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        .lg\:grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .lg\:grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .lg\:col-span-2 {
            grid-column: span 2 / span 2;
        }
    }

    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
    .animate-bounce {
        animation: bounce 1.2s infinite;
    }
    @keyframes bounce {
        0%, 100% {
            transform: translateY(-12%);
            animation-timing-function: cubic-bezier(0.8,0,1,1);
        }
        50% {
            transform: none;
            animation-timing-function: cubic-bezier(0,0,0.2,1);
        }
    }
</style>
@endpush

@section('content')
@php
    // Helper closures to construct proper asset paths
    $getVideoUrl = function($video) {
        if (!$video) return '';
        if ($video->video_path) {
            return route('videos.stream', $video->id);
        }
        return '';
    };

    $getThumbUrl = function($path) {
        if (!$path) {
            return 'https://images.unsplash.com/photo-1611162617213-7d7a39e9b1d7?auto=format&fit=crop&w=800&q=80';
        }
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        return asset('storage/' . $path);
    };

    // Calculate Hero Section Videos
    $hero_video_1 = $videos->get(1) ?? $videos->first();
    $hero_video_2 = $videos->get(4) ?? $videos->get(2) ?? $videos->first();

    // Default active featured video
    $featured_video = $videos->first();
    $selected_id = request()->integer('id');
    if ($selected_id > 0) {
        $found = $videos->firstWhere('id', $selected_id);
        if ($found) {
            $featured_video = $found;
        }
    }
@endphp

<div class="video-page-shell min-h-screen pb-20">
    @if($videos->isEmpty())
        <div class="container mx-auto px-4 py-16 flex items-center justify-center min-h-[70vh]">
            <div class="text-center px-6 py-12 max-w-lg bg-white rounded-3xl border border-blue-100 shadow-xl mx-4 transform hover:scale-[1.01] transition duration-300">
                <div class="w-28 h-28 mx-auto mb-6 bg-red-50 rounded-full flex items-center justify-center text-red-500 border border-red-100 relative">
                    <i class="fa-solid fa-video-slash text-4xl animate-bounce"></i>
                    <span class="absolute top-1 right-1 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 border-2 border-white"></span>
                    </span>
                </div>
                <h2 class="text-2xl font-black text-gray-800 mb-2">Góc Video đang được cập nhật</h2>
                <p class="text-gray-500 text-sm leading-relaxed mb-6">
                    Hiện tại danh sách video review thiết bị đã được làm trống để cập nhật các nội dung mới. Vui lòng quay lại sau hoặc liên hệ Admin để thêm video review!
                </p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary text-white font-bold hover:bg-blue-800 transition shadow-md">
                        <i class="fa-solid fa-house"></i> Quay về Trang chủ
                    </a>
                    <a href="{{ url('/#support') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-slate-50 border border-gray-200 text-gray-600 font-semibold hover:bg-slate-100 transition">
                        <i class="fa-solid fa-headset text-primary"></i> Trợ giúp trực tuyến
                    </a>
                </div>
            </div>
        </div>
    @else
        <main class="container mx-auto px-4 py-6 md:py-10">
            
            <!-- HERO SECTION -->
            <section class="video-hero p-6 md:p-10 mb-10">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <div class="video-pill mb-4">
                            <i class="fa-solid fa-play-circle animate-pulse"></i>
                            Góc Video Công Nghệ
                        </div>
                        <h1 class="text-3xl md:text-5xl font-black leading-tight mb-4 text-gray-900">
                            Trực quan sinh động<br>
                            <span class="text-primary">Trải nghiệm đỉnh cao</span>
                        </h1>
                        <p class="text-gray-600 text-sm md:text-base leading-7 max-w-xl">
                            Xem cận cảnh, đánh giá chi tiết các thiết bị điện máy, điện tử thông minh và học các mẹo vặt sử dụng hiệu quả từ chuyên gia hàng đầu của DienMayPro.
                        </p>

                        <div class="flex flex-wrap gap-3 mt-6">
                            <a href="#featured-video" class="btn-start-watch">
                                <i class="fa-solid fa-circle-play"></i> Bắt đầu xem ngay
                            </a>
                            <a href="{{ url('/') }}" class="btn-back-to-shop">
                                <i class="fa-solid fa-arrow-left"></i> Quay lại mua sắm
                            </a>
                        </div>
                    </div>

                    <!-- 2 Video nổi bật ở Hero -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($hero_video_1)
                            <div class="video-card p-4 cursor-pointer" onclick="playVideo({{ $hero_video_1->id }})">
                                <div class="video-thumb mb-3">
                                    <img src="{{ $getThumbUrl($hero_video_1->thumbnail_path) }}" alt="Video thumb">
                                    <div class="video-play-overlay">
                                        <span class="video-play-btn"><i class="fa-solid fa-play text-lg pl-0.5"></i></span>
                                    </div>
                                    <span class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                                        {{ $hero_video_1->duration ?: '0:00' }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold text-primary bg-blue-50 px-2 py-0.5 rounded-full uppercase">
                                    {{ $hero_video_1->category ?: 'REVIEW' }}
                                </span>
                                <h3 class="font-bold text-gray-800 text-sm mt-1.5 line-clamp-1">{{ $hero_video_1->title }}</h3>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $hero_video_1->description ?: 'Không có mô tả' }}</p>
                            </div>
                        @endif

                        @if($hero_video_2)
                            <div class="video-card p-4 mt-0 sm:mt-8 cursor-pointer" onclick="playVideo({{ $hero_video_2->id }})">
                                <div class="video-thumb mb-3">
                                    <img src="{{ $getThumbUrl($hero_video_2->thumbnail_path) }}" alt="Video thumb">
                                    <div class="video-play-overlay">
                                        <span class="video-play-btn"><i class="fa-solid fa-play text-lg pl-0.5"></i></span>
                                    </div>
                                    <span class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] font-bold px-1.5 py-0.5 rounded">
                                        {{ $hero_video_2->duration ?: '0:00' }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-bold text-primary bg-blue-50 px-2 py-0.5 rounded-full uppercase">
                                    {{ $hero_video_2->category ?: 'REVIEW' }}
                                </span>
                                <h3 class="font-bold text-gray-800 text-sm mt-1.5 line-clamp-1">{{ $hero_video_2->title }}</h3>
                                <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $hero_video_2->description ?: 'Không có mô tả' }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            @php
                // Get all root categories from database
                $all_categories = [];
                foreach($categories as $cat) {
                    $all_categories[$cat->category_id] = $cat->name;
                }
                
                // Add any missing custom category strings from the videos list if they don't match db names
                foreach($videos as $v) {
                    if (!$v->category_id && $v->category) {
                        $matches_db = false;
                        foreach($all_categories as $db_name) {
                            if (mb_strtolower($db_name) === mb_strtolower($v->category)) {
                                $matches_db = true;
                                break;
                            }
                        }
                        if (!$matches_db && !in_array($v->category, $all_categories)) {
                            $all_categories[$v->category] = $v->category;
                        }
                    }
                }
            @endphp

            <!-- CATEGORY FILTER PILLS -->
            <div class="mb-8 relative z-10">
                <h2 class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-eye text-primary"></i> Xem theo danh mục
                </h2>
                <div class="relative">
                    <!-- Premium Fade Gradient Indicators -->
                    <div class="absolute top-0 right-0 bottom-0 w-12 bg-gradient-to-l from-[#f8fafc] to-transparent pointer-events-none z-10"></div>
                    <div class="absolute top-0 left-0 bottom-0 w-6 bg-gradient-to-r from-[#f8fafc] to-transparent pointer-events-none z-10"></div>

                    <div id="category-filter-scroll" class="flex items-center gap-2 overflow-x-auto pb-2 pr-14 custom-scrollbar">
                        <button onclick="filterCategory('all')" id="cat-tab-all" class="category-filter-tab px-5 py-2.5 rounded-full font-bold text-xs transition-all duration-300 shadow-sm bg-primary text-white border border-transparent whitespace-nowrap shrink-0">
                            <i class="fa-solid fa-layer-group mr-1.5"></i> Tất cả
                        </button>
                        @foreach($all_categories as $key => $name)
                            <button onclick="filterCategory('{{ $key }}')" id="cat-tab-{{ $key }}" class="category-filter-tab px-5 py-2.5 rounded-full font-bold text-xs transition-all duration-300 shadow-sm bg-white text-gray-600 hover:bg-slate-50 border border-gray-150 whitespace-nowrap shrink-0">
                                {{ $name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- MAIN INTERACTIVE PLAYER & PLAYLIST -->
            <section id="featured-video" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cột chính bên trái: Trình phát & Bình luận (Chiếm 2 cột) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Trình phát video chính -->
                    <div class="video-card p-4 md:p-6 flex flex-col justify-between">
                        <!-- Header của Trình phát -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span id="player-category" class="px-2.5 py-1 rounded-full bg-blue-50 text-primary text-xs font-bold uppercase tracking-wider">
                                    {{ $featured_video->category ?: 'Smart Home' }}
                                </span>
                                <span class="px-2.5 py-1 rounded-full bg-red-50 text-danger text-xs font-bold animate-pulse">
                                    🔥 Đang chiếu
                                </span>
                            </div>

                            @php
                                $hasMp4 = !empty($featured_video->video_path);
                                $hasYoutube = !empty($featured_video->youtube_url);
                                $defaultSource = $hasMp4 ? 'mp4' : ($hasYoutube ? 'youtube' : 'mp4');
                            @endphp
                            <!-- BỘ CHUYỂN NGUỒN PHÁT DỰ PHÒNG -->
                            <div id="source-switcher" class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl self-start text-[11px] font-bold text-gray-700 {{ (!$hasMp4 || !$hasYoutube) ? 'hidden' : '' }}">
                                <span class="px-1.5 text-gray-500 uppercase text-[9px]">Nguồn:</span>
                                <button id="src-mp4-btn" onclick="switchSource('mp4')" class="px-2.5 py-1 rounded-lg {{ $defaultSource === 'mp4' ? 'bg-primary text-white shadow-sm active' : 'hover:bg-white text-primary' }} source-switcher-btn">
                                    <i class="fa-solid fa-file-video mr-1"></i> MP4
                                </button>
                                <button id="src-yt-btn" onclick="switchSource('youtube')" class="px-2.5 py-1 rounded-lg {{ $defaultSource === 'youtube' ? 'bg-primary text-white shadow-sm active' : 'hover:bg-white text-red-600' }} source-switcher-btn">
                                    <i class="fa-brands fa-youtube mr-1"></i> YouTube
                                </button>
                            </div>
                        </div>

                        <!-- EXPANDED Video Cinema Frame -->
                        <div class="video-player-frame mb-4">
                            <!-- NGUỒN 1: HTML5 VIDEO PLAYER (MP4 trực tiếp) -->
                            <div id="mp4-player-container" class="w-full h-full {{ $defaultSource === 'youtube' ? 'hidden' : '' }}">
                                <video id="main-video-player" class="w-full h-full object-cover animate-fade-in" controls preload="auto" poster="{{ $getThumbUrl($featured_video->thumbnail_path) }}" src="{{ $getVideoUrl($featured_video) }}">
                                    Trình duyệt của bạn không hỗ trợ phát video HTML5.
                                </video>
                            </div>

                            <!-- NGUỒN 2: YOUTUBE EMBED PLAYER -->
                            <div id="youtube-player-container" class="w-full h-full {{ $defaultSource === 'youtube' ? '' : 'hidden' }}">
                                <iframe id="main-youtube-player" class="w-full h-full border-0 absolute inset-0" src="{{ $defaultSource === 'youtube' ? ($featured_video->youtube_url . (str_contains($featured_video->youtube_url, '?') ? '&' : '?') . 'rel=0') : '' }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </div>

                        <!-- Video Info -->
                        <div class="flex-grow flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start gap-4">
                                    <h2 id="player-title" class="text-xl md:text-2xl font-extrabold text-gray-900 leading-tight">
                                        {{ $featured_video->title }}
                                    </h2>
                                    <span id="player-views" class="text-xs text-gray-500 font-medium shrink-0 mt-1">
                                        <i class="fa-solid fa-eye text-primary mr-1"></i> {{ number_format($featured_video->views) }} lượt xem
                                    </span>
                                </div>
                                <p id="player-desc" class="text-gray-600 mt-3 leading-relaxed text-sm md:text-base">
                                    {{ $featured_video->description ?: 'Chưa có mô tả chi tiết cho video này.' }}
                                </p>
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-clock"></i>
                                    <span id="player-date">Cập nhật ngày: {{ optional($featured_video->published_at ?: $featured_video->created_at)->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <!-- NÚT THÍCH SINH ĐỘNG -->
                                    <button id="like-btn" onclick="toggleLikeCurrentVideo()" class="flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-gray-700 font-bold transition-all relative overflow-visible group">
                                        <i id="like-icon" class="fa-regular fa-heart text-red-500 group-hover:scale-110 transition-transform duration-200"></i>
                                        <span id="like-text" style="font-weight: 700;">Thích</span>
                                        <span id="like-count" class="bg-white px-2 py-0.5 rounded-full text-[10px] text-gray-500 border border-gray-200/50 shadow-sm font-extrabold">{{ $featured_video->likes }}</span>
                                    </button>

                                    <!-- NÚT CHIA SẺ -->
                                    <button id="share-btn" onclick="shareCurrentVideo()" class="flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-gray-700 font-bold transition-all">
                                        <i class="fa-solid fa-share text-primary"></i>
                                        <span style="font-weight: 700;">Chia sẻ</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Khu vực bình luận -->
                    <div class="video-card p-5 md:p-6">
                        <h3 class="font-extrabold text-gray-900 text-base mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-comments text-primary"></i> Bình luận
                            <span id="comment-count-badge" class="text-xs font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">0</span>
                        </h3>

                        <!-- Viết bình luận mới -->
                        <div class="mb-6">
                            @auth
                                <form id="comment-form" onsubmit="submitComment(event)" class="flex gap-3 items-start">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center font-bold text-primary shrink-0 border border-primary/20">
                                        {{ mb_substr(auth()->user()->full_name ?? auth()->user()->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div class="flex-grow">
                                        <textarea id="comment-textarea" placeholder="Viết bình luận của bạn..." class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition resize-none h-20" required></textarea>
                                        <div class="flex justify-end mt-2">
                                            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-xl text-xs font-bold hover:bg-blue-800 transition shadow-sm flex items-center gap-1.5">
                                                <i class="fa-solid fa-paper-plane"></i> Gửi bình luận
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @else
                                <div class="bg-slate-50 border border-gray-150 rounded-xl p-4 text-center text-sm text-gray-500">
                                    Vui lòng <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">Đăng nhập</a> để tham gia bình luận.
                                </div>
                            @endauth
                        </div>

                        <!-- Danh sách bình luận -->
                        <div id="comments-list" class="space-y-4 max-h-[400px] overflow-y-auto pr-1">
                            <!-- Danh sách bình luận sẽ được tải bằng JS -->
                        </div>
                    </div>
                </div>

                <!-- Danh sách Playlist bên phải (Chiếm 1 cột) -->
                <aside class="space-y-4">
                    <div class="video-playlist-container p-4">
                        <h3 class="font-extrabold text-gray-900 text-base mb-3 pb-3 border-b border-gray-100 flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-list-ul text-primary"></i> Playlist bài viết
                            </span>
                            <span id="playlist-video-count" class="text-xs font-bold text-gray-400 bg-gray-155 px-2.5 py-0.5 rounded-full">
                                {{ $videos->count() }} Video
                            </span>
                        </h3>

                        <div class="playlist-scroll space-y-2 pr-1 flex flex-col gap-2">
                            @foreach($videos as $v)
                                @php
                                    $parentId = null;
                                    if ($v->categoryRel) {
                                        $parentId = $v->categoryRel->parent_id;
                                    }
                                @endphp
                                <button id="playlist-item-{{ $v->id }}" onclick="playVideo({{ $v->id }})" data-category-id="{{ $v->category_id }}" data-parent-id="{{ $parentId }}" data-category-name="{{ $v->category }}" data-video-id="{{ $v->id }}" class="w-full text-left playlist-item p-2.5 flex gap-3 items-center rounded-xl hover:bg-slate-50 transition-all border border-gray-100 {{ $v->id == $featured_video->id ? 'active' : '' }}">
                                    <div class="w-24 h-14 rounded-lg overflow-hidden shrink-0 bg-blue-50 relative shadow-sm border border-gray-200/40">
                                        <img src="{{ $getThumbUrl($v->thumbnail_path) }}" class="w-full h-full object-cover" alt="thumbnail">
                                        <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[9px] font-bold px-1 py-0.5 rounded leading-none">
                                            {{ $v->duration ?: '0:00' }}
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <span class="text-[9px] font-bold text-primary uppercase tracking-wider block leading-none mb-1">
                                            {{ $v->category ?: 'REVIEW' }}
                                        </span>
                                        <h4 class="font-extrabold text-gray-800 text-xs line-clamp-2 leading-snug">{{ $v->title }}</h4>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[10px] text-gray-400"><i class="fa-solid fa-eye text-[9px]"></i> {{ number_format($v->views) }}</span>
                                            <span class="text-[10px] text-gray-400 ms-2"><i class="fa-solid fa-heart text-red-500 text-[9px]"></i> <span>{{ number_format($v->likes) }}</span></span>
                                            <!-- Animated Neon Soundwave -->
                                            <div class="equalizer-container ms-auto">
                                                <div class="eq-bar eq-bar-1"></div>
                                                <div class="eq-bar eq-bar-2"></div>
                                                <div class="eq-bar eq-bar-3"></div>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                            <div id="playlist-empty-message" class="hidden py-8 text-center text-xs text-gray-400">
                                <i class="fa-solid fa-folder-open text-xl mb-2 block text-gray-300"></i>
                                Không có video nào trong danh mục này.
                            </div>
                        </div>
                    </div>

                    <!-- Banner thông điệp đi kèm -->
                    <div class="video-card p-5 bg-gradient-to-br from-blue-50 to-white flex flex-col justify-between h-48 border border-blue-100">
                        <div>
                            <h3 class="font-black text-gray-800 flex items-center gap-2">
                                <i class="fa-solid fa-headset text-primary animate-bounce"></i>
                                Tư vấn qua Video
                            </h3>
                            <p class="text-xs text-gray-600 leading-relaxed mt-2">
                                Mọi sản phẩm của Điện Máy Pro đều được đội ngũ kỹ thuật viên review chân thực và hướng dẫn sử dụng chi tiết nhất giúp bạn hoàn toàn an tâm mua sắm.
                            </p>
                        </div>
                        <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-blue-800 transition shadow-md">
                            Mua sắm sản phẩm ngay <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </aside>
            </section>
        </main>
    @endif
</div>
@endsection

@push('scripts')
<script>
    const videosData = @json($videos);
    const csrfToken = "{{ csrf_token() }}";
    const currentUserId = @json(auth()->id());
    const currentUserRole = @json(auth()->check() ? auth()->user()->role_id : null);
    let currentSourceType = '{{ $defaultSource }}';
    let currentVideoId = @json($featured_video ? $featured_video->id : 0);
    let activeFilter = 'all';

    function filterCategory(key) {
        activeFilter = key;
        
        // 1. Update tab styles
        document.querySelectorAll('.category-filter-tab').forEach(tab => {
            tab.classList.remove('bg-primary', 'text-white', 'border-transparent');
            tab.classList.add('bg-white', 'text-gray-600', 'border', 'border-gray-150');
        });
        
        const activeTab = document.getElementById(`cat-tab-${key}`);
        if (activeTab) {
            activeTab.classList.remove('bg-white', 'text-gray-600', 'border', 'border-gray-150');
            activeTab.classList.add('bg-primary', 'text-white', 'border-transparent');
        }

        // 2. Filter playlist items
        let firstVisibleVideoId = null;
        let isCurrentVideoVisible = false;
        let visibleCount = 0;

        document.querySelectorAll('.playlist-item').forEach(item => {
            const catId = item.getAttribute('data-category-id');
            const parentId = item.getAttribute('data-parent-id');
            const catName = item.getAttribute('data-category-name');
            const videoId = item.getAttribute('data-video-id');

            const matches = (key === 'all') || (catId === key) || (parentId === key) || (catName === key);
            
            if (matches) {
                item.style.setProperty('display', 'flex', 'important');
                visibleCount++;
                if (!firstVisibleVideoId) {
                    firstVisibleVideoId = videoId;
                }
                if (videoId == currentVideoId) {
                    isCurrentVideoVisible = true;
                }
            } else {
                item.style.setProperty('display', 'none', 'important');
            }
        });

        // Update video count element
        const countSpan = document.getElementById('playlist-video-count');
        if (countSpan) {
            countSpan.textContent = `${visibleCount} Video`;
        }

        // Toggle empty playlist message
        const emptyMsg = document.getElementById('playlist-empty-message');
        if (emptyMsg) {
            if (visibleCount === 0) {
                emptyMsg.classList.remove('hidden');
            } else {
                emptyMsg.classList.add('hidden');
            }
        }

        // 3. If current video is no longer visible under this category, automatically load the first visible video
        if (!isCurrentVideoVisible && firstVisibleVideoId) {
            playVideo(firstVisibleVideoId, false);
        } else {
            const playerElem = document.getElementById('featured-video');
            if (playerElem) {
                const headerOffset = 90;
                const elementPosition = playerElem.getBoundingClientRect().top + window.scrollY;
                const offsetPosition = elementPosition - headerOffset;
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }
    }

    function switchSource(type) {
        currentSourceType = type;
        const mp4Btn = document.getElementById('src-mp4-btn');
        const ytBtn = document.getElementById('src-yt-btn');
        const mp4Container = document.getElementById('mp4-player-container');
        const ytContainer = document.getElementById('youtube-player-container');
        const mp4Player = document.getElementById('main-video-player');
        const ytPlayer = document.getElementById('main-youtube-player');

        const video = videosData.find(v => v.id == currentVideoId);
        if (!video) return;

        if (type === 'mp4') {
            mp4Btn.classList.add('bg-primary', 'text-white', 'shadow-sm', 'active');
            mp4Btn.classList.remove('hover:bg-white', 'text-primary');
            ytBtn.classList.add('hover:bg-white', 'text-red-600');
            ytBtn.classList.remove('bg-primary', 'text-white', 'shadow-sm', 'active');

            mp4Container.classList.remove('hidden');
            ytContainer.classList.add('hidden');

            ytPlayer.src = '';
            mp4Player.src = video.video_url;
            mp4Player.load();
            mp4Player.play().catch(() => {});
        } else {
            ytBtn.classList.add('bg-primary', 'text-white', 'shadow-sm', 'active');
            ytBtn.classList.remove('hover:bg-white', 'text-red-600');
            mp4Btn.classList.add('hover:bg-white', 'text-primary');
            mp4Btn.classList.remove('bg-primary', 'text-white', 'shadow-sm', 'active');

            ytContainer.classList.remove('hidden');
            mp4Container.classList.add('hidden');

            mp4Player.pause();
            let ytEmbed = video.youtube_url;
            if (ytEmbed && !ytEmbed.includes('autoplay=')) {
                ytEmbed += (ytEmbed.includes('?') ? '&' : '?') + 'autoplay=1&rel=0';
            }
            ytPlayer.src = ytEmbed;
        }
    }

    function getFullUrl(path) {
        if (!path) return '';
        if (path.startsWith('http://') || path.startsWith('https://')) {
            return path;
        }
        return `{{ asset('storage') }}/${path}`;
    }

    function showToast(message, type = 'success') {
        const oldToast = document.getElementById('custom-toast');
        if (oldToast) oldToast.remove();

        const toast = document.createElement('div');
        toast.id = 'custom-toast';
        
        // Base inline styles for the toast to guarantee visual presentation and z-index level
        toast.style.position = 'fixed';
        toast.style.bottom = '24px';
        toast.style.right = '24px';
        toast.style.zIndex = '999999';
        toast.style.display = 'flex';
        toast.style.alignItems = 'center';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '12px';
        toast.style.color = '#ffffff';
        toast.style.fontSize = '13px';
        toast.style.fontWeight = 'bold';
        toast.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.2)';
        toast.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        toast.style.transform = 'translateY(20px)';
        toast.style.opacity = '0';
        toast.style.border = '1px solid rgba(255, 255, 255, 0.15)';
        toast.style.backdropFilter = 'blur(8px)';
        
        let bgColor = '#10b981'; // Emerald 500
        let icon = '<i class="fa-solid fa-circle-check" style="margin-right: 8px; color: #a7f3d0; font-size: 14px;"></i>';
        
        if (type === 'error') {
            bgColor = '#f43f5e'; // Rose 500
            icon = '<i class="fa-solid fa-circle-exclamation" style="margin-right: 8px; color: #fecdd3; font-size: 14px;"></i>';
        } else if (type === 'info') {
            bgColor = '#3b82f6'; // Blue 500
            icon = '<i class="fa-solid fa-circle-info" style="margin-right: 8px; color: #bfdbfe; font-size: 14px;"></i>';
        }
        
        toast.style.backgroundColor = bgColor;
        toast.innerHTML = `${icon}<span>${message}</span>`;
        
        document.body.appendChild(toast);
        
        // Force reflow
        toast.offsetHeight;
        
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
        
        setTimeout(() => {
            toast.style.transform = 'translateY(20px)';
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // Dynamic cache for loaded liked actions
    function updateLikeButtonUI(videoId) {
        const isLiked = localStorage.getItem('liked_video_' + videoId) === 'true';
        const likeIcon = document.getElementById('like-icon');
        const likeBtn = document.getElementById('like-btn');
        const likeCountSpan = document.getElementById('like-count');

        const video = videosData.find(v => v.id == videoId);
        if (!video) return;

        let baseLikes = parseInt(video.likes || 0);

        if (isLiked) {
            likeIcon.className = "fa-solid fa-heart text-red-500 scale-110";
            likeBtn.className = "flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-red-50 hover:bg-red-100 text-red-700 font-bold transition-all relative overflow-visible group border border-red-200/50 shadow-sm";
        } else {
            likeIcon.className = "fa-regular fa-heart text-red-500";
            likeBtn.className = "flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-gray-700 font-bold transition-all relative overflow-visible group";
        }

        likeCountSpan.innerText = baseLikes;

        // Update likes count in the sidebar playlist item
        const itemLikes = document.querySelector(`#playlist-item-${videoId} .fa-heart`);
        if (itemLikes && itemLikes.nextElementSibling) {
            itemLikes.nextElementSibling.textContent = baseLikes.toLocaleString();
        }
    }

    function toggleLikeCurrentVideo() {
        if (!currentVideoId) return;

        const isLiked = localStorage.getItem('liked_video_' + currentVideoId) === 'true';
        const action = isLiked ? 'unlike' : 'like';

        const likeIcon = document.getElementById('like-icon');
        likeIcon.classList.add('heart-pop');
        setTimeout(() => likeIcon.classList.remove('heart-pop'), 450);

        fetch(`/videos/${currentVideoId}/like`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ action: action })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const video = videosData.find(v => v.id == currentVideoId);
                if (video) {
                    video.likes = data.likes;
                }

                if (isLiked) {
                    localStorage.setItem('liked_video_' + currentVideoId, 'false');
                    showToast('Đã bỏ thích video.', 'info');
                } else {
                    localStorage.setItem('liked_video_' + currentVideoId, 'true');
                    showToast('Cảm ơn bạn đã thích video này! ❤️', 'success');
                }

                updateLikeButtonUI(currentVideoId);
            }
        })
        .catch(() => {
            showToast('Không thể kết nối máy chủ.', 'error');
        });
    }

    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.opacity = "0";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                return successful ? Promise.resolve() : Promise.reject();
            } catch (err) {
                document.body.removeChild(textArea);
                return Promise.reject(err);
            }
        }
    }

    function shareCurrentVideo() {
        if (!currentVideoId) return;

        const shareUrl = window.location.origin + window.location.pathname + '?id=' + currentVideoId;

        copyToClipboard(shareUrl).then(() => {
            showToast('Đã sao chép liên kết', 'success');
        }).catch(() => {
            showToast('Không thể sao chép, liên kết: ' + shareUrl, 'error');
        });
    }

    function incrementViews(videoId) {
        fetch(`/videos/${videoId}/view`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const video = videosData.find(v => v.id == videoId);
                if (video) {
                    video.views = data.views;
                }
                const viewsSpan = document.getElementById('player-views');
                if (viewsSpan && currentVideoId == videoId) {
                    viewsSpan.innerHTML = `<i class="fa-solid fa-eye text-primary mr-1"></i> ${Number(data.views).toLocaleString()} lượt xem`;
                }
                // Update views count in the sidebar
                const itemViews = document.querySelector(`#playlist-item-${videoId} .fa-eye`);
                if (itemViews && itemViews.nextSibling) {
                    itemViews.nextSibling.textContent = ` ${Number(data.views).toLocaleString()}`;
                }
            }
        })
        .catch(err => console.log('Error counting view:', err));
    }

    function playVideo(id, preventScroll = false) {
        const video = videosData.find(v => v.id == id);
        if (!video) return;

        currentVideoId = id;
        updateLikeButtonUI(id);
        incrementViews(id);

        const mp4Player = document.getElementById('main-video-player');
        const ytPlayer = document.getElementById('main-youtube-player');
        const mp4Container = document.getElementById('mp4-player-container');
        const ytContainer = document.getElementById('youtube-player-container');

        const hasMp4 = video.video_url && video.video_url.trim() !== '';
        const hasYoutube = video.youtube_url && video.youtube_url.trim() !== '';

        const switcher = document.getElementById('source-switcher');
        
        let targetSource = 'mp4';
        if (hasMp4 && hasYoutube) {
            targetSource = currentSourceType;
            if (switcher) switcher.classList.remove('hidden');
        } else if (hasYoutube) {
            targetSource = 'youtube';
            if (switcher) switcher.classList.add('hidden');
        } else {
            targetSource = 'mp4';
            if (switcher) switcher.classList.add('hidden');
        }

        const mp4Btn = document.getElementById('src-mp4-btn');
        const ytBtn = document.getElementById('src-yt-btn');
        if (targetSource === 'mp4') {
            if (mp4Btn) {
                mp4Btn.classList.add('bg-primary', 'text-white', 'shadow-sm', 'active');
                mp4Btn.classList.remove('hover:bg-white', 'text-primary');
            }
            if (ytBtn) {
                ytBtn.classList.add('hover:bg-white', 'text-red-600');
                ytBtn.classList.remove('bg-primary', 'text-white', 'shadow-sm', 'active');
            }

            mp4Container.classList.remove('hidden');
            ytContainer.classList.add('hidden');

            mp4Player.src = video.video_url;
            mp4Player.poster = video.thumbnail_url;
            mp4Player.load();
            mp4Player.play().catch(() => {});

            ytPlayer.src = '';
        } else {
            if (ytBtn) {
                ytBtn.classList.add('bg-primary', 'text-white', 'shadow-sm', 'active');
                ytBtn.classList.remove('hover:bg-white', 'text-red-600');
            }
            if (mp4Btn) {
                mp4Btn.classList.add('hover:bg-white', 'text-primary');
                mp4Btn.classList.remove('bg-primary', 'text-white', 'shadow-sm', 'active');
            }

            ytContainer.classList.remove('hidden');
            mp4Container.classList.add('hidden');

            let ytEmbed = video.youtube_url;
            if (ytEmbed && !ytEmbed.includes('autoplay=')) {
                ytEmbed += (ytEmbed.includes('?') ? '&' : '?') + 'autoplay=1&rel=0';
            }
            ytPlayer.src = ytEmbed;

            mp4Player.pause();
        }

        currentSourceType = targetSource;

        document.getElementById('player-title').innerText = video.title;
        document.getElementById('player-desc').innerText = video.description || 'Chưa có mô tả chi tiết cho video này.';
        document.getElementById('player-category').innerText = video.category || 'REVIEW';

        const dateSrc = video.published_at || video.created_at;
        if (dateSrc) {
            const videoDate = new Date(dateSrc);
            const formattedDate = String(videoDate.getDate()).padStart(2, '0') + '/' + String(videoDate.getMonth() + 1).padStart(2, '0') + '/' + videoDate.getFullYear();
            document.getElementById('player-date').innerText = 'Cập nhật ngày: ' + formattedDate;
        }
        
        document.querySelectorAll('.playlist-item').forEach(item => {
            item.classList.remove('active');
        });

        const activeItem = document.getElementById(`playlist-item-${id}`);
        if (activeItem) {
            activeItem.classList.add('active');
            activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        if (!preventScroll) {
            const playerElem = document.getElementById('featured-video');
            if (playerElem) {
                const headerOffset = 90;
                const elementPosition = playerElem.getBoundingClientRect().top + window.scrollY;
                const offsetPosition = elementPosition - headerOffset;
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        }

        loadComments(id);
    }

    function loadComments(videoId) {
        const commentsList = document.getElementById('comments-list');
        const badge = document.getElementById('comment-count-badge');
        if (!commentsList) return;

        commentsList.innerHTML = `
            <div class="py-6 text-center text-xs text-gray-400">
                <i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Đang tải bình luận...
            </div>
        `;

        fetch(`/videos/${videoId}/comments`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (badge) badge.innerText = data.total_count;
                
                if (data.comments.length === 0) {
                    commentsList.innerHTML = `
                        <div class="py-8 text-center text-xs text-gray-400">
                            <i class="fa-regular fa-comment-dots text-2xl mb-2 block text-gray-300"></i>
                            Chưa có bình luận nào. Hãy là người đầu tiên bình luận!
                        </div>
                    `;
                    return;
                }

                commentsList.innerHTML = data.comments.map(c => {
                    const isRootDeleteable = (currentUserRole == 1 || currentUserRole == 2 || currentUserId == c.user.id);
                    
                    return `
                        <div class="video-comment-item border-b border-gray-50 py-3 last:border-0 last:pb-0 animate-fade-in" id="comment-container-${c.id}">
                            <div class="flex gap-3 items-start">
                                <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center font-bold text-gray-600 text-xs shrink-0 border border-gray-200">
                                    ${(c.user.name || 'U').substring(0, 1).toUpperCase()}
                                </div>
                                <div class="flex-grow min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-gray-800 text-xs">${c.user.name}</span>
                                        ${c.user.role_id == 1 ? '<span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-blue-50 text-blue-600 border border-blue-200 scale-90 origin-left">Admin</span>' : ''}
                                        ${c.user.role_id == 2 ? '<span class="px-1.5 py-0.5 rounded text-[8px] font-bold bg-green-50 text-green-600 border border-green-200 scale-90 origin-left">Quản lý</span>' : ''}
                                        <span class="text-[9px] text-gray-400 ms-auto shrink-0">${c.created_at}</span>
                                        ${isRootDeleteable ? `
                                            <button onclick="deleteVideoComment(${c.id})" title="Xóa bình luận" class="text-red-500 hover:text-red-700 text-xs ms-2 shrink-0">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            </button>
                                        ` : ''}
                                    </div>
                                    <p class="text-gray-600 text-xs mt-1 leading-relaxed break-words">${escapeHTML(c.content)}</p>
                                    
                                    <!-- Nút Trả lời -->
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <button class="text-[10px] font-bold text-primary hover:underline flex items-center gap-1" onclick="toggleVideoReplyForm(${c.id})">
                                            <i class="fa-solid fa-reply text-[8px]"></i> Trả lời
                                        </button>
                                    </div>

                                    <!-- Form Trả lời -->
                                    <div class="reply-form-container mt-2.5 hidden" id="reply-form-${c.id}">
                                        <form onsubmit="submitVideoReply(event, ${c.id})" class="flex gap-2 items-center">
                                            <input type="text" placeholder="Viết câu trả lời..." class="flex-grow border border-gray-200 rounded-lg px-2.5 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition" required id="reply-input-${c.id}">
                                            <button type="submit" class="px-2.5 py-1 bg-primary text-white rounded-lg text-xs font-bold hover:bg-blue-800 transition shrink-0">Gửi</button>
                                            <button type="button" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-gray-500 rounded-lg text-xs font-bold transition shrink-0" onclick="toggleVideoReplyForm(${c.id})">Hủy</button>
                                        </form>
                                    </div>

                                    <!-- Danh sách câu trả lời lồng nhau -->
                                    <div class="replies-list space-y-2.5 mt-2.5 pl-4 border-l border-slate-150" id="replies-list-${c.id}">
                                        ${c.replies ? c.replies.map((r, index) => {
                                            const isReplyDeleteable = (currentUserRole == 1 || currentUserRole == 2 || currentUserId == r.user.id);
                                            return `
                                                <div class="reply-item flex gap-2 items-start ${index >= 1 ? 'reply-hidden hidden' : ''}" id="comment-container-${r.id}">
                                                    <div class="w-7 h-7 rounded-full bg-slate-50 flex items-center justify-center font-bold text-gray-500 text-[10px] shrink-0 border border-gray-150">
                                                        ${(r.user.name || 'U').substring(0, 1).toUpperCase()}
                                                    </div>
                                                    <div class="flex-grow min-w-0">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="font-extrabold text-gray-700 text-[11px]">${r.user.name}</span>
                                                            ${r.user.role_id == 1 ? '<span class="px-1 py-0.2 rounded text-[7px] font-bold bg-blue-50 text-blue-600 border border-blue-150 scale-90 origin-left">Admin</span>' : ''}
                                                            ${r.user.role_id == 2 ? '<span class="px-1 py-0.2 rounded text-[7px] font-bold bg-green-50 text-green-600 border border-green-150 scale-90 origin-left">Quản lý</span>' : ''}
                                                            <span class="text-[8px] text-gray-400 ms-auto shrink-0">${r.created_at}</span>
                                                            ${isReplyDeleteable ? `
                                                                <button onclick="deleteVideoComment(${r.id})" title="Xóa phản hồi" class="text-red-500 hover:text-red-700 text-[10px] ms-1.5 shrink-0">
                                                                    <i class="fa-solid fa-trash-can text-[9px]"></i>
                                                                </button>
                                                            ` : ''}
                                                        </div>
                                                        <p class="text-gray-600 text-xs mt-0.5 leading-relaxed break-words">${escapeHTML(r.content)}</p>
                                                        <!-- Nút Trả lời cho phản hồi con -->
                                                        <button class="text-[9px] font-bold text-primary hover:underline flex items-center gap-0.5 mt-0.5" onclick="replyToVideoUser(${c.id}, '${r.user.name.replace(/'/g, "\\'")}', ${r.id})">
                                                            <i class="fa-solid fa-reply text-[7px]"></i> Trả lời
                                                        </button>
                                                    </div>
                                                </div>
                                            `;
                                        }).join('') : ''}
                                    </div>
                                    
                                    ${c.replies && c.replies.length > 1 ? `
                                        <button class="btn-show-more-replies text-[10px] font-bold text-gray-500 hover:text-primary mt-2 flex items-center gap-1 transition" onclick="showAllVideoReplies(${c.id}, this)">
                                            <i class="fa-solid fa-chevron-down text-[8px]"></i> Xem thêm ${c.replies.length - 1} phản hồi
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                commentsList.innerHTML = `
                    <div class="py-6 text-center text-xs text-red-500">
                        Không thể tải bình luận.
                    </div>
                `;
            }
        })
        .catch(err => {
            console.error('Error loading comments:', err);
            commentsList.innerHTML = `
                <div class="py-6 text-center text-xs text-red-500">
                    Lỗi kết nối máy chủ.
                </div>
            `;
        });
    }

    window.toggleVideoReplyForm = function(parentId) {
        const form = document.getElementById('reply-form-' + parentId);
        const repliesList = document.getElementById('replies-list-' + parentId);
        if (form) {
            const isHidden = form.classList.contains('hidden');
            if (isHidden) {
                // Đưa form về vị trí mặc định (trước danh sách câu trả lời)
                if (repliesList) {
                    repliesList.parentNode.insertBefore(form, repliesList);
                }
                form.classList.remove('hidden');
                const input = document.getElementById('reply-input-' + parentId);
                if (input) {
                    input.value = '';
                    input.focus();
                }
            } else {
                form.classList.add('hidden');
            }
        }
    }

    window.replyToVideoUser = function(parentId, userName, specificReplyId) {
        const form = document.getElementById('reply-form-' + parentId);
        const specificReply = document.getElementById('comment-container-' + specificReplyId);
        if (form && specificReply) {
            // Di chuyển khung nhập reply xuống dưới phản hồi cụ thể
            specificReply.parentNode.insertBefore(form, specificReply.nextSibling);
            form.classList.remove('hidden');
            const input = document.getElementById('reply-input-' + parentId);
            if (input) {
                input.value = '@' + userName + ': ';
                input.focus();
            }
        }
    }

    window.showAllVideoReplies = function(parentId, btn) {
        const list = document.getElementById('replies-list-' + parentId);
        if (list) {
            const hiddenItems = list.querySelectorAll('.reply-hidden');
            hiddenItems.forEach(item => {
                item.classList.remove('reply-hidden', 'hidden');
            });
        }
        btn.remove();
    }

    window.submitVideoReply = function(event, parentId) {
        event.preventDefault();
        const input = document.getElementById('reply-input-' + parentId);
        if (!input) return;
        const content = input.value.trim();
        if (!content) return;

        const form = event.target;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i>`;

        fetch(`/videos/${currentVideoId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                content: content,
                parent_id: parentId
            })
        })
        .then(res => {
            if (res.status === 401) {
                showToast('Vui lòng đăng nhập để phản hồi.', 'error');
                throw new Error('Unauthorized');
            }
            return res.json();
        })
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;

            if (data.success) {
                input.value = '';
                toggleVideoReplyForm(parentId);
                loadComments(currentVideoId);
                showToast('Gửi phản hồi thành công!', 'success');
            } else {
                showToast(data.message || 'Không thể gửi phản hồi.', 'error');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
            if (err.message !== 'Unauthorized') {
                console.error('Error submitting reply:', err);
                showToast('Lỗi máy chủ, vui lòng thử lại.', 'error');
            }
        });
    }

    window.deleteVideoComment = function(commentId) {
        if (!confirm('Bạn có chắc chắn muốn xóa bình luận này không? Thao tác này không thể hoàn tác.')) return;

        fetch(`/videos/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById('comment-container-' + commentId);
                if (el) {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => {
                        el.remove();
                        // Reload lại danh sách bình luận để đếm lại chính xác badge và thứ tự
                        loadComments(currentVideoId);
                    }, 300);
                }
                showToast(data.message || 'Xóa bình luận thành công!', 'success');
            } else {
                showToast(data.message || 'Không thể xóa bình luận.', 'error');
            }
        })
        .catch(err => {
            console.error('Error deleting comment:', err);
            showToast('Lỗi kết nối máy chủ.', 'error');
        });
    }

    function escapeHTML(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function submitComment(event) {
        event.preventDefault();
        if (!currentVideoId) return;

        const textarea = document.getElementById('comment-textarea');
        if (!textarea) return;

        const content = textarea.value.trim();
        if (!content) return;

        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-1"></i> Đang gửi...`;

        fetch(`/videos/${currentVideoId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ content: content })
        })
        .then(res => {
            if (res.status === 401) {
                showToast('Vui lòng đăng nhập để bình luận.', 'error');
                throw new Error('Unauthorized');
            }
            return res.json();
        })
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;

            if (data.success) {
                textarea.value = '';
                loadComments(currentVideoId);
                showToast('Đăng bình luận thành công!', 'success');
            } else {
                showToast(data.message || 'Không thể đăng bình luận.', 'error');
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
            if (err.message !== 'Unauthorized') {
                console.error('Error posting comment:', err);
                showToast('Lỗi máy chủ, vui lòng thử lại.', 'error');
            }
        });
    }

    window.addEventListener('DOMContentLoaded', () => {
        if (currentVideoId) {
            updateLikeButtonUI(currentVideoId);
        }

        const urlParams = new URLSearchParams(window.location.search);
        const urlVideoId = urlParams.get('id');
        if (urlVideoId) {
            const videoExists = videosData.some(v => v.id == urlVideoId);
            if (videoExists && urlVideoId != currentVideoId) {
                playVideo(urlVideoId, true);
            } else if (videoExists && urlVideoId == currentVideoId) {
                incrementViews(currentVideoId);
                loadComments(currentVideoId);
            }
        } else if (currentVideoId) {
            incrementViews(currentVideoId);
            loadComments(currentVideoId);
        }

        // Drag to scroll for horizontal category bar on Desktop PCs
        const slider = document.getElementById('category-filter-scroll');
        if (slider) {
            let isDown = false;
            let startX;
            let scrollLeft;

            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                slider.style.cursor = 'grabbing';
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });
            slider.addEventListener('mouseleave', () => {
                isDown = false;
                slider.style.cursor = 'grab';
            });
            slider.addEventListener('mouseup', () => {
                isDown = false;
                slider.style.cursor = 'grab';
            });
            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 2; // scroll speed multiplier
                slider.scrollLeft = scrollLeft - walk;
            });
            slider.style.cursor = 'grab';
        }

        // Đúp chuột để tua trên đầu phát video MP4
        const mp4Player = document.getElementById('main-video-player');
        if (mp4Player) {
            mp4Player.addEventListener('dblclick', (e) => {
                const rect = mp4Player.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const width = rect.width;
                
                if (clickX < width / 2) {
                    // Đúp chuột bên trái: tua lùi 10s
                    mp4Player.currentTime = Math.max(0, mp4Player.currentTime - 10);
                    showToast('Tua lùi 10 giây', 'info');
                } else {
                    // Đúp chuột bên phải: tua tiến 10s
                    mp4Player.currentTime = Math.min(mp4Player.duration || 0, mp4Player.currentTime + 10);
                    showToast('Tua nhanh 10 giây', 'info');
                }
            });
        }

        // Lắng nghe sự kiện bàn phím (Phím mũi tên trái/phải để tua, phím cách để dừng/phát)
        window.addEventListener('keydown', (e) => {
            const activeTag = document.activeElement ? document.activeElement.tagName : '';
            if (activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT') {
                return;
            }

            const activePlayer = document.getElementById('main-video-player');
            if (!activePlayer || activePlayer.offsetParent === null) {
                return;
            }

            if (e.key === 'ArrowRight') {
                e.preventDefault();
                activePlayer.currentTime = Math.min(activePlayer.duration || 0, activePlayer.currentTime + 10);
                showToast('Tua nhanh 10 giây', 'info');
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                activePlayer.currentTime = Math.max(0, activePlayer.currentTime - 10);
                showToast('Tua lùi 10 giây', 'info');
            } else if (e.key === ' ' || e.key === 'Spacebar') {
                e.preventDefault();
                if (activePlayer.paused) {
                    activePlayer.play().catch(() => {});
                } else {
                    activePlayer.pause();
                }
            }
        });
    });
</script>
@endpush
