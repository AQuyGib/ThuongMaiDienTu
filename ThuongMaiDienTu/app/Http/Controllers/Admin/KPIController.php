<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\RepairTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KPIController extends Controller
{
    /**
     * Hiển thị bảng điều khiển KPI nhân sự.
     */
    public function index(Request $request)
    {
        // 1. Xử lý bộ lọc ngày
        $filter = $request->input('filter', 'month');
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfDay();

        if ($filter === 'today') {
            $startDate = now()->startOfDay();
        } elseif ($filter === 'yesterday') {
            $startDate = now()->subDay()->startOfDay();
            $endDate = now()->subDay()->endOfDay();
        } elseif ($filter === 'week') {
            $startDate = now()->startOfWeek();
        } elseif ($filter === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
        } elseif ($filter === 'year') {
            $startDate = now()->startOfYear();
        } elseif ($filter === 'custom') {
            $requestStart = $request->input('start');
            $requestEnd = $request->input('end');
            
            if ($requestStart && $requestEnd) {
                $startDate = \Carbon\Carbon::parse($requestStart)->startOfDay();
                $endDate = \Carbon\Carbon::parse($requestEnd)->endOfDay();
                
                // Đảm bảo không vượt quá ngày hiện tại
                if ($endDate > now()) {
                    $endDate = now()->endOfDay();
                }
            }
        }

        // 2. Thống kê Sales KPI
        $salesKPI = User::where('role_id', 4)
            ->withCount(['salesOrders as total_orders' => function($query) use ($startDate, $endDate) {
                $query->where('status', 'Delivered')
                      ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['salesOrders as total_revenue' => function($query) use ($startDate, $endDate) {
                $query->where('status', 'Delivered')
                      ->whereBetween('created_at', [$startDate, $endDate]);
            }], 'final_amount')
            ->get();

        // 3. Thống kê Kỹ thuật KPI
        $techKPI = User::whereHas('repairTickets')
            ->withCount(['repairTickets as completed_tickets' => function($query) use ($startDate, $endDate) {
                $query->where('status', 'Done')
                      ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get();

        // 4. Dữ liệu biểu đồ doanh thu theo ngày
        $rawRevenue = Order::where('status', 'Delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, SUM(final_amount) as total')
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $rawRepairs = RepairTicket::where('status', 'Done')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        // Lấp đầy các ngày trống bằng 0
        $revenueChart = collect();
        $repairsChart = collect();
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $revenueChart->push([
                'date' => $dateStr,
                'total' => (float)$rawRevenue->get($dateStr, 0)
            ]);
            $repairsChart->push([
                'date' => $dateStr,
                'total' => (int)$rawRepairs->get($dateStr, 0)
            ]);
        }

        // 5. Tính toán khoảng thời gian kỳ trước để tính tỷ lệ tăng trưởng
        $prevStartDate = null;
        $prevEndDate = null;
        if ($filter === 'today') {
            $prevStartDate = now()->subDay()->startOfDay();
            $prevEndDate = now()->subDay()->endOfDay();
        } elseif ($filter === 'yesterday') {
            $prevStartDate = now()->subDays(2)->startOfDay();
            $prevEndDate = now()->subDays(2)->endOfDay();
        } elseif ($filter === 'week') {
            $prevStartDate = now()->subWeek()->startOfWeek();
            $prevEndDate = now()->subWeek()->endOfWeek();
        } elseif ($filter === 'last_month') {
            $prevStartDate = now()->subMonths(2)->startOfMonth();
            $prevEndDate = now()->subMonths(2)->endOfMonth();
        } elseif ($filter === 'year') {
            $prevStartDate = now()->subYear()->startOfYear();
            $prevEndDate = now()->subYear()->endOfYear();
        } elseif ($filter === 'custom') {
            $diffDays = $startDate->diffInDays($endDate);
            $prevStartDate = $startDate->copy()->subDays($diffDays + 1)->startOfDay();
            $prevEndDate = $startDate->copy()->subDay()->endOfDay();
        } else { // default is month
            $prevStartDate = now()->subMonth()->startOfMonth();
            $prevEndDate = now()->subMonth()->endOfMonth();
        }

        $currentRevenue = (float)Order::where('status', 'Delivered')->whereBetween('created_at', [$startDate, $endDate])->sum('final_amount');
        $currentOrders = (int)Order::where('status', 'Delivered')->whereBetween('created_at', [$startDate, $endDate])->count();
        $currentRepairs = (int)RepairTicket::where('status', 'Done')->whereBetween('created_at', [$startDate, $endDate])->count();

        $prevRevenue = (float)Order::where('status', 'Delivered')->whereBetween('created_at', [$prevStartDate, $prevEndDate])->sum('final_amount');
        $prevOrders = (int)Order::where('status', 'Delivered')->whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        $prevRepairs = (int)RepairTicket::where('status', 'Done')->whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();

        // Tính tỷ lệ tăng trưởng %
        $growthRevenue = $prevRevenue > 0 ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100 : ($currentRevenue > 0 ? 100.0 : 0.0);
        $growthOrders = $prevOrders > 0 ? (($currentOrders - $prevOrders) / $prevOrders) * 100 : ($currentOrders > 0 ? 100.0 : 0.0);
        $growthRepairs = $prevRepairs > 0 ? (($currentRepairs - $prevRepairs) / $prevRepairs) * 100 : ($currentRepairs > 0 ? 100.0 : 0.0);

        // AOV & Tỷ lệ sửa chữa hoàn thành thành công
        $aov = $currentOrders > 0 ? ($currentRevenue / $currentOrders) : 0.0;
        
        $totalTickets = RepairTicket::whereBetween('created_at', [$startDate, $endDate])->count();
        $repairSuccessRate = $totalTickets > 0 ? ($currentRepairs / $totalTickets) * 100 : 0.0;

        $totalOrdersPlaced = (int)Order::whereBetween('created_at', [$startDate, $endDate])->count();
        $orderCompletionRate = $totalOrdersPlaced > 0 ? ($currentOrders / $totalOrdersPlaced) * 100 : 0.0;

        $props = [
            'stats' => [
                'total_sales_revenue' => $currentRevenue,
                'total_orders_completed' => $currentOrders,
                'total_repairs_done' => $currentRepairs,
                'growth_revenue' => (float)round($growthRevenue, 1),
                'growth_orders' => (float)round($growthOrders, 1),
                'growth_repairs' => (float)round($growthRepairs, 1),
                'average_order_value' => (float)round($aov, 0),
                'repair_success_rate' => (float)round($repairSuccessRate, 1),
                'order_completion_rate' => (float)round($orderCompletionRate, 1),
                'top_sales' => $salesKPI->sortByDesc('total_revenue')->first() ? $salesKPI->sortByDesc('total_revenue')->first()->only(['user_id', 'full_name', 'total_revenue']) : null,
                'top_tech' => $techKPI->sortByDesc('completed_tickets')->first() ? $techKPI->sortByDesc('completed_tickets')->first()->only(['user_id', 'full_name', 'completed_tickets']) : null,
                'filter' => $filter,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'salesKPI' => $salesKPI->sortByDesc('total_revenue')
                ->values()
                ->take(10)
                ->map(fn($u) => [
                    'user_id' => $u->user_id,
                    'full_name' => $u->full_name,
                    'total_orders' => (int)($u->total_orders ?? 0),
                    'total_revenue' => (float)($u->total_revenue ?? 0),
                ])
                ->toArray(),
            'techKPI' => $techKPI->sortByDesc('completed_tickets')
                ->values()
                ->take(10)
                ->map(fn($u) => [
                    'user_id' => $u->user_id,
                    'full_name' => $u->full_name,
                    'completed_tickets' => (int)($u->completed_tickets ?? 0),
                ])
                ->toArray(),
            'revenueChart' => $revenueChart->values()->toArray(),
            'repairsChart' => $repairsChart->values()->toArray()
        ];

        if (request()->wantsJson()) {
            return response()->json($props);
        }

        return view('admin.kpi.index', compact('props'));
    }

    /**
     * Lấy chi tiết KPI của một nhân viên cụ thể theo khoảng thời gian.
     */
    public function employeeDetails(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        // Xử lý bộ lọc tương tự index
        $filter = $request->input('filter', 'month');
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfDay();

        if ($filter === 'today') {
            $startDate = now()->startOfDay();
        } elseif ($filter === 'yesterday') {
            $startDate = now()->subDay()->startOfDay();
            $endDate = now()->subDay()->endOfDay();
        } elseif ($filter === 'week') {
            $startDate = now()->startOfWeek();
        } elseif ($filter === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
        } elseif ($filter === 'year') {
            $startDate = now()->startOfYear();
        } elseif ($filter === 'custom') {
            $requestStart = $request->input('start');
            $requestEnd = $request->input('end');
            if ($requestStart && $requestEnd) {
                $startDate = \Carbon\Carbon::parse($requestStart)->startOfDay();
                $endDate = \Carbon\Carbon::parse($requestEnd)->endOfDay();
                if ($endDate > now()) {
                    $endDate = now()->endOfDay();
                }
            }
        }

        // Lấy chi tiết orders (nếu là nhân viên bán hàng)
        $orders = Order::where('staff_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at')
            ->take(10)
            ->get()
            ->map(fn($o) => [
                'order_id' => $o->order_id,
                'order_code' => $o->order_code,
                'final_amount' => $o->final_amount,
                'status' => $o->status,
                'created_at' => $o->created_at->format('Y-m-d H:i'),
            ]);

        // Lấy chi tiết repair tickets (nếu là kỹ thuật viên)
        $tickets = RepairTicket::where('technician_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest('created_at')
            ->take(10)
            ->get()
            ->map(fn($t) => [
                'ticket_id' => $t->ticket_id,
                'imei_serial' => $t->imei_serial,
                'service_name' => $t->service_name,
                'service_fee' => $t->service_fee,
                'status' => $t->status,
                'customer_name' => $t->customer_name,
                'created_at' => $t->created_at ? $t->created_at->format('Y-m-d H:i') : null,
            ]);

        // Tính các thống kê cá nhân
        $personalStats = [
            'revenue' => 0.0,
            'orders' => 0,
            'aov' => 0.0,
            'repairs' => 0,
            'total_tickets' => 0,
            'repair_success_rate' => 0.0,
        ];

        // Doanh thu cá nhân
        $personalRevenue = Order::where('staff_id', $userId)
            ->where('status', 'Delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('final_amount');
        $personalOrders = Order::where('staff_id', $userId)
            ->where('status', 'Delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $personalAov = $personalOrders > 0 ? ($personalRevenue / $personalOrders) : 0;

        // Số ca sửa cá nhân
        $personalRepairs = RepairTicket::where('technician_id', $userId)
            ->where('status', 'Done')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $personalTotalTickets = RepairTicket::where('technician_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        $personalRepairSuccess = $personalTotalTickets > 0 ? ($personalRepairs / $personalTotalTickets) * 100 : 0;

        $personalStats = [
            'revenue' => (float)$personalRevenue,
            'orders' => (int)$personalOrders,
            'aov' => (float)round($personalAov, 0),
            'repairs' => (int)$personalRepairs,
            'total_tickets' => (int)$personalTotalTickets,
            'repair_success_rate' => (float)round($personalRepairSuccess, 1),
        ];

        return response()->json([
            'employee' => [
                'user_id' => $user->user_id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'status' => $user->status,
                'is_online' => $user->isOnline(),
            ],
            'orders' => $orders,
            'tickets' => $tickets,
            'stats' => $personalStats
        ]);
    }
}
