<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $categoriesQuery = Category::query()
            ->withTranslation()
            ->with(['parent'])
            ->withCount('products')
            ->latest('category_id');

        if ($search !== '') {
            $categoriesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('parent', function ($parentQuery) use ($search) {
                        $parentQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $categories = $categoriesQuery->paginate(20)->withQueryString();

        $totalCategories = Category::count();
        $rootCategories = Category::whereNull('parent_id')->count();
        $childCategories = Category::whereNotNull('parent_id')->count();
        
        $allCategories = Category::query()
            ->withTranslation()
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', compact(
            'categories',
            'allCategories',
            'totalCategories',
            'rootCategories',
            'childCategories',
            'search'
        ));
    }

    public function create()
    {
        $parents = Category::query()->orderBy('name')->get();

        return view('admin.categories.create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.edit', $category->category_id)
            ->with('success', 'Đã tạo danh mục và tự động đồng bộ bản dịch.');
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
