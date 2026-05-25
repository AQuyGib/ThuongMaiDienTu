<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoManagementController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function create()
    {
        $categories = \App\Models\Category::all();
        $products = \App\Models\Product::orderBy('name')->get();
        return view('admin.videos.create', compact('categories', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video' => ['nullable', 'file', 'mimes:mp4,mkv', 'max:20480'],
            'youtube_url' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,category_id'],
            'product_id' => ['nullable', 'exists:products,product_id'],
            'duration' => ['nullable', 'string', 'max:50'],
        ], [
            'video.max' => 'Video không được vượt quá 20MB.',
            'video.mimes' => 'Chỉ chấp nhận định dạng .mp4 hoặc .mkv.',
            'thumbnail.image' => 'Thumbnail phải là file ảnh hợp lệ.',
            'thumbnail.max' => 'Thumbnail không được vượt quá 2MB.',
            'category_id.exists' => 'Danh mục được chọn không hợp lệ.',
            'product_id.exists' => 'Sản phẩm được chọn không hợp lệ.',
        ]);

        if (!$request->hasFile('video') && empty($validated['youtube_url'])) {
            if ($request->ajax()) {
                return response()->json([
                    'errors' => [
                        'video' => ['Vui lòng chọn tải lên tệp video hoặc điền liên kết YouTube.']
                    ]
                ], 422);
            }
            return back()->withErrors(['video' => 'Vui lòng chọn tải lên tệp video hoặc điền liên kết YouTube.'])->withInput();
        }

        $videoPath = null;
        $fileSize = 0;
        $mimeType = null;

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('videos', 'public');
            $fileSize = $request->file('video')->getSize();
            $mimeType = $request->file('video')->getMimeType();
        }

        $thumbnailPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->store('videos/thumbnails', 'public')
            : null;

        $youtubeUrl = $this->parseYoutubeEmbed($validated['youtube_url'] ?? null);

        $categoryName = 'REVIEW';
        $catId = $validated['category_id'] ?? null;
        $prodId = $validated['product_id'] ?? null;

        if (empty($catId) && !empty($prodId)) {
            $prod = \App\Models\Product::find($prodId);
            if ($prod && $prod->category_id) {
                $catId = $prod->category_id;
            }
        }

        if (!empty($catId)) {
            $cat = \App\Models\Category::find($catId);
            if ($cat) {
                $categoryName = $cat->name;
            }
        }

        $video = Video::create([
            'user_id' => auth()->id(),
            'uploaded_by_admin' => true,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'video_path' => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'youtube_url' => $youtubeUrl,
            'category' => $categoryName,
            'category_id' => $catId,
            'product_id' => $prodId,
            'duration' => $validated['duration'] ?? '0:00',
            'status' => 'published',
            'published_at' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'message' => 'Upload video thành công.',
                'data' => $video,
            ], 201);
        }

        return redirect()->route('admin.videos.index')->with('success', 'Tải video thành công. Video đã được đăng lên Góc video.');
    }

    private function parseYoutubeEmbed($url)
    {
        if (!$url) return null;
        if (str_contains($url, 'youtube.com/embed/')) {
            return $url;
        }
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            return 'https://www.youtube.com/embed/' . $match[1];
        }
        return $url;
    }

    public function index(Request $request)
    {
        $query = Video::with('user')->latest();

        if ($request->filled('keyword')) {
            $keyword = $request->string('keyword')->trim()->toString();
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        $selectedStatus = $request->string('status')->toString();
        if ($selectedStatus === 'admin_upload') {
            $query->where('uploaded_by_admin', true);
        } elseif ($request->filled('status')) {
            $query->where('status', $selectedStatus);
        }

        $baseQuery = Video::query();

        $counts = [
            'adminUploadCount' => (clone $baseQuery)->where('uploaded_by_admin', true)->count(),
            'pendingCount' => (clone $baseQuery)->where('status', 'pending')->count(),
            'publishedCount' => (clone $baseQuery)->where('status', 'published')->count(),
            'hiddenCount' => (clone $baseQuery)->where('status', 'hidden')->count(),
        ];

        $videos = $query->paginate(10)->withQueryString();

        return view('admin.videos.index', array_merge(compact('videos'), $counts));
    }

    public function show(Video $video)
    {
        $video->load('user');

        return view('admin.videos.show', compact('video'));
    }

    public function approve(Video $video)
    {
        $video->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Video đã được duyệt và công khai.');
    }

    public function hide(Request $request, Video $video)
    {
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $video->update([
            'status' => 'hidden',
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        if ($video->user) {
            $this->notificationService->createForUser($video->user, [
                'type' => 'video.hidden',
                'title' => 'Video của bạn đã bị ẩn',
                'content' => 'Video "' . $video->title . '" đã bị admin ẩn. Vui lòng kiểm tra lại nội dung.',
                'action_url' => route('videos.index'),
                'data' => [
                    'video_id' => $video->id,
                    'status' => 'hidden',
                    'admin_note' => $validated['admin_note'] ?? null,
                ],
            ]);
        }

        return back()->with('success', 'Video đã bị ẩn.');
    }

    public function destroy(Video $video)
    {
        if ($video->video_path) {
            Storage::disk('public')->delete($video->video_path);
        }

        if ($video->thumbnail_path) {
            Storage::disk('public')->delete($video->thumbnail_path);
        }

        $video->delete();

        return back()->with('success', 'Video đã được xóa.');
    }
}
