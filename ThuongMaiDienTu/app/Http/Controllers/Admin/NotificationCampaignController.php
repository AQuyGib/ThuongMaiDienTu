<?php

namespace App\Http\Controllers; // giữ nguyên namespace cũ nếu cần, hoặc namespace đúng của file là App\Http\Controllers\Admin

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponFlashSale;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use App\Jobs\SendNotificationCampaignJob;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Controller quản lý các chiến dịch thông báo của phía Quản trị (Admin-side).
 * Cung cấp chức năng xem danh sách thông báo hệ thống, bộ lọc nâng cao, các biểu đồ thống kê số lượng thông báo theo ngày/tháng,
 * tạo chiến dịch gửi thông báo hàng loạt qua hàng đợi chạy ngầm (Queue), và quét cảnh báo tồn kho thấp.
 */
class NotificationCampaignController extends Controller
{
    /**
     * Khởi tạo Controller với NotificationService.
     */
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Hiển thị danh sách thông báo trong trang quản trị cùng với bộ lọc và biểu đồ thống kê.
     */
    public function index(Request $request): View
    {
        // Thu thập các tham số lọc từ URL query string
        $type = $request->query('type');
        $read = $request->query('read');
        $recipient = $request->query('recipient');
        $from = $request->query('from');
        $to = $request->query('to');

        // Khởi tạo Eloquent query nạp kèm thông tin người nhận (User) để tránh lỗi N+1 query
        $query = Notification::query()->with('user')->orderByDesc('notification_id');

        // Áp dụng các điều kiện lọc động
        if ($type) {
            $query->where('type', $type);
        }

        if ($read === 'read') {
            $query->whereNotNull('read_at');
        } elseif ($read === 'unread') {
            $query->whereNull('read_at');
        }

        if ($recipient) {
            $query->whereHas('user', function ($subQuery) use ($recipient) {
                $subQuery->where('full_name', 'like', '%' . $recipient . '%')
                    ->orWhere('email', 'like', '%' . $recipient . '%');
            });
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Tối ưu hóa hiệu năng: Lưu trữ dữ liệu thống kê biểu đồ vào Cache trong 1 giờ (3600 giây)
        $cacheData = Cache::remember('admin_notifications_index_stats_and_charts', 3600, function () {
            // Thống kê số lượng thông báo được tạo ra trong 30 ngày gần đây
            $chartStart = Carbon::now()->subDays(29)->startOfDay();
            $dailyStats = Notification::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->where('created_at', '>=', $chartStart)
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('total', 'date');

            $dailyLabels = [];
            $dailyValues = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dailyLabels[] = now()->subDays($i)->format('d/m');
                $dailyValues[] = (int) ($dailyStats[$date] ?? 0);
            }

            // Thống kê số lượng thông báo được tạo ra trong 12 tháng gần đây
            $monthlyStats = Notification::query()
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as total")
                ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('total', 'month');

            $monthlyLabels = [];
            $monthlyValues = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i)->format('Y-m');
                $monthlyLabels[] = now()->subMonths($i)->format('m/Y');
                $monthlyValues[] = (int) ($monthlyStats[$month] ?? 0);
            }

            // Tổng hợp các chỉ số đếm nhanh cho các hộp thông tin (Stats cards)
            $stats = [
                'total' => Notification::count(),
                'unread' => Notification::unread()->count(),
                'promo' => Notification::whereIn('type', ['promotion.auto', 'promotion.auto_updated', 'promotion.product_discount'])->count(),
                'manual' => Notification::where('type', 'admin.manual_campaign')->count(),
            ];

            return [
                'dailyChart' => [
                    'labels' => $dailyLabels,
                    'values' => $dailyValues,
                ],
                'monthlyChart' => [
                    'labels' => $monthlyLabels,
                    'values' => $monthlyValues,
                ],
                'stats' => $stats,
            ];
        });

        return view('admin.notifications.index', [
            // Phân trang danh sách thông báo, hiển thị 20 bản ghi mỗi trang
            'notifications' => $query->paginate(20)->withQueryString(),
            'stats' => $cacheData['stats'],
            'selectedType' => $type,
            'selectedRead' => $read,
            // Định nghĩa nhãn hiển thị trực quan cho các loại thông báo khác nhau
            'typeOptions' => [
                'promotion.auto' => 'Khuyến mãi tự động',
                'promotion.auto_updated' => 'Khuyến mãi cập nhật',
                'promotion.product_discount' => 'Giảm giá sản phẩm',
                'admin.manual_campaign' => 'Gửi tay',
                'order.created' => 'Đơn hàng mới',
                'order.status_updated' => 'Cập nhật đơn hàng',
                'admin.order.created' => 'Đơn hàng cho admin',
                'article.published' => 'Bài viết mới',
                'review.created' => 'Review mới',
                'inventory.low_stock' => 'Tồn kho thấp',
            ],
            'dailyChart' => $cacheData['dailyChart'],
            'monthlyChart' => $cacheData['monthlyChart'],
            // Nạp sẵn dữ liệu mẫu cho autocomplete của modal tạo thông báo
            'promoItems' => CouponFlashSale::query()->orderByDesc('promo_id')->limit(20)->get(),
            'products' => Product::query()->orderByDesc('product_id')->limit(20)->get(),
            'roles' => Role::query()->orderBy('role_id')->get(),
        ]);
    }

    /**
     * Hiển thị trang dashboard tóm tắt phân tích các số liệu thông báo.
     * Cung cấp các thống kê như tổng số thông báo, số thông báo chưa đọc, số lượng thông báo hôm nay và trong tháng hiện tại.
     */
    public function dashboard(): View
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::unread()->count(),
            'today' => Notification::whereDate('created_at', now()->toDateString())->count(),
            'month' => Notification::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count(),
        ];

        // Lấy top 5 loại thông báo được tạo ra nhiều nhất để hiển thị biểu đồ cơ cấu
        $topTypes = Notification::query()
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('admin.notifications.dashboard', [
            'stats' => $stats,
            'topTypes' => $topTypes,
            'typeOptions' => [
                'promotion.auto' => 'Khuyến mãi tự động',
                'promotion.auto_updated' => 'Khuyến mãi cập nhật',
                'promotion.product_discount' => 'Giảm giá sản phẩm',
                'admin.manual_campaign' => 'Gửi tay',
                'order.created' => 'Đơn hàng mới',
                'order.status_updated' => 'Cập nhật đơn hàng',
                'admin.order.created' => 'Đơn hàng cho admin',
                'article.published' => 'Bài viết mới',
                'review.created' => 'Review mới',
                'inventory.low_stock' => 'Tồn kho thấp',
            ],
        ]);
    }

    /**
     * API JSON tìm kiếm mã khuyến mãi / chương trình flash sale phục vụ tính năng autocomplete trên giao diện.
     */
    public function searchPromo(Request $request)
    {
        $query = $request->get('q');
        $promos = CouponFlashSale::query()
            ->where('code', 'LIKE', "%{$query}%")
            ->orWhere('promo_type', 'LIKE', "%{$query}%")
            ->orWhere('promo_id', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        return response()->json($promos);
    }

    /**
     * API JSON tìm kiếm tài khoản người dùng phục vụ cho việc gửi thông báo tới các cá nhân cụ thể.
     */
    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        $users = User::query()
            ->where(function($q) use ($query) {
                $q->where('full_name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('user_id', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get(['user_id', 'full_name', 'email']);

        return response()->json($users);
    }

    /**
     * Xem chi tiết một thông báo cụ thể từ phía quản trị viên.
     * Tự động đánh dấu thông báo là đã đọc nếu nó chưa từng được đọc trước đó.
     */
    public function show(Notification $notification): View
    {
        if (is_null($notification->read_at)) {
            $this->notificationService->markAsRead($notification);
        }

        $notification->load('user');

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Hiển thị giao diện tạo chiến dịch thông báo mới độc lập.
     */
    public function create(): View
    {
        return view('admin.notifications.create', [
            'promoItems' => CouponFlashSale::query()->orderByDesc('promo_id')->limit(20)->get(),
            'products' => Product::query()->orderByDesc('product_id')->limit(20)->get(),
            'roles' => Role::query()->orderBy('role_id')->get(),
        ]);
    }

    /**
     * Xử lý lưu trữ chiến dịch thông báo mới và phân phối tới người nhận.
     * Hỗ trợ chuẩn hóa payload sản phẩm, khuyến mãi liên quan, xác thực động vai trò người nhận,
     * và đẩy xử lý gửi thông báo hàng loạt vào hàng đợi (Queue Job) để tối ưu hiệu năng.
     */
    public function store(Request $request): RedirectResponse
    {
        // Đồng bộ hóa trường product_id đơn lẻ (nếu có gửi lên từ form cũ) vào mảng product_ids
        if ($request->has('product_id') && !empty($request->input('product_id'))) {
            $request->merge(['product_ids' => [(int) $request->input('product_id')]]);
        }
        // Đồng bộ hóa trường promo_id đơn lẻ vào mảng promo_ids
        if ($request->has('promo_id') && !empty($request->input('promo_id'))) {
            $request->merge(['promo_ids' => [(int) $request->input('promo_id')]]);
        }

        // Lấy danh sách role_id hợp lệ từ DB để tạo mảng kiểm tra động (ví dụ: role:1, role:2...)
        $validRoleTargets = Role::query()->pluck('role_id')->map(fn($id) => 'role:' . $id)->toArray();
        $allowedTargets = array_merge(['all', 'specific'], $validRoleTargets);

        // Tiến hành kiểm tra tính hợp lệ của dữ liệu đầu vào
        $data = $request->validate([
            'target' => ['required', 'in:' . implode(',', $allowedTargets)],
            'user_ids' => ['required_if:target,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,user_id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:1000'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,product_id'],
            'promo_ids' => ['nullable', 'array'],
            'promo_ids.*' => ['integer', 'exists:coupons_flash_sales,promo_id'],
        ]);

        if ($data['target'] === 'specific') {
            // Trường hợp gửi cho danh sách tài khoản cụ thể đã chọn
            $userIds = $data['user_ids'];
        } elseif (str_starts_with($data['target'], 'role:')) {
            // Trường hợp lọc theo một vai trò (role) cụ thể từ database (ví dụ: Admin, Sale...)
            $roleId = (int) str_replace('role:', '', $data['target']);
            $userIds = User::query()->where('role_id', $roleId)->pluck('user_id')->toArray();
        } else {
            // Trường hợp 'all' - Gửi cho tất cả mọi tài khoản đang có trong hệ thống
            $userIds = User::query()->pluck('user_id')->toArray();
        }

        // Tạo mảng dữ liệu payload thông báo gửi đi
        $payload = [
            'type' => 'admin.manual_campaign',
            'title' => $data['title'],
            'content' => $data['content'],
            'action_url' => $data['action_url'] ?? url('/'),
            // Lưu trữ siêu dữ liệu (metadata) hỗ trợ truy vết các liên kết sản phẩm, coupon sau này
            'data' => [
                'product_ids' => $data['product_ids'] ?? [],
                'promo_ids' => $data['promo_ids'] ?? [],
                'target' => $data['target'],
            ],
        ];

        // Đẩy chiến dịch gửi thông báo hàng loạt vào hàng đợi chạy ngầm (Laravel Queue Job)
        SendNotificationCampaignJob::dispatch($userIds, $payload);

        // Xóa cache thống kê trang quản trị vì dữ liệu đã thay đổi
        Cache::forget('admin_notifications_index_stats_and_charts');

        return back()->with('success', 'Chiến dịch gửi thông báo đã được đưa vào hàng đợi xử lý.');
    }

    /**
     * Đánh dấu một thông báo là đã đọc (Gọi từ phía quản trị).
     */
    public function markAsRead(Notification $notification): RedirectResponse
    {
        $this->notificationService->markAsRead($notification);

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    /**
     * Xóa hoàn toàn một thông báo cụ thể khỏi hệ thống.
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();

        // Xóa cache thống kê trang quản trị
        Cache::forget('admin_notifications_index_stats_and_charts');

        return back()->with('success', 'Đã xóa thông báo.');
    }

    /**
     * Xóa hàng loạt nhiều thông báo cùng lúc (nhận mảng ID gửi từ checkbox).
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        // Xác thực danh sách ID thông báo cần xóa
        $data = $request->validate([
            'notification_ids' => ['required', 'array', 'min:1'],
            'notification_ids.*' => ['integer', 'exists:notifications,notification_id'],
        ]);

        // Thực hiện xóa hàng loạt trực tiếp trong database
        Notification::query()
            ->whereIn('notification_id', $data['notification_ids'])
            ->delete();

        // Xóa cache thống kê trang quản trị
        Cache::forget('admin_notifications_index_stats_and_charts');

        return back()->with('success', 'Đã xóa các thông báo đã chọn.');
    }

    /**
     * Quét và kiểm tra tồn kho thấp (Low stock auto-scanning).
     * Tìm tất cả các biến thể sản phẩm có số lượng sản phẩm trong kho lớn hơn 0 và nhỏ hơn hoặc bằng ngưỡng cảnh báo (10).
     * Tự động tạo và gửi thông báo cảnh báo trực quan cho toàn bộ tài khoản Admin.
     */
    public function lowStockCheck(): RedirectResponse
    {
        $threshold = 10; // Ngưỡng số lượng bắt đầu cảnh báo
        
        // Truy vấn các biến thể có đếm số lượng bản ghi kho thực tế nằm dưới ngưỡng
        $variants = \App\Models\ProductVariant::query()
            ->with(['product'])
            ->withCount('inventoryItems')
            ->having('inventory_items_count', '>', 0)
            ->having('inventory_items_count', '<=', $threshold)
            ->get();

        // Lấy toàn bộ tài khoản thuộc nhóm quản trị viên hệ thống để phân phối cảnh báo
        $admins = User::query()->whereIn('role_id', [1, 2, 4])->get();
        $insertData = [];
        $now = Carbon::now();
        $count = 0;

        // Duyệt qua từng biến thể sản phẩm bị thiếu hụt tồn kho
        foreach ($variants as $variant) {
            $stock = (int) $variant->inventory_items_count;
            
            // Xây dựng nội dung và đường dẫn liên kết cho cảnh báo
            $adminPayload = [
                'type' => 'inventory.low_stock',
                'title' => 'Tồn kho thấp: ' . ($variant->product->name ?? 'Sản phẩm'),
                'content' => 'Biến thể ' . $variant->label . ' hiện chỉ còn ' . $stock . ' sản phẩm trong kho.',
                'action_url' => url('/admin/products/' . ($variant->product->product_id ?? 0)),
                'data' => json_encode([
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                    'stock' => $stock,
                    'threshold' => $threshold,
                ]),
            ];

            // Tạo bản ghi tương ứng cho từng admin nhận cảnh báo
            foreach ($admins as $admin) {
                $insertData[] = [
                    'user_id' => $admin->user_id,
                    'type' => $adminPayload['type'],
                    'title' => $adminPayload['title'],
                    'content' => $adminPayload['content'],
                    'action_url' => $adminPayload['action_url'],
                    'data' => $adminPayload['data'],
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $count++;
        }

        // Thực hiện insert hàng loạt (Bulk insert) tối ưu hóa, chia nhỏ 1000 bản ghi mỗi lượt để tránh quá tải database
        if (!empty($insertData)) {
            collect($insertData)->chunk(1000)->each(function ($chunk) {
                Notification::insert($chunk->toArray());
            });
        }

        // Xóa cache thống kê trang quản trị để hiển thị các số liệu mới nhất
        Cache::forget('admin_notifications_index_stats_and_charts');

        return back()->with('success', 'Đã kiểm tra tồn kho thấp cho ' . $count . ' biến thể.');
    }

    /**
     * API JSON trả về số lượng thông báo chưa đọc của Admin đang đăng nhập.
=======
     * API JSON trả về số lượng thông báo chưa đọc của admin hiện tại.
>>>>>>> origin/master
     */
    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $this->notificationService->unreadCountForUser($request->user()),
        ]);
    }
}

