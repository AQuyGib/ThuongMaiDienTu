<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $videos = Video::query()
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('videos.index', compact('videos'));
    }

    public function create()
    {
        abort(403, 'Người dùng không có quyền upload video.');
    }

    public function store(Request $request)
    {
        abort(403, 'Người dùng không có quyền upload video.');
    }
}
