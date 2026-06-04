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
    growth_revenue: number;
    growth_orders: number;
    growth_repairs: number;
    average_order_value: number;
    repair_success_rate: number;
    order_completion_rate: number;
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
    repairsChart: any[];
}

const KPIDashboard: React.FC<KPIProps> = (initialProps) => {
    const [data, setData] = useState<KPIProps>({
        stats: initialProps.stats || {
            total_sales_revenue: 0,
            total_orders_completed: 0,
            total_repairs_done: 0,
            growth_revenue: 0,
            growth_orders: 0,
            growth_repairs: 0,
            average_order_value: 0,
            repair_success_rate: 0,
            order_completion_rate: 0,
            top_sales: null,
            top_tech: null,
            filter: 'month',
            start_date: new Date().toISOString(),
            end_date: new Date().toISOString(),
        },
        salesKPI: initialProps.salesKPI || [],
        techKPI: initialProps.techKPI || [],
        revenueChart: initialProps.revenueChart || [],
        repairsChart: initialProps.repairsChart || []
    });

    const [isRefreshing, setIsRefreshing] = useState(false);
    const [isRevenueExpanded, setIsRevenueExpanded] = useState(false);
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const [showCustomDate, setShowCustomDate] = useState(false);
    const [customStart, setCustomStart] = useState('');
    const [customEnd, setCustomEnd] = useState('');
    const [chartMode, setChartMode] = useState<'revenue' | 'repairs'>('revenue');
    const [selectedEmployeeId, setSelectedEmployeeId] = useState<number | null>(null);
    const [employeeType, setEmployeeType] = useState<'sales' | 'tech' | null>(null);
    const [isDrawerOpen, setIsDrawerOpen] = useState(false);
    const [employeeDetailData, setEmployeeDetailData] = useState<any>(null);
    const [isDetailLoading, setIsDetailLoading] = useState(false);
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

    const openEmployeeDrawer = async (userId: number, type: 'sales' | 'tech') => {
        setSelectedEmployeeId(userId);
        setEmployeeType(type);
        setIsDrawerOpen(true);
        setIsDetailLoading(true);
        setEmployeeDetailData(null);
        try {
            let url = `/admin/kpi/employee/${userId}?filter=${data.stats.filter}`;
            if (data.stats.filter === 'custom' && customStart && customEnd) {
                url += `&start=${customStart}&end=${customEnd}`;
            }
            const response = await axios.get(url);
            setEmployeeDetailData(response.data);
        } catch (error) {
            console.error("Lỗi tải chi tiết nhân viên:", error);
        } finally {
            setIsDetailLoading(false);
        }
    };

    useEffect(() => {
        // Destroy existing chart instances first to prevent memory leaks and multiple rendering instances
        if (revChartInstance.current) {
            revChartInstance.current.destroy();
            revChartInstance.current = null;
        }
        if (distChartInstance.current) {
            distChartInstance.current.destroy();
            distChartInstance.current = null;
        }

        const chartData = chartMode === 'revenue' ? data.revenueChart : (data.repairsChart || []);

        // Use a small delay to allow container dimensions to finish rendering/animating (e.g. during Maximize/Minimize)
        const timer = setTimeout(() => {
            if (revenueChartRef.current && chartData.length > 0) {
                const ctx = revenueChartRef.current.getContext('2d');
                if (ctx) {
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    if (chartMode === 'revenue') {
                        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.15)');
                        gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');
                    } else {
                        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.15)');
                        gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
                    }

                    revChartInstance.current = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.map(d => {
                                const date = new Date(d.date);
                                return `${date.getDate()}/${date.getMonth() + 1}`;
                            }),
                            datasets: [{
                                label: chartMode === 'revenue' ? t('Doanh thu', 'Revenue') : t('Số ca sửa', 'Repairs'),
                                data: chartData.map(d => d.total),
                                borderColor: chartMode === 'revenue' ? '#4f46e5' : '#10b981',
                                borderWidth: 3,
                                fill: true,
                                backgroundColor: gradient,
                                tension: 0.4,
                                pointRadius: 0,
                                pointHoverRadius: 6,
                                pointHoverBackgroundColor: chartMode === 'revenue' ? '#4f46e5' : '#10b981',
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
                                    ticks: {
                                        callback: (v: any) => {
                                            if (chartMode === 'revenue') {
                                                return v >= 1000000 ? (v / 1000000) + 'M' : v.toLocaleString() + 'đ';
                                            }
                                            return v + ' ca';
                                        }
                                    }
                                },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }
            }

            if (distChartRef.current && data.salesKPI.length > 0 && !isRevenueExpanded) {
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
                                    labels: { 
                                        usePointStyle: true, 
                                        padding: 8, 
                                        font: { size: 10, weight: 'bold' },
                                        // Show only top 5 performers in the legend to preserve space for the doughnut circle
                                        filter: (legendItem: any) => legendItem.index < 5
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }, 100); // 100ms matches the layout transition tick

        return () => {
            clearTimeout(timer);
            if (revChartInstance.current) revChartInstance.current.destroy();
            if (distChartInstance.current) distChartInstance.current.destroy();
        };
    }, [data.revenueChart, data.repairsChart, data.salesKPI, isRevenueExpanded, chartMode]);

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

    const filter = data.stats.filter;
    let revTarget = 100000000; // 100M default
    let ordersTarget = 200;
    let repairsTarget = 100;

    if (filter === 'today' || filter === 'yesterday') {
        revTarget = 5000000; // 5M
        ordersTarget = 10;
        repairsTarget = 5;
    } else if (filter === 'week') {
        revTarget = 35000000; // 35M
        ordersTarget = 70;
        repairsTarget = 35;
    } else if (filter === 'year') {
        revTarget = 1000000000; // 1B
        ordersTarget = 2000;
        repairsTarget = 1000;
    } else if (filter === 'custom') {
        const days = Math.max(1, Math.round((new Date(data.stats.end_date).getTime() - new Date(data.stats.start_date).getTime()) / (1000 * 60 * 60 * 24)) + 1);
        revTarget = days * 5000000; // 5M per day
        ordersTarget = days * 10;
        repairsTarget = days * 5;
    }

    const formatCompact = (val: number) => {
        if (val >= 1000000000) return (val / 1000000000) + ' tỷ';
        if (val >= 1000000) return (val / 1000000) + 'M';
        return val.toLocaleString() + 'đ';
    };

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
                <MetricCard 
                    icon={<TrendingUp />} 
                    value={`${formatMoney(data.stats.total_sales_revenue)}đ`} 
                    label={t('Doanh thu', 'Revenue')} 
                    color="indigo" 
                    growth={data.stats.growth_revenue}
                    target={formatCompact(revTarget)}
                    progressValue={(data.stats.total_sales_revenue / revTarget) * 100}
                />
                <MetricCard 
                    icon={<ShoppingCart />} 
                    value={`${data.stats.total_orders_completed}`} 
                    label={t('Đơn hàng', 'Orders')} 
                    color="emerald" 
                    growth={data.stats.growth_orders}
                    target={ordersTarget}
                    progressValue={(data.stats.total_orders_completed / ordersTarget) * 100}
                />
                <MetricCard 
                    icon={<Wrench />} 
                    value={`${data.stats.total_repairs_done}`} 
                    label={t('Sửa chữa', 'Technical')} 
                    color="amber" 
                    growth={data.stats.growth_repairs}
                    target={repairsTarget}
                    progressValue={(data.stats.total_repairs_done / repairsTarget) * 100}
                />
                <div className="bg-slate-900 px-6 py-5 rounded-[1.5rem] shadow-lg flex items-center justify-between group cursor-default transition-all duration-300 hover:shadow-xl">
                    <div>
                        <div className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Top Sales</div>
                        <div className="text-base font-black text-white mt-1 truncate max-w-[150px]">{data.stats.top_sales?.full_name || 'N/A'}</div>
                        {data.stats.top_sales && (
                            <div className="text-[11px] font-bold text-yellow-400 mt-1">
                                {formatMoney(data.stats.top_sales.total_revenue)}đ
                            </div>
                        )}
                    </div>
                    <Trophy className="w-8 h-8 text-yellow-400 opacity-60 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300" />
                </div>
            </div>

            {/* Advanced Operational KPIs Row */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                {/* AOV Card */}
                <div className="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shrink-0 shadow-inner">
                            <ShoppingCart className="w-6 h-6" />
                        </div>
                        <div>
                            <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Giá trị đơn hàng trung bình (AOV)</div>
                            <div className="text-xl font-black text-slate-800 tracking-tight leading-none mt-1.5">
                                {formatMoney(data.stats.average_order_value || 0)}đ
                            </div>
                            <div className="text-[11px] text-slate-400 mt-1 font-medium">
                                Doanh thu bình quân trên mỗi đơn hàng hoàn tất
                            </div>
                        </div>
                    </div>
                </div>

                {/* Order Completion Rate Card */}
                <div className="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shrink-0 shadow-inner">
                            <TrendingUp className="w-6 h-6" />
                        </div>
                        <div>
                            <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tỷ lệ hoàn thành đơn hàng</div>
                            <div className="text-xl font-black text-slate-800 tracking-tight leading-none mt-1.5">
                                {data.stats.order_completion_rate || 0}%
                            </div>
                            <div className="text-[11px] text-slate-400 mt-1 font-medium">
                                Phần trăm số đơn đã giao trên tổng đơn phát sinh
                            </div>
                        </div>
                    </div>
                    {/* Modern Radial Ring Badge */}
                    <div className="relative w-12 h-12 flex items-center justify-center shrink-0">
                        <svg className="w-full h-full transform -rotate-90">
                            <circle cx="24" cy="24" r="18" stroke="#f1f5f9" strokeWidth="4" fill="transparent" />
                            <circle 
                                cx="24" 
                                cy="24" 
                                r="18" 
                                stroke="#10b981" 
                                strokeWidth="4" 
                                fill="transparent" 
                                strokeDasharray={2 * Math.PI * 18}
                                strokeDashoffset={2 * Math.PI * 18 * (1 - (data.stats.order_completion_rate || 0) / 100)}
                                strokeLinecap="round"
                                className="transition-all duration-500"
                            />
                        </svg>
                        <span className="absolute text-[10px] font-black text-slate-700">{Math.round(data.stats.order_completion_rate || 0)}%</span>
                    </div>
                </div>

                {/* Repair Success Rate Card */}
                <div className="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 hover:shadow-md transition-all duration-300 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 bg-pink-50 text-pink-600 rounded-xl flex items-center justify-center shrink-0 shadow-inner">
                            <Wrench className="w-6 h-6" />
                        </div>
                        <div>
                            <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tỷ lệ sửa máy thành công</div>
                            <div className="text-xl font-black text-slate-800 tracking-tight leading-none mt-1.5">
                                {data.stats.repair_success_rate || 0}%
                            </div>
                            <div className="text-[11px] text-slate-400 mt-1 font-medium">
                                Phần trăm số ca sửa xong trên tổng số phiếu tiếp nhận
                            </div>
                        </div>
                    </div>
                    {/* Modern Radial Ring Badge */}
                    <div className="relative w-12 h-12 flex items-center justify-center shrink-0">
                        <svg className="w-full h-full transform -rotate-90">
                            <circle cx="24" cy="24" r="18" stroke="#f1f5f9" strokeWidth="4" fill="transparent" />
                            <circle 
                                cx="24" 
                                cy="24" 
                                r="18" 
                                stroke="#ec4899" 
                                strokeWidth="4" 
                                fill="transparent" 
                                strokeDasharray={2 * Math.PI * 18}
                                strokeDashoffset={2 * Math.PI * 18 * (1 - (data.stats.repair_success_rate || 0) / 100)}
                                strokeLinecap="round"
                                className="transition-all duration-500"
                            />
                        </svg>
                        <span className="absolute text-[10px] font-black text-slate-700">{Math.round(data.stats.repair_success_rate || 0)}%</span>
                    </div>
                </div>
            </div>

            {/* Charts Section */}
            <div className={`grid gap-4 ${isRevenueExpanded ? 'grid-cols-1' : 'grid-cols-1 lg:grid-cols-3'}`}>
                <div className={`${isRevenueExpanded ? 'h-[500px]' : 'lg:col-span-2 h-[350px]'} bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 flex flex-col group`}>
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 shrink-0">
                        <div className="flex flex-col xs:flex-row xs:items-center gap-4">
                            <span className="text-xs sm:text-sm font-black text-slate-700 uppercase tracking-tight truncate">
                                {chartMode === 'revenue' ? t('Doanh thu theo ngày', 'Daily Revenue') : t('Số ca sửa hoàn thành', 'Completed Repairs')}
                            </span>
                            {/* Segment Toggle Buttons */}
                            <div className="flex bg-slate-100 p-0.5 rounded-lg text-[10px] sm:text-[11px] font-black uppercase tracking-wider w-fit">
                                <button
                                    onClick={() => setChartMode('revenue')}
                                    className={`px-3 py-1.5 rounded-md transition-all ${
                                        chartMode === 'revenue' 
                                            ? 'bg-white text-indigo-600 shadow-sm' 
                                            : 'text-slate-500 hover:text-slate-800'
                                    }`}
                                >
                                    Doanh thu
                                </button>
                                <button
                                    onClick={() => setChartMode('repairs')}
                                    className={`px-3 py-1.5 rounded-md transition-all ${
                                        chartMode === 'repairs' 
                                            ? 'bg-white text-emerald-600 shadow-sm' 
                                            : 'text-slate-500 hover:text-slate-800'
                                    }`}
                                >
                                    Ca sửa
                                </button>
                            </div>
                        </div>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="rounded-md h-8 w-8 hover:bg-slate-50 shrink-0 self-end sm:self-auto"
                            onClick={() => setIsRevenueExpanded(!isRevenueExpanded)}
                        >
                            {isRevenueExpanded ? <Minimize2 className="w-3.5 h-3.5" /> : <Maximize2 className="w-3.5 h-3.5" />}
                        </Button>
                    </div>
                    <div className="flex-1 w-full min-h-0 relative">
                        <canvas ref={revenueChartRef}></canvas>
                    </div>
                </div>

                {!isRevenueExpanded && (
                    <div className="h-[350px] bg-white p-6 rounded-[1.5rem] shadow-sm border border-slate-100 flex flex-col">
                        <span className="text-sm font-black text-slate-700 uppercase tracking-tight mb-6 shrink-0">{t('Cơ cấu Sales', 'Sales Structure')}</span>
                        <div className="flex-1 w-full min-h-0 relative">
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
                    onExport={() => exportExcel(data.salesKPI, 'sales', currentFilterLabel)}
                    onRowClick={(id: number) => openEmployeeDrawer(id, 'sales')}
                />
                <TableCard
                    title={t('Bảng vàng Kỹ thuật', 'Technical Leaderboard')}
                    icon={<Wrench className="w-4 h-4" />}
                    data={data.techKPI}
                    color="emerald"
                    onExport={() => exportExcel(data.techKPI, 'tech', currentFilterLabel)}
                    onRowClick={(id: number) => openEmployeeDrawer(id, 'tech')}
                />
            </div>

            {/* Slide-over Drawer for Employee Details */}
            <div className={`fixed inset-0 z-50 overflow-hidden transition-all duration-300 ${isDrawerOpen ? 'pointer-events-auto' : 'pointer-events-none'}`}>
                {/* Backdrop with premium blur and fade */}
                <div 
                    className={`absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity duration-300 ${isDrawerOpen ? 'opacity-100' : 'opacity-0'}`} 
                    onClick={() => setIsDrawerOpen(false)}
                ></div>

                <div className="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-6 sm:pl-10">
                    <div className={`pointer-events-auto w-screen max-w-md transform transition-transform duration-300 ease-in-out bg-white shadow-2xl flex flex-col h-full border-l border-slate-100 ${isDrawerOpen ? 'translate-x-0' : 'translate-x-full'}`}>
                        {isDrawerOpen && (
                            <>
                                {/* Header */}
                                <div className="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                                    <h2 className="text-base font-black text-slate-800 uppercase tracking-tight">
                                        {t('Chi tiết hiệu suất', 'Performance details')}
                                    </h2>
                                    <button 
                                        onClick={() => setIsDrawerOpen(false)}
                                        className="rounded-lg p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 hover:rotate-90 transition-all duration-300 outline-none"
                                    >
                                        <X className="w-5 h-5" />
                                    </button>
                                </div>

                                {/* Content */}
                                <div className="flex-1 overflow-y-auto p-6 space-y-6 scrollbar-thin scrollbar-thumb-slate-200">
                                    {isDetailLoading ? (
                                        /* Skeleton Loading with premium pulse */
                                        <div className="space-y-6 animate-pulse">
                                            <div className="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                                <div className="w-14 h-14 bg-slate-200 rounded-full"></div>
                                                <div className="space-y-2 flex-1">
                                                    <div className="h-4 bg-slate-200 rounded w-1/2"></div>
                                                    <div className="h-3 bg-slate-200 rounded w-3/4"></div>
                                                </div>
                                            </div>
                                            <div className="space-y-3">
                                                <div className="h-3 bg-slate-200 rounded w-1/3"></div>
                                                <div className="grid grid-cols-2 gap-4">
                                                    <div className="h-20 bg-slate-100 rounded-2xl"></div>
                                                    <div className="h-20 bg-slate-100 rounded-2xl"></div>
                                                    <div className="h-20 bg-slate-100 rounded-2xl col-span-2"></div>
                                                </div>
                                            </div>
                                            <div className="space-y-3">
                                                <div className="h-3 bg-slate-200 rounded w-1/3"></div>
                                                <div className="h-14 bg-slate-50 rounded-xl"></div>
                                                <div className="h-14 bg-slate-50 rounded-xl"></div>
                                                <div className="h-14 bg-slate-50 rounded-xl"></div>
                                            </div>
                                        </div>
                                    ) : employeeDetailData ? (
                                        <>
                                            {/* Profile Header */}
                                            <div className="flex items-center gap-4 bg-gradient-to-r from-slate-50 to-indigo-50/10 p-4 rounded-2xl border border-slate-100">
                                                <div className="w-14 h-14 bg-gradient-to-tr from-indigo-500 to-purple-600 text-white rounded-full flex items-center justify-center text-xl font-black shadow-md shadow-indigo-200 shrink-0">
                                                    {employeeDetailData.employee.full_name?.split(' ').pop()?.substring(0, 2).toUpperCase() || 'NV'}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center gap-2">
                                                        <h3 className="text-lg font-black text-slate-800 truncate">{employeeDetailData.employee.full_name}</h3>
                                                        {employeeDetailData.employee.is_online ? (
                                                            <span className="flex h-2.5 w-2.5 relative shrink-0" title="Đang hoạt động">
                                                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                                <span className="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                                            </span>
                                                        ) : (
                                                            <span className="w-2.5 h-2.5 rounded-full bg-slate-300 shrink-0" title="Ngoại tuyến"></span>
                                                        )}
                                                    </div>
                                                    <p className="text-xs font-bold text-slate-500 truncate">{employeeDetailData.employee.email}</p>
                                                    <p className="text-xs font-semibold text-slate-400 mt-0.5">{employeeDetailData.employee.phone_number || t('Chưa có SĐT', 'No Phone')}</p>
                                                    <div className="mt-1">
                                                        <span className="inline-block bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider">
                                                            ID: #{employeeDetailData.employee.user_id}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {/* Metrics Scorecard */}
                                            <div>
                                                <h4 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">{t('Chỉ số hiệu suất', 'Performance Indicators')}</h4>
                                                
                                                {employeeType === 'sales' ? (
                                                    <div className="grid grid-cols-2 gap-4">
                                                        <div className="bg-indigo-50/40 border border-indigo-100/50 p-4 rounded-2xl hover:scale-[1.02] transition-transform duration-300">
                                                            <div className="text-[10px] font-black text-indigo-500 uppercase tracking-widest">{t('Doanh thu', 'Revenue')}</div>
                                                            <div className="text-base sm:text-lg font-black text-indigo-900 mt-1">{formatMoney(employeeDetailData.stats.revenue)}đ</div>
                                                        </div>
                                                        <div className="bg-emerald-50/40 border border-emerald-100/50 p-4 rounded-2xl hover:scale-[1.02] transition-transform duration-300">
                                                            <div className="text-[10px] font-black text-emerald-500 uppercase tracking-widest">{t('Đơn hoàn thành', 'Orders Completed')}</div>
                                                            <div className="text-base sm:text-lg font-black text-emerald-900 mt-1">{employeeDetailData.stats.orders} {t('đơn', 'orders')}</div>
                                                        </div>
                                                        <div className="bg-slate-50 border border-slate-100 p-4 rounded-2xl col-span-2 hover:scale-[1.01] transition-transform duration-300">
                                                            <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">{t('Giá trị đơn trung bình (AOV)', 'Average Order Value')}</div>
                                                            <div className="text-base sm:text-lg font-black text-slate-800 mt-1">{formatMoney(employeeDetailData.stats.aov)}đ</div>
                                                        </div>
                                                    </div>
                                                ) : (
                                                    <div className="grid grid-cols-2 gap-4">
                                                        <div className="bg-emerald-50/40 border border-emerald-100/50 p-4 rounded-2xl hover:scale-[1.02] transition-transform duration-300">
                                                            <div className="text-[10px] font-black text-emerald-500 uppercase tracking-widest">{t('Ca sửa xong', 'Repairs Done')}</div>
                                                            <div className="text-base sm:text-lg font-black text-emerald-900 mt-1">{employeeDetailData.stats.repairs} {t('ca', 'tickets')}</div>
                                                        </div>
                                                        <div className="bg-amber-50/40 border border-amber-100/50 p-4 rounded-2xl hover:scale-[1.02] transition-transform duration-300">
                                                            <div className="text-[10px] font-black text-amber-500 uppercase tracking-widest">{t('Tổng số phiếu', 'Total Tickets')}</div>
                                                            <div className="text-base sm:text-lg font-black text-amber-900 mt-1">{employeeDetailData.stats.total_tickets} {t('phiếu', 'tickets')}</div>
                                                        </div>
                                                        <div className="bg-slate-50 border border-slate-100 p-4 rounded-2xl col-span-2 flex items-center justify-between hover:scale-[1.01] transition-transform duration-300">
                                                            <div>
                                                                <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">{t('Tỷ lệ thành công', 'Success Rate')}</div>
                                                                <div className="text-base sm:text-lg font-black text-slate-800 mt-1">{employeeDetailData.stats.repair_success_rate}%</div>
                                                            </div>
                                                            <div className="w-24 h-2 bg-slate-200 rounded-full overflow-hidden">
                                                                <div className="h-full bg-emerald-500 rounded-full transition-all duration-500" style={{ width: `${employeeDetailData.stats.repair_success_rate}%` }}></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                )}
                                            </div>

                                            {/* Recent Activity List */}
                                            <div className="flex-1 flex flex-col min-h-[300px]">
                                                <h4 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-3">
                                                    {t('Hoạt động gần đây (Tối đa 10)', 'Recent Activity (Max 10)')}
                                                </h4>

                                                {employeeType === 'sales' ? (
                                                    employeeDetailData.orders && employeeDetailData.orders.length > 0 ? (
                                                        <div className="space-y-3 overflow-y-auto pr-1 max-h-[350px]">
                                                            {employeeDetailData.orders.map((order: any) => (
                                                                <div key={order.order_id} className="p-3.5 bg-white border border-slate-100 rounded-xl hover:shadow-md hover:border-slate-200 transition-all duration-200 flex items-center justify-between">
                                                                    <div>
                                                                        <div className="text-xs font-black text-slate-800">{order.order_code}</div>
                                                                        <div className="text-[10px] font-bold text-slate-400 mt-0.5">{order.created_at}</div>
                                                                    </div>
                                                                    <div className="text-right">
                                                                        <div className="text-xs font-black text-slate-800">{formatMoney(order.final_amount)}đ</div>
                                                                        <span className={`inline-block px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-md mt-1 ${
                                                                            order.status === 'Delivered' 
                                                                                ? 'bg-emerald-50 text-emerald-600' 
                                                                                : order.status === 'Cancelled' 
                                                                                    ? 'bg-rose-50 text-rose-600' 
                                                                                    : 'bg-amber-50 text-amber-600'
                                                                        }`}>
                                                                            {order.status === 'Delivered' ? t('Đã giao', 'Delivered') : order.status === 'Cancelled' ? t('Đã hủy', 'Cancelled') : order.status}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    ) : (
                                                        <div className="flex-1 flex flex-col items-center justify-center py-12 text-slate-400 text-xs bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                                                            <Loader2 className="w-8 h-8 text-slate-300 mb-2" />
                                                            <span>{t('Không có đơn hàng nào trong kỳ này', 'No orders in this period')}</span>
                                                        </div>
                                                    )
                                                ) : (
                                                    employeeDetailData.tickets && employeeDetailData.tickets.length > 0 ? (
                                                        <div className="space-y-3 overflow-y-auto pr-1 max-h-[350px]">
                                                            {employeeDetailData.tickets.map((ticket: any) => (
                                                                <div key={ticket.ticket_id} className="p-3.5 bg-white border border-slate-100 rounded-xl hover:shadow-md hover:border-slate-200 transition-all duration-200 flex items-center justify-between">
                                                                    <div className="min-w-0 flex-1 pr-3">
                                                                        <div className="text-xs font-black text-slate-800 flex items-center gap-1.5">
                                                                            <span>#RT-{ticket.ticket_id}</span>
                                                                            <span className="text-[10px] font-bold text-slate-400 truncate">({ticket.customer_name})</span>
                                                                        </div>
                                                                        <div className="text-[10px] text-slate-500 font-semibold truncate mt-0.5">{ticket.service_name || t('Chưa xác định dịch vụ', 'No service')}</div>
                                                                        {ticket.imei_serial && (
                                                                            <div className="text-[10px] text-slate-400 font-medium truncate mt-0.5">IMEI: {ticket.imei_serial}</div>
                                                                        )}
                                                                        <div className="text-[9px] font-bold text-slate-400 mt-0.5">{ticket.created_at}</div>
                                                                    </div>
                                                                    <div className="text-right shrink-0">
                                                                        <div className="text-xs font-black text-slate-800">{formatMoney(ticket.service_fee)}đ</div>
                                                                        <span className={`inline-block px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-md mt-1 ${
                                                                            ticket.status === 'Done' 
                                                                                ? 'bg-emerald-50 text-emerald-600' 
                                                                                : ticket.status === 'Cancelled' 
                                                                                    ? 'bg-rose-50 text-rose-600' 
                                                                                    : 'bg-amber-50 text-amber-600'
                                                                        }`}>
                                                                            {ticket.status === 'Done' ? t('Đã sửa xong', 'Done') : ticket.status === 'Under_Repair' ? t('Đang sửa', 'Under Repair') : ticket.status === 'Received' ? t('Đã nhận', 'Received') : ticket.status}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            ))}
                                                        </div>
                                                    ) : (
                                                        <div className="flex-1 flex flex-col items-center justify-center py-12 text-slate-400 text-xs bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                                                            <Loader2 className="w-8 h-8 text-slate-300 mb-2" />
                                                            <span>{t('Không có phiếu sửa chữa nào trong kỳ này', 'No tickets in this period')}</span>
                                                        </div>
                                                    )
                                                )}
                                            </div>
                                        </>
                                    ) : (
                                        <div className="text-center text-rose-500 py-12 font-bold">{t('Lỗi tải dữ liệu.', 'Failed to load details.')}</div>
                                    )}
                                </div>
                            </>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
};

const exportExcel = (tableData: any[], type: 'sales' | 'tech', periodLabel: string) => {
    const BOM = "\uFEFF";
    let title = type === 'sales' ? t('Bảng vàng Sales', 'Sales Leaderboard') : t('Bảng vàng Kỹ thuật', 'Technical Leaderboard');
    
    let html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">`;
    html += `<head><meta http-equiv="content-type" content="text/html; charset=UTF-8">`;
    html += `<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Sheet1</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->`;
    html += `<style>`;
    html += `table { border-collapse: collapse; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; width: 100%; }`;
    html += `th { background-color: ${type === 'sales' ? '#4f46e5' : '#10b981'}; color: #ffffff; font-weight: bold; padding: 12px 15px; border: 1px solid #cbd5e1; text-align: left; font-size: 13px; }`;
    html += `td { padding: 10px 15px; border: 1px solid #cbd5e1; text-align: left; font-size: 12px; color: #334155; }`;
    html += `.header-title { font-size: 16px; font-weight: bold; color: #1e293b; padding: 10px 0; }`;
    html += `.rank { text-align: center; font-weight: bold; color: #475569; }`;
    html += `.number { text-align: right; }`;
    html += `</style></head><body>`;
    
    html += `<table>`;
    html += `<tr><td colspan="${type === 'sales' ? 4 : 3}" class="header-title" style="border: none;">${title} (${periodLabel})</td></tr>`;
    html += `<tr><td colspan="${type === 'sales' ? 4 : 3}" style="border: none; color: #64748b; font-size: 11px;">Ngày xuất: ${new Date().toLocaleString('vi-VN')}</td></tr>`;
    html += `<tr><td colspan="${type === 'sales' ? 4 : 3}" style="border: none; height: 10px;"></td></tr>`;
    
    if (type === 'sales') {
        html += `<tr>`;
        html += `<th style="width: 80px; text-align: center;">${t('Hạng', 'Rank')}</th>`;
        html += `<th>${t('Họ và tên', 'Full Name')}</th>`;
        html += `<th style="text-align: right; width: 150px;">${t('Số lượng đơn', 'Total Orders')}</th>`;
        html += `<th style="text-align: right; width: 200px;">${t('Doanh thu (đ)', 'Revenue (VND)')}</th>`;
        html += `</tr>`;
        
        tableData.forEach((item, index) => {
            html += `<tr>`;
            html += `<td class="rank">${index + 1}</td>`;
            html += `<td style="font-weight: bold;">${item.full_name}</td>`;
            html += `<td class="number">${item.total_orders || 0}</td>`;
            html += `<td class="number" style="color: #4f46e5; font-weight: bold;">${new Intl.NumberFormat('vi-VN').format(item.total_revenue || 0)}đ</td>`;
            html += `</tr>`;
        });
    } else {
        html += `<tr>`;
        html += `<th style="width: 80px; text-align: center;">${t('Hạng', 'Rank')}</th>`;
        html += `<th>${t('Họ và tên', 'Full Name')}</th>`;
        html += `<th style="text-align: right; width: 200px;">${t('Số ca hoàn thành', 'Completed Repairs')}</th>`;
        html += `</tr>`;
        
        tableData.forEach((item, index) => {
            html += `<tr>`;
            html += `<td class="rank">${index + 1}</td>`;
            html += `<td style="font-weight: bold;">${item.full_name}</td>`;
            html += `<td class="number" style="color: #10b981; font-weight: bold;">${item.completed_tickets || 0} ca</td>`;
            html += `</tr>`;
        });
    }
    
    html += `</table></body></html>`;
    
    const blob = new Blob([BOM + html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    
    // Generate beautiful clean filename
    const cleanPeriod = periodLabel.trim().replace(/[\s\.]+/g, '_');
    const timestamp = new Date().toLocaleDateString('vi-VN').replace(/\//g, '_');
    const prefix = type === 'sales' ? 'Bang_Vang_Sales' : 'Bang_Vang_Ky_Thuat';
    const filename = `${prefix}_${cleanPeriod}_${timestamp}.xls`;
    
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

const MetricCard = ({ icon, value, label, color, growth, target, progressValue }: any) => {
    const colors: any = {
        indigo: 'text-indigo-600 bg-indigo-50',
        emerald: 'text-emerald-600 bg-emerald-50',
        amber: 'text-amber-600 bg-amber-50',
    };
    
    const hasProgress = progressValue !== undefined && target !== undefined;
    
    return (
        <div className="bg-white p-5 rounded-[1.5rem] shadow-sm border border-slate-100 flex flex-col justify-between hover:shadow-md transition-all duration-300">
            <div className="flex items-center justify-between w-full">
                <div className="flex items-center gap-3">
                    <div className={`w-10 h-10 ${colors[color]} rounded-xl flex items-center justify-center shrink-0`}>
                        {React.cloneElement(icon, { className: 'w-5 h-5' })}
                    </div>
                    <div>
                        <div className="text-[10px] font-black text-slate-400 uppercase tracking-widest">{label}</div>
                        <div className="text-xl font-black text-slate-800 tracking-tight leading-none mt-1">{value}</div>
                    </div>
                </div>
                
                {growth !== undefined && (
                    <div className={`flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black tracking-tight ${
                        growth >= 0 
                            ? 'text-emerald-600 bg-emerald-50' 
                            : 'text-rose-600 bg-rose-50'
                    }`}>
                        {growth >= 0 ? '+' : ''}{growth}%
                        <span className="text-[9px] font-bold text-slate-400">vs kỳ trước</span>
                    </div>
                )}
            </div>

            {hasProgress && (
                <div className="mt-4 pt-3 border-t border-slate-50">
                    <div className="flex justify-between items-center text-[10px] font-bold text-slate-500 mb-1">
                        <span>Mục tiêu: {target}</span>
                        <span className="text-indigo-600 font-black">{isNaN(progressValue) ? 0 : Math.min(100, Math.round(progressValue))}%</span>
                    </div>
                    <div className="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                        <div 
                            className={`h-full rounded-full transition-all duration-500 ${
                                color === 'indigo' ? 'bg-indigo-600' : color === 'emerald' ? 'bg-emerald-500' : 'bg-amber-500'
                            }`}
                            style={{ width: `${isNaN(progressValue) ? 0 : Math.min(100, progressValue)}%` }}
                        />
                    </div>
                </div>
            )}
        </div>
    );
};

const TableCard = ({ title, icon, data = [], color, onExport, onRowClick }: any) => {
    const activeClasses: any = {
        indigo: 'bg-indigo-50 text-indigo-600',
        emerald: 'bg-emerald-50 text-emerald-600'
    };
    const hoverClass = color === 'indigo' ? 'hover:bg-indigo-50/40' : 'hover:bg-emerald-50/40';
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
                            <tr 
                                key={index} 
                                onClick={() => onRowClick && onRowClick(item.user_id)}
                                className={`transition-colors duration-200 ${onRowClick ? `${hoverClass} cursor-pointer` : 'hover:bg-slate-50/50'}`}
                            >
                                <td className="px-6 py-3 text-center">
                                    {index === 0 ? (
                                        <span className="inline-flex items-center justify-center w-6 h-6 bg-yellow-100 text-yellow-800 rounded-full text-xs font-black shadow-sm" title="Quán quân">🥇</span>
                                    ) : index === 1 ? (
                                        <span className="inline-flex items-center justify-center w-6 h-6 bg-slate-100 text-slate-800 rounded-full text-xs font-black shadow-sm" title="Á quân">🥈</span>
                                    ) : index === 2 ? (
                                        <span className="inline-flex items-center justify-center w-6 h-6 bg-amber-100 text-amber-800 rounded-full text-xs font-black shadow-sm" title="Hạng ba">🥉</span>
                                    ) : (
                                        <span className="text-[11px] font-black text-slate-400">#{index + 1}</span>
                                    )}
                                </td>
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
