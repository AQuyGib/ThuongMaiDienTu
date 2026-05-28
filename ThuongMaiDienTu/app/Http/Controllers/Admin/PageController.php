<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Models\Page;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::query()
            ->withTranslation()
            ->latest('page_id')
            ->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(StorePageRequest $request)
    {
        $page = Page::create($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->title),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.pages.edit', $page->page_id)
            ->with('success', 'Đã tạo trang và tự động đồng bộ bản dịch.');
    }

    public function edit(Page $page)
    {
        $page->loadMissing('translations');

        return view('admin.pages.edit', compact('page'));
    }

    public function update(StorePageRequest $request, Page $page)
    {
        $page->update($request->validated() + [
            'slug' => $request->filled('slug') ? $request->slug : Str::slug($request->title),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Đã cập nhật trang và tự động đồng bộ bản dịch.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Đã xóa trang.');
    }
}
