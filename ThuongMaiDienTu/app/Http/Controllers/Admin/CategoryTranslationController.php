<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryTranslation;
use Illuminate\Http\Request;

class CategoryTranslationController extends Controller
{
    public function edit(Category $category)
    {
        $translation = CategoryTranslation::query()->firstOrNew([
            'category_id' => $category->category_id,
            'locale' => 'en',
        ]);

        return view('admin.categories.translation-edit', compact('category', 'translation'));
    }

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
