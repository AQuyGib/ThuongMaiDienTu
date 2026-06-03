import React, { useState, useEffect, useRef, useTransition, useCallback } from 'react';
import useSWR from 'swr';
import axios from 'axios';
import toast, { Toaster } from 'react-hot-toast';
import { 
  Users, 
  ShieldCheck, 
  ShieldAlert, 
  Shield, 
  Plus, 
  Search, 
  Filter, 
  Activity, 
  FileSpreadsheet, 
  X, 
  Trash2, 
  Edit, 
  CheckCircle2, 
  XCircle, 
  Calendar,
  RotateCcw,
  ShieldBan,
  Zap,
  Clock,
  Hash,
  Wifi,
  WifiOff,
  ChevronDown,
  FileText
} from 'lucide-react';
import Chart from 'chart.js/auto';
import EmployeeTable from './EmployeeTable';
import EmployeeModalForm from './EmployeeModalForm';

// Khởi tạo BroadcastChannel để đồng bộ hóa dữ liệu thời gian thực giữa nhiều tab trình duyệt khác nhau
const channel = new BroadcastChannel('employee_sync_channel');

// --- TS INTERFACES ---
interface Employee {
  user_id: number;
  full_name: string;
  email: string;
  phone?: string;
  role_id: number;
  role?: {
    role_id: number;
    name: string;
    description?: string;
  };
  status: 'Active' | 'Banned';
  version?: number;
  created_at: string | null;
  updated_at?: string | null;
  last_login_at?: string | null;
  is_online?: boolean;
}

interface Role {
  role_id: number;
  name: string;
  description: string;
}

interface Stats {
  total: number;
  active: number;
  banned: number;
  by_role: {
    admin: number;
    manager: number;
    staff: number;
  };
}

interface EmployeeManagerProps {
  employees: {
    data: Employee[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
    from?: number;
    to?: number;
  };
  roles: Role[];
  stats: Stats;
  auth_id: number;
}

// --- GLOBAL AXIOS INTERCEPTOR: XỬ LÝ HẾT HẠN SESSION / CSRF ---
let interceptorRegistered = false;
const registerSessionInterceptor = () => {
  if (interceptorRegistered) return;
  axios.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 419 || error.response?.status === 401) {
        toast.error('🔴 Phiên làm việc đã hết hạn! Đang chuyển hướng về trang đăng nhập...');
        setTimeout(() => {
          window.location.href = '/login';
        }, 2000);
      }
      return Promise.reject(error);
    }
  );
  interceptorRegistered = true;
};

// --- SWR FETCHER ENGINE ---
const fetcher = (url: string, params: any) =>
  axios.get(url, { params, headers: { 'X-Requested-With': 'XMLHttpRequest' } }).then(res => res.data);

