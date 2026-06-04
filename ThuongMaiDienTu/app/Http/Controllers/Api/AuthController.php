<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Xử lý đăng nhập qua API (POST /api/v1/login)
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ], [
            'email.required' => app()->getLocale() === 'en' ? 'Email is required.' : 'Email là bắt buộc.',
            'email.email' => app()->getLocale() === 'en' ? 'Invalid email format.' : 'Email không đúng định dạng.',
            'password.required' => app()->getLocale() === 'en' ? 'Password is required.' : 'Mật khẩu là bắt buộc.',
            'password.min' => app()->getLocale() === 'en' ? 'Password must be at least 8 characters.' : 'Mật khẩu phải từ 8 ký tự trở lên.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => app()->getLocale() === 'en' ? 'Validation failed.' : 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
                'locale' => app()->getLocale()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'status' => 'error',
                'message' => app()->getLocale() === 'en' 
                    ? 'Incorrect email or password.' 
                    : 'Email hoặc mật khẩu không chính xác.',
                'locale' => app()->getLocale()
            ], 401);
        }

        // Kiểm tra xem tài khoản có bị khóa hay không
        if ($user->status === 'Banned') {
            return response()->json([
                'status' => 'error',
                'message' => app()->getLocale() === 'en' 
                    ? 'Your account has been banned.' 
                    : 'Tài khoản của bạn đã bị khóa.',
                'locale' => app()->getLocale()
            ], 403);
        }

        // Tạo Sanctum Personal Access Token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => app()->getLocale() === 'en' ? 'Logged in successfully.' : 'Đăng nhập thành công.',
            'token' => $token,
            'user' => new UserResource($user),
            'locale' => app()->getLocale()
        ], 200);
    }

    /**
     * Đăng xuất API - Xóa session (POST /api/v1/logout)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => app()->getLocale() === 'en' ? 'Logged out successfully.' : 'Đăng xuất thành công.',
            'locale' => app()->getLocale()
        ], 200);
    }

    /**
     * Lấy thông tin tài khoản hiện tại (GET /api/v1/me)
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => 'success',
            'user' => new UserResource($user),
            'locale' => app()->getLocale()
        ], 200);
    }
}
