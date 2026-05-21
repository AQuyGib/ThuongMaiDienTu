<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponFlashSale;
use App\Models\Notification;
use App\Models\Product;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class NotificationCampaignController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function index(Request $request): View
    {
        $type = $request->query('type');
        $read = $request->query('read');
        $recipient = $request->query('recipient');
        $from = $request->query('from');
        $to = $request->query('to');

        $query = Notification::query()->with('user')->orderByDesc('notification_id');

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

        return view('admin.notifications.index', [
            'notifications' => $query->paginate(20)->withQueryString(),
            'stats' => [
                'total' => Notification::count(),
                'unread' => Notification::unread()->count(),
                'promo' => Notification::whereIn('type', ['promotion.auto', 'promotion.auto_updated', 'promotion.product_discount'])->count(),
                'manual' => Notification::where('type', 'admin.manual_campaign')->count(),
            ],
            'selectedType' => $type,
            'selectedRead' => $read,
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
            'dailyChart' => [
                'labels' => $dailyLabels,
                'values' => $dailyValues,
            ],
            'monthlyChart' => [
                'labels' => $monthlyLabels,
                'values' => $monthlyValues,
            ],
            'promoItems' => CouponFlashSale::query()->orderByDesc('promo_id')->limit(20)->get(),
            'products' => Product::query()->orderByDesc('product_id')->limit(20)->get(),
        ]);
    }

    public function dashboard(): View
    {
        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::unread()->count(),
            'today' => Notification::whereDate('created_at', now()->toDateString())->count(),
            'month' => Notification::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
        ];

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

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => Notification::unread()->count(),
        ]);
    }

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

    public function show(Notification $notification): View
    {
        if (is_null($notification->read_at)) {
            $this->notificationService->markAsRead($notification);
        }

        $notification->load('user');

        return view('admin.notifications.show', compact('notification'));
    }

    public function create(): View
    {
        return view('admin.notifications.create', [
            'promoItems' => CouponFlashSale::query()->orderByDesc('promo_id')->limit(20)->get(),
            'products' => Product::query()->orderByDesc('product_id')->limit(20)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target' => ['required', 'in:admins,users,all,specific'],
            'user_ids' => ['required_if:target,specific', 'array'],
            'user_ids.*' => ['integer', 'exists:users,user_id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:1000'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'integer', 'exists:products,product_id'],
            'promo_id' => ['nullable', 'integer', 'exists:coupons_flash_sales,promo_id'],
        ]);

        if ($data['target'] === 'specific') {
            $users = User::query()->whereIn('user_id', $data['user_ids'])->get();
        } else {
            $users = match ($data['target']) {
                'admins' => User::query()->whereIn('role_id', [1, 2, 4])->get(),
                'users' => User::query()->whereNotIn('role_id', [1, 2, 4])->get(),
                default => User::query()->get(),
            };
        }

        foreach ($users as $user) {
            $this->notificationService->createForUser($user, [
                'type' => 'admin.manual_campaign',
                'title' => $data['title'],
                'content' => $data['content'],
                'action_url' => $data['action_url'] ?? url('/'),
                'data' => [
                    'product_id' => $data['product_id'] ?? null,
                    'promo_id' => $data['promo_id'] ?? null,
                    'target' => $data['target'],
                ],
            ]);
        }

        return back()->with('success', 'Đã gửi thông báo thành công.');
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        $this->notificationService->markAsRead($notification);

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->delete();

        return back()->with('success', 'Đã xóa thông báo.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'notification_ids' => ['required', 'array', 'min:1'],
            'notification_ids.*' => ['integer', 'exists:notifications,notification_id'],
        ]);

        Notification::query()
            ->whereIn('notification_id', $data['notification_ids'])
            ->delete();

        return back()->with('success', 'Đã xóa các thông báo đã chọn.');
    }

    public function lowStockCheck(): RedirectResponse
    {
        $threshold = 10;
        $variants = \App\Models\ProductVariant::query()
            ->with(['product', 'inventoryItems'])
            ->get()
            ->filter(function ($variant) use ($threshold) {
                $stock = $variant->inventoryItems->count();
                return $stock > 0 && $stock <= $threshold;
            });

        $count = 0;
        foreach ($variants as $variant) {
            $adminPayload = [
                'type' => 'inventory.low_stock',
                'title' => 'Tồn kho thấp: ' . ($variant->product->name ?? 'Sản phẩm'),
                'content' => 'Biến thể ' . $variant->label . ' hiện chỉ còn ' . $variant->inventoryItems->count() . ' sản phẩm trong kho.',
                'action_url' => url('/admin/products/' . ($variant->product->product_id ?? 0)),
                'data' => [
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->variant_id,
                    'stock' => $variant->inventoryItems->count(),
                    'threshold' => $threshold,
                ],
            ];

            $this->notificationService->notifyAdmins($adminPayload);
            $count++;
        }

        return back()->with('success', 'Đã kiểm tra tồn kho thấp cho ' . $count . ' biến thể.');
    }
}
