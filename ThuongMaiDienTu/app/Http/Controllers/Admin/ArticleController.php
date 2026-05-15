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

        $articles = Article::with('author')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('author', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
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
        return view('admin.articles.form', compact('article'));
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
        return view('admin.articles.form', compact('article'));
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
