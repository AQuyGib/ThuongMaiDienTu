import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import axios from 'axios';
import toast from 'react-hot-toast';
import { X, User, Mail, Phone, Shield, ShieldAlert, Eye, EyeOff, Loader2, CheckCircle2, XCircle, Clock } from 'lucide-react';

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

interface EmployeeModalFormProps {
  employee: Employee | null;
  roles: Role[];
  isOpen: boolean;
  onClose: () => void;
  onSave: (savedEmployee: Employee, isEditMode: boolean) => void;
}

// --- CLIENT-SIDE ZOD VALIDATION SCHEMA ---
const createEmployeeSchema = (isEdit: boolean) => z.object({
  full_name: z
    .string()
    .min(2, 'Họ tên nhân viên phải có ít nhất 2 ký tự')
    .max(50, 'Họ tên tối đa 50 ký tự'),
  email: z
    .string()
    .min(1, 'Vui lòng nhập địa chỉ email')
    .email('Định dạng địa chỉ email không hợp lệ')
    .max(100, 'Email tối đa 100 ký tự'),
  phone: z
    .string()
    .min(10, 'Số điện thoại phải chứa ít nhất 10 chữ số')
    .max(15, 'Số điện thoại không được vượt quá 15 chữ số')
    .regex(/^[0-9]+$/, 'Số điện thoại chỉ được chứa ký số (0-9)'),
  role_id: z.string().min(1, 'Vui lòng chọn vai trò làm việc'),
  status: z.enum(['Active', 'Banned']),
  password: isEdit
    ? z.string().optional().refine(val => !val || val.length >= 8, 'Mật khẩu mới nếu cập nhật phải có ít nhất 8 ký tự')
    : z.string().min(8, 'Mật khẩu tài khoản phải chứa ít nhất 8 ký tự'),
  password_confirmation: z.string().optional()
}).refine(data => {
  if (data.password && data.password !== data.password_confirmation) {
    return false;
  }
  return true;
}, {
  message: 'Xác nhận mật khẩu mới không trùng khớp',
  path: ['password_confirmation']
});

type EmployeeFormData = z.infer<ReturnType<typeof createEmployeeSchema>>;

