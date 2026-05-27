<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\ApiWrapsResponse;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ApiWrapsResponse;

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);

        $products = Product::query()
            ->withTranslation($request->attributes->get('locale'))
            ->with(['category.translations'])
            ->latest('product_id')
            ->paginate($perPage)
            ->withQueryString();

        return static::wrapCollection(ProductResource::collection($products), [
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Request $request, Product $product)
    {
        $product->loadMissing(['translations', 'category.translations']);

        return static::wrapResource(new ProductResource($product));
    }
}
