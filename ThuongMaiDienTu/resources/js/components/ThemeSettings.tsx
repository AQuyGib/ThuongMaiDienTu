import React, { useState, useRef, useEffect } from 'react';
import axios from 'axios';

interface ThemeSettingsProps {
    settings: Record<string, any>;
    asset_url: string;
}

// 🎨 Cấu hình bảng màu phối sẵn (Presets) cao cấp
const COLOR_PRESETS = [
    {
        name: 'Ocean Premium (Mặc định)',
        colors: {
            header_bg_color: '#0046ab',
            header_text_color: '#ffffff',
            announcement_bg_color: '#003380',
            announcement_text_color: '#ffffff',
            footer_bg_color: '#0046ab',
            footer_text_color: '#ffffff',
            footer_heading_color: '#ffffff'
        }
    },
    {
        name: 'Midnight Black Luxury',
        colors: {
            header_bg_color: '#0f172a',
            header_text_color: '#f8fafc',
            announcement_bg_color: '#020617',
            announcement_text_color: '#38bdf8',
            footer_bg_color: '#0b0f19',
            footer_text_color: '#94a3b8',
            footer_heading_color: '#38bdf8'
        }
    },
    {
        name: 'Eco Green Fresh',
        colors: {
            header_bg_color: '#15803d',
            header_text_color: '#ffffff',
            announcement_bg_color: '#14532d',
            announcement_text_color: '#bbf7d0',
            footer_bg_color: '#166534',
            footer_text_color: '#f0fdf4',
            footer_heading_color: '#ffffff'
        }
    },
    {
        name: 'Cyberpunk Red Technology',
        colors: {
            header_bg_color: '#d70018',
            header_text_color: '#ffffff',
            announcement_bg_color: '#99000c',
            announcement_text_color: '#ffe4e6',
            footer_bg_color: '#18181b',
            footer_text_color: '#d4d4d8',
            footer_heading_color: '#d70018'
        }
    },
    {
        name: 'Gold & Ivory Luxury',
        colors: {
            header_bg_color: '#1e1b4b',
            header_text_color: '#fef08a',
            announcement_bg_color: '#12103e',
            announcement_text_color: '#fef9c3',
            footer_bg_color: '#1e1b4b',
            footer_text_color: '#e0e7ff',
            footer_heading_color: '#fef08a'
        }
    }
];

