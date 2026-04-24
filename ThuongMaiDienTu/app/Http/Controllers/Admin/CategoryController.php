<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách danh mục (Trang Quản lý Danh mục)
     */
    public function index()
    {
        // Lấy danh sách danh mục có phân trang, đếm products
        $categories = Category::withCount('products')
            ->orderBy('category_id', 'desc')
            ->paginate(10);

        // Thống kê
        $totalCategories = Category::count();

        return view('admin.categories.QLDanhMuc', compact(
            'categories',
            'totalCategories'
        ));
    }

    /**
     * Thêm danh mục mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục không được vượt quá 50 ký tự.',
        ]);

        Category::create([
            'name' => $request->name,
            'icon' => $request->icon ?: null,
        ]);

        return redirect()->route('admin.danhmuc.index')
            ->with('success', 'Thêm danh mục "' . $request->name . '" thành công!');
    }

    /**
     * Cập nhật danh mục
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục không được vượt quá 50 ký tự.',
        ]);

        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name,
            'icon' => $request->icon ?: null,
        ]);

        return redirect()->route('admin.danhmuc.index')
            ->with('success', 'Cập nhật danh mục "' . $request->name . '" thành công!');
    }

    /**
     * Xóa danh mục
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;

        // Kiểm tra xem danh mục có sản phẩm không
        if ($category->products()->count() > 0) {
            return redirect()->route('admin.danhmuc.index')
                ->with('error', 'Không thể xóa danh mục "' . $name . '" vì đang chứa sản phẩm!');
        }

        $category->delete();

        return redirect()->route('admin.danhmuc.index')
            ->with('success', 'Xóa danh mục "' . $name . '" thành công!');
    }
}
