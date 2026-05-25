import React, { useState, useEffect } from 'react';

interface MenuItem {
    label: string;
    route: string;
    icon: string;
    active: boolean;
    section?: string;
}

interface AdminSidebarProps {
    user: {
        full_name: string;
        role_name: string;
        email?: string;
    };
    menu: MenuItem[];
    homeRoute: string;
    logoutRoute: string;
    csrfToken: string;
}

const getActiveIconColor = (label: string): string => {
    switch (label) {
        case 'Bảng điều khiển': return 'text-emerald-400';
        case 'Thống kê KPI': return 'text-cyan-300';
        case 'Đơn hàng': return 'text-orange-400';
        case 'Khách hàng': return 'text-sky-300';
        case 'Sổ Quỹ & Thu chi': return 'text-green-300';
        case 'Hóa đơn dịch vụ': return 'text-teal-300';
        case 'Phiếu sửa chữa': return 'text-amber-400';
        case 'Flash Sale': return 'text-red-400';
        case 'Sản phẩm': return 'text-indigo-300';
        case 'Bài viết & CMS': return 'text-fuchsia-300';
        case 'Quản lý Kho': return 'text-blue-300';
        case 'Góc video': return 'text-yellow-300';
        case 'Điều chuyển kho': return 'text-violet-400';
        case 'Nhà cung cấp': return 'text-pink-400';
        case 'Danh mục': return 'text-lime-300';
        case 'Đổi thưởng': return 'text-rose-400';
        case 'Tùy biến Giao diện': return 'text-purple-300';
        case 'Thông báo': return 'text-amber-300';
        case 'Quản lý Trang chủ': return 'text-teal-400';
        case 'Tài khoản': return 'text-violet-300';
        case 'Cài đặt hệ thống': return 'text-slate-300';
        case 'Nhật ký hoạt động': return 'text-orange-300';
        default: return 'text-yellow-300';
    }
};

