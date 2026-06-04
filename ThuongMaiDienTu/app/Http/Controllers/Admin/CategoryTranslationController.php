<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Http\Request;

/**
 * ==========================================
 * BỘ ĐIỀU HƯỚNG: CategoryTranslationController
 * Ý NGHĨA NGHIỆP VỤ: Quản lý biên dịch thủ công thông tin danh mục sản phẩm sang tiếng Anh.
 * 
 * - Để phục vụ việc hiển thị giao diện đa ngôn ngữ, hệ thống hỗ trợ Admin dịch tên danh mục (ví dụ "Điện thoại" thành "Phones")
 *   và mô tả danh mục sang tiếng Anh để hiển thị tương ứng ở trang ngoài cho người dùng chọn ngôn ngữ tiếng Anh.
 * ==========================================
 */
class CategoryTranslationController extends Controller
{
    /**
     * ==========================================
     * HÀM: edit (Category $category)
     * Ý NGHĨA NGHIỆP VỤ: Hiển thị giao diện biên dịch thông tin danh mục.
     * 
     * 1. Khi Admin nhấn vào chức năng biên dịch của một danh mục sản phẩm cụ thể.
     * 2. Hệ thống tìm kiếm bản ghi ngôn ngữ tiếng Anh ("en") đã được tạo trước đó cho danh mục này.
     * 3. Trả về giao diện điền thông tin bản dịch kèm dữ liệu danh mục hiện tại.
     * ==========================================
     */
    public function edit(Category $category)
    {
        $translation = CategoryTranslation::query()->firstOrNew([
            'category_id' => $category->category_id,
            'locale' => 'en',
        ]);

        return view('admin.categories.translation-edit', compact('category', 'translation'));
    }

    /**
     * ==========================================
     * HÀM: update (Request $request, Category $category)
     * Ý NGHĨA NGHIỆP VỤ: Lưu lại dữ liệu bản dịch tiếng Anh của danh mục.
     * 
     * 1. Tiếp nhận thông tin bản dịch được Admin gửi lên từ biểu mẫu chỉnh sửa.
     * 2. Thực hiện kiểm tra tính hợp lệ của dữ liệu (tên danh mục tiếng Anh bắt buộc phải điền).
     * 3. Thực hiện lưu vào cơ sở dữ liệu:
     *    - Nếu chưa từng được dịch, hệ thống sẽ tạo mới bản ghi dịch thuật tiếng Anh ("en").
     *    - Nếu đã được dịch từ trước, hệ thống sẽ tự động cập nhật đè lên thông tin cũ.
     * 4. Trở lại trang trước đó kèm theo thông báo thành công cho người quản trị.
     * ==========================================
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        CategoryTranslation::updateOrCreate(
            [
                'category_id' => $category->category_id,
                'locale' => 'en',
            ],
            $data + [
                'category_id' => $category->category_id,
                'locale' => 'en',
            ]
        );

        return back()->with('success', 'Đã lưu bản dịch EN thủ công cho danh mục.');
    }
}

