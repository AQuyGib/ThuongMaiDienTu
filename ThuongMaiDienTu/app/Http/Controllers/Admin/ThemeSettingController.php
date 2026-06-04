<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ThemeSettingController extends Controller
{
    /**
     * Hiển thị trang cấu hình giao diện.
     */
    public function index()
    {
        $settings = Setting::pluck('setting_value', 'setting_key')->toArray();
        $props = [
            'settings' => $settings,
            'asset_url' => asset('')
        ];

        if (request()->wantsJson()) {
            return response()->json($props);
        }

        return view('admin.settings.theme', compact('props'));
    }

    /**
     * Cập nhật các cấu hình giao diện.
     */
    public function update(Request $request)
    {
        $data = $request->except('_token');

        // Xử lý encode JSON cho các mảng động (ví dụ: social_links)
        if ($request->has('social_links')) {
            $data['social_links'] = json_decode(json_encode($request->social_links), false); // Đảm bảo format sạch
            $data['social_links'] = json_encode($data['social_links']);
        }

        // Xử lý xóa logo nếu có yêu cầu
        if ($request->remove_logo == '1') {
            $data['logo'] = null;
        }
        unset($data['remove_logo']);

        // Xử lý xóa banner nếu có yêu cầu
        if ($request->remove_banner_1 == '1') {
            $data['banner_1'] = null;
        }
        unset($data['remove_banner_1']);

        foreach ($data as $key => $value) {
            // Xử lý upload ảnh (Logo, Banner, v.v.)
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = $key . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/theme'), $filename);
                $value = 'uploads/theme/' . $filename;
            }

            // Nếu giá trị là null (trường hợp checkbox hoặc xóa text), lưu chuỗi rỗng hoặc null tùy ý
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        // Xóa cache để dữ liệu mới có hiệu lực ngay
        \Illuminate\Support\Facades\Cache::forget('settings');

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật cấu hình giao diện thành công!',
                'settings' => Setting::pluck('setting_value', 'setting_key')->toArray()
            ]);
        }

        return redirect()->back()->with('success', 'Đã cập nhật cấu hình giao diện thành công!');
    }

    /**
     * Khôi phục cài đặt mặc định (Xóa tất cả settings).
     */
    public function reset()
    {
        Setting::all()->each->delete();
        return redirect()->back()->with('success', 'Đã khôi phục cài đặt mặc định thành công!');
    }
}
