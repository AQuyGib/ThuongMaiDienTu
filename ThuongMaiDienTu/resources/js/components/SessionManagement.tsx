import React, { useState } from 'react';
import axios from 'axios';
import { 
  Monitor, Smartphone, Tablet, Globe, 
  Clock, MapPin, Shield, XCircle, 
  LogOut, Trash2, ShieldCheck, Loader2,
  AlertTriangle, UserCheck
} from 'lucide-react';
import { Button } from './ui/button';

interface Session {
  id: string;
  ip_address: string;
  user_agent: string;
  last_activity: number;
  browser: string;
  os: string;
  device: string;
  last_active: string;
}

interface SessionManagementProps {
  sessions: Session[];
  userName: string;
  revokeAllUrl: string;
  revokeUrl: string;
  csrfToken: string;
  currentSessionId: string;
}

export default function SessionManagement({ 
  sessions: initialSessions, 
  userName,
  revokeAllUrl, 
  revokeUrl, 
  csrfToken, 
  currentSessionId 
}: SessionManagementProps) {
  const [sessions, setSessions] = useState(initialSessions);
  const [loadingId, setLoadingId] = useState<string | null>(null);
  const [revokingAll, setRevokingAll] = useState(false);

  const handleRevoke = async (id: string) => {
    const isMe = id === currentSessionId;
    const msg = isMe 
      ? 'Đây là phiên đăng nhập CỦA BẠN (Admin). Nếu thu hồi, bạn sẽ bị văng khỏi hệ thống ngay lập tức. Xác nhận?'
      : `Bạn có chắc chắn muốn buộc thiết bị này của "${userName}" đăng xuất?`;

    if (!confirm(msg)) return;

    setLoadingId(id);
    try {
      await axios.post(`${revokeUrl}/${id}`, {
        _method: 'DELETE',
        _token: csrfToken
      }, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      
      setSessions(sessions.filter(s => s.id !== id));
      if (isMe) window.location.href = '/login';
    } catch (error) {
      alert('Lỗi hệ thống khi thu hồi phiên làm việc.');
    } finally {
      setLoadingId(null);
    }
  };

  const handleRevokeAll = async () => {
    if (!confirm(`Tất cả các thiết bị hiện tại của "${userName}" sẽ bị buộc đăng xuất ngay lập tức. Tiếp tục?`)) return;
    
    setRevokingAll(true);
    try {
      await axios.post(revokeAllUrl, { _token: csrfToken }, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      setSessions(sessions.filter(s => s.id === currentSessionId));
    } catch (error) {
      alert('Lỗi khi thu hồi tất cả phiên làm việc.');
    } finally {
      setRevokingAll(false);
    }
  };

  const getDeviceIcon = (device: string) => {
    if (device.toLowerCase().includes('phone')) return <Smartphone size={24} />;
    if (device.toLowerCase().includes('tablet')) return <Tablet size={24} />;
    return <Monitor size={24} />;
  };

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Admin Control Banner */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div className="bg-white dark:bg-slate-900 p-7 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-5">
          <div className="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center shadow-inner">
            <UserCheck size={28} />
          </div>
          <div>
            <div className="text-sm font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Đang kiểm soát</div>
            <div className="text-xl font-black text-slate-900 dark:text-white truncate max-w-[150px]">{userName}</div>
          </div>
        </div>

        <div className="md:col-span-2 bg-slate-900 dark:bg-slate-800 p-7 rounded-[2.5rem] text-white flex flex-col md:flex-row items-center justify-between gap-6 shadow-2xl shadow-slate-900/20 relative overflow-hidden">
          <div className="absolute right-0 top-0 w-32 h-32 bg-blue-500/10 rounded-full blur-3xl" />
          <div className="relative z-10">
            <h3 className="text-xl font-black tracking-tight mb-1 flex items-center gap-2">
              <Shield size={20} className="text-blue-500" /> Công cụ Quản trị viên
            </h3>
            <p className="text-slate-400 text-sm font-medium">Buộc kết thúc toàn bộ phiên đăng nhập của người dùng này.</p>
          </div>
          <Button 
            onClick={handleRevokeAll}
            disabled={revokingAll || sessions.length === 0}
            className="bg-rose-600 hover:bg-rose-700 text-white font-black uppercase text-[10px] tracking-widest px-8 h-12 rounded-2xl shadow-lg shadow-rose-600/20 transition-all relative z-10"
          >
            {revokingAll ? <Loader2 className="animate-spin mr-2" size={16} /> : <LogOut className="mr-2" size={16} />}
            Thu hồi toàn bộ phiên
          </Button>
        </div>
      </div>

      {/* Sessions Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {sessions.map((session) => (
          <div 
            key={session.id} 
            className={`group relative bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] border ${session.id === currentSessionId ? 'border-amber-500 ring-4 ring-amber-500/5' : 'border-slate-100 dark:border-slate-800'} shadow-sm hover:shadow-xl transition-all duration-300`}
          >
            <div className="flex items-start justify-between mb-6">
              <div className="flex items-center gap-4">
                <div className={`p-4 rounded-2xl ${session.id === currentSessionId ? 'bg-amber-500 text-white shadow-lg' : 'bg-slate-50 dark:bg-slate-800 text-slate-400 group-hover:bg-blue-50 group-hover:text-blue-600'} transition-all duration-500`}>
                  {getDeviceIcon(session.device)}
                </div>
                <div>
                  <div className="flex items-center gap-2">
                    <h4 className="font-black text-slate-900 dark:text-white text-lg tracking-tight">{session.browser} ({session.os})</h4>
                    {session.id === currentSessionId && (
                      <span className="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-500/10 text-amber-600 text-[9px] font-black uppercase tracking-widest border border-amber-500/20 animate-pulse">
                        <AlertTriangle size={10} /> Phiên của bạn
                      </span>
                    )}
                  </div>
                  <div className="flex items-center gap-4 mt-1">
                    <div className="flex items-center gap-1.5 text-xs text-slate-400 font-bold uppercase tracking-wider">
                      <Globe size={12} className="opacity-70" /> IP: {session.ip_address}
                    </div>
                    <div className="flex items-center gap-1.5 text-xs text-slate-400 font-bold uppercase tracking-wider">
                      <Clock size={12} className="opacity-70" /> {session.last_active}
                    </div>
                  </div>
                </div>
              </div>
              
              <Button 
                variant="ghost" 
                size="icon" 
                disabled={loadingId === session.id}
                onClick={() => handleRevoke(session.id)}
                className="h-12 w-12 rounded-2xl text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition-all"
              >
                {loadingId === session.id ? <Loader2 className="animate-spin" size={20} /> : <Trash2 size={20} />}
              </Button>
            </div>

            <div className="grid grid-cols-2 gap-4 pt-6 border-t border-slate-50 dark:border-slate-800">
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400"><MapPin size={16} /></div>
                <div>
                  <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nguồn truy cập</div>
                  <div className="text-xs font-bold text-slate-700 dark:text-slate-300">Việt Nam</div>
                </div>
              </div>
              <div className="flex items-center gap-3">
                <div className="p-2 rounded-xl bg-slate-50 dark:bg-slate-800 text-slate-400"><ShieldCheck size={16} /></div>
                <div>
                  <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Xác thực</div>
                  <div className="text-xs font-bold text-emerald-600 uppercase tracking-widest">Hợp lệ</div>
                </div>
              </div>
            </div>
          </div>
        ))}

        {sessions.length === 0 && (
          <div className="col-span-full py-20 flex flex-col items-center justify-center text-center bg-slate-50 dark:bg-slate-800/50 rounded-[3rem] border-2 border-dashed border-slate-200 dark:border-slate-800">
            <div className="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300 mb-6"><LogOut size={40} /></div>
            <h3 className="text-xl font-black text-slate-900 dark:text-white mb-2">Người dùng hiện không trực tuyến</h3>
            <p className="text-slate-500 max-w-xs font-medium italic">Không tìm thấy phiên đăng nhập nào của "{userName}".</p>
          </div>
        )}
      </div>
    </div>
  );
}