const ThemeSettings: React.FC<ThemeSettingsProps> = ({ settings: initialSettings, asset_url }) => {
    const [settings, setSettings] = useState(initialSettings);
    const [isSaving, setIsSaving] = useState(false);
    const [previewScale, setPreviewScale] = useState(0.8);
    const [activeTab, setActiveTab] = useState<'header' | 'footer'>('header');
    
    // Quản lý các phân hệ collapsible accordion cho form bên trái
    const [expandedSections, setExpandedSections] = useState<Record<string, boolean>>({
        brand: true,
        headerColors: true,
        topbar: true,
        footerContact: true,
        footerColors: true,
        footerLinks: true,
        footerSocials: true,
        footerSubscribe: true
    });

    const toggleSection = (section: string) => {
        setExpandedSections(prev => ({ ...prev, [section]: !prev[section] }));
    };

    // Các danh sách liên kết động cho chân trang (Footer Columns 2 & 3)
    const [col2Links, setCol2Links] = useState<any[]>(() => {
        try { return JSON.parse(initialSettings.footer_col_2_links || '[]'); } catch (e) { return []; }
    });
    const [col3Links, setCol3Links] = useState<any[]>(() => {
        try { return JSON.parse(initialSettings.footer_col_3_links || '[]'); } catch (e) { return []; }
    });

    const fileInputRefs = {
        logo: useRef<HTMLInputElement>(null)
    };

    const previewContainerRef = useRef<HTMLDivElement>(null);
    const iframeRef = useRef<HTMLIFrameElement>(null);
    const [iframeLoaded, setIframeLoaded] = useState(false);

    // Bộ đồng bộ hóa thời gian thực (DOM Synchronization) vào Iframe Storefront
    const syncIframeDOM = () => {
        const iframe = iframeRef.current;
        if (!iframe || !iframe.contentDocument) return;
        const doc = iframe.contentDocument;

        try {
            // 1. Cập nhật các biến CSS màu sắc trên :root của iframe
            const root = doc.documentElement;
            if (root) {
                root.style.setProperty('--header-bg-color', settings.header_bg_color || '#0046ab');
                root.style.setProperty('--header-text-color', settings.header_text_color || '#ffffff');
                root.style.setProperty('--announcement-bg-color', settings.announcement_bg_color || '#003380');
                root.style.setProperty('--announcement-text-color', settings.announcement_text_color || '#ffffff');
                root.style.setProperty('--footer-bg-color', settings.footer_bg_color || '#ffffff');
                root.style.setProperty('--footer-text-color', settings.footer_text_color || '#555555');
                root.style.setProperty('--footer-heading-color', settings.footer_heading_color || '#1e293b');
            }

            // 2. Tiêm stylesheet highlight nếu chưa có
            let customStyle = doc.getElementById('customizer-preview-styles');
            if (!customStyle) {
                customStyle = doc.createElement('style');
                customStyle.id = 'customizer-preview-styles';
                doc.head.appendChild(customStyle);
            }
            customStyle.innerHTML = `
                .customizer-highlight {
                    transition: all 0.3s ease !important;
                }
                .active-highlight {
                    outline: 3px dashed #d70018 !important;
                    outline-offset: -3px !important;
                    box-shadow: 0 0 15px rgba(215, 0, 24, 0.35) !important;
                    background-color: rgba(215, 0, 24, 0.02) !important;
                }
                /* Ngăn click hoặc chuyển hướng trên trang mô phỏng */
                iframe a, iframe button, iframe form {
                    pointer-events: none !important;
                }
                /* Ẩn hoàn toàn phần nội dung giữa, chatbot, thanh so sánh theo yêu cầu của user */
                main, 
                .chatbot-fab, 
                #chatbot-fab, 
                #ai-chat-window, 
                #pending-payment-alert, 
                .compare-bar, 
                .compare-bar-inner, 
                .compare-floating-bar {
                    display: none !important;
                }
                /* Tối ưu hóa body của trang mô phỏng */
                body {
                    padding-bottom: 0 !important;
                    margin-bottom: 0 !important;
                    background-color: #f8fafc !important;
                }
            `;

            // 3. Cập nhật Logo thương hiệu
            const logoLink = doc.querySelector('header.header-main a.logo');
            if (logoLink) {
                if (settings.logo || settings.logo_preview) {
                    const logoUrl = settings.logo_preview ? settings.logo_preview : (settings.logo ? asset_url + settings.logo : '');
                    logoLink.innerHTML = `<img src="${logoUrl}" alt="Logo" style="max-height: 40px; width: auto; object-fit: contain;">`;
                } else {
                    logoLink.innerHTML = `<i class="fa-solid fa-bolt"></i> ${settings.site_name || 'DIENMAY'}<span>${settings.site_suffix || 'PRO'}</span>`;
                }
            }

            // 4. Cập nhật Thanh thông báo Topbar ẩn hiện & Nội dung
            const topBar = doc.querySelector('.top-bar');
            if (topBar) {
                if (settings.announcement_show === '0') {
                    topBar.style.display = 'none';
                } else {
                    topBar.style.display = '';
                    
                    // Cập nhật text 1, 2, 3
                    const text1Span = topBar.querySelector('.top-bar-left span:nth-child(1)');
                    if (text1Span) text1Span.innerHTML = `<i class="fa-solid fa-recycle"></i> ${settings.topbar_text_1 || 'Thu cũ đổi mới'}`;
                    
                    const text2Span = topBar.querySelector('.top-bar-left span:nth-child(2)');
                    if (text2Span) text2Span.innerHTML = `<i class="fa-solid fa-certificate"></i> ${settings.topbar_text_2 || '100% chính hãng'}`;
                    
                    const text3Span = topBar.querySelector('.top-bar-left span:nth-child(3)');
                    if (text3Span) text3Span.innerHTML = `<i class="fa-solid fa-truck-fast"></i> ${settings.topbar_text_3 || 'Giao hàng cực nhanh'}`;
                    
                    // Cập nhật text 4
                    const text4Span = topBar.querySelector('.top-bar-right span:nth-child(1)');
                    if (text4Span) text4Span.innerHTML = `<i class="fa-solid fa-store"></i> ${settings.topbar_text_4 || 'Cửa hàng gần nhất'}`;
                    
                    // Cập nhật Hotline
                    const hotlineStrong = topBar.querySelector('.top-bar-right strong');
                    if (hotlineStrong) hotlineStrong.innerText = settings.hotline || '1800 2097';
                }
            }

            // 5. Cập nhật Footer Column 1 titles và hotline
            const footer = doc.querySelector('footer.footer');
            if (footer) {
                const col1 = footer.querySelector('.footer-grid .footer-col:nth-child(1)');
                if (col1) {
                    const h4 = col1.querySelector('h4');
                    if (h4) h4.innerText = settings.footer_col_1_title || 'TỔNG ĐÀI HỖ TRỢ';
                    
                    const li1Strong = col1.querySelector('ul li:nth-child(1) strong');
                    if (li1Strong) li1Strong.innerText = settings.footer_hotline_buy || '1800.1060';
                    
                    const li2Strong = col1.querySelector('ul li:nth-child(2) strong');
                    if (li2Strong) li2Strong.innerText = settings.footer_hotline_tech || '1800.1763';
                    
                    const li3Strong = col1.querySelector('ul li:nth-child(3) strong');
                    if (li3Strong) li3Strong.innerText = settings.footer_hotline_complaint || '1800.1062';
                    
                    const li4Strong = col1.querySelector('ul li:nth-child(4) strong');
                    if (li4Strong) li4Strong.innerText = settings.footer_hotline_warranty || '1800.1064';

                    // Cập nhật địa chỉ và email
                    const addrIcon = col1.querySelector('ul li i.fa-location-dot');
                    if (addrIcon && addrIcon.parentNode) {
                        addrIcon.parentNode.innerHTML = `<i class="fa-solid fa-location-dot" style="margin-right: 5px;"></i> ${settings.address || ''}`;
                    }
                    const emailIcon = col1.querySelector('ul li i.fa-envelope');
                    if (emailIcon && emailIcon.parentNode) {
                        emailIcon.parentNode.innerHTML = `<i class="fa-solid fa-envelope" style="margin-right: 5px;"></i> ${settings.email || ''}`;
                    }
                }

                // Cập nhật Footer Column 2 (Về chúng tôi)
                const col2 = footer.querySelector('.footer-grid .footer-col:nth-child(2)');
                if (col2) {
                    const h4 = col2.querySelector('h4');
                    if (h4) h4.innerText = settings.footer_col_2_title || 'VỀ CHÚNG TÔI';
                    
                    const ul2 = col2.querySelector('ul');
                    if (ul2) {
                        ul2.innerHTML = '';
                        if (col2Links.length > 0) {
                            col2Links.forEach(lnk => {
                                const li = doc.createElement('li');
                                const a = doc.createElement('a');
                                a.href = lnk.url || '#';
                                a.innerText = lnk.label || '';
                                li.appendChild(a);
                                ul2.appendChild(li);
                            });
                        } else {
                            ['Giới thiệu công ty', 'Hệ thống sản phẩm', 'Tin công nghệ', 'Góc video'].forEach(x => {
                                const li = doc.createElement('li');
                                const a = doc.createElement('a');
                                a.href = '#';
                                a.innerText = x;
                                li.appendChild(a);
                                ul2.appendChild(li);
                            });
                        }
                    }
                }

                // Cập nhật Footer Column 3 (Chính sách)
                const col3 = footer.querySelector('.footer-grid .footer-col:nth-child(3)');
                if (col3) {
                    const h4 = col3.querySelector('h4');
                    if (h4) h4.innerText = settings.footer_col_3_title || 'CHÍNH SÁCH';
                    
                    const ul3 = col3.querySelector('ul');
                    if (ul3) {
                        ul3.innerHTML = '';
                        if (col3Links.length > 0) {
                            col3Links.forEach(lnk => {
                                const li = doc.createElement('li');
                                const a = doc.createElement('a');
                                a.href = lnk.url || '#';
                                a.innerText = lnk.label || '';
                                li.appendChild(a);
                                ul3.appendChild(li);
                            });
                        } else {
                            ['Tích điểm đổi quà', 'Chính sách bảo hành', 'Chính sách đổi trả', 'Bảo mật thông tin'].forEach(x => {
                                const li = doc.createElement('li');
                                const a = doc.createElement('a');
                                a.href = '#';
                                a.innerText = x;
                                li.appendChild(a);
                                ul3.appendChild(li);
                            });
                        }
                    }
                }

                // Cập nhật Footer Column 4 (Mạng xã hội)
                const col4 = footer.querySelector('.footer-grid .footer-col:nth-child(4)');
                if (col4) {
                    const h4 = col4.querySelector('h4');
                    if (h4) h4.innerText = settings.footer_col_4_title || 'KẾT NỐI MẠNG XÃ HỘI';
                    
                    const socialContainer = col4.querySelector('.social-icons');
                    if (socialContainer) {
                        socialContainer.innerHTML = '';
                        const socials = [
                            { key: 'facebook', icon: 'fa-facebook' },
                            { key: 'youtube', icon: 'fa-youtube' },
                            { key: 'tiktok', icon: 'fa-tiktok' },
                            { key: 'instagram', icon: 'fa-instagram' }
                        ];
                        socials.forEach(s => {
                            const url = settings[`social_${s.key}`];
                            if (url) {
                                const a = doc.createElement('a');
                                a.href = url;
                                a.target = '_blank';
                                const i = doc.createElement('i');
                                i.className = `fa-brands ${s.icon}`;
                                a.appendChild(i);
                                socialContainer.appendChild(a);
                            } else {
                                const i = doc.createElement('i');
                                i.className = `fa-brands ${s.icon}`;
                                i.style.color = '#cbd5e1';
                                i.style.opacity = '0.5';
                                socialContainer.appendChild(i);
                            }
                        });
                    }
                }

                // Cập nhật Footer Column 5 (Khuyến mãi)
                const col5 = footer.querySelector('.footer-grid .footer-col:nth-child(5)');
                if (col5) {
                    const h4 = col5.querySelector('h4');
                    if (h4) h4.innerText = settings.footer_col_5_title || 'NHẬN KHUYẾN MÃI';
                    
                    const descP = col5.querySelector('p');
                    if (descP) descP.innerHTML = settings.footer_subscribe_desc || 'Đăng ký ngay để nhận ưu đãi <strong>giảm 10%</strong> cho đơn hàng đầu tiên!';
                }

                // Cập nhật Copyright
                const copyrightBarP = footer.querySelector('.footer-copyright-bar p');
                if (copyrightBarP) {
                    copyrightBarP.innerText = settings.footer_copyright || `© 2026 ${settings.site_name || 'DIENMAYPRO'} - DESIGNED BY DIENMAYPRO. All Rights Reserved.`;
                }
            }

            // 6. Cập nhật các lớp Highlight trên đầu trang & chân trang (Không dùng badge đè chữ)
            const headerMain = doc.querySelector('header.header-main');
            
            // Xóa các lớp cũ
            doc.querySelectorAll('.customizer-highlight').forEach(el => el.classList.remove('customizer-highlight', 'active-highlight'));

            if (activeTab === 'header') {
                if (topBar && settings.announcement_show !== '0') {
                    topBar.classList.add('customizer-highlight', 'active-highlight');
                }
                if (headerMain) {
                    headerMain.classList.add('customizer-highlight', 'active-highlight');
                }
                setTimeout(() => {
                    iframe.contentWindow?.scrollTo({ top: 0, behavior: 'smooth' });
                }, 80);
            } else if (activeTab === 'footer') {
                if (footer) {
                    footer.classList.add('customizer-highlight', 'active-highlight');
                }
                setTimeout(() => {
                    iframe.contentWindow?.scrollTo({ top: doc.body.scrollHeight || 2000, behavior: 'smooth' });
                }, 80);
            }
        } catch (e) {
            console.error('Error syncing customizer state to storefront iframe:', e);
        }
    };

    // Tự động đồng bộ hóa mỗi khi các cài đặt thay đổi
    useEffect(() => {
        if (iframeLoaded) {
            syncIframeDOM();
        }
    }, [settings, col2Links, col3Links, activeTab, iframeLoaded]);

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
        const { name, value } = e.target;
        setSettings(prev => ({ ...prev, [name]: value }));
    };

    const applyPreset = (presetColors: Record<string, string>) => {
        setSettings(prev => ({
            ...prev,
            ...presetColors
        }));
    };

    const handleFileChange = (key: keyof typeof fileInputRefs) => {
        const file = fileInputRefs[key].current?.files?.[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                setSettings(prev => ({ ...prev, [key + '_preview']: e.target?.result }));
            };
            reader.readAsDataURL(file);
        }
    };

    const handleRemoveLogo = () => {
        Swal.fire({
            title: 'Xác nhận xóa?',
            text: "Hệ thống sẽ xóa logo hình ảnh và chuyển về logo chữ mặc định.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                setSettings(prev => ({ ...prev, logo: '', logo_preview: '' }));
                if (fileInputRefs.logo.current) {
                    fileInputRefs.logo.current.value = '';
                }
            }
        });
    };

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSaving(true);
        const fd = new FormData();

        // Nạp toàn bộ settings thông thường
        Object.keys(settings).forEach(k => {
            if (!k.endsWith('_preview')) {
                fd.append(k, settings[k] === null || settings[k] === undefined ? '' : settings[k]);
            }
        });

        // Xử lý gửi logo file hoặc đánh dấu xóa logo
        if (fileInputRefs.logo.current?.files?.[0]) {
            fd.append('logo', fileInputRefs.logo.current.files[0]);
        } else if (!settings.logo) {
            fd.append('remove_logo', '1');
        }

        // Đóng gói mảng liên kết động chân trang gửi lên máy chủ
        fd.append('footer_col_2_links', JSON.stringify(col2Links));
        fd.append('footer_col_3_links', JSON.stringify(col3Links));

        try {
            const res = await axios.post('/admin/settings/theme', fd, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'Accept': 'application/json'
                }
            });
            if (res.data.success) {
                Swal.fire({
                    title: 'Đã lưu cài đặt!',
                    text: res.data.message || 'Cập nhật cấu hình website thành công.',
                    icon: 'success',
                    confirmButtonColor: '#4f46e5'
                });
                if (res.data.settings) {
                    setSettings(res.data.settings);
                }
            }
        } catch (error) {
            Swal.fire({
                title: 'Lỗi máy chủ!',
                text: 'Không thể kết nối hoặc lưu dữ liệu cài đặt giao diện.',
                icon: 'error',
                confirmButtonColor: '#4f46e5'
            });
        } finally {
            setIsSaving(false);
        }
    };

    const getImageUrl = (key: string, defaultPath: string) => {
        if (settings[key + '_preview']) return settings[key + '_preview'] as string;
        if (settings[key]) return asset_url + settings[key];
        return asset_url + defaultPath;
    };

    return (
        <div className="w-full min-h-screen bg-transparent flex flex-col gap-6 pb-16 font-sans text-slate-800 animate-in fade-in duration-500">
            
            {/* Grid 2 Cột: Bảng cài đặt bên trái & Live Preview bên phải */}
            <div className="grid grid-cols-1 xl:grid-cols-12 gap-8 items-start w-full">
                
                {/* ==================== CỘT 1: BẢNG ĐIỀU KHIỂN CUSTOMIZER (5/12) ==================== */}
                <div className="xl:col-span-5 flex flex-col gap-6">
                    
                    {/* Bảng điều khiển chính với hiệu ứng Glassmorphism & Shadow đặc trưng */}
                    <div className="bg-white rounded-3xl border border-slate-200/60 shadow-[0_15px_45px_rgba(0,0,0,0.03)] p-6 space-y-6">
                        
                        {/* Tab Switcher hiện đại dạng Pill tích hợp Icon */}
                        <div className="flex flex-col bg-slate-100 p-2 rounded-2xl border border-slate-200/50 gap-2">
                            <button
                                type="button"
                                onClick={() => setActiveTab('header')}
                                className={`w-full py-3.5 rounded-xl font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2.5 transition-all ${activeTab === 'header' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-500 hover:text-slate-800'}`}
                            >
                                <i className="fa-solid fa-window-maximize text-sm"></i>
                                Tùy biến Header (Đầu trang)
                            </button>
                            <button
                                type="button"
                                onClick={() => setActiveTab('footer')}
                                className={`w-full py-3.5 rounded-xl font-black text-xs uppercase tracking-wider flex items-center justify-center gap-2.5 transition-all ${activeTab === 'footer' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-500 hover:text-slate-800'}`}
                            >
                                <i className="fa-solid fa-window-restore text-sm"></i>
                                Tùy biến Footer (Chân trang)
                            </button>
                        </div>

                        {/* Tiêu đề & Nút Lưu */}
                        <div className="flex items-center justify-between border-b border-slate-100 pb-5">
                            <div>
                                <h3 className="text-base font-black text-slate-800 uppercase tracking-tight flex items-center gap-2.5">
                                    <span className="w-2.5 h-6 bg-gradient-to-b from-indigo-500 to-indigo-600 rounded-full inline-block"></span>
                                    {activeTab === 'header' ? 'Cấu hình Đầu trang' : 'Cấu hình Chân trang'}
                                </h3>
                                <p className="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider mt-1">
                                    Thiết kế & đồng bộ giao diện storefront
                                </p>
                            </div>
                            <button 
                                type="button"
                                onClick={handleSave} 
                                disabled={isSaving} 
                                className="px-5 py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-600/15 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-2"
                            >
                                {isSaving ? (
                                    <>
                                        <div className="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                        Đang lưu...
                                    </>
                                ) : (
                                    <>
                                        <i className="fa-solid fa-floppy-disk text-xs"></i>
                                        Lưu cài đặt
                                    </>
                                )}
                            </button>
                        </div>

                        {/* 🎨 Bảng phối màu sẵn có (Presets Picker) */}
                        <div className="bg-slate-50 p-5 rounded-2xl border border-slate-200/60 shadow-inner">
                            <h4 className="text-[10px] font-black text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                <i className="fa-solid fa-palette text-indigo-500 text-xs"></i>
                                🎨 Bảng phối màu nhanh (Presets)
                            </h4>
                            <div className="grid grid-cols-2 gap-2">
                                {COLOR_PRESETS.map((p, idx) => (
                                    <button
                                        key={idx}
                                        type="button"
                                        onClick={() => applyPreset(p.colors)}
                                        className="p-2.5 bg-white hover:bg-slate-50 border border-slate-200 rounded-xl text-[10px] font-extrabold flex items-center gap-2 transition-all active:scale-95 shadow-sm text-slate-600 group"
                                    >
                                        <span className="w-3.5 h-3.5 rounded-full inline-block border border-slate-200/80 shrink-0 group-hover:scale-105 transition-transform" style={{ backgroundColor: p.colors.header_bg_color }}></span>
                                        <span className="truncate">{p.name}</span>
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* ==================== DANH SÁCH FORM FIELDS ==================== */}
                        <div className="space-y-4">
                            
                            {/* ---------- PHÂN HỆ 1: HEADER & TOPBAR ---------- */}
                            {activeTab === 'header' && (
                                <div className="space-y-4">
                                    
                                    {/* Khối: Thương hiệu & Logo */}
                                    <CollapsibleCard title="⚡ Thương hiệu & Logo" id="brand" isOpen={expandedSections.brand} onToggle={toggleSection}>
                                        
                                        {/* Upload Logo */}
                                        <div className="bg-slate-50/50 p-4.5 rounded-2xl border border-slate-200/50 flex items-center gap-5 mb-4">
                                            <div className="w-20 h-20 bg-white rounded-xl border border-slate-200 flex items-center justify-center p-2.5 relative overflow-hidden group/img shadow-inner">
                                                {settings.logo || settings.logo_preview ? (
                                                    <>
                                                        <img src={getImageUrl('logo', 'images/logo.png')} className="max-h-full max-w-full object-contain" />
                                                        <div className="absolute inset-0 bg-black/60 opacity-0 group-hover/img:opacity-100 transition-opacity flex flex-col gap-1.5 items-center justify-center text-white">
                                                            <label className="cursor-pointer font-bold text-[8px] uppercase hover:underline">Thay đổi</label>
                                                            <button type="button" onClick={handleRemoveLogo} className="font-bold text-[8px] uppercase text-rose-400 hover:underline">Xóa bỏ</button>
                                                        </div>
                                                    </>
                                                ) : (
                                                    <div className="text-slate-400 font-black text-center text-[9px] uppercase tracking-wider">Chưa có ảnh</div>
                                                )}
                                                <input type="file" className="hidden" ref={fileInputRefs.logo} onChange={() => handleFileChange('logo')} accept="image/*" />
                                            </div>
                                            <div className="flex-1 space-y-2">
                                                <h5 className="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none">Logo dạng hình ảnh</h5>
                                                <p className="text-[9px] text-slate-400 font-semibold leading-normal">Chọn ảnh định dạng PNG, JPG, SVG có nền trong suốt.</p>
                                                <button 
                                                    type="button"
                                                    onClick={() => fileInputRefs.logo.current?.click()} 
                                                    className="px-3.5 py-2 bg-indigo-55 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-lg font-black text-[9px] uppercase tracking-wider shadow-sm hover:bg-indigo-100 transition-all"
                                                >
                                                    Tải ảnh lên
                                                </button>
                                            </div>
                                        </div>

                                        <div className="grid grid-cols-2 gap-4">
                                            <InputField label="Tên Logo (Chữ chính)" name="site_name" value={settings.site_name} onChange={handleInputChange} icon="fa-font" />
                                            <InputField label="Hậu tố Logo (Chữ phụ)" name="site_suffix" value={settings.site_suffix} onChange={handleInputChange} icon="fa-text-width" />
                                        </div>
                                        <div className="mt-4">
                                            <InputField label="Hotline Toàn hệ thống" name="hotline" value={settings.hotline} onChange={handleInputChange} icon="fa-phone-volume" />
                                        </div>
                                    </CollapsibleCard>

                                    {/* Khối: Màu sắc Header */}
                                    <CollapsibleCard title="🎨 Màu sắc Header" id="headerColors" isOpen={expandedSections.headerColors} onToggle={toggleSection}>
                                        <div className="grid grid-cols-2 gap-4">
                                            <ColorInputField label="Nền Header" name="header_bg_color" value={settings.header_bg_color} onChange={handleInputChange} />
                                            <ColorInputField label="Chữ & Icon Header" name="header_text_color" value={settings.header_text_color} onChange={handleInputChange} />
                                        </div>
                                    </CollapsibleCard>

                                    {/* Khối: Thanh thông báo Topbar */}
                                    <CollapsibleCard title="📢 Thanh thông báo Topbar" id="topbar" isOpen={expandedSections.topbar} onToggle={toggleSection}>
                                        
                                        {/* Styled Toggle Switch */}
                                        <div className="flex items-center justify-between bg-slate-50 p-4.5 rounded-2xl border border-slate-200/50 shadow-inner mb-4">
                                            <div>
                                                <h5 className="text-[10px] font-black text-slate-500 uppercase tracking-wider">Trạng thái Topbar</h5>
                                                <p className="text-[9px] text-slate-400 font-semibold mt-0.5">Bật hoặc ẩn thanh chạy thông báo đầu trang.</p>
                                            </div>
                                            <div className="relative inline-flex items-center cursor-pointer select-none">
                                                <input 
                                                    type="checkbox" 
                                                    name="announcement_show"
                                                    id="announcement_show_toggle"
                                                    checked={settings.announcement_show !== '0'} 
                                                    onChange={(e) => setSettings(prev => ({ ...prev, announcement_show: e.target.checked ? '1' : '0' }))}
                                                    className="sr-only peer"
                                                />
                                                <div className="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-350 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                            </div>
                                        </div>

                                        {settings.announcement_show !== '0' && (
                                            <div className="space-y-4 pt-2 animate-in slide-in-from-top-3 duration-300">
                                                <div className="grid grid-cols-2 gap-4">
                                                    <ColorInputField label="Màu nền Topbar" name="announcement_bg_color" value={settings.announcement_bg_color} onChange={handleInputChange} />
                                                    <ColorInputField label="Màu chữ Topbar" name="announcement_text_color" value={settings.announcement_text_color} onChange={handleInputChange} />
                                                </div>
                                                <div className="space-y-3 pt-2">
                                                    <InputField label="Thông báo chạy 1" name="topbar_text_1" value={settings.topbar_text_1} onChange={handleInputChange} icon="fa-bullhorn" />
                                                    <InputField label="Thông báo chạy 2" name="topbar_text_2" value={settings.topbar_text_2} onChange={handleInputChange} icon="fa-bullhorn" />
                                                    <InputField label="Thông báo chạy 3" name="topbar_text_3" value={settings.topbar_text_3} onChange={handleInputChange} icon="fa-bullhorn" />
                                                    <InputField label="Địa chỉ cửa hàng/Showroom" name="topbar_text_4" value={settings.topbar_text_4} onChange={handleInputChange} icon="fa-location-dot" />
                                                </div>
                                            </div>
                                        )}
                                    </CollapsibleCard>

                                </div>
                            )}

                            {/* ---------- PHÂN HỆ 2: FOOTER CHÂN TRANG ---------- */}
                            {activeTab === 'footer' && (
                                <div className="space-y-4">
                                    
                                    {/* Khối: Thông tin liên hệ chân trang */}
                                    <CollapsibleCard title="📞 Cột 1: Thông tin liên hệ" id="footerContact" isOpen={expandedSections.footerContact} onToggle={toggleSection}>
                                        <div className="space-y-4">
                                            <InputField label="Tiêu đề Cột 1" name="footer_col_1_title" value={settings.footer_col_1_title} onChange={handleInputChange} icon="fa-heading" />
                                            
                                            <div className="grid grid-cols-2 gap-4">
                                                <InputField label="Hotline Gọi mua" name="footer_hotline_buy" value={settings.footer_hotline_buy} onChange={handleInputChange} icon="fa-phone" />
                                                <InputField label="Hotline Kỹ thuật" name="footer_hotline_tech" value={settings.footer_hotline_tech} onChange={handleInputChange} icon="fa-wrench" />
                                                <InputField label="Hotline Khiếu nại" name="footer_hotline_complaint" value={settings.footer_hotline_complaint} onChange={handleInputChange} icon="fa-face-frown-open" />
                                                <InputField label="Hotline Bảo hành" name="footer_hotline_warranty" value={settings.footer_hotline_warranty} onChange={handleInputChange} icon="fa-shield" />
                                            </div>
                                            
                                            <InputField label="Địa chỉ trụ sở" name="address" value={settings.address} onChange={handleInputChange} icon="fa-map-location-dot" />
                                            <InputField label="Email liên hệ doanh nghiệp" name="email" value={settings.email} onChange={handleInputChange} icon="fa-envelope" placeholder="sales@dienmaypro.com" />
                                        </div>
                                    </CollapsibleCard>

                                    {/* Khối: Màu sắc chân trang */}
                                    <CollapsibleCard title="🎨 Bảng màu Chân trang" id="footerColors" isOpen={expandedSections.footerColors} onToggle={toggleSection}>
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <ColorInputField label="Nền Chân trang" name="footer_bg_color" value={settings.footer_bg_color} onChange={handleInputChange} />
                                            <ColorInputField label="Màu chữ Footer" name="footer_text_color" value={settings.footer_text_color} onChange={handleInputChange} />
                                            <div className="md:col-span-2">
                                                <ColorInputField label="Màu tiêu đề cột Footer" name="footer_heading_color" value={settings.footer_heading_color} onChange={handleInputChange} />
                                            </div>
                                        </div>
                                    </CollapsibleCard>

                                    {/* Khối: Các danh mục liên kết động */}
                                    <CollapsibleCard title="🔗 Danh sách Liên kết Chân trang" id="footerLinks" isOpen={expandedSections.footerLinks} onToggle={toggleSection}>
                                        <div className="space-y-6">
                                            <div className="space-y-3">
                                                <InputField label="Tiêu đề Cột 2" name="footer_col_2_title" value={settings.footer_col_2_title} onChange={handleInputChange} icon="fa-heading" />
                                                <LinksListEditor title="Liên kết Cột 2" links={col2Links} onChange={setCol2Links} />
                                            </div>
                                            <div className="h-px bg-slate-100 my-4"></div>
                                            <div className="space-y-3">
                                                <InputField label="Tiêu đề Cột 3" name="footer_col_3_title" value={settings.footer_col_3_title} onChange={handleInputChange} icon="fa-heading" />
                                                <LinksListEditor title="Liên kết Cột 3" links={col3Links} onChange={setCol3Links} />
                                            </div>
                                        </div>
                                    </CollapsibleCard>

                                    {/* Khối: Kết nối mạng xã hội */}
                                    <CollapsibleCard title="🌍 Kết nối mạng xã hội" id="footerSocials" isOpen={expandedSections.footerSocials} onToggle={toggleSection}>
                                        <div className="space-y-3">
                                            <InputField label="Tiêu đề Cột 4 (Mạng xã hội)" name="footer_col_4_title" value={settings.footer_col_4_title} onChange={handleInputChange} icon="fa-heading" />
                                            <div className="grid grid-cols-2 gap-4">
                                                <InputField label="Liên kết Facebook" name="social_facebook" value={settings.social_facebook} onChange={handleInputChange} placeholder="https://facebook.com/..." icon="fa-brands fa-facebook" />
                                                <InputField label="Liên kết Youtube" name="social_youtube" value={settings.social_youtube} onChange={handleInputChange} placeholder="https://youtube.com/..." icon="fa-brands fa-youtube" />
                                                <InputField label="Liên kết TikTok" name="social_tiktok" value={settings.social_tiktok} onChange={handleInputChange} placeholder="https://tiktok.com/@..." icon="fa-brands fa-tiktok" />
                                                <InputField label="Liên kết Instagram" name="social_instagram" value={settings.social_instagram} onChange={handleInputChange} placeholder="https://instagram.com/..." icon="fa-brands fa-instagram" />
                                            </div>
                                        </div>
                                    </CollapsibleCard>

                                    {/* Khối: Nhận khuyến mãi & Bản quyền */}
                                    <CollapsibleCard title="🎁 Khuyến mãi & Bản quyền" id="footerSubscribe" isOpen={expandedSections.footerSubscribe} onToggle={toggleSection}>
                                        <div className="space-y-4">
                                            <InputField label="Tiêu đề Cột 5 (Đăng ký)" name="footer_col_5_title" value={settings.footer_col_5_title} onChange={handleInputChange} icon="fa-heading" />
                                            <div className="space-y-2">
                                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-wider ml-1">Mô tả chương trình khuyến mãi</label>
                                                <div className="relative">
                                                    <textarea 
                                                        name="footer_subscribe_desc" 
                                                        value={settings.footer_subscribe_desc || ''} 
                                                        onChange={handleInputChange} 
                                                        rows={3}
                                                        placeholder="Đăng ký nhận thông tin khuyến mãi..."
                                                        className="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 outline-none hover:border-slate-300 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner"
                                                    />
                                                </div>
                                            </div>
                                            <InputField label="Bản quyền dưới cùng (Copyright)" name="footer_copyright" value={settings.footer_copyright} onChange={handleInputChange} icon="fa-copyright" />
                                        </div>
                                    </CollapsibleCard>

                                </div>
                            )}

                        </div>
                    </div>
                </div>

                {/* ==================== CỘT 2: KHUNG XEM TRƯỚC THỜI GIAN THỰC (7/12) ==================== */}
                <div className="xl:col-span-7 bg-slate-900/5 backdrop-blur border border-slate-200/50 p-6 rounded-3xl flex flex-col gap-6 sticky top-6 shadow-inner">
                    
                    {/* Thanh điều khiển Live Preview (Đổi thiết bị, Tỉ lệ thu phóng) */}
                    <div className="flex flex-col sm:flex-row justify-between items-center bg-white p-3 rounded-2xl border border-slate-200/40 shadow-sm gap-3">
                        <div className="flex items-center gap-3">
                            <span className="text-[10px] font-black text-slate-400 uppercase tracking-wider pl-2">MÔ PHỎNG:</span>
                            <div className="flex bg-slate-100 p-0.5 rounded-xl border border-slate-200/60">
                                <button 
                                    type="button"
                                    onClick={() => setPreviewScale(0.85)} 
                                    className={`px-3 py-2 rounded-lg transition-all font-black text-[9px] tracking-wider flex items-center gap-1.5 ${previewScale >= 0.6 ? 'bg-white text-indigo-600 shadow-sm border border-slate-200/30' : 'text-slate-500 hover:text-slate-700'}`}
                                >
                                    <i className="fa-solid fa-desktop"></i>
                                    MÁY TÍNH
                                </button>
                                <button 
                                    type="button"
                                    onClick={() => setPreviewScale(0.45)} 
                                    className={`px-3 py-2 rounded-lg transition-all font-black text-[9px] tracking-wider flex items-center gap-1.5 ${previewScale < 0.6 ? 'bg-white text-indigo-600 shadow-sm border border-slate-200/30' : 'text-slate-500 hover:text-slate-700'}`}
                                >
                                    <i className="fa-solid fa-mobile-screen-button"></i>
                                    ĐIỆN THOẠI
                                </button>
                            </div>
                        </div>

                        {/* Slider zoom tự do */}
                        <div className="flex items-center gap-2 bg-slate-50 px-3.5 py-1.5 rounded-xl border border-slate-200/50 w-full sm:w-auto">
                            <span className="text-[9px] text-slate-400 font-extrabold shrink-0">THU PHÓNG:</span>
                            <input 
                                type="range" 
                                min="0.3" 
                                max="1.0" 
                                step="0.05"
                                value={previewScale} 
                                onChange={(e) => setPreviewScale(parseFloat(e.target.value))}
                                className="w-full sm:w-28 h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                            />
                            <span className="text-[10px] text-indigo-600 font-black w-10 text-right">{Math.round(previewScale * 100)}%</span>
                        </div>
                    </div>

                    {/* ==================== THIẾT BỊ MÔ PHỎNG VẬT LÝ ==================== */}
                    <div className={`relative overflow-hidden rounded-[2.5rem] bg-slate-100 h-[650px] shadow-inner p-4 border border-slate-250 ${previewScale < 0.6 ? 'flex justify-center items-center' : ''}`}>
                        
                        {/* 🖥️ PHIÊN BẢN DESKTOP: KHUNG TRÌNH DUYỆT MAC SAFARI MỊN MÀNG */}
                        {previewScale >= 0.6 ? (
                            <div 
                                className="absolute top-4 left-4 bg-white rounded-2xl shadow-2xl border border-slate-250 flex flex-col overflow-hidden origin-top-left transition-transform duration-500 shrink-0"
                                style={{ 
                                    transform: `scale(${previewScale})`, 
                                    height: `calc((100% - 32px) / ${previewScale})`, 
                                    width: `calc((100% - 32px) / ${previewScale})` 
                                }}
                            >
                                {/* Safari Tab Bar */}
                                <div className="bg-slate-100 px-4 py-3 flex items-center gap-4 border-b border-slate-200 select-none shrink-0">
                                    {/* 3 Nút macOS */}
                                    <div className="flex gap-1.5 shrink-0">
                                        <span className="w-3 h-3 rounded-full bg-rose-450 bg-rose-500 inline-block shadow-sm"></span>
                                        <span className="w-3 h-3 rounded-full bg-amber-450 bg-amber-500 inline-block shadow-sm"></span>
                                        <span className="w-3 h-3 rounded-full bg-emerald-450 bg-emerald-500 inline-block shadow-sm"></span>
                                    </div>
                                    {/* Thanh Địa chỉ Safari */}
                                    <div className="flex-1 bg-white border border-slate-200 text-[10px] font-bold text-slate-400 py-1 px-4 rounded-lg flex items-center justify-between shadow-inner">
                                        <span className="flex items-center gap-1.5">
                                            <i className="fa-solid fa-lock text-emerald-500"></i>
                                            https://dienmaypro.com.vn
                                        </span>
                                        <i className="fa-solid fa-rotate-right text-[8px] cursor-pointer"></i>
                                    </div>
                                    <div className="flex gap-3 text-slate-400 text-xs shrink-0 pl-1">
                                        <i className="fa-solid fa-chevron-left"></i>
                                        <i className="fa-solid fa-chevron-right"></i>
                                    </div>
                                </div>

                                {/* Khung cuộn nội dung Storefront */}
                                <div className="flex-1 bg-white relative">
                                    <iframe
                                        ref={iframeRef}
                                        src="/?theme_preview=1"
                                        className="w-full h-full border-0"
                                        onLoad={() => {
                                            setIframeLoaded(true);
                                            setTimeout(syncIframeDOM, 50);
                                        }}
                                    />
                                </div>
                            </div>
                        ) : (
                            /* 📱 PHIÊN BẢN MOBILE: BẰNG MỘT MẪU ĐIỆN THOẠI IPHONE CAO CẤP */
                            <div 
                                className="relative rounded-[3.5rem] bg-slate-950 p-3.5 shadow-2xl border-4 border-slate-800 overflow-hidden flex flex-col origin-top transition-transform duration-500 shrink-0"
                                style={{ transform: `scale(${previewScale * 1.6})`, height: '820px', width: '380px' }}
                            >
                                {/* Dynamic Island */}
                                <div className="absolute top-6 left-1/2 -translate-x-1/2 w-28 h-7 bg-black rounded-3xl z-50 flex items-center justify-between px-3 select-none">
                                    <div className="w-3 h-3 rounded-full bg-indigo-900/60 border border-slate-800 flex items-center justify-center">
                                        <div className="w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></div>
                                    </div>
                                </div>

                                {/* iPhone Screen Status Bar */}
                                <div className="bg-white px-8 pt-3 pb-2 flex justify-between items-center text-[10px] font-black text-slate-800 shrink-0 z-40 select-none">
                                    <span>09:41</span>
                                    <div className="flex items-center gap-1.5">
                                        <i className="fa-solid fa-signal"></i>
                                        <i className="fa-solid fa-wifi"></i>
                                        <i className="fa-solid fa-battery-three-quarters text-[11px]"></i>
                                    </div>
                                </div>

                                {/* Khung cuộn màn hình di động */}
                                <div className="flex-1 bg-white relative rounded-[2.5rem] overflow-hidden">
                                    <iframe
                                        ref={iframeRef}
                                        src="/?theme_preview=1"
                                        className="w-full h-full border-0"
                                        onLoad={() => {
                                            setIframeLoaded(true);
                                            setTimeout(syncIframeDOM, 50);
                                        }}
                                    />
                                </div>

                                {/* Home Indicator Bar */}
                                <div className="absolute bottom-2 left-1/2 -translate-x-1/2 w-32 h-1 bg-white/70 rounded-full z-50 select-none"></div>
                            </div>
                        )}
                    </div>
                </div>

            </div>
        </div>
    );
};

// ==================== 🛠️ SUB-COMPONENT: THẺ ACCORDION COLLAPSIBLE CARD ====================
interface CollapsibleCardProps {
    title: string;
    id: string;
    isOpen: boolean;
    onToggle: (id: string) => void;
    children: React.ReactNode;
}

const CollapsibleCard: React.FC<CollapsibleCardProps> = ({ title, id, isOpen, onToggle, children }) => {
    return (
        <div className="bg-slate-50/50 rounded-2xl border border-slate-200/60 shadow-sm overflow-hidden transition-all duration-300">
            <button
                type="button"
                onClick={() => onToggle(id)}
                className="w-full px-5 py-4 flex justify-between items-center font-bold text-xs uppercase tracking-wider text-slate-650 hover:bg-slate-100/50 transition-all select-none border-b border-transparent focus:outline-none"
                style={{ borderBottomColor: isOpen ? '#f1f5f9' : 'transparent' }}
            >
                <span>{title}</span>
                <i className={`fa-solid fa-chevron-right transition-transform duration-300 ${isOpen ? 'rotate-90 text-indigo-650' : 'text-slate-400'}`}></i>
            </button>
            
            <div 
                className={`transition-all duration-300 overflow-hidden ${isOpen ? 'max-h-[1000px] opacity-100 p-5' : 'max-h-0 opacity-0 p-0'}`}
            >
                {children}
            </div>
        </div>
    );
};

// ==================== 🛠️ SUB-COMPONENT: INPUT ĐIỀU KHIỂN CHUNG ====================
const InputField = ({ label, name, value, onChange, placeholder, icon }: any) => (
    <div className="space-y-2">
        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">{label}</label>
        <div className="relative flex items-center">
            {icon && (
                <div className="absolute left-4 text-slate-400 text-xs shrink-0 select-none">
                    <i className={icon}></i>
                </div>
            )}
            <input 
                name={name} 
                value={value || ''} 
                onChange={onChange} 
                placeholder={placeholder}
                className={`w-full ${icon ? 'pl-11' : 'px-4'} pr-4 py-2.5 bg-slate-50 hover:bg-slate-100/50 border border-slate-200 rounded-xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all text-xs font-semibold text-slate-700 outline-none shadow-sm`} 
            />
        </div>
    </div>
);

// ==================== 🎨 SUB-COMPONENT: THIẾT KẾ MÀU SẮC ĐỘNG CHỐNG BỊ MÓP XẸP ====================
const ColorInputField = ({ label, name, value, onChange }: any) => (
    <div className="space-y-2">
        <label className="text-[10px] font-bold text-slate-500 uppercase tracking-wider ml-1">{label}</label>
        <div className="relative flex items-center">
            {/* Styled Color Swatch Indicator (Prefix) */}
            <div className="absolute left-3 w-6 h-6 rounded-full border border-slate-200/80 shadow-sm flex items-center justify-center overflow-hidden cursor-pointer active:scale-95 transition-all shrink-0">
                <input 
                    type="color" 
                    name={name} 
                    value={value || '#ffffff'} 
                    onChange={onChange} 
                    className="absolute w-[200%] h-[200%] cursor-pointer border-0 p-0 opacity-0"
                />
                <div className="w-full h-full rounded-full transition-transform" style={{ backgroundColor: value || '#ffffff' }}></div>
            </div>
            {/* Text Input with padded left space to accommodate the prefix color swatch */}
            <input 
                name={name} 
                value={value || ''} 
                onChange={onChange} 
                placeholder="#ffffff"
                className="w-full pl-12 pr-4 py-2.5 bg-slate-50 hover:bg-slate-100/50 border border-slate-200 rounded-xl focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 transition-all text-xs font-mono font-bold uppercase text-slate-700 outline-none shadow-sm"
            />
        </div>
    </div>
);

// ==================== 🔗 SUB-COMPONENT: BỘ CHỈNH SỬA LIÊN KẾT ĐỘNG CHÂN TRANG ====================
interface LinksListEditorProps {
    title: string;
    links: any[];
    onChange: (links: any[]) => void;
}

const LinksListEditor: React.FC<LinksListEditorProps> = ({ title, links, onChange }) => {
    
    // Quick Add Presets
    const linkPresets = [
        { label: 'Chính sách bảo hành', url: '/policy/warranty' },
        { label: 'Chính sách đổi trả', url: '/policy/return' },
        { label: 'Hành trình đơn hàng', url: '/cart/tracking' },
        { label: 'Tuyển dụng', url: '/careers' },
        { label: 'Liên hệ trợ giúp', url: '/help' }
    ];

    const addLink = () => {
        onChange([...links, { label: 'Liên kết mới', url: '#' }]);
    };

    const addPreset = (p: { label: string, url: string }) => {
        onChange([...links, { label: p.label, url: p.url }]);
    };
    
    const updateLink = (index: number, key: string, val: string) => {
        const next = [...links];
        next[index] = { ...next[index], [key]: val };
        onChange(next);
    };

    const removeLink = (index: number) => {
        onChange(links.filter((_, i) => i !== index));
    };

    return (
        <div className="bg-slate-100/50 p-4.5 rounded-2xl border border-slate-200/60 space-y-4 shadow-sm">
            <div className="flex justify-between items-center">
                <h5 className="text-[10px] font-black text-slate-500 uppercase tracking-wider">{title}</h5>
                <button 
                    type="button" 
                    onClick={addLink} 
                    className="px-3 py-1.5 bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-lg font-black text-[9px] uppercase tracking-wider hover:bg-indigo-100 transition-all flex items-center gap-1"
                >
                    <i className="fa-solid fa-plus"></i>
                    Thêm dòng
                </button>
            </div>

            {/* Quick Picker */}
            <div className="flex flex-wrap gap-1.5 pb-3 border-b border-slate-200/50 items-center">
                <span className="text-[8px] text-slate-400 font-extrabold uppercase mr-1 flex items-center gap-1">
                    <i className="fa-solid fa-bolt-lightning text-yellow-500"></i> Thêm nhanh:
                </span>
                {linkPresets.map((p, idx) => (
                    <button
                        key={idx}
                        type="button"
                        onClick={() => addPreset(p)}
                        className="px-2.5 py-1 bg-white hover:bg-slate-50 rounded-lg text-[9px] font-bold text-slate-600 hover:text-slate-800 border border-slate-250 transition-all active:scale-95 shadow-sm"
                    >
                        + {p.label}
                    </button>
                ))}
            </div>
            
            <div className="space-y-2.5 max-h-[220px] overflow-y-auto pr-1.5 custom-scrollbar">
                {links.length === 0 ? (
                    <p className="text-[10px] text-slate-400 italic font-bold text-center py-4 bg-white rounded-xl border border-slate-200/40">Chưa cấu hình liên kết tùy chỉnh (Sẽ hiện liên kết mặc định).</p>
                ) : (
                    links.map((link, idx) => (
                        <div key={idx} className="flex gap-2 items-center bg-white p-2.5 rounded-xl border border-slate-200/40 shadow-sm animate-in zoom-in-95 duration-200">
                            <span className="text-[10px] font-black text-slate-400 w-5 text-center shrink-0">{idx + 1}</span>
                            <div className="flex-1 grid grid-cols-2 gap-2">
                                <input 
                                    value={link.label} 
                                    onChange={(e) => updateLink(idx, 'label', e.target.value)} 
                                    placeholder="Tên nhãn" 
                                    className="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100/50 border border-slate-200 rounded-lg text-[10px] font-semibold text-slate-700 outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-inner" 
                                />
                                <input 
                                    value={link.url} 
                                    onChange={(e) => updateLink(idx, 'url', e.target.value)} 
                                    placeholder="Đường dẫn (URL)" 
                                    className="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100/50 border border-slate-200 rounded-lg text-[10px] font-semibold text-slate-700 outline-none focus:border-indigo-500 focus:bg-white transition-all shadow-inner" 
                                />
                            </div>
                            <button 
                                type="button" 
                                onClick={() => removeLink(idx)} 
                                className="w-7 h-7 text-rose-500 hover:bg-rose-50 hover:text-rose-600 rounded-lg flex items-center justify-center font-black transition-all shrink-0 border border-transparent hover:border-rose-100"
                            >
                                <i className="fa-solid fa-trash-can text-[10px]"></i>
                            </button>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};

export default ThemeSettings;
