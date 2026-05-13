import React, { useState, useEffect, useCallback, useRef } from 'react';
import axios from 'axios';
import { VercelTabs } from './ui/vercel-tabs';
import { 
  Users, ShieldCheck, ShieldAlert, Trophy, 
  Search, Filter, Download, Plus, 
  Edit, Trash2, Shield,
  Clock, CheckCircle2, XCircle, X, Loader2,
  ChevronDown, Mail, Phone, Calendar, Check
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
          <span className="text-xs font-black text-slate-700 dark:text-slate-200 uppercase tracking-wider">
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
                className={`flex items-center justify-between w-full px-4 py-3 rounded-xl text-left transition-all group/opt ${
                  value === option.value 
                    ? 'bg-blue-600 text-white' 
                    : 'hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300'
                }`}
              >
                <div className="flex items-center gap-3">
                  {option.icon && <div className={`${value === option.value ? 'text-white' : 'text-slate-400 group-hover/opt:text-blue-500'} transition-colors`}>{option.icon}</div>}
                  <span className="text-xs font-bold uppercase tracking-widest">{option.label}</span>
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

export default function UserManagement({ 
  users: initialUsers = { data: [], total: 0, current_page: 1, last_page: 1, links: [] }, 
  roles = [], 
  stats: initialStats = { total: 0, active: 0, banned: 0, tiers: { Vang: 0, Bac: 0, Dong: 0 } } 
}: UserManagementProps) {
  const [activeTab, setActiveTab] = useState('users');
  const [users, setUsers] = useState(initialUsers);
  const [stats, setStats] = useState(initialStats);
  const [loading, setLoading] = useState(false);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);

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

  const tabs = [
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
      content: <RolesDashboard roles={roles} />
    }
  ];

  return (
    <div className="space-y-8 animate-in fade-in duration-500">
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <h1 className="text-4xl font-black text-slate-900 dark:text-white tracking-tighter mb-2">Hệ thống Quyền hạn</h1>
          <div className="flex items-center gap-3">
            <span className="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <p className="text-slate-500 dark:text-slate-400 font-bold uppercase text-[10px] tracking-widest">Hệ thống quản trị thời gian thực</p>
          </div>
        </div>
        
        <div className="flex items-center gap-3">
          <Button variant="outline" className="h-12 px-6 gap-2 font-bold border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all rounded-2xl" onClick={() => window.location.href='?export=csv'}>
            <Download size={18} /> Xuất dữ liệu
          </Button>
          <Button onClick={() => { setSelectedUser(null); setIsModalOpen(true); }} className="h-12 px-6 gap-2 font-bold bg-blue-600 hover:bg-blue-700 text-white rounded-2xl shadow-xl shadow-blue-500/20">
            <Plus size={18} /> Thêm tài khoản
          </Button>
        </div>
      </div>

      <VercelTabs tabs={tabs} value={activeTab} onChange={setActiveTab} />

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
    </div>
  );
}