export default function EmployeeModalForm({ employee, roles, isOpen, onClose, onSave }: EmployeeModalFormProps) {
  const isEdit = !!employee;
  const [saving, setSaving] = useState(false);
  const [showPass, setShowPass] = useState(false);

  // Mốc thời gian cập nhật cuối cùng ban đầu (last_updated_at) dùng cho Khóa Lạc Quan (Optimistic Locking)
  const [lastUpdatedAt, setLastUpdatedAt] = useState<string>(employee?.updated_at || '');
  // State lưu trữ bản ghi xung đột mới nhất tải về từ server
  const [conflictData, setConflictData] = useState<Employee | null>(null);

  const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

  // --- INITIALIZE REACT HOOK FORM WITH ZOD RESOLVER ---
  const { register, handleSubmit, formState: { errors }, setError, setValue } = useForm<EmployeeFormData>({
    resolver: zodResolver(createEmployeeSchema(isEdit)),
    defaultValues: {
      full_name: employee?.full_name || '',
      email: employee?.email || '',
      phone: employee?.phone || '',
      role_id: employee?.role_id?.toString() || '4', // Mặc định là 4 (Nhân viên) khi thêm mới
      status: employee?.status || 'Active',
      password: '',
      password_confirmation: ''
    }
  });

  // --- HÀM NẠP LẠI DỮ LIỆU MỚI NHẤT TỪ MÁY CHỦ KHI XẢY RA XUNG ĐỘT (409) ---
  const handleReloadLatest = () => {
    if (!conflictData) return;

    // Nạp đè các giá trị mới từ server vào form
    setValue('full_name', conflictData.full_name);
    setValue('email', conflictData.email);
    setValue('phone', conflictData.phone || '');
    setValue('role_id', conflictData.role_id.toString());
    setValue('status', conflictData.status);

    // Cập nhật mốc thời gian cập nhật mới nhất để gửi lên thành công ở lần bấm lưu tiếp theo
    setLastUpdatedAt(conflictData.updated_at || '');

    // Ẩn bảng cảnh báo xung đột
    setConflictData(null);
    toast.success('🔄 Đã tải dữ liệu mới nhất từ máy chủ! Bạn có thể xem và lưu lại.');
  };

  const onSubmit = async (data: EmployeeFormData) => {
    // 1. Chống Double-Submit
    setSaving(true);

    // Chuẩn bị dữ liệu form
    const formData = new FormData();
    formData.append('_token', csrfToken || '');
    formData.append('full_name', data.full_name);
    formData.append('email', data.email);
    formData.append('phone', data.phone);
    formData.append('role_id', data.role_id);
    formData.append('status', data.status);

    if (data.password) {
      formData.append('password', data.password);
      formData.append('password_confirmation', data.password_confirmation || '');
    }

    if (isEdit && employee) {
      formData.append('_method', 'PUT');
      formData.append('version', (employee.version || 1).toString());
      formData.append('last_updated_at', lastUpdatedAt); // Gửi kèm thời gian cập nhật để phát hiện xung đột dữ liệu đồng thời
    }

    try {
      const url = isEdit ? `/admin/employees/${employee?.user_id}` : '/admin/employees';
      const response = await axios.post(url, formData, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Content-Type': 'multipart/form-data'
        }
      });

      if (response.data.success) {
        toast.success(response.data.message || 'Đã lưu thông tin nhân viên thành công!');
        onSave(response.data.employee, isEdit);
      } else {
        throw new Error(response.data.message);
      }
    } catch (error: any) {
      const status = error.response?.status;

      if (status === 404) {
        // Tình huống 1: Nhân viên đã bị xóa bởi quản trị viên khác
        toast.error('❌ Nhân viên này đã bị xóa bởi quản trị viên khác! Hệ thống đang tự động tải lại...');
        onClose(); // Tự động đóng Form
        if (employee) {
          onSave(employee, true); // Gọi hàm để re-fetch làm mới bảng
        }
        // Tự động tải lại toàn bộ trang sau 1.5 giây để đồng bộ dữ liệu tuyệt đối
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else if (status === 409) {
        // Tình huống 2: Xung đột dữ liệu đồng thời (Race Condition)
        const latest = error.response?.data?.latest_employee;
        if (latest) {
          setConflictData(latest);
        }
        toast.error(error.response?.data?.message || '⚠️ Dữ liệu đã bị thay đổi bởi người khác kể từ lúc bạn mở trang.');
      } else if (status === 422 && error.response?.data?.errors) {
        // Bắt lỗi validate form thông thường
        const serverErrors = error.response.data.errors;
        Object.keys(serverErrors).forEach((key) => {
          setError(key as any, {
            type: 'server',
            message: serverErrors[key][0]
          });
        });
        toast.error('⚠️ Dữ liệu không hợp lệ! Vui lòng kiểm tra lại các trường báo đỏ.');
      } else {
        toast.error(error.response?.data?.message || error.message || 'Đã xảy ra lỗi khi lưu thông tin nhân viên.');
      }
    } finally {
      // Cho phép bấm nút lại nếu có lỗi xảy ra
      setSaving(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-300 overflow-y-auto">
      <div className="bg-white dark:bg-slate-900 w-full max-w-5xl rounded-[2.5rem] shadow-2xl border border-slate-100 dark:border-slate-800 overflow-hidden animate-in zoom-in-95 duration-500 flex flex-col my-8">

        {/* Header Modal */}
        <div className="px-10 py-8 shrink-0 bg-gradient-to-r from-slate-50 to-white dark:from-slate-900 dark:to-slate-800 border-b border-slate-100 dark:border-slate-800 relative overflow-hidden">
          <div className="absolute right-0 top-0 w-80 h-80 bg-blue-500/5 rounded-full blur-[80px] -mr-40 -mt-40" />
          <div className="relative z-10 flex items-center justify-between">
            <div className="flex items-center gap-5">
              <div className="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-xl shadow-blue-600/20">
                {isEdit ? <Loader2 className="animate-spin" size={26} style={{ display: saving ? 'block' : 'none' }} /> : null}
                {!saving && (isEdit ? <Loader2 className="hidden" /> : null)}
                {!saving && (isEdit ? <User size={26} /> : <User size={26} />)}
              </div>
              <div>
                <h2 className="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                  {isEdit ? 'Chỉnh sửa' : 'Thêm mới'} Nhân viên
                </h2>
                <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Hồ sơ nghiệp vụ nhân sự hệ thống POS</p>
              </div>
            </div>
            <button
              onClick={onClose}
              disabled={saving}
              className="w-12 h-12 flex items-center justify-center rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 shadow-sm transition-all disabled:opacity-50"
            >
              <X size={22} />
            </button>
          </div>
        </div>

        {/* Form Body */}
        <form onSubmit={handleSubmit(onSubmit)} className="p-10 space-y-8 max-h-[70vh] overflow-y-auto custom-scrollbar" autoComplete="off">
          {/* Hộp Cảnh báo Xung Đột Dữ Liệu Đồng Thời (Optimistic Locking) */}
          {conflictData && (
            <div className="p-6 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900 rounded-3xl animate-in slide-in-from-top duration-300">
              <div className="flex gap-4 items-start">
                <div className="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                  <ShieldAlert size={24} />
                </div>
                <div className="flex-1 space-y-2">
                  <h5 className="font-black text-amber-900 dark:text-amber-300 text-sm">⚠️ Phát hiện xung đột dữ liệu đồng thời!</h5>
                  <p className="text-xs font-bold text-amber-700 dark:text-amber-400 leading-relaxed">
                    Hồ sơ nhân sự này đã được thay đổi bởi một người khác kể từ lúc bạn mở trang. Vui lòng nạp lại dữ liệu mới nhất từ máy chủ để đối chiếu trước khi lưu.
                  </p>
                  <div className="pt-2 flex gap-3">
                    <button
                      type="button"
                      onClick={handleReloadLatest}
                      className="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-[10px] uppercase tracking-wider rounded-xl transition-all shadow-md shadow-amber-600/10"
                    >
                      Nạp lại dữ liệu mới nhất
                    </button>
                    <button
                      type="button"
                      onClick={() => setConflictData(null)}
                      className="px-4 py-2 border border-amber-300 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-amber-950/20 text-amber-700 dark:text-amber-400 font-bold text-[10px] uppercase tracking-wider rounded-xl transition-all"
                    >
                      Bỏ qua
                    </button>
                  </div>
                </div>
              </div>
            </div>
          )}

          <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {/* Cột 1: Thông tin cơ bản */}
            <div className="space-y-6">
              <h4 className="text-xs font-black text-blue-600 uppercase tracking-widest mb-4">Thông tin cơ bản</h4>

              {/* Tên nhân viên */}
              <div className="space-y-2">
                <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Họ và tên nhân viên</label>
                <div className="relative group">
                  <div className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                    <User size={18} />
                  </div>
                  <input
                    {...register('full_name')}
                    disabled={saving}
                    className={`w-full pl-12 pr-4 h-12 bg-slate-50 dark:bg-slate-800 border ${errors.full_name ? 'border-rose-500 focus:border-rose-500' : 'border-slate-100 dark:border-slate-700 focus:border-blue-500'} focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all disabled:opacity-50`}
                    placeholder="VD: Nguyễn Văn Tráng..."
                  />
                </div>
                {errors.full_name && (
                  <p className="text-xs text-rose-500 font-bold ml-1 animate-in fade-in slide-in-from-top-1 duration-200">{errors.full_name.message}</p>
                )}
              </div>

              {/* Email */}
              <div className="space-y-2">
                <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Địa chỉ Email</label>
                <div className="relative group">
                  <div className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                    <Mail size={18} />
                  </div>
                  <input
                    {...register('email')}
                    type="email"
                    autoComplete="new-password"
                    disabled={saving}
                    className={`w-full pl-12 pr-4 h-12 bg-slate-50 dark:bg-slate-800 border ${errors.email ? 'border-rose-500 focus:border-rose-500' : 'border-slate-100 dark:border-slate-700 focus:border-blue-500'} focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all disabled:opacity-50`}
                    placeholder="example@dienmaypro.vn"
                  />
                </div>
                {errors.email && (
                  <p className="text-xs text-rose-500 font-bold ml-1 animate-in fade-in slide-in-from-top-1 duration-200">{errors.email.message}</p>
                )}
              </div>

              {/* SĐT */}
              <div className="space-y-2">
                <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Số điện thoại</label>
                <div className="relative group">
                  <div className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-blue-500 transition-colors">
                    <Phone size={18} />
                  </div>
                  <input
                    {...register('phone')}
                    disabled={saving}
                    className={`w-full pl-12 pr-4 h-12 bg-slate-50 dark:bg-slate-800 border ${errors.phone ? 'border-rose-500 focus:border-rose-500' : 'border-slate-100 dark:border-slate-700 focus:border-blue-500'} focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all disabled:opacity-50`}
                    placeholder="09xxxxxxxx"
                  />
                </div>
                {errors.phone && (
                  <p className="text-xs text-rose-500 font-bold ml-1 animate-in fade-in slide-in-from-top-1 duration-200">{errors.phone.message}</p>
                )}
              </div>
            </div>

            {/* Cột 2: Thiết lập hệ thống */}
            <div className="space-y-6">
              <h4 className="text-xs font-black text-indigo-600 uppercase tracking-widest mb-4">Cấu hình làm việc</h4>

              {/* Password */}
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Mật khẩu tài khoản</label>
                  <div className="relative">
                    <input
                      {...register('password')}
                      type={showPass ? "text" : "password"}
                      autoComplete="new-password"
                      disabled={saving}
                      className={`w-full px-4 h-12 bg-slate-50 dark:bg-slate-800 border ${errors.password ? 'border-rose-500 focus:border-rose-500' : 'border-slate-100 dark:border-slate-700 focus:border-blue-500'} focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all pr-10 disabled:opacity-50`}
                      placeholder={isEdit ? "Để trống nếu không đổi" : "Tối thiểu 8 ký tự"}
                    />
                    <button type="button" onClick={() => setShowPass(!showPass)} className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-600">
                      {showPass ? <EyeOff size={16} /> : <Eye size={16} />}
                    </button>
                  </div>
                  {errors.password && (
                    <p className="text-xs text-rose-500 font-bold ml-1 animate-in fade-in slide-in-from-top-1 duration-200">{errors.password.message}</p>
                  )}
                </div>

                <div className="space-y-2">
                  <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Xác nhận mật khẩu</label>
                  <input
                    {...register('password_confirmation')}
                    type={showPass ? "text" : "password"}
                    disabled={saving}
                    className={`w-full px-4 h-12 bg-slate-50 dark:bg-slate-800 border ${errors.password_confirmation ? 'border-rose-500 focus:border-rose-500' : 'border-slate-100 dark:border-slate-700 focus:border-blue-500'} focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all disabled:opacity-50`}
                    placeholder={isEdit ? "Nhập lại mật khẩu mới" : "••••••••"}
                  />
                  {errors.password_confirmation && (
                    <p className="text-xs text-rose-500 font-bold ml-1 animate-in fade-in slide-in-from-top-1 duration-200">{errors.password_confirmation.message}</p>
                  )}
                </div>
              </div>

              {/* Vai trò */}
              <div className="space-y-2">
                <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Vai trò công việc</label>
                <div className="relative">
                  <select
                    {...register('role_id')}
                    disabled={saving || !isEdit}
                    className={`w-full px-4 h-12 border ${errors.role_id ? 'border-rose-500 focus:border-rose-500' : 'border-slate-100 dark:border-slate-700 focus:border-blue-500'} ${!isEdit ? 'bg-slate-100 dark:bg-slate-800/40 text-slate-400 cursor-not-allowed select-none' : 'bg-slate-50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-900 cursor-pointer'} rounded-2xl font-bold text-sm outline-none transition-all appearance-none disabled:opacity-80`}
                  >
                    {!isEdit ? (
                      <option value="4">Mặc định: Nhân viên</option>
                    ) : (
                      <>
                        <option value="">Chọn vai trò...</option>
                        {roles.map(r => (
                          <option key={r.role_id} value={r.role_id.toString()}>{r.name}</option>
                        ))}
                      </>
                    )}
                  </select>
                  <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <Shield size={16} />
                  </div>
                </div>
                {errors.role_id && (
                  <p className="text-xs text-rose-500 font-bold ml-1 animate-in fade-in slide-in-from-top-1 duration-200">{errors.role_id.message}</p>
                )}
              </div>

              {/* Trạng thái */}
              <div className="space-y-2">
                <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Trạng thái hoạt động</label>
                <div className="relative">
                  <select
                    {...register('status')}
                    disabled={saving}
                    className="w-full px-4 h-12 bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 focus:border-blue-500 focus:bg-white dark:focus:bg-slate-900 rounded-2xl font-bold text-sm outline-none transition-all appearance-none cursor-pointer disabled:opacity-50"
                  >
                    <option value="Active">Đang làm việc (Active)</option>
                    <option value="Banned">Tạm dừng vận hành (Banned)</option>
                  </select>
                  <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                    <Clock size={16} />
                  </div>
                </div>
              </div>
            </div>
          </div>

          {/* Form Footer */}
          <div className="pt-6 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-4 shrink-0">
            <button
              type="button"
              disabled={saving}
              className="px-6 h-12 rounded-2xl font-bold text-xs uppercase tracking-widest border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 transition-all text-slate-700 dark:text-slate-300 disabled:opacity-50"
              onClick={onClose}
            >
              Hủy bỏ
            </button>
            <button
              type="submit"
              disabled={saving}
              className="px-10 h-12 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold text-xs uppercase tracking-widest shadow-lg shadow-blue-500/10 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-2"
            >
              {saving ? <Loader2 className="animate-spin" size={16} /> : null}
              {saving ? 'Đang xử lý...' : (isEdit ? 'Lưu thay đổi' : 'Thêm nhân viên')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
