<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('q');
        $status = $request->input('status');

        $articles = Article::with('author')
            ->when($status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('author', function ($authorQuery) use ($search) {
                          $authorQuery->where('full_name', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    public function create()
    {
        $article = new Article(); // Empty model for the form
        $recentArticles = Article::with('author')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $sidebarArticlesJson = $recentArticles->map(fn ($item) => [
            'title' => $item->title,
            'slug' => route('articles.show', $item->slug),
            'thumbnail' => $item->thumbnail ? asset($item->thumbnail) : 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=400',
        ])->values()->toJson();

        return view('admin.articles.form', compact('article', 'recentArticles', 'sidebarArticlesJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'format_type' => 'required|in:standard,lookbook,storytelling',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $data = $request->except(['_token', 'thumbnail_file']);
            $data['slug'] = Str::slug($request->title) . '-' . time();
            $data['author_id'] = Auth::id() ?? 1; // Fallback for testing if no auth
            $data['author_type'] = 'admin';
            $data['status'] = 'approved';
            $data['published_at'] = now();

            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/articles'), $filename);
                $data['thumbnail'] = '/uploads/articles/' . $filename;
            }

            Article::create($data);

            return redirect()->route('admin.articles.index')->with('success', 'Tạo bài viết thành công!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi khi tạo bài viết: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $article = Article::findOrFail($id);
        $recentArticles = Article::with('author')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $sidebarArticlesJson = $recentArticles->map(fn ($item) => [
            'title' => $item->title,
            'slug' => route('articles.show', $item->slug),
            'thumbnail' => $item->thumbnail ? asset($item->thumbnail) : 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=400',
        ])->values()->toJson();

        return view('admin.articles.form', compact('article', 'recentArticles', 'sidebarArticlesJson'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'format_type' => 'required|in:standard,lookbook,storytelling',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $article = Article::findOrFail($id);
            $data = $request->except(['_token', '_method', 'thumbnail_file']);
            if ($request->title !== $article->title) {
                $data['slug'] = Str::slug($request->title) . '-' . time();
            }

            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/articles'), $filename);
                $data['thumbnail'] = '/uploads/articles/' . $filename;
            }

            $article->update($data);

            return redirect()->route('admin.articles.index')->with('success', 'Cập nhật bài viết thành công!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi khi cập nhật bài viết: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Xóa bài viết thành công!');
    }

    public function approve(Request $request, string $id)
    {
        $article = Article::findOrFail($id);
        $points = $request->input('points', 0);

        if ($article->approveAndReward($points)) {
            return redirect()->route('admin.articles.index')->with('success', "Đã duyệt bài và cộng $points điểm cho khách hàng!");
        }

        return redirect()->route('admin.articles.index')->with('error', 'Bài viết không đủ điều kiện duyệt hoặc đã được duyệt!');
    }
}
