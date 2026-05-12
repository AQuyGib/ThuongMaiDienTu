@extends('admin.layouts.master')

@section('title', 'Master Theme Editor')
@section('page-title', 'Tùy biến Giao diện Master')

@section('content')
<div class="master-editor-wrapper bg-[#f8fafc] rounded-[2rem] shadow-2xl border border-white/60 overflow-hidden">
    <form id="themeSettingsForm" action="{{ route('admin.settings.theme.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="flex flex-col lg:flex-row h-[950px]">
            {{-- ==========================================
                 PANEL ĐIỀU KHIỂN (SIDEBAR)
                 ========================================== --}}
            <div class="w-full lg:w-[500px] border-r border-slate-200/60 flex flex-col h-[950px] bg-white shadow-xl z-20 overflow-hidden">
                
                {{-- HEADER CỐ ĐỊNH --}}
                <div class="p-8 border-b border-slate-100 bg-white/80 backdrop-blur-xl sticky top-0 z-30 space-y-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-slate-800 text-xl tracking-tight flex items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-200">
                                    <i class="fa-solid fa-wand-magic-sparkles text-white text-sm"></i>
                                </div>
                                <span>Master Editor</span>
                            </h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-[0.3em] mt-1">Hệ thống đồng bộ trực tiếp</p>
                        </div>
                        <button type="submit" id="btnSave" class="group relative px-7 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black text-xs transition-all duration-300 shadow-xl shadow-blue-200 active:scale-95 flex items-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up text-sm group-hover:translate-y-[-2px] transition-transform"></i>
                            LƯU CẤU HÌNH
                        </button>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" onclick="handleReset()" class="flex-1 flex items-center justify-center gap-2 py-3 bg-red-50 text-red-600 border border-red-100 rounded-xl text-[10px] font-black hover:bg-red-600 hover:text-white transition-all duration-300 uppercase tracking-widest">
                            <i class="fa-solid fa-arrow-rotate-left"></i> Khôi phục gốc
                        </button>
                        <button type="button" onclick="window.location.reload()" class="px-5 py-3 bg-slate-50 text-slate-400 border border-slate-100 rounded-xl text-[10px] font-black hover:bg-slate-100 hover:text-slate-600 transition-all duration-300 uppercase tracking-widest">
                            Hủy bỏ
                        </button>
                    </div>
                </div>

                {{-- NỘI DUNG FORM (CUỘN) --}}
                <div class="flex-1 overflow-y-auto p-8 space-y-10 custom-scrollbar scroll-smooth bg-slate-50/30">
                    
                    {{-- PHẦN 1: THANH CÔNG CỤ (TOP BAR) - MỚI --}}
                    <section class="editor-section animate-fade-in" style="animation-delay: 0.1s;">
                        <div class="section-header flex items-center gap-4 mb-6">
                            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-black shadow-lg shadow-blue-100">1</div>
                            <h4 class="font-black text-slate-800 text-xs uppercase tracking-widest">Thanh công cụ (Top Bar)</h4>
                        </div>
                        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-4">
                            <div class="field-group">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1 block ml-1">Thông báo 1 (Trái)</label>
                                <input type="text" name="topbar_text_1" value="{{ $settings['topbar_text_1'] ?? 'Thu cũ giá ngon - Lên đời tiết kiệm' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-600 shadow-inner outline-none focus:ring-4 focus:ring-blue-500/10 transition-all">
                            </div>
                            <div class="field-group">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1 block ml-1">Thông báo 2 (Trái)</label>
                                <input type="text" name="topbar_text_2" value="{{ $settings['topbar_text_2'] ?? 'Sản phẩm Chính hãng - Xuất VAT đầy đủ' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-600 shadow-inner outline-none focus:ring-4 focus:ring-blue-500/10 transition-all">
                            </div>
                            <div class="field-group">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1 block ml-1">Thông báo 3 (Trái)</label>
                                <input type="text" name="topbar_text_3" value="{{ $settings['topbar_text_3'] ?? 'Giao nhanh - Miễn phí cho đơn 300k' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-600 shadow-inner outline-none focus:ring-4 focus:ring-blue-500/10 transition-all">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="field-group">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1 block ml-1">Link 1 (Phải)</label>
                                    <input type="text" name="topbar_text_4" value="{{ $settings['topbar_text_4'] ?? 'Cửa hàng gần bạn' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-600 shadow-inner outline-none focus:ring-4 focus:ring-blue-500/10 transition-all">
                                </div>
                                <div class="field-group">
                                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-1 block ml-1">Link 2 (Phải)</label>
                                    <input type="text" name="topbar_text_5" value="{{ $settings['topbar_text_5'] ?? 'Tra cứu đơn hàng' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-600 shadow-inner outline-none focus:ring-4 focus:ring-blue-500/10 transition-all">
                                </div>
                            </div>
                        </div>
                    </section>
                    
                    {{-- PHẦN 2: THƯƠNG HIỆU --}}
                    <section class="editor-section animate-fade-in" style="animation-delay: 0.2s;">
                        <div class="section-header flex items-center gap-4 mb-6">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-black shadow-inner">2</div>
                            <h4 class="font-black text-slate-800 text-xs uppercase tracking-widest">Định danh thương hiệu</h4>
                        </div>
                        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-5">
                            <div class="field-group">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block ml-1">Tên hiển thị & Hậu tố</label>
                                <div class="flex gap-3">
                                    <input type="text" name="site_name" value="{{ $settings['site_name'] ?? 'DIENMAY' }}" class="flex-1 px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-black text-slate-700 shadow-inner">
                                    <input type="text" name="site_suffix" value="{{ $settings['site_suffix'] ?? 'PRO' }}" class="w-24 px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-blue-400 font-black shadow-inner">
                                </div>
                            </div>
                            <div class="field-group">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2 block ml-1">Hotline Topbar</label>
                                <input type="text" name="hotline" value="{{ $settings['hotline'] ?? '1800 2097' }}" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-bold text-slate-600 shadow-inner">
                            </div>
                        </div>
                    </section>

                    {{-- PHẦN 3: HÌNH ẢNH --}}
                    <section class="editor-section animate-fade-in" style="animation-delay: 0.3s;">
                        <div class="section-header flex items-center gap-4 mb-6">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-black shadow-inner">3</div>
                            <h4 class="font-black text-slate-800 text-xs uppercase tracking-widest">Đồ họa & Banner</h4>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-white p-5 rounded-[2rem] border border-slate-100 shadow-sm flex items-center gap-5 group">
                                <div class="w-24 h-24 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-center p-3 relative overflow-hidden group/img transition-all shadow-inner">
                                    <img id="preview_logo" src="{{ asset($settings['logo'] ?? 'images/logo.png') }}" class="max-h-full object-contain">
                                    <label for="file_logo" class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center cursor-pointer text-white text-lg"><i class="fa-solid fa-camera"></i></label>
                                </div>
                                <div class="flex-1 space-y-3">
                                    <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Logo thương hiệu</h5>
                                    <div class="flex gap-2">
                                        <input type="file" name="logo" onchange="previewImage(this, 'preview_logo')" class="hidden" id="file_logo">
                                        <input type="hidden" name="remove_logo" id="remove_logo_input" value="0">
                                        <label for="file_logo" class="flex-1 py-2 text-center bg-blue-50 text-blue-600 border border-blue-100 rounded-xl text-[10px] font-black cursor-pointer hover:bg-blue-600 hover:text-white transition-all shadow-sm">THAY ĐỔI</label>
                                        <button type="button" onclick="removeLogo()" class="px-4 py-2 bg-red-50 text-red-400 border border-red-100 rounded-xl text-[10px] hover:bg-red-500 hover:text-white transition-all shadow-sm"><i class="fa-solid fa-trash-can"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-4 group">
                                <div class="flex justify-between items-center"><h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Banner Chính</h5><div class="flex gap-2"><label for="file_banner" class="px-4 py-1.5 bg-blue-50 text-blue-600 border border-blue-100 rounded-lg text-[10px] font-black cursor-pointer hover:bg-blue-600 hover:text-white transition-all shadow-sm">ĐỔI ẢNH</label><button type="button" onclick="removeBanner()" class="px-3 py-1.5 bg-red-50 text-red-400 border border-red-100 rounded-lg text-[10px] hover:bg-red-500 hover:text-white transition-all shadow-sm"><i class="fa-solid fa-trash-can"></i></button></div></div>
                                <div class="aspect-[21/9] bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden shadow-inner group/banner"><img id="preview_banner_1" src="{{ asset($settings['banner_1'] ?? 'images/banner1.jpg') }}" class="w-full h-full object-cover"></div>
                                <input type="hidden" name="remove_banner_1" id="remove_banner_input" value="0">
                                <input type="file" name="banner_1" onchange="previewImage(this, 'preview_banner_1')" class="hidden" id="file_banner">
                            </div>
                        </div>
                    </section>

                    {{-- PHẦN 4: DỊCH VỤ & CHÂN TRANG --}}
                    <section class="editor-section animate-fade-in" style="animation-delay: 0.4s;">
                        <div class="section-header flex items-center gap-4 mb-6">
                            <div class="w-8 h-8 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-black shadow-inner">4</div>
                            <h4 class="font-black text-slate-800 text-xs uppercase tracking-widest">Dịch vụ & Chân trang</h4>
                        </div>
                        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                @foreach([['key' => 'footer_hotline_buy', 'label' => 'GỌI MUA', 'def' => '1800.1060 (7:30 - 22:00)'],['key' => 'footer_hotline_tech', 'label' => 'KỸ THUẬT', 'def' => '1800.1763 (7:30 - 22:00)'],['key' => 'footer_hotline_complain', 'label' => 'KHIẾU NẠI', 'def' => '1800.1062 (8:00 - 21:30)'],['key' => 'footer_hotline_warranty', 'label' => 'BẢO HÀNH', 'def' => '1800.1064 (8:00 - 21:00)']] as $f)
                                <div class="field-item">
                                    <label class="text-[8px] font-black text-slate-300 mb-1 block ml-1 uppercase">{{$f['label']}}</label>
                                    <input type="text" name="{{$f['key']}}" value="{{ $settings[$f['key']] ?? $f['def'] }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-[11px] font-black text-slate-600 shadow-inner focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                                </div>
                                @endforeach
                            </div>
                            <div class="space-y-3">
                                <label class="text-[8px] font-black text-slate-300 mb-1 block ml-1 uppercase">Mô tả Chân trang</label>
                                <textarea name="site_description" rows="3" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-[11px] font-bold text-slate-500 shadow-inner leading-relaxed">{{ $settings['site_description'] ?? 'Hệ thống điện máy hàng đầu.' }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="field-item"><label class="text-[8px] font-black text-slate-300 mb-1 block ml-1 uppercase">Email</label><input type="email" name="email" value="{{ $settings['email'] ?? 'contact@dienmay.vn' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-500 shadow-inner outline-none focus:ring-4 focus:ring-blue-500/10"></div>
                                <div class="field-item"><label class="text-[8px] font-black text-slate-300 mb-1 block ml-1 uppercase">Địa chỉ</label><input type="text" name="address" value="{{ $settings['address'] ?? '' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl text-[11px] font-bold text-slate-500 shadow-inner outline-none focus:ring-4 focus:ring-blue-500/10"></div>
                            </div>
                        </div>
                    </section>

                    {{-- PHẦN 5: MẠNG XÃ HỘI --}}
                    <section class="editor-section animate-fade-in pb-12" style="animation-delay: 0.5s;">
                        <div class="section-header flex items-center gap-4 mb-6">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-black shadow-inner">5</div>
                            <h4 class="font-black text-slate-800 text-xs uppercase tracking-widest">Kết nối cộng đồng</h4>
                        </div>
                        <div class="bg-white p-6 rounded-[2rem] border border-slate-100 shadow-sm space-y-6">
                            <div class="grid grid-cols-4 gap-3">
                                @foreach([['icon' => 'fa-brands fa-facebook-f', 'color' => '#1877f2', 'p' => 'FB'],['icon' => 'fa-brands fa-youtube', 'color' => '#ff0000', 'p' => 'YT'],['icon' => 'fa-brands fa-tiktok', 'color' => '#000000', 'p' => 'TT'],['icon' => 'fa-solid fa-comment-sms', 'color' => '#0084ff', 'p' => 'ZL']] as $soc)
                                <button type="button" onclick="addSocialRow('{{ $soc['icon'] }}')" class="flex flex-col items-center gap-2 p-3 bg-slate-50 rounded-2xl hover:bg-white hover:shadow-lg transition-all group">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white shadow-md group-hover:scale-110 transition-transform" style="background: {{$soc['color']}}"><i class="{{ $soc['icon'] }} text-xs"></i></div>
                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">{{ $soc['p'] }}</span>
                                </button>
                                @endforeach
                            </div>
                            <div id="social-repeater" class="space-y-3">
                                @php $socialLinks = isset($settings['social_links']) ? json_decode($settings['social_links'], true) : []; @endphp
                                @foreach($socialLinks as $index => $link)
                                <div class="social-row flex items-center gap-3 group animate-slide-in">
                                    <div class="flex-1 bg-slate-50 p-4 rounded-2xl border border-slate-100 shadow-inner flex items-center gap-4 transition-all focus-within:bg-white focus-within:shadow-lg focus-within:ring-4 focus-within:ring-blue-500/5">
                                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-blue-600 border border-slate-100"><i class="{{ $link['icon'] ?? 'fa-solid fa-link' }} text-xs"></i></div>
                                        <div class="flex-1">
                                            <input type="text" name="social_links[{{ $index }}][icon]" value="{{ $link['icon'] }}" class="hidden">
                                            <input type="url" name="social_links[{{ $index }}][url]" value="{{ $link['url'] }}" placeholder="https://..." class="w-full bg-transparent border-none p-0 text-[11px] font-bold text-slate-600 focus:ring-0 outline-none">
                                        </div>
                                    </div>
                                    <button type="button" onclick="this.closest('.social-row').remove(); updatePreview();" class="w-10 h-10 rounded-xl bg-red-50 text-red-400 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm"><i class="fa-solid fa-xmark text-sm"></i></button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            {{-- ==========================================
                 KHUNG XEM TRƯỚC (LIVE PREVIEW)
                 ========================================== --}}
            <div class="flex-1 bg-slate-100 p-8 flex flex-col h-[950px] overflow-hidden relative shadow-inner">
                <div class="mb-6 flex justify-between items-center px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex gap-1.5 p-2 bg-white/60 backdrop-blur rounded-full shadow-inner">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        </div>
                        <div class="text-[9px] font-black text-slate-400 uppercase tracking-[0.4em] ml-2">Real-time Visualization</div>
                    </div>
                    <div id="previewStatus" class="px-5 py-2 bg-white rounded-2xl shadow-sm border border-slate-200 text-[10px] font-black text-blue-600 uppercase tracking-widest flex items-center gap-2 animate-pulse-slow">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span> ĐANG THEO DÕI...
                    </div>
                </div>

                <div class="flex-1 relative overflow-hidden rounded-[3rem] shadow-[0_40px_100px_-20px_rgba(0,0,0,0.15)] border-[12px] border-white bg-white">
                    <div class="absolute inset-0 overflow-y-auto custom-scrollbar website-preview-container" style="zoom: 0.62; transform-origin: top center;">
                        <div class="real-website-clone">
                            {{-- Top Bar Mock --}}
                            <div class="mock-topbar" style="background:#003380; color:#fff; font-size:12px; padding:10px 0; font-weight:500;">
                                <div class="container-mock" style="max-width:1200px; margin:0 auto; padding:0 20px; display:flex; justify-content:space-between; align-items:center;">
                                    <div style="display:flex; gap:15px;">
                                        <span><i class="fa-solid fa-recycle mr-2"></i> <span id="mock-topbar-1">{{ $settings['topbar_text_1'] ?? 'Thu cũ giá ngon - Lên đời tiết kiệm' }}</span></span>
                                        <span><i class="fa-solid fa-certificate mr-2"></i> <span id="mock-topbar-2">{{ $settings['topbar_text_2'] ?? 'Sản phẩm Chính hãng - Xuất VAT đầy đủ' }}</span></span>
                                        <span><i class="fa-solid fa-truck-fast mr-2"></i> <span id="mock-topbar-3">{{ $settings['topbar_text_3'] ?? 'Giao nhanh - Miễn phí cho đơn 300k' }}</span></span>
                                    </div>
                                    <div style="display:flex; gap:15px; align-items:center;">
                                        <span><i class="fa-solid fa-store mr-2"></i> <span id="mock-topbar-4">{{ $settings['topbar_text_4'] ?? 'Cửa hàng gần bạn' }}</span></span>
                                        <span><i class="fa-solid fa-truck mr-2"></i> <span id="mock-topbar-5">{{ $settings['topbar_text_5'] ?? 'Tra cứu đơn hàng' }}</span></span>
                                        <span><i class="fa-solid fa-phone mr-1"></i> Hotline: <strong id="mock-hotline" style="font-weight:800;">{{ $settings['hotline'] ?? '1800 2097' }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            <header style="background:#0046ab; padding:18px 0; color:#fff; position:sticky; top:0; z-index:100; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
                                <div class="container-mock" style="max-width:1200px; margin:0 auto; padding:0 20px; display:flex; align-items:center; gap:20px;">
                                    <div id="mock-logo-container" style="font-size:26px; font-weight:900; text-transform:uppercase; flex-shrink:0;">
                                        @if(isset($settings['logo']))
                                            <img id="mock-logo" src="{{ asset($settings['logo']) }}" style="max-height: 42px;">
                                        @else
                                            <i class="fa-solid fa-bolt-lightning mr-1"></i> <span id="mock-sitename-full">{{ $settings['site_name'] ?? 'DIENMAY' }}<span style="color:#00d2ff">{{ $settings['site_suffix'] ?? 'PRO' }}</span></span>
                                        @endif
                                    </div>
                                    <div style="background:rgba(255,255,255,0.15); padding:10px 18px; border-radius:10px; font-size:13px; font-weight:700;"><i class="fa-solid fa-bars mr-2"></i> Danh mục</div>
                                    <div style="flex:1; background:#fff; padding:12px 20px; border-radius:10px; color:#999; font-size:14px; display:flex; justify-content:space-between; align-items:center;">Tìm sản phẩm... <i class="fa-solid fa-search"></i></div>
                                    <div style="display:flex; gap:15px; text-align:center;"><i class="fa-solid fa-cart-shopping text-2xl"></i><i class="fa-regular fa-circle-user text-2xl"></i></div>
                                </div>
                            </header>

                            <div class="container-mock" style="max-width:1200px; margin:25px auto; padding:0 20px; display:flex; gap:20px;">
                                <div style="width:230px; background:#fff; border-radius:15px; border:1px solid #eee; overflow:hidden;">
                                    @foreach(['Điện thoại', 'Laptop', 'Tablet', 'Đồng hồ', 'Phụ kiện'] as $n)
                                        <div style="padding:13px 20px; font-size:14px; font-weight:700; color:#444; border-bottom:1px solid #f9f9f9; display:flex; justify-content:space-between;"><span>{{$n}}</span><i class="fa-solid fa-angle-right text-[10px] text-gray-300"></i></div>
                                    @endforeach
                                </div>
                                <div style="flex:1; border-radius:15px; overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.05);"><img id="mock-banner" src="{{ asset($settings['banner_1'] ?? 'images/banner1.jpg') }}" style="width:100%; height:400px; object-fit:cover;"></div>
                            </div>

                            <footer style="background:#fff; border-top:1px solid #f1f3f5; padding:80px 0 40px; margin-top:80px;">
                                <div class="container-mock" style="max-width:1200px; margin:0 auto; padding:0 20px; display:grid; grid-template-columns: repeat(4, 1fr); gap:40px;">
                                    <div><h4 style="font-size:16px; font-weight:900; margin-bottom:20px;">Hỗ trợ khách hàng</h4><ul style="list-style:none; padding:0; font-size:13px; color:#666; line-height:2.2;"><li>Gọi mua: <strong id="mock-footer-buy">{{ $settings['footer_hotline_buy'] ?? '1800.1060' }}</strong></li><li>Kỹ thuật: <strong id="mock-footer-tech">{{ $settings['footer_hotline_tech'] ?? '1800.1763' }}</strong></li><li>Bảo hành: <strong id="mock-footer-warranty">{{ $settings['footer_hotline_warranty'] ?? '1800.1064' }}</strong></li></ul></div>
                                    <div><h4 style="font-size:16px; font-weight:900; margin-bottom:20px;">Về công ty</h4><ul style="list-style:none; padding:0; font-size:13px; color:#666; line-height:2.2;"><li>Giới thiệu <span class="site-name-text">{{ $settings['site_name'] ?? 'DIENMAY' }}</span></li><li>Tuyển dụng</li><li>Hệ thống cửa hàng</li></ul></div>
                                    <div><h4 style="font-size:16px; font-weight:900; margin-bottom:20px;">Chính sách</h4><ul style="list-style:none; padding:0; font-size:13px; color:#666; line-height:2.2;"><li>Chính sách bảo hành</li><li>Chính sách đổi trả</li><li>Bảo mật thông tin</li></ul></div>
                                    <div><h4 style="font-size:16px; font-weight:900; margin-bottom:20px;">Kết nối mạng xã hội</h4><div id="mock-social-icons" style="display:flex; gap:15px; font-size:32px; color:#0046ab;"></div><div style="margin-top:30px; font-size:12px; color:#999; line-height:1.6;"><div style="display:flex; gap:10px;"><i class="fa-solid fa-map-marker-alt text-blue-600"></i> <span id="mock-address">{{ $settings['address'] ?? '' }}</span></div><div style="display:flex; gap:10px;"><i class="fa-solid fa-envelope text-blue-600"></i> <span id="mock-email">{{ $settings['email'] ?? '' }}</span></div></div></div>
                                </div>
                                <div style="text-align:center; padding-top:50px; border-top:1px solid #f1f3f5; margin-top:60px; font-size:12px; color:#adb5bd; font-weight:700;" id="mock-copyright">© 2026 {{ $settings['site_name'] ?? 'DIENMAY' }} - TẤT CẢ QUYỀN ĐƯỢC BẢO HỘ</div>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form id="resetForm" action="{{ route('admin.settings.theme.reset') }}" method="POST" style="display: none;">@csrf</form>
</div>
@endsection

@push('styles')
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(15px); } to { opacity:1; transform:translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; opacity: 0; }
    @keyframes slideIn { from { opacity:0; transform:translateX(-20px); } to { opacity:1; transform:translateX(0); } }
    .animate-slide-in { animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes pulseSlow { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
    .animate-pulse-slow { animation: pulseSlow 3s infinite ease-in-out; }
    #themeSettingsForm input:focus, #themeSettingsForm textarea:focus { background-color: white !important; border: 2px solid #3b82f6 !important; box-shadow: 0 10px 20px -5px rgba(59, 130, 246, 0.1) !important; transform: translateY(-1px); }
</style>
@endpush

@push('scripts')
<script>
    let socialIndex = {{ count($socialLinks) }};

    function updatePreview() {
        // Site Data
        const siteName = document.querySelector('input[name="site_name"]')?.value || 'DIENMAY';
        const siteSuffix = document.querySelector('input[name="site_suffix"]')?.value || 'PRO';
        const hotline = document.querySelector('input[name="hotline"]')?.value || '1800 2097';
        
        // Topbar Data
        const tb1 = document.querySelector('input[name="topbar_text_1"]')?.value || '';
        const tb2 = document.querySelector('input[name="topbar_text_2"]')?.value || '';
        const tb3 = document.querySelector('input[name="topbar_text_3"]')?.value || '';
        const tb4 = document.querySelector('input[name="topbar_text_4"]')?.value || '';
        const tb5 = document.querySelector('input[name="topbar_text_5"]')?.value || '';

        // Footer Data
        const ftBuy = document.querySelector('input[name="footer_hotline_buy"]')?.value || '';
        const ftTech = document.querySelector('input[name="footer_hotline_tech"]')?.value || '';
        const ftWar = document.querySelector('input[name="footer_hotline_warranty"]')?.value || '';
        const address = document.querySelector('input[name="address"]')?.value || '';
        const email = document.querySelector('input[name="email"]')?.value || '';

        // Sync UI
        document.getElementById('mock-sitename-full').innerHTML = `${siteName}<span style="color:#00d2ff">${siteSuffix}</span>`;
        document.querySelectorAll('.site-name-text').forEach(el => el.innerText = siteName);
        document.getElementById('mock-hotline').innerText = hotline;
        
        document.getElementById('mock-topbar-1').innerText = tb1;
        document.getElementById('mock-topbar-2').innerText = tb2;
        document.getElementById('mock-topbar-3').innerText = tb3;
        document.getElementById('mock-topbar-4').innerText = tb4;
        document.getElementById('mock-topbar-5').innerText = tb5;

        document.getElementById('mock-footer-buy').innerText = ftBuy;
        document.getElementById('mock-footer-tech').innerText = ftTech;
        document.getElementById('mock-footer-warranty').innerText = ftWar;
        document.getElementById('mock-address').innerText = address;
        document.getElementById('mock-email').innerText = email;
        document.getElementById('mock-copyright').innerText = `© 2026 ${siteName} - TẤT CẢ QUYỀN ĐƯỢC BẢO HỘ`;

        // Social Icons
        const socialContainer = document.getElementById('mock-social-icons');
        const socialRows = document.querySelectorAll('.social-row');
        let socialHtml = '';
        socialRows.forEach(row => {
            const iconInp = row.querySelector('input[name*="[icon]"]');
            const urlInp = row.querySelector('input[name*="[url]"]');
            if(iconInp && urlInp && urlInp.value) {
                const color = iconInp.value.includes('facebook') ? '#1877f2' : (iconInp.value.includes('youtube') ? '#ff0000' : (iconInp.value.includes('tiktok') ? '#000000' : '#0084ff'));
                socialHtml += `<i class="${iconInp.value}" style="color: ${color}"></i>`;
            }
        });
        socialContainer.innerHTML = socialHtml || '<i class="fa-brands fa-facebook text-slate-200"></i>';
    }

    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
                if(previewId === 'preview_logo') {
                    document.getElementById('remove_logo_input').value = '0';
                    document.getElementById('mock-logo-container').innerHTML = `<img src="${e.target.result}" style="max-height: 42px;">`;
                }
                if(previewId === 'preview_banner_1') {
                    document.getElementById('remove_banner_input').value = '0';
                    document.getElementById('mock-banner').src = e.target.result;
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeLogo() {
        Swal.fire({ title:'Xóa Logo?', text:"Sẽ dùng Logo chữ mặc định!", icon:'warning', showCancelButton:true, confirmButtonText:'Xác nhận' }).then((result) => {
            if (result.isConfirmed) { document.getElementById('remove_logo_input').value = '1'; updatePreview(); }
        });
    }

    function removeBanner() {
        Swal.fire({ title:'Xóa Banner?', text:"Sẽ dùng ảnh nền mặc định!", icon:'warning', showCancelButton:true, confirmButtonText:'Xác nhận' }).then((result) => {
            if (result.isConfirmed) { document.getElementById('remove_banner_input').value = '1'; const no = 'https://via.placeholder.com/1200x400?text=Banner+Default'; document.getElementById('preview_banner_1').src = no; document.getElementById('mock-banner').src = no; }
        });
    }

    function addSocialRow(icon = 'fa-solid fa-link') {
        const container = document.getElementById('social-repeater');
        const html = `
            <div class="social-row flex items-center gap-3 group animate-slide-in">
                <div class="flex-1 bg-slate-50 p-4 rounded-2xl border border-slate-100 shadow-inner flex items-center gap-4 transition-all focus-within:bg-white focus-within:shadow-lg focus-within:ring-4 focus-within:ring-blue-500/5">
                    <div class="w-10 h-10 rounded-xl bg-white shadow-sm flex items-center justify-center text-blue-600 border border-slate-100"><i class="${icon} text-xs"></i></div>
                    <div class="flex-1">
                        <input type="text" name="social_links[${socialIndex}][icon]" value="${icon}" class="hidden">
                        <input type="url" name="social_links[${socialIndex}][url]" value="" placeholder="https://..." class="w-full bg-transparent border-none p-0 text-[11px] font-bold text-slate-600 focus:ring-0 outline-none">
                    </div>
                </div>
                <button type="button" onclick="this.closest('.social-row').remove(); updatePreview();" class="w-10 h-10 rounded-xl bg-red-50 text-red-400 flex items-center justify-center hover:bg-red-500 hover:text-white transition-all shadow-sm"><i class="fa-solid fa-xmark text-sm"></i></button>
            </div>`;
        container.insertAdjacentHTML('beforeend', html); socialIndex++; updatePreview();
    }

    function handleReset() {
        Swal.fire({ title:'Khôi phục gốc?', text:"Toàn bộ cài đặt sẽ bị xóa sạch!", icon:'error', showCancelButton:true, confirmButtonText:'Đồng ý' }).then((result) => {
            if (result.isConfirmed) document.getElementById('resetForm').submit();
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        updatePreview();
        const form = document.getElementById('themeSettingsForm');
        if(form) { 
            form.addEventListener('input', updatePreview); 
            form.addEventListener('change', updatePreview); 
            form.addEventListener('submit', () => { const btn = document.getElementById('btnSave'); btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> ĐANG LƯU...'; btn.disabled = true; });
        }
    });
</script>
@endpush
