<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Http\Request;

class PageTranslationController extends Controller
{
    public function edit(Page $page)
    {
        $translation = PageTranslation::query()->firstOrNew([
            'page_id' => $page->page_id,
            'locale' => 'en',
        ]);

        return view('admin.pages.translation-edit', compact('page', 'translation'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);

        PageTranslation::updateOrCreate(
            [
                'page_id' => $page->page_id,
                'locale' => 'en',
            ],
            $data + [
                'page_id' => $page->page_id,
                'locale' => 'en',
            ]
        );

        return back()->with('success', 'Đã lưu bản dịch EN thủ công cho trang.');
    }
}
