@extends('admin.layouts.master')

@section('title', 'Thống kê KPI Nhân sự')
@section('page-title', 'Đo lường Hiệu suất Nhân sự')

@section('content')
<div class="space-y-6">
    {{-- BỘ LỌC --}}
    <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap items-center justify-between gap-4">
        <form action="{{ route('admin.kpi.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <select name="filter" onchange="this.form.submit()" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-blue-500 outline-none transition">
                <option value="today" {{ $stats['filter'] == 'today' ? 'selected' : '' }}>Hôm nay</option>
                <option value="yesterday" {{ $stats['filter'] == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                <option value="month" {{ $stats['filter'] == 'month' ? 'selected' : '' }}>Tháng này</option>
                <option value="last_month" {{ $stats['filter'] == 'last_month' ? 'selected' : '' }}>Tháng trước</option>
                <option value="custom" {{ $stats['filter'] == 'custom' ? 'selected' : '' }}>Tùy chọn</option>
            </select>

            @if($stats['filter'] == 'custom')
                <input type="date" name="start_date" value="{{ $stats['start_date'] }}" class="px-4 py-2 border border-gray-200 rounded-xl text-sm outline-none">
                <input type="date" name="end_date" value="{{ $stats['end_date'] }}" class="px-4 py-2 border border-gray-200 rounded-xl text-sm outline-none">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-blue-700 transition">Lọc</button>
            @endif
        </form>

        <div class="text-xs text-gray-400 font-medium">
            Dữ liệu từ: <span class="text-gray-600">{{ \Carbon\Carbon::parse($stats['start_date'])->format('d/m/Y') }}</span> 
            đến <span class="text-gray-600">{{ \Carbon\Carbon::parse($stats['end_date'])->format('d/m/Y') }}</span>
        </div>
    </div>

    {{-- TOP STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Giữ nguyên các card stats cũ --}}
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <div class="text-blue-100 text-sm font-medium mb-1">Tổng doanh thu Sales</div>
                <div class="text-3xl font-extrabold">{{ number_format($stats['total_sales_revenue']) }}đ</div>
                <div class="mt-4 flex items-center text-xs text-blue-100 bg-white/10 w-fit px-2 py-1 rounded-full">
                    <i class="fa-solid fa-chart-line mr-1"></i> Thời gian thực
                </div>
            </div>
            <i class="fa-solid fa-sack-dollar absolute -bottom-4 -right-4 text-8xl text-white/10"></i>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <div class="text-emerald-100 text-sm font-medium mb-1">Đơn hàng thành công</div>
                <div class="text-3xl font-extrabold">{{ number_format($stats['total_orders_completed']) }}</div>
                <div class="mt-4 flex items-center text-xs text-emerald-100 bg-white/10 w-fit px-2 py-1 rounded-full">
                    <i class="fa-solid fa-check-double mr-1"></i> Hoàn thành
                </div>
            </div>
            <i class="fa-solid fa-cart-check absolute -bottom-4 -right-4 text-8xl text-white/10"></i>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <div class="text-purple-100 text-sm font-medium mb-1">Ca sửa chữa đã xong</div>
                <div class="text-3xl font-extrabold">{{ number_format($stats['total_repairs_done']) }}</div>
                <div class="mt-4 flex items-center text-xs text-purple-100 bg-white/10 w-fit px-2 py-1 rounded-full">
                    <i class="fa-solid fa-wrench mr-1"></i> Kỹ thuật
                </div>
            </div>
            <i class="fa-solid fa-screwdriver-wrench absolute -bottom-4 -right-4 text-8xl text-white/10"></i>
        </div>

        <div class="bg-gradient-to-br from-amber-400 to-amber-500 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
            <div class="relative z-10">
                <div class="text-amber-100 text-sm font-medium mb-1">Nhân viên xuất sắc</div>
                <div class="text-lg font-bold truncate">{{ $stats['top_sales']->full_name ?? 'Đang cập nhật' }}</div>
                <div class="mt-4 flex items-center text-xs text-amber-100 bg-white/10 w-fit px-2 py-1 rounded-full">
                    <i class="fa-solid fa-trophy mr-1"></i> Ngôi sao Sales
                </div>
            </div>
            <i class="fa-solid fa-star absolute -bottom-4 -right-4 text-8xl text-white/10"></i>
        </div>
    </div>

    {{-- BIỂU ĐỒ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-gray-800">Xu hướng doanh thu</h3>
                <i class="fa-solid fa-ellipsis-vertical text-gray-300"></i>
            </div>
            <div class="h-80">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-gray-800">Phân bổ doanh thu Sales</h3>
            </div>
            <div class="h-80">
                <canvas id="salesDistChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- BẢNG KPI SALES (Giữ nguyên) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-users-viewfinder text-blue-500"></i> Hiệu suất Bán hàng
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs uppercase text-gray-400 font-bold bg-gray-50">
                            <th class="px-6 py-4">Nhân viên</th>
                            <th class="px-6 py-4 text-center">Đơn chốt</th>
                            <th class="px-6 py-4 text-right">Doanh thu</th>
                            <th class="px-6 py-4 text-right">Đóng góp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($salesKPI->sortByDesc('total_revenue') as $staff)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($staff->full_name, 0, 1) }}
                                    </div>
                                    <div class="font-bold text-gray-800 text-sm">{{ $staff->full_name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-50 text-blue-700 px-2 py-1 rounded-lg text-xs font-bold">{{ $staff->total_orders }}</span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-700">
                                {{ number_format($staff->total_revenue ?? 0) }}đ
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php 
                                    $percent = $stats['total_sales_revenue'] > 0 ? ($staff->total_revenue / $stats['total_sales_revenue']) * 100 : 0;
                                @endphp
                                <span class="text-xs font-bold text-blue-600">{{ round($percent, 1) }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BẢNG KPI KỸ THUẬT (Giữ nguyên) --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-gear text-purple-500"></i> Hiệu suất Kỹ thuật
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-xs uppercase text-gray-400 font-bold bg-gray-50">
                            <th class="px-6 py-4">Kỹ thuật viên</th>
                            <th class="px-6 py-4 text-center">Ca hoàn thành</th>
                            <th class="px-6 py-4 text-right">Hệ thống</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($techKPI->sortByDesc('completed_tickets') as $tech)
                        <tr class="hover:bg-purple-50/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($tech->full_name, 0, 1) }}
                                    </div>
                                    <div class="font-bold text-gray-800 text-sm">{{ $tech->full_name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-purple-50 text-purple-700 px-2 py-1 rounded-lg text-xs font-bold">{{ $tech->completed_tickets }} ca</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php 
                                    $percent = $stats['total_repairs_done'] > 0 ? ($tech->completed_tickets / $stats['total_repairs_done']) * 100 : 0;
                                @endphp
                                <span class="text-xs font-bold text-purple-600">{{ round($percent, 1) }}%</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1. Biểu đồ Xu hướng doanh thu
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueChart->pluck('date')) !!},
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: {!! json_encode($revenueChart->pluck('total')) !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb',
                pointBorderColor: '#fff',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + 'đ';
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Biểu đồ Phân bổ Sales
    const salesDistCtx = document.getElementById('salesDistChart').getContext('2d');
    new Chart(salesDistCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($salesKPI->pluck('full_name')) !!},
            datasets: [{
                data: {!! json_encode($salesKPI->pluck('total_revenue')) !!},
                backgroundColor: [
                    '#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 11 }
                    }
                }
            },
            cutout: '70%'
        }
    });
</script>
@endpush
