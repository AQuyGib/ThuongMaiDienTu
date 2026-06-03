<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    /**
     * Xác định xem người dùng có thể xem danh sách nhân sự hay không.
     */
    public function viewAny(User $user): bool
    {
        // Cho phép Admin (1) và Quản lý (2) xem danh sách nhân viên
        return in_array($user->role_id, [1, 2]);
    }

    /**
     * Xác định xem người dùng có thể thêm mới nhân viên hay không.
     */
    public function create(User $user): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('Chỉ có Quản trị viên hệ thống (Admin) mới có quyền khởi tạo nhân viên mới.');
    }

    /**
     * Xác định xem người dùng có thể chỉnh sửa thông tin nhân viên hay không.
     */
    public function update(User $user, User $employee): Response
    {
        return $user->role_id === 1
            ? Response::allow()
            : Response::deny('Chỉ có Quản trị viên hệ thống (Admin) mới có quyền chỉnh sửa nhân viên.');
    }

    /**
     * Xác định xem người dùng có thể xóa mềm nhân viên hay không.
     */
    public function delete(User $user, User $employee): Response
    {
        // Phải là Admin mới có quyền xóa
        if ($user->role_id !== 1) {
            return Response::deny('Chỉ có Quản trị viên hệ thống (Admin) mới có quyền xóa nhân viên.');
        }

        // Tuyệt đối chặn hành vi tự xóa chính mình
        if ($user->user_id === $employee->user_id) {
            return Response::deny('Bạn không thể tự xóa chính tài khoản đang đăng nhập!');
        }

        return Response::allow();
    }
}
