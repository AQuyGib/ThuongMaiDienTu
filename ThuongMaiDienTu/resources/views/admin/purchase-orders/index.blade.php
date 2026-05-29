@extends('admin.layouts.master')

@section('title', 'Phiếu Nhập Kho')

@push('styles')
<style>
    .page-tabs{display:flex;justify-content:center;gap:28px;margin-bottom:28px;flex-wrap:wrap}
    .page-tab{position:relative;font-size:1rem;font-weight:700;color:#64748b;text-decoration:none;padding-bottom:8px}
    .page-tab.active{color:#111827}
    .page-tab.active::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2px;border-radius:999px;background:#111827}
    .hero-card{background:linear-gradient(135deg,#fff 0%,#f8fbff 100%);border:1px solid #e2e8f0;border-radius:26px;padding:22px 24px;box-shadow:0 12px 30px rgba(15,23,42,.05)}
    .hero-title{font-size:1.5rem;font-weight:800;margin:0;color:#0f172a;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .hero-title i{color:#2563eb}
    .hero-desc{color:#64748b;margin-top:4px}
    .badge-count{background:#2563eb;color:#fff;font-size:.75rem;font-weight:800;padding:5px 10px;border-radius:999px}
    .btn-soft{border:1px solid #dbe3ee;background:#fff;color:#475569;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
    .btn-soft:hover{background:#f8fafc;color:#0f172a}
    .btn-primary-strong{background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:#fff;border:none;border-radius:16px;padding:12px 20px;font-weight:900;display:inline-flex;align-items:center;gap:8px;text-decoration:none;box-shadow:0 12px 28px rgba(37,99,235,.22);transition:all .2s ease;white-space:nowrap}
    .btn-primary-strong:hover{background:linear-gradient(135deg,#1d4ed8 0%,#1e40af 100%);color:#fff;transform:translateY(-1px);box-shadow:0 16px 34px rgba(37,99,235,.28)}
    .stat-card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:22px 24px;transition:all .25s;box-shadow:0 10px 30px rgba(15,23,42,.05);min-height:116px}
    .stat-card:hover{transform:translateY(-2px);box-shadow:0 16px 34px rgba(15,23,42,.08)}
    .stat-card .stat-icon{width:48px;height:48px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.05rem;background:#fff;box-shadow:0 8px 18px rgba(15,23,42,.05)}
    .stat-card .stat-value{font-size:1.7rem;font-weight:800;line-height:1;color:#111827}
    .stat-card .stat-label{font-size:.74rem;color:#94a3b8;margin-top:4px;text-transform:uppercase;letter-spacing:.12em;font-weight:700}
    .table-card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,.05)}
    .table-card-header{padding:18px 22px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .table-card-header h5{margin:0;font-weight:800;color:#0f172a}
    .table-custom{margin:0;width:100%}
    .table-custom thead th{background:#f8fafc;color:#64748b;font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;padding:14px 18px;border:none;white-space:nowrap}
    .table-custom tbody td{padding:16px 18px;border-bottom:1px solid #eef2f7;vertical-align:middle;font-size:.92rem;color:#334155}
    .table-custom tbody tr:hover{background:#f8fafc}
    .id-badge{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:10px;padding:4px 10px;font-size:.8rem;font-weight:800;color:#475569}
    .po-badge{background:rgba(37,99,235,.1);color:#2563eb;padding:4px 12px;border-radius:999px;font-size:.8rem;font-weight:800}
    .cost-badge{font-weight:800;color:#16a34a}
    .empty-state{text-align:center;padding:70px 20px;color:#64748b}
    .empty-state i{font-size:3rem;margin-bottom:14px;opacity:.35}
    .pagination .page-link{background:#fff;border:1px solid #dbe3ee;color:#475569;border-radius:10px!important;margin:0 3px;font-size:.85rem;padding:7px 13px}
    .pagination .page-link:hover,.pagination .page-item.active .page-link{background:#2563eb;border-color:#2563eb;color:#fff}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
    .animate-in{animation:fadeInUp .35s ease forwards}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @include('admin.partials.inventory-nav')

    <div class="hero-card mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 w-100">
            <div class="pe-3 flex-grow-1">
                <h1 class="hero-title"><i class="bi bi-file-earmark-arrow-down"></i> Phiếu Nhập Kho <span class="badge-count">{{ $totalPO ?? 0 }} phiếu</span></h1>
                <div class="hero-desc">Quản lý các phiếu nhập hàng từ nhà cung cấp và theo dõi số IMEI đã tạo.</div>
            </div>
            <div class="d-flex align-items-center justify-content-end flex-shrink-0 ms-auto">
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn-primary-strong"><i class="bi bi-plus-lg"></i> Tạo Phiếu Nhập</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" id="flash-alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4" id="flash-alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="row g-3 mb-4 justify-content-center mx-auto" style="max-width: 1080px;">
        <div class="col-12 col-md-6">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="color:#2563eb;background:rgba(37,99,235,.10);"><i class="bi bi-file-earmark-text"></i></div>
                <div><div class="stat-value">{{ $totalPO ?? 0 }}</div><div class="stat-label">Tổng Phiếu Nhập</div></div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="color:#16a34a;background:rgba(34,197,94,.10);"><i class="bi bi-box-seam"></i></div>
                <div><div class="stat-value">{{ $totalItems ?? 0 }}</div><div class="stat-label">Tổng IMEI Đã Nhập</div></div>
            </div>
        </div>
    </div>

    <div class="table-card animate-in">
        <div class="table-card-header">
            <h5><i class="bi bi-list-ul me-2 text-primary"></i>Danh Sách Phiếu Nhập</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width:90px;">#</th>
                        <th>Mã Phiếu</th>
                        <th>Nhà Cung Cấp</th>
                        <th class="text-center">Số Lượng IMEI</th>
                        <th class="text-center">Tổng Tiền Nhập</th>
                        <th>Ngày Tạo</th>
                        <th class="text-center">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseOrders as $po)
                        <tr>
                            <td><span class="id-badge">{{ $purchaseOrders->firstItem() + $loop->index }}</span></td>
                            <td><strong>PO-{{ str_pad($po->po_id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                            <td>{{ $po->supplier->name ?? '—' }}</td>
                            <td class="text-center"><span class="po-badge">{{ $po->inventory_items_count ?? 0 }}</span></td>
                            <td class="text-center"><span class="cost-badge">{{ number_format($po->total_cost, 0, ',', '.') }}₫</span></td>
                            <td>{{ $po->created_at ? $po->created_at->format('d/m/Y H:i') : '—' }}</td>
                            <td class="text-center"><a href="{{ route('admin.purchase-orders.show', $po->po_id) }}" class="btn-soft"><i class="bi bi-eye"></i> Chi tiết</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-inbox d-block"></i>
                                    <p class="mb-0">Chưa có phiếu nhập nào. Hãy tạo phiếu nhập đầu tiên!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($purchaseOrders, 'links'))
            <div class="d-flex justify-content-center py-3">{{ $purchaseOrders->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>
@endsection