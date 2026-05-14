<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi Tiết Phiếu Nhập #{{ $po->po_id }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--bg-primary:#0f1117;--bg-secondary:#1a1d27;--bg-card:#1e2231;--bg-hover:#262a3a;--accent:#6c5ce7;--text-primary:#e8e8ef;--text-secondary:#9ca3b4;--border:#2d3148;--success:#2ed573;--warning:#ffa502;--danger:#e74c5e;}
        *{box-sizing:border-box}body{font-family:'Inter',sans-serif;background:var(--bg-primary);color:var(--text-primary);min-height:100vh;margin:0}
        .page-header{background:linear-gradient(135deg,var(--bg-secondary),var(--bg-card));border-bottom:1px solid var(--border);padding:28px 0}.page-header h1{font-size:1.75rem;font-weight:700;margin:0;display:flex;align-items:center;gap:12px}.page-header h1 i{color:var(--accent)}
        .info-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:20px}
        .info-card h5{font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px}.info-card h5 i{color:var(--accent)}
        .info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border);font-size:.9rem}.info-row:last-child{border-bottom:none}.info-row .label{color:var(--text-secondary)}.info-row .value{font-weight:600}
        .table-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden}.table-card-header{padding:20px 24px;border-bottom:1px solid var(--border)}.table-card-header h5{margin:0;font-weight:600}
        .table-custom{margin:0;width:100%}.table-custom thead th{background:var(--bg-secondary);color:var(--text-secondary);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;padding:14px 20px;border:none;white-space:nowrap}.table-custom tbody td{padding:16px 20px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:.9rem}.table-custom tbody tr{transition:background .2s}.table-custom tbody tr:hover{background:var(--bg-hover)}.table-custom tbody tr:last-child td{border-bottom:none}
        .btn-cancel{background:var(--bg-secondary);color:var(--text-secondary);border:1px solid var(--border);border-radius:10px;padding:9px 20px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:6px}.btn-cancel:hover{background:var(--bg-hover);color:var(--text-primary)}
        .status-badge{padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:600}
        .status-In_Stock{background:rgba(46,213,115,.15);color:var(--success)}
        .status-Sold{background:rgba(108,92,231,.15);color:var(--accent)}
        .status-Defective{background:rgba(231,76,94,.15);color:var(--danger)}
        .id-badge{background:var(--bg-primary);border:1px solid var(--border);border-radius:8px;padding:4px 10px;font-size:.8rem;font-weight:600;color:var(--text-secondary)}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}.animate-in{animation:fadeInUp .4s ease forwards}
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    @include('admin.partials.sidebar')
    <div class="flex-1 overflow-y-auto w-full">
        <div class="page-header"><div class="container"><div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h1><i class="bi bi-file-earmark-text"></i> Chi Tiết Phiếu Nhập PO-{{ str_pad($po->po_id, 5, '0', STR_PAD_LEFT) }}</h1>
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-cancel"><i class="bi bi-arrow-left"></i> Quay lại</a>
        </div></div></div>

        <div class="container py-4">
            <div class="row g-4">
                <div class="col-md-4 animate-in">
                    <div class="info-card">
                        <h5><i class="bi bi-info-circle"></i> Thông Tin Phiếu</h5>
                        <div class="info-row"><span class="label">Mã phiếu</span><span class="value">PO-{{ str_pad($po->po_id, 5, '0', STR_PAD_LEFT) }}</span></div>
                        <div class="info-row"><span class="label">Nhà cung cấp</span><span class="value">{{ $po->supplier->name ?? '—' }}</span></div>
                        <div class="info-row"><span class="label">Tổng tiền nhập</span><span class="value" style="color:var(--success)">{{ number_format($po->total_cost, 0, ',', '.') }}₫</span></div>
                        <div class="info-row"><span class="label">Số lượng SP</span><span class="value">{{ $po->inventoryItems->count() }}</span></div>
                        <div class="info-row"><span class="label">Ngày tạo</span><span class="value">{{ $po->created_at ? $po->created_at->format('d/m/Y H:i') : '—' }}</span></div>
                    </div>
                </div>
                <div class="col-md-8 animate-in">
                    <div class="table-card">
                        <div class="table-card-header"><h5><i class="bi bi-list-ul me-2" style="color:var(--accent);"></i>Danh Sách IMEI/Serial Đã Nhập</h5></div>
                        <div class="table-responsive"><table class="table table-custom">
                            <thead><tr><th>#</th><th>IMEI/Serial</th><th>Sản Phẩm</th><th>Biến Thể</th><th>Vị Trí Kho</th><th>Trạng Thái</th></tr></thead>
                            <tbody>
                                @forelse($po->inventoryItems as $index => $item)
                                <tr>
                                    <td><span class="id-badge">{{ $index + 1 }}</span></td>
                                    <td><strong style="font-family:monospace;letter-spacing:1px;">{{ $item->imei_serial }}</strong></td>
                                    <td>{{ $item->variant->product->name ?? '—' }}</td>
                                    <td style="color:var(--text-secondary)">{{ $item->variant ? ($item->variant->color ?? '') . ($item->variant->rom_capacity ? ' - '.$item->variant->rom_capacity : '') : '—' }}</td>
                                    <td style="color:var(--text-secondary)">{{ $item->warehouse_loc ?? '—' }}</td>
                                    <td><span class="status-badge status-{{ $item->status }}">
                                        @if($item->status == 'In_Stock') Còn hàng @elseif($item->status == 'Sold') Đã bán @else Lỗi @endif
                                    </span></td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center" style="padding:40px;color:var(--text-secondary)">Không có sản phẩm nào.</td></tr>
                                @endforelse
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>function toggleSidebar(){const s=document.getElementById('sidebar');if(s)s.classList.toggle('-translate-x-full');}</script>
</body>
</html>
