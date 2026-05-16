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

        // Lấp đầy các ngày trống bằng 0
        $revenueChart = collect();
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $revenueChart->push([
                'date' => $dateStr,
                'total' => $rawRevenue->get($dateStr, 0)
            ]);
        }

        // 5. Thống kê tổng hợp cho Dashboard
        $stats = [
            'total_sales_revenue' => Order::where('status', 'Delivered')->whereBetween('created_at', [$startDate, $endDate])->sum('final_amount'),
            'total_orders_completed' => Order::where('status', 'Delivered')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'total_repairs_done' => RepairTicket::where('status', 'Done')->whereBetween('created_at', [$startDate, $endDate])->count(),
            'top_sales' => $salesKPI->sortByDesc('total_revenue')->first(),
            'top_tech' => $techKPI->sortByDesc('completed_tickets')->first(),
            'filter' => $filter,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];

        $props = [
            'stats' => [
                'total_sales_revenue' => (float)($stats['total_sales_revenue'] ?? 0),
                'total_orders_completed' => (int)($stats['total_orders_completed'] ?? 0),
                'total_repairs_done' => (int)($stats['total_repairs_done'] ?? 0),
                'top_sales' => $stats['top_sales'] ? $stats['top_sales']->only(['user_id', 'full_name', 'total_revenue']) : null,
                'top_tech' => $stats['top_tech'] ? $stats['top_tech']->only(['user_id', 'full_name', 'completed_tickets']) : null,
                'filter' => $stats['filter'],
                'start_date' => $stats['start_date'],
                'end_date' => $stats['end_date'],
            ],
            'salesKPI' => $salesKPI->map(fn($u) => $u->only(['user_id', 'full_name', 'total_orders', 'total_revenue']))->values()->toArray(),
            'techKPI' => $techKPI->map(fn($u) => $u->only(['user_id', 'full_name', 'completed_tickets']))->values()->toArray(),
            'revenueChart' => $revenueChart->values()->toArray()
        ];

        if (request()->wantsJson()) {
            return response()->json($props);
        }

        return view('admin.kpi.index', compact('props'));
    }
}
