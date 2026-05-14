import React, { useState, useRef } from 'react';
import axios from 'axios';

interface ThemeSettingsProps {
    settings: Record<string, any>;
    asset_url: string;
}

const ThemeSettings: React.FC<ThemeSettingsProps> = ({ settings: initialSettings, asset_url }) => {
    const [settings, setSettings] = useState(initialSettings);
    const [isSaving, setIsSaving] = useState(false);
    const [previewScale, setPreviewScale] = useState(0.65);
    const [activeTab, setActiveTab] = useState('header');
    const [socialLinks, setSocialLinks] = useState<any[]>(() => {
        try { return JSON.parse(initialSettings.social_links || '[]'); } catch (e) { return []; }
    });

    const fileInputRefs = {
        logo: useRef<HTMLInputElement>(null),
        banner_1: useRef<HTMLInputElement>(null)
    };

    const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
        const { name, value } = e.target;
        setSettings(prev => ({ ...prev, [name]: value }));
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

    const handleSave = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSaving(true);
        const fd = new FormData();
        Object.keys(settings).forEach(k => { if (!k.endsWith('_preview')) fd.append(k, settings[k] || ''); });
        if (fileInputRefs.logo.current?.files?.[0]) fd.append('logo', fileInputRefs.logo.current.files[0]);
        if (fileInputRefs.banner_1.current?.files?.[0]) fd.append('banner_1', fileInputRefs.banner_1.current.files[0]);
        fd.append('social_links', JSON.stringify(socialLinks));

        try {
            const res = await axios.post('/admin/settings/theme', fd, { headers: { 'Content-Type': 'multipart/form-data', 'Accept': 'application/json' } });
            if (res.data.success) alert(res.data.message);
        } catch (error) { alert('Có lỗi xảy ra!'); } finally { setIsSaving(false); }
    };

    const getImageUrl = (key: string, defaultPath: string) => {
        if (settings[key + '_preview']) return settings[key + '_preview'] as string;
        if (settings[key]) return asset_url + settings[key];
        return asset_url + defaultPath;
    };

    return (
        <div className="flex flex-col xl:flex-row h-screen max-h-[920px] bg-slate-50 overflow-hidden rounded-[3rem] border border-slate-200 shadow-2xl">
            {/* Sidebar Controls */}
            <div className="w-full xl:w-[500px] bg-white flex h-full border-r border-slate-100 z-20 shadow-2xl overflow-hidden">
                {/* Left Mini-Nav */}
                <div className="w-24 bg-slate-50 border-r border-slate-100 flex flex-col items-center py-10 gap-8">
                    <div className="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-100 text-white text-xl font-black italic">J</div>
                    
                    <div className="flex flex-col gap-4">
                        {[
                            { id: 'header', icon: 'M4 6h16M4 12h16m-7 6h7' },
                            { id: 'brand', icon: 'M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5' },
                            { id: 'visuals', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
                            { id: 'footer', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' }
                        ].map(t => (
                            <button 
                                key={t.id} 
                                onClick={() => setActiveTab(t.id)} 
                                className={`w-14 h-14 rounded-2xl flex items-center justify-center transition-all ${activeTab === t.id ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-slate-400 hover:bg-white hover:text-indigo-600'}`}
                            >
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d={t.icon} />
                                </svg>
                            </button>
                        ))}
                    </div>
                </div>

                {/* Right Form Area */}
                <div className="flex-1 flex flex-col h-full overflow-hidden bg-white">
                    <div className="p-8 border-b border-slate-50 flex items-center justify-between">
                        <div>
                            <h2 className="text-xl font-black text-slate-800 uppercase tracking-tighter">{activeTab}</h2>
                            <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Cấu hình chi tiết</p>
                        </div>
                        <button onClick={handleSave} disabled={isSaving} className="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-indigo-100 transition-all active:scale-95">
                            {isSaving ? '...' : 'Lưu'}
                        </button>
                    </div>

                    <div className="flex-1 overflow-y-auto p-8 space-y-10 custom-scrollbar">
                        <div className="space-y-8">
                            {activeTab === 'header' && (
                                <>
                                    <InputField label="Thông báo chạy 1" name="topbar_text_1" value={settings.topbar_text_1} onChange={handleInputChange} />
                                    <InputField label="Thông báo chạy 2" name="topbar_text_2" value={settings.topbar_text_2} onChange={handleInputChange} />
                                    <InputField label="Link cửa hàng" name="topbar_text_4" value={settings.topbar_text_4} onChange={handleInputChange} />
                                </>
                            )}
                            {activeTab === 'brand' && (
                                <>
                                    <div className="grid grid-cols-2 gap-4">
                                        <InputField label="Site Name" name="site_name" value={settings.site_name} onChange={handleInputChange} />
                                        <InputField label="Suffix" name="site_suffix" value={settings.site_suffix} onChange={handleInputChange} />
                                    </div>
                                    <InputField label="Số Hotline Hệ thống" name="hotline" value={settings.hotline} onChange={handleInputChange} />
                                </>
                            )}
                            {activeTab === 'visuals' && (
                                <div className="space-y-8">
                                    <div className="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 flex items-center gap-6 shadow-inner">
                                        <div className="w-24 h-24 bg-white rounded-2xl border border-slate-200 flex items-center justify-center p-4 relative overflow-hidden group/img shadow-sm">
                                            <img src={getImageUrl('logo', 'images/logo.png')} className="max-h-full object-contain" />
                                            <label className="absolute inset-0 bg-indigo-900/40 opacity-0 group-hover/img:opacity-100 transition-opacity flex items-center justify-center cursor-pointer text-white font-bold text-[10px]">
                                                ĐỔI LOGO
                                                <input type="file" className="hidden" ref={fileInputRefs.logo} onChange={() => handleFileChange('logo')} />
                                            </label>
                                        </div>
                                        <div className="flex-1 space-y-2">
                                            <h5 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Logo Chính</h5>
                                            <button onClick={() => fileInputRefs.logo.current?.click()} className="px-4 py-2 bg-white border border-slate-100 rounded-lg font-black text-[10px] uppercase shadow-sm hover:bg-slate-50 transition-all">Chọn File</button>
                                        </div>
                                    </div>
                                    <div className="bg-slate-50 p-6 rounded-[2rem] border border-slate-100 space-y-4 shadow-inner">
                                        <h5 className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Banner Home</h5>
                                        <div className="aspect-[21/9] bg-white rounded-2xl overflow-hidden shadow-sm border border-slate-100"><img src={getImageUrl('banner_1', 'images/banner1.jpg')} className="w-full h-full object-cover" /></div>
                                        <button onClick={() => fileInputRefs.banner_1.current?.click()} className="w-full py-3 bg-white border border-slate-100 rounded-xl font-black text-[10px] uppercase tracking-widest shadow-sm hover:bg-slate-50 transition-all">Tải banner mới</button>
                                        <input type="file" className="hidden" ref={fileInputRefs.banner_1} onChange={() => handleFileChange('banner_1')} />
                                    </div>
                                </div>
                            )}
                            {activeTab === 'footer' && (
                                <div className="space-y-8">
                                    <div className="grid grid-cols-2 gap-4">
                                        <InputField label="Gọi mua" name="footer_hotline_buy" value={settings.footer_hotline_buy} onChange={handleInputChange} />
                                        <InputField label="Kỹ thuật" name="footer_hotline_tech" value={settings.footer_hotline_tech} onChange={handleInputChange} />
                                    </div>
                                    <InputField label="Email Liên hệ" name="email" value={settings.email} onChange={handleInputChange} />
                                    <InputField label="Địa chỉ Trụ sở" name="address" value={settings.address} onChange={handleInputChange} />
                                    <div className="pt-6 border-t border-slate-100">
                                        <h5 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Mạng xã hội</h5>
                                        <div className="flex gap-2 mb-6">
                                            {['fb', 'yt', 'ig', 'tw'].map(s => (
                                                <button key={s} onClick={() => setSocialLinks(prev => [...prev, { type: s, url: '' }])} className="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center font-black text-[10px] uppercase hover:bg-indigo-600 hover:text-white transition-all shadow-sm">{s}</button>
                                            ))}
                                        </div>
                                        <div className="space-y-3">
                                            {socialLinks.map((link, idx) => (
                                                <div key={idx} className="flex items-center gap-3 group">
                                                    <div className="flex-1 bg-white p-4 rounded-2xl border border-slate-200 flex items-center gap-4 shadow-sm focus-within:ring-4 focus-within:ring-indigo-500/5 transition-all">
                                                        <span className="text-[10px] font-black uppercase text-indigo-600 w-8">{link.type}</span>
                                                        <input value={link.url} onChange={(e) => {
                                                            const next = [...socialLinks];
                                                            next[idx].url = e.target.value;
                                                            setSocialLinks(next);
                                                        }} placeholder="https://..." className="flex-1 bg-transparent border-none p-0 text-[10px] font-bold outline-none" />
                                                    </div>
                                                    <button onClick={() => setSocialLinks(p => p.filter((_, i) => i !== idx))} className="w-10 h-10 flex items-center justify-center text-rose-400 hover:bg-rose-50 rounded-xl transition-all font-black">×</button>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>

            {/* Live Preview Panel */}
            <div className="flex-1 bg-slate-100 p-12 flex flex-col h-full relative overflow-hidden">
                <div className="mb-10 flex justify-between items-center">
                    <div className="flex bg-white/90 p-1.5 rounded-2xl shadow-xl border border-white">
                        <button onClick={() => setPreviewScale(0.65)} className={`px-4 py-2 rounded-xl transition-all font-black text-xs ${previewScale === 0.65 ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400'}`}>DESKTOP</button>
                        <button onClick={() => setPreviewScale(0.35)} className={`px-4 py-2 rounded-xl transition-all font-black text-xs ${previewScale === 0.35 ? 'bg-indigo-600 text-white shadow-lg' : 'text-slate-400'}`}>MOBILE</button>
                    </div>
                    <div className="px-8 py-3 bg-white rounded-full text-[10px] font-black text-indigo-600 uppercase tracking-widest shadow-xl flex items-center gap-3">Xem trước</div>
                </div>

                <div className="flex-1 relative overflow-hidden rounded-[4rem] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.15)] border-[15px] border-white bg-white flex justify-center">
                    <div className={`overflow-y-auto origin-top transition-all duration-500 custom-scrollbar ${previewScale < 0.5 ? 'w-[430px]' : 'w-full'}`} style={{ transform: `scale(${previewScale})`, minWidth: previewScale < 0.5 ? '430px' : '100%' }}>
                        <div className="mockup-website font-sans pb-40 bg-white">
                            {/* Topbar */}
                            <div className="bg-[#003380] text-white py-5 px-16 text-[16px] font-bold flex justify-between items-center">
                                <div className="flex gap-12">
                                    <span>{settings.topbar_text_1 || 'Hàng chính hãng 100%'}</span>
                                    <span>{settings.topbar_text_2 || 'Hỗ trợ 24/7'}</span>
                                </div>
                                <div className="flex gap-10 items-center">
                                    <span className="opacity-70 text-sm">{settings.topbar_text_4 || 'Hệ thống Showroom'}</span>
                                    <span className="text-yellow-400 font-black text-2xl tracking-tight leading-none">{settings.hotline || '1800 1234'}</span>
                                </div>
                            </div>

                            {/* Header */}
                            <header className="bg-[#0046ab] py-10 px-16 text-white flex items-center gap-16 sticky top-0 z-50 shadow-2xl">
                                <div className="flex-shrink-0">
                                    {settings.logo || settings.logo_preview ? (
                                        <img src={getImageUrl('logo', 'images/logo.png')} className="h-16 object-contain" />
                                    ) : (
                                        <div className="text-5xl font-black italic tracking-tighter leading-none uppercase">
                                            {settings.site_name || 'DIENMAY'}<span className="text-cyan-400">{settings.site_suffix || 'PRO'}</span>
                                        </div>
                                    )}
                                </div>
                                <div className="flex-1 bg-white/10 rounded-3xl h-20 border border-white/20 shadow-inner flex items-center px-10 text-white/30 text-2xl font-black italic">Tìm sản phẩm tại đây...</div>
                                <div className="flex gap-12 text-4xl">🛒 👤</div>
                            </header>

                            {/* Hero */}
                            <div className="max-w-[1500px] mx-auto px-16 py-16">
                                <div className="grid grid-cols-4 gap-12">
                                    <div className="bg-white rounded-[3rem] border border-slate-100 shadow-2xl overflow-hidden p-4 h-fit space-y-2">
                                        {['Tivi & Loa', 'Điện lạnh', 'Gia dụng', 'Điện thoại', 'Laptop'].map(item => (
                                            <div key={item} className="px-10 py-7 hover:bg-slate-50 font-black text-slate-600 text-2xl flex justify-between items-center transition-all rounded-[2rem] cursor-pointer">
                                                {item} <span>→</span>
                                            </div>
                                        ))}
                                    </div>
                                    <div className="col-span-3 rounded-[4rem] overflow-hidden shadow-2xl relative group border-[12px] border-white aspect-[21/9]">
                                        <img src={getImageUrl('banner_1', 'images/banner1.jpg')} className="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" />
                                        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex flex-col justify-end p-24 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                            <h2 className="text-7xl font-black mb-6 leading-tight uppercase italic">Summer<br /><span className="text-yellow-400">Big Sale</span></h2>
                                            <p className="text-3xl font-bold text-white/80 max-w-2xl">Áp dụng cho toàn bộ thiết bị Joly trong tháng này.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Footer */}
                            <footer className="bg-slate-900 text-white pt-40 pb-20 mt-40 shadow-2xl">
                                <div className="max-w-[1500px] mx-auto px-16 grid grid-cols-4 gap-24">
                                    <div className="space-y-12 col-span-2">
                                        <div className="text-6xl font-black italic uppercase tracking-tighter text-indigo-400">{settings.site_name || 'JOLY'}</div>
                                        <div className="space-y-8 text-white/50 font-bold text-2xl leading-relaxed max-w-xl">
                                            <div className="flex gap-4">📍 {settings.address || 'Hà Nội, Việt Nam'}</div>
                                            <div className="flex gap-10">
                                                <div>📞 Mua hàng: {settings.footer_hotline_buy || settings.hotline}</div>
                                                <div>🛠️ Kỹ thuật: {settings.footer_hotline_tech || settings.hotline}</div>
                                            </div>
                                            <div>✉️ {settings.email || 'contact@joly.vn'}</div>
                                        </div>
                                    </div>
                                    <div className="space-y-10">
                                        <h4 className="text-3xl font-black mb-10 text-indigo-400 uppercase tracking-widest">Dịch Vụ</h4>
                                        <div className="space-y-6 text-white/60 font-bold text-2xl"><div>Trung tâm bảo hành</div><div>Chính sách đổi trả</div><div>Trả góp Online</div></div>
                                    </div>
                                    <div className="space-y-12">
                                        <h4 className="text-3xl font-black mb-10 text-indigo-400 uppercase tracking-widest">Kết Nối</h4>
                                        <div className="flex gap-6">
                                            {socialLinks.length > 0 ? socialLinks.map((s, i) => (
                                                <div key={i} className="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center hover:bg-indigo-600 transition-all border border-white/10 text-3xl font-black uppercase">{s.type}</div>
                                            )) : (
                                                <>
                                                    <div className="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center border border-white/10 text-4xl">f</div>
                                                    <div className="w-20 h-20 rounded-3xl bg-white/5 flex items-center justify-center border border-white/10 text-4xl">y</div>
                                                </>
                                            )}
                                        </div>
                                        <div className="text-xs font-black text-white/20 uppercase tracking-[0.3em] leading-relaxed pt-10 border-t border-white/5">© 2026 {settings.site_name || 'CASSAVAS'} - DESIGNED BY CASSAVAS</div>
                                    </div>
                                </div>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

const InputField = ({ label, name, value, onChange }: any) => (
    <div className="space-y-3">
        <label className="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] ml-2">{label}</label>
        <input name={name} value={value || ''} onChange={onChange} className="w-full px-7 py-5 bg-white border border-slate-200 rounded-[1.5rem] focus:ring-[12px] focus:ring-indigo-500/5 transition-all text-lg font-black text-slate-800 outline-none shadow-sm hover:border-slate-300" />
    </div>
);

export default ThemeSettings;
