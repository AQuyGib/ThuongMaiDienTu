<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Nhập Kho</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--bg-primary:#0f1117;--bg-secondary:#1a1d27;--bg-card:#1e2231;--bg-hover:#262a3a;--accent:#6c5ce7;--accent-hover:#7f71ed;--accent-glow:rgba(108,92,231,.25);--text-primary:#e8e8ef;--text-secondary:#9ca3b4;--border:#2d3148;--danger:#e74c5e;--success:#2ed573;--warning:#ffa502;}
        *{box-sizing:border-box}body{font-family:'Inter',sans-serif;background:var(--bg-primary);color:var(--text-primary);min-height:100vh;margin:0}
        .page-header{background:linear-gradient(135deg,var(--bg-secondary),var(--bg-card));border-bottom:1px solid var(--border);padding:28px 0}.page-header h1{font-size:1.75rem;font-weight:700;margin:0;display:flex;align-items:center;gap:12px}.page-header h1 i{color:var(--accent)}
        .badge-count{background:var(--accent);color:#fff;font-size:.75rem;font-weight:600;padding:4px 10px;border-radius:20px;margin-left:8px}
        .table-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden}.table-card-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}.table-card-header h5{margin:0;font-weight:600}
        .table-custom{margin:0;width:100%}.table-custom thead th{background:var(--bg-secondary);color:var(--text-secondary);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;padding:14px 20px;border:none;white-space:nowrap}.table-custom tbody td{padding:16px 20px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:.9rem}.table-custom tbody tr{transition:background .2s}.table-custom tbody tr:hover{background:var(--bg-hover)}.table-custom tbody tr:last-child td{border-bottom:none}
        .btn-accent{background:var(--accent);color:#fff;border:none;border-radius:10px;padding:9px 20px;font-weight:600;font-size:.875rem;transition:all .25s;display:inline-flex;align-items:center;gap:6px;text-decoration:none}.btn-accent:hover{background:var(--accent-hover);color:#fff;transform:translateY(-1px);box-shadow:0 4px 18px var(--accent-glow)}
        .btn-action{width:36px;height:36px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--border);background:var(--bg-secondary);color:var(--text-secondary);transition:all .2s;cursor:pointer;text-decoration:none}.btn-action:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
        .id-badge{background:var(--bg-primary);border:1px solid var(--border);border-radius:8px;padding:4px 10px;font-size:.8rem;font-weight:600;color:var(--text-secondary)}
        .stat-card{background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:22px 24px;transition:all .3s}.stat-card:hover{transform:translateY(-3px);border-color:var(--accent);box-shadow:0 8px 30px var(--accent-glow)}.stat-card .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.25rem}.stat-card .stat-value{font-size:1.6rem;font-weight:700}.stat-card .stat-label{font-size:.82rem;color:var(--text-secondary)}
        .empty-state{text-align:center;padding:60px 20px;color:var(--text-secondary)}.empty-state i{font-size:3rem;margin-bottom:16px;opacity:.4}
        .pagination .page-link{background:var(--bg-secondary);border:1px solid var(--border);color:var(--text-secondary);border-radius:8px!important;margin:0 3px;font-size:.85rem;padding:7px 13px}.pagination .page-link:hover{background:var(--accent);color:#fff}.pagination .page-item.active .page-link{background:var(--accent);border-color:var(--accent);color:#fff}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}.animate-in{animation:fadeInUp .4s ease forwards}
        .alert-custom{border-radius:12px;padding:14px 20px;font-size:.9rem;border:none;display:flex;align-items:center;gap:10px}.alert-success-custom{background:rgba(46,213,115,.1);color:var(--success)}.alert-danger-custom{background:rgba(231,76,94,.1);color:var(--danger)}
        .cost-badge{font-weight:600;color:var(--success)}
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    @include('admin.partials.sidebar')
    <div class="flex-1 overflow-y-auto w-full">
        <div class="page-header"><div class="container"><div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h1><i class="bi bi-file-earmark-arrow-down"></i> Phiếu Nhập Kho <span class="badge-count">{{ $totalPO ?? 0 }} phiếu</span></h1>
            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Tạo Phiếu Nhập</a>
        </div></div></div>

        <div class="container py-4">
            @if(session('success'))<div class="alert alert-custom alert-success-custom animate-in" id="flash-alert"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-custom alert-danger-custom animate-in" id="flash-alert"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif

            <div class="row g-3 mb-4">
                <div class="col-md-6 animate-in">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:rgba(108,92,231,.15);color:var(--accent);"><i class="bi bi-file-earmark-text"></i></div>
                        <div><div class="stat-value">{{ $totalPO ?? 0 }}</div><div class="stat-label">Tổng Phiếu Nhập</div></div>
                    </div>
                </div>
                <div class="col-md-6 animate-in">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:rgba(46,213,115,.15);color:var(--success);"><i class="bi bi-box-seam"></i></div>
                        <div><div class="stat-value">{{ $totalItems ?? 0 }}</div><div class="stat-label">Tổng SP Đã Nhập (IMEI)</div></div>
                    </div>
                </div>
            </div>

            <div class="table-card animate-in">
                <div class="table-card-header"><h5><i class="bi bi-list-ul me-2" style="color:var(--accent);"></i>Danh Sách Phiếu Nhập</h5></div>
                <div class="table-responsive"><table class="table table-custom">
                    <thead><tr><th>#</th><th>Mã Phiếu</th><th>Nhà Cung Cấp</th><th>Số Lượng SP</th><th>Tổng Tiền Nhập</th><th>Ngày Tạo</th><th class="text-center">Thao Tác</th></tr></thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                        <tr>
                            <td><span class="id-badge">{{ $purchaseOrders->firstItem() + $loop->index }}</span></td>
                            <td><strong>PO-{{ str_pad($po->po_id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                            <td><i class="bi bi-building-fill me-1" style="color:var(--accent)"></i>{{ $po->supplier->name ?? '—' }}</td>
                            <td><span style="background:rgba(108,92,231,.15);color:var(--accent);padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:600;">{{ $po->inventory_items_count ?? 0 }}</span></td>
                            <td class="cost-badge">{{ number_format($po->total_cost, 0, ',', '.') }}₫</td>
                            <td style="color:var(--text-secondary)">{{ $po->created_at ? $po->created_at->format('d/m/Y H:i') : '—' }}</td>
                            <td class="text-center"><a href="{{ route('admin.purchase-orders.show', $po->po_id) }}" class="btn-action" title="Xem chi tiết"><i class="bi bi-eye-fill"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox d-block"></i><p>Chưa có phiếu nhập nào. Hãy tạo phiếu nhập đầu tiên!</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table></div>
                @if(method_exists($purchaseOrders,'links'))<div class="d-flex justify-content-center py-3">{{ $purchaseOrders->links('pagination::bootstrap-5') }}</div>@endif
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar(){const s=document.getElementById('sidebar');if(s)s.classList.toggle('-translate-x-full');}
    const fa=document.getElementById('flash-alert');if(fa)setTimeout(()=>{fa.style.transition='opacity .5s';fa.style.opacity='0';setTimeout(()=>fa.remove(),500);},4000);
    </script>
</body>
</html>
