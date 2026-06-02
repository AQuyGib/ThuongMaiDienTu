import React, { useEffect, useRef, useState } from 'react';
import axios from 'axios';
import {
    TrendingUp,
    ShoppingCart,
    Wrench,
    Trophy,
    Calendar,
    Users,
    ChevronDown,
    ArrowUpRight,
    Activity,
    RefreshCw,
    Maximize2,
    Minimize2,
    FileSpreadsheet,
    Loader2,
    Filter,
    Check,
    X
} from 'lucide-react';
import Chart from 'chart.js/auto';
import { Button } from './ui/button';
import { t } from '../helpers';

interface Stats {
    total_sales_revenue: number;
    total_orders_completed: number;
    total_repairs_done: number;
    top_sales: any;
    top_tech: any;
    filter: string;
    start_date: string;
    end_date: string;
}

interface KPIProps {
    stats: Stats;
    salesKPI: any[];
    techKPI: any[];
    revenueChart: any[];
}

const KPIDashboard: React.FC<KPIProps> = (initialProps) => {
    const [data, setData] = useState<KPIProps>({
        stats: initialProps.stats || {
            total_sales_revenue: 0,
            total_orders_completed: 0,
            total_repairs_done: 0,
            top_sales: null,
            top_tech: null,
            filter: 'month',
            start_date: new Date().toISOString(),
            end_date: new Date().toISOString(),
        },
        salesKPI: initialProps.salesKPI || [],
        techKPI: initialProps.techKPI || [],
        revenueChart: initialProps.revenueChart || []
    });

    const [isRefreshing, setIsRefreshing] = useState(false);
    const [isRevenueExpanded, setIsRevenueExpanded] = useState(false);
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const [showCustomDate, setShowCustomDate] = useState(false);
    const [customStart, setCustomStart] = useState('');
    const [customEnd, setCustomEnd] = useState('');
    const dropdownRef = useRef<HTMLDivElement>(null);

    const revenueChartRef = useRef<HTMLCanvasElement>(null);
    const distChartRef = useRef<HTMLCanvasElement>(null);
    const revChartInstance = useRef<Chart | null>(null);
    const distChartInstance = useRef<Chart | null>(null);

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setIsDropdownOpen(false);
                setShowCustomDate(false); // Reset custom view on close
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const fetchKPI = async (filter: string, start?: string, end?: string) => {
        setIsRefreshing(true);
        setIsDropdownOpen(false);
        setShowCustomDate(false);
        try {
            let url = `/admin/kpi?filter=${filter}`;
            if (filter === 'custom' && start && end) {
                url += `&start=${start}&end=${end}`;
            }
            const response = await axios.get(url, {
                headers: { 'Accept': 'application/json' }
            });
            setData(response.data);
            const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + `?filter=${filter}${start ? `&start=${start}&end=${end}` : ''}`;
            window.history.pushState({ path: newUrl }, '', newUrl);
        } catch (error) {
            console.error(t("Lỗi tải dữ liệu KPI:", "Error loading KPI data:"), error);
        } finally {
            setIsRefreshing(false);
        }
    };

    useEffect(() => {
        if (revChartInstance.current) revChartInstance.current.destroy();
        if (distChartInstance.current) distChartInstance.current.destroy();

        if (revenueChartRef.current && data.revenueChart.length > 0) {
            const ctx = revenueChartRef.current.getContext('2d');
            if (ctx) {
                const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(79, 70, 229, 0.15)');
                gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

                revChartInstance.current = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.revenueChart.map(d => {
                            const date = new Date(d.date);
                            return `${date.getDate()}/${date.getMonth() + 1}`;
                        }),
                        datasets: [{
                            label: t('Doanh thu', 'Revenue'),
                            data: data.revenueChart.map(d => d.total),
                            borderColor: '#4f46e5',
                            borderWidth: 3,
                            fill: true,
                            backgroundColor: gradient,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#4f46e5',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: { callback: (v: any) => v >= 1000000 ? (v / 1000000) + 'M' : v.toLocaleString() + 'đ' }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }
        }

        if (distChartRef.current && data.salesKPI.length > 0) {
            const ctx = distChartRef.current.getContext('2d');
            if (ctx) {
                distChartInstance.current = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: data.salesKPI.map(s => s.full_name),
                        datasets: [{
                            data: data.salesKPI.map(s => s.total_revenue),
                            backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'],
                            borderWidth: 4,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { usePointStyle: true, padding: 20, font: { size: 11, weight: 'bold' } }
                            }
                        }
                    }
                });
            }
        }

        return () => {
            if (revChartInstance.current) revChartInstance.current.destroy();
            if (distChartInstance.current) distChartInstance.current.destroy();
        };
    }, [data.revenueChart, data.salesKPI, isRevenueExpanded]);

    const formatMoney = (val: number) => new Intl.NumberFormat('vi-VN').format(val || 0);

    const formatDate = (dateStr: string) => {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString(t('vi-VN', 'en-US'), { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    const filterOptions = [
        { label: t('Hôm nay', 'Today'), value: 'today' },
        { label: t('Hôm qua', 'Yesterday'), value: 'yesterday' },
        { label: t('Tuần này', 'This Week'), value: 'week' },
        { label: t('Tháng này', 'This Month'), value: 'month' },
        { label: t('Tháng trước', 'Last Month'), value: 'last_month' },
        { label: t('Năm nay', 'This Year'), value: 'year' },
        { label: t('Tùy chỉnh...', 'Custom...'), value: 'custom' },
    ];

    const currentFilterLabel = filterOptions.find(o => o.value === data.stats.filter)?.label || t('Chọn kỳ', 'Select Period');

    // Get today's date in YYYY-MM-DD format for the 'max' attribute
    const todayStr = new Date().toISOString().split('T')[0];

    return (
        <div className="max-w-[1600px] mx-auto space-y-4 pb-16 animate-in fade-in duration-500 relative">
            {isRefreshing && (
                <div className="absolute inset-0 z-50 flex items-center justify-center bg-white/10 backdrop-blur-[1px] rounded-[2rem]">
                    <Loader2 className="w-10 h-10 text-indigo-600 animate-spin" />
                </div>
            )}

            {/* Compact Header with Premium Dropdown */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white px-6 py-4 rounded-[1.5rem] shadow-sm border border-slate-100">
                <div className="flex items-center gap-3">
                    <div className="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shadow-inner">
                        <Activity className="w-5 h-5" />
                    </div>
                    <div>
                        <h1 className="text-xl font-black text-slate-800 tracking-tight">{t('Thống kê KPI', 'KPI Statistics')}</h1>
                        {data.stats.start_date && data.stats.end_date && (
                            <div className="flex items-center gap-1.5 mt-0.5">
                                <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">{t('Kỳ thống kê:', 'Period:')}</span>
                                <span className="text-[11px] font-black text-indigo-600 tracking-tight">
                                    {formatDate(data.stats.start_date)} - {formatDate(data.stats.end_date)}
                                </span>
                            </div>
                        )}
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    {/* Premium Dropdown Menu */}
                    <div className="relative" ref={dropdownRef}>
                        <button
                            onClick={() => setIsDropdownOpen(!isDropdownOpen)}
                            className="flex items-center gap-3 px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl hover:bg-white hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-500/5 transition-all duration-300 group"
                        >
                            <Calendar className="w-4 h-4 text-indigo-500" />
                            <span className="text-sm font-black text-slate-700 min-w-[80px] text-left">{currentFilterLabel}</span>
                            <ChevronDown className={`w-4 h-4 text-slate-400 transition-transform duration-300 ${isDropdownOpen ? 'rotate-180' : ''}`} />
                        </button>

                        {isDropdownOpen && (
                            <div className="absolute right-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-200 overflow-hidden ring-1 ring-slate-900/5">
                                {!showCustomDate ? (
                                    filterOptions.map((option) => (
                                        <button
                                            key={option.value}
                                            onClick={() => {
                                                if (option.value === 'custom') {
                                                    setShowCustomDate(true);
                                                } else {
                                                    fetchKPI(option.value);
                                                }
                                            }}
                                            className={`w-full flex items-center justify-between px-4 py-2.5 text-sm font-bold transition-colors ${data.stats.filter === option.value
                                                    ? 'bg-indigo-50 text-indigo-600'
                                                    : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600'
                                                }`}
                                        >
                                            {option.label}
                                            {data.stats.filter === option.value && <Check className="w-4 h-4" />}
                                        </button>
                                    ))
                                ) : (
                                    <div className="p-4 space-y-4">
                                        <div className="flex items-center justify-between mb-2">
                                            <span className="text-xs font-black text-slate-700 uppercase tracking-widest">{t('Tùy chỉnh ngày', 'Custom Date')}</span>
                                            <button onClick={() => setShowCustomDate(false)} className="text-slate-400 hover:text-rose-500"><X className="w-4 h-4" /></button>
                                        </div>
                                        <div className="space-y-3">
                                            <div>
                                                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 block">{t('Từ ngày', 'From Date')}</label>
                                                <input 
                                                    type="date" 
                                                    max={todayStr}
                                                    value={customStart}
                                                    onChange={e => setCustomStart(e.target.value)}
                                                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                                                />
                                            </div>
                                            <div>
                                                <label className="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1 block">{t('Đến ngày', 'To Date')}</label>
                                                <input 
                                                    type="date" 
                                                    max={todayStr}
                                                    value={customEnd}
                                                    onChange={e => setCustomEnd(e.target.value)}
                                                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-slate-700 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none"
                                                />
                                            </div>
                                        </div>
                                        <Button 
                                            className="w-full h-9 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest"
                                            disabled={!customStart || !customEnd || customStart > customEnd}
                                            onClick={() => fetchKPI('custom', customStart, customEnd)}
                                        >
                                            {t('Áp dụng', 'Apply')}
                                        </Button>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    <Button
                        variant="outline"
                        size="icon"
                        className="rounded-xl h-10 w-10 border-slate-100 hover:bg-indigo-50 hover:text-indigo-600 transition-all"
                        onClick={() => fetchKPI(data.stats.filter)}
                    >
                        <RefreshCw className={`w-3.5 h-3.5 ${isRefreshing ? 'animate-spin' : ''}`} />
                    </Button>
                </div>
            </div>

            {/* Metrics Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <MetricCard icon={<TrendingUp />} value={`${formatMoney(data.stats.total_sales_revenue)}đ`} label={t('Doanh thu', 'Revenue')} color="indigo" />
                <MetricCard icon={<ShoppingCart />} value={`${data.stats.total_orders_completed}`} label={t('Đơn hàng', 'Orders')} color="emerald" />
                <MetricCard icon={<Wrench />} value={`${data.stats.total_repairs_done}`} label={t('Kỹ thuật', 'Technical')} color="amber" />
                <div className="bg-slate-900 px-6 py-4 rounded-[1.25rem] shadow-lg flex items-center justify-between group cursor-default">
                    <div>
                        <div className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Top Sales</div>
                        <div className="text-sm font-black text-white truncate max-w-[120px]">{data.stats.top_sales?.full_name || 'N/A'}</div>
                    </div>
                    <Trophy className="w-6 h-6 text-yellow-400 opacity-50 group-hover:opacity-100 transition-opacity" />
                </div>
            </div>

            {/* Charts Section */}
            <div className={`grid gap-4 ${isRevenueExpanded ? 'grid-cols-1' : 'grid-cols-1 lg:grid-cols-3'}`}>
                <div className={`${isRevenueExpanded ? 'h-[500px]' : 'lg:col-span-2 h-[350px]'} bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 relative group`}>
                    <div className="flex items-center justify-between mb-6">
                        <span className="text-sm font-black text-slate-700 uppercase tracking-tight">{t('Doanh thu theo ngày', 'Daily Revenue')}</span>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="rounded-md h-8 w-8 hover:bg-slate-50"
                            onClick={() => setIsRevenueExpanded(!isRevenueExpanded)}
                        >
                            {isRevenueExpanded ? <Minimize2 className="w-3.5 h-3.5" /> : <Maximize2 className="w-3.5 h-3.5" />}
                        </Button>
                    </div>
                    <div className="absolute inset-0 pt-16 px-6 pb-6">
                        <canvas ref={revenueChartRef}></canvas>
                    </div>
                </div>

                {!isRevenueExpanded && (
                    <div className="h-[350px] bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 relative">
                        <span className="text-sm font-black text-slate-700 uppercase tracking-tight">{t('Cơ cấu Sales', 'Sales Structure')}</span>
                        <div className="absolute inset-0 pt-16 px-6 pb-6">
                            <canvas ref={distChartRef}></canvas>
                        </div>
                    </div>
                )}
            </div>

            {/* Tables Section */}
            <div className="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <TableCard
                    title={t('Bảng vàng Sales', 'Sales Leaderboard')}
                    icon={<Users className="w-4 h-4" />}
                    data={data.salesKPI}
                    color="indigo"
                    onExport={() => exportCSV(data.salesKPI, 'sales')}
                />
                <TableCard
                    title={t('Bảng vàng Kỹ thuật', 'Technical Leaderboard')}
                    icon={<Wrench className="w-4 h-4" />}
                    data={data.techKPI}
                    color="emerald"
                    onExport={() => exportCSV(data.techKPI, 'tech')}
                />
            </div>
        </div>
    );
};

const exportCSV = (tableData: any[], filename: string) => {
    const csvContent = "data:text/csv;charset=utf-8,"
        + [t('Tên,Số lượng,Giá trị', 'Name,Quantity,Value')].join(",") + "\n"
        + tableData.map(item => `${item.full_name},${item.total_orders || item.completed_tickets},${item.total_revenue || 0}`).join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `${filename}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

const MetricCard = ({ icon, value, label, color }: any) => {
    const colors: any = {
        indigo: 'text-indigo-600 bg-indigo-50',
        emerald: 'text-emerald-600 bg-emerald-50',
        amber: 'text-amber-600 bg-amber-50',
    };
    return (
        <div className="bg-white p-4 rounded-[1.25rem] shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-md transition-shadow">
            <div className={`w-10 h-10 ${colors[color]} rounded-xl flex items-center justify-center shrink-0`}>
                {React.cloneElement(icon, { className: 'w-5 h-5' })}
            </div>
            <div>
                <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">{label}</div>
                <div className="text-lg font-black text-slate-800 tracking-tight leading-none mt-0.5">{value}</div>
            </div>
        </div>
    );
};

const TableCard = ({ title, icon, data = [], color, onExport }: any) => {
    const activeClasses: any = {
        indigo: 'bg-indigo-50 text-indigo-600',
        emerald: 'bg-emerald-50 text-emerald-600'
    };
    return (
        <div className="bg-white rounded-[1.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-50 flex items-center justify-between">
                <div className="flex items-center gap-2">
                    <div className={`w-8 h-8 ${activeClasses[color]} rounded-lg flex items-center justify-center`}>{icon}</div>
                    <h3 className="font-black text-slate-700 text-xs uppercase tracking-tight">{title}</h3>
                </div>
                <Button variant="ghost" size="sm" className="rounded-lg h-8 px-3 text-slate-400 hover:text-indigo-600 text-xs" onClick={onExport}>
                    <FileSpreadsheet className="w-3.5 h-3.5 mr-1.5" /> {t('Xuất', 'Export')}
                </Button>
            </div>
            <div className="overflow-x-auto">
                <table className="w-full text-left">
                    <thead>
                        <tr className="text-[9px] font-black text-slate-400 uppercase tracking-widest bg-slate-50/50">
                            <th className="px-6 py-3 w-16 text-center">{t('Hạng', 'Rank')}</th>
                            <th className="px-6 py-3">{t('Nhân viên', 'Employee')}</th>
                            <th className="px-6 py-3 text-right">{t('Giá trị', 'Value')}</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-50 text-xs">
                        {data.map((item, index) => (
                            <tr key={index} className="hover:bg-slate-50/50 transition-colors">
                                <td className="px-6 py-3 text-center font-black text-slate-200">#{index + 1}</td>
                                <td className="px-6 py-3 font-bold text-slate-600">{item.full_name}</td>
                                <td className="px-6 py-3 text-right font-black text-slate-800 italic">
                                    {item.total_revenue !== undefined ? `${new Intl.NumberFormat('vi-VN').format(item.total_revenue)}đ` : `${item.completed_tickets} ${t('ca', 'tickets')}`}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default KPIDashboard;
