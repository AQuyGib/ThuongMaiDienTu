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
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $categoriesQuery = Category::with(['parent'])
            ->withCount('products')
            ->orderBy('category_id', 'desc');

        if ($search !== '') {
            $categoriesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('parent', function ($parentQuery) use ($search) {
                        $parentQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $categories = $categoriesQuery->paginate(10)->withQueryString();

        // Thống kê
        $totalCategories = Category::count();
        $rootCategories = Category::whereNull('parent_id')->count();
        $childCategories = Category::whereNotNull('parent_id')->count();
        $allCategories = Category::orderBy('name')->get(['category_id', 'name', 'parent_id']);

        return view('admin.categories.Category', compact(
            'categories',
            'allCategories',
            'totalCategories',
            'rootCategories',
            'childCategories',
            'search'
        ));
    }

    /**
     * Thêm danh mục mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'parent_id' => 'nullable|integer|exists:categories,category_id',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục không được vượt quá 50 ký tự.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
        ]);

        Category::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id ?: null,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Thêm danh mục "' . $request->name . '" thành công!');
    }

    /**
     * Cập nhật danh mục
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'parent_id' => 'nullable|integer|exists:categories,category_id',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục không được vượt quá 50 ký tự.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
        ]);

        $category = Category::findOrFail($id);

        $category->update([
            'name' => $request->name,
            'parent_id' => $request->parent_id ?: null,
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục "' . $request->name . '" thành công!');
    }

    /**
     * Xóa danh mục
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $name = $category->name;

        if ($category->products()->count() > 0 || $category->children()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Không thể xóa danh mục "' . $name . '" vì đang chứa sản phẩm hoặc danh mục con!');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Xóa danh mục "' . $name . '" thành công!');
    }
}
