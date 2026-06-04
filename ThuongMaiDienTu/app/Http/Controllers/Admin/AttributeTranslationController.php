<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeTranslation;
use Illuminate\Http\Request;

/**
 * ==========================================
 * BỘ ĐIỀU HƯỚNG: AttributeTranslationController
 * Ý NGHĨA NGHIỆP VỤ: Quản lý biên dịch thủ công thông tin thuộc tính sản phẩm sang tiếng Anh.
 * 
 * - Đối với các thuộc tính của sản phẩm (như "Màu sắc", "Dung lượng", "Kích thước"...), Admin có thể
 *   biên dịch tên thuộc tính và mô tả của nó sang tiếng Anh (ví dụ "Màu sắc" thành "Color", "Dung lượng" thành "Capacity")
 *   để bộ lọc tìm kiếm và trang chi tiết sản phẩm hiển thị chuẩn xác nhất khi chuyển ngôn ngữ.
 * ==========================================
 */
class AttributeTranslationController extends Controller
{
    /**
     * ==========================================
     * HÀM: edit (Attribute $attribute)
     * Ý NGHĨA NGHIỆP VỤ: Hiển thị giao diện biên dịch thông tin thuộc tính sản phẩm.
     * 
     * 1. Khi Admin nhấn biên dịch một thuộc tính sản phẩm cụ thể.
     * 2. Hệ thống tìm kiếm bản dịch tiếng Anh của thuộc tính đó.
     * 3. Trả về giao diện điền thông tin bản dịch tiếng Anh kèm dữ liệu thuộc tính hiện tại.
     * ==========================================
     */
    public function edit(Attribute $attribute)
    {
        $translation = AttributeTranslation::query()->firstOrNew([
            'attribute_id' => $attribute->attribute_id,
            'locale' => 'en',
        ]);

        return view('admin.attributes.translation-edit', compact('attribute', 'translation'));
    }

    /**
     * ==========================================
     * HÀM: update (Request $request, Attribute $attribute)
     * Ý NGHĨA NGHIỆP VỤ: Lưu lại dữ liệu bản dịch tiếng Anh của thuộc tính sản phẩm.
     * 
     * 1. Tiếp nhận dữ liệu bản dịch tiếng Anh được Admin gửi lên (tên thuộc tính tiếng Anh và mô tả tiếng Anh).
     * 2. Kiểm tra dữ liệu hợp lệ (yêu cầu điền tên thuộc tính tiếng Anh).
     * 3. Lưu vào cơ sở dữ liệu: Tạo mới bản dịch tiếng Anh hoặc cập nhật đè lên bản dịch cũ.
     * 4. Trả về trang cũ kèm theo thông báo thành công cho người quản trị.
     * ==========================================
     */
    public function update(Request $request, Attribute $attribute)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        AttributeTranslation::updateOrCreate(
            [
                'attribute_id' => $attribute->attribute_id,
                'locale' => 'en',
            ],
            $data + [
                'attribute_id' => $attribute->attribute_id,
                'locale' => 'en',
            ]
        );

        return back()->with('success', 'Đã lưu bản dịch EN thủ công cho thuộc tính.');
    }
}

