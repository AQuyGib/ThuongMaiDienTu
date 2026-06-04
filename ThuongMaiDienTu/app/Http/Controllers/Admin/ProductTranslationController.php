<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Http\Request;

/**
 * ==========================================
 * BỘ ĐIỀU HƯỚNG: ProductTranslationController
 * Ý NGHĨA NGHIỆP VỤ: Quản lý biên dịch thủ công nội dung sản phẩm sang tiếng Anh (Multilingual Product).
 * 
 * - Để phục vụ khách hàng nước ngoài hoặc xuất khẩu sản phẩm, hệ thống hỗ trợ hiển thị giao diện đa ngôn ngữ (Tiếng Việt và Tiếng Anh).
 * - Bộ điều hướng này giúp Admin trực tiếp nhập tên sản phẩm, mô tả sản phẩm và mô tả SEO bằng tiếng Anh cho từng sản phẩm cụ thể.
 * ==========================================
 */
class ProductTranslationController extends Controller
{
    /**
     * ==========================================
     * HÀM: edit (Product $product)
     * Ý NGHĨA NGHIỆP VỤ: Hiển thị trang chỉnh sửa bản dịch tiếng Anh cho sản phẩm.
     * 
     * 1. Khi Admin bấm nút dịch tiếng Anh cho một sản phẩm nào đó, hệ thống sẽ mở ra một biểu mẫu nhập liệu.
     * 2. Hệ thống tìm xem sản phẩm đó đã từng có bản dịch tiếng Anh nào được lưu chưa.
     * 3. Nếu đã có thì điền sẵn vào biểu mẫu để sửa đổi, nếu chưa có thì hiển thị biểu mẫu trống để điền mới.
     * ==========================================
     */
    public function edit(Product $product)
    {
        $translation = ProductTranslation::query()->firstOrNew([
            'product_id' => $product->product_id,
            'locale' => 'en',
        ]);

        return view('admin.products.translation-edit', compact('product', 'translation'));
    }

    /**
     * ==========================================
     * HÀM: update (Request $request, Product $product)
     * Ý NGHĨA NGHIỆP VỤ: Lưu lại hoặc cập nhật thông tin bản dịch tiếng Anh của sản phẩm vào cơ sở dữ liệu.
     * 
     * 1. Nhận dữ liệu tiếng Anh do Admin gửi lên từ biểu mẫu (gồm tên sản phẩm tiếng Anh, mô tả, thông tin SEO).
     * 2. Kiểm tra dữ liệu: Yêu cầu bắt buộc phải có tên sản phẩm tiếng Anh, các trường khác có thể bỏ trống.
     * 3. Thực hiện lưu trữ:
     *    - Nếu sản phẩm này chưa từng có bản dịch tiếng Anh, hệ thống sẽ tạo mới một bản ghi ngôn ngữ tiếng Anh ("en").
     *    - Nếu đã có bản dịch tiếng Anh trước đó, hệ thống sẽ ghi đè thông tin mới lên bản dịch cũ.
     * 4. Trở lại trang chỉnh sửa kèm theo thông báo "Đã lưu bản dịch thành công" để Admin biết kết quả.
     * ==========================================
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'seo_description' => ['nullable', 'string'],
        ]);

        ProductTranslation::updateOrCreate(
            [
                'product_id' => $product->product_id,
                'locale' => 'en',
            ],
            $data + [
                'product_id' => $product->product_id,
                'locale' => 'en',
            ]
        );

        return back()->with('success', 'Đã lưu bản dịch EN thủ công cho sản phẩm.');
    }
}

