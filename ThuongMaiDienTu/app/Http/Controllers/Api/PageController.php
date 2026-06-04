<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\ApiWrapsResponse;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use ApiWrapsResponse;

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);

        $pages = Page::query()
            ->withTranslation($request->attributes->get('locale'))
            ->latest('page_id')
            ->paginate($perPage)
            ->withQueryString();

        return static::wrapCollection(PageResource::collection($pages), [
            'pagination' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ],
        ]);
    }

    public function show(Request $request, Page $page)
    {
        $page->loadMissing('translations');

        return static::wrapResource(new PageResource($page));
    }
}
