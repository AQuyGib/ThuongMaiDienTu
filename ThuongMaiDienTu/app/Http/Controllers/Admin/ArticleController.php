<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

/**
 * ArticleController - Bộ điều khiển Quản trị Bài viết (Admin Article CRUD).
 *
 * Nhiệm vụ chính:
 * 1. Hiển thị danh sách toàn bộ bài viết trong hệ thống kèm bộ lọc tìm kiếm nâng cao (theo tiêu đề, tên tác giả, trạng thái bài viết).
 * 2. Cung cấp chức năng tạo mới bài viết của Admin (tự động phê duyệt, sinh slug duy nhất).
 * 3. Cho phép chỉnh sửa thông tin chi tiết bài viết (cập nhật nội dung, tiêu đề, thay đổi ảnh đại diện thumbnail).
 * 4. Hỗ trợ xóa bài viết ra khỏi cơ sở dữ liệu.
 * 5. Duyệt bài viết do khách hàng tự viết gửi lên (Community Posts) và tự động cộng điểm thưởng (Loyalty Points) tương ứng cho tài khoản của khách hàng đó.
 */
class ArticleController extends Controller
{
    /**
     * Hiển thị danh sách các bài viết với bộ lọc trạng thái và tìm kiếm.
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Lấy từ khóa tìm kiếm và bộ lọc trạng thái từ request gửi lên
        $search = $request->input('q');
        $status = $request->input('status');

        // Thực hiện truy vấn danh sách bài viết kèm thông tin tác giả để tránh lỗi N+1 Query
        $articles = Article::with('author')
            ->when($status, function ($query, $status) {
                if ($status === 'ai_checked') {
                    // Lọc theo các bài viết đã được AI kiểm duyệt quét qua
                    $query->where('ai_checked', 1);
                } else {
                    // Lọc theo trạng thái bài viết (ví dụ: pending - chờ duyệt, approved - đã duyệt)
                    $query->where('status', $status);
                }
            })
            ->when($search, function ($query, $search) {
                // Lọc theo từ khóa tìm kiếm (so khớp tiêu đề hoặc tên đầy đủ của tác giả viết bài)
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhereHas('author', function ($authorQuery) use ($search) {
                          $authorQuery->where('full_name', 'like', "%{$search}%");
                      });
                });
            })
            ->orderBy('created_at', 'desc') // Sắp xếp bài viết mới nhất lên trên đầu
            ->paginate(10) // Phân trang, mỗi trang hiển thị tối đa 10 bài viết
            ->withQueryString(); // Giữ nguyên các tham số bộ lọc trên thanh URL khi chuyển trang

        // Tính toán các chỉ số thống kê toàn cục từ cơ sở dữ liệu (thay vì tính cục bộ trên trang phân trang)
        $stats = [
            'total' => Article::count(),
            'approved' => Article::where('status', 'approved')->count(),
            'pending' => Article::where('status', 'pending')->count(),
            'rejected' => Article::where('status', 'rejected')->count(),
            'ai_checked' => Article::where('ai_checked', 1)->count(),
        ];

        // Trả về giao diện danh sách bài viết trong khu vực Admin
        return view('admin.articles.index', compact('articles', 'stats'));
    }

    /**
     * Hiển thị giao diện form tạo mới một bài viết.
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Khởi tạo một đối tượng Article trống để form dùng chung với chế độ chỉnh sửa (edit)
        $article = new Article(); 
        
        // Lấy ra 5 bài viết đã duyệt gần đây nhất để hiển thị ở sidebar làm tài liệu tham khảo
        $recentArticles = Article::with('author')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Chuyển đổi thông tin bài viết gần đây thành dạng JSON để truyền xuống cho các component JavaScript ở View xử lý
        $sidebarArticlesJson = $recentArticles->map(fn ($item) => [
            'title' => $item->title,
            'slug' => route('articles.show', $item->slug),
            'thumbnail' => $item->thumbnail ? asset($item->thumbnail) : 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=400',
        ])->values()->toJson();

        return view('admin.articles.form', compact('article', 'recentArticles', 'sidebarArticlesJson'));
    }

    /**
     * Tiếp nhận dữ liệu gửi lên và lưu bài viết mới vào cơ sở dữ liệu.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Kiểm tra và ràng buộc dữ liệu đầu vào (Validation)
        $request->validate([
            'title' => 'required|string|max:255', // Tiêu đề bắt buộc, tối đa 255 ký tự
            'content' => 'required', // Nội dung bài viết bắt buộc nhập
            'format_type' => 'required|in:standard,lookbook,storytelling', // Định dạng hiển thị bắt buộc nằm trong danh sách cho phép
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Ảnh đại diện không bắt buộc, định dạng hình ảnh và tối đa 2MB
        ]);

        try {
            // Lấy tất cả dữ liệu từ request ngoại trừ token bảo mật CSRF và file ảnh
            $data = $request->except(['_token', 'thumbnail_file']);
            
            // Tự động sinh đường dẫn thân thiện (slug) từ tiêu đề tiếng Việt và nối thêm timestamp để đảm bảo tính độc nhất
            $data['slug'] = Str::slug($request->title) . '-' . time();
            $data['author_id'] = Auth::id() ?? 1; // Gán ID của Admin hiện tại đang đăng nhập
            $data['author_type'] = 'admin'; // Loại tác giả là admin
            $data['status'] = 'approved'; // Bài viết do admin tự tạo sẽ được duyệt ngay lập tức
            $data['published_at'] = now(); // Đặt thời gian xuất bản là thời điểm hiện tại

            // Xử lý lưu trữ file hình ảnh đại diện (thumbnail) nếu admin có tải lên
            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                // Di chuyển file ảnh vào thư mục công khai public/uploads/articles
                $file->move(public_path('uploads/articles'), $filename);
                $data['thumbnail'] = '/uploads/articles/' . $filename;
            }

            // Tiến hành ghi dữ liệu bài viết mới vào database
            Article::create($data);

            // Chuyển hướng về trang danh sách bài viết kèm thông báo thành công
            return redirect()->route('admin.articles.index')->with('success', 'Tạo bài viết thành công!');
        } catch (\Exception $e) {
            // Nếu có bất kỳ lỗi ngoại lệ nào xảy ra, quay lại form, giữ lại dữ liệu đã điền và báo lỗi
            return back()->withInput()->with('error', 'Lỗi khi tạo bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị giao diện form chỉnh sửa một bài viết đã tồn tại.
     * 
     * @param string $id ID của bài viết cần sửa
     * @return \Illuminate\View\View
     */
    public function edit(string $id)
    {
        // Truy vấn bài viết cần sửa, nếu không tìm thấy sẽ trả về lỗi HTTP 404
        $article = Article::findOrFail($id);
        
        // Lấy ra 5 bài viết đã duyệt gần đây nhất để hiển thị ở sidebar
        $recentArticles = Article::with('author')
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Chuyển đổi danh sách bài viết gần đây thành chuỗi JSON
        $sidebarArticlesJson = $recentArticles->map(fn ($item) => [
            'title' => $item->title,
            'slug' => route('articles.show', $item->slug),
            'thumbnail' => $item->thumbnail ? asset($item->thumbnail) : 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=400',
        ])->values()->toJson();

        return view('admin.articles.form', compact('article', 'recentArticles', 'sidebarArticlesJson'));
    }

