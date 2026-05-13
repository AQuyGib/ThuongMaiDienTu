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
        $filter = $request->input('filter', 'month'); // mặc định là tháng này
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfDay();

        if ($filter == 'today') {
            $startDate = now()->startOfDay();
        } elseif ($filter == 'yesterday') {
            $startDate = now()->subDay()->startOfDay();
            $endDate = now()->subDay()->endOfDay();
        } elseif ($filter == 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
        } elseif ($filter == 'custom' && $request->has('start_date') && $request->has('end_date')) {
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
        }

        // 2. Thống kê Sales (Role 4)
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

        // 3. Thống kê Kỹ thuật viên (Lấy tất cả user có repair tickets)
        $techKPI = User::whereHas('repairTickets')
            ->withCount(['repairTickets as completed_tickets' => function($query) use ($startDate, $endDate) {
                $query->where('status', 'Done')
                      ->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->get();

        // 4. Dữ liệu cho biểu đồ Doanh thu (Group by Date)
        $revenueChart = Order::where('status', 'Delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(final_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

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

        return view('admin.kpi.index', compact('salesKPI', 'techKPI', 'stats', 'revenueChart'));
    }
}
