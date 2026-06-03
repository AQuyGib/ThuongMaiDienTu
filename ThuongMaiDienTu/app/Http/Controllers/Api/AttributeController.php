<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttributeResource;
use App\Http\Resources\Concerns\ApiWrapsResponse;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    use ApiWrapsResponse;

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);

        $attributes = Attribute::query()
            ->withTranslation($request->attributes->get('locale'))
            ->latest('attribute_id')
            ->paginate($perPage)
            ->withQueryString();

        return static::wrapCollection(AttributeResource::collection($attributes), [
            'pagination' => [
                'current_page' => $attributes->currentPage(),
                'last_page' => $attributes->lastPage(),
                'per_page' => $attributes->perPage(),
                'total' => $attributes->total(),
            ],
        ]);
    }

    public function show(Request $request, Attribute $attribute)
    {
        $attribute->loadMissing('translations');

        return static::wrapResource(new AttributeResource($attribute));
    }
}
