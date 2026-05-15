import React from 'react';

interface AdminTopbarProps {
    pageTitle: string;
    todayDate: string;
}

const AdminTopbar: React.FC<AdminTopbarProps> = ({ pageTitle, todayDate }) => {
    const [isFullscreen, setIsFullscreen] = React.useState(false);
    const [currentTime, setCurrentTime] = React.useState(new Date());

    React.useEffect(() => {
        const timer = setInterval(() => {
            setCurrentTime(new Date());
        }, 1000);
        return () => clearInterval(timer);
    }, []);

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

    const formatTime = (date: Date) => {
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    };

    const formatDate = (date: Date) => {
        return date.toLocaleDateString('vi-VN', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    };

    return (
        <header className="h-28 bg-white/90 backdrop-blur-xl border-b border-slate-200/50 flex items-center justify-between px-12 z-10 shrink-0 sticky top-0 shadow-sm transition-all duration-300">
            {/* Left: Branding & Navigation */}
            <div className="flex items-center gap-8 w-1/4">
                <button 
                    onClick={() => window.dispatchEvent(new CustomEvent('admin-sidebar-toggle'))} 
                    className="w-12 h-12 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-2xl flex items-center justify-center transition-all duration-300 shadow-sm border border-indigo-100 group shrink-0" 
                    title="Thu gọn/Mở rộng Menu"
                >
                    <i className="fa-solid fa-bars-staggered transition-transform group-hover:rotate-12"></i>
                </button>

                <div className="flex flex-col shrink-0">
                    <h2 className="text-lg font-black text-slate-800 tracking-tighter uppercase leading-none mb-1.5">
                        {pageTitle}
                    </h2>
                    <div className="flex items-center gap-2">
                        <div className="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse shadow-sm shadow-emerald-500/50"></div>
                        <p className="text-[9px] text-slate-400 font-bold uppercase tracking-[0.15em] leading-none">System Stable</p>
                    </div>
                </div>
            </div>

            {/* Middle: Professional Clock */}
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
                                    {currentTime.toLocaleDateString('vi-VN', { weekday: 'long' })}
                                </span>
                                <span className="text-[11px] font-bold text-slate-600 uppercase tracking-tight leading-none whitespace-nowrap">
                                    {currentTime.toLocaleDateString('vi-VN', { day: '2-digit', month: 'long', year: 'numeric' })}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Right: Actions & Utilities */}
            <div className="flex items-center justify-end gap-5 w-1/4">
                <div className="flex items-center gap-1.5 bg-slate-50/50 p-1.5 rounded-2xl border border-slate-100">
                    {/* Fullscreen */}
                    <button 
                        onClick={toggleFullscreen}
                        className="w-10 h-10 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-xl flex items-center justify-center transition-all group"
                        title="Toàn màn hình"
                    >
                        <i className={`fa-solid ${isFullscreen ? 'fa-compress' : 'fa-expand'} text-sm`}></i>
                    </button>

                    {/* Messages */}
                    <div className="relative group cursor-pointer w-10 h-10 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-xl flex items-center justify-center transition-all">
                        <i className="fa-solid fa-comment-dots text-sm"></i>
                        <span className="absolute top-2.5 right-2.5 w-2 h-2 bg-indigo-500 border-2 border-white rounded-full"></span>
                    </div>

                    {/* Notifications */}
                    <div className="relative group cursor-pointer w-10 h-10 text-slate-400 hover:text-indigo-600 hover:bg-white rounded-xl flex items-center justify-center transition-all">
                        <i className="fa-solid fa-bell text-sm"></i>
                        <span className="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-500 border-2 border-white rounded-full shadow-sm shadow-rose-500/50"></span>
                    </div>
                </div>

                {/* Quick Add Button */}
                <button className="w-12 h-12 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-600/20 transition-all hover:-translate-y-1 active:translate-y-0 group">
                    <i className="fa-solid fa-plus text-lg transition-transform group-hover:rotate-90"></i>
                </button>
            </div>
        </header>
    );
};

export default AdminTopbar;
