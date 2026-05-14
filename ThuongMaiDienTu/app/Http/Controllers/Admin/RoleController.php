<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Lưu vai trò mới.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:30|unique:roles,name',
            'description' => 'nullable|string|max:150',
            'permissions' => 'nullable|array',
        ], [
            'name.required' => 'Vui lòng nhập tên vai trò.',
            'name.unique' => 'Tên vai trò này đã tồn tại.',
        ]);

        $role = Role::create($validated);

        return response()->json([
            'message' => 'Tạo vai trò thành công!',
            'role' => $role
        ]);
    }

    /**
     * Cập nhật vai trò.
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:30|unique:roles,name,' . $role->role_id . ',role_id',
            'description' => 'nullable|string|max:150',
            'permissions' => 'nullable|array',
        ]);

        $role->update($validated);

        return response()->json([
            'message' => 'Cập nhật vai trò thành công!',
            'role' => $role
        ]);
    }

    /**
     * Xóa vai trò.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        // Kiểm tra xem có người dùng nào đang dùng vai trò này không
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Không thể xóa vai trò đang có người dùng sử dụng!'
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Xóa vai trò thành công!'
        ]);
    }
}