const AdminSidebar: React.FC<AdminSidebarProps> = ({ user, menu, homeRoute, logoutRoute, csrfToken }) => {
    const [collapsed, setCollapsed] = useState(false);

    useEffect(() => {
        const handleToggle = () => setCollapsed(prev => !prev);
        window.addEventListener('admin-sidebar-toggle', handleToggle);
        return () => window.removeEventListener('admin-sidebar-toggle', handleToggle);
    }, []);

    useEffect(() => {
        const navEl = document.getElementById('admin-sidebar-nav');
        if (navEl) {
            const savedPosition = localStorage.getItem('sidebar-scroll-position');
            if (savedPosition) {
                setTimeout(() => {
                    navEl.scrollTop = parseInt(savedPosition, 10);
                }, 50);
            }
            
            const handleScroll = () => {
                localStorage.setItem('sidebar-scroll-position', navEl.scrollTop.toString());
            };
            
            navEl.addEventListener('scroll', handleScroll);
            return () => {
                navEl.removeEventListener('scroll', handleScroll);
            };
        }
    }, []);

    if (!menu || !user) return <div className="p-4 text-white bg-rose-600">MISSING PROPS</div>;

    return (
        <aside
            className={`bg-slate-900 text-white h-full flex flex-col border-r-4 border-indigo-600 transition-all duration-300 ${collapsed ? 'w-20' : 'w-72'}`}
            style={{ minHeight: '100vh' }}
        >
            <div className={`h-28 flex items-center border-b border-slate-800/50 transition-all ${collapsed ? 'justify-center px-0' : 'px-8'}`}>
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20 rotate-3 shrink-0">
                        <i className="fa-solid fa-bolt-lightning text-white text-lg"></i>
                    </div>
                    {!collapsed && (
                        <div className="flex flex-col">
                            <span className="text-xl font-black tracking-tighter leading-none text-white">
                                DIENMAY<span className="bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">PRO</span>
                            </span>
                            <span className="text-[8px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">DIENMAYPRO Admin</span>
                        </div>
                    )}
                </div>
            </div>

            <nav id="admin-sidebar-nav" className={`flex-1 space-y-6 overflow-y-auto premium-scrollbar transition-all ${collapsed ? 'p-2 px-3' : 'p-4'}`}>
                {(() => {
                    const sections: { [key: string]: MenuItem[] } = {};
                    menu.forEach(item => {
                        const s = item.section || 'Khác';
                        if (!sections[s]) sections[s] = [];
                        sections[s].push(item);
                    });

                    return Object.entries(sections).map(([sectionName, items], sIdx) => (
                        <div key={sIdx} className="space-y-1">
                            {!collapsed && (
                                <div className="px-4 mb-3">
                                    <p className="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">{sectionName}</p>
                                </div>
                            )}
                            {items.map((item, idx) => {
                                return (
                                <a
                                    key={idx}
                                    href={item.route}
                                    title={collapsed ? item.label : ''}
                                    className={`group flex items-center rounded-xl transition-all duration-300 ${collapsed ? 'justify-center py-4 px-0' : 'gap-4 px-4 py-3'} ${item.active ? 'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-600/25 ring-1 ring-white/10' : 'text-slate-400 hover:bg-slate-800 hover:text-white'}`}
                                >
                                    <i className={`${item.icon} ${collapsed ? 'text-lg' : 'w-5 text-center'} ${item.active ? `${getActiveIconColor(item.label)} drop-shadow` : ''}`}></i>
                                    {!collapsed && (
                                        <span className="flex items-center gap-2 min-w-0">
                                            <span className="font-bold text-sm truncate">{item.label}</span>
                                        </span>
                                    )}
                                </a>
                                );
                            })}
                            {collapsed && <div className="h-px bg-slate-800/50 my-4 mx-2" />}
                        </div>
                    ));
                })()}
            </nav>

            <div className={`bg-slate-950/50 backdrop-blur-xl border-t border-slate-800 transition-all ${collapsed ? 'p-2' : 'p-4'}`}>
                {!collapsed ? (
                    <div className="bg-gradient-to-br from-slate-800/50 to-slate-900/50 p-4 rounded-3xl border border-slate-700/50 mb-4 shadow-2xl">
                        <div className="flex items-center gap-3 mb-4">
                            <div className="relative">
                                <div className="w-12 h-12 bg-gradient-to-tr from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center font-black text-lg text-white shadow-xl shadow-indigo-500/20">
                                    {user.full_name?.charAt(0) || 'A'}
                                </div>
                                <div className="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 border-2 border-slate-900 rounded-full animate-pulse"></div>
                            </div>
                            <div className="flex-1 min-w-0">
                                <p className="text-xs font-black truncate uppercase tracking-tight text-white">{user.full_name}</p>
                                <div className="flex flex-col gap-0.5 mt-0.5">
                                    <p className="text-[9px] text-indigo-400 font-black uppercase tracking-widest">{user.role_name}</p>
                                    {user.email && <p className="text-[8px] text-slate-500 font-medium truncate">{user.email}</p>}
                                </div>
                            </div>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-2">
                            <a 
                                href="/" 
                                className="flex items-center justify-center gap-2 py-2.5 bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white rounded-xl transition-all duration-300 text-[9px] font-black uppercase tracking-widest group"
                            >
                                <i className="fa-solid fa-house group-hover:-translate-y-0.5 transition-transform"></i> Home
                            </a>
                            <form action={logoutRoute} method="POST" className="contents">
                                <input type="hidden" name="_token" value={csrfToken} />
                                <button 
                                    type="submit"
                                    className="flex items-center justify-center gap-2 py-2.5 bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white rounded-xl transition-all duration-300 text-[9px] font-black uppercase tracking-widest group"
                                >
                                    <i className="fa-solid fa-power-off group-hover:rotate-12 transition-transform"></i> Exit
                                </button>
                            </form>
                        </div>
                    </div>
                ) : (
                    <div className="flex flex-col items-center gap-4 py-2">
                        <div className="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-bold shadow-lg shadow-indigo-600/20 text-white">
                            {user.full_name?.charAt(0)}
                        </div>
                        <div className="w-full h-px bg-slate-800/50"></div>
                        <a href="/" title="Home" className="w-10 h-10 bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all shadow-lg">
                            <i className="fa-solid fa-house"></i>
                        </a>
                        <form action={logoutRoute} method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <button type="submit" title="Exit" className="w-10 h-10 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all shadow-lg">
                                <i className="fa-solid fa-power-off"></i>
                            </button>
                        </form>
                    </div>
                )}
            </div>
        </aside>
    );
};

export default AdminSidebar;
