@extends('admin.layouts.master')
@section('title', 'Bảng điều khiển')
@section('page-title', 'Bảng điều khiển')

@section('content')
<!-- Container chính sử dụng khoảng cách flex-column giãn cách 6 đơn vị (space-y-6) -->
<div class="space-y-6">

    {{-- ═══ 1. TIÊU ĐỀ & THANH ĐIỀU HƯỚNG NHANH ═══ --}}
    <!-- Bố cục dạng dòng trên màn hình lớn, cột trên màn hình nhỏ. Chứa lời chào và các link tắt đến các tính năng cốt lõi. -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tổng quan hệ thống</h1>
            <p class="text-slate-500 text-sm mt-0.5">Xin chào <span class="font-bold text-slate-700">{{ Auth::user()->full_name ?? 'Admin' }}</span>, hôm nay là {{ now()->format('d/m/Y') }}</p>
        </div>
        {{-- Nút Thao tác nhanh (Quick Links) giúp chuyển hướng tức thì sang các phân hệ chính --}}
        <div class="flex flex-wrap gap-2">
            @php $qLinks = [
                ['r'=>'admin.products.index','i'=>'fa-plus-circle','l'=>'Sản phẩm'],
                ['r'=>'admin.purchase-orders.index','i'=>'fa-file-invoice','l'=>'Nhập kho'],
                ['r'=>'admin.repair-tickets.index','i'=>'fa-wrench','l'=>'Sửa chữa'],
                ['r'=>'admin.cashbooks.index','i'=>'fa-wallet','l'=>'Sổ quỹ'],
            ]; @endphp
            @foreach($qLinks as $ql)
            <a href="{{ route($ql['r']) }}" class="px-3 py-1.5 bg-white rounded-lg border border-slate-200 text-xs font-bold text-slate-600 hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all flex items-center gap-1.5">
                <i class="fa-solid {{ $ql['i'] }} text-[10px]"></i>{{ $ql['l'] }}
            </a>
            @endforeach
        </div>
    </div>

    {{-- ═══ 2. CẢNH BÁO KIỂM DUYỆT CMS (BÌNH LUẬN & ĐÁNH GIÁ) ═══ --}}
    <!-- Banner màu vàng nổi bật, chỉ xuất hiện khi có đánh giá hoặc bình luận video chưa được kiểm duyệt. -->
    @if($stats['reviews_pending'] > 0 || $stats['comments_pending'] > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3">
        <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
        <span class="text-xs text-amber-800 flex-1">
            <b>Chờ kiểm duyệt:</b>
            @if($stats['reviews_pending'] > 0) {{ $stats['reviews_pending'] }} đánh giá @endif
            @if($stats['reviews_pending'] > 0 && $stats['comments_pending'] > 0), @endif
            @if($stats['comments_pending'] > 0) {{ $stats['comments_pending'] }} bình luận video @endif
        </span>
        <a href="{{ route('admin.comments.index') }}" class="px-3 py-1.5 bg-amber-600 text-white text-[10px] font-bold rounded-lg hover:bg-amber-700 transition-colors">Duyệt ngay</a>
    </div>
    @endif

    {{-- ═══ 3. HÀNG KPI: 6 THẺ CHỈ SỐ CƠ BẢN ═══ --}}
    <!-- Grid 6 cột tự động co giãn. Hiển thị doanh thu, chi phí, đơn hàng, sản phẩm, khách hàng, và đánh giá. -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @php $kpis = [
            ['l'=>'Thu nhập','v'=>number_format($stats['total_income']).'đ','i'=>'fa-arrow-trend-up','c'=>'emerald'],
            ['l'=>'Chi phí','v'=>number_format($stats['total_expense']).'đ','i'=>'fa-arrow-trend-down','c'=>'red'],
            ['l'=>'Đơn hàng','v'=>$stats['total_orders'],'i'=>'fa-shopping-bag','c'=>'amber'],
            ['l'=>'Sản phẩm','v'=>$stats['total_products'],'i'=>'fa-boxes-stacked','c'=>'blue'],
            ['l'=>'Khách hàng','v'=>$stats['total_users'],'i'=>'fa-user-group','c'=>'violet'],
            ['l'=>'Đánh giá','v'=>$stats['reviews_total'],'i'=>'fa-star','c'=>'pink'],
        ]; @endphp
        @foreach($kpis as $kpi)
        <div class="bg-white rounded-xl border border-slate-100 p-4 hover:shadow-md transition-all">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-7 h-7 rounded-lg bg-{{ $kpi['c'] }}-100 text-{{ $kpi['c'] }}-600 flex items-center justify-center text-xs"><i class="fa-solid {{ $kpi['i'] }}"></i></div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ $kpi['l'] }}</span>
            </div>
            <div class="text-lg font-black text-slate-900 leading-tight">{{ $kpi['v'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ═══ 4. BIỂU ĐỒ DOANH THU & TRẠNG THÁI ĐƠN HÀNG ═══ --}}
    <!-- Chia tỉ lệ Grid 3/5 cho biểu đồ cột (doanh thu) và 2/5 cho biểu đồ tròn (trạng thái đơn hàng). -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
        {{-- Biểu đồ doanh thu 6 tháng gần nhất --}}
        <div class="lg:col-span-3 bg-white rounded-xl border border-slate-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold text-slate-900 text-sm">Xu hướng doanh thu (6 tháng)</h2>
            </div>
            <div style="height:240px"><canvas id="revenueChart"></canvas></div>
        </div>
        {{-- Biểu đồ phân bổ trạng thái đơn hàng (Donut) kèm chú thích màu sắc đồng bộ --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-100 p-5">
            <h2 class="font-bold text-slate-900 text-sm mb-4">Đơn hàng theo trạng thái</h2>
            <div style="height:160px" class="flex items-center justify-center"><canvas id="orderChart"></canvas></div>
            @php $oColors = ['Pending'=>'#f59e0b','Processing'=>'#3b82f6','Shipped'=>'#6366f1','Shipping'=>'#818cf8','Delivered'=>'#10b981','Completed'=>'#059669','Cancelled'=>'#ef4444','BaoCK'=>'#d946ef']; @endphp
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 mt-4">
                @foreach($stats['order_by_status'] as $st => $ct)
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background:{{ $oColors[$st] ?? '#94a3b8' }}"></span><span class="text-slate-500">{{ $st }}</span></span>
                    <span class="font-bold text-slate-800">{{ $ct }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══ 5. TOP SẢN PHẨM BÁN CHẠY & CẢNH BÁO TỒN KHO ═══ --}}
    <!-- Bố cục 2 cột song song hiển thị các sản phẩm bán nhiều nhất và các sản phẩm sắp hết hàng. -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Top 5 sản phẩm bán chạy --}}
        <div class="bg-white rounded-xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-500 text-sm"></i>
                <h2 class="font-bold text-slate-900 text-sm">Top sản phẩm bán chạy</h2>
            </div>
            @forelse($stats['top_products'] as $i => $tp)
            <div class="px-5 py-3 flex items-center gap-3 border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                <span class="w-6 h-6 rounded-md flex items-center justify-center text-[10px] font-black {{ $i===0?'bg-amber-100 text-amber-700':($i===1?'bg-slate-200 text-slate-600':($i===2?'bg-orange-100 text-orange-700':'bg-slate-100 text-slate-400')) }}">#{{ $i+1 }}</span>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-800 text-xs truncate">{{ $tp->product_name }}</div>
                </div>
                <div class="text-right">
                    <span class="font-black text-indigo-600 text-xs">{{ number_format($tp->total_qty) }}</span>
                    <span class="text-[10px] text-slate-400 ml-0.5">sp</span>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-slate-400 text-xs">Chưa có dữ liệu bán hàng.</div>
            @endforelse
        </div>

        {{-- Cảnh báo tồn kho thấp (Tồn kho <= 3) & Liên kết đến đơn nhập kho --}}
        <div class="bg-white rounded-xl border border-slate-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-red-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">Cảnh báo tồn kho thấp</h2>
                </div>
                <div class="flex items-center gap-2 text-xs">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="font-bold text-slate-500 hover:text-indigo-600">Đơn nhập kho ({{ $stats['purchase_orders_total'] }})</a>
                    <span class="text-slate-200">|</span>
                    <a href="{{ route('admin.inventory.index') }}" class="font-bold text-indigo-600 hover:underline">Xem kho →</a>
                </div>
            </div>
            @forelse($stats['low_stock'] as $v)
            <div class="px-5 py-3 flex items-center gap-3 border-b border-slate-50 last:border-0 hover:bg-slate-50/50 transition-colors">
                <i class="fa-solid {{ $v->stock_count == 0 ? 'fa-xmark text-red-500' : 'fa-exclamation text-amber-500' }} text-xs w-4 text-center"></i>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-slate-800 text-xs truncate">{{ $v->product->name ?? 'Sản phẩm' }}</div>
                    <div class="text-[10px] text-slate-400">{{ $v->label }}</div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $v->stock_count == 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                    {{ $v->stock_count == 0 ? 'Hết hàng' : 'Còn '.$v->stock_count }}
                </span>
            </div>
            @empty
            <div class="px-5 py-8 text-center"><i class="fa-solid fa-circle-check text-green-400 text-lg"></i><p class="text-xs text-green-600 mt-1">Kho hàng ổn định.</p></div>
            @endforelse
        </div>
    </div>

    {{-- ═══ 6. PHÂN HỆ SỬA CHỮA, HÓA ĐƠN DỊCH VỤ & THẺ TRUYỀN THÔNG CMS ═══ --}}
    <!-- Bố cục 3 cột tương thích thiết bị di động, hiển thị chi tiết tiến trình kỹ thuật & truyền thông. -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Phiếu sửa chữa: Hiển thị thanh phân bổ tiến độ theo các trạng thái kỹ thuật --}}
        <div class="bg-white rounded-xl border border-slate-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-slate-900 text-sm flex items-center gap-2"><i class="fa-solid fa-wrench text-orange-500 text-xs"></i>Phiếu sửa chữa</h2>
                <a href="{{ route('admin.repair-tickets.index') }}" class="text-[10px] font-bold text-indigo-600 hover:underline">Xem →</a>
            </div>
            <div class="text-2xl font-black text-slate-900 mb-3">{{ $stats['repair_total'] }}</div>
            @if($stats['repair_total'] > 0)
            @php $barC = ['Received'=>'bg-blue-500','Checking'=>'bg-indigo-500','Under_Repair'=>'bg-amber-500','Waiting_Parts'=>'bg-orange-500','Done'=>'bg-green-500']; @endphp
            <div class="flex rounded-lg overflow-hidden h-5 mb-3">
                @foreach($stats['repair_by_status'] as $st => $ct)
                @if($ct > 0)
                <div class="{{ $barC[$st]??'bg-slate-400' }} flex items-center justify-center text-white text-[9px] font-bold" style="width:{{ round($ct/$stats['repair_total']*100,1) }}%" title="{{ str_replace('_',' ',$st) }}">{{ $ct }}</div>
                @endif
                @endforeach
            </div>
            <div class="space-y-1">
                @foreach($stats['repair_by_status'] as $st => $ct)
                <div class="flex items-center gap-1.5 text-[10px] text-slate-500"><span class="w-2 h-2 rounded-full {{ $barC[$st]??'bg-slate-400' }}"></span>{{ str_replace('_',' ',$st) }}: <b class="text-slate-700">{{ $ct }}</b></div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Hóa đơn dịch vụ: Tổng tiền thực thu (đã paid) và số hóa đơn còn tồn đọng --}}
        <div class="bg-white rounded-xl border border-slate-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-slate-900 text-sm flex items-center gap-2"><i class="fa-solid fa-file-invoice-dollar text-emerald-500 text-xs"></i>Hóa đơn dịch vụ</h2>
                <a href="{{ route('admin.service-invoices.index') }}" class="text-[10px] font-bold text-indigo-600 hover:underline">Xem →</a>
            </div>
            <div class="text-2xl font-black text-slate-900 mb-3">{{ $stats['service_total'] }}</div>
            <div class="space-y-2">
                <div class="flex justify-between items-center text-xs"><span class="text-slate-500">Doanh thu (paid)</span><span class="font-bold text-emerald-600">{{ number_format($stats['service_revenue']) }}đ</span></div>
                <div class="flex justify-between items-center text-xs"><span class="text-slate-500">Chờ xử lý</span><span class="font-bold text-amber-600">{{ $stats['service_pending'] }}</span></div>
            </div>
        </div>

        {{-- Video & Nội dung CMS: Đánh giá hiệu quả tương tác (Views, Likes) & Bài viết --}}
        <div class="bg-white rounded-xl border border-slate-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-play-circle text-pink-500 text-xs"></i>Video & Nội dung
                </h2>
                <a href="{{ route('admin.videos.index') }}" class="text-[10px] font-bold text-indigo-600 hover:underline">Xem video →</a>
            </div>
            
            <div class="grid grid-cols-3 gap-3 text-center mb-4">
                <div>
                    <div class="text-lg font-black text-slate-900">{{ $stats['video_total'] }}</div>
                    <div class="text-[10px] text-slate-400">Video</div>
                </div>
                <div>
                    <div class="text-lg font-black text-slate-900">{{ number_format($stats['video_views']) }}</div>
                    <div class="text-[10px] text-slate-400">Lượt xem</div>
                </div>
                <div>
                    <div class="text-lg font-black text-slate-900">{{ number_format($stats['video_likes']) }}</div>
                    <div class="text-[10px] text-slate-400">Lượt thích</div>
                </div>
            </div>
            
            <div class="border-t border-slate-100 pt-3 flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 flex items-center gap-1.5">
                    <i class="fa-solid fa-newspaper text-cyan-500 text-xs"></i>
                    Bài viết CMS: <b class="text-slate-800">{{ $stats['articles_total'] }}</b>
                </span>
                <a href="{{ route('admin.articles.index') }}" class="text-[10px] font-bold text-indigo-600 hover:underline">Xem bài viết →</a>
            </div>
        </div>
    </div>

    {{-- ═══ 7. BANNER FLASH SALE (NẾU ĐANG DIỄN RA) ═══ --}}
    <!-- Dòng quảng bá chương trình Flash Sale đang hoạt động, có màu gradient bắt mắt -->
    @if($stats['active_flash_sales']->count() > 0)
    <div class="bg-gradient-to-r from-rose-500 to-orange-400 rounded-xl p-4 text-white flex items-center gap-4 flex-wrap">
        <div class="flex items-center gap-2"><i class="fa-solid fa-bolt text-lg"></i><b class="text-sm">Flash Sale:</b></div>
        @foreach($stats['active_flash_sales'] as $fs)
        <div class="bg-white/20 backdrop-blur rounded-lg px-3 py-2 text-xs">
            <b>{{ $fs->name ?? 'Sale #'.$fs->flash_sale_id }}</b> · {{ $fs->products_count }} SP · đến {{ $fs->end_at->format('d/m H:i') }}
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══ 8. DANH SÁCH ĐƠN HÀNG MỚI NHẤT (SẮP XẾP TĂNG DẦN THEO ID) ═══ --}}
    <!-- Bảng thông tin 5 đơn hàng cũ đến mới nhất với màu nền tương ứng trạng thái đơn hàng. -->
    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 text-sm">Đơn hàng mới nhất</h2>
            <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Xem tất cả →</a>
        </div>
        <table class="w-full text-left">
            <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                <tr><th class="px-6 py-3">Mã</th><th class="px-6 py-3">Khách hàng</th><th class="px-6 py-3 text-right">Tổng tiền</th><th class="px-6 py-3 text-center">Trạng thái</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($stats['recent_orders'] as $order)
                @php $stCls = ['completed'=>'bg-green-100 text-green-700','delivered'=>'bg-green-100 text-green-700','pending'=>'bg-yellow-100 text-yellow-700','processing'=>'bg-blue-100 text-blue-700','shipped'=>'bg-indigo-100 text-indigo-700','shipping'=>'bg-indigo-100 text-indigo-700','cancelled'=>'bg-red-100 text-red-700','baock'=>'bg-fuchsia-100 text-fuchsia-700']; @endphp
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-3.5 font-bold text-slate-900 text-xs">#{{ $order->order_id }}</td>
                    <td class="px-6 py-3.5">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center text-[10px] font-bold">{{ mb_substr($order->user->full_name ?? 'K', 0, 1) }}</div>
                            <span class="text-xs font-semibold text-slate-700">{{ $order->user->full_name ?? 'Khách lẻ' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-3.5 text-right font-bold text-slate-900 text-xs">{{ number_format($order->total_amount) }}đ</td>
                    <td class="px-6 py-3.5 text-center">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $stCls[strtolower($order->status)] ?? 'bg-slate-100 text-slate-600' }}">{{ $order->status }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-6 py-10 text-center text-slate-400 text-xs">Chưa có đơn hàng nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══ 9. KHỞI TẠO BIỂU ĐỒ CHART.JS ═══ --}}
