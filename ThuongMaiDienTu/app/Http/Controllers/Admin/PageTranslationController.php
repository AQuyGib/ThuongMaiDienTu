<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\Request;

/**
 * ==========================================
 * BỘ ĐIỀU HƯỚNG: PageTranslationController
 * Ý NGHĨA NGHIỆP VỤ: Quản lý biên dịch thủ công nội dung các trang tĩnh (Chính sách, Giới thiệu) sang tiếng Anh.
 * 
 * - Đối với các trang tĩnh như trang điều khoản dịch vụ, chính sách bảo mật, chính sách đổi trả, giới thiệu...
 *   Admin có thể chủ động biên dịch toàn bộ tiêu đề, tóm tắt, nội dung chi tiết và các thẻ meta SEO sang tiếng Anh
 *   để người dùng nước ngoài đọc hiểu chính xác nhất.
 * ==========================================
 */
class PageTranslationController extends Controller
{
    /**
     * ==========================================
     * HÀM: edit (Page $page)
     * Ý NGHĨA NGHIỆP VỤ: Hiển thị giao diện soạn thảo bản dịch tiếng Anh cho trang tĩnh.
     * 
     * 1. Admin bấm nút chỉnh sửa bản dịch tiếng Anh của một trang cụ thể.
     * 2. Hệ thống tìm kiếm bản dịch cũ (nếu có) hoặc khởi tạo một đối tượng bản dịch mới.
     * 3. Trả về giao diện nhập liệu bản dịch tiếng Anh tương ứng.
     * ==========================================
     */
    public function edit(Page $page)
    {
        $translation = PageTranslation::query()->firstOrNew([
            'page_id' => $page->page_id,
            'locale' => 'en',
        ]);

        return view('admin.pages.translation-edit', compact('page', 'translation'));
    }

    /**
     * ==========================================
     * HÀM: update (Request $request, Page $page)
     * Ý NGHĨA NGHIỆP VỤ: Lưu lại dữ liệu bản dịch tiếng Anh của trang tĩnh.
     * 
     * 1. Nhận thông tin biên dịch mới (Tiêu đề tiếng Anh, nội dung tóm tắt, nội dung chi tiết, tiêu đề SEO, mô tả SEO tiếng Anh).
     * 2. Kiểm tra tính hợp lệ (tiêu đề trang tiếng Anh là bắt buộc).
     * 3. Thực hiện lưu trữ: Tạo mới bản dịch tiếng Anh hoặc cập nhật đè lên bản dịch cũ.
     * 4. Trả về trang cũ kèm theo thông báo lưu thành công.
     * ==========================================
     */
    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);

        PageTranslation::updateOrCreate(
            [
                'page_id' => $page->page_id,
                'locale' => 'en',
            ],
            $data + [
                'page_id' => $page->page_id,
                'locale' => 'en',
            ]
        );

        return back()->with('success', 'Đã lưu bản dịch EN thủ công cho trang.');
    }
}

