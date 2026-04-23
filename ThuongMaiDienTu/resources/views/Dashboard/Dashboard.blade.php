<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Điện Máy PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .stat-card { transition: transform .2s, box-shadow .2s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,.08); }
    </style>
</head>
<body class="bg-slate-100 text-slate-800">

<div class="flex min-h-screen items-stretch">

    {{-- ══════════════════════════════════════════════════════
         SIDEBAR – dùng component riêng biệt
    ══════════════════════════════════════════════════════ --}}
    @include('components.sidebar')

    {{-- ══════════════════════════════════════════════════════
         MAIN CONTENT
    ══════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Top Header --}}
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-4">
                {{-- Nút mở menu mobile --}}
                <button onclick="toggleSidebar()" class="md:hidden text-slate-500 hover:text-indigo-600 transition">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Bảng điều khiển</h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ now('Asia/Ho_Chi_Minh')->locale('vi')->isoFormat('dddd, D [tháng] M, YYYY') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                {{-- Shortcut nhanh sang Sổ Quỹ --}}
                <a href="{{ route('cashbooks.index') }}"
                   class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-xl font-semibold transition shadow">
                    <i class="fa-solid fa-wallet text-sm"></i>
                    <span class="hidden sm:inline">Sổ Quỹ</span>
                </a>
                <div class="w-px h-6 bg-slate-200"></div>
                {{-- Nút thông báo kèm Dropdown --}}
                <div class="relative">
                    <button onclick="toggleNotifications()" class="relative text-slate-500 hover:text-slate-800 transition w-9 h-9 rounded-xl hover:bg-slate-100 flex items-center justify-center">
                        <i class="fa-solid fa-bell text-sm"></i>
                        @if($recentTransactions->isNotEmpty())
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full"></span>
                        @endif
                    </button>
                    
                    {{-- Popup Dropdown --}}
                    <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden text-left origin-top-right transition-all">
                        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                            <h4 class="font-bold text-slate-800 text-sm">Hoạt động gần đây</h4>
                            <span class="text-[10px] bg-rose-100 text-rose-600 font-bold px-2 py-0.5 rounded-full">{{ $recentTransactions->count() }} mới</span>
                        </div>
                        
                        <div class="max-h-72 overflow-y-auto">
                            @if($recentTransactions->isEmpty())
                                <div class="px-4 py-8 text-center">
                                    <i class="fa-solid fa-box-open text-slate-300 text-2xl mb-2"></i>
                                    <p class="text-sm text-slate-500 font-medium">Chưa có thông báo nào</p>
                                </div>
                            @else
                                <div class="divide-y divide-slate-100">
                                    @foreach($recentTransactions as $tx)
                                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition">
                                        <div class="w-8 h-8 flex items-center justify-center rounded-full shrink-0 {{ $tx->type === 'Income' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                                            <i class="fa-solid {{ $tx->type === 'Income' ? 'fa-arrow-down' : 'fa-arrow-up' }} text-xs"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[13px] font-semibold text-slate-700 truncate">{{ $tx->description ?? 'Giao dịch thu/chi' }}</p>
                                            <p class="text-xs font-bold mt-0.5 {{ $tx->type === 'Income' ? 'text-emerald-600' : 'text-rose-500' }}">
                                                {{ $tx->type === 'Income' ? '+' : '-' }}{{ number_format($tx->amount) }}đ
                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-1">{{ $tx->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        
                        <div class="border-t border-slate-100 bg-slate-50">
                            <a href="{{ route('cashbooks.index') }}" class="block w-full px-4 py-2.5 text-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                                Đi tới Sổ Quỹ <i class="fa-solid fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="w-px h-6 bg-slate-200"></div>
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">TH</div>
                    <span class="text-sm font-medium text-slate-700 hidden sm:block">Thanh Hiền</span>
                </div>
            </div>
        </header>

        {{-- Page Body --}}
        <main class="flex-1 p-8 space-y-8">

            {{-- ── Stat Cards ───────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

                {{-- Tổng thu – click lọc Income --}}
                <a href="{{ route('cashbooks.index', ['type' => 'Income']) }}"
                   class="stat-card bg-white rounded-2xl p-5 border border-slate-200 flex items-center justify-between group">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng thu</p>
                        <p class="text-2xl font-black text-slate-800 mt-1.5 tabular-nums group-hover:text-emerald-600 transition">
                            {{ number_format($totalIncome) }}<span class="text-sm font-bold">đ</span>
                        </p>
                        <p class="text-xs text-emerald-600 font-semibold mt-1.5">
                            <i class="fa-solid fa-arrow-up text-[10px]"></i>
                            Tháng này: {{ number_format($incomeThisMonth) }}đ
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 group-hover:bg-emerald-100 flex items-center justify-center flex-shrink-0 transition">
                        <i class="fa-solid fa-arrow-trend-up text-emerald-500 text-lg"></i>
                    </div>
                </a>

                {{-- Tổng chi – click lọc Expense --}}
                <a href="{{ route('cashbooks.index', ['type' => 'Expense']) }}"
                   class="stat-card bg-white rounded-2xl p-5 border border-slate-200 flex items-center justify-between group">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tổng chi</p>
                        <p class="text-2xl font-black text-slate-800 mt-1.5 tabular-nums group-hover:text-rose-600 transition">
                            {{ number_format($totalExpense) }}<span class="text-sm font-bold">đ</span>
                        </p>
                        <p class="text-xs text-rose-500 font-semibold mt-1.5">
                            <i class="fa-solid fa-arrow-down text-[10px]"></i>
                            Tháng này: {{ number_format($expenseThisMonth) }}đ
                        </p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 group-hover:bg-rose-100 flex items-center justify-center flex-shrink-0 transition">
                        <i class="fa-solid fa-arrow-trend-down text-rose-500 text-lg"></i>
                    </div>
                </a>

                {{-- Tồn quỹ – click xem toàn bộ --}}
                <a href="{{ route('cashbooks.index') }}"
                   class="stat-card bg-white rounded-2xl p-5 border border-slate-200 flex items-center justify-between group">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tồn quỹ</p>
                        <p class="text-2xl font-black mt-1.5 tabular-nums {{ $balance >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                            {{ $balance < 0 ? '-' : '' }}{{ number_format(abs($balance)) }}<span class="text-sm font-bold">đ</span>
                        </p>
                        <p class="text-xs text-slate-400 font-medium mt-1.5">Thu − Chi</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 group-hover:bg-blue-100 flex items-center justify-center flex-shrink-0 transition">
                        <i class="fa-solid fa-scale-balanced text-blue-500 text-lg"></i>
                    </div>
                </a>

                {{-- Tổng giao dịch – click xem danh sách --}}
                <a href="{{ route('cashbooks.index') }}"
                   class="stat-card bg-white rounded-2xl p-5 border border-slate-200 flex items-center justify-between group">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Giao dịch</p>
                        <p class="text-2xl font-black text-slate-800 mt-1.5 tabular-nums group-hover:text-violet-600 transition">{{ $totalTransactions }}</p>
                        <p class="text-xs text-slate-400 font-medium mt-1.5">Tổng bản ghi</p>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-violet-50 group-hover:bg-violet-100 flex items-center justify-center flex-shrink-0 transition">
                        <i class="fa-solid fa-receipt text-violet-500 text-lg"></i>
                    </div>
                </a>
            </div>

            {{-- ── Row: Bảng + Tóm tắt ─────────────────────────── --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                {{-- Bảng giao dịch gần nhất --}}
                <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-bold text-slate-800">Giao dịch gần nhất</h3>
                            <p class="text-xs text-slate-400 mt-0.5">5 bản ghi mới nhất trong sổ quỹ</p>
                        </div>
                        <a href="{{ route('cashbooks.index') }}"
                           class="flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg">
                            Xem tất cả <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>

                    @if($recentTransactions->isEmpty())
                        <div class="py-16 text-center">
                            <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fa-solid fa-inbox text-slate-400 text-xl"></i>
                            </div>
                            <p class="text-slate-500 font-medium">Chưa có giao dịch nào</p>
                            <p class="text-slate-400 text-sm mt-1">Dữ liệu sẽ hiển thị ở đây khi có giao dịch</p>
                            <a href="{{ route('cashbooks.index') }}"
                               class="inline-flex items-center gap-2 mt-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-5 py-2.5 rounded-xl font-semibold transition shadow">
                                <i class="fa-solid fa-plus"></i> Thêm giao dịch đầu tiên
                            </a>
                        </div>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-slate-400 text-xs uppercase tracking-wider bg-slate-50">
                                    <th class="text-left px-6 py-3 font-semibold">Thời gian</th>
                                    <th class="text-left px-4 py-3 font-semibold">Loại</th>
                                    <th class="text-left px-4 py-3 font-semibold">Nội dung</th>
                                    <th class="text-right px-6 py-3 font-semibold">Số tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($recentTransactions as $tx)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-3.5 text-slate-400 whitespace-nowrap text-xs">
                                        {{ $tx->created_at->format('d/m/Y') }}<br>
                                        <span class="text-slate-300">{{ $tx->created_at->format('H:i') }}</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($tx->type === 'Income')
                                            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                                <i class="fa-solid fa-plus text-[9px]"></i> Thu
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-600 text-[11px] font-bold px-2 py-0.5 rounded-full">
                                                <i class="fa-solid fa-minus text-[9px]"></i> Chi
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 max-w-[200px] truncate font-medium">
                                        {{ $tx->description ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-right font-bold tabular-nums {{ $tx->type === 'Income' ? 'text-emerald-600' : 'text-rose-500' }}">
                                        {{ $tx->type === 'Income' ? '+' : '-' }}{{ number_format($tx->amount) }}đ
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Panel bên phải --}}
                <div class="space-y-5">

                    {{-- Tóm tắt tháng này --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-bold text-slate-800 mb-4">Tháng {{ now()->month }}/{{ now()->year }}</h3>

                        <div class="space-y-1">
                            <a href="{{ route('cashbooks.index', ['type' => 'Income']) }}"
                               class="flex items-center justify-between text-sm hover:bg-emerald-50 -mx-2 px-2 py-2 rounded-lg transition group">
                                <span class="flex items-center space-x-2 text-slate-500 group-hover:text-emerald-700">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                    <span>Thu</span>
                                </span>
                                <span class="font-bold text-emerald-600">+{{ number_format($incomeThisMonth) }}đ</span>
                            </a>
                            <a href="{{ route('cashbooks.index', ['type' => 'Expense']) }}"
                               class="flex items-center justify-between text-sm hover:bg-rose-50 -mx-2 px-2 py-2 rounded-lg transition group">
                                <span class="flex items-center space-x-2 text-slate-500 group-hover:text-rose-700">
                                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                                    <span>Chi</span>
                                </span>
                                <span class="font-bold text-rose-500">-{{ number_format($expenseThisMonth) }}đ</span>
                            </a>
                            <div class="border-t border-slate-100 pt-3 flex items-center justify-between text-sm">
                                <span class="text-slate-500 font-medium">Ròng tháng này</span>
                                @php $monthBalance = $incomeThisMonth - $expenseThisMonth; @endphp
                                <span class="font-black text-base {{ $monthBalance >= 0 ? 'text-blue-600' : 'text-rose-600' }}">
                                    {{ $monthBalance < 0 ? '-' : '+' }}{{ number_format(abs($monthBalance)) }}đ
                                </span>
                            </div>
                        </div>

                        {{-- Progress bar --}}
                        @if($incomeThisMonth + $expenseThisMonth > 0)
                        @php
                            $total = $incomeThisMonth + $expenseThisMonth;
                            $incomePct = round(($incomeThisMonth / $total) * 100);
                        @endphp
                        <div class="mt-4">
                            <div class="flex justify-between text-[11px] text-slate-400 mb-1.5">
                                <span>Thu {{ $incomePct }}%</span>
                                <span>Chi {{ 100 - $incomePct }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $incomePct }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Trạng thái module --}}
                    <div class="bg-white rounded-2xl border border-slate-200 p-5">
                        <h3 class="font-bold text-slate-800 mb-4">Trạng thái module</h3>
                        <div class="space-y-2.5 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center space-x-2 text-slate-600">
                                    <i class="fa-solid fa-gauge-high text-blue-500 w-4"></i>
                                    <span>Dashboard</span>
                                </span>
                                <span class="text-[11px] bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded-full">Hoạt động</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <a href="{{ route('cashbooks.index') }}"
                                   class="flex items-center space-x-2 text-slate-600 hover:text-indigo-600 transition">
                                    <i class="fa-solid fa-wallet text-indigo-500 w-4"></i>
                                    <span>Sổ quỹ</span>
                                </a>
                                <a href="{{ route('cashbooks.index') }}"
                                   class="text-[11px] bg-emerald-100 text-emerald-700 font-bold px-2 py-0.5 rounded-full hover:bg-emerald-200 transition">
                                    Hoạt động
                                </a>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center space-x-2 text-slate-400">
                                    <i class="fa-solid fa-boxes-stacked text-slate-300 w-4"></i>
                                    <span>Kho hàng</span>
                                </span>
                                <span class="text-[11px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-full">Chờ</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center space-x-2 text-slate-400">
                                    <i class="fa-solid fa-cart-shopping text-slate-300 w-4"></i>
                                    <span>Đơn hàng</span>
                                </span>
                                <span class="text-[11px] bg-slate-100 text-slate-500 font-bold px-2 py-0.5 rounded-full">Chờ</span>
                            </div>
                        </div>
                    </div>

                </div>{{-- /right panel --}}

            </div>{{-- /row --}}

        </main>
    </div>{{-- /main area --}}

</div>{{-- /flex wrapper --}}

{{-- ── JS: Toggle Sidebar & Notifications Mobile ──────────── --}}
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('-translate-x-full');
    }

    function toggleNotifications() {
        const dropdown = document.getElementById('notification-dropdown');
        dropdown.classList.toggle('hidden');
    }

    // Đóng dropdown khi click ra ngoài
    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('notification-dropdown');
        const bellButton = dropdown.previousElementSibling;
        
        if (!dropdown.contains(e.target) && !bellButton.contains(e.target) && !dropdown.classList.contains('hidden')) {
            dropdown.classList.add('hidden');
        }
    });
</script>

</body>
</html>