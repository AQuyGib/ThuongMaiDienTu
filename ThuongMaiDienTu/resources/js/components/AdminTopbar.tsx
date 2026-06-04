import React from 'react';
import { isEn, t } from '../helpers';
import CommunicationHub from './CommunicationHub';

// Định nghĩa kiểu dữ liệu cho Props truyền vào Topbar
interface AdminTopbarProps {
    pageTitle: string; // Tiêu đề của trang hiện tại
    todayDate: string; // Ngày hiện tại truyền từ Controller/Blade
    userRoleId: number; // Vai trò của user (1: Admin, 2: Manager, 4: Staff)
}

/**
 * Component thanh công cụ Quản trị phía trên (AdminTopbar).
 * Quản lý đồng hồ thời gian thực tế, chế độ toàn màn hình,
 * và danh mục chọn ngôn ngữ Dropdown quả địa cầu (English/Tiếng Việt).
 */
const AdminTopbar: React.FC<AdminTopbarProps> = ({ pageTitle, todayDate, userRoleId }) => {
    // Trạng thái phóng to/thu nhỏ toàn màn hình
    const [isFullscreen, setIsFullscreen] = React.useState(false);
    
    // Đồng hồ đếm giờ hiển thị thời gian thực tế
    const [currentTime, setCurrentTime] = React.useState(new Date());
    
    // Trạng thái đóng/mở menu chọn ngôn ngữ dropdown
    const [isLangDropdownOpen, setIsLangDropdownOpen] = React.useState(false);

    // Trạng thái đóng/mở Communication Hub
    const [isHubOpen, setIsHubOpen] = React.useState(false);
    const [unreadChatCount, setUnreadChatCount] = React.useState(3);
    
    // Sử dụng ref để kiểm tra vị trí click của chuột hỗ trợ click-outside
    const langDropdownRef = React.useRef<HTMLDivElement>(null);

    // Hiệu ứng đếm giây cập nhật đồng hồ mỗi 1 giây (1000ms)
    React.useEffect(() => {
        const timer = setInterval(() => {
            setCurrentTime(new Date());
        }, 1000);
        return () => clearInterval(timer);
    }, []);

    // Hiệu ứng lắng nghe sự kiện click chuột để đóng dropdown nếu click ra bên ngoài
    React.useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (langDropdownRef.current && !langDropdownRef.current.contains(event.target as Node)) {
                setIsLangDropdownOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    // Hàm phóng to / thu nhỏ trình duyệt toàn màn hình (Fullscreen API)
    const toggleFullscreen = () => {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            setIsFullscreen(true);
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
                setIsFullscreen(false);
            }
        }
    };

    // Định dạng giờ hiển thị theo từng ngôn ngữ (Tiếng Anh: 24h hoặc AM/PM, Tiếng Việt: 24h)
    const formatTime = (date: Date) => {
        return date.toLocaleTimeString(isEn() ? 'en-US' : 'vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    };

    // Định dạng thứ ngày hiển thị ở đồng hồ lớn
    const formatDate = (date: Date) => {
        return date.toLocaleDateString(isEn() ? 'en-US' : 'vi-VN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    };

    // Danh sách cấu hình các ngôn ngữ được hỗ trợ của ứng dụng
    const languages = [
        { code: 'vi', label: 'VI', name: 'Tiếng Việt', flag: '🇻🇳' },
        { code: 'en', label: 'EN', name: 'English', flag: '🇺🇸' },
    ];

    // Xác định ngôn ngữ hiện tại đang được áp dụng
    const currentLang = languages.find(l => l.code === (isEn() ? 'en' : 'vi')) || languages[0];

    return (
        <>
            <header className="h-20 md:h-28 bg-white/90 backdrop-blur-xl border-b border-slate-200/50 flex items-center justify-between px-4 md:px-12 z-10 shrink-0 sticky top-0 shadow-sm transition-all duration-300">
            {/* Nhóm trái: Tiêu đề trang & Nút đóng mở Sidebar */}
            <div className="flex items-center gap-3 md:gap-8 w-auto md:w-1/4 flex-1 min-w-0">
                <button 
                    onClick={() => window.dispatchEvent(new CustomEvent('admin-sidebar-toggle'))} 
                    className="w-10 h-10 md:w-12 md:h-12 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl md:rounded-2xl flex items-center justify-center transition-all duration-300 shadow-sm border border-indigo-100 group shrink-0" 
                    title={t("Thu gọn/Mở rộng Menu", "Collapse/Expand Menu")}
                >
                    <i className="fa-solid fa-bars-staggered transition-transform group-hover:rotate-12"></i>
                </button>

                <div className="flex flex-col min-w-0">
                    <h2 className="text-sm md:text-lg font-black text-slate-800 tracking-tighter uppercase leading-none mb-1 md:mb-1.5 truncate">
                        {pageTitle}
                    </h2>
                    <div className="flex items-center gap-2">
                        <div className="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse shadow-sm shadow-emerald-500/50"></div>
                        <p className="text-[8px] md:text-[9px] text-slate-400 font-bold uppercase tracking-[0.15em] leading-none">System Stable</p>
                    </div>
                </div>
            </div>

            {/* Nhóm giữa: Đồng hồ thời gian thực tế dịch động theo Locale */}
            <div className="hidden xl:flex flex-1 justify-center">
                <div className="flex items-center gap-6 bg-slate-50/80 px-8 py-2.5 rounded-3xl border border-slate-200/50 shadow-sm backdrop-blur-sm group hover:border-indigo-200 transition-colors duration-500">
                    <div className="flex flex-col items-center">
                        <div className="flex items-center gap-4">
                            <span className="text-2xl font-black text-slate-800 tracking-tighter tabular-nums leading-none group-hover:text-indigo-600 transition-colors">
                                {formatTime(currentTime)}
                            </span>
                            <div className="w-px h-5 bg-slate-300/50"></div>
                            <div className="flex flex-col">
                                <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">
                                    {currentTime.toLocaleDateString(isEn() ? 'en-US' : 'vi-VN', { weekday: 'long' })}
                                </span>
                                <span className="text-[11px] font-bold text-slate-600 uppercase tracking-tight leading-none whitespace-nowrap">
                                    {currentTime.toLocaleDateString(isEn() ? 'en-US' : 'vi-VN', { day: '2-digit', month: 'long', year: 'numeric' })}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Nhóm phải: Thanh điều khiển chứa Dropdown quả địa cầu chọn ngôn ngữ & Fullscreen */}
            <div className="flex items-center justify-end gap-2 md:gap-5 w-auto md:w-1/4 shrink-0">
                <div className="flex items-center gap-1 bg-slate-50/50 p-1 md:p-1.5 rounded-2xl border border-slate-100">
                    {/* Trình chọn Dropdown ngôn ngữ */}
                    <div className="relative shrink-0" ref={langDropdownRef}>
                        <button 
                            onClick={() => setIsLangDropdownOpen(!isLangDropdownOpen)}
                            className="w-12 h-8 md:w-16 md:h-10 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-lg md:rounded-xl flex items-center justify-center gap-1 md:gap-1.5 transition-all group"
                            title={t("Ngôn ngữ", "Language")}
                        >
                            <i className="fa-solid fa-globe text-sm"></i>
                            {/* Hiển thị nhãn ngôn ngữ hiện tại (VI/EN) dưới dạng Badge nhãn nhỏ */}
                            <span className="bg-indigo-50 text-indigo-600 font-black px-1 md:px-1.5 py-0.5 rounded-md md:rounded-lg uppercase tracking-wider text-[8px] md:text-[9px] group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-200">
                                {currentLang.label}
                            </span>
                        </button>

                        {/* Danh sách Dropdown thả xuống */}
                        {isLangDropdownOpen && (
                            <div className="absolute right-0 mt-2 w-40 bg-white rounded-2xl shadow-2xl border border-slate-100 py-1.5 z-50 animate-in fade-in slide-in-from-top-2 duration-200 ring-1 ring-slate-900/5">
                                {languages.map((lang) => (
                                    <a
                                        key={lang.code}
                                        href={`/locale/${lang.code}`}
                                        className={`flex items-center justify-between px-3.5 py-2 text-xs font-bold transition-all hover:bg-indigo-50 hover:text-indigo-600 ${
                                            currentLang.code === lang.code ? 'text-indigo-600 bg-indigo-50/50' : 'text-slate-600'
                                        }`}
                                    >
                                        <span className="flex items-center gap-2">
                                            <span className="text-sm">{lang.flag}</span>
                                            <span>{lang.name}</span>
                                        </span>
                                        {currentLang.code === lang.code && <i className="fa-solid fa-check text-[10px] text-indigo-600"></i>}
                                    </a>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* Nút phóng to / thu nhỏ toàn màn hình */}
                    <button 
                        onClick={toggleFullscreen}
                        className="hidden md:flex w-10 h-10 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-xl items-center justify-center transition-all group"
                        title={t("Toàn màn hình", "Fullscreen")}
                    >
                        <i className={`fa-solid ${isFullscreen ? 'fa-compress' : 'fa-expand'} text-sm`}></i>
                    </button>

                    {/* Nút nhắn tin nhanh */}
                    {(userRoleId === 1 || userRoleId === 2) && (
                        <button 
                            onClick={() => setIsHubOpen(true)}
                            className="relative group w-8 h-8 md:w-10 md:h-10 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-lg md:rounded-xl flex items-center justify-center transition-all"
                            title={t("Trung tâm liên lạc", "Communication Hub")}
                        >
                            <i className="fa-solid fa-comment-dots text-sm"></i>
                            {unreadChatCount > 0 && (
                                <span className="absolute -top-1 -right-1 min-w-[14px] h-[14px] px-0.5 bg-indigo-500 text-white text-[8px] font-black flex items-center justify-center rounded-full border border-white animate-pulse">
                                    {unreadChatCount}
                                </span>
                            )}
                        </button>
                    )}

                    {/* Nút thông báo */}
                    <div className="relative group cursor-pointer w-8 h-8 md:w-10 md:h-10 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-lg md:rounded-xl flex items-center justify-center transition-all">
                        <i className="fa-solid fa-bell text-sm"></i>
                        <span className="absolute top-2 right-2 md:top-2.5 md:right-2.5 w-2 h-2 bg-rose-500 border-2 border-white rounded-full shadow-sm shadow-rose-500/50"></span>
                    </div>
                </div>

                {/* Nút tạo mới nhanh */}
                <button className="hidden sm:flex w-12 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl items-center justify-center shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-1 active:translate-y-0 group">
                    <i className="fa-solid fa-plus text-lg transition-transform group-hover:rotate-90"></i>
                </button>
            </div>
            </header>
            {/* Communication Hub Drawer */}
            <CommunicationHub 
                isOpen={isHubOpen} 
                onClose={() => setIsHubOpen(false)} 
                onUnreadChange={(count) => setUnreadChatCount(count)} 
                userRoleId={userRoleId}
            />
        </>
    );
};

export default AdminTopbar;
