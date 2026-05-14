import React, { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';
import { VercelTabs } from './ui/vercel-tabs';
import {
  Users, ShieldCheck, ShieldAlert, Trophy,
  Search, Filter, Download, Plus,
  Edit, Trash2, Shield,
  Clock, CheckCircle2, XCircle, X, Loader2,
  ChevronDown, Mail, Phone, Calendar, Check,
  Eye, EyeOff
} from 'lucide-react';
import { Button } from './ui/button';

// --- CUSTOM SELECT COMPONENT (PREMIUM LOOK) ---
interface SelectOption {
  value: string;
  label: string;
  icon?: React.ReactNode;
}

function CustomSelect({ options, value, onChange, placeholder, icon: LeftIcon }: {
  options: SelectOption[],
  value: string,
  onChange: (val: string) => void,
  placeholder: string,
  icon?: React.ReactNode
}) {
  const [isOpen, setIsOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement>(null);
  const selectedOption = options.find(opt => opt.value === value);

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
        setIsOpen(false);
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  return (
    <div className="relative" ref={containerRef}>
      <button
        type="button"
        onClick={() => setIsOpen(!isOpen)}
        className={`flex items-center justify-between h-12 px-4 gap-3 bg-slate-50 dark:bg-slate-800 border-2 ${isOpen ? 'border-blue-500 bg-white dark:bg-slate-900' : 'border-transparent'} rounded-2xl transition-all duration-300 min-w-[160px] group`}
      >
        <div className="flex items-center gap-2.5">
          <div className={`${isOpen ? 'text-blue-500' : 'text-slate-400'} transition-colors`}>
            {LeftIcon}
          </div>
          <span className="text-sm font-bold text-slate-700 dark:text-slate-200 uppercase tracking-wider">
            {selectedOption ? selectedOption.label : placeholder}
          </span>
        </div>
        <ChevronDown className={`text-slate-400 transition-transform duration-300 ${isOpen ? 'rotate-180 text-blue-500' : ''}`} size={16} />
      </button>

      {isOpen && (
        <div className="absolute top-full left-0 mt-2 w-full min-w-[200px] bg-white/90 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-100 dark:border-slate-800 rounded-[1.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.1)] dark:shadow-none z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
          <div className="p-2 max-h-[300px] overflow-y-auto custom-scrollbar">
            {options.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => {
                  onChange(option.value);
                  setIsOpen(false);
                }}
                className={`flex items-center justify-between w-full px-4 py-3 rounded-xl text-left transition-all group/opt ${value === option.value
                  ? 'bg-blue-600 text-white'
                  : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'
                  }`}
              >
                <div className="flex items-center gap-3">
                  {option.icon && <div className={`${value === option.value ? 'text-white' : 'text-slate-400 group-hover/opt:text-blue-500'} transition-colors`}>{option.icon}</div>}
                  <span className="text-sm font-bold uppercase tracking-widest">{option.label}</span>
                </div>
                {value === option.value && <Check size={14} className="text-white" />}
              </button>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}

// --- MAIN COMPONENTS ---

interface User {
  user_id: number;
  full_name: string;
  email: string;
  role: {
    name: string;
    role_id: number;
  };
  status: 'Active' | 'Banned' | 'Inactive';
  member_tier: 'Vang' | 'Bac' | 'Dong';
  created_at: string;
  phone_number?: string;
  version?: number;
}

interface Role {
  role_id: number;
  name: string;
  description: string;
  color: string;
  permissions?: Record<string, boolean>;
}

interface Stats {
  total: number;
  active: number;
  banned: number;
  tiers: {
    Vang: number;
    Bac: number;
    Dong: number;
  };
}

interface UserManagementProps {
  users: {
    data: User[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
  };
  roles: Role[];
  stats: Stats;
}

export default function UserManagement({ users: initialUsers, roles, stats: initialStats }: UserManagementProps) {
  const [activeTab, setActiveTab] = useState('users');
  const [users, setUsers] = useState(initialUsers);
  const [stats, setStats] = useState(initialStats);
  const [loading, setLoading] = useState(false);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);

  // Role Management State
  const [isRoleModalOpen, setIsRoleModalOpen] = useState(false);
  const [selectedRole, setSelectedRole] = useState<Role | null>(null);

  const [filters, setFilters] = useState({
    search: new URLSearchParams(window.location.search).get('search') || '',
    role_id: new URLSearchParams(window.location.search).get('role_id') || '',
    status: new URLSearchParams(window.location.search).get('status') || '',
    sort: new URLSearchParams(window.location.search).get('sort') || 'newest',
    page: new URLSearchParams(window.location.search).get('page') || '1',
  });

  const fetchData = useCallback(async (newFilters = filters) => {
    setLoading(true);
    try {
      const response = await axios.get('/admin/permissions', {
        params: newFilters,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      setUsers(response.data.users);
      setStats(response.data.stats);

      const url = new URL(window.location.href);
      Object.entries(newFilters).forEach(([key, value]) => {
        if (value) url.searchParams.set(key, value as string);
        else url.searchParams.delete(key);
      });
      window.history.pushState({}, '', url.toString());
    } catch (error) {
      console.error('Lỗi khi tải dữ liệu:', error);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    const timer = setTimeout(() => {
      if (filters.search !== new URLSearchParams(window.location.search).get('search')) {
        fetchData();
      }
    }, 500);
    return () => clearTimeout(timer);
  }, [filters.search, fetchData]);

  const handleFilterChange = (key: string, value: string) => {
    const newFilters = { ...filters, [key]: value, page: '1' };
    setFilters(newFilters);
    fetchData(newFilters);
  };

  return (
    <div className="min-h-screen bg-[#F8FAFC] dark:bg-slate-950 p-4 md:p-8 lg:p-12 font-sans selection:bg-blue-500 selection:text-white relative overflow-hidden">
      <div className="absolute top-0 left-1/4 w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] pointer-events-none" />
      <div className="absolute bottom-0 right-1/4 w-[500px] h-[500px] bg-indigo-500/5 rounded-full blur-[120px] pointer-events-none" />

      <div className="max-w-[1600px] mx-auto relative z-10 space-y-8 animate-in fade-in duration-500">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <h1 className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter mb-2">Hệ thống Quyền hạn</h1>
            <div className="flex items-center gap-3">
              <span className="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
              <p className="text-slate-500 dark:text-slate-400 font-bold uppercase text-xs tracking-widest">Hệ thống quản trị thời gian thực</p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <Button variant="outline" className="h-12 px-6 gap-2 font-bold border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all rounded-2xl" onClick={() => window.location.href = '?export=csv'}>
              <Download size={18} /> Xuất dữ liệu
            </Button>
            <Button onClick={() => { setSelectedUser(null); setIsModalOpen(true); }} className="h-12 px-6 gap-2 font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-2xl shadow-xl shadow-blue-500/20">
              <Plus size={18} /> Thêm tài khoản
            </Button>
          </div>
        </div>

        <VercelTabs
          tabs={[
            {
              label: 'Danh sách Người dùng',
              value: 'users',
              content: (
                <UserDashboard
                  users={users}
                  stats={stats}
                  roles={roles}
                  loading={loading}
                  filters={filters}
                  onFilterChange={handleFilterChange}
                  onPageChange={(page: number) => {
                    const newFilters = { ...filters, page: page.toString() };
                    setFilters(newFilters);
                    fetchData(newFilters);
                  }}
                  onEdit={(user: User) => { setSelectedUser(user); setIsModalOpen(true); }}
                  onRefresh={fetchData}
                />
              )
            },
            {
              label: 'Cấu hình Vai trò',
              value: 'roles',
              content: <RolesDashboard roles={roles} onAdd={() => { setSelectedRole(null); setIsRoleModalOpen(true); }} onEdit={(role: Role) => { setSelectedRole(role); setIsRoleModalOpen(true); }} />
            }
          ]}
          value={activeTab}
          onChange={setActiveTab}
        />

        {isModalOpen && (
          <UserModal
            user={selectedUser}
            roles={roles}
            onClose={() => setIsModalOpen(false)}
            onSuccess={() => {
              setIsModalOpen(false);
              fetchData();
            }}
          />
        )}

        {isRoleModalOpen && (
          <RoleModal
            role={selectedRole}
            onClose={() => setIsRoleModalOpen(false)}
            onSuccess={() => {
              setIsRoleModalOpen(false);
              window.location.reload();
            }}
          />
        )}
      </div>
    </div>
  );
}


function UserDashboard({ users, stats, roles, loading, filters, onFilterChange, onPageChange, onEdit, onRefresh }: any) {
  const handleDelete = async (user: User) => {
    const Swal = (window as any).Swal;

    const result = await Swal.fire({
      title: 'Xác nhận xóa?',
      text: `Bạn có chắc chắn muốn xóa tài khoản "${user.full_name}"? Hành động này không thể hoàn tác!`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e11d48', // rose-600
      cancelButtonColor: '#64748b', // slate-500
      confirmButtonText: 'Đồng ý xóa',
      cancelButtonText: 'Hủy bỏ',
      customClass: {
        popup: 'rounded-[2rem]',
        confirmButton: 'rounded-xl font-bold uppercase text-xs tracking-widest px-6 py-3',
        cancelButton: 'rounded-xl font-bold uppercase text-xs tracking-widest px-6 py-3'
      }
    });

    if (result.isConfirmed) {
      try {
        await axios.post(`/admin/permissions/${user.user_id}`, {
          _method: 'DELETE',
          _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
        }, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });

        onRefresh();

        Swal.fire({
          title: 'Đã xóa!',
          text: 'Tài khoản đã được gỡ khỏi hệ thống.',
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          customClass: { popup: 'rounded-[2rem]' }
        });
      } catch (error) {
        Swal.fire({
          title: 'Lỗi!',
          text: 'Không thể xóa người dùng này.',
          icon: 'error',
          customClass: { popup: 'rounded-[2rem]' }
        });
      }
    }
  };

  const roleOptions = [
    { value: '', label: 'Tất cả vai trò' },
    ...roles.map((r: any) => ({ value: r.role_id.toString(), label: r.name, icon: <Shield size={14} /> }))
  ];

  const statusOptions = [
    { value: '', label: 'Trạng thái' },
    { value: 'Active', label: 'Hoạt động', icon: <CheckCircle2 size={14} className="text-emerald-500" /> },
    { value: 'Banned', label: 'Bị khóa', icon: <XCircle size={14} className="text-rose-500" /> }
  ];

  const sortOptions = [
    { value: 'newest', label: 'Mới nhất', icon: <Calendar size={14} /> },
    { value: 'oldest', label: 'Cũ nhất', icon: <Calendar size={14} /> },
    { value: 'name_az', label: 'Tên A-Z', icon: <Users size={14} /> },
    { value: 'name_za', label: 'Tên Z-A', icon: <Users size={14} /> }
  ];

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard icon={<Users className="text-blue-600" />} label="Tổng người dùng" value={stats.total} color="blue" description="Tất cả tài khoản" />
        <StatCard icon={<ShieldCheck className="text-emerald-600" />} label="Đang hoạt động" value={stats.active} color="emerald" description="User đã xác minh" />
        <StatCard icon={<ShieldAlert className="text-rose-600" />} label="Đã bị khóa" value={stats.banned} color="rose" description="Vi phạm chính sách" />
        <StatCard icon={<Trophy className="text-amber-500" />} label="Thành viên VIP" value={stats.tiers.Vang} color="amber" description="Hạng thành viên Vàng" />
      </div>

      {/* Filters Bar */}
      <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none flex flex-wrap items-center gap-4">
        <div className="relative flex-1 min-w-[280px]">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
          <input
            type="text"
            value={filters.search}
            onChange={(e) => onFilterChange('search', e.target.value)}
            placeholder="Tìm kiếm người dùng..."
            className="w-full pl-12 pr-4 h-12 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl text-base font-bold transition-all text-slate-900 dark:text-white outline-none"
          />
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <CustomSelect options={roleOptions} value={filters.role_id} onChange={(val) => onFilterChange('role_id', val)} placeholder="Vai trò" icon={<Shield size={16} />} />
          <CustomSelect options={statusOptions} value={filters.status} onChange={(val) => onFilterChange('status', val)} placeholder="Trạng thái" icon={<CheckCircle2 size={16} />} />
          <CustomSelect options={sortOptions} value={filters.sort} onChange={(val) => onFilterChange('sort', val)} placeholder="Sắp xếp" icon={<Filter size={16} />} />
          <Button variant="ghost" className="h-12 w-12 rounded-2xl text-slate-400 hover:text-rose-500 hover:bg-rose-50" onClick={() => onFilterChange('search', '')}>
            {loading ? <Loader2 className="animate-spin" size={20} /> : <XCircle size={20} />}
          </Button>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl shadow-slate-200/40 dark:shadow-none overflow-hidden relative">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800">
                <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Người dùng</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Vai trò & Hạng</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Trạng thái</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50 dark:divide-slate-800">
              {users.data.map((user: User) => (
                <tr key={user.user_id} className="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-all group duration-300">
                  <td className="px-8 py-6">
                    <div className="flex items-center gap-4">
                      <div className="w-14 h-14 rounded-[1.25rem] bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        {user.full_name.substring(0, 2).toUpperCase()}
                      </div>
                      <div>
                        <div className="font-black text-slate-900 dark:text-white text-base leading-tight">{user.full_name}</div>
                        <div className="flex flex-col gap-1 mt-1">
                          <div className="flex items-center gap-1.5 text-sm text-slate-500 font-medium"><Mail size={12} /> {user.email}</div>
                          <div className="flex items-center gap-1.5 text-sm text-slate-500 font-medium">
                            <Phone size={12} /> {user.phone_number || <span className="opacity-50 italic text-xs">Chưa cập nhật</span>}
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td className="px-8 py-6">
                    <div className="flex flex-col gap-2">
                      <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-wider w-fit">
                        <Shield size={12} className="text-blue-500" /> {user.role?.name || 'Customer'}
                      </span>
                      <TierBadge tier={user.member_tier} />
                    </div>
                  </td>
                  <td className="px-8 py-6">
                    <StatusBadge status={user.status} />
                    <div className="flex items-center gap-1.5 mt-2 text-[10px] font-bold text-slate-400">
                      <Calendar size={12} /> {new Date(user.created_at).toLocaleDateString('vi-VN')}
                    </div>
                  </td>
                  <td className="px-8 py-6 text-right">
                    <div className="flex items-center justify-end gap-2">
                      <Button variant="ghost" size="icon" className="h-10 w-10 rounded-xl text-blue-600 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-600 hover:text-white transition-all shadow-sm" onClick={() => onEdit(user)}><Edit size={16} /></Button>
                      <Button variant="ghost" size="icon" className="h-10 w-10 rounded-xl text-amber-600 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-600 hover:text-white transition-all shadow-sm" onClick={() => window.location.href = `/admin/permissions/${user.user_id}/sessions`}><Clock size={16} /></Button>
                      <Button variant="ghost" size="icon" className="h-10 w-10 rounded-xl text-rose-600 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-600 hover:text-white transition-all shadow-sm" onClick={() => handleDelete(user)}><Trash2 size={16} /></Button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Pagination */}
        <div className="flex flex-col sm:flex-row items-center justify-between px-4 py-6 gap-6">
          <div className="flex items-center gap-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest bg-white dark:bg-slate-900 px-6 py-3 rounded-2xl border border-slate-100 dark:border-slate-800">
            <span>Kết quả: <span className="text-slate-900 dark:text-white">{users.total}</span></span>
            <div className="w-px h-3 bg-slate-200 dark:bg-slate-800" />
            <span>Trang <span className="text-blue-600">{users.current_page}</span> / {users.last_page}</span>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" className="h-12 px-6 font-black uppercase text-[10px] tracking-widest rounded-2xl border-slate-200" disabled={users.current_page === 1} onClick={() => onPageChange(users.current_page - 1)}>Trước</Button>
            <Button variant="outline" className="h-12 px-6 font-black uppercase text-[10px] tracking-widest rounded-2xl border-slate-200" disabled={users.current_page === users.last_page} onClick={() => onPageChange(users.current_page + 1)}>Tiếp</Button>
          </div>
        </div>
      </div>
    </div>
  );
}

function RolesDashboard({ roles, onAdd, onEdit }: { roles: Role[], onAdd: () => void, onEdit: (role: Role) => void }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-8 duration-700">
      {roles.map(role => (
        <div key={role.role_id} className="group relative bg-white dark:bg-slate-900 rounded-[3rem] p-10 border border-slate-100 dark:border-slate-800 hover:border-blue-500/50 transition-all duration-500 shadow-xl shadow-slate-200/40 hover:shadow-2xl hover:shadow-blue-500/10 overflow-hidden">
          {/* Animated Background Decor */}
          <div className="absolute -right-12 -top-12 w-48 h-48 bg-gradient-to-br from-blue-500/10 to-indigo-500/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-700" />

          <div className="flex items-start justify-between mb-8 relative z-10">
            <div className="p-5 rounded-[1.5rem] bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 text-blue-600 shadow-inner group-hover:scale-110 transition-transform duration-500">
              <Shield size={36} className="drop-shadow-sm" />
            </div>
            <div className="px-4 py-1.5 rounded-full bg-slate-50 dark:bg-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-widest border border-slate-100 dark:border-slate-700">
              System Role
            </div>
          </div>

          <div className="relative z-10">
            <h3 className="text-3xl font-black text-slate-900 dark:text-white mb-4 tracking-tight group-hover:text-blue-600 transition-colors">{role.name}</h3>
            <p className="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-8 min-h-[48px]">
              {role.description || 'Quản trị viên hệ thống - có toàn quyền điều phối và quản lý mọi tài nguyên.'}
            </p>

            <div className="pt-6 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between">
              <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                {(role as any).permissions ? `${Object.keys((role as any).permissions).filter(k => (role as any).permissions[k]).length} Quyền hạn` : 'Toàn quyền hệ thống'}
              </span>
              <button
                onClick={() => onEdit(role)}
                className="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-2 group/link"
              >
                Chi tiết <Plus size={14} className="group-hover/link:rotate-90 transition-transform" />
              </button>
            </div>
          </div>
        </div>
      ))}

      <div
        onClick={onAdd}
        className="relative group bg-white dark:bg-slate-900 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[3rem] p-10 flex flex-col items-center justify-center gap-6 hover:border-blue-500 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-all duration-500 cursor-pointer min-h-[360px] group shadow-sm hover:shadow-xl hover:shadow-blue-500/5"
      >
        <div className="w-20 h-20 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:bg-blue-500 group-hover:text-white transition-all duration-500 shadow-inner group-hover:shadow-blue-500/50 group-hover:scale-110">
          <Plus size={40} strokeWidth={1.5} />
        </div>
        <div className="text-center">
          <h3 className="text-xl font-black text-slate-900 dark:text-white mb-2">Thêm vai trò mới</h3>
          <p className="text-xs font-bold text-slate-400 uppercase tracking-widest">Tùy chỉnh quyền hạn chi tiết</p>
        </div>
      </div>
    </div>
  );
}


function UserModal({ user, roles, onClose, onSuccess }: any) {
  const isEdit = !!user;
  const [loading, setLoading] = useState(false);
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

  // Local state for custom selects
  const [memberTier, setMemberTier] = useState(user?.member_tier || '');
  const [roleId, setRoleId] = useState(user?.role_id?.toString() || user?.role?.role_id?.toString() || '');
  const [status, setStatus] = useState(user?.status || '');

  // Password visibility state
  const [showPass, setShowPass] = useState(false);
  const [showConfirmPass, setShowConfirmPass] = useState(false);

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    const formData = new FormData(e.currentTarget);
    try {
      await axios.post(isEdit ? `/admin/permissions/${user.user_id}` : '/admin/permissions', formData, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'multipart/form-data' }
      });
      onSuccess();
    } catch (error: any) {
      const Swal = (window as any).Swal;
      Swal.fire({
        title: 'Thất bại!',
        text: error.response?.data?.message || 'Có lỗi xảy ra khi lưu dữ liệu!',
        icon: 'error',
        customClass: { popup: 'rounded-[2rem]' }
      });
    }
    finally { setLoading(false); }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md animate-in fade-in duration-300">
      <div className="bg-white dark:bg-slate-900 w-full max-w-lg rounded-[3rem] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden animate-in zoom-in-95 duration-300">
        <div className="px-10 py-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
          <div>
            <h3 className="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{isEdit ? 'Cập nhật' : 'Tạo mới'} Tài khoản</h3>
          </div>
          <button onClick={onClose} className="w-10 h-10 flex items-center justify-center hover:bg-white dark:hover:bg-slate-700 rounded-2xl text-slate-400 hover:text-rose-500 shadow-sm transition-all"><X size={24} /></button>
        </div>

        <form onSubmit={handleSubmit} className="p-10 space-y-6" autoComplete="off">
          <input type="hidden" name="_token" value={csrfToken} />
          {isEdit && <input type="hidden" name="_method" value="PUT" />}
          {isEdit && <input type="hidden" name="version" value={user.version || 0} />}

          <div className="space-y-2">
            <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Họ và tên</label>
            <input name="full_name" defaultValue={user?.full_name} required className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-base font-bold focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>

          <div className="grid grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Email</label>
              <input name="email" type="email" defaultValue={user?.email} required className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-base font-bold focus:ring-2 focus:ring-blue-500 outline-none" placeholder="example@gmail.com" autoComplete="off" />
            </div>
            <div className="space-y-2">
              <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Số điện thoại</label>
              <input name="phone_number" type="tel" defaultValue={user?.phone_number} className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-base font-bold focus:ring-2 focus:ring-blue-500 outline-none" placeholder="09xx..." />
            </div>
          </div>

          <div className="grid grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">
                Mật khẩu {isEdit && <span className="text-xs lowercase opacity-60 font-medium">(Bỏ trống nếu không đổi)</span>}
              </label>
              <div className="relative group">
                <input
                  name="password"
                  type={showPass ? "text" : "password"}
                  required={!isEdit}
                  className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-base font-bold focus:ring-2 focus:ring-blue-500 outline-none pr-12 transition-all"
                  placeholder="••••••••"
                  autoComplete="new-password"
                />
                <button
                  type="button"
                  onClick={() => setShowPass(!showPass)}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-500 transition-colors p-1"
                >
                  {showPass ? <EyeOff size={18} /> : <Eye size={18} />}
                </button>
              </div>
            </div>
            <div className="space-y-2">
              <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Xác nhận mật khẩu</label>
              <div className="relative group">
                <input
                  name="password_confirmation"
                  type={showConfirmPass ? "text" : "password"}
                  required={!isEdit}
                  className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-base font-bold focus:ring-2 focus:ring-blue-500 outline-none pr-12 transition-all"
                  placeholder="••••••••"
                  autoComplete="new-password"
                />
                <button
                  type="button"
                  onClick={() => setShowConfirmPass(!showConfirmPass)}
                  className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-500 transition-colors p-1"
                >
                  {showConfirmPass ? <EyeOff size={18} /> : <Eye size={18} />}
                </button>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Vai trò</label>
              <input type="hidden" name="role_id" value={roleId} />
              <CustomSelect
                options={[
                  { value: '', label: 'Chọn vai trò...' },
                  ...roles.map((r: any) => ({ value: r.role_id.toString(), label: r.name, icon: <Shield size={14} /> }))
                ]}
                value={roleId}
                onChange={setRoleId}
                placeholder="Chọn vai trò"
                icon={<Shield size={16} />}
              />
            </div>
            <div className="space-y-2">
              <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Hạng thành viên</label>
              <input type="hidden" name="member_tier" value={memberTier} />
              <CustomSelect
                options={[
                  { value: '', label: 'Chọn hạng...' },
                  { value: 'Dong', label: 'Đồng', icon: <Trophy size={14} className="text-orange-500" /> },
                  { value: 'Bac', label: 'Bạc', icon: <Trophy size={14} className="text-slate-400" /> },
                  { value: 'Vang', label: 'Vàng', icon: <Trophy size={14} className="text-amber-500" /> }
                ]}
                value={memberTier}
                onChange={setMemberTier}
                placeholder="Chọn hạng"
                icon={<Trophy size={16} />}
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Trạng thái</label>
            <input type="hidden" name="status" value={status} />
            <CustomSelect
              options={[
                { value: '', label: 'Chọn trạng thái...' },
                { value: 'Active', label: 'Hoạt động', icon: <CheckCircle2 size={14} className="text-emerald-500" /> },
                { value: 'Banned', label: 'Bị khóa', icon: <XCircle size={14} className="text-rose-500" /> }
              ]}
              value={status}
              onChange={setStatus}
              placeholder="Chọn trạng thái"
              icon={<Clock size={16} />}
            />
          </div>

          <div className="pt-6 flex gap-4">
            <Button type="button" variant="outline" className="flex-1 font-black uppercase text-[10px] tracking-widest h-14 rounded-2xl border-slate-200" onClick={onClose}>Hủy bỏ</Button>
            <Button type="submit" disabled={loading} className="flex-1 font-black uppercase text-[10px] tracking-widest h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl shadow-xl shadow-blue-500/30">
              {loading ? <Loader2 className="animate-spin" /> : (isEdit ? 'Cập nhật' : 'Tạo mới')}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}


function RoleModal({ role, onClose, onSuccess }: { role: Role | null, onClose: () => void, onSuccess: () => void }) {
  const isEdit = !!role;
  const [loading, setLoading] = useState(false);
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

  const defaultPermissions = {
    user_view: false, user_manage: false,
    product_view: false, product_manage: false,
    order_view: false, order_manage: false,
    content_view: false, content_manage: false,
    system_config: false
  };

  const [permissions, setPermissions] = useState<Record<string, boolean>>(() => {
    if (role?.permissions && typeof role.permissions === 'object') {
      return { ...defaultPermissions, ...role.permissions };
    }
    return defaultPermissions;
  });

  const permissionGroups = [
    { title: 'Người dùng', keys: ['user_view', 'user_manage'], labels: ['Xem danh sách', 'Quản lý tài khoản'] },
    { title: 'Sản phẩm', keys: ['product_view', 'product_manage'], labels: ['Xem sản phẩm', 'Quản lý kho hàng'] },
    { title: 'Đơn hàng', keys: ['order_view', 'order_manage'], labels: ['Xem đơn hàng', 'Xử lý vận chuyển'] },
    { title: 'Nội dung', keys: ['content_view', 'content_manage'], labels: ['Xem bài viết', 'Đăng tải nội dung'] },
    { title: 'Hệ thống', keys: ['system_config'], labels: ['Cấu hình chuyên sâu'] },
  ];

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    const formData = new FormData(e.currentTarget);
    const data: any = {
      name: formData.get('name'),
      description: formData.get('description'),
      permissions: permissions,
      _token: csrfToken
    };
    if (isEdit) data._method = 'PUT';

    try {
      await axios.post(isEdit ? `/admin/roles/${role.role_id}` : '/admin/roles', data, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      onSuccess();
    } catch (error: any) {
      const Swal = (window as any).Swal;
      Swal.fire({
        title: 'Thất bại!',
        text: error.response?.data?.message || 'Có lỗi xảy ra!',
        icon: 'error',
        customClass: { popup: 'rounded-[2rem]' }
      });
    }
    finally { setLoading(false); }
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md animate-in fade-in duration-300">
      <div className="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-[3rem] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden animate-in zoom-in-95 duration-300 max-h-[90vh] flex flex-col">
        <div className="px-10 py-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
          <div>
            <h2 className="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{isEdit ? 'Cấu hình Vai trò' : 'Tạo Vai trò mới'}</h2>
            <p className="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Phân quyền chi tiết hệ thống</p>
          </div>
          <button onClick={onClose} className="w-10 h-10 flex items-center justify-center hover:bg-white dark:hover:bg-slate-700 rounded-2xl text-slate-400 hover:text-rose-500 shadow-sm transition-all"><X size={24} /></button>
        </div>

        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-10 space-y-8">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div className="space-y-2">
              <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Tên vai trò</label>
              <input name="name" defaultValue={role?.name} required className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-base font-bold focus:ring-2 focus:ring-blue-500 outline-none" placeholder="VD: Marketing, Accountant..." />
            </div>
            <div className="space-y-2">
              <label className="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest ml-1">Mô tả ngắn</label>
              <input name="description" defaultValue={role?.description} className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-base font-bold focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Mô tả trách nhiệm..." />
            </div>
          </div>

          <div className="space-y-6">
            <div className="flex items-center gap-3">
              <Shield size={20} className="text-blue-500" />
              <h4 className="text-sm font-black text-slate-900 dark:text-white uppercase tracking-widest">Quyền hạn chi tiết</h4>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {permissionGroups.map((group, gIdx) => (
                <div key={gIdx} className="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-[2rem] border border-slate-100 dark:border-slate-800">
                  <h5 className="text-xs font-black text-blue-600 uppercase tracking-widest mb-4">{group.title}</h5>
                  <div className="space-y-3">
                    {group.keys.map((key, kIdx) => (
                      <label key={key} className="flex items-center justify-between group cursor-pointer">
                        <span className="text-sm font-bold text-slate-600 dark:text-slate-300 group-hover:text-blue-600 transition-colors">{group.labels[kIdx]}</span>
                        <div
                          onClick={() => setPermissions(prev => ({ ...prev, [key]: !prev[key] }))}
                          className={`w-12 h-6 rounded-full p-1 transition-all duration-300 ${permissions[key] ? 'bg-blue-600' : 'bg-slate-300 dark:bg-slate-700'}`}
                        >
                          <div className={`w-4 h-4 bg-white rounded-full transition-all duration-300 shadow-sm ${permissions[key] ? 'translate-x-6' : 'translate-x-0'}`} />
                        </div>
                      </label>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="pt-6 flex gap-4">
            <Button type="button" variant="outline" className="flex-1 font-black uppercase text-[10px] tracking-widest h-14 rounded-2xl border-slate-200" onClick={onClose}>Hủy bỏ</Button>
            <Button type="submit" disabled={loading} className="flex-1 font-black uppercase text-[10px] tracking-widest h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl shadow-xl shadow-blue-500/30">
              {loading ? <Loader2 className="animate-spin" /> : (isEdit ? 'Cập nhật' : 'Tạo mới')}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
}

function StatCard({ icon, label, value, color, description }: { icon: any, label: string, value: number, color: string, description: string }) {
  const colors: any = {
    blue: 'from-blue-50 to-indigo-50/30 dark:from-blue-900/10 dark:to-transparent border-blue-100/50 dark:border-blue-900/30 text-blue-600',
    emerald: 'from-emerald-50 to-teal-50/30 dark:from-emerald-900/10 dark:to-transparent border-emerald-100/50 dark:border-emerald-900/30 text-emerald-600',
    rose: 'from-rose-50 to-pink-50/30 dark:from-rose-900/10 dark:to-transparent border-rose-100/50 dark:border-rose-900/30 text-rose-600',
    amber: 'from-amber-50 to-yellow-50/30 dark:from-amber-900/10 dark:to-transparent border-amber-100/50 dark:border-amber-900/30 text-amber-500'
  };
  return (
    <div className={`p-7 rounded-[2rem] border bg-gradient-to-br ${colors[color]} shadow-lg shadow-slate-200/20 dark:shadow-none hover:scale-[1.02] transition-all duration-300 group`}>
      <div className="flex items-center justify-between mb-6">
        <div className="p-3 rounded-2xl bg-white dark:bg-slate-900 shadow-sm">{icon}</div>
        <div className="text-[8px] font-black uppercase tracking-[0.2em] opacity-40">Live</div>
      </div>
      <div className="text-3xl font-black text-slate-900 dark:text-white tracking-tighter">{value.toLocaleString()}</div>
      <div className="text-[10px] font-black text-slate-400 mt-1 uppercase tracking-widest">{label}</div>
    </div>
  );
}

function TierBadge({ tier }: { tier: string }) {
  const styles: any = { Vang: 'bg-amber-400 text-white shadow-lg shadow-amber-500/20', Bac: 'bg-slate-400 text-white shadow-lg shadow-slate-500/20', Dong: 'bg-orange-500 text-white shadow-lg shadow-orange-500/20' };
  return (
    <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider ${styles[tier] || styles.Dong}`}>
      <Trophy size={10} /> Member {tier}
    </span>
  );
}

function StatusBadge({ status }: { status: string }) {
  if (status === 'Active') return <span className="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-500/20 shadow-inner"><span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" /> Hoạt động</span>;
  return <span className="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-widest border border-rose-500/20 shadow-inner"><span className="w-1.5 h-1.5 rounded-full bg-rose-500" /> Bị khóa</span>;
}
