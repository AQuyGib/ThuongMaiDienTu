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
  };
  isLoading: boolean;
  onEdit: (employee: Employee) => void;
  onDelete: (employee: Employee) => void;
  onPageChange: (page: number) => void;
}

export default function EmployeeTable({ employees, isLoading, onEdit, onDelete, onPageChange }: EmployeeTableProps) {
  
  // --- PULSING SKELETON COMPONENT FOR PREMIUM LOADING ---
  if (isLoading) {
    return (
      <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl overflow-hidden animate-pulse">
        <div className="overflow-x-auto">
          <table className="w-full text-left border-collapse">
            <thead>
              <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800">
                <th className="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Nhân viên</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Vai trò</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest">Trạng thái</th>
                <th className="px-8 py-5 text-xs font-bold text-slate-400 uppercase tracking-widest text-right">Thao tác</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-50 dark:divide-slate-800">
              {[...Array(5)].map((_, idx) => (
                <tr key={idx} className="border-b border-slate-50 dark:border-slate-800">
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

  return (
    <div className="bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-2xl overflow-hidden relative transition-all duration-350">
      <div className="overflow-x-auto">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-slate-50/80 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-800">
              <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Nhân viên</th>
              <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Phòng ban / Quyền</th>
              <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Trạng thái vận hành</th>
              <th className="px-8 py-5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest text-right">Thao tác</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-50 dark:divide-slate-800">
            {employees.data.length > 0 ? (
              employees.data.map((employee: Employee) => (
                <tr key={employee.user_id} className="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-all group duration-300">
                  <td className="px-8 py-6">
                    <div className="flex items-center gap-4">
                      <div className="w-14 h-14 rounded-[1.25rem] bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-black text-lg shadow-lg shadow-blue-500/20 group-hover:scale-110 transition-transform duration-300">
                        {employee.full_name.substring(0, 2).toUpperCase()}
                      </div>
                      <div>
                        <div className="font-black text-slate-900 dark:text-white text-base leading-tight">{employee.full_name}</div>
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
                    {employee.status === 'Active' ? (
                      <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 text-[10px] font-black uppercase tracking-wider">
                        <CheckCircle2 size={12} /> Đang làm việc
                      </span>
                    ) : (
                      <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-50 dark:bg-rose-950/20 text-rose-600 text-[10px] font-black uppercase tracking-wider">
                        <XCircle size={12} /> Đã khóa
                      </span>
                    )}
                    <div className="flex items-center gap-1.5 mt-2 text-[10px] font-bold text-slate-400">
                      <Calendar size={12} /> Tham gia: {employee.created_at || 'Đang đồng bộ'}
                    </div>
                  </td>
                  <td className="px-8 py-6 text-right">
                    <div className="flex items-center justify-end gap-2">
                      <button
                        onClick={() => onEdit(employee)}
                        className="h-10 w-10 rounded-xl text-blue-600 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-600 hover:text-white transition-all shadow-sm flex items-center justify-center"
                        title="Chỉnh sửa thông tin"
                      >
                        <Edit size={16} />
                      </button>
                      <button
                        onClick={() => onDelete(employee)}
                        className="h-10 w-10 rounded-xl text-rose-600 bg-rose-50 dark:bg-rose-900/20 hover:bg-rose-600 hover:text-white transition-all shadow-sm flex items-center justify-center"
                        title="Xóa nhân viên"
                      >
                        <Trash2 size={16} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td colSpan={4} className="px-8 py-20 text-center text-slate-400">
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
          <span>Tổng số: <span className="text-slate-900 dark:text-white">{employees.total}</span></span>
          <div className="w-px h-3 bg-slate-200 dark:bg-slate-800" />
          <span>Trang <span className="text-blue-600">{employees.current_page}</span> / {employees.last_page}</span>
        </div>
        <div className="flex items-center gap-2">
          <button
            disabled={employees.current_page === 1}
            onClick={() => onPageChange(employees.current_page - 1)}
            className="h-11 px-5 font-black uppercase text-[10px] tracking-widest rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-all bg-white dark:bg-slate-900"
          >
            Trước
          </button>
          <button
            disabled={employees.current_page === employees.last_page}
            onClick={() => onPageChange(employees.current_page + 1)}
            className="h-11 px-5 font-black uppercase text-[10px] tracking-widest rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-all bg-white dark:bg-slate-900"
          >
            Tiếp
          </button>
        </div>
      </div>
    </div>
  );
}
