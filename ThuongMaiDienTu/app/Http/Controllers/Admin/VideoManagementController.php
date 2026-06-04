<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * =================================================================================
 * BỘ ĐIỀU KHIỂN: QUẢN LÝ VIDEO (VIDEO MANAGEMENT CONTROLLER) - DÀNH CHO ADMIN
 * ---------------------------------------------------------------------------------
 * [Ý NGHĨA DÀNH CHO NGƯỜI DÙNG / NGƯỜI KHÔNG BIẾT VỀ CODE]:
 * Đây là công cụ dành cho Quản trị viên (Admin) để quản lý Góc video của cửa hàng.
 * Admin có thể đăng video mới (từ file máy tính hoặc link YouTube), chỉnh sửa thông tin video,
 * duyệt các video do người dùng tải lên, ẩn các video vi phạm chính sách và xóa video khỏi hệ thống.
 * =================================================================================
 */
class VideoManagementController extends Controller
{
    // Sử dụng cơ chế tiêm dịch vụ thông báo (NotificationService) để gửi thông báo đến người dùng khi video bị ẩn.
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * HÀM: create
     * Ý NGHĨA: Hiển thị trang đăng tải video mới (giao diện upload cho Admin).
     */
    public function create()
    {
        // 1. Lấy tất cả danh mục gốc (không có danh mục cha) để cho phép Admin gán video vào danh mục phù hợp.
        $categories = \App\Models\Category::whereNull('parent_id')->orderBy('name')->get();
        
        // 2. Trả về giao diện upload kèm danh mục.
        return view('admin.videos.create', compact('categories'));
    }

