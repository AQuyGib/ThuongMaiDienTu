<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Cashbook;
use App\Models\FlashSale;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\RepairTicket;
use App\Models\Review;
use App\Models\ServiceInvoice;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoComment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * PHƯƠNG THỨC HIỂN THỊ TRANG CHỦ BẢNG ĐIỀU KHIỂN (ADMIN DASHBOARD INDEX)
     *
     * 1. Nhiệm vụ chính:
     *    - Kiểm tra phân quyền truy cập (chỉ cho phép Quản trị viên/Quản lý, chặn Kỹ thuật viên).
     *    - Tổng hợp toàn bộ chỉ số vận hành và tài chính thời gian thực từ 12+ Models trong hệ thống.
     *    - Tổ chức dữ liệu thành các cấu trúc mảng logic để truyền qua View `admin.dashboard`.
     *
     * 2. Các nhóm dữ liệu thống kê thu thập ($stats):
     *    - KPI Tài chính & Quy mô: Doanh thu thực tế (Incomes), chi phí thực chi (Expenses) từ Sổ quỹ, tổng số Đơn hàng, Sản phẩm, Khách hàng và Đánh giá.
     *    - Phân hệ Sửa chữa (Repair): Phân bổ số phiếu sửa chữa theo trạng thái để theo dõi tiến độ sửa chữa thiết bị của khách hàng.
     *    - Phân hệ Dịch vụ (Service): Doanh thu các hóa đơn dịch vụ đã thanh toán (paid) và số lượng hóa đơn dịch vụ chưa thanh toán.
     *    - Phân hệ Media & CMS: Số lượng video đã đăng, tổng lượt xem, tổng lượt thích, tổng số bài viết Lookbook/Ecosystem và tổng đơn nhập kho.
     *    - Phân hệ Kiểm duyệt: Số lượng bình luận video và đánh giá sản phẩm mới chưa duyệt để quản trị viên kịp thời xử lý.
     *    - Biểu đồ thống kê:
     *         + Doanh thu/Chi phí 6 tháng gần nhất (Bar Chart): Nhóm theo năm-tháng để vẽ xu hướng tài chính.
     *         + Trạng thái đơn hàng (Donut Chart): Thống kê số đơn hàng theo từng trạng thái để hiển thị phân bổ.
     *    - Cảnh báo và Đề xuất:
     *         + Top 5 sản phẩm bán chạy nhất: Dựa trên số lượng bán (quantity) từ chi tiết đơn hàng.
     *         + Cảnh báo tồn kho thấp: Tìm các biến thể sản phẩm có số lượng tồn kho thực tế (In_Stock) <= 3 để cảnh báo nhập hàng.
     *         + Các chương trình Flash Sale đang hoạt động trong thời gian thực.
     *
     * 3. Trả về:
     *    - \Illuminate\View\View : Giao diện trang chủ bảng điều khiển admin chứa các biểu đồ và số liệu thống kê.
     */
    public function index()
    {
        // 1. CHẶN PHÂN QUYỀN: Nếu user là Kỹ thuật viên (Role ID = 4), không có quyền xem dashboard, chuyển hướng sang trang khách hàng.
        if (auth()->user()->role_id == 4) {
            return redirect()->route('admin.customers.index');
        }

        // 2. THỐNG KÊ TỔNG QUAN (KPI CARDS): Đếm số lượng sản phẩm, khách hàng, đơn hàng,
        //    tính tổng thu nhập & chi phí từ sổ quỹ (Cashbook), và lấy danh sách 5 đơn hàng đầu tiên (xếp tăng dần theo ID).
        $stats = [
            'total_products' => Product::count(),
            'total_users'    => User::count(),
            'total_orders'   => Order::count(),
            'total_income'   => Cashbook::ofType('Income')->sum('amount'),
            'total_expense'  => Cashbook::ofType('Expense')->sum('amount'),
            'recent_orders'  => Order::with('user')->orderBy('order_id', 'asc')->take(5)->get(),
        ];

        // 3. PHÂN HỆ SỬA CHỮA (REPAIR TICKETS): Thống kê số lượng phiếu theo từng trạng thái cụ thể
        //    để vẽ thanh tiến trình (progress bar) và danh sách theo dõi trạng thái.
        $repairStatuses = ['Received', 'Checking', 'Under_Repair', 'Waiting_Parts', 'Done'];
        $repairByStatus = [];
        foreach ($repairStatuses as $s) {
            $repairByStatus[$s] = RepairTicket::where('status', $s)->count();
        }
        $stats['repair_total']     = RepairTicket::count();
        $stats['repair_by_status'] = $repairByStatus;

        // 4. HÓA ĐƠN DỊCH VỤ (SERVICE INVOICES): Tổng hợp tổng số hóa đơn, doanh thu dịch vụ thực tế (paid),
        //    và số hóa đơn dịch vụ còn đang chờ xử lý (trạng thái nháp hoặc đã phát hành nhưng chưa thanh toán).
        $stats['service_total']   = ServiceInvoice::count();
        $stats['service_revenue'] = ServiceInvoice::where('status', 'paid')->sum('total_amount');
        $stats['service_pending'] = ServiceInvoice::whereIn('status', ['issued', 'draft'])->count();

        // 5. TRUYỀN THÔNG & VIDEO (MEDIA ENGAGEMENT): Tổng hợp số lượng video, tổng views, tổng likes để đánh giá độ phủ nội dung.
        $stats['video_total'] = Video::count();
        $stats['video_views'] = (int) Video::sum('views');
        $stats['video_likes'] = (int) Video::sum('likes');

        // 6. KIỂM DUYỆT CMS (MODERATION COUNTS): Đếm số đánh giá và bình luận video chưa được duyệt.
        //    Đồng thời lấy tổng số đánh giá chính gốc (reviews_total - loại bỏ phản hồi của admin/nhân viên).
        $stats['reviews_pending']  = Review::where('is_approved', 0)->whereNull('parent_id')->count();
        $stats['comments_pending'] = VideoComment::where('is_approved', 0)->whereNull('parent_id')->count();
        $stats['reviews_total']    = Review::whereNull('parent_id')->count();

        // 7. BIỂU ĐỒ TRẠNG THÁI ĐƠN HÀNG (ORDER DONUT CHART): GROUP BY để đếm số đơn hàng theo từng trạng thái (Pending, Delivered, v.v.).
        $orderStatuses = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status')->toArray();
        $stats['order_by_status'] = $orderStatuses;

        // 8. BIỂU ĐỒ DOANH THU & CHI PHÍ 6 THÁNG (REVENUE TREND BAR CHART):
        //    Lặp qua 6 tháng gần nhất để tính tổng Income & Expense của mỗi tháng từ Cashbook.
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date  = Carbon::now()->subMonths($i);
            $label = $date->format('m/Y');
            $income  = Cashbook::ofType('Income')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            $expense = Cashbook::ofType('Expense')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
            $monthlyRevenue[] = [
                'label'   => $label,
                'income'  => (int) $income,
                'expense' => (int) $expense,
            ];
        }
        $stats['monthly_revenue'] = $monthlyRevenue;

        // 9. TOP 5 SẢN PHẨM BÁN CHẠY (BEST SELLERS):
        //    Tính tổng số lượng đã bán từ chi tiết hóa đơn (order_details) để xếp hạng.
        $stats['top_products'] = DB::table('order_details')
            ->select('product_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(price) as total_revenue'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 10. CẢNH BÁO TỒN KHO THẤP (LOW STOCK ALERT):
        //     Sử dụng truy vấn phụ (selectSub) đếm các sản phẩm 'In_Stock' trong kho cho từng biến thể sản phẩm,
        //     lọc những biến thể có số lượng tồn kho <= 3 để hiển thị cảnh báo nguy cơ hết hàng.
        $lowStockThreshold = 3;
        $stats['low_stock'] = ProductVariant::select('product_variants.*')
            ->selectSub(
                InventoryItem::selectRaw('COUNT(*)')
                    ->whereColumn('inventory_items.variant_id', 'product_variants.variant_id')
                    ->where('status', 'In_Stock'),
                'stock_count'
            )
            ->having('stock_count', '<=', $lowStockThreshold)
            ->with('product')
            ->orderBy('stock_count')
            ->limit(5)
            ->get();

        // 11. CHƯƠNG TRÌNH KHUYẾN MÃI (FLASH SALES): Lấy các chương trình Flash Sale đang hoạt động trong khung giờ hiện tại.
        $stats['active_flash_sales'] = FlashSale::where('is_active', true)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->withCount('products')
            ->get();

        // 12. CMS BÀI VIẾT & ĐƠN NHẬP KHO (ARTICLES & PURCHASE ORDERS):
        //     Lấy tổng số bài viết của Lookbook/Ecosystem và tổng đơn nhập kho hàng từ nhà cung cấp.
        $stats['articles_total'] = Article::count();
        $stats['purchase_orders_total'] = PurchaseOrder::count();

        return view('admin.dashboard', compact('stats'));
    }
}
