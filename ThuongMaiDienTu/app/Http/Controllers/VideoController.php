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

        $categories = \App\Models\Category::whereHas('products')->orderBy('name')->get();

        return view('videos.index', compact('videos', 'categories'));
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

    public function getComments(Video $video)
    {
        $commentsQuery = $video->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->get();
            
        $totalCount = $video->comments()->count();
        
        $comments = $commentsQuery->map(function ($comment) {
            return [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->format('d/m/Y H:i'),
                'user' => [
                    'id' => $comment->user_id,
                    'name' => $comment->user->full_name ?? $comment->user->name ?? 'Người dùng',
                    'role_id' => $comment->user->role_id ?? 3,
                ],
                'replies' => $comment->replies->map(function ($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'created_at' => $reply->created_at->format('d/m/Y H:i'),
                        'user' => [
                            'id' => $reply->user_id,
                            'name' => $reply->user->full_name ?? $reply->user->name ?? 'Người dùng',
                            'role_id' => $reply->user->role_id ?? 3,
                        ]
                    ];
                })
            ];
        });

        return response()->json([
            'success' => true,
            'comments' => $comments,
            'total_count' => $totalCount,
        ]);
    }

    public function storeComment(Request $request, Video $video)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:video_comments,id',
        ]);

        $comment = \App\Models\VideoComment::create([
            'video_id' => $video->id,
            'parent_id' => $request->parent_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->format('d/m/Y H:i'),
                'user' => [
                    'name' => auth()->user()->full_name ?? auth()->user()->name ?? 'Người dùng',
                    'role_id' => auth()->user()->role_id ?? 3,
                ]
            ],
        ], 201);
    }

    public function destroyComment(\App\Models\VideoComment $comment)
    {
        if (auth()->user()->role_id == 1 || auth()->user()->role_id == 2 || auth()->id() == $comment->user_id) {
            $comment->delete();
            return response()->json([
                'success' => true,
                'message' => 'Xóa bình luận thành công!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Bạn không có quyền thực hiện hành động này.'
        ], 403);
    }

    public function stream(Video $video)
    {
        if (empty($video->video_path)) {
            abort(404, 'Video path not found.');
        }

        $path = public_path($video->video_path);
        
        if (!file_exists($path)) {
            $path = storage_path('app/public/' . $video->video_path);
        }
        
        if (!file_exists($path)) {
            if (filter_var($video->video_path, FILTER_VALIDATE_URL)) {
                return redirect()->away($video->video_path);
            }
            abort(404, 'Video file does not exist.');
        }

        $file = fopen($path, 'rb');
        $size = filesize($path);
        $length = $size;
        $start = 0;
        $end = $size - 1;

        $headers = [
            'Content-Type' => $video->mime_type ?: 'video/mp4',
            'Accept-Ranges' => 'bytes',
        ];

        if (request()->header('Range')) {
            $range = request()->header('Range');
            if (preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches)) {
                $start = intval($matches[1]);
                if (isset($matches[2])) {
                    $end = intval($matches[2]);
                }
                $length = $end - $start + 1;
                
                fseek($file, $start);
                
                $headers['Content-Length'] = $length;
                $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
                
                return response()->stream(function() use ($file, $length) {
                    $buffer = 102400; // 100kb
                    $bytes_sent = 0;
                    while (!feof($file) && $bytes_sent < $length) {
                        if (connection_aborted()) break;
                        $to_send = min($buffer, $length - $bytes_sent);
                        echo fread($file, $to_send);
                        flush();
                        $bytes_sent += $to_send;
                    }
                    fclose($file);
                }, 206, $headers);
            }
        }

        $headers['Content-Length'] = $length;
        return response()->stream(function() use ($file) {
            fpassthru($file);
            fclose($file);
        }, 200, $headers);
    }
}
