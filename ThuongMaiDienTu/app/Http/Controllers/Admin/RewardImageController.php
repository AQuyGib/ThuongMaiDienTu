<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RewardCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Class RewardImageController
 * Controller phụ trợ phục vụ thao tác tải lên và cập nhật nhanh ảnh đại diện của phần thưởng (Catalog Reward).
 */
class RewardImageController extends Controller
{
    /**
     * Cập nhật ảnh đại diện của một phần thưởng.
     * Logic hoạt động:
     * - Xác thực file tải lên (phải là ảnh, các định dạng JPG, PNG, WEBP, GIF và dung lượng <= 2MB).
     * - Lưu tệp tin mới vào disk 'public' trong thư mục 'rewards'.
     * - Kiểm tra và xóa tệp tin ảnh cũ (nếu có) trên ổ đĩa vật lý để tránh rác hệ thống.
     * - Cập nhật lại đường dẫn mới vào cả 2 cột image_path và thumbnail_path trong DB.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RewardCatalog  $reward
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, RewardCatalog $reward)
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ]);

        // Lưu ảnh mới tải lên vào thư mục public/rewards
        $path = $request->file('image')->store('rewards', 'public');

        // Nếu phần thưởng đã có ảnh cũ, xóa file ảnh cũ để giải phóng dung lượng máy chủ
        if ($reward->image_path) {
            Storage::disk('public')->delete($reward->image_path);
        }

        // Cập nhật đường dẫn ảnh mới vào database
        $reward->update([
            'image_path' => $path,
            'thumbnail_path' => $path,
        ]);

        return back()->with('success', 'Đã cập nhật ảnh reward.');
    }
}