    /**
     * Cập nhật thông tin thay đổi của bài viết vào cơ sở dữ liệu.
     * 
     * @param Request $request
     * @param string $id ID của bài viết
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $id)
    {
        // Kiểm tra và xác thực dữ liệu đầu vào của bài viết chỉnh sửa
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'format_type' => 'required|in:standard,lookbook,storytelling',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $article = Article::findOrFail($id);
            // Loại bỏ các trường không cần thiết ra khỏi mảng dữ liệu cập nhật
            $data = $request->except(['_token', '_method', 'thumbnail_file']);
            
            // Nếu admin thay đổi tiêu đề bài viết, tiến hành sinh lại slug mới tương ứng
            if ($request->title !== $article->title) {
                $data['slug'] = Str::slug($request->title) . '-' . time();
            }

            // Xử lý cập nhật file ảnh đại diện mới (nếu có tải lên hình ảnh mới)
            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/articles'), $filename);
                $data['thumbnail'] = '/uploads/articles/' . $filename;
            }

            // Cập nhật các thông tin thay đổi vào cơ sở dữ liệu
            $article->update($data);

            return redirect()->route('admin.articles.index')->with('success', 'Cập nhật bài viết thành công!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi khi cập nhật bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Thực hiện xóa bài viết ra khỏi hệ thống.
     * 
     * @param string $id ID bài viết
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(string $id)
    {
        $article = Article::findOrFail($id);
        // Xóa bản ghi bài viết
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Xóa bài viết thành công!');
    }

    /**
     * Phê duyệt bài viết do khách hàng viết và cộng điểm thưởng tích lũy (Loyalty Points) tương ứng.
     * 
     * @param Request $request
     * @param string $id ID bài viết
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, string $id)
    {
        $article = Article::findOrFail($id);
        // Lấy số điểm thưởng chỉ định từ request, mặc định là 0 nếu không điền
        $points = $request->input('points', 0);

        // Gọi phương thức approveAndReward từ model Article để cập nhật trạng thái đã duyệt và cộng điểm cho khách hàng
        if ($article->approveAndReward($points)) {
            return redirect()->route('admin.articles.index')->with('success', "Đã duyệt bài và cộng $points điểm cho khách hàng!");
        }

        return redirect()->route('admin.articles.index')->with('error', 'Bài viết không đủ điều kiện duyệt hoặc đã được duyệt!');
    }

    /**
     * Từ chối bài viết do khách hàng viết.
     * 
     * @param string $id ID bài viết
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(string $id)
    {
        $article = Article::findOrFail($id);
        
        if ($article->author_type === 'customer' && $article->status === 'pending') {
            $article->update([
                'status' => 'rejected'
            ]);
            return redirect()->route('admin.articles.index')->with('success', 'Đã từ chối bài viết thành công!');
        }

        return redirect()->route('admin.articles.index')->with('error', 'Bài viết không ở trạng thái chờ duyệt!');
    }

    /**
     * Phê duyệt hàng loạt các bài viết chờ duyệt đạt chuẩn kiểm duyệt của AI.
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkApproveAi()
    {
        $articles = Article::where('author_type', 'customer')
            ->where('status', 'pending')
            ->where('ai_checked', 1)
            ->where('ai_moderation_verdict', 'approved')
            ->get();

        $count = 0;
        foreach ($articles as $article) {
            $points = (int) ($article->ai_analysis['recommended_reward_points'] ?? 20);
            if ($article->approveAndReward($points)) {
                $count++;
            }
        }

        if ($count > 0) {
            return redirect()->route('admin.articles.index')->with('success', "Đã duyệt hàng loạt thành công $count bài viết đạt chuẩn AI và cộng điểm tương ứng!");
        }

        return redirect()->route('admin.articles.index')->with('error', 'Không tìm thấy bài viết chờ duyệt nào đạt chuẩn AI!');
    }
}
