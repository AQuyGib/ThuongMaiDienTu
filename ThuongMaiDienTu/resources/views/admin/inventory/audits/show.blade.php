@extends('admin.layouts.master')

@section('title', 'Chi Tiết Phiếu Kiểm Kho ' . $audit->audit_code)

@push('styles')
<style>
    .info-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        padding: 24px;
    }
    .table-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }
    .table-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid #e2e8f0;
    }
    .table-custom { margin: 0; width: 100% }
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
    .id-badge {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 4px 10px;
        font-size: .8rem;
        font-weight: 800;
        color: #475569;
    }
    .btn-action-primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 10px 24px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.2) !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
    }
    .btn-action-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 8px 18px rgba(16, 185, 129, 0.28) !important;
        color: #ffffff !important;
    }
    .text-success-custom { color: #16a34a; font-weight: 800; }
    .text-danger-custom { color: #dc2626; font-weight: 800; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px) }
        to { opacity: 1; transform: translateY(0) }
    }
    .animate-in { animation: fadeInUp .35s ease forwards }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 animate-in">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}" class="text-decoration-none">Kho hàng</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory-audits.index') }}" class="text-decoration-none">Kiểm kê</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $audit->audit_code }}</li>
                </ol>
            </nav>
            <h1 class="h3 font-weight-bold text-slate-800 m-0"><i class="bi bi-file-earmark-text-fill me-1 text-primary"></i> Phiếu Kiểm Kho: {{ $audit->audit_code }}</h1>
        </div>
        <div>
            <a href="{{ route('admin.inventory-audits.index') }}" class="btn btn-light rounded-3 px-4 fw-bold text-muted border">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-4">
            <div class="info-card h-100">
                <h5 class="font-weight-bold mb-4 text-slate-800 border-bottom pb-2"><i class="bi bi-info-circle-fill text-primary"></i> Thông Tin Phiếu</h5>
                
                <table class="table table-borderless m-0">
                    <tbody>
                        <tr>
                            <td class="text-muted ps-0 py-2">Mã phiếu:</td>
                            <td class="fw-bold py-2">{{ $audit->audit_code }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 py-2">Vị trí kho:</td>
                            <td class="fw-bold py-2"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $audit->warehouse_loc }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 py-2">Trạng thái:</td>
                            <td class="py-2">
                                @if($audit->status === 'Draft')
                                    <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill">Chờ duyệt</span>
                                @else
                                    <span class="badge bg-success px-2.5 py-1 rounded-pill">Đã cân bằng</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 py-2">Người tạo:</td>
                            <td class="py-2">{{ $audit->creator->full_name ?? ($audit->creator->name ?? '—') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0 py-2">Ngày lập:</td>
                            <td class="py-2">{{ $audit->created_at ? $audit->created_at->format('d/m/Y H:i') : '—' }}</td>
                        </tr>
                        @if($audit->completed_at)
                            <tr>
                                <td class="text-muted ps-0 py-2">Ngày cân bằng:</td>
                                <td class="py-2 text-success fw-bold">{{ $audit->completed_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted ps-0 py-2">Ghi chú:</td>
                            <td class="py-2 text-muted">{{ $audit->notes ?: '—' }}</td>
                        </tr>
                    </tbody>
                </table>

                @if($audit->status === 'Draft')
                    <div class="mt-4 border-top pt-3 d-grid">
                        <form id="reconcile-form" method="POST" action="{{ route('admin.inventory-audits.reconcile', $audit->audit_id) }}">
                            @csrf
                            <button type="button" class="btn-action-primary w-100" onclick="confirmReconcile()">
                                <i class="bi bi-check-lg me-1"></i> Xác nhận & Cân bằng tồn
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="table-card">
                <div class="table-card-header">
                    <h5 class="m-0 font-weight-bold"><i class="bi bi-card-list me-1 text-primary"></i> Chi Tiết Khớp Số Lượng</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Sản Phẩm</th>
                                <th>Biến Thể</th>
                                <th class="text-center">Số Tồn Hệ Thống</th>
                                <th class="text-center">Số Thực Tế</th>
                                <th class="text-center">Chênh Lệch</th>
                                <th>Ghi Chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($audit->details as $index => $detail)
                                <tr>
                                    <td><span class="id-badge">{{ $index + 1 }}</span></td>
                                    <td><strong>{{ $detail->variant->product->name ?? '—' }}</strong></td>
                                    <td>
                                        @if($detail->variant)
                                            <span class="badge bg-secondary">{{ $detail->variant->color ?? 'Mặc định' }}</span>
                                            @if($detail->variant->rom_capacity)
                                                <span class="badge bg-dark">{{ $detail->variant->rom_capacity }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold text-slate-700">{{ $detail->system_qty }}</td>
                                    <td class="text-center font-weight-bold text-primary">{{ $detail->actual_qty }}</td>
                                    <td class="text-center">
                                        @if($detail->discrepancy_qty > 0)
                                            <span class="text-success-custom"><i class="bi bi-caret-up-fill"></i> +{{ $detail->discrepancy_qty }} (Thừa)</span>
                                        @elseif($detail->discrepancy_qty < 0)
                                            <span class="text-danger-custom"><i class="bi bi-caret-down-fill"></i> {{ $detail->discrepancy_qty }} (Thiếu)</span>
                                        @else
                                            <span class="text-muted"><i class="bi bi-check-lg"></i> Khớp (0)</span>
                                        @endif
                                    </td>
                                    <td><span class="text-muted text-sm">{{ $detail->notes ?: '—' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Sử dụng SweetAlert2 có sẵn trong dự án để xác nhận duyệt phiếu --}}
<script>
    function confirmReconcile() {
        Swal.fire({
            title: 'Xác nhận Cân bằng tồn?',
            text: 'Tồn kho thực tế của các biến thể tại địa điểm này sẽ tự động tăng/giảm theo mức lệch thừa/thiếu. Hành động này không thể hoàn tác.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Đồng ý cân bằng',
            cancelButtonText: 'Hủy bỏ'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('reconcile-form').submit();
            }
        });
    }
</script>
@endsection
