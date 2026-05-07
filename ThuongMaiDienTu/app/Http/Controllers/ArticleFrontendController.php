<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleFrontendController extends Controller
{
    public function index()
    {
        // 3 bài viết mới nhất làm bài nổi bật (Featured)
        $featuredArticles = Article::where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Lấy IDs của các bài featured để loại trừ khỏi danh sách bên dưới
        $featuredIds = $featuredArticles->pluck('article_id')->toArray();

        // Các bài viết còn lại
        $latestArticles = Article::where('status', 'approved')
            ->whereNotIn('article_id', $featuredIds)
            ->orderBy('created_at', 'desc')
            ->paginate(12);
            
        return view('articles.index', compact('featuredArticles', 'latestArticles'));
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
}
