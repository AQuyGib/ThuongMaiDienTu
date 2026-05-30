<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

/**
 * ArticleFrontendController - Bộ điều khiển giao diện bài viết phía khách hàng (Frontend Article Controller).
 *
 * Nhiệm vụ chính:
 * 1. Hiển thị trang danh sách bài viết công khai với cơ chế chia bài viết nổi bật (Featured) và bài viết mới nhất (Latest), hỗ trợ lọc theo thẻ tag.
 * 2. Cho phép người dùng xem chi tiết một bài viết kèm theo các bài viết liên quan khác và thông tin phiếu sửa chữa liên quan (nếu có).
 * 3. Cho phép khách hàng tự đăng bài viết mới lên cộng đồng (Community Posts) ở trạng thái chờ duyệt (Pending).
 * 4. Hỗ trợ khách hàng tự chỉnh sửa thông tin bài viết của chính họ (tự động chuyển trạng thái về chờ duyệt lại sau khi chỉnh sửa).
 * 5. Cho phép khách hàng tự xóa/rút lại bài viết đã gửi lên của chính họ.
 * 6. Kiểm tra bảo mật nghiêm ngặt ở Server-side, đảm bảo người dùng chỉ được sửa/xóa bài viết do chính họ làm tác giả, tránh giả mạo bằng F12.
 */
class ArticleFrontendController extends Controller
{
    /**
     * Hiển thị danh sách bài viết công khai cho khách hàng.
     * 
     * @param Request $request Yêu cầu chứa tham số thẻ lọc tag
     * @return \Illuminate\View\View Giao diện danh sách bài viết
     */
    public function index(Request $request)
    {
        // Lấy thẻ tag lọc từ URL query string
        $tag = $request->input('tag');

        // BƯỚC 1: Lấy 3 bài viết đã được duyệt mới nhất để hiển thị ở khu vực bài viết nổi bật (Featured Articles)
        $featuredArticles = Article::where('status', 'approved')
            ->when($tag, function ($query, $tag) {
                $this->applyTagFilter($query, $tag);
            })
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // Thu thập danh sách ID của các bài viết nổi bật để loại trừ khỏi danh sách bên dưới, tránh hiển thị trùng lặp
        $featuredIds = $featuredArticles->pluck('article_id')->toArray();

        // BƯỚC 2: Truy vấn danh sách các bài viết còn lại.
        // Điều kiện hiển thị: các bài viết đã duyệt ('approved') HOẶC bài viết ở trạng thái chờ duyệt ('pending') của chính người dùng đang đăng nhập.
        $latestArticles = Article::query()
            ->where(function($query) {
                $query->where('status', 'approved');
                // Nếu khách hàng đã đăng nhập, cho phép họ nhìn thấy bài viết đang chờ duyệt của chính mình
                if (\Illuminate\Support\Facades\Auth::check()) {
                    $query->orWhere(function($q) {
                        $q->where('author_id', \Illuminate\Support\Facades\Auth::id())
                          ->where('author_type', 'customer');
                    });
                }
            })
            ->when($tag, function ($query, $tag) {
                // Áp dụng bộ lọc thẻ tag nếu khách hàng chọn phân loại bài viết
                $this->applyTagFilter($query, $tag);
            })
            ->whereNotIn('article_id', $featuredIds) // Loại trừ các bài nổi bật đã hiển thị ở trên
            ->orderBy('created_at', 'desc') // Sắp xếp giảm dần theo thời gian tạo
            ->paginate(12) // Phân trang 12 bài viết trên một trang
            ->withQueryString(); // Duy trì tham số lọc khi chuyển trang
            
        // Trả về view danh sách bài viết
        return view('articles.index', compact('featuredArticles', 'latestArticles', 'tag'));
    }

