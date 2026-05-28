<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->withTranslation()
            ->latest('category_id')
            ->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::query()->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request)
    {
<<<<<<< HEAD
        $request->validate([
            'name' => 'required|string|max:50',
            'parent_id' => 'nullable|integer|exists:categories,category_id',
            'version' => 'required|integer',
        ], [
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục không được vượt quá 50 ký tự.',
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'version.required' => 'Thiếu thông tin phiên bản danh mục.',
            'version.integer' => 'Phiên bản danh mục không hợp lệ.',
        ]);

        $category = Category::findOrFail($id);

        // 1. Kiểm tra Optimistic Locking (Khóa lạc quan)
        if ((int)$category->version !== (int)$request->version) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Danh mục "' . $category->name . '" đã bị cập nhật bởi một người quản trị khác. Vui lòng tải lại trang và thử lại.');
        }

        $newParentId = $request->parent_id ?: null;

        // 2. Chống lặp vòng cha-con
        if ($newParentId !== null) {
            if ((int)$newParentId === (int)$id) {
                return redirect()->route('admin.categories.index')
                    ->with('error', 'Không thể chọn chính danh mục này làm danh mục cha.');
            }

            if (Category::isDescendant($newParentId, $id)) {
                return redirect()->route('admin.categories.index')
                    ->with('error', 'Không thể chọn danh mục con làm danh mục cha (tránh tạo vòng lặp vô hạn).');
            }
        }

        $category->name = $request->name;
        $category->parent_id = $newParentId;
        $category->version = $category->version + 1; // Tăng phiên bản
        $category->save();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục "' . $request->name . '" thành công!');
=======
        $category = Category::create($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.edit', $category->category_id)
            ->with('success', 'Đã tạo danh mục và tự động đồng bộ bản dịch.');
>>>>>>> master
    }

    public function edit(Category $category)
    {
        $category->loadMissing('translations', 'parent.translations', 'children.translations');
        $parents = Category::query()->where('category_id', '!=', $category->category_id)->orderBy('name')->get();

        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $category->update($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Đã cập nhật danh mục và tự động đồng bộ bản dịch.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Đã xóa danh mục.');
    }
}
