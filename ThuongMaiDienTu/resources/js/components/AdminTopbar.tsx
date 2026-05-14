import React from 'react';

interface AdminTopbarProps {
    pageTitle: string;
    todayDate: string;
}

const AdminTopbar: React.FC<AdminTopbarProps> = ({ pageTitle, todayDate }) => {
    return (
        <header className="h-24 bg-white/80 backdrop-blur-md border-b border-slate-100 flex items-center justify-between px-10 z-30 shrink-0 sticky top-0">
            <div className="flex items-center gap-8">
                {/* Menu Toggle */}
                <button 
                    onClick={() => window.dispatchEvent(new CustomEvent('admin-sidebar-toggle'))} 
                    className="w-12 h-12 bg-slate-50 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 rounded-2xl flex items-center justify-center transition-all shadow-sm border border-slate-100 group" 
                    title="Thu gọn/Mở rộng Menu"
                >
                    <i className="fa-solid fa-bars-staggered transition-transform group-hover:rotate-12"></i>
                </button>

                <div className="flex flex-col">
                    <h2 className="text-xl font-black text-slate-800 tracking-tighter uppercase leading-none mb-1">
                        {pageTitle}
                    </h2>
                    <div className="flex items-center gap-2">
                        <div className="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-pulse"></div>
                        <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest leading-none">Hệ thống đang hoạt động ổn định</p>
                    </div>
                </div>
            </div>

            <div className="flex items-center gap-4">
                <div className="hidden md:flex flex-col items-end mr-2">
                    <span className="text-[10px] text-slate-400 font-black uppercase tracking-widest">Hôm nay</span>
                    <span className="text-sm font-bold text-slate-700 leading-none">{todayDate}</span>
                </div>
                <div className="w-12 h-12 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-center text-slate-400 hover:bg-white hover:text-indigo-600 transition-all cursor-pointer shadow-sm">
                    <i className="fa-solid fa-bell"></i>
                </div>
            </div>
        </header>
    );
};

export default AdminTopbar;
