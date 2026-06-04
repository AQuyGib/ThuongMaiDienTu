@extends('admin.layouts.master')

@section('title', 'Chi Tiết Phiếu Nhập #' . str_pad($po->po_id, 5, '0', STR_PAD_LEFT))

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
    .btn-soft{border:1px solid #dbe3ee;background:#fff;color:#475569;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
    .btn-soft:hover{background:#f8fafc;color:#0f172a}
    .info-card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:22px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
    .info-card h5{margin:0 0 18px;font-weight:800;color:#0f172a;display:flex;align-items:center;gap:8px}
    .info-row{display:flex;justify-content:space-between;gap:16px;padding:12px 0;border-bottom:1px solid #eef2f7;font-size:.92rem}
    .info-row:last-child{border-bottom:none}
    .info-row .label{color:#64748b}
    .info-row .value{font-weight:700;color:#0f172a;text-align:right}
    .table-card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,.05)}
    .table-card-header{padding:18px 22px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .table-card-header h5{margin:0;font-weight:800;color:#0f172a}
    .table-custom{margin:0;width:100%}
    .table-custom thead th{background:#f8fafc;color:#64748b;font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;padding:14px 18px;border:none;white-space:nowrap}
    .table-custom tbody td{padding:16px 18px;border-bottom:1px solid #eef2f7;vertical-align:middle;font-size:.92rem;color:#334155}
    .table-custom tbody tr:hover{background:#f8fafc}
    .id-badge{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:10px;padding:4px 10px;font-size:.8rem;font-weight:800;color:#475569}
    .status-badge{padding:5px 12px;border-radius:999px;font-size:.8rem;font-weight:800;display:inline-flex;align-items:center;gap:6px}
    .status-In_Stock{background:rgba(34,197,94,.12);color:#16a34a}
    .status-Sold{background:rgba(37,99,235,.12);color:#2563eb}
    .status-Defective{background:rgba(239,68,68,.12);color:#dc2626}
    .imei-code{font-family:monospace;letter-spacing:1px;font-weight:800;color:#0f172a}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
    .animate-in{animation:fadeInUp .35s ease forwards}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @include('admin.partials.inventory-nav')

    <div class="hero-card mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="hero-title"><i class="bi bi-file-earmark-text"></i> Chi Tiết Phiếu Nhập PO-{{ str_pad($po->po_id, 5, '0', STR_PAD_LEFT) }}</h1>
                <div class="hero-desc">Xem thông tin phiếu và danh sách IMEI/Serial đã nhập.</div>
            </div>
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Quay lại</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="info-card animate-in">
                <h5><i class="bi bi-info-circle"></i> Thông Tin Phiếu</h5>
                <div class="info-row"><span class="label">Mã phiếu</span><span class="value">PO-{{ str_pad($po->po_id, 5, '0', STR_PAD_LEFT) }}</span></div>
                <div class="info-row"><span class="label">Nhà cung cấp</span><span class="value">{{ $po->supplier->name ?? '—' }}</span></div>
                <div class="info-row"><span class="label">Tổng tiền nhập</span><span class="value text-success">{{ number_format($po->total_cost, 0, ',', '.') }}₫</span></div>
                <div class="info-row"><span class="label">Số lượng IMEI</span><span class="value">{{ $po->inventory_items_count }}</span></div>
                <div class="info-row"><span class="label">Ngày tạo</span><span class="value">{{ $po->created_at ? $po->created_at->format('d/m/Y H:i') : '—' }}</span></div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="table-card animate-in">
                <div class="table-card-header">
                    <h5><i class="bi bi-list-ul me-2 text-primary"></i>Danh Sách IMEI/Serial Đã Nhập</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th style="width:90px;">#</th>
                                <th>IMEI/Serial</th>
                                <th>Sản Phẩm</th>
                                <th>Biến Thể</th>
                                <th>Vị Trí Kho</th>
                                <th>Trạng Thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryItems as $index => $item)
                                <tr>
                                    <td><span class="id-badge">{{ $loop->iteration + ($inventoryItems->currentPage() - 1) * $inventoryItems->perPage() }}</span></td>
                                    <td><span class="imei-code">{{ $item->imei_serial }}</span></td>
                                    <td>{{ $item->variant->product->name ?? '—' }}</td>
                                    <td>{{ $item->variant ? ($item->variant->color ?? '') . ($item->variant->rom_capacity ? ' - '.$item->variant->rom_capacity : '') : '—' }}</td>
                                    <td>{{ $item->warehouse_loc ?? '—' }}</td>
                                    <td><span class="status-badge status-{{ $item->status }}">{{ $item->status == 'In_Stock' ? 'Còn hàng' : ($item->status == 'Sold' ? 'Đã bán' : 'Lỗi') }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="text-center py-5 text-secondary">
                                            <i class="bi bi-inbox d-block mb-2" style="font-size:3rem;opacity:.35"></i>
                                            <p class="mb-0">Không có sản phẩm nào.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($inventoryItems->hasPages())
                    <div class="px-4 py-3 border-top bg-light">
                        {{ $inventoryItems->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection