@extends('admin.layouts.master')

@section('title', 'Chi Tiết Phiếu Điều Chuyển')

@push('styles')
<style>
    .details-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
        margin-bottom: 24px;
    }
    .info-label {
        font-size: .85rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        margin-bottom: 4px;
    }
    .info-value {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
    }
    .table-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
    }
    .table-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .table-card-header h5 {
        margin: 0;
        font-weight: 800;
        color: #0f172a;
    }
    .table-custom { margin: 0; width: 100%; }
    .table-custom thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 14px 18px;
        border: none;
    }
    .table-custom tbody td {
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        font-size: .92rem;
        color: #334155;
    }
    .status-badge {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: .8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-completed { background: #dcfce7; color: #16a34a; }
    .status-cancelled { background: #fee2e2; color: #dc2626; }
    .imei-badge {
        font-family: monospace;
        background: #f1f5f9;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: .9rem;
        font-weight: 700;
        color: #334155;
        border: 1px solid #e2e8f0;
    }

    /* Button styling fallback */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 12px;
        border: 1px solid transparent;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
        border-radius: 8px;
    }
    .btn-primary {
        background-color: #2563eb;
        color: #ffffff !important;
        border-color: #2563eb;
    }
    .btn-primary:hover {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }
    .btn-success {
        background-color: #16a34a;
        color: #ffffff !important;
        border-color: #16a34a;
    }
    .btn-success:hover {
        background-color: #15803d;
        border-color: #15803d;
    }
    .btn-warning {
        background-color: #eab308;
        color: #1e293b !important;
        border-color: #eab308;
    }
    .btn-warning:hover {
        background-color: #ca8a04;
        border-color: #ca8a04;
    }
    .btn-danger {
        background-color: #dc2626;
        color: #ffffff !important;
        border-color: #dc2626;
    }
    .btn-danger:hover {
        background-color: #b91c1c;
        border-color: #b91c1c;
    }
    .btn-secondary {
        background-color: #64748b;
        color: #ffffff !important;
        border-color: #64748b;
    }
    .btn-secondary:hover {
        background-color: #475569;
        border-color: #475569;
    }
    .btn-outline-secondary {
        background-color: transparent;
        color: #475569 !important;
        border-color: #cbd5e1;
    }
    .btn-outline-secondary:hover {
        background-color: #f8fafc;
        color: #1e293b !important;
        border-color: #94a3b8;
    }
    .btn-outline-danger {
        background-color: transparent;
        color: #dc2626 !important;
        border-color: #fca5a5;
    }
    .btn-outline-danger:hover {
        background-color: #fef2f2;
        border-color: #dc2626;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 960px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center gap-3">
            <h4 class="mb-0" style="font-weight: 800; color: #0f172a;">
                Phiếu Điều Chuyển: {{ $transfer->transfer_code }}
            </h4>
            @if($transfer->status == 'Pending')
                <span class="status-badge status-pending"><i class="bi bi-clock-history"></i> Chờ xử lý</span>
            @elseif($transfer->status == 'Completed')
                <span class="status-badge status-completed"><i class="bi bi-check-circle"></i> Đã hoàn thành</span>
            @else
                <span class="status-badge status-cancelled"><i class="bi bi-x-circle"></i> Đã hủy</span>
            @endif
        </div>
        <a href="{{ route('admin.warehouse-transfers.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight:600;">
            <i class="bi bi-arrow-left"></i> Danh sách phiếu
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" id="flash-alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <h6 style="font-weight:700;"><i class="bi bi-exclamation-triangle-fill me-2"></i>Có lỗi xảy ra:</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="details-card">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="info-label">Kho đi (Kho nguồn)</div>
                <div class="info-value"><i class="bi bi-geo-alt-fill text-muted"></i> {{ $transfer->from_warehouse }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-label">Kho đến (Kho đích)</div>
                <div class="info-value"><i class="bi bi-geo-alt-fill text-primary"></i> {{ $transfer->to_warehouse }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-label">Người lập phiếu</div>
                <div class="info-value">{{ $transfer->creator->name ?? 'Hệ thống' }}</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="info-label">Ngày lập</div>
                <div class="info-value">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
            </div>
            @if($transfer->notes)
                <div class="col-12 border-top pt-3">
                    <div class="info-label">Ghi chú phiếu</div>
                    <div class="info-value" style="font-weight:500; font-size:0.95rem; color:#475569;">{{ $transfer->notes }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="table-card-header">
            <h5 class="mb-0"><i class="bi bi-box-seam me-2 text-primary"></i>Danh Sách IMEI Điều Chuyển ({{ $transfer->items->count() }} mặt hàng)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width:70px;">#</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Biến Thể</th>
                        <th>Mã IMEI / Serial</th>
                        <th class="text-center" style="width: 150px;">Trạng Thái IMEI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfer->items as $index => $item)
                        <tr>
                            <td><span class="id-badge">{{ $index + 1 }}</span></td>
                            <td><strong>{{ $item->variant->product->name ?? '—' }}</strong></td>
                            <td>
                                <span class="badge bg-secondary">{{ $item->variant->color ?? 'Mặc định' }}</span>
                                @if($item->variant->rom_capacity)
                                    <span class="badge bg-dark">{{ $item->variant->rom_capacity }}</span>
                                @endif
                            </td>
                            <td><span class="imei-badge">{{ $item->imei_serial }}</span></td>
                            <td class="text-center">
                                @if($item->status == 'In_Stock')
                                    <span class="badge bg-success">Còn hàng</span>
                                @elseif($item->status == 'Sold')
                                    <span class="badge bg-primary">Đã bán</span>
                                @else
                                    <span class="badge bg-danger">Lỗi</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($transfer->status == 'Pending')
        <div class="d-flex justify-content-end gap-3 mt-4">
            <form action="{{ route('admin.warehouse-transfers.cancel', $transfer->transfer_id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn HỦY phiếu điều chuyển này không?')">
                @csrf
                <button type="submit" class="btn btn-outline-danger" style="border-radius:12px; padding:10px 20px; font-weight:700;">
                    <i class="bi bi-x-circle"></i> Hủy Phiếu
                </button>
            </form>

            <form action="{{ route('admin.warehouse-transfers.complete', $transfer->transfer_id) }}" method="POST" onsubmit="return confirm('Xác nhận hoàn thành việc điều chuyển hàng hóa? Vị trí kho của các IMEI trong phiếu này sẽ tự động thay đổi sang kho đích.')">
                @csrf
                <button type="submit" class="btn btn-success" style="border-radius:12px; padding:10px 24px; font-weight:700;">
                    <i class="bi bi-check-circle-fill"></i> Hoàn Thành Điều Chuyển
                </button>
            </form>
        </div>
    @endif
</div>
@endsection
