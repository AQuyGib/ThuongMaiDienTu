<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleFrontendController extends Controller
{
    public function index(Request $request)
    {
        $tag = $request->input('tag');

        // 3 bài viết mới nhất làm bài nổi bật (Featured)
        $featuredArticles = Article::where('status', 'approved')
            ->when($tag, function ($query, $tag) {
                $this->applyTagFilter($query, $tag);
            })
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Lấy IDs của các bài featured để loại trừ khỏi danh sách bên dưới
        $featuredIds = $featuredArticles->pluck('article_id')->toArray();

        // Các bài viết còn lại: Lấy các bài đã duyệt HOẶC bài của chính mình (nếu đang đăng nhập)
        $latestArticles = Article::query()
            ->where(function($query) {
                $query->where('status', 'approved');
                if (\Illuminate\Support\Facades\Auth::check()) {
                    $query->orWhere(function($q) {
                        $q->where('author_id', \Illuminate\Support\Facades\Auth::id())
                          ->where('author_type', 'customer');
                    });
                }
            })
            ->when($tag, function ($query, $tag) {
                $this->applyTagFilter($query, $tag);
            })
            ->whereNotIn('article_id', $featuredIds)
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();
            
        return view('articles.index', compact('featuredArticles', 'latestArticles', 'tag'));
    }

    private function applyTagFilter($query, string $tag)
    {
        return match ($tag) {
            'standard', 'lookbook', 'storytelling' => $query->where('format_type', $tag),
            'dienmay-pro' => $query->where('author_type', 'admin'),
            'lifestyle' => $query,
            default => $query,
        };
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();
            
        // Liên kết với đơn sửa chữa nếu có
        $relatedTicket = $article->repairTicket;
        
        // Lấy 5 bài viết mới nhất cho thanh sidebar
        $recentArticles = Article::where('status', 'approved')
            ->where('article_id', '!=', $article->article_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('articles.show', compact('article', 'relatedTicket', 'recentArticles'));
    }

    public function create()
    {
        $article = new Article();
        return view('articles.create', compact('article'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $data = $request->only(['title', 'summary', 'content']);
            $data['slug'] = \Illuminate\Support\Str::slug($request->title) . '-' . time();
            $data['author_id'] = \Illuminate\Support\Facades\Auth::id();
            $data['author_type'] = 'customer'; // Đánh dấu là bài viết của khách hàng
            $data['status'] = 'pending';   // Mặc định chờ duyệt
            $data['format_type'] = 'standard';

            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/articles'), $filename);
                $data['thumbnail'] = '/uploads/articles/' . $filename;
            }

            Article::create($data);

            return redirect()->route('articles.index')->with('success', 'Bài viết đã được gửi và đang chờ quản trị viên duyệt!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi khi gửi bài viết: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $article = Article::where('article_id', $id)
            ->where('author_id', \Illuminate\Support\Facades\Auth::id())
            ->where('author_type', 'customer')
            ->firstOrFail();

        return view('articles.create', compact('article'));
    }

    public function update(Request $request, $id)
    {
        $article = Article::where('article_id', $id)
            ->where('author_id', \Illuminate\Support\Facades\Auth::id())
            ->where('author_type', 'customer')
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $data = $request->only(['title', 'summary', 'content']);
            if ($request->title !== $article->title) {
                $data['slug'] = \Illuminate\Support\Str::slug($request->title) . '-' . time();
            }
            
            // Nếu bài viết đã duyệt mà khách sửa, có nên chuyển về pending không? 
            // Thường là có để đảm bảo nội dung mới vẫn sạch.
            $data['status'] = 'pending'; 

            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/articles'), $filename);
                $data['thumbnail'] = '/uploads/articles/' . $filename;
            }

            $article->update($data);

            return redirect()->route('articles.index')->with('success', 'Cập nhật bài viết thành công! Bài viết sẽ được duyệt lại.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi khi cập nhật bài viết: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $article = Article::where('article_id', $id)
            ->where('author_id', \Illuminate\Support\Facades\Auth::id())
            ->where('author_type', 'customer')
            ->firstOrFail();

        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Đã rút lại bài viết thành công!');
    }
}