    /**
     * Áp dụng bộ lọc thẻ tag vào câu truy vấn danh sách bài viết.
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query Đối tượng xây dựng truy vấn Eloquent
     * @param string $tag Tên thẻ tag cần lọc
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyTagFilter($query, string $tag)
    {
        return match ($tag) {
            // Lọc theo định dạng bài viết (standard - tiêu chuẩn, lookbook - bộ sưu tập, storytelling - kể chuyện)
            'standard', 'lookbook', 'storytelling' => $query->where('format_type', $tag),
            // Lọc các bài viết chính thức do ban quản trị DienMay Pro biên soạn
            'dienmay-pro' => $query->where('author_type', 'admin'),
            // Lọc chung phong cách sống (lifestyle) - hiển thị toàn bộ bài viết
            'lifestyle' => $query,
            // Giá trị mặc định không lọc thêm gì
            default => $query,
        };
    }

    /**
     * Hiển thị trang chi tiết bài viết công khai dựa theo slug.
     * 
     * @param string $slug Đường dẫn thân thiện của bài viết
     * @return \Illuminate\View\View Giao diện chi tiết bài viết
     */
    public function show($slug)
    {
        // Truy vấn thông tin bài viết theo slug, chỉ cho phép hiển thị các bài viết đã duyệt
        $article = Article::where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail(); // Trả về trang lỗi 404 nếu không tìm thấy
             
        // Lấy thông tin phiếu sửa chữa liên quan gắn kèm với bài viết này nếu có
        $relatedTicket = $article->repairTicket;
        
        // Lấy ra danh sách 5 bài viết mới nhất đã duyệt (loại trừ bài viết hiện tại) để hiển thị ở thanh bên (Sidebar)
        $recentArticles = Article::where('status', 'approved')
            ->where('article_id', '!=', $article->article_id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
        
        return view('articles.show', compact('article', 'relatedTicket', 'recentArticles'));
    }

    /**
     * Hiển thị giao diện form viết bài mới cho khách hàng.
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $article = new Article(); // Khởi tạo model rỗng phục vụ form tạo
        return view('articles.create', compact('article'));
    }

    /**
     * Tiếp nhận dữ liệu bài viết mới do khách hàng gửi lên và lưu ở trạng thái chờ duyệt (pending).
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Kiểm tra và ràng buộc dữ liệu đầu vào gửi từ Form
        $request->validate([
            'title' => 'required|string|max:255', // Tiêu đề bắt buộc, tối đa 255 ký tự
            'summary' => 'nullable|string|max:500', // Tóm tắt ngắn gọn tối đa 500 ký tự
            'content' => 'required', // Nội dung bài viết bắt buộc nhập
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Ảnh đại diện tối đa 2MB
        ]);

        try {
            // Lấy các dữ liệu hợp lệ được phép điền từ phía người dùng
            $data = $request->only(['title', 'summary', 'content']);
            
            // Tự sinh slug từ tiêu đề tiếng Việt và hậu tố thời gian để đảm bảo tính duy nhất
            $data['slug'] = \Illuminate\Support\Str::slug($request->title) . '-' . time();
            $data['author_id'] = \Illuminate\Support\Facades\Auth::id(); // Lấy ID tài khoản người dùng đang đăng nhập
            $data['author_type'] = 'customer'; // Phân loại tác giả là khách hàng (Customer)
            $data['status'] = 'pending';   // Thiết lập trạng thái mặc định ban đầu là chờ duyệt (Pending)
            $data['format_type'] = 'standard'; // Gán định dạng hiển thị mặc định

            // Xử lý tải ảnh đại diện bài viết lên hệ thống
            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/articles'), $filename);
                $data['thumbnail'] = '/uploads/articles/' . $filename;
            }

            // Tiến hành ghi bản ghi bài viết mới vào DB
            Article::create($data);

            return redirect()->route('articles.index')->with('success', 'Bài viết đã được gửi và đang chờ quản trị viên duyệt!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi khi gửi bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị giao diện sửa bài viết của khách hàng.
     * Có kiểm tra bảo mật nghiêm ngặt để tránh người khác dùng F12 sửa ID bài viết của người khác.
     * 
     * @param int $id ID bài viết cần chỉnh sửa
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Truy vấn bài viết theo ID kết hợp bắt buộc điều kiện tác giả là chính người dùng đang đăng nhập
        $article = Article::where('article_id', $id)
            ->where('author_id', \Illuminate\Support\Facades\Auth::id())
            ->where('author_type', 'customer')
            ->firstOrFail(); // Nếu cố tình can thiệp hoặc sai ID tác giả, trả về lỗi HTTP 404

        return view('articles.create', compact('article'));
    }

    /**
     * Cập nhật thông tin sửa đổi bài viết của khách hàng vào cơ sở dữ liệu.
     * Tự động chuyển trạng thái bài viết về chờ duyệt lại (pending) để đảm bảo an toàn nội dung.
     * 
     * @param Request $request
     * @param int $id ID bài viết
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Kiểm tra bảo mật quyền sở hữu bài viết trước khi thực hiện xử lý
        $article = Article::where('article_id', $id)
            ->where('author_id', \Illuminate\Support\Facades\Auth::id())
            ->where('author_type', 'customer')
            ->firstOrFail();

        // Ràng buộc dữ liệu chỉnh sửa bài viết đầu vào
        $request->validate([
            'title' => 'required|string|max:255',
            'summary' => 'nullable|string|max:500',
            'content' => 'required',
            'thumbnail_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            $data = $request->only(['title', 'summary', 'content']);
            
            // Nếu tiêu đề bị sửa đổi, tiến hành cập nhật lại slug mới tương ứng
            if ($request->title !== $article->title) {
                $data['slug'] = \Illuminate\Support\Str::slug($request->title) . '-' . time();
            }
            
            // BẮT BUỘC: Khi khách hàng chỉnh sửa bài viết đã duyệt, tự động reset trạng thái bài viết về chờ duyệt (pending)
            // để ban quản trị kiểm soát chất lượng nội dung sửa đổi, chống F12 lách luật sửa nội dung xấu sau khi đã được duyệt.
            $data['status'] = 'pending'; 

            // Cập nhật ảnh đại diện mới nếu có tải lên
            if ($request->hasFile('thumbnail_file')) {
                $file = $request->file('thumbnail_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/articles'), $filename);
                $data['thumbnail'] = '/uploads/articles/' . $filename;
            }

            // Tiến hành cập nhật dữ liệu mới vào DB
            $article->update($data);

            return redirect()->route('articles.index')->with('success', 'Cập nhật bài viết thành công! Bài viết sẽ được duyệt lại.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Lỗi khi cập nhật bài viết: ' . $e->getMessage());
        }
    }

    /**
     * Cho phép khách hàng xóa/rút lại bài viết của chính mình.
     * Ràng buộc bảo mật quyền sở hữu bắt buộc ở Server-side.
     * 
     * @param int $id ID bài viết cần xóa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        // Kiểm tra quyền tác giả: chỉ cho phép xóa bài viết của chính mình
        $article = Article::where('article_id', $id)
            ->where('author_id', \Illuminate\Support\Facades\Auth::id())
            ->where('author_type', 'customer')
            ->firstOrFail();

        // Thực hiện xóa bài viết
        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Đã rút lại bài viết thành công!');
    }
}