export default function EmployeeManager({ 
  employees: initialEmployees, 
  roles, 
  stats: initialStats,
  auth_id
}: EmployeeManagerProps) {
  
  useEffect(() => {
    registerSessionInterceptor();
  }, []);

  const [isPending, startTransition] = useTransition();

  // --- STATE BỘ LỌC CLIENT ---
  const [searchQuery, setSearchQuery] = useState('');
  const [debouncedSearch, setDebouncedSearch] = useState('');
  const [roleId, setRoleId] = useState('');
  const [status, setStatus] = useState('');
  const [sort, setSort] = useState('oldest');
  const [page, setPage] = useState(1);

  // UI Control states
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedEmployee, setSelectedEmployee] = useState<Employee | null>(null);
  const [isAnalyticsOpen, setIsAnalyticsOpen] = useState(false);
  const [activeDrawerEmployee, setActiveDrawerEmployee] = useState<Employee | null>(null);
  const [isExportDropdownOpen, setIsExportDropdownOpen] = useState(false);
  const exportDropdownRef = useRef<HTMLDivElement>(null);

  // Batch Selection state
  const [selectedIds, setSelectedIds] = useState<Set<number>>(new Set());

  // Search input ref for Ctrl+K
  const searchInputRef = useRef<HTMLInputElement>(null);

  // Chart refs
  const distChartRef = useRef<HTMLCanvasElement>(null);
  const distChartInstance = useRef<Chart | null>(null);

  // --- SEARCH DEBOUNCE ENGINE (500ms) ---
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearch(searchQuery);
      setPage(1); // Reset trang về 1 khi lọc
    }, 500);
    return () => clearTimeout(timer);
  }, [searchQuery]);

  // --- SWR HOOK: QUẢN LÝ DỮ LIỆU & CACHE ĐỘNG ---
  const swrKey = ['/admin/employees', debouncedSearch, roleId, status, sort, page];
  const { data, error, isLoading, mutate } = useSWR(
    swrKey,
    () => fetcher('/admin/employees', { search: debouncedSearch, role_id: roleId, status, sort, page }),
    {
      fallbackData: { employees: initialEmployees, stats: initialStats }, // Hydration server-side data ban đầu
      keepPreviousData: true, // Tránh nhấp nháy UI khi tải trang phân trang tiếp theo
      revalidateOnFocus: true, // Tự động đồng bộ tải lại khi chuyển tab về màn hình này
    }
  );

  // Tự động đồng bộ hóa chéo giữa các tab trình duyệt bằng BroadcastChannel
  useEffect(() => {
    const handleSync = (event: MessageEvent) => {
      if (event.data === 'REFRESH_EMPLOYEES') {
        mutate();
      }
    };
    channel.addEventListener('message', handleSync);
    return () => {
      channel.removeEventListener('message', handleSync);
    };
  }, [mutate]);

  const employeesData = data?.employees || initialEmployees;
  const statsData = data?.stats || initialStats;

  // --- KEYBOARD SHORTCUTS ---
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      // Escape → đóng modal / drawer
      if (e.key === 'Escape') {
        if (isModalOpen) { setIsModalOpen(false); return; }
        if (activeDrawerEmployee) { setActiveDrawerEmployee(null); return; }
        // Clear selection nếu đang chọn batch
        if (selectedIds.size > 0) { setSelectedIds(new Set()); return; }
      }
      // Ctrl+K → focus ô tìm kiếm
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        searchInputRef.current?.focus();
      }
    };
    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  }, [isModalOpen, activeDrawerEmployee, selectedIds]);

  // Click outside to close Export Dropdown
  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (exportDropdownRef.current && !exportDropdownRef.current.contains(event.target as Node)) {
        setIsExportDropdownOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  // --- CHART.JS EFFECT ---
  useEffect(() => {
    if (distChartInstance.current) {
      distChartInstance.current.destroy();
    }

    if (isAnalyticsOpen && distChartRef.current) {
      const ctx = distChartRef.current.getContext('2d');
      if (ctx) {
        distChartInstance.current = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Quản trị (Admin)', 'Quản lý (Manager)', 'Nhân viên (Staff)'],
            datasets: [{
              data: [
                statsData.by_role.admin,
                statsData.by_role.manager,
                statsData.by_role.staff
              ],
              backgroundColor: ['#4f46e5', '#8b5cf6', '#10b981'],
              borderWidth: 4,
              borderColor: document.documentElement.classList.contains('dark') ? '#0f172a' : '#ffffff'
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
              legend: {
                display: false
              }
            }
          }
        });
      }
    }

    return () => {
      if (distChartInstance.current) {
        distChartInstance.current.destroy();
      }
    };
  }, [isAnalyticsOpen, statsData]);

  // Cập nhật bộ lọc
  const handleFilterChange = (key: 'role' | 'status' | 'sort', value: string) => {
    startTransition(() => {
      if (key === 'role') setRoleId(value);
      if (key === 'status') setStatus(value);
      if (key === 'sort') setSort(value);
      setPage(1);
    });
  };

  // Reset toàn bộ bộ lọc
  const handleResetFilters = () => {
    setSearchQuery('');
    setDebouncedSearch('');
    setRoleId('');
    setStatus('');
    setSort('oldest');
    setPage(1);
  };

  // Kiểm tra có bộ lọc nào đang hoạt động không
  const hasActiveFilters = debouncedSearch !== '' || roleId !== '' || status !== '' || sort !== 'oldest';

  // --- BATCH SELECTION HANDLERS ---
  const handleSelectId = useCallback((id: number) => {
    setSelectedIds(prev => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  }, []);

  const handleSelectAll = useCallback(() => {
    const currentPageIds = employeesData.data
      .map((e: Employee) => e.user_id)
      .filter((id: number) => id !== auth_id);
    const allSelected = currentPageIds.every((id: number) => selectedIds.has(id));
    
    if (allSelected) {
      // Bỏ chọn tất cả trang hiện tại
      setSelectedIds(prev => {
        const next = new Set(prev);
        currentPageIds.forEach((id: number) => next.delete(id));
        return next;
      });
    } else {
      // Chọn tất cả trang hiện tại
      setSelectedIds(prev => {
        const next = new Set(prev);
        currentPageIds.forEach((id: number) => next.add(id));
        return next;
      });
    }
  }, [employeesData, selectedIds, auth_id]);

  // --- BATCH ACTION API ---
  const handleBatchAction = async (action: 'activate' | 'ban' | 'delete') => {
    const Swal = (window as any).Swal;
    const actionLabels: Record<string, string> = {
      activate: 'kích hoạt',
      ban: 'khóa',
      delete: 'xóa mềm'
    };

    const result = await Swal.fire({
      title: `${actionLabels[action].charAt(0).toUpperCase() + actionLabels[action].slice(1)} hàng loạt?`,
      text: `Bạn có chắc muốn ${actionLabels[action]} ${selectedIds.size} nhân viên đã chọn?`,
      icon: action === 'delete' ? 'warning' : 'question',
      showCancelButton: true,
      confirmButtonColor: action === 'activate' ? '#10b981' : '#e11d48',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Xác nhận',
      cancelButtonText: 'Hủy'
    });

    if (!result.isConfirmed) return;

    try {
      const response = await axios.post('/admin/employees/batch-action', {
        action,
        ids: Array.from(selectedIds),
        _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
      }, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

      if (response.data.success) {
        toast.success(response.data.message);
        setSelectedIds(new Set());
        mutate();
        try { channel.postMessage('REFRESH_EMPLOYEES'); } catch (e) {}
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || 'Không thể thực hiện thao tác hàng loạt.');
    }
  };

  // --- EXPORT CSV ENGINE ---
  const handleExportCSV = () => {
    if (!employeesData.data || employeesData.data.length === 0) {
      toast.error('Không có dữ liệu nhân viên để xuất!');
      return;
    }

    const headers = ['Họ tên', 'Email', 'Số điện thoại', 'Vai trò', 'Trạng thái', 'Ngày tham gia'];
    const rows = employeesData.data.map((emp: Employee) => [
      emp.full_name,
      emp.email,
      emp.phone || '',
      emp.role?.name || 'N/A',
      emp.status === 'Active' ? 'Đang làm việc' : 'Đã khóa',
      emp.created_at || ''
    ]);

    // Build CSV string with UTF-8 BOM so Excel opens it perfectly with Vietnamese characters
    const csvContent = '\uFEFF' 
      + [headers.join(',')].concat(rows.map(row => row.map(val => `"${val.replace(/"/g, '""')}"`).join(','))).join('\n');
      
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `danh_sach_nhan_vien_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    toast.success('📊 Đã xuất file CSV thành công!');
  };

  // --- API MUTATION: CHUYỂN TRẠNG THÁI NHANH ---
  const handleToggleStatus = async (employee: Employee) => {
    if (employee.user_id === auth_id) {
      toast.error('❌ Bạn không thể tự khóa tài khoản của chính mình!');
      return;
    }

    const Swal = (window as any).Swal;
    const actionText = employee.status === 'Active' ? 'Khóa' : 'Kích hoạt';
    const result = await Swal.fire({
      title: `${actionText} tài khoản?`,
      text: `Bạn có chắc muốn ${actionText.toLowerCase()} tài khoản của nhân viên "${employee.full_name}"?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: employee.status === 'Active' ? '#e11d48' : '#10b981',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Đồng ý',
      cancelButtonText: 'Hủy'
    });

    if (!result.isConfirmed) return;

    // 1. Sao lưu cache cũ đề phòng rollback
    const previousData = { ...data };
    
    // Đảo ngược trạng thái
    const newStatus = employee.status === 'Active' ? 'Banned' : 'Active';
    
    // 2. CẬP NHẬT UI TỨC THÌ (OPTIMISTIC UPDATE)
    const updatedEmployees = {
      ...employeesData,
      data: employeesData.data.map((emp: Employee) => 
        emp.user_id === employee.user_id ? { ...emp, status: newStatus, version: (emp.version || 1) + 1 } : emp
      )
    };
    
    const updatedStats = {
      ...statsData,
      active: newStatus === 'Active' ? statsData.active + 1 : statsData.active - 1,
      banned: newStatus === 'Banned' ? statsData.banned + 1 : statsData.banned - 1
    };
    
    mutate(
      { ...data, employees: updatedEmployees, stats: updatedStats },
      false
    );
    
    try {
      const response = await axios.patch(`/admin/employees/${employee.user_id}/toggle-status`, {
        _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
      }, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      
      if (response.data.success) {
        toast.success(response.data.message);
        mutate(); // Đồng bộ lại chuẩn từ Server
        try {
          channel.postMessage('REFRESH_EMPLOYEES');
        } catch (e) {}
      } else {
        throw new Error(response.data.message);
      }
    } catch (err: any) {
      // Rollback
      mutate(previousData, false);
      toast.error(err.response?.data?.message || err.message || 'Không thể thay đổi trạng thái.');
    }
  };

  // --- OPTIMISTIC MUTATION: XÓA MỀM NHÂN VIÊN ---
  const handleDelete = async (employee: Employee) => {
    const Swal = (window as any).Swal;

    const result = await Swal.fire({
      title: 'Xóa nhân viên?',
      text: `Bạn có chắc chắn muốn xóa mềm nhân viên "${employee.full_name}"? Tài khoản sẽ bị vô hiệu hóa nhưng lịch sử giao dịch POS vẫn được bảo toàn.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e11d48', // rose-600
      cancelButtonColor: '#64748b', // slate-500
      confirmButtonText: 'Xác nhận xóa',
      cancelButtonText: 'Hủy bỏ',
      customClass: {
        popup: 'rounded-[2rem] border border-slate-100 dark:border-slate-800 dark:bg-slate-900',
        confirmButton: 'rounded-xl font-bold uppercase text-xs tracking-widest px-6 py-3',
        cancelButton: 'rounded-xl font-bold uppercase text-xs tracking-widest px-6 py-3'
      }
    });

    if (result.isConfirmed) {
      const previousData = { ...data };

      const updatedEmployeesData = {
        ...employeesData,
        total: employeesData.total - 1,
        data: employeesData.data.filter((emp: Employee) => emp.user_id !== employee.user_id)
      };

      const updatedStatsData = {
        ...statsData,
        total: statsData.total - 1,
        active: employee.status === 'Active' ? statsData.active - 1 : statsData.active,
        banned: employee.status === 'Banned' ? statsData.banned - 1 : statsData.banned,
      };

      mutate(
        { ...data, employees: updatedEmployeesData, stats: updatedStatsData },
        false
      );

      try {
        const response = await axios.post(`/admin/employees/${employee.user_id}`, {
          _method: 'DELETE',
          _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
        }, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

        if (response.data.success) {
          toast.success(response.data.message || 'Đã xóa mềm nhân viên thành công.');
          mutate();
          try {
            channel.postMessage('REFRESH_EMPLOYEES');
          } catch (e) {}
        } else {
          throw new Error(response.data.message);
        }
      } catch (err: any) {
        mutate(previousData, false);

        if (err.response?.status === 403) {
          toast.error(`❌ Lỗi bảo mật: ${err.response.data.message || 'Bạn không thể tự xóa chính mình!'}`);
        } else {
          toast.error(err.response?.data?.message || err.message || 'Không thể thực thi lệnh xóa mềm.');
        }
      }
    }
  };

  // --- FILTER CHIP DATA ---
  const filterChips: { key: string; label: string; onRemove: () => void }[] = [];
  if (debouncedSearch) {
    filterChips.push({ key: 'search', label: `Tìm kiếm: "${debouncedSearch}"`, onRemove: () => { setSearchQuery(''); setDebouncedSearch(''); } });
  }
  if (roleId) {
    const roleName = roleId === 'senior' ? 'Quản lý cấp cao' : roles.find(r => r.role_id.toString() === roleId)?.name || roleId;
    filterChips.push({ key: 'role', label: `Vai trò: ${roleName}`, onRemove: () => setRoleId('') });
  }
  if (status) {
    filterChips.push({ key: 'status', label: `Trạng thái: ${status === 'Active' ? 'Đang làm việc' : 'Tạm dừng'}`, onRemove: () => setStatus('') });
  }
  if (sort !== 'oldest') {
    const sortLabels: Record<string, string> = { newest: 'Mới nhất', name_az: 'Tên A-Z', name_za: 'Tên Z-A' };
    filterChips.push({ key: 'sort', label: `Sắp xếp: ${sortLabels[sort] || sort}`, onRemove: () => setSort('oldest') });
  }

  return (
    <div className="min-h-screen bg-[#F8FAFC] dark:bg-slate-950 p-4 md:p-8 font-sans relative overflow-hidden">
      
      {/* Toast Notification Mount Point */}
      <Toaster position="top-right" reverseOrder={false} />

      {/* Decorative Ornaments */}
      <div className="absolute top-0 left-1/4 w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none" />
      <div className="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none" />

      <div className="max-w-[1600px] mx-auto relative z-10 space-y-8 animate-in fade-in duration-500">
        
        {/* Header Section */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <h1 className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter mb-2">Đội ngũ Nhân viên</h1>
            <div className="flex items-center gap-3">
              <span className="flex h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
              <p className="text-slate-500 dark:text-slate-400 font-bold uppercase text-xs tracking-widest">
                Phân hệ nhân sự đạt chuẩn RESTful API & Security
              </p>
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-3">
            <button
              onClick={() => setIsAnalyticsOpen(!isAnalyticsOpen)}
              className={`h-12 px-5 gap-2 font-bold rounded-2xl flex items-center transition-all duration-300 border-2 text-xs uppercase tracking-wider ${
                isAnalyticsOpen
                  ? 'bg-blue-50 border-blue-200 text-blue-600 dark:bg-blue-900/20 dark:border-blue-800'
                  : 'bg-white border-slate-100 hover:border-slate-200 text-slate-700 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-200'
              }`}
            >
              <Activity size={16} /> {isAnalyticsOpen ? 'Ẩn thống kê' : 'Xem thống kê'}
            </button>
            
            {/* Premium Export Dropdown */}
            <div className="relative" ref={exportDropdownRef}>
              <button
                onClick={() => setIsExportDropdownOpen(!isExportDropdownOpen)}
                className="h-12 px-5 gap-2 font-bold bg-white dark:bg-slate-900 border-2 border-slate-100 dark:border-slate-800 hover:border-indigo-300 text-slate-700 dark:text-slate-200 rounded-2xl flex items-center transition-all duration-300 active:scale-95 text-xs uppercase tracking-wider"
              >
                <FileSpreadsheet size={16} className="text-indigo-500" /> 
                <span>Xuất tài liệu</span>
                <ChevronDown size={14} className={`transition-transform duration-200 ${isExportDropdownOpen ? 'rotate-180' : ''}`} />
              </button>

              {isExportDropdownOpen && (
                <div className="absolute right-0 mt-2 w-56 rounded-2xl border border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl z-50 py-2 animate-in fade-in slide-in-from-top-2 duration-200">
                  <div className="px-4 py-2 border-b border-slate-50 dark:border-slate-800/50">
                    <span className="text-[10px] font-black text-slate-400 uppercase tracking-wider">Chọn định dạng xuất</span>
                  </div>
                  
                  <button
                    onClick={() => {
                      setIsExportDropdownOpen(false);
                      const params = new URLSearchParams({
                        search: debouncedSearch,
                        role_id: roleId,
                        status: status,
                        sort: sort
                      });
                      window.location.href = `/admin/employees/export/excel?${params.toString()}`;
                    }}
                    className="w-full text-left px-4 py-3 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-200 transition-colors"
                  >
                    <FileSpreadsheet size={16} className="text-emerald-500 shrink-0" />
                    <div>
                      <div className="text-xs font-black uppercase tracking-wider">Xuất Excel (.xlsx)</div>
                      <div className="text-[9px] text-slate-400 mt-0.5">Bảng tính định dạng chuyên nghiệp</div>
                    </div>
                  </button>

                  <button
                    onClick={() => {
                      setIsExportDropdownOpen(false);
                      const params = new URLSearchParams({
                        search: debouncedSearch,
                        role_id: roleId,
                        status: status,
                        sort: sort
                      });
                      window.open(`/admin/employees/export/pdf?${params.toString()}`, '_blank');
                    }}
                    className="w-full text-left px-4 py-3 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-200 transition-colors"
                  >
                    <FileText size={16} className="text-rose-500 shrink-0" />
                    <div>
                      <div className="text-xs font-black uppercase tracking-wider">Xuất PDF (.pdf)</div>
                      <div className="text-[9px] text-slate-400 mt-0.5">Báo cáo nhân sự chuẩn in ấn</div>
                    </div>
                  </button>

                  <button
                    onClick={() => {
                      setIsExportDropdownOpen(false);
                      handleExportCSV();
                    }}
                    className="w-full text-left px-4 py-3 flex items-center gap-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-200 transition-colors border-t border-slate-50 dark:border-slate-800/50"
                  >
                    <FileSpreadsheet size={16} className="text-slate-400 shrink-0" />
                    <div>
                      <div className="text-xs font-black uppercase tracking-wider">Xuất nhanh CSV</div>
                      <div className="text-[9px] text-slate-400 mt-0.5">Tải tệp văn bản thô tức thì</div>
                    </div>
                  </button>
                </div>
              )}
            </div>

            <button
              onClick={() => { setSelectedEmployee(null); setIsModalOpen(true); }}
              className="h-12 px-6 gap-2 font-bold bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-2xl shadow-lg shadow-blue-500/20 flex items-center transition-all duration-300 hover:scale-[1.02] text-xs uppercase tracking-wider"
            >
              <Plus size={16} /> Thêm nhân viên
            </button>
          </div>
        </div>

        {/* Real-time statistics counters (Click to Filter) */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            onClick={() => { setRoleId(''); setStatus(''); setPage(1); }}
            className={`p-6 rounded-[2rem] border backdrop-blur-md shadow-xl flex items-center gap-5 cursor-pointer hover:scale-[1.02] transition-all duration-300 ${
              status === '' && roleId === ''
                ? 'bg-blue-500/10 border-blue-500 dark:bg-blue-500/20'
                : 'bg-white/80 border-slate-100 hover:border-blue-300 dark:bg-slate-900/80 dark:border-slate-800 dark:shadow-none'
            }`}
          >
            <div className="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-2xl shadow-inner shrink-0">
              <Users size={28} />
            </div>
            <div>
              <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tổng nhân sự</div>
              <div className="text-2xl font-black text-slate-950 dark:text-white leading-none mt-1">{statsData.total}</div>
            </div>
          </div>

          <div 
            onClick={() => { setRoleId(''); setStatus('Active'); setPage(1); }}
            className={`p-6 rounded-[2rem] border backdrop-blur-md shadow-xl flex items-center gap-5 cursor-pointer hover:scale-[1.02] transition-all duration-300 ${
              status === 'Active' && roleId === ''
                ? 'bg-emerald-500/10 border-emerald-500 dark:bg-emerald-500/20'
                : 'bg-white/80 border-slate-100 hover:border-emerald-300 dark:bg-slate-900/80 dark:border-slate-800 dark:shadow-none'
            }`}
          >
            <div className="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center text-2xl shadow-inner shrink-0">
              <ShieldCheck size={28} />
            </div>
            <div>
              <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đang làm việc</div>
              <div className="text-2xl font-black text-slate-950 dark:text-white leading-none mt-1">{statsData.active}</div>
            </div>
          </div>

          <div 
            onClick={() => { setRoleId(''); setStatus('Banned'); setPage(1); }}
            className={`p-6 rounded-[2rem] border backdrop-blur-md shadow-xl flex items-center gap-5 cursor-pointer hover:scale-[1.02] transition-all duration-300 ${
              status === 'Banned' && roleId === ''
                ? 'bg-rose-500/10 border-rose-500 dark:bg-rose-500/20'
                : 'bg-white/80 border-slate-100 hover:border-rose-300 dark:bg-slate-900/80 dark:border-slate-800 dark:shadow-none'
            }`}
          >
            <div className="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 flex items-center justify-center text-2xl shadow-inner shrink-0">
              <ShieldAlert size={28} />
            </div>
            <div>
              <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tạm dừng vận hành</div>
              <div className="text-2xl font-black text-slate-950 dark:text-white leading-none mt-1">{statsData.banned}</div>
            </div>
          </div>

          <div 
            onClick={() => { setRoleId('senior'); setStatus(''); setPage(1); }}
            className={`p-6 rounded-[2rem] border backdrop-blur-md shadow-xl flex items-center gap-5 cursor-pointer hover:scale-[1.02] transition-all duration-300 ${
              roleId === 'senior'
                ? 'bg-indigo-500/10 border-indigo-500 dark:bg-indigo-500/20'
                : 'bg-white/80 border-slate-100 hover:border-indigo-300 dark:bg-slate-900/80 dark:border-slate-800 dark:shadow-none'
            }`}
          >
            <div className="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center text-2xl shadow-inner shrink-0">
              <Shield size={28} />
            </div>
            <div>
              <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Quản lý cấp cao</div>
              <div className="text-2xl font-black text-slate-950 dark:text-white leading-none mt-1">{statsData.by_role.admin + statsData.by_role.manager}</div>
            </div>
          </div>
        </div>

        {/* Visual Analytics Panel (Doughnut Chart) */}
        {isAnalyticsOpen && (
          <div className="bg-white/85 dark:bg-slate-900/85 backdrop-blur-md p-8 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl animate-in slide-in-from-top duration-300 grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="lg:col-span-1 h-[240px] flex items-center justify-center relative">
              <canvas ref={distChartRef} className="max-h-full"></canvas>
            </div>
            <div className="lg:col-span-2 flex flex-col justify-center space-y-6">
              <h3 className="text-lg font-black text-slate-950 dark:text-white uppercase tracking-tight">Cơ cấu nhân sự hệ thống</h3>
              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="space-y-2">
                  <div className="flex justify-between items-center text-xs font-bold text-slate-500 uppercase">
                    <span>Admin (Quản trị)</span>
                    <span className="font-black text-indigo-600">{statsData.by_role.admin} ({((statsData.by_role.admin / (statsData.total || 1)) * 100).toFixed(0)}%)</span>
                  </div>
                  <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div className="h-full bg-indigo-600 rounded-full" style={{ width: `${(statsData.by_role.admin / (statsData.total || 1)) * 100}%` }} />
                  </div>
                </div>
                
                <div className="space-y-2">
                  <div className="flex justify-between items-center text-xs font-bold text-slate-500 uppercase">
                    <span>Quản lý (Manager)</span>
                    <span className="font-black text-purple-500">{statsData.by_role.manager} ({((statsData.by_role.manager / (statsData.total || 1)) * 100).toFixed(0)}%)</span>
                  </div>
                  <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div className="h-full bg-purple-500 rounded-full" style={{ width: `${(statsData.by_role.manager / (statsData.total || 1)) * 100}%` }} />
                  </div>
                </div>

                <div className="space-y-2">
                  <div className="flex justify-between items-center text-xs font-bold text-slate-500 uppercase">
                    <span>Nhân viên (Staff)</span>
                    <span className="font-black text-emerald-500">{statsData.by_role.staff} ({((statsData.by_role.staff / (statsData.total || 1)) * 100).toFixed(0)}%)</span>
                  </div>
                  <div className="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div className="h-full bg-emerald-500 rounded-full" style={{ width: `${(statsData.by_role.staff / (statsData.total || 1)) * 100}%` }} />
                  </div>
                </div>
              </div>
              <p className="text-xs italic text-slate-400 font-medium">Biểu đồ cơ cấu được tính tự động dựa trên tổng số {statsData.total} nhân viên thuộc biên chế của hệ thống.</p>
            </div>
          </div>
        )}

        {/* Dynamic Filters Bar */}
        <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/30 dark:shadow-none space-y-4">
          <div className="flex flex-wrap items-center gap-4">
            <div className="relative flex-1 min-w-[280px]">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
              <input
                ref={searchInputRef}
                type="text"
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                placeholder="Tìm kiếm nhân viên... (Ctrl+K)"
                className="w-full pl-12 pr-4 h-12 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl text-base font-bold transition-all text-slate-900 dark:text-white outline-none"
              />
            </div>

            <div className="flex flex-wrap items-center gap-3 w-full md:w-auto">
              {/* Vai trò */}
              <div className="flex items-center gap-2">
                <Filter size={14} className="text-slate-400" />
                <select
                  value={roleId}
                  onChange={(e) => handleFilterChange('role', e.target.value)}
                  className="h-12 px-4 bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-200 outline-none cursor-pointer"
                >
                  <option value="">Tất cả vai trò</option>
                  <option value="senior">Quản lý cấp cao</option>
                  {roles.map(r => (
                    <option key={r.role_id} value={r.role_id.toString()}>{r.name}</option>
                  ))}
                </select>
              </div>

              {/* Trạng thái */}
              <select
                value={status}
                onChange={(e) => handleFilterChange('status', e.target.value)}
                className="h-12 px-4 bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-200 outline-none cursor-pointer"
              >
                <option value="">Trạng thái</option>
                <option value="Active">Đang làm việc</option>
                <option value="Banned">Tạm dừng</option>
              </select>

              {/* Sắp xếp */}
              <select
                value={sort}
                onChange={(e) => handleFilterChange('sort', e.target.value)}
                className="h-12 px-4 bg-white dark:bg-slate-800 border-2 border-slate-100 dark:border-slate-700 rounded-2xl font-bold text-xs uppercase tracking-wider text-slate-700 dark:text-slate-200 outline-none cursor-pointer"
              >
                <option value="newest">Mới nhất</option>
                <option value="oldest">Cũ nhất</option>
                <option value="name_az">Tên A-Z</option>
                <option value="name_za">Tên Z-A</option>
              </select>

              {/* Nút Reset bộ lọc */}
              {hasActiveFilters && (
                <button
                  onClick={handleResetFilters}
                  className="h-12 px-4 gap-2 font-bold bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/20 text-slate-500 hover:text-rose-600 rounded-2xl flex items-center transition-all duration-300 text-xs uppercase tracking-wider active:scale-95"
                  title="Xóa tất cả bộ lọc"
                >
                  <RotateCcw size={14} /> Reset
                </button>
              )}
            </div>
          </div>

          {/* Active Filter Chips */}
          {filterChips.length > 0 && (
            <div className="flex flex-wrap items-center gap-2 pt-2">
              {filterChips.map(chip => (
                <span
                  key={chip.key}
                  className="inline-flex items-center gap-1.5 pl-3 pr-1.5 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 text-[10px] font-black uppercase tracking-wider border border-blue-200 dark:border-blue-800 animate-in fade-in zoom-in-95 duration-200"
                >
                  {chip.label}
                  <button
                    onClick={chip.onRemove}
                    className="w-5 h-5 rounded-lg hover:bg-blue-200 dark:hover:bg-blue-800 flex items-center justify-center transition-colors ml-0.5"
                  >
                    <X size={10} />
                  </button>
                </span>
              ))}
              {filterChips.length > 1 && (
                <button
                  onClick={handleResetFilters}
                  className="text-[10px] font-black text-slate-400 hover:text-rose-500 uppercase tracking-wider transition-colors px-2 py-1"
                >
                  Xóa tất cả
                </button>
              )}
            </div>
          )}
        </div>

        {/* Data Table with pulsing skeleton loaders */}
        <EmployeeTable
          employees={employeesData}
          isLoading={isLoading || isPending}
          selectedIds={selectedIds}
          onEdit={(emp) => { setSelectedEmployee(emp); setIsModalOpen(true); }}
          onDelete={handleDelete}
          onPageChange={setPage}
          onToggleStatus={handleToggleStatus}
          onOpenDrawer={(emp) => setActiveDrawerEmployee(emp)}
          onSelectId={handleSelectId}
          onSelectAll={handleSelectAll}
        />
      </div>

      {/* ====== BATCH ACTION FLOATING BAR ====== */}
      {selectedIds.size > 0 && (
        <div className="fixed bottom-6 left-1/2 -translate-x-1/2 z-[9998] animate-in slide-in-from-bottom duration-300">
          <div className="bg-slate-900/95 dark:bg-white/95 backdrop-blur-xl px-6 py-4 rounded-2xl shadow-2xl shadow-slate-900/30 dark:shadow-white/10 border border-slate-700 dark:border-slate-200 flex items-center gap-5">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black text-sm">
                {selectedIds.size}
              </div>
              <span className="text-white dark:text-slate-900 font-bold text-xs uppercase tracking-wider">đã chọn</span>
            </div>

            <div className="w-px h-8 bg-slate-700 dark:bg-slate-300" />

            <div className="flex items-center gap-2">
              <button
                onClick={() => handleBatchAction('activate')}
                className="h-9 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] uppercase tracking-wider flex items-center gap-1.5 transition-all active:scale-95"
              >
                <Zap size={12} /> Kích hoạt
              </button>
              <button
                onClick={() => handleBatchAction('ban')}
                className="h-9 px-4 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-[10px] uppercase tracking-wider flex items-center gap-1.5 transition-all active:scale-95"
              >
                <ShieldBan size={12} /> Khóa
              </button>
              <button
                onClick={() => handleBatchAction('delete')}
                className="h-9 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-[10px] uppercase tracking-wider flex items-center gap-1.5 transition-all active:scale-95"
              >
                <Trash2 size={12} /> Xóa mềm
              </button>
            </div>

            <button
              onClick={() => setSelectedIds(new Set())}
              className="w-8 h-8 rounded-xl hover:bg-slate-800 dark:hover:bg-slate-100 text-slate-400 dark:text-slate-500 flex items-center justify-center transition-colors"
              title="Bỏ chọn tất cả (Esc)"
            >
              <X size={16} />
            </button>
          </div>
        </div>
      )}

      {/* Shared add/edit modal form with React Hook Form & Zod resolution */}
      {isModalOpen && (
        <EmployeeModalForm
          isOpen={isModalOpen}
          employee={selectedEmployee}
          roles={roles}
          onClose={() => setIsModalOpen(false)}
          onSave={(savedEmployee, isEditMode) => {
            setIsModalOpen(false);
            mutate();
            try {
              channel.postMessage('REFRESH_EMPLOYEES');
            } catch (e) {}
          }}
        />
      )}

      {/* Profile Sidebar Drawer (Right Slide-out Drawer) */}
      {activeDrawerEmployee && (
        <div className="fixed inset-0 z-[9999] flex justify-end">
          {/* Backdrop mờ */}
          <div 
            className="absolute inset-0 bg-slate-950/40 backdrop-blur-sm animate-in fade-in duration-300"
            onClick={() => setActiveDrawerEmployee(null)}
          />
          
          {/* Drawer container */}
          <div className="relative w-full max-w-md h-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-l border-slate-100 dark:border-slate-800 shadow-2xl flex flex-col z-10 animate-in slide-in-from-right duration-350">
            
            {/* Drawer Header */}
            <div className="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
              <h4 className="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Hồ sơ chi tiết</h4>
              <button 
                onClick={() => setActiveDrawerEmployee(null)}
                className="w-10 h-10 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-400 hover:text-rose-500 transition-colors flex items-center justify-center dark:bg-slate-800"
              >
                <X size={20} />
              </button>
            </div>

            {/* Drawer Content */}
            <div className="flex-1 overflow-y-auto px-6 py-8 space-y-8">
              
              {/* Profile Card Look */}
              <div className="flex flex-col items-center text-center space-y-4">
                {/* Avatar with initial gradients */}
                <div className="relative">
                  <div className="w-24 h-24 rounded-[2rem] bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center text-white font-black text-3xl shadow-xl shadow-purple-500/20">
                    {activeDrawerEmployee.full_name.substring(0, 2).toUpperCase()}
                  </div>
                  {/* Online / Offline indicator */}
                  <div className={`absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-[3px] border-white dark:border-slate-900 flex items-center justify-center ${
                    activeDrawerEmployee.is_online ? 'bg-emerald-500' : 'bg-slate-400'
                  }`}>
                    {activeDrawerEmployee.is_online 
                      ? <Wifi size={10} className="text-white" /> 
                      : <WifiOff size={10} className="text-white" />
                    }
                  </div>
                </div>
                <div>
                  <h3 className="text-xl font-black text-slate-950 dark:text-white leading-tight">{activeDrawerEmployee.full_name}</h3>
                  <div className="flex items-center justify-center gap-2 mt-2">
                    <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-wider">
                      <Shield size={12} className="text-blue-500" /> {activeDrawerEmployee.role?.name || 'N/A'}
                    </span>
                    <span className="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 text-[10px] font-black uppercase tracking-wider">
                      <Hash size={10} /> EMP-{activeDrawerEmployee.user_id}
                    </span>
                  </div>
                </div>
              </div>

              {/* Detailed Fields Sheet */}
              <div className="space-y-6 bg-slate-50 dark:bg-slate-800/40 p-6 rounded-3xl border border-slate-100 dark:border-slate-800/80">
                <div className="space-y-1">
                  <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Địa chỉ Email</span>
                  <span className="text-sm font-black text-slate-800 dark:text-slate-200 select-all">{activeDrawerEmployee.email}</span>
                </div>

                <div className="space-y-1">
                  <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Số điện thoại</span>
                  <span className="text-sm font-black text-slate-800 dark:text-slate-200">{activeDrawerEmployee.phone || <span className="opacity-50 italic font-bold">Chưa bổ sung</span>}</span>
                </div>

                <div className="space-y-1">
                  <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Trạng thái vận hành</span>
                  <div className="flex items-center gap-2">
                    {activeDrawerEmployee.status === 'Active' ? (
                      <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 text-[10px] font-black uppercase tracking-wider">
                        <CheckCircle2 size={10} /> Đang hoạt động
                      </span>
                    ) : (
                      <span className="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-600 text-[10px] font-black uppercase tracking-wider">
                        <XCircle size={10} /> Đã bị khóa
                      </span>
                    )}
                    {activeDrawerEmployee.is_online ? (
                      <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-500 text-[9px] font-black uppercase tracking-wider">
                        <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" /> Online
                      </span>
                    ) : (
                      <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 text-[9px] font-black uppercase tracking-wider">
                        Offline
                      </span>
                    )}
                  </div>
                </div>

                <div className="space-y-1">
                  <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Ngày tham gia hệ thống</span>
                  <span className="text-sm font-black text-slate-800 dark:text-slate-200">{activeDrawerEmployee.created_at || 'Đang đồng bộ...'}</span>
                </div>

                <div className="space-y-1">
                  <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Đăng nhập gần nhất</span>
                  <span className="text-sm font-black text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                    <Clock size={12} className="text-blue-500" />
                    {activeDrawerEmployee.last_login_at || <span className="opacity-50 italic font-bold">Chưa đăng nhập</span>}
                  </span>
                </div>

                <div className="space-y-1">
                  <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Phiên bản ghi (Version)</span>
                  <span className="text-sm font-black text-slate-800 dark:text-slate-200">v{activeDrawerEmployee.version || 1}</span>
                </div>
              </div>

              {/* Action Panel in Drawer */}
              <div className="space-y-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest ml-1 block">Tác vụ nhanh</span>
                <div className="grid grid-cols-2 gap-3">
                  <button
                    onClick={() => {
                      setActiveDrawerEmployee(null);
                      setSelectedEmployee(activeDrawerEmployee);
                      setIsModalOpen(true);
                    }}
                    className="h-11 rounded-xl bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white transition-all font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <Edit size={14} /> Chỉnh sửa
                  </button>
                  <button
                    onClick={() => {
                      setActiveDrawerEmployee(null);
                      handleDelete(activeDrawerEmployee);
                    }}
                    className="h-11 rounded-xl bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white transition-all font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <Trash2 size={14} /> Xóa mềm
                  </button>
                </div>
                
                <button
                  onClick={() => {
                    handleToggleStatus(activeDrawerEmployee);
                    setActiveDrawerEmployee(prev => prev ? {
                      ...prev,
                      status: prev.status === 'Active' ? 'Banned' : 'Active',
                      version: (prev.version || 1) + 1
                    } : null);
                  }}
                  className={`w-full h-11 rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer transition-all border ${
                    activeDrawerEmployee.status === 'Active'
                      ? 'border-rose-200 text-rose-600 hover:bg-rose-600 hover:text-white hover:border-rose-600'
                      : 'border-emerald-200 text-emerald-600 hover:bg-emerald-600 hover:text-white hover:border-emerald-600'
                  }`}
                >
                  {activeDrawerEmployee.status === 'Active' ? 'Khóa tài khoản nhân sự' : 'Kích hoạt tài khoản nhân sự'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
