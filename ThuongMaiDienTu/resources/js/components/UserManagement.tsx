import React, { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';
import { VercelTabs } from './ui/vercel-tabs';
import {
  Users, User, Briefcase, ShieldCheck, ShieldAlert, Trophy,
  Search, Filter, Download, Plus,
  Edit, Trash2, Shield,
  Clock, CheckCircle2, XCircle, X, Loader2,
  ChevronDown, Mail, Phone, Calendar, Check,
  Eye, EyeOff
} from 'lucide-react';
import { Button } from './ui/button';
import { t } from '../helpers';

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

  useEffect(() => {
    if (isModalOpen || isRoleModalOpen) {
      document.body.classList.add('admin-modal-open');
    } else {
      document.body.classList.remove('admin-modal-open');
    }
  }, [isModalOpen, isRoleModalOpen]);

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
            <h1 className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter mb-2">{t('Hệ thống Quyền hạn', 'Role & Permission System')}</h1>
            <div className="flex items-center gap-3">
              <span className="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
              <p className="text-slate-500 dark:text-slate-400 font-bold uppercase text-xs tracking-widest">{t('Hệ thống quản trị thời gian thực', 'Real-time Administration System')}</p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <Button variant="outline" className="h-12 px-6 gap-2 font-bold border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all rounded-2xl" onClick={() => window.location.href = '?export=csv'}>
              <Download size={18} /> {t('Xuất dữ liệu', 'Export Data')}
            </Button>
            <Button onClick={() => { setSelectedUser(null); setIsModalOpen(true); }} className="h-12 px-6 gap-2 font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-2xl shadow-xl shadow-blue-500/20">
              <Plus size={18} /> {t('Thêm tài khoản', 'Add Account')}
            </Button>
          </div>
        </div>

        <VercelTabs
          tabs={[
            {
              label: t('Danh sách Người dùng', 'User List'),
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
              label: t('Cấu hình Vai trò', 'Role Settings'),
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
      title: t('Xác nhận xóa?', 'Confirm Delete?'),
      text: t('Bạn có chắc chắn muốn xóa tài khoản', 'Are you sure you want to delete the account') + ` "${user.full_name}"? ` + t('Hành động này không thể hoàn tác!', 'This action cannot be undone!'),
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e11d48', // rose-600
      cancelButtonColor: '#64748b', // slate-500
      confirmButtonText: t('Đồng ý xóa', 'Yes, delete it'),
      cancelButtonText: t('Hủy bỏ', 'Cancel'),
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
          title: t('Đã xóa!', 'Deleted!'),
          text: t('Tài khoản đã được gỡ khỏi hệ thống.', 'The account has been removed from the system.'),
          icon: 'success',
          timer: 2000,
          showConfirmButton: false,
          customClass: { popup: 'rounded-[2rem]' }
        });
      } catch (error) {
        Swal.fire({
          title: t('Lỗi!', 'Error!'),
          text: t('Không thể xóa người dùng này.', 'Cannot delete this user.'),
          icon: 'error',
          customClass: { popup: 'rounded-[2rem]' }
        });
      }
    }
  };

  const roleOptions = [
    { value: '', label: t('Tất cả vai trò', 'All Roles') },
    ...roles.map((r: any) => ({ value: r.role_id.toString(), label: r.name, icon: <Shield size={14} /> }))
  ];

  const statusOptions = [
    { value: '', label: t('Trạng thái', 'Status') },
    { value: 'Active', label: t('Hoạt động', 'Active'), icon: <CheckCircle2 size={14} className="text-emerald-500" /> },
    { value: 'Banned', label: t('Bị khóa', 'Banned'), icon: <XCircle size={14} className="text-rose-500" /> }
  ];

  const sortOptions = [
    { value: 'newest', label: t('Mới nhất', 'Newest'), icon: <Calendar size={14} /> },
    { value: 'oldest', label: t('Cũ nhất', 'Oldest'), icon: <Calendar size={14} /> },
    { value: 'name_az', label: t('Tên A-Z', 'Name A-Z'), icon: <Users size={14} /> },
    { value: 'name_za', label: t('Tên Z-A', 'Name Z-A'), icon: <Users size={14} /> }
  ];

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard icon={<Users className="text-blue-600" />} label={t('Tổng người dùng', 'Total Users')} value={stats.total} color="blue" description={t('Tất cả tài khoản', 'All Accounts')} />
        <StatCard icon={<ShieldCheck className="text-emerald-600" />} label={t('Đang hoạt động', 'Active')} value={stats.active} color="emerald" description={t('User đã xác minh', 'Verified Users')} />
        <StatCard icon={<ShieldAlert className="text-rose-600" />} label={t('Đã bị khóa', 'Banned')} value={stats.banned} color="rose" description={t('Vi phạm chính sách', 'Policy Violations')} />
        <StatCard icon={<Trophy className="text-amber-500" />} label={t('Thành viên VIP', 'VIP Members')} value={stats.tiers.Vang} color="amber" description={t('Hạng thành viên Vàng', 'Gold Tier Members')} />
      </div>

      {/* Filters Bar */}
      <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none flex flex-wrap items-center gap-4">
        <div className="relative flex-1 min-w-[280px]">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
          <input
            type="text"
            value={filters.search}
            onChange={(e) => onFilterChange('search', e.target.value)}
            placeholder={t('Tìm kiếm người dùng...', 'Search users...')}
            className="w-full pl-12 pr-4 h-12 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl text-base font-bold transition-all text-slate-900 dark:text-white outline-none"
          />
        </div>

        <div className="flex flex-wrap items-center gap-3">
          <CustomSelect options={roleOptions} value={filters.role_id} onChange={(val) => onFilterChange('role_id', val)} placeholder={t('Vai trò', 'Role')} icon={<Shield size={16} />} />
          <CustomSelect options={statusOptions} value={filters.status} onChange={(val) => onFilterChange('status', val)} placeholder={t('Trạng thái', 'Status')} icon={<CheckCircle2 size={16} />} />
          <CustomSelect options={sortOptions} value={filters.sort} onChange={(val) => onFilterChange('sort', val)} placeholder={t('Sắp xếp', 'Sort by')} icon={<Filter size={16} />} />
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
                <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">{t('Người dùng', 'User')}</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">{t('Vai trò & Hạng', 'Role & Tier')}</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">{t('Trạng thái', 'Status')}</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">{t('Thao tác', 'Actions')}</th>
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
                            <Phone size={12} /> {user.phone_number || <span className="opacity-50 italic text-xs">{t('Chưa cập nhật', 'Not updated')}</span>}
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
                      <Calendar size={12} /> {new Date(user.created_at).toLocaleDateString(t('vi-VN', 'en-US'))}
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
            <span>{t('Kết quả:', 'Results:')} <span className="text-slate-900 dark:text-white">{users.total}</span></span>
            <div className="w-px h-3 bg-slate-200 dark:bg-slate-800" />
            <span>{t('Trang', 'Page')} <span className="text-blue-600">{users.current_page}</span> / {users.last_page}</span>
          </div>
          <div className="flex items-center gap-2">
            <Button variant="outline" className="h-12 px-6 font-black uppercase text-[10px] tracking-widest rounded-2xl border-slate-200" disabled={users.current_page === 1} onClick={() => onPageChange(users.current_page - 1)}>{t('Trước', 'Previous')}</Button>
            <Button variant="outline" className="h-12 px-6 font-black uppercase text-[10px] tracking-widest rounded-2xl border-slate-200" disabled={users.current_page === users.last_page} onClick={() => onPageChange(users.current_page + 1)}>{t('Tiếp', 'Next')}</Button>
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
              {role.description || t('Quản trị viên hệ thống - có toàn quyền điều phối và quản lý mọi tài nguyên.', 'System Administrator - has full authority to coordinate and manage all resources.')}
            </p>

            <div className="pt-6 border-t border-slate-50 dark:border-slate-800 flex items-center justify-between">
              <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                {(role as any).permissions ? `${Object.keys((role as any).permissions).filter(k => (role as any).permissions[k]).length} ` + t('Quyền hạn', 'Permissions') : t('Toàn quyền hệ thống', 'Full system access')}
              </span>
              <button
                onClick={() => onEdit(role)}
                className="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-2 group/link"
              >
                {t('Chi tiết', 'Details')} <Plus size={14} className="group-hover/link:rotate-90 transition-transform" />
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
          <h3 className="text-xl font-black text-slate-900 dark:text-white mb-2">{t('Thêm vai trò mới', 'Add New Role')}</h3>
          <p className="text-xs font-bold text-slate-400 uppercase tracking-widest">{t('Tùy chỉnh quyền hạn chi tiết', 'Customize detailed permissions')}</p>
        </div>
      </div>
    </div>
  );
}


