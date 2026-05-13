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
            <div className="h-24 flex items-center px-6 border-b border-slate-800">
                <span className="text-xl font-black italic">DIENMAY<span className="text-indigo-500 font-bold">PRO</span></span>
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

            <div className="p-4 bg-slate-950 border-t border-slate-800">
                <div className="flex items-center gap-3 mb-4">
                    <div className="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center font-bold">
                        {user.full_name?.charAt(0) || 'A'}
                    </div>
                    {!collapsed && (
                        <div className="flex-1 min-w-0">
                            <p className="text-xs font-bold truncate uppercase">{user.full_name}</p>
                            <p className="text-[10px] text-slate-500 uppercase">{user.role_name}</p>
                        </div>
                    )}
                </div>
                <form action={logoutRoute} method="POST">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <button className="w-full py-2 bg-slate-800 hover:bg-rose-900/30 text-slate-400 hover:text-rose-400 rounded-lg text-[10px] font-bold uppercase transition-colors">
                        <i className="fa-solid fa-power-off mr-2"></i> {!collapsed && 'Thoát'}
                    </button>
                </form>
            </div>
        </aside>
    );
};

export default AdminSidebar;
