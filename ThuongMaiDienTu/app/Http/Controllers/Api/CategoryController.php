<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\ApiWrapsResponse;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ApiWrapsResponse;

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);

        $categories = Category::query()
            ->withTranslation($request->attributes->get('locale'))
            ->with(['parent.translations', 'children.translations'])
            ->latest('category_id')
            ->paginate($perPage)
            ->withQueryString();

        return static::wrapCollection(CategoryResource::collection($categories), [
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ]);
    }

    public function show(Request $request, Category $category)
    {
        $category->loadMissing(['translations', 'parent.translations', 'children.translations']);

        return static::wrapResource(new CategoryResource($category));
    }
}
