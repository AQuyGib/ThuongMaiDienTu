import React, { useState, useEffect, useTransition } from 'react';
import useSWR from 'swr';
import axios from 'axios';
import toast, { Toaster } from 'react-hot-toast';
import { Users, ShieldCheck, ShieldAlert, Shield, Plus, Search, Filter } from 'lucide-react';
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
  };
  roles: Role[];
  stats: Stats;
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

export default function EmployeeManager({ employees: initialEmployees, roles, stats: initialStats }: EmployeeManagerProps) {
  useEffect(() => {
    registerSessionInterceptor();
  }, []);

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

  const [isPending, startTransition] = useTransition();

  // --- STATE BỘ LỌC CLIENT ---
  const [searchQuery, setSearchQuery] = useState('');
  const [debouncedSearch, setDebouncedSearch] = useState('');
  const [roleId, setRoleId] = useState('');
  const [status, setStatus] = useState('');
  const [sort, setSort] = useState('newest');
  const [page, setPage] = useState(1);

  // Modal states
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedEmployee, setSelectedEmployee] = useState<Employee | null>(null);

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

  const employeesData = data?.employees || initialEmployees;
  const statsData = data?.stats || initialStats;

  // Cập nhật bộ lọc
  const handleFilterChange = (key: 'role' | 'status' | 'sort', value: string) => {
    startTransition(() => {
      if (key === 'role') setRoleId(value);
      if (key === 'status') setStatus(value);
      if (key === 'sort') setSort(value);
      setPage(1);
    });
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
      // 1. Sao lưu cache cũ đề phòng rollback
      const previousData = { ...data };

      // 2. CẬP NHẬT UI TỨC THÌ (OPTIMISTIC MUTATION)
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
        false // set false để tránh SWR gọi lại API lập tức làm gián đoạn
      );

      try {
        const response = await axios.post(`/admin/employees/${employee.user_id}`, {
          _method: 'DELETE',
          _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
        }, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

        if (response.data.success) {
          toast.success(response.data.message || 'Đã xóa mềm nhân viên thành công.');
          // Gọi revalidate đồng bộ chuẩn từ Server
          mutate();
          // Phát sóng đồng bộ tới các tab khác
          try {
            channel.postMessage('REFRESH_EMPLOYEES');
          } catch (e) {}
        } else {
          throw new Error(response.data.message);
        }
      } catch (err: any) {
        // 3. HOÀN TÁC (ROLLBACK) STATE UI NẾU XẢY RA LỖI (Ví dụ: HTTP 403)
        mutate(previousData, false);

        if (err.response?.status === 403) {
          toast.error(`❌ Lỗi bảo mật: ${err.response.data.message || 'Bạn không thể tự xóa chính mình!'}`);
        } else {
          toast.error(err.response?.data?.message || err.message || 'Không thể thực thi lệnh xóa mềm.');
        }
      }
    }
  };

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

          <button
            onClick={() => { setSelectedEmployee(null); setIsModalOpen(true); }}
            className="h-12 px-6 gap-2 font-bold bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white rounded-2xl shadow-lg shadow-blue-500/20 flex items-center transition-all duration-300 hover:scale-[1.02]"
          >
            <Plus size={18} /> Thêm nhân viên
          </button>
        </div>

        {/* Real-time statistics counters */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none flex items-center gap-5 hover:scale-[1.02] transition-transform duration-300">
            <div className="w-14 h-14 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-2xl shadow-inner">
              <Users size={28} />
            </div>
            <div>
              <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tổng nhân sự</div>
              <div className="text-2xl font-black text-slate-950 dark:text-white">{statsData.total}</div>
            </div>
          </div>

          <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none flex items-center gap-5 hover:scale-[1.02] transition-transform duration-300">
            <div className="w-14 h-14 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 flex items-center justify-center text-2xl shadow-inner">
              <ShieldCheck size={28} />
            </div>
            <div>
              <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đang làm việc</div>
              <div className="text-2xl font-black text-slate-950 dark:text-white">{statsData.active}</div>
            </div>
          </div>

          <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none flex items-center gap-5 hover:scale-[1.02] transition-transform duration-300">
            <div className="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 flex items-center justify-center text-2xl shadow-inner">
              <ShieldAlert size={28} />
            </div>
            <div>
              <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tạm dừng vận hành</div>
              <div className="text-2xl font-black text-slate-950 dark:text-white">{statsData.banned}</div>
            </div>
          </div>

          <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/40 dark:shadow-none flex items-center gap-5 hover:scale-[1.02] transition-transform duration-300">
            <div className="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 flex items-center justify-center text-2xl shadow-inner">
              <Shield size={28} />
            </div>
            <div>
              <div className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Quản lý cấp cao</div>
              <div className="text-2xl font-black text-slate-950 dark:text-white">{statsData.by_role.admin + statsData.by_role.manager}</div>
            </div>
          </div>
        </div>

        {/* Dynamic Filters Bar */}
        <div className="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/30 dark:shadow-none flex flex-wrap items-center gap-4">
          <div className="relative flex-1 min-w-[280px]">
            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Tìm kiếm nhân viên theo Họ tên, Email, Số điện thoại..."
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
          </div>
        </div>

        {/* Data Table with pulsing skeleton loaders */}
        <EmployeeTable
          employees={employeesData}
          isLoading={isLoading || isPending}
          onEdit={(emp) => { setSelectedEmployee(emp); setIsModalOpen(true); }}
          onDelete={handleDelete}
          onPageChange={setPage}
        />
      </div>

      {/* Shared add/edit modal form with React Hook Form & Zod resolution */}
      {isModalOpen && (
        <EmployeeModalForm
          isOpen={isModalOpen}
          employee={selectedEmployee}
          roles={roles}
          onClose={() => setIsModalOpen(false)}
          onSave={() => {
            setIsModalOpen(false);
            // Kích hoạt revalidate để đồng bộ hóa chuẩn
            mutate();
            // Phát sóng đồng bộ tới các tab khác
            try {
              channel.postMessage('REFRESH_EMPLOYEES');
            } catch (e) {}
          }}
        />
      )}
    </div>
  );
}
