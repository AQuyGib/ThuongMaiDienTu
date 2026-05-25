<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $videos = Video::query()
            ->where('status', 'published')
            ->latest()
            ->get();

        $categories = \App\Models\Category::whereNull('parent_id')->get();

        return view('videos.index', compact('videos', 'categories'));
    }

    public function create()
    {
        abort(403, 'Người dùng không có quyền upload video.');
    }

    public function store(Request $request)
    {
        abort(403, 'Người dùng không có quyền upload video.');
    }

    public function like(Request $request, Video $video)
    {
        $action = $request->input('action', 'like');

        if ($action === 'like') {
            $video->increment('likes');
        } else {
            if ($video->likes > 0) {
                $video->decrement('likes');
            }
        }

        return response()->json([
            'success' => true,
            'likes' => $video->fresh()->likes,
        ]);
    }

    public function view(Video $video)
    {
        $video->increment('views');

        return response()->json([
            'success' => true,
            'views' => $video->fresh()->views,
        ]);
    }
}
