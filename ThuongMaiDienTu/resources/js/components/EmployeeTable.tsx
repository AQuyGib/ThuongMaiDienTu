import React from 'react';
import { Mail, Phone, Calendar, Shield, Edit, Trash2, CheckCircle2, XCircle } from 'lucide-react';

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

interface EmployeeTableProps {
  employees: {
    data: Employee[];
    links: any[];
    current_page: number;
    last_page: number;
    total: number;
    from?: number;
    to?: number;
  };
  isLoading: boolean;
  selectedIds: Set<number>;
  onEdit: (employee: Employee) => void;
  onDelete: (employee: Employee) => void;
  onPageChange: (page: number) => void;
  onToggleStatus: (employee: Employee) => void;
  onOpenDrawer: (employee: Employee) => void;
  onSelectId: (id: number) => void;
  onSelectAll: () => void;
}

export default function EmployeeTable({ employees, isLoading, selectedIds, onEdit, onDelete, onPageChange, onToggleStatus, onOpenDrawer, onSelectId, onSelectAll }: EmployeeTableProps) {
  
  // --- PULSING SKELETON COMPONENT FOR PREMIUM LOADING ---
  if (isLoading) {
    return (
      <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl overflow-hidden animate-pulse">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800">
                <th className="px-4 py-5 w-12"></th>
                <th className="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Nhân viên</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Vai trò</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Trạng thái</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50 dark:divide-slate-800">
              {[...Array(5)].map((_, idx) => (
                <tr key={idx} className="border-b border-slate-50 dark:border-slate-800">
                  <td className="px-4 py-6"><div className="h-5 w-5 bg-slate-200 dark:bg-slate-800 rounded" /></td>
                  <td className="px-8 py-6 flex items-center gap-4">
                    <div className="w-14 h-14 bg-slate-200 dark:bg-slate-800 rounded-2xl shrink-0" />
                    <div className="space-y-2 flex-1">
                      <div className="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/3" />
                      <div className="h-3 bg-slate-100 dark:bg-slate-800/50 rounded w-1/2" />
                      <div className="h-3 bg-slate-100 dark:bg-slate-800/50 rounded w-1/4" />
                    </div>
                  </td>
                  <td className="px-8 py-6">
                    <div className="h-6 bg-slate-150 dark:bg-slate-800 rounded-[0.75rem] w-24 mb-1.5" />
                    <div className="h-3 bg-slate-100 dark:bg-slate-850 rounded w-32" />
                  </td>
                  <td className="px-8 py-6">
                    <div className="h-5 bg-slate-150 dark:bg-slate-800 rounded-full w-28 mb-1.5" />
                    <div className="h-3 bg-slate-100 dark:bg-slate-850 rounded w-20" />
                  </td>
                  <td className="px-8 py-6 text-right">
                    <div className="flex justify-end gap-2">
                      <div className="h-10 w-10 bg-slate-200 dark:bg-slate-800 rounded-xl" />
                      <div className="h-10 w-10 bg-slate-200 dark:bg-slate-800 rounded-xl" />
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        
        {/* Skeleton Pagination */}
        <div className="flex items-center justify-between px-6 py-5 border-t border-slate-50 dark:border-slate-800">
          <div className="h-9 bg-slate-200 dark:bg-slate-800 rounded-2xl w-40" />
          <div className="flex gap-2">
            <div className="h-10 bg-slate-200 dark:bg-slate-800 rounded-2xl w-16" />
            <div className="h-10 bg-slate-200 dark:bg-slate-800 rounded-2xl w-16" />
          </div>
        </div>
      </div>
    );
  }

  // --- PAGINATION PAGE LIST ENGINE ---
  const getPageNumbers = () => {
    const totalPages = employees.last_page;
    const currentPage = employees.current_page;
    const pages: (number | string)[] = [];

    if (totalPages <= 7) {
      for (let i = 1; i <= totalPages; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);
      if (currentPage > 3) {
        pages.push('...');
      }

      const start = Math.max(2, currentPage - 1);
      const end = Math.min(totalPages - 1, currentPage + 1);

      for (let i = start; i <= end; i++) {
        pages.push(i);
      }

      if (currentPage < totalPages - 2) {
        pages.push('...');
      }
      pages.push(totalPages);
    }
    return pages;
  };

  const allOnPageSelected = employees.data.length > 0 && employees.data.every(e => selectedIds.has(e.user_id));
  const someSelected = selectedIds.size > 0;

  return (
    <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl overflow-hidden relative transition-all duration-350">
      <div className="overflow-x-auto">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800">
              <th className="pl-6 pr-2 py-5 w-12">
                <label className="relative flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    checked={allOnPageSelected}
                    onChange={onSelectAll}
                    className="peer sr-only"
                  />
                  <div className={`w-5 h-5 rounded-lg border-2 transition-all flex items-center justify-center ${
                    allOnPageSelected 
                      ? 'bg-blue-600 border-blue-600' 
                      : someSelected 
                        ? 'bg-blue-600/40 border-blue-500' 
                        : 'border-slate-300 dark:border-slate-600 hover:border-blue-400'
                  }`}>
                    {(allOnPageSelected || someSelected) && (
                      <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
                        {allOnPageSelected 
                          ? <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                          : <path strokeLinecap="round" strokeLinejoin="round" d="M5 12h14" />
                        }
                      </svg>
                    )}
                  </div>
                </label>
              </th>
              <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Nhân viên</th>
              <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Phòng ban / Quyền</th>
              <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Trạng thái vận hành</th>
              <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-50 dark:divide-slate-800">
            {employees.data.length > 0 ? (
              employees.data.map((employee: Employee) => {
                const isSelected = selectedIds.has(employee.user_id);
                return (
                <tr 
                  key={employee.user_id} 
                  onClick={() => onOpenDrawer(employee)} 
                  className={`hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-all group duration-300 cursor-pointer ${
                    isSelected ? 'bg-blue-50/50 dark:bg-blue-900/20' : ''
                  }`}
                >
                  <td className="pl-6 pr-2 py-6" onClick={(e) => e.stopPropagation()}>
                    <label className="relative flex items-center cursor-pointer">
                      <input
                        type="checkbox"
                        checked={isSelected}
                        onChange={() => onSelectId(employee.user_id)}
                        className="peer sr-only"
                      />
                      <div className={`w-5 h-5 rounded-lg border-2 transition-all flex items-center justify-center ${
                        isSelected 
                          ? 'bg-blue-600 border-blue-600 shadow-sm shadow-blue-500/30' 
                          : 'border-slate-300 dark:border-slate-600 hover:border-blue-400'
                      }`}>
                        {isSelected && (
                          <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
                            <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                          </svg>
                        )}
                      </div>
                    </label>
                  </td>
                  <td className="px-8 py-6">
                    <div className="flex items-center gap-4">
                      <div className="w-14 h-14 rounded-[1.25rem] bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform duration-300">
                        {employee.full_name.substring(0, 2).toUpperCase()}
                      </div>
                      <div>
                        <div className="font-black text-slate-900 dark:text-white text-base leading-tight group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{employee.full_name}</div>
                        <div className="flex flex-col gap-1 mt-1.5">
                          <div className="flex items-center gap-1.5 text-sm text-slate-500 font-medium">
                            <Mail size={12} /> {employee.email}
                          </div>
                          <div className="flex items-center gap-1.5 text-sm text-slate-500 font-medium">
                            <Phone size={12} /> {employee.phone || <span className="opacity-50 italic text-xs">Chưa có SĐT</span>}
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td className="px-8 py-6">
                    <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold uppercase tracking-wider w-fit">
                      <Shield size={12} className="text-blue-500" /> {employee.role?.name || 'Chưa phân vai'}
                    </span>
                    <div className="text-[10px] text-slate-400 font-medium mt-1 ml-1">
                      {employee.role?.description || 'Quyền hạn cơ bản'}
                    </div>
                  </td>
                  <td className="px-8 py-6">
                    <button
                      onClick={(e) => { e.stopPropagation(); onToggleStatus(employee); }}
                      className={`inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-wider transition-all duration-300 active:scale-95 cursor-pointer ${
                        employee.status === 'Active'
                          ? 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 hover:text-rose-600'
                          : 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 hover:text-emerald-600'
                      }`}
                      title="Nhấp để thay đổi trạng thái"
                    >
                      {employee.status === 'Active' ? (
                        <>
                          <CheckCircle2 size={12} className="group-hover:scale-110 transition-transform" /> 
                          <span className="group-hover:inline hidden">Khóa TK</span>
                          <span className="group-hover:hidden inline">Đang hoạt động</span>
                        </>
                      ) : (
                        <>
                          <XCircle size={12} className="group-hover:scale-110 transition-transform" />
                          <span className="group-hover:inline hidden">Kích hoạt</span>
                          <span className="group-hover:hidden inline">Đang khóa</span>
                        </>
                      )}
                    </button>
                    <div className="flex items-center gap-1.5 mt-2 text-[10px] font-bold text-slate-400">
                      <Calendar size={12} /> Tham gia: {employee.created_at || 'Đang đồng bộ'}
                    </div>
                  </td>
                  <td className="px-8 py-6 text-right">
                    <div className="flex items-center justify-end gap-2">
                      <button
                        onClick={(e) => { e.stopPropagation(); onEdit(employee); }}
                        className="h-10 w-10 rounded-xl text-blue-600 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-600 hover:text-white transition-all shadow-sm flex items-center justify-center"
                        title="Chỉnh sửa thông tin"
                      >
                        <Edit size={16} />
                      </button>
                      <button
                        onClick={(e) => { e.stopPropagation(); onDelete(employee); }}
                        className="h-10 w-10 rounded-xl text-rose-600 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-600 hover:text-white transition-all shadow-sm flex items-center justify-center"
                        title="Xóa nhân viên"
                      >
                        <Trash2 size={16} />
                      </button>
                    </div>
                  </td>
                </tr>
              );})
            ) : (
              <tr>
                <td colSpan={5} className="px-8 py-20 text-center text-slate-400">
                  <div className="text-4xl mb-3 opacity-30">📦</div>
                  <p className="font-bold uppercase tracking-widest text-xs">Không tìm thấy nhân viên phù hợp</p>
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination Container */}
      <div className="flex flex-col sm:flex-row items-center justify-between px-6 py-5 gap-4 border-t border-slate-50 dark:border-slate-800">
        <div className="flex items-center gap-4 text-sm font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest bg-slate-50 dark:bg-slate-800/30 px-5 py-2.5 rounded-2xl border border-slate-100 dark:border-slate-800">
          <span>Hiển thị <span className="text-blue-600 font-black">{employees.from || 0} - {employees.to || 0}</span> trong tổng số <span className="text-slate-900 dark:text-white">{employees.total}</span></span>
          <div className="w-px h-3 bg-slate-200 dark:bg-slate-800" />
          <span>Trang <span className="text-blue-600">{employees.current_page}</span> / {employees.last_page}</span>
        </div>
        
        <div className="flex items-center gap-1.5 flex-wrap">
          {/* Nút Trước */}
          <button
            disabled={employees.current_page === 1}
            onClick={() => onPageChange(employees.current_page - 1)}
            className="h-10 px-4 font-black uppercase text-[9px] tracking-widest rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-all bg-white dark:bg-slate-900 cursor-pointer"
          >
            Trước
          </button>

          {/* Danh sách các số trang */}
          {getPageNumbers().map((p, idx) => {
            if (p === '...') {
              return (
                <span key={`dots-${idx}`} className="px-2 text-slate-400 select-none font-bold">
                  ...
                </span>
              );
            }
            const isCurrent = p === employees.current_page;
            return (
              <button
                key={`page-${p}`}
                onClick={() => onPageChange(p as number)}
                className={`h-10 w-10 flex items-center justify-center rounded-xl text-xs font-black transition-all cursor-pointer border active:scale-95 ${
                  isCurrent
                    ? 'bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-md shadow-blue-500/20 border-transparent'
                    : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'
                }`}
              >
                {p}
              </button>
            );
          })}

          {/* Nút Tiếp */}
          <button
            disabled={employees.current_page === employees.last_page}
            onClick={() => onPageChange(employees.current_page + 1)}
            className="h-10 px-4 font-black uppercase text-[9px] tracking-widest rounded-xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-all bg-white dark:bg-slate-900 cursor-pointer"
          >
            Tiếp
          </button>
        </div>
      </div>
    </div>
  );
}
