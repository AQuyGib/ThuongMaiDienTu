import React, { useState, useEffect } from 'react';
import axios from 'axios';
import {
    Shield,
    ShieldCheck,
    ShieldAlert,
    Smartphone,
    Monitor,
    KeyRound,
    Mail,
    LogOut,
    CircleCheck,
    CircleX,
    TriangleAlert,
    ArrowLeft,
    Loader2
} from 'lucide-react';
import { Button } from './ui/button';

interface Session {
    id: string;
    ip_address: string;
    is_current_device: boolean;
    device: string;
    platform: string;
    browser: string;
    last_active: string;
}

interface Detail {
    status: 'pass' | 'fail' | 'warning';
    label: string;
}

interface SecurityProps {
    user: {
        id: number;
        full_name: string;
        email: string;
        phone_number: string | null;
        is_2fa_enabled: boolean;
    };
    sessions: Session[];
    score: number;
    details: Record<string, Detail>;
    securityTier: string;
    tierColor: string;
}

const SecuritySettings: React.FC<SecurityProps> = ({ user, sessions: initialSessions, score, details, securityTier, tierColor }) => {
    const [is2faEnabled, setIs2faEnabled] = useState(user.is_2fa_enabled);
    const [isLoading2fa, setIsLoading2fa] = useState(false);
    const [toastMsg, setToastMsg] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

    const [showOtpModal, setShowOtpModal] = useState(false);
    const [otp, setOtp] = useState('');
    const [isConfirming, setIsConfirming] = useState(false);
    const [resendCooldown, setResendCooldown] = useState(0);
    const [expireCountdown, setExpireCountdown] = useState(0);

    useEffect(() => {
        let resendTimer: ReturnType<typeof setInterval>;
        let expireTimer: ReturnType<typeof setInterval>;

        if (showOtpModal) {
            if (resendCooldown > 0) {
                resendTimer = setInterval(() => setResendCooldown(prev => prev - 1), 1000);
            }
            if (expireCountdown > 0) {
                expireTimer = setInterval(() => setExpireCountdown(prev => prev - 1), 1000);
            }
        }

        return () => {
            clearInterval(resendTimer);
            clearInterval(expireTimer);
        };
    }, [showOtpModal, resendCooldown, expireCountdown]);

    const handleToggleRequest = async (isResend = false) => {
        if (!isResend) setIsLoading2fa(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await axios.post('/2fa/toggle-request', {
                is_2fa_enabled: !is2faEnabled
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });

            if (res.data.success) {
                showToast(res.data.message, 'success');
                if (!isResend) {
                    setShowOtpModal(true);
                    setOtp('');
                }
                setResendCooldown(60);
                setExpireCountdown(300);
            }
        } catch (error: any) {
            console.error("Lỗi khi yêu cầu 2FA:", error);
            showToast(error.response?.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.', 'error');
        } finally {
            if (!isResend) setIsLoading2fa(false);
        }
    };

    const handleToggleConfirm = async () => {
        if (otp.length !== 6) {
            showToast('Vui lòng nhập đủ 6 số OTP', 'error');
            return;
        }

        setIsConfirming(true);
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await axios.post('/2fa/toggle-confirm', {
                is_2fa_enabled: !is2faEnabled,
                otp: otp
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });

            if (res.data.success) {
                setIs2faEnabled(res.data.is_enabled);
                setShowOtpModal(false);
                showToast(res.data.message, 'success');
                setTimeout(() => window.location.reload(), 1500);
            }
        } catch (error: any) {
            console.error("Lỗi xác nhận 2FA:", error);
            showToast(error.response?.data?.message || 'Mã OTP không hợp lệ.', 'error');
        } finally {
            setIsConfirming(false);
        }
    };

    const handleLogoutSession = async (sessionId: string) => {
        if (!confirm('Bạn có chắc chắn muốn đăng xuất khỏi thiết bị này?')) return;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await axios.post(`/security/session/${sessionId}`, {
                _method: 'DELETE'
            }, {
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });

            showToast('Đã đăng xuất thiết bị thành công.', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } catch (error) {
            console.error("Lỗi đăng xuất:", error);
            showToast('Có lỗi xảy ra, vui lòng thử lại.', 'error');
        }
    };

    const showToast = (message: string, type: 'success' | 'error') => {
        setToastMsg({ message, type });
        setTimeout(() => setToastMsg(null), 3000);
    };

    return (
        <div className="min-h-screen bg-slate-50 dark:bg-slate-950 font-sans selection:bg-indigo-500/30">
            {/* Background Effects */}
            <div className="fixed inset-0 overflow-hidden pointer-events-none">
                <div className="absolute top-0 left-1/4 w-[500px] h-[500px] bg-indigo-500/10 rounded-full blur-[120px] mix-blend-multiply" />
                <div className="absolute bottom-0 right-1/4 w-[600px] h-[600px] bg-purple-500/10 rounded-full blur-[150px] mix-blend-multiply" />
            </div>

            {/* Toast Notification */}
            {toastMsg && (
                <div className={`fixed top-6 right-6 z-[200] flex items-center gap-3 px-5 py-4 rounded-2xl shadow-2xl border bg-white dark:bg-slate-900 animate-in slide-in-from-right duration-300 ${toastMsg.type === 'success' ? 'border-green-100 dark:border-green-900/30' : 'border-rose-100 dark:border-rose-900/30'}`}>
                    {toastMsg.type === 'success' ? <CircleCheck className="w-5 h-5 text-green-500" /> : <CircleX className="w-5 h-5 text-rose-500" />}
                    <span className="font-bold text-sm text-slate-800 dark:text-white">{toastMsg.message}</span>
                </div>
            )}

            {/* OTP Modal */}
            {showOtpModal && (
                <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm animate-in fade-in duration-200">
                    <div className="bg-white dark:bg-slate-900 rounded-3xl p-8 max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-800 animate-in zoom-in-95 duration-300">
                        <div className="w-16 h-16 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-6">
                            <ShieldCheck className="w-8 h-8" />
                        </div>
                        <h2 className="text-2xl font-black text-center text-slate-900 dark:text-white mb-2">Xác nhận thao tác</h2>
                        <p className="text-sm text-center text-slate-500 dark:text-slate-400 mb-8 px-4">
                            Vui lòng nhập mã OTP gồm 6 chữ số vừa được gửi đến email <strong>{user.email}</strong> để {is2faEnabled ? 'tắt' : 'bật'} 2FA.
                        </p>

                        <div className="mb-6">
                            <input
                                type="text"
                                maxLength={6}
                                value={otp}
                                onChange={(e) => setOtp(e.target.value.replace(/[^0-9]/g, ''))}
                                placeholder="000000"
                                className="w-full text-center text-4xl font-black tracking-[0.5em] text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl py-4 focus:outline-none focus:border-indigo-500 transition-colors placeholder:text-slate-300"
                                autoFocus
                            />
                        </div>

                        <div className="flex flex-col items-center gap-2 mb-8">
                            {expireCountdown > 0 ? (
                                <p className="text-xs font-bold text-slate-500 dark:text-slate-400">
                                    Mã hết hạn sau: <span className="text-rose-500">{Math.floor(expireCountdown / 60).toString().padStart(2, '0')}:{(expireCountdown % 60).toString().padStart(2, '0')}</span>
                                </p>
                            ) : (
                                <p className="text-xs font-bold text-rose-500">Mã OTP đã hết hạn</p>
                            )}

                            <button
                                onClick={() => handleToggleRequest(true)}
                                disabled={resendCooldown > 0}
                                className={`text-xs font-bold transition-colors ${resendCooldown > 0 ? 'text-slate-400 cursor-not-allowed' : 'text-indigo-600 hover:text-indigo-700 hover:underline'}`}
                            >
                                {resendCooldown > 0 ? `Gửi lại mã sau ${resendCooldown}s` : 'Chưa nhận được mã? Gửi lại ngay'}
                            </button>
                        </div>

                        <div className="flex gap-3">
                            <Button
                                variant="outline"
                                className="flex-1 h-12 rounded-xl font-bold border-slate-200"
                                onClick={() => setShowOtpModal(false)}
                                disabled={isConfirming}
                            >
                                Hủy
                            </Button>
                            <Button
                                className="flex-1 h-12 rounded-xl font-bold bg-indigo-600 hover:bg-indigo-700 text-white"
                                onClick={handleToggleConfirm}
                                disabled={isConfirming || otp.length !== 6}
                            >
                                {isConfirming ? <Loader2 className="w-5 h-5 animate-spin mx-auto" /> : 'Xác nhận'}
                            </Button>
                        </div>
                    </div>
                </div>
            )}

            <div className="max-w-6xl mx-auto px-6 py-12 relative z-10">
                {/* Header */}
                <div className="flex items-center gap-4 mb-10">
                    <a href="/profile" className="w-12 h-12 flex items-center justify-center rounded-2xl bg-white dark:bg-slate-900 shadow-sm border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-indigo-600 transition-colors">
                        <ArrowLeft className="w-5 h-5" />
                    </a>
                    <div>
                        <h1 className="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Trung tâm Bảo mật</h1>
                        <p className="text-sm font-bold text-slate-500 mt-1 uppercase tracking-widest">Bảo vệ tài khoản DienMayPro của bạn</p>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {/* Left Column: Security Status */}
                    <div className="space-y-8">
                        {/* Security Score Card */}
                        <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-800 relative overflow-hidden">
                            <div className="absolute top-0 right-0 w-40 h-40 bg-indigo-500/10 rounded-full blur-[40px] -mr-10 -mt-10" />

                            <div className="flex items-center justify-between mb-8 relative z-10">
                                <h3 className="text-base font-black text-slate-800 dark:text-white uppercase tracking-wider">Điểm bảo mật</h3>
                                {score >= 90 ? <ShieldCheck className="w-8 h-8 text-green-500" /> : <ShieldAlert className="w-8 h-8 text-amber-500" />}
                            </div>

                            <div className="flex items-end gap-2 mb-2 relative z-10">
                                <span className="text-6xl font-black tracking-tighter" style={{ color: tierColor }}>{score}</span>
                                <span className="text-xl font-bold text-slate-400 mb-2">/100</span>
                            </div>
                            <p className="text-sm font-black uppercase tracking-widest mb-8" style={{ color: tierColor }}>{securityTier}</p>

                            <div className="space-y-4 relative z-10 bg-slate-50 dark:bg-slate-800/50 p-5 rounded-3xl border border-slate-100 dark:border-slate-700">
                                <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Danh mục kiểm tra:</p>
                                {(Object.entries(details) as [string, Detail][]).map(([key, detail]) => (
                                    <div key={key} className="flex items-start gap-3">
                                        {detail.status === 'pass' && <CircleCheck className="w-4 h-4 text-green-500 mt-0.5 shrink-0" />}
                                        {detail.status === 'fail' && <CircleX className="w-4 h-4 text-rose-500 mt-0.5 shrink-0" />}
                                        {detail.status === 'warning' && <TriangleAlert className="w-4 h-4 text-amber-500 mt-0.5 shrink-0" />}
                                        <span className={`text-xs font-bold leading-tight ${detail.status === 'pass' ? 'text-slate-600 dark:text-slate-300' : 'text-slate-800 dark:text-white'}`}>
                                            {detail.label}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Quick Info Card */}
                        <div className="bg-gradient-to-br from-indigo-600 to-indigo-800 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-indigo-500/30">
                            <h3 className="text-xs font-black uppercase tracking-widest opacity-80 mb-6">Thông tin liên kết</h3>

                            <div className="space-y-6">
                                <div className="flex items-center gap-4">
                                    <div className="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                        <Mail className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-black uppercase tracking-widest opacity-60">Email đăng nhập</p>
                                        <p className="text-sm font-bold truncate max-w-[150px]">{user.email}</p>
                                    </div>
                                </div>
                                <div className="flex items-center gap-4">
                                    <div className="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-md">
                                        <Smartphone className="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p className="text-[10px] font-black uppercase tracking-widest opacity-60">Số điện thoại</p>
                                        <p className="text-sm font-bold">{user.phone_number || 'Chưa cập nhật'}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Right Column: Main Settings */}
                    <div className="lg:col-span-2 space-y-8">
                        {/* 2FA Setting */}
                        <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 shadow-sm border border-slate-200 dark:border-slate-800">
                            <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div className="flex items-center gap-5">
                                    <div className={`w-16 h-16 rounded-[1.5rem] flex items-center justify-center shrink-0 transition-colors ${is2faEnabled ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-400 dark:bg-slate-800'}`}>
                                        <KeyRound className="w-7 h-7" />
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-black text-slate-800 dark:text-white mb-1">Xác thực 2 bước (2FA)</h3>
                                        <p className="text-sm font-medium text-slate-500 leading-relaxed max-w-md">
                                            Bảo vệ tài khoản bằng cách yêu cầu mã xác minh OTP gửi về Email ngoài mật khẩu khi đăng nhập.
                                        </p>
                                    </div>
                                </div>

                                <div className="shrink-0 flex flex-col items-center gap-2">
                                    <button
                                        onClick={() => handleToggleRequest(false)}
                                        disabled={isLoading2fa}
                                        className={`relative inline-flex h-10 w-20 items-center rounded-full transition-colors duration-300 focus:outline-none ${is2faEnabled ? 'bg-green-500 shadow-lg shadow-green-500/30' : 'bg-slate-300 dark:bg-slate-700'}`}
                                    >
                                        <span className={`inline-block h-8 w-8 transform rounded-full bg-white transition-transform duration-300 ${is2faEnabled ? 'translate-x-11' : 'translate-x-1'}`} />
                                    </button>
                                    <span className={`text-[10px] font-black uppercase tracking-widest ${is2faEnabled ? 'text-green-500' : 'text-slate-400'}`}>
                                        {isLoading2fa ? 'Đang xử lý...' : (is2faEnabled ? 'Đang hoạt động' : 'Đã vô hiệu')}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {/* Session Management */}
                        <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 shadow-sm border border-slate-200 dark:border-slate-800">
                            <div className="flex items-center gap-4 mb-8">
                                <div className="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 flex items-center justify-center">
                                    <Monitor className="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 className="text-lg font-black text-slate-800 dark:text-white">Thiết bị đang đăng nhập</h3>
                                    <p className="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Quản lý các phiên hoạt động</p>
                                </div>
                            </div>

                            <div className="space-y-4">
                                {initialSessions.map((session) => (
                                    <div key={session.id} className={`flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-[1.5rem] border transition-all ${session.is_current_device ? 'bg-indigo-50/50 border-indigo-100 dark:bg-indigo-900/10 dark:border-indigo-900/30' : 'bg-white border-slate-100 dark:bg-slate-800/30 dark:border-slate-800'}`}>
                                        <div className="flex items-center gap-4">
                                            <div className="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 flex items-center justify-center shrink-0">
                                                {session.device === 'iPhone' || session.device === 'Điện thoại Android' ? <Smartphone className="w-5 h-5" /> : <Monitor className="w-5 h-5" />}
                                            </div>
                                            <div>
                                                <div className="flex items-center gap-2 mb-1">
                                                    <h4 className="text-sm font-black text-slate-800 dark:text-white">{session.platform} ({session.browser})</h4>
                                                    {session.is_current_device && (
                                                        <span className="px-2 py-0.5 rounded-md bg-green-100 text-green-700 text-[9px] font-black uppercase tracking-wider">Thiết bị này</span>
                                                    )}
                                                </div>
                                                <p className="text-[11px] font-bold text-slate-400">IP: {session.ip_address} • {session.last_active}</p>
                                            </div>
                                        </div>

                                        {!session.is_current_device && (
                                            <Button
                                                variant="outline"
                                                onClick={() => handleLogoutSession(session.id)}
                                                className="rounded-xl h-10 px-4 text-rose-500 border-rose-100 hover:bg-rose-50 hover:border-rose-200 dark:border-rose-900/30 dark:hover:bg-rose-900/20 font-bold text-xs"
                                            >
                                                <LogOut className="w-3.5 h-3.5 mr-2" /> Đăng xuất
                                            </Button>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default SecuritySettings;