    /**
     * HÀM: store
     * Ý NGHĨA: Lưu trữ video mới tải lên vào hệ thống.
     */
    public function store(Request $request)
    {
        // 1. Kiểm tra tính hợp lệ của dữ liệu (Tiêu đề, video tải lên tối đa 100MB, ảnh bìa tối đa 2MB, danh mục/sản phẩm...)
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video' => ['nullable', 'file', 'mimes:mp4,mkv', 'max:102400'],
            'youtube_url' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,category_id'],
            'product_id' => ['nullable', 'exists:products,product_id'],
            'duration' => ['nullable', 'string', 'max:50'],
        ], [
            'video.max' => 'Video không được vượt quá 100MB.',
            'video.mimes' => 'Chỉ chấp nhận định dạng .mp4 hoặc .mkv.',
            'thumbnail.image' => 'Thumbnail phải là file ảnh hợp lệ.',
            'thumbnail.max' => 'Thumbnail không được vượt quá 2MB.',
            'category_id.exists' => 'Danh mục được chọn không hợp lệ.',
            'product_id.exists' => 'Sản phẩm được chọn không hợp lệ.',
        ]);

        // 2. Ràng buộc: Bắt buộc phải có file video tải lên hoặc đường link YouTube
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

        // 3. Xử lý lưu file video nội bộ nếu Admin chọn tải lên trực tiếp
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('videos', 'public');
            $fileSize = $request->file('video')->getSize();
            $mimeType = $request->file('video')->getMimeType();
        }

        // 4. Xử lý lưu ảnh bìa (thumbnail) nếu có
        $thumbnailPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->store('videos/thumbnails', 'public')
            : null;

        // 5. Trích xuất mã ID nhúng YouTube nếu Admin nhập liên kết YouTube thông thường
        $youtubeUrl = $this->parseYoutubeEmbed($validated['youtube_url'] ?? null);

        $categoryName = 'REVIEW';
        $catId = $validated['category_id'] ?? null;
        $prodId = $validated['product_id'] ?? null;

        // 6. Tự động liên kết danh mục dựa trên sản phẩm được chọn nếu Admin để trống danh mục
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

        // 7. Tạo bản ghi video mới với trạng thái tự động công khai (published)
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

    /**
     * HÀM: parseYoutubeEmbed
     * Ý NGHĨA: Chuyển đổi một link YouTube thường thành link nhúng (embed) để hiển thị trong thẻ iframe.
     */
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

    /**
     * HÀM: edit
     * Ý NGHĨA: Hiển thị giao diện chỉnh sửa thông tin video.
     */
    public function edit(Video $video)
    {
        $categories = \App\Models\Category::whereNull('parent_id')->orderBy('name')->get();
        return view('admin.videos.edit', compact('video', 'categories'));
    }

    /**
     * HÀM: update
     * Ý NGHĨA: Cập nhật thông tin video sau khi Admin chỉnh sửa.
     */
    public function update(Request $request, Video $video)
    {
        // 1. Kiểm tra tính hợp lệ dữ liệu chỉnh sửa
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'video' => ['nullable', 'file', 'mimes:mp4,mkv', 'max:102400'],
            'youtube_url' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'category_id' => ['nullable', 'exists:categories,category_id'],
            'duration' => ['nullable', 'string', 'max:50'],
        ], [
            'video.max' => 'Video không được vượt quá 100MB.',
            'video.mimes' => 'Chỉ chấp nhận định dạng .mp4 hoặc .mkv.',
            'thumbnail.image' => 'Thumbnail phải là file ảnh hợp lệ.',
            'thumbnail.max' => 'Thumbnail không được vượt quá 2MB.',
        ]);

        // 2. Nếu Admin tải lên file video mới -> Tiến hành xóa file cũ và lưu file mới
        if ($request->hasFile('video')) {
            if ($video->video_path) {
                Storage::disk('public')->delete($video->video_path);
            }
            $video->video_path = $request->file('video')->store('videos', 'public');
            $video->file_size = $request->file('video')->getSize();
            $video->mime_type = $request->file('video')->getMimeType();
        }

        // 3. Nếu Admin tải lên ảnh bìa mới -> Xóa ảnh bìa cũ và lưu ảnh bìa mới
        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail_path) {
                Storage::disk('public')->delete($video->thumbnail_path);
            }
            $video->thumbnail_path = $request->file('thumbnail')->store('videos/thumbnails', 'public');
        }

        $youtubeUrl = $this->parseYoutubeEmbed($validated['youtube_url'] ?? null);

        $categoryName = $video->category;
        $catId = $validated['category_id'] ?? null;
        if (!empty($catId)) {
            $cat = \App\Models\Category::find($catId);
            if ($cat) {
                $categoryName = $cat->name;
            }
        }

        // 4. Lưu tất cả thay đổi
        $video->title = $validated['title'];
        $video->description = $validated['description'] ?? $video->description;
        $video->youtube_url = $youtubeUrl ?? $video->youtube_url;
        $video->category = $categoryName;
        $video->category_id = $catId;
        $video->duration = $validated['duration'] ?? $video->duration;
        $video->save();

        return redirect()->route('admin.videos.index')->with('success', 'Cập nhật video thành công.');
    }

    /**
     * HÀM: index
     * Ý NGHĨA: Hiển thị danh sách tất cả video trong trang quản lý Admin kèm bộ đếm phân loại.
     */
    public function index(Request $request)
    {
        $query = Video::with('user')->latest();

        // 1. Tìm kiếm theo từ khóa (tiêu đề, mô tả)
        if ($request->filled('keyword')) {
            $keyword = $request->string('keyword')->trim()->toString();
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        // 2. Lọc theo trạng thái video (Do admin đăng, Chờ duyệt, Công khai, Đã ẩn)
        $selectedStatus = $request->string('status')->toString();
        if ($selectedStatus === 'admin_upload') {
            $query->where('uploaded_by_admin', true);
        } elseif ($request->filled('status')) {
            $query->where('status', $selectedStatus);
        }

        $baseQuery = Video::query();

        // 3. Lấy số lượng tương ứng của từng phân loại để hiển thị các bộ đếm trên Tab đầu trang
        $counts = [
            'adminUploadCount' => (clone $baseQuery)->where('uploaded_by_admin', true)->count(),
            'pendingCount' => (clone $baseQuery)->where('status', 'pending')->count(),
            'publishedCount' => (clone $baseQuery)->where('status', 'published')->count(),
            'hiddenCount' => (clone $baseQuery)->where('status', 'hidden')->count(),
        ];

        // 4. Phân trang 10 video mỗi trang
        $videos = $query->paginate(10)->withQueryString();

        return view('admin.videos.index', array_merge(compact('videos'), $counts));
    }

    /**
     * HÀM: show
     * Ý NGHĨA: Hiển thị chi tiết nội dung video và thông tin người đăng.
     */
    public function show(Video $video)
    {
        $video->load('user');

        return view('admin.videos.show', compact('video'));
    }

    /**
     * HÀM: approve
     * Ý NGHĨA: Phê duyệt video (chuyển trạng thái từ "Chờ duyệt" sang "Công khai" để hiển thị trên web).
     */
    public function approve(Video $video)
    {
        $video->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Video đã được công khai.');
    }

    /**
     * HÀM: hide
     * Ý NGHĨA: Ẩn video khỏi hệ thống (ví dụ video vi phạm thuần phong mỹ tục) và gửi thông báo cho tác giả.
     */
    public function hide(Request $request, Video $video)
    {
        // 1. Kiểm tra ghi chú lý do ẩn video của Admin
        $validated = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        // 2. Cập nhật trạng thái video thành ẩn
        $video->update([
            'status' => 'hidden',
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        // 3. Nếu video do người dùng thường đăng, gửi thông báo cảnh báo đến tài khoản của họ
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

    /**
     * HÀM: destroy
     * Ý NGHĨA: Xóa hoàn toàn một video khỏi cơ sở dữ liệu và dọn dẹp các tệp lưu trên ổ cứng.
     */
    public function destroy(Video $video)
    {
        // 1. Xóa file video gốc trên đĩa lưu trữ public
        if ($video->video_path) {
            Storage::disk('public')->delete($video->video_path);
        }

        // 2. Xóa tệp ảnh bìa trên đĩa lưu trữ public
        if ($video->thumbnail_path) {
            Storage::disk('public')->delete($video->thumbnail_path);
        }

        // 3. Xóa bản ghi trong database
        $video->delete();

        return back()->with('success', 'Video đã được xóa.');
    }

    /**
     * HÀM: destroyComment
     * Ý NGHĨA: Xóa một bình luận video (sử dụng trong trang quản lý của Admin).
     */
    public function destroyComment(\App\Models\VideoComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Bình luận đã được xóa.');
    }
}
