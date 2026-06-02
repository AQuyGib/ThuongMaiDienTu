<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * HomeSectionController - Bộ điều khiển Quản lý Khung hiển thị Trang chủ (Admin Home Sections Manager).
 *
 * Nhiệm vụ chính:
 * 1. Hiển thị danh sách các khối (khung) sản phẩm hiển thị trên trang chủ ngoài Storefront.
 * 2. Cung cấp chức năng thiết lập các khung hiển thị động theo 3 loại: 
 *    - Sản phẩm mới nhất (Latest)
 *    - Theo danh mục (Category)
 *    - Chọn thủ công bằng tay (Manual - gắp sản phẩm).
 * 3. Cho phép tải lên hoặc nhập URL của banner quảng cáo đi kèm bên sườn của mỗi khung sản phẩm.
 * 4. Xử lý API tự động tìm kiếm sản phẩm (Autocomplete Search) hỗ trợ Admin gắp sản phẩm nhanh chóng.
 * 5. Hỗ trợ API cập nhật trực tiếp thứ tự sắp xếp (Reorder) của các khung sản phẩm ngoài trang chủ bằng AJAX kéo thả.
 */
class HomeSectionController extends Controller
{
    /**
     * Hiển thị danh sách các khung trang chủ hiện tại, sắp xếp theo thứ tự hiển thị định sẵn.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Lấy tất cả các khung trang chủ sắp xếp tăng dần theo cột `order`
        $sections = HomeSection::orderBy('order', 'asc')->get();
        return view('admin.home-sections.index', compact('sections'));
    }

    /**
     * Hiển thị giao diện form tạo mới một khung sản phẩm trang chủ.
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Lấy danh sách các danh mục cha (không có parent_id) kèm theo quan hệ danh mục con (children)
        // để hỗ trợ Admin chọn danh mục sản phẩm làm nguồn hiển thị cho khung
        $categories = Category::whereNull('parent_id')->with('children')->get();
        return view('admin.home-sections.create', compact('categories'));
    }

    /**
     * Xử lý lưu thông tin khung sản phẩm mới được tạo vào cơ sở dữ liệu.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Ràng buộc tính hợp lệ của dữ liệu gửi từ form
        $request->validate([
            'title' => 'required|string|max:255', // Tiêu đề khung sản phẩm bắt buộc
            'type' => 'required|in:latest,manual,category', // Loại hiển thị bắt buộc nằm trong 3 loại quy định
            'limit' => 'required|integer|min:1|max:20', // Giới hạn số lượng sản phẩm từ 1 đến 20
            'sidebar_banner_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // File ảnh banner tối đa 2MB
        ]);

        // Trích xuất các trường dữ liệu cần thiết từ request
        $data = $request->only(['title', 'type', 'category_id', 'limit', 'sidebar_link', 'order', 'status']);
        
        // Thiết lập trạng thái hiển thị (status) là true nếu checkbox được chọn, ngược lại là false
        $data['status'] = $request->has('status');

        // Ưu tiên 1: Nếu người dùng tải file ảnh banner lên từ máy tính
        if ($request->hasFile('sidebar_banner_file')) {
            // Lưu file vào thư mục public/banners
            $path = $request->file('sidebar_banner_file')->store('banners', 'public');
            // Lấy URL công khai để lưu vào database
            $data['sidebar_banner'] = Storage::url($path);
        } 
        // Ưu tiên 2: Nếu người dùng không tải file mà nhập URL hình ảnh trực tiếp
        elseif ($request->sidebar_banner_url) {
            $data['sidebar_banner'] = $request->sidebar_banner_url;
        }

        // Tạo bản ghi khung trang chủ mới trong database
        $section = HomeSection::create($data);

        // Nếu loại hiển thị là thủ công (manual) và có danh sách sản phẩm được chọn (product_ids)
        if ($request->type === 'manual' && $request->product_ids) {
            // Tách chuỗi ID sản phẩm được ngăn cách bằng dấu phẩy thành mảng
            $productIds = explode(',', $request->product_ids);
            
            // Duyệt mảng và ghi nhận quan hệ nhiều-nhiều vào bảng trung gian kèm theo thứ tự hiển thị sắp xếp
            foreach ($productIds as $index => $productId) {
                $section->products()->attach($productId, ['order' => $index]);
            }
        }

        return redirect()->route('admin.home-sections.index')->with('success', 'Tạo khung sản phẩm thành công!');
    }

    /**
     * Hiển thị giao diện chỉnh sửa khung sản phẩm trang chủ.
     * 
     * @param int $id ID của khung sản phẩm cần sửa
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Truy vấn khung trang chủ kèm danh sách sản phẩm đã được chọn (nếu có), trả về lỗi 404 nếu không tìm thấy
        $section = HomeSection::with('products')->findOrFail($id);
        
        // Lấy danh sách danh mục cha và các danh mục con trực thuộc
        $categories = Category::whereNull('parent_id')->with('children')->get();
        
        return view('admin.home-sections.edit', compact('section', 'categories'));
    }

    /**
     * Cập nhật thông tin thay đổi của khung sản phẩm vào cơ sở dữ liệu.
     * 
     * @param Request $request
     * @param int $id ID của khung
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Kiểm tra và ràng buộc dữ liệu đầu vào chỉnh sửa
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:latest,manual,category',
            'limit' => 'required|integer|min:1|max:20',
        ]);

        $section = HomeSection::findOrFail($id);
        $data = $request->only(['title', 'type', 'category_id', 'limit', 'sidebar_link', 'order', 'status']);
        $data['status'] = $request->has('status');

        // Xử lý cập nhật hình ảnh đại diện banner tương tự như lúc tạo mới
        if ($request->hasFile('sidebar_banner_file')) {
            $path = $request->file('sidebar_banner_file')->store('banners', 'public');
            $data['sidebar_banner'] = Storage::url($path);
        } elseif ($request->sidebar_banner_url) {
            $data['sidebar_banner'] = $request->sidebar_banner_url;
        }

        // Cập nhật thông tin khung sản phẩm
        $section->update($data);

        // Nếu loại hiển thị được chuyển thành hoặc giữ nguyên là chọn thủ công (manual)
        if ($request->type === 'manual') {
            // Xóa toàn bộ liên kết sản phẩm cũ trong bảng trung gian
            $section->products()->detach();
            
            // Nếu có danh sách sản phẩm mới được chọn, tiến hành ghi lại liên kết mới
            if ($request->product_ids) {
                $productIds = explode(',', $request->product_ids);
                foreach ($productIds as $index => $productId) {
                    $section->products()->attach($productId, ['order' => $index]);
                }
            }
        }

        return redirect()->route('admin.home-sections.index')->with('success', 'Cập nhật khung sản phẩm thành công!');
    }

    /**
     * Xóa một khung sản phẩm ra khỏi hệ thống.
     * 
     * @param int $id ID của khung cần xóa
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $section = HomeSection::findOrFail($id);
        // Xóa bản ghi
        $section->delete();
        
        return redirect()->route('admin.home-sections.index')->with('success', 'Xóa khung sản phẩm thành công!');
    }

    /**
     * Xử lý truy vấn tìm kiếm sản phẩm tự động (Autocomplete) trả về dữ liệu JSON.
     * Phục vụ cho ô tìm kiếm sản phẩm khi Admin gắp sản phẩm ở chế độ chọn thủ công (manual).
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        
        // Truy vấn tìm kiếm sản phẩm có tên khớp với từ khóa, giới hạn 10 kết quả
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->select('product_id', 'name', 'thumbnail', 'base_price')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    /**
     * API cập nhật thứ tự hiển thị sắp xếp của các khung sản phẩm ngoài trang chủ.
     * Được gọi thông qua AJAX từ tính năng kéo thả Sortable của Admin.
     * 
     * @param Request $request Mảng chứa danh sách thứ tự mới gửi lên
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request)
    {
        $orders = $request->orders; // Mảng cấu trúc: [id_khung => số_thứ_tự]
        
        // Duyệt danh sách và cập nhật cột order tương ứng cho từng khung sản phẩm
        foreach ($orders as $id => $order) {
            HomeSection::where('id', $id)->update(['order' => $order]);
        }
        
        return response()->json(['status' => 'success']);
    }
}