function UserDashboard({ users, stats, roles, loading, filters, onFilterChange, onPageChange, onEdit, onRefresh }: any) {
  const handleDelete = async (user: User) => {
    if (!confirm(`Bạn có chắc chắn muốn xóa tài khoản "${user.full_name}"?`)) return;
    try {
      await axios.post(`/admin/permissions/${user.user_id}`, {
        _method: 'DELETE',
        _token: (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content
      }, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      onRefresh();
    } catch (error) { alert('Lỗi khi xóa người dùng'); }
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
    <div className="space-y-8">
      {/* Stats Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <StatCard icon={<Users className="text-blue-600" />} label="Tổng người dùng" value={stats.total} color="blue" description="Tất cả tài khoản" />
        <StatCard icon={<ShieldCheck className="text-emerald-600" />} label="Đang hoạt động" value={stats.active} color="emerald" description="User đã xác minh" />
        <StatCard icon={<ShieldAlert className="text-rose-600" />} label="Đã bị khóa" value={stats.banned} color="rose" description="Vi phạm chính sách" />
        <StatCard icon={<Trophy className="text-amber-500" />} label="Thành viên VIP" value={stats.tiers.Vang} color="amber" description="Hạng thành viên Vàng" />
      </div>

      {/* Filters Bar - USING CUSTOM SELECTS */}
      <div className="bg-white dark:bg-slate-900 p-6 rounded-[2rem] border border-slate-100 dark:border-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-none flex flex-wrap items-center gap-4">
        <div className="relative flex-1 min-w-[280px]">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" size={20} />
          <input 
            type="text" 
            value={filters.search}
            onChange={(e) => onFilterChange('search', e.target.value)}
            placeholder="Tìm kiếm người dùng..." 
            className="w-full pl-12 pr-4 h-12 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl text-sm font-bold transition-all text-slate-900 dark:text-white outline-none"
          />
        </div>
        
        <div className="flex flex-wrap items-center gap-3">
          <CustomSelect 
            options={roleOptions} 
            value={filters.role_id} 
            onChange={(val) => onFilterChange('role_id', val)} 
            placeholder="Vai trò" 
            icon={<Shield size={16} />}
          />

          <CustomSelect 
            options={statusOptions} 
            value={filters.status} 
            onChange={(val) => onFilterChange('status', val)} 
            placeholder="Trạng thái" 
            icon={<CheckCircle2 size={16} />}
          />

          <CustomSelect 
            options={sortOptions} 
            value={filters.sort} 
            onChange={(val) => onFilterChange('sort', val)} 
            placeholder="Sắp xếp" 
            icon={<Filter size={16} />}
          />

          <Button variant="ghost" className="h-12 w-12 rounded-2xl text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20" onClick={() => onFilterChange('search', '')}>
            {loading ? <Loader2 className="animate-spin" size={20} /> : <XCircle size={20} />}
          </Button>
        </div>
      </div>

      {/* Table - Premium Overhaul */}
      <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl shadow-slate-200/40 dark:shadow-none overflow-hidden relative">
        {loading && (
          <div className="absolute inset-0 bg-white/40 dark:bg-slate-900/40 backdrop-blur-[2px] z-20 flex items-center justify-center">
            <Loader2 className="animate-spin text-blue-600" size={48} />
          </div>
        )}
        
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800">
                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Người dùng</th>
                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Vai trò & Hạng</th>
                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Trạng thái</th>
                <th className="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Thao tác</th>
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
                          <div className="flex items-center gap-1.5 text-xs text-slate-400 font-medium"><Mail size={12} /> {user.email}</div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td className="px-8 py-6">
                    <div className="flex flex-col gap-2">
                      <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-black uppercase tracking-wider w-fit">
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
                      <Button variant="ghost" size="icon" className="h-10 w-10 rounded-xl text-amber-600 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-600 hover:text-white transition-all shadow-sm" onClick={() => window.location.href=`/admin/permissions/${user.user_id}/sessions`}><Clock size={16} /></Button>
                      <Button variant="ghost" size="icon" className="h-10 w-10 rounded-xl text-rose-600 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-600 hover:text-white transition-all shadow-sm" onClick={() => handleDelete(user)}><Trash2 size={16} /></Button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* Pagination - Premium */}
      <div className="flex flex-col sm:flex-row items-center justify-between px-4 py-6 gap-6">
        <div className="flex items-center gap-4 text-xs font-bold text-slate-400 uppercase tracking-widest bg-white dark:bg-slate-900 px-6 py-3 rounded-2xl border border-slate-100 dark:border-slate-800">
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
  );
}

function UserModal({ user, roles, onClose, onSuccess }: any) {
  const isEdit = !!user;
  const [loading, setLoading] = useState(false);
  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    const formData = new FormData(e.currentTarget);
    try {
      await axios.post(isEdit ? `/admin/permissions/${user.user_id}` : '/admin/permissions', formData, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'multipart/form-data' }
      });
      onSuccess();
    } catch (error: any) { alert(error.response?.data?.message || 'Có lỗi xảy ra!'); }
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

        <form onSubmit={handleSubmit} className="p-10 space-y-6">
          <input type="hidden" name="_token" value={csrfToken} />
          {isEdit && <input type="hidden" name="_method" value="PUT" />}
          
          <div className="space-y-2">
            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Họ và tên</label>
            <input name="full_name" defaultValue={user?.full_name} required className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>

          <div className="space-y-2">
            <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Email</label>
            <input name="email" type="email" defaultValue={user?.email} required className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none" />
          </div>

          <div className="grid grid-cols-2 gap-6">
            <div className="space-y-2">
              <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Vai trò</label>
              <select name="role_id" defaultValue={user?.role?.role_id} className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                {roles.map((r: any) => <option key={r.role_id} value={r.role_id}>{r.name}</option>)}
              </select>
            </div>
            <div className="space-y-2">
              <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Trạng thái</label>
              <select name="status" defaultValue={user?.status} className="w-full px-5 py-3.5 bg-slate-50 dark:bg-slate-800 border-none rounded-2xl text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                <option value="Active">Hoạt động</option>
                <option value="Banned">Bị khóa</option>
              </select>
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

function RolesDashboard({ roles }: { roles: Role[] }) {
  return (
    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-in fade-in slide-in-from-bottom-8 duration-700">
      {roles.map(role => (
        <div key={role.role_id} className="group relative bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 border border-slate-100 dark:border-slate-800 hover:border-blue-500 transition-all duration-500 shadow-xl shadow-slate-200/50 hover:shadow-2xl overflow-hidden">
          <div className="absolute -right-8 -top-8 w-32 h-32 bg-blue-500/5 rounded-full blur-3xl group-hover:bg-blue-500/15 transition-all" />
          <div className="flex items-start justify-between mb-6 relative z-10">
            <div className="p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 shadow-inner"><Shield size={32} /></div>
          </div>
          <h3 className="text-2xl font-black text-slate-900 dark:text-white mb-3 tracking-tight">{role.name}</h3>
          <p className="text-sm text-slate-500 dark:text-slate-400 mb-8 line-clamp-3 font-medium leading-relaxed">{role.description || 'Chưa có mô tả chi tiết.'}</p>
          <div className="flex items-center justify-between pt-6 border-t border-slate-50 dark:border-slate-800 relative z-10">
            <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">Toàn quyền hệ thống</span>
            <Button variant="ghost" size="sm" className="font-bold text-blue-600 hover:bg-blue-50 rounded-xl">Chi tiết</Button>
          </div>
        </div>
      ))}
      <button className="flex flex-col items-center justify-center gap-4 p-12 rounded-[2.5rem] border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-blue-500 hover:bg-blue-50/30 transition-all duration-500 group">
        <div className="w-16 h-16 rounded-[1.5rem] bg-slate-50 dark:bg-slate-800 flex items-center justify-center text-slate-400 group-hover:bg-blue-600 group-hover:text-white group-hover:scale-110 transition-all duration-500 shadow-sm"><Plus size={32} /></div>
        <span className="block font-black text-slate-900 dark:text-white text-lg tracking-tight">Thêm vai trò mới</span>
      </button>
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
