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
    };
    menu: MenuItem[];
    homeRoute: string;
    logoutRoute: string;
    csrfToken: string;
}

const AdminSidebar: React.FC<AdminSidebarProps> = ({ user, menu, homeRoute, logoutRoute, csrfToken }) => {
    const [collapsed, setCollapsed] = useState(false);

    useEffect(() => {
        const handleToggle = () => setCollapsed(prev => !prev);
        window.addEventListener('admin-sidebar-toggle', handleToggle);
        return () => window.removeEventListener('admin-sidebar-toggle', handleToggle);
    }, []);

    if (!menu || !user) return <div className="p-4 text-white bg-rose-600">MISSING PROPS</div>;

    return (
        <aside
            className={`bg-slate-900 text-white h-full flex flex-col border-r-4 border-indigo-600 transition-all duration-300 ${collapsed ? 'w-20' : 'w-72'}`}
            style={{ minHeight: '100vh' }}
        >
            <div className="h-28 flex items-center px-8 border-b border-slate-800/50">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20 rotate-3">
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

            <nav className="flex-1 p-4 space-y-2 overflow-y-auto">
                {menu.map((item, idx) => (
                    <a
                        key={idx}
                        href={item.route}
                        className={`flex items-center gap-4 px-4 py-3 rounded-xl transition-colors ${item.active ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white'}`}
                    >
                        <i className={`${item.icon} w-5 text-center`}></i>
                        {!collapsed && <span className="font-bold text-sm truncate">{item.label}</span>}
                    </a>
                ))}
            </nav>

            <div className="p-4 bg-slate-950/50 backdrop-blur-xl border-t border-slate-800">
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
                                <p className="text-[9px] text-indigo-400 font-black uppercase tracking-widest mt-0.5">{user.role_name}</p>
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
                        <div className="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center font-bold shadow-lg shadow-indigo-600/20">
                            {user.full_name?.charAt(0)}
                        </div>
                        <div className="w-px h-8 bg-slate-800"></div>
                        <a href="/" className="w-10 h-10 bg-slate-800 hover:bg-indigo-600 text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all">
                            <i className="fa-solid fa-house"></i>
                        </a>
                        <form action={logoutRoute} method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <button type="submit" className="w-10 h-10 bg-slate-800 hover:bg-rose-600 text-slate-400 hover:text-white rounded-xl flex items-center justify-center transition-all">
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
