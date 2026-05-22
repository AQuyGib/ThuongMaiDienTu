<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền thực hiện yêu cầu này hay không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho yêu cầu.
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        // Trích xuất user_id nếu $employee là một instance của Model User
        $employeeId = $employee instanceof \App\Models\User ? $employee->user_id : $employee;

        return [
            'full_name' => 'required|string|max:50',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($employeeId, 'user_id'),
            ],
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,role_id|not_in:3', // 3 là role_id của Khách hàng, nhân viên không thể có vai trò này
            'status' => 'required|in:Active,Banned',
            'version' => 'required|integer', // Dùng cho Optimistic Locking
            'last_updated_at' => 'required|string', // Mốc thời gian cập nhật cuối cùng
        ];
    }

    /**
     * Lấy thông báo lỗi tùy chỉnh cho các quy tắc đã xác định.
     */
    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ và tên nhân viên.',
            'full_name.max' => 'Họ và tên không được vượt quá 50 ký tự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'email.max' => 'Email không được vượt quá 100 ký tự.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng bởi tài khoản khác.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'password.min' => 'Mật khẩu cập nhật phải có ít nhất 8 ký tự.',
            'role_id.required' => 'Vui lòng chọn vai trò làm việc.',
            'role_id.exists' => 'Vai trò được chọn không hợp lệ.',
            'role_id.not_in' => 'Không thể gán vai trò Khách hàng cho nhân viên.',
            'status.required' => 'Vui lòng chọn trạng thái vận hành.',
            'status.in' => 'Trạng thái vận hành không hợp lệ.',
            'version.required' => 'Thiếu thông tin phiên bản (version) để thực hiện cập nhật an toàn.',
            'version.integer' => 'Phiên bản không hợp lệ.',
            'last_updated_at.required' => 'Thiếu mốc thời gian cập nhật cuối cùng (last_updated_at) để kiểm tra xung đột.',
        ];
    }

    /**
     * Xử lý khi xác thực Form thất bại: Trả về mã lỗi 422 JSON chuẩn hóa.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'message' => 'Dữ liệu nhập vào không hợp lệ.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
