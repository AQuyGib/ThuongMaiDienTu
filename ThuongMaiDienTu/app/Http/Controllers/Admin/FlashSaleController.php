<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class FlashSaleController
 * 
 * Bộ điều khiển (Controller) quản lý các chương trình Flash Sale phía quản trị (Admin).
 * Hỗ trợ các chức năng: hiển thị danh sách chiến dịch, thêm mới chiến dịch, chỉnh sửa cấu hình thời gian/trạng thái và xóa chiến dịch.
 */
class FlashSaleController extends Controller
{
    /**
     * Hiển thị danh sách các chiến dịch Flash Sale.
     * Hỗ trợ nạp trước thông tin chiến dịch đang được chỉnh sửa nếu có tham số `edit` truyền vào URL.
     */
    public function index(Request $request)
    {
        // Lấy danh sách các chiến dịch Flash Sale, kèm số lượng sản phẩm tham gia trong mỗi chiến dịch
        $flashSales = FlashSale::withCount('products')
            ->orderByDesc('flash_sale_id')
            ->paginate(10);

        // Lấy toàn bộ danh sách sản phẩm để quản trị viên chọn đưa vào chiến dịch Flash Sale
        $products = Product::orderBy('name')->get();
        
        // Nếu có tham số `edit` trên URL, lấy chi tiết chiến dịch đó kèm theo danh sách sản phẩm của nó để đổ dữ liệu vào form chỉnh sửa
        $editingFlashSale = $request->filled('edit')
            ? FlashSale::with('products.product')->find($request->integer('edit'))
            : null;

        return view('admin.flash-sales.index', compact('flashSales', 'products', 'editingFlashSale'));
    }

    /**
     * Tiếp nhận dữ liệu và lưu mới một chương trình Flash Sale.
     */
    public function store(Request $request)
    {
        // Ràng buộc dữ liệu đầu vào: end_at phải diễn ra sau start_at
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'is_active' => ['nullable', Rule::in([0, 1, '0', '1'])],
            'description' => 'nullable|string|max:1000',
        ]);

        // Tạo mới bản ghi chương trình Flash Sale
        FlashSale::create([
            'name' => $validated['name'],
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'is_active' => $request->boolean('is_active'),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.flash-sales.index')->with('success', 'Tạo Flash Sale thành công!');
    }

    /**
     * Cập nhật thông tin chi tiết của một chương trình Flash Sale đã tồn tại.
     */
    public function update(Request $request, FlashSale $flash_sale)
    {
        // Ràng buộc dữ liệu chỉnh sửa
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'is_active' => ['nullable', Rule::in([0, 1, '0', '1'])],
            'description' => 'nullable|string|max:1000',
        ]);

        // Cập nhật dữ liệu vào database
        $flash_sale->update([
            'name' => $validated['name'],
            'start_at' => $validated['start_at'],
            'end_at' => $validated['end_at'],
            'is_active' => $request->boolean('is_active'),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.flash-sales.index')->with('success', 'Cập nhật Flash Sale thành công!');
    }

    /**
     * Xóa bỏ một chương trình Flash Sale.
     * Khi xóa Flash Sale, dữ liệu liên kết sản phẩm (FlashSaleProduct) thường sẽ tự động được xử lý theo ràng buộc ngoại khóa.
     */
    public function destroy(FlashSale $flash_sale)
    {
        $flash_sale->delete();

        return redirect()->route('admin.flash-sales.index')->with('success', 'Xóa Flash Sale thành công!');
    }
}