<!-- Truyền biến từ controller sang Javascript thông qua định dạng JSON. -->
<script>
window.__chartData = { revenue: @json($stats['monthly_revenue']), orders: @json($stats['order_by_status']) };
</script>
<!-- Tải thư viện Chart.js qua CDN và bắt đầu vẽ biểu đồ khi thư viện load xong. -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js" onload="window.__initCharts && window.__initCharts()"></script>
<script>
window.__initCharts = function() {
    if (typeof Chart === 'undefined') return;
    var d = window.__chartData;

    // A. Biểu đồ cột (Bar Chart) - Xu hướng doanh thu & Chi phí 6 tháng
    var rc = document.getElementById('revenueChart');
    if (rc) new Chart(rc, {
        type:'bar',
        data:{labels:d.revenue.map(function(r){return r.label}),datasets:[
            {label:'Thu nhập',data:d.revenue.map(function(r){return r.income}),backgroundColor:'rgba(16,185,129,0.7)',borderRadius:6,barPercentage:0.4},
            {label:'Chi phí',data:d.revenue.map(function(r){return r.expense}),backgroundColor:'rgba(239,68,68,0.5)',borderRadius:6,barPercentage:0.4}
        ]},
        options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{font:{weight:'bold',size:11},usePointStyle:true,pointStyle:'rectRounded'}}},scales:{y:{beginAtZero:true,ticks:{callback:function(v){return(v/1e6).toFixed(0)+'tr'},font:{size:10}},grid:{color:'#f1f5f9'}},x:{ticks:{font:{size:10}},grid:{display:false}}}}
    });

    // B. Biểu đồ tròn khuyết (Doughnut Chart) - Phân bổ trạng thái Đơn hàng
    var oc = document.getElementById('orderChart');
    if (oc) {
        var labels=Object.keys(d.orders),vals=Object.values(d.orders);
        var cm={Pending:'#f59e0b',Processing:'#3b82f6',Shipped:'#6366f1',Shipping:'#818cf8',Delivered:'#10b981',Completed:'#059669',Cancelled:'#ef4444',BaoCK:'#d946ef'};
        new Chart(oc,{type:'doughnut',data:{labels:labels,datasets:[{data:vals,backgroundColor:labels.map(function(l){return cm[l]||'#94a3b8'}),borderWidth:0,hoverOffset:6}]},options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:false}}}});
    }
};
// Dự phòng trường hợp thư viện Chart.js đã được tải trước đó
if (typeof Chart !== 'undefined') window.__initCharts();
</script>
@endsection
