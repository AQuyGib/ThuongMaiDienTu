<?php
// app/Http/Controllers/Admin/InventoryMovementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    /**
     * Danh sách lịch sử biến động kho
     */
    public function index(Request $request)
    {
        $query = InventoryMovement::with(['product', 'variant', 'order', 'creator']);

        // Lọc theo sản phẩm
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Lọc theo loại biến động
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Lọc theo khoảng thời gian
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Tìm kiếm theo mã đơn hàng hoặc ghi chú
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('note', 'like', '%' . $search . '%')
                  ->orWhere('order_id', 'like', '%' . $search . '%')
                  ->orWhere('reference_id', 'like', '%' . $search . '%');
            });
        }

        $movements = $query->orderBy('movement_id', 'desc')
            ->paginate(15)
            ->onEachSide(1)
            ->appends($request->query());

        // Lấy danh sách sản phẩm để hiển thị trong bộ lọc dropdown
        $products = Product::orderBy('name')->get(['product_id', 'name']);

        // Định nghĩa các loại biến động có màu sắc và nhãn tương ứng
        $types = [
            'sale' => [
                'label' => 'Bán hàng',
                'bg' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            ],
            'restock' => [
                'label' => 'Hoàn kho',
                'bg' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            ],
            'adjustment' => [
                'label' => 'Cân bằng',
                'bg' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            ],
            'import' => [
                'label' => 'Nhập kho',
                'bg' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            ],
            'return' => [
                'label' => 'Trả hàng',
                'bg' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            ],
        ];

        return view('admin.inventory.movements', compact('movements', 'products', 'types'));
    }
}
