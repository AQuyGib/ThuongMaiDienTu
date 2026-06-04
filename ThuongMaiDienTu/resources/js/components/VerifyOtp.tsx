import React, { useState, useEffect, useRef } from 'react';
import { ArrowLeft, ShieldCheck, MailOpen, AlertCircle, CheckCircle2, Loader2 } from 'lucide-react';
import { Button } from './ui/button';

interface VerifyOtpProps {
    email: string;
    actionUrl: string;
    csrfToken: string;
    errorMsg?: string;
    successMsg?: string;
    resendUrl: string;
    resendMethod?: 'GET' | 'POST';
    type?: 'forgot_password' | '2fa';
}

const VerifyOtp: React.FC<VerifyOtpProps> = ({ email, actionUrl, csrfToken, errorMsg, successMsg, resendUrl, resendMethod = 'GET', type }) => {
    const is2Fa = type === '2fa' || actionUrl.includes('2fa') || actionUrl.includes('two_factor') || actionUrl.includes('2fa/verify');
    const [otp, setOtp] = useState(['', '', '', '', '', '']);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [cooldown, setCooldown] = useState(60);
    const inputRefs = useRef<(HTMLInputElement | null)[]>([]);

    // Auto submit when all 6 digits are entered
    useEffect(() => {
        if (otp.join('').length === 6) {
            handleSubmit();
        }
    }, [otp]);

    // Anti-spam cooldown timer
    useEffect(() => {
        if (cooldown > 0) {
            const timer = setInterval(() => setCooldown(c => c - 1), 1000);
            return () => clearInterval(timer);
        }
    }, [cooldown]);

    const handleOtpChange = (index: number, value: string) => {
        // Strip non-digits
        const digits = value.replace(/\D/g, '');
        if (digits.length === 0 && value !== '') return; // Ignore if user typed a letter

        const newOtp = [...otp];

        // Handle Paste or AutoFill
        if (digits.length > 1) {
            if (digits.length === 6) {
                // Full 6-digit autofill or paste
                const pasted = digits.split('');
                for (let i = 0; i < 6; i++) {
                    newOtp[i] = pasted[i];
                }
                setOtp(newOtp);
                inputRefs.current[5]?.focus();
            } else {
                // User typed a new digit over an existing digit (e.g. "34")
                const lastChar = digits.slice(-1);
                newOtp[index] = lastChar;
                setOtp(newOtp);
                if (index < 5) {
                    inputRefs.current[index + 1]?.focus();
                }
            }
            return;
        }

        // Handle single digit input (or clearing)
        newOtp[index] = digits;
        setOtp(newOtp);

        // Auto focus next input if digit entered
        if (digits !== '' && index < 5) {
            inputRefs.current[index + 1]?.focus();
        }
    };

    const handleKeyDown = (index: number, e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Backspace' && otp[index] === '' && index > 0) {
            inputRefs.current[index - 1]?.focus();
        }
    };

    const handleSubmit = (e?: React.FormEvent) => {
        if (e) e.preventDefault();
        const otpValue = otp.join('');
        if (otpValue.length !== 6) return;

        setIsSubmitting(true);
        // Form submission happens via a hidden form to keep it simple and compatible with Laravel's standard redirect logic
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = actionUrl;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        const emailInput = document.createElement('input');
        emailInput.type = 'hidden';
        emailInput.name = 'email';
        emailInput.value = email;
        form.appendChild(emailInput);

        const otpInputHidden = document.createElement('input');
        otpInputHidden.type = 'hidden';
        otpInputHidden.name = 'otp';
        otpInputHidden.value = otpValue;
        form.appendChild(otpInputHidden);

        document.body.appendChild(form);
        form.submit();
    };

    const handleResend = (e: React.MouseEvent) => {
        e.preventDefault();
        if (cooldown > 0) return;

        if (resendMethod === 'POST') {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = resendUrl;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        } else {
            window.location.href = resendUrl;
        }
    };

    return (
        <div className="min-h-screen w-full font-sans flex items-center justify-center p-6 relative overflow-hidden bg-transparent">
            {/* Background Ambient Glows */}
            <div className="absolute top-0 left-1/4 w-[600px] h-[600px] bg-blue-600/20 rounded-full blur-[120px] mix-blend-screen pointer-events-none" />
            <div className="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-red-600/20 rounded-full blur-[100px] mix-blend-screen pointer-events-none" />

            <div className="w-full max-w-5xl bg-white/90 dark:bg-slate-900/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-[2.5rem] shadow-[0_20px_60px_-15px_rgba(0,0,0,0.5)] overflow-hidden flex flex-col md:flex-row relative z-10">

                {/* Left Panel - Visual */}
                <div className="w-full md:w-5/12 bg-gradient-to-br from-slate-900/90 to-slate-950/90 backdrop-blur-xl p-10 flex flex-col relative overflow-hidden hidden md:flex border-r border-white/10">
                    <div className="absolute top-0 right-0 w-64 h-64 bg-blue-500/20 rounded-full blur-[60px]" />
                    <div className="absolute bottom-0 left-0 w-48 h-48 bg-red-500/20 rounded-full blur-[40px]" />

                    <a href={is2Fa ? "/login" : "/forgot-password"} className="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm font-bold w-fit z-10">
                        <ArrowLeft className="w-4 h-4" /> Quay lại
                    </a>

                    <div className="flex-1 flex flex-col justify-center relative z-10 mt-12">
                        <div className="w-20 h-20 bg-white/10 border border-white/20 rounded-2xl flex items-center justify-center mb-8 backdrop-blur-md shadow-lg">
                            {is2Fa ? (
                                <ShieldCheck className="w-10 h-10 text-blue-400" />
                            ) : (
                                <MailOpen className="w-10 h-10 text-blue-400" />
                            )}
                        </div>
                        <h2 className="text-3xl font-black text-white mb-4 leading-tight tracking-tight drop-shadow-md">
                            {is2Fa ? (
                                <>Xác thực<br />Bảo mật Hai Lớp</>
                            ) : (
                                <>Khôi phục<br />Mật khẩu tài khoản</>
                            )}
                        </h2>
                        <p className="text-slate-300 text-sm leading-relaxed max-w-sm">
                            {is2Fa ? (
                                "Để bảo vệ tài khoản của bạn khỏi truy cập trái phép, chúng tôi yêu cầu mã xác thực một lần (OTP) mỗi khi bạn thực hiện các thao tác quan trọng."
                            ) : (
                                "Vui lòng nhập mã xác minh (OTP) đã được gửi tới hòm thư của bạn để xác thực và tiến hành đặt lại mật khẩu mới."
                            )}
                        </p>
                    </div>

                    <div className="relative z-10 mt-auto">
                        <p className="text-xs font-bold text-slate-500 tracking-widest uppercase">DIENMAYPRO SECURE &copy; 2026</p>
                    </div>
                </div>

                {/* Right Panel - Form */}
                <div className="w-full md:w-7/12 bg-white/50 dark:bg-slate-900/50 p-8 md:p-14 flex flex-col justify-center relative backdrop-blur-sm">
                    <div className="max-w-sm mx-auto w-full">
                        <div className="mb-10 text-center md:text-left">
                            <div className="w-16 h-16 bg-slate-50 border border-slate-100 rounded-full flex items-center justify-center md:hidden mx-auto mb-6">
                                <ShieldCheck className="w-8 h-8 text-blue-500" />
                            </div>
                            <h1 className="text-2xl md:text-3xl font-black text-slate-900 mb-3 tracking-tight">Nhập mã xác minh</h1>
                            <p className="text-sm text-slate-500 font-medium">
                                Mã 6 chữ số đã được gửi đến email <br />
                                <strong className="text-slate-900 bg-slate-100 px-2 py-0.5 rounded ml-1">{email}</strong>
                            </p>
                        </div>

                        {errorMsg && (
                            <div className="mb-6 p-4 bg-red-50 border border-red-100 rounded-xl flex items-start gap-3">
                                <AlertCircle className="w-5 h-5 text-red-500 shrink-0 mt-0.5" />
                                <p className="text-sm font-bold text-red-700">{errorMsg}</p>
                            </div>
                        )}

                        {successMsg && (
                            <div className="mb-6 p-4 bg-green-50 border border-green-100 rounded-xl flex items-start gap-3">
                                <CheckCircle2 className="w-5 h-5 text-green-500 shrink-0 mt-0.5" />
                                <p className="text-sm font-bold text-green-700">{successMsg}</p>
                            </div>
                        )}

                        <form onSubmit={handleSubmit} className="space-y-8">
                            <div className="flex justify-between gap-2 md:gap-3">
                                {[0, 1, 2, 3, 4, 5].map((index) => (
                                    <input
                                        key={index}
                                        ref={(el) => (inputRefs.current[index] = el)}
                                        type="text"
                                        maxLength={6}
                                        value={otp[index]}
                                        onChange={(e) => handleOtpChange(index, e.target.value)}
                                        onKeyDown={(e) => handleKeyDown(index, e)}
                                        className="w-12 h-14 md:w-14 md:h-16 text-center text-2xl font-black text-slate-900 bg-slate-50 border-2 border-slate-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none"
                                        autoFocus={index === 0}
                                        inputMode="numeric"
                                        pattern="\d*"
                                    />
                                ))}
                            </div>

                            <Button
                                type="submit"
                                className="w-full h-14 rounded-xl font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-xl shadow-slate-900/20"
                                disabled={otp.join('').length !== 6 || isSubmitting}
                            >
                                {isSubmitting ? <Loader2 className="w-5 h-5 animate-spin" /> : 'Xác Minh Mã OTP'}
                            </Button>
                        </form>

                        <div className="mt-10 pt-8 border-t border-slate-100 text-center">
                            {cooldown > 0 ? (
                                <p className="text-sm font-medium text-slate-500">
                                    Không nhận được mã? Gửi lại sau <strong className="text-blue-600">{cooldown}s</strong>
                                </p>
                            ) : (
                                <p className="text-sm font-medium text-slate-500">
                                    Chưa nhận được mã?{' '}
                                    <button onClick={handleResend} className="font-bold text-blue-600 hover:text-blue-700 hover:underline transition-all bg-transparent border-none cursor-pointer">
                                        Gửi lại ngay
                                    </button>
                                </p>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default VerifyOtp;
