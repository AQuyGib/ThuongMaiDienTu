<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản Lý IMEI/Serial</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--bg-primary:#0f1117;--bg-secondary:#1a1d27;--bg-card:#1e2231;--bg-hover:#262a3a;--accent:#6c5ce7;--accent-hover:#7f71ed;--accent-glow:rgba(108,92,231,.25);--text-primary:#e8e8ef;--text-secondary:#9ca3b4;--border:#2d3148;--danger:#e74c5e;--success:#2ed573;--warning:#ffa502;}
        *{box-sizing:border-box}body{font-family:'Inter',sans-serif;background:var(--bg-primary);color:var(--text-primary);min-height:100vh;margin:0}
        .page-header{background:linear-gradient(135deg,var(--bg-secondary),var(--bg-card));border-bottom:1px solid var(--border);padding:28px 0}.page-header h1{font-size:1.75rem;font-weight:700;margin:0;display:flex;align-items:center;gap:12px}.page-header h1 i{color:var(--accent)}
        .badge-count{background:var(--accent);color:#fff;font-size:.75rem;font-weight:600;padding:4px 10px;border-radius:20px;margin-left:8px}
        .stat-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:22px 24px;transition:all .3s}.stat-card:hover{transform:translateY(-3px);border-color:var(--accent);box-shadow:0 8px 30px var(--accent-glow)}.stat-card .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem}.stat-card .stat-value{font-size:1.6rem;font-weight:700}.stat-card .stat-label{font-size:.82rem;color:var(--text-secondary)}
        .table-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden}.table-card-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}.table-card-header h5{margin:0;font-weight:600}
        .table-custom{margin:0;width:100%}.table-custom thead th{background:var(--bg-secondary);color:var(--text-secondary);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;padding:14px 20px;border:none;white-space:nowrap}.table-custom tbody td{padding:16px 20px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:.9rem}.table-custom tbody tr{transition:background .2s}.table-custom tbody tr:hover{background:var(--bg-hover)}.table-custom tbody tr:last-child td{border-bottom:none}
        .search-box{position:relative;max-width:280px}.search-box input{background:var(--bg-primary);border:1px solid var(--border);color:var(--text-primary);border-radius:10px;padding:9px 14px 9px 38px;width:100%;font-size:.875rem}.search-box input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow)}.search-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary)}
        .status-badge{padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:600;cursor:pointer;border:none;transition:all .2s}
        .status-In_Stock{background:rgba(46,213,115,.15);color:var(--success)}
        .status-Sold{background:rgba(108,92,231,.15);color:var(--accent)}
        .status-Defective{background:rgba(231,76,94,.15);color:var(--danger)}
        .filter-btn{padding:6px 16px;border-radius:20px;font-size:.8rem;font-weight:600;border:1px solid var(--border);background:var(--bg-secondary);color:var(--text-secondary);cursor:pointer;transition:all .2s;text-decoration:none}.filter-btn:hover,.filter-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}
        .id-badge{background:var(--bg-primary);border:1px solid var(--border);border-radius:8px;padding:4px 10px;font-size:.8rem;font-weight:600;color:var(--text-secondary)}
        .empty-state{text-align:center;padding:60px 20px;color:var(--text-secondary)}.empty-state i{font-size:3rem;margin-bottom:16px;opacity:.4}
        .pagination .page-link{background:var(--bg-secondary);border:1px solid var(--border);color:var(--text-secondary);border-radius:8px!important;margin:0 3px;font-size:.85rem;padding:7px 13px}.pagination .page-link:hover{background:var(--accent);color:#fff}.pagination .page-item.active .page-link{background:var(--accent);border-color:var(--accent);color:#fff}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}.animate-in{animation:fadeInUp .4s ease forwards}
        .alert-custom{border-radius:12px;padding:14px 20px;font-size:.9rem;border:none;display:flex;align-items:center;gap:10px}.alert-success-custom{background:rgba(46,213,115,.1);color:var(--success)}
        .form-select-sm{background:var(--bg-primary);border:1px solid var(--border);color:var(--text-primary);border-radius:8px;padding:4px 8px;font-size:.8rem}
        .form-select-sm:focus{border-color:var(--accent);box-shadow:0 0 0 2px var(--accent-glow)}
        .form-select-sm option{background:var(--bg-primary);color:var(--text-primary)}
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    @include('admin.partials.sidebar')
    <div class="flex-1 overflow-y-auto w-full">
        <div class="page-header"><div class="container"><div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h1><i class="bi bi-upc-scan"></i> Quản Lý IMEI/Serial <span class="badge-count">{{ ($totalInStock + $totalSold + $totalDefective) }} IMEI</span></h1>
        </div></div></div>

        <div class="container py-4">
            @if(session('success'))<div class="alert alert-custom alert-success-custom animate-in" id="flash-alert"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif

            {{-- Stats --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4 animate-in">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:rgba(46,213,115,.15);color:var(--success);"><i class="bi bi-box-seam-fill"></i></div>
                        <div><div class="stat-value">{{ $totalInStock }}</div><div class="stat-label">Còn Trong Kho</div></div>
                    </div>
                </div>
                <div class="col-md-4 animate-in">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:rgba(108,92,231,.15);color:var(--accent);"><i class="bi bi-bag-check-fill"></i></div>
                        <div><div class="stat-value">{{ $totalSold }}</div><div class="stat-label">Đã Bán</div></div>
                    </div>
                </div>
                <div class="col-md-4 animate-in">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:rgba(231,76,94,.15);color:var(--danger);"><i class="bi bi-exclamation-triangle-fill"></i></div>
                        <div><div class="stat-value">{{ $totalDefective }}</div><div class="stat-label">Lỗi / Bảo Hành</div></div>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <div class="d-flex gap-2 mb-4 flex-wrap animate-in">
                <a href="{{ route('admin.inventory.index') }}" class="filter-btn {{ !request('status') ? 'active' : '' }}">Tất cả</a>
                <a href="{{ route('admin.inventory.index', ['status' => 'In_Stock']) }}" class="filter-btn {{ request('status') == 'In_Stock' ? 'active' : '' }}">🟢 Còn hàng</a>
                <a href="{{ route('admin.inventory.index', ['status' => 'Sold']) }}" class="filter-btn {{ request('status') == 'Sold' ? 'active' : '' }}">🟣 Đã bán</a>
                <a href="{{ route('admin.inventory.index', ['status' => 'Defective']) }}" class="filter-btn {{ request('status') == 'Defective' ? 'active' : '' }}">🔴 Lỗi</a>
            </div>

            {{-- Table --}}
            <div class="table-card animate-in">
                <div class="table-card-header">
                    <h5><i class="bi bi-list-ul me-2" style="color:var(--accent);"></i>Danh Sách IMEI</h5>
                    <form method="GET" action="{{ route('admin.inventory.index') }}" class="search-box">
                        @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                        <i class="bi bi-search"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo IMEI...">
                    </form>
                </div>
                <div class="table-responsive"><table class="table table-custom">
                    <thead><tr><th>#</th><th>IMEI/Serial</th><th>Sản Phẩm</th><th>Biến Thể</th><th>NCC (Phiếu Nhập)</th><th>Vị Trí Kho</th><th class="text-center">Trạng Thái</th></tr></thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr>
                            <td><span class="id-badge">{{ $items->firstItem() + $loop->index }}</span></td>
                            <td><strong style="font-family:monospace;letter-spacing:1px;">{{ $item->imei_serial }}</strong></td>
                            <td>{{ $item->variant->product->name ?? '—' }}</td>
                            <td style="color:var(--text-secondary)">{{ $item->variant ? ($item->variant->color ?? '') . ($item->variant->rom_capacity ? ' - '.$item->variant->rom_capacity : '') : '—' }}</td>
                            <td style="color:var(--text-secondary)">
                                @if($item->purchaseOrder && $item->purchaseOrder->supplier)
                                    {{ $item->purchaseOrder->supplier->name }}
                                    <small style="opacity:.5">(PO-{{ str_pad($item->po_id, 5, '0', STR_PAD_LEFT) }})</small>
                                @else —
                                @endif
                            </td>
                            <td style="color:var(--text-secondary)">{{ $item->warehouse_loc ?? '—' }}</td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.inventory.updateStatus', $item->item_id) }}" style="display:inline">
                                    @csrf @method('PUT')
                                    <select name="status" class="form-select-sm" onchange="this.form.submit()">
                                        <option value="In_Stock" {{ $item->status == 'In_Stock' ? 'selected' : '' }}>🟢 Còn hàng</option>
                                        <option value="Sold" {{ $item->status == 'Sold' ? 'selected' : '' }}>🟣 Đã bán</option>
                                        <option value="Defective" {{ $item->status == 'Defective' ? 'selected' : '' }}>🔴 Lỗi</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox d-block"></i><p>Không tìm thấy IMEI nào. Hãy tạo phiếu nhập kho trước!</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table></div>
                @if(method_exists($items,'links'))<div class="d-flex justify-content-center py-3">{{ $items->links('pagination::bootstrap-5') }}</div>@endif
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function toggleSidebar(){const s=document.getElementById('sidebar');if(s)s.classList.toggle('-translate-x-full');}
    const fa=document.getElementById('flash-alert');if(fa)setTimeout(()=>{fa.style.transition='opacity .5s';fa.style.opacity='0';setTimeout(()=>fa.remove(),500);},4000);
    </script>
</body>
</html>