function UserModal({ user, roles, onClose, onSuccess }: any) {
  const isEdit = !!user;
  const [loading, setLoading] = useState(false);
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

  const [memberTier, setMemberTier] = useState(user?.member_tier || '');
  const [roleId, setRoleId] = useState(user?.role_id?.toString() || user?.role?.role_id?.toString() || '');
  const [status, setStatus] = useState(user?.status || 'Active');
  const [showPass, setShowPass] = useState(false);

  const formId = "user-management-form";

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    const formData = new FormData(e.currentTarget);
    
    try {
      const url = isEdit ? `/admin/permissions/${user.user_id}` : '/admin/permissions';
      await axios.post(url, formData, {
        headers: { 
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'multipart/form-data'
        }
      });
      
      const Swal = (window as any).Swal;
      await Swal.fire({
        title: t('Thành công!', 'Success!'),
        text: isEdit ? t('Đã cập nhật tài khoản.', 'Account updated.') : t('Đã tạo tài khoản mới.', 'New account created.'),
        icon: 'success',
        timer: 1500,
        showConfirmButton: false,
        customClass: { popup: 'rounded-[2rem]' }
      });
      
      onSuccess();
    } catch (error: any) {
      const Swal = (window as any).Swal;
      Swal.fire({
        title: t('Thất bại!', 'Failed!'),
        text: error.response?.data?.message || t('Không thể lưu dữ liệu tài khoản.', 'Could not save account data.'),
        icon: 'error',
        customClass: { popup: 'rounded-[2rem]' }
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-500 overflow-y-auto custom-scrollbar">
      <div className="bg-white dark:bg-slate-900 w-full max-w-6xl rounded-[3.5rem] shadow-[0_40px_160px_rgba(0,0,0,0.4)] border border-white/20 overflow-hidden animate-in zoom-in-95 duration-700 flex flex-col">
        
        {/* Master Header */}
        <div className="px-12 py-10 relative overflow-hidden shrink-0 bg-gradient-to-r from-slate-50 to-white dark:from-slate-900 dark:to-slate-800 border-b border-slate-100 dark:border-slate-800">
          <div className="absolute right-0 top-0 w-96 h-96 bg-blue-500/10 rounded-full blur-[100px] -mr-48 -mt-48" />
          <div className="relative z-10 flex items-center justify-between">
            <div className="flex items-center gap-6">
              <div className="w-16 h-16 rounded-[1.75rem] bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-2xl shadow-blue-600/30 ring-4 ring-blue-500/10">
                {isEdit ? <Edit size={32} /> : <Plus size={32} />}
              </div>
              <div>
                <h2 className="text-3xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                  {isEdit ? t('Cập nhật', 'Update') : t('Khởi tạo', 'Create')} {t('Tài khoản', 'Account')}
                </h2>
                <div className="flex items-center gap-2 mt-1">
                  <div className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse" />
                  <p className="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">{t('Hệ thống quản trị tài nguyên chuyên sâu', 'Advanced Resource Management System')}</p>
                </div>
              </div>
            </div>
            <button 
              onClick={onClose} 
              className="w-14 h-14 flex items-center justify-center rounded-[1.25rem] bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 shadow-sm transition-all active:scale-90"
            >
              <X size={28} />
            </button>
          </div>
        </div>

        {/* Master Form Content */}
        <form id={formId} onSubmit={handleSubmit} className="relative" autoComplete="off">
          <input type="hidden" name="_token" value={csrfToken} />
          {isEdit && <input type="hidden" name="_method" value="PUT" />}
          {isEdit && <input type="hidden" name="version" value={user.version || 0} />}

          <div className="flex flex-col lg:flex-row">
            {/* LEFT PANE: IDENTITY */}
            <div className="flex-1 p-12 space-y-10">
              <div className="flex items-center gap-4 mb-2">
                <div className="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                  <User size={20} />
                </div>
                <div>
                  <h4 className="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">{t('Thông tin định danh', 'Identification Info')}</h4>
                  <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{t('Dữ liệu cá nhân & liên lạc', 'Personal & Contact Data')}</p>
                </div>
              </div>

              <div className="space-y-8">
                <div className="space-y-2.5">
                  <label className="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Họ và tên đầy đủ', 'Full Name')}</label>
                  <div className="relative group">
                    <div className="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                      <User size={20} />
                    </div>
                    <input 
                      name="full_name" 
                      defaultValue={isEdit ? user?.full_name : ''} 
                      required 
                      autoComplete="off"
                      className="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-[1.5rem] font-bold text-base outline-none transition-all shadow-sm group-hover:bg-slate-100/50"
                      placeholder="VD: Nguyễn Văn A..."
                    />
                  </div>
                </div>

                <div className="space-y-2.5">
                  <label className="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Địa chỉ Email liên hệ', 'Contact Email')}</label>
                  <div className="relative group">
                    <div className="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                      <Mail size={20} />
                    </div>
                    <input 
                      name="email" 
                      type="email" 
                      defaultValue={isEdit ? user?.email : ''} 
                      required 
                      autoComplete="off"
                      className="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-[1.5rem] font-bold text-base outline-none transition-all shadow-sm group-hover:bg-slate-100/50"
                      placeholder="email@example.com"
                    />
                  </div>
                </div>

                <div className="space-y-2.5">
                  <label className="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Số điện thoại di động', 'Mobile Phone Number')}</label>
                  <div className="relative group">
                    <div className="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                      <Phone size={20} />
                    </div>
                    <input 
                      name="phone_number" 
                      defaultValue={isEdit ? user?.phone_number : ''} 
                      autoComplete="off"
                      className="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-[1.5rem] font-bold text-base outline-none transition-all shadow-sm group-hover:bg-slate-100/50"
                      placeholder="09xx..."
                    />
                  </div>
                </div>
              </div>
            </div>

            {/* VERTICAL DIVIDER */}
            <div className="hidden lg:block w-px bg-slate-100 dark:bg-slate-800 self-stretch my-12" />

            {/* RIGHT PANE: ACCESS & SECURITY */}
            <div className="flex-1 p-12 space-y-10">
              <div className="flex items-center gap-4 mb-2">
                <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600">
                  <ShieldCheck size={20} />
                </div>
                <div>
                  <h4 className="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider">{t('Cấu hình hệ thống', 'System Configuration')}</h4>
                  <p className="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{t('Quyền hạn & Bảo mật truy cập', 'Permissions & Access Security')}</p>
                </div>
              </div>

              <div className="space-y-6">
                <div className="grid grid-cols-2 gap-6">
                  <div className="space-y-2.5">
                    <label className="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Mật khẩu', 'Password')}</label>
                    <div className="relative">
                      <input 
                        name="password" 
                        type={showPass ? "text" : "password"} 
                        required={!isEdit}
                        autoComplete="new-password"
                        className="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all pr-12 shadow-sm"
                        placeholder="••••••••"
                      />
                      <button type="button" onClick={() => setShowPass(!showPass)} className="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600">
                        {showPass ? <EyeOff size={18} /> : <Eye size={18} />}
                      </button>
                    </div>
                  </div>
                  <div className="space-y-2.5">
                    <label className="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Xác nhận', 'Confirm Password')}</label>
                    <input 
                      name="password_confirmation" 
                      type={showPass ? "text" : "password"} 
                      required={!isEdit}
                      autoComplete="new-password"
                      className="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all shadow-sm"
                      placeholder="••••••••"
                    />
                  </div>
                </div>

                <div className="grid grid-cols-2 gap-6">
                  <div className="space-y-2.5">
                    <label className="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Vai trò hệ thống', 'System Role')}</label>
                    <input type="hidden" name="role_id" value={roleId} />
                    <CustomSelect
                      options={[
                        { value: '', label: t('Chọn vai trò...', 'Select role...') },
                        ...roles.map((r: any) => ({ value: r.role_id.toString(), label: r.name, icon: <Shield size={14} /> }))
                      ]}
                      value={roleId}
                      onChange={setRoleId}
                      placeholder={t('Chọn vai trò', 'Select role')}
                      icon={<Shield size={16} />}
                    />
                  </div>
                  <div className="space-y-2.5">
                    <label className="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Hạng thành viên', 'Member Tier')}</label>
                    <input type="hidden" name="member_tier" value={memberTier} />
                    <CustomSelect
                      options={[
                        { value: '', label: t('Chọn hạng...', 'Select tier...') },
                        { value: 'Dong', label: t('Đồng (Bronze)', 'Bronze'), icon: <Trophy size={14} className="text-orange-500" /> },
                        { value: 'Bac', label: t('Bạc (Silver)', 'Silver'), icon: <Trophy size={14} className="text-slate-400" /> },
                        { value: 'Vang', label: t('Vàng (Gold)', 'Gold'), icon: <Trophy size={14} className="text-amber-500" /> }
                      ]}
                      value={memberTier}
                      onChange={setMemberTier}
                      placeholder={t('Chọn hạng', 'Select tier')}
                      icon={<Trophy size={16} />}
                    />
                  </div>
                </div>

                <div className="space-y-2.5">
                  <label className="text-[11px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Trạng thái vận hành', 'Operational Status')}</label>
                  <input type="hidden" name="status" value={status} />
                  <CustomSelect
                    options={[
                      { value: 'Active', label: t('Hoạt động bình thường', 'Active / Normal'), icon: <CheckCircle2 size={14} className="text-emerald-500" /> },
                      { value: 'Banned', label: t('Bị khóa/Tạm dừng', 'Banned / Suspended'), icon: <XCircle size={14} className="text-rose-500" /> }
                    ]}
                    value={status}
                    onChange={setStatus}
                    placeholder={t('Chọn trạng thái', 'Select status')}
                    icon={<Clock size={16} />}
                  />
                </div>
              </div>
            </div>
          </div>
        </form>

        {/* Master Footer */}
        <div className="px-12 py-10 bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-md border-t border-slate-100 dark:border-slate-800 flex justify-end gap-6 shrink-0">
          <Button 
            type="button" 
            variant="outline" 
            className="px-12 h-16 rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-all shadow-sm" 
            onClick={onClose}
          >
            {t('Hủy yêu cầu', 'Cancel')}
          </Button>
          <Button 
            type="submit" 
            form={formId}
            disabled={loading}
            className="px-16 h-16 rounded-[1.5rem] bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-black text-xs uppercase tracking-[0.2em] shadow-2xl shadow-blue-600/30 transition-all active:scale-95 disabled:opacity-50"
          >
            {loading ? <Loader2 className="animate-spin" /> : (isEdit ? t('Xác nhận Cập nhật', 'Confirm Update') : t('Khởi tạo Tài khoản', 'Create Account'))}
          </Button>
        </div>
      </div>
    </div>
  );
}


function RoleModal({ role, onClose, onSuccess }: { role: Role | null, onClose: () => void, onSuccess: () => void }) {
  const isEdit = !!role;
  const [loading, setLoading] = useState(false);
  const formId = "role-management-form";
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
    { title: t('Người dùng', 'Users'), keys: ['user_view', 'user_manage'], labels: [t('Xem danh sách', 'View List'), t('Quản lý tài khoản', 'Manage Accounts')], icon: <Users size={16} /> },
    { title: t('Sản phẩm', 'Products'), keys: ['product_view', 'product_manage'], labels: [t('Xem sản phẩm', 'View Products'), t('Quản lý kho hàng', 'Manage Inventory')], icon: <Briefcase size={16} /> },
    { title: t('Đơn hàng', 'Orders'), keys: ['order_view', 'order_manage'], labels: [t('Xem đơn hàng', 'View Orders'), t('Xử lý vận chuyển', 'Process Shipping')], icon: <Clock size={16} /> },
    { title: t('Nội dung', 'Content'), keys: ['content_view', 'content_manage'], labels: [t('Xem bài viết', 'View Articles'), t('Đăng tải nội dung', 'Publish Content')], icon: <Calendar size={16} /> },
    { title: t('Hệ thống', 'System'), keys: ['system_config'], labels: [t('Cấu hình chuyên sâu', 'Advanced Config')], icon: <Shield size={16} /> },
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
        title: t('Thất bại!', 'Failed!'),
        text: error.response?.data?.message || t('Có lỗi xảy ra!', 'An error occurred!'),
        icon: 'error',
        customClass: { popup: 'rounded-[2rem]' }
      });
    }
    finally { setLoading(false); }
  };

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center p-6 bg-slate-950/80 backdrop-blur-md animate-in fade-in duration-500 overflow-y-auto custom-scrollbar">
      <div className="bg-white dark:bg-slate-900 w-full max-w-5xl rounded-[3rem] shadow-[0_40px_160px_rgba(0,0,0,0.4)] border border-white/20 overflow-hidden animate-in zoom-in-95 duration-700 flex flex-col my-8">
        
        {/* Header */}
        <div className="px-10 py-8 relative overflow-hidden shrink-0 bg-gradient-to-r from-indigo-50 to-white dark:from-slate-900 dark:to-slate-800 border-b border-slate-100 dark:border-slate-800">
          <div className="absolute right-0 top-0 w-80 h-80 bg-indigo-500/10 rounded-full blur-[80px] -mr-40 -mt-40" />
          <div className="relative z-10 flex items-center justify-between">
            <div className="flex items-center gap-5">
              <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-purple-700 flex items-center justify-center text-white shadow-xl shadow-indigo-600/20 ring-4 ring-indigo-500/5">
                <Shield size={28} />
              </div>
              <div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                  {isEdit ? t('Cấu hình', 'Configure') : t('Tạo mới', 'Create')} {t('Vai trò', 'Role')}
                </h2>
                <p className="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-0.5">{t('Phân quyền & Kiểm soát truy cập', 'Role Permissions & Access Control')}</p>
              </div>
            </div>
            <button 
              onClick={onClose} 
              className="w-12 h-12 flex items-center justify-center rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-all active:scale-90"
            >
              <X size={24} />
            </button>
          </div>
        </div>

        {/* Content */}
        <form id={formId} onSubmit={handleSubmit} className="p-10 space-y-10 overflow-y-auto custom-scrollbar max-h-[60vh]">
          {/* Identity Section */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div className="space-y-2">
              <label className="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Tên định danh', 'Identifier Name')}</label>
              <input 
                name="name" 
                defaultValue={role?.name} 
                required 
                className="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-black text-sm outline-none transition-all shadow-sm"
                placeholder={t('VD: Quản trị viên...', 'E.g. Administrator...')}
              />
            </div>
            <div className="space-y-2">
              <label className="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-[0.1em] ml-1">{t('Mô tả chức trách', 'Role Description')}</label>
              <input 
                name="description" 
                defaultValue={role?.description} 
                className="w-full px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-2 border-transparent focus:border-indigo-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all shadow-sm"
                placeholder={t('Mô tả ngắn gọn quyền hạn...', 'Brief description of role rights...')}
              />
            </div>
          </div>

          {/* Permissions Matrix */}
          <div className="space-y-6">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600">
                <ShieldCheck size={16} />
              </div>
              <h4 className="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">{t('Ma trận Phân quyền', 'Permissions Matrix')}</h4>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
              {permissionGroups.map((group, gIdx) => (
                <div key={gIdx} className="p-6 bg-slate-50/50 dark:bg-slate-800/30 rounded-[2rem] border border-slate-100 dark:border-slate-800 transition-all hover:border-indigo-200 dark:hover:border-indigo-900 group">
                  <div className="flex items-center gap-3 mb-5">
                    <div className="text-indigo-500 group-hover:scale-110 transition-transform">{group.icon}</div>
                    <h5 className="text-[10px] font-black text-indigo-600 uppercase tracking-[0.1em]">{group.title}</h5>
                  </div>
                  <div className="space-y-3.5">
                    {group.keys.map((key, kIdx) => (
                      <label key={key} className="flex items-center justify-between group/item cursor-pointer">
                        <span className="text-xs font-bold text-slate-600 dark:text-slate-300 group-hover/item:text-indigo-600 transition-colors">{group.labels[kIdx]}</span>
                        <div
                          onClick={() => setPermissions(prev => ({ ...prev, [key]: !prev[key] }))}
                          className={`w-12 h-6 rounded-full p-1 transition-all duration-300 ${permissions[key] ? 'bg-indigo-600 shadow-lg shadow-indigo-500/10' : 'bg-slate-300 dark:bg-slate-700'}`}
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
        </form>

        {/* Footer */}
        <div className="px-10 py-8 bg-slate-50/80 dark:bg-slate-800/80 backdrop-blur-md border-t border-slate-100 dark:border-slate-800 flex justify-end gap-5 shrink-0">
          <Button 
            type="button" 
            variant="outline" 
            className="px-10 h-14 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 transition-all shadow-sm" 
            onClick={onClose}
          >
            {t('Hủy bỏ', 'Cancel')}
          </Button>
          <Button 
            type="submit" 
            form={formId}
            disabled={loading}
            className="px-14 h-14 rounded-2xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-black text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-600/20 transition-all active:scale-95"
          >
            {loading ? <Loader2 className="animate-spin" /> : (isEdit ? t('Lưu cấu hình', 'Save Configuration') : t('Tạo Vai trò', 'Create Role'))}
          </Button>
        </div>
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
  const tierNameMap: Record<string, string> = { Vang: t('Vàng', 'Gold'), Bac: t('Bạc', 'Silver'), Dong: t('Đồng', 'Bronze') };
  return (
    <span className={`inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider ${styles[tier] || styles.Dong}`}>
      <Trophy size={10} /> {t('Thành viên', 'Member')} {tierNameMap[tier] || tier}
    </span>
  );
}

function StatusBadge({ status }: { status: string }) {
  if (status === 'Active') return <span className="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-500/20 shadow-inner"><span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" /> {t('Hoạt động', 'Active')}</span>;
  return <span className="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 text-[10px] font-black uppercase tracking-widest border border-rose-500/20 shadow-inner"><span className="w-1.5 h-1.5 rounded-full bg-rose-500" /> {t('Bị khóa', 'Banned')}</span>;
}
