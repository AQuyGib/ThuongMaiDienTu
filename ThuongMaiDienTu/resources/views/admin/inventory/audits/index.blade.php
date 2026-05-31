@extends('admin.layouts.master')

@section('title', 'Phiếu Kiểm Kê Kho')

@push('styles')
<style>
    .page-tabs {
        display: flex;
        justify-content: center;
        gap: 28px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }
    .page-tab {
        position: relative;
        font-size: 1rem;
        font-weight: 700;
        color: #64748b;
        text-decoration: none;
        padding-bottom: 8px;
        transition: color .2s ease;
    }
    .page-tab:hover { color: #111827 }
    .page-tab.active { color: #111827 }
    .page-tab.active::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 2px;
        border-radius: 999px;
        background: #111827;
    }
    .hero-card {
        background: linear-gradient(135deg, #fff 0%, #f0f9ff 100%);
        border: 1px solid #e0f2fe;
        border-radius: 26px;
        padding: 22px 24px;
        box-shadow: 0 12px 30px rgba(14, 165, 233, 0.03);
    }
    .hero-title {
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0;
        color: #0369a1;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .hero-title i { color: #0ea5e9 }
    .hero-desc {
        color: #0369a1;
        opacity: 0.8;
        margin-top: 4px;
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
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .table-card-header h5 {
        margin: 0;
        font-weight: 800;
        color: #0f172a;
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
        white-space: nowrap;
    }
    .table-custom tbody td {
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
        font-size: .92rem;
        color: #334155;
    }
    .table-custom tbody tr:hover { background: #f8fafc }
    .id-badge {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 4px 10px;
        font-size: .8rem;
        font-weight: 800;
        color: #475569;
    }
    .btn-create-audit {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 10px 18px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        box-shadow: 0 8px 20px rgba(14, 165, 233, 0.2) !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .btn-create-audit:hover {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 10px 24px rgba(14, 165, 233, 0.28) !important;
        color: #ffffff !important;
    }
    .empty-state {
        text-align: center;
        padding: 70px 20px;
        color: #64748b;
    }
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 14px;
        opacity: .35;
    }
    .custom-pagination-nav {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        padding: 16px 24px !important;
        border-top: 1px solid #eef2f7 !important;
        background-color: #ffffff !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
    }
    .custom-pagination-nav p {
        margin: 0 !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background: #f8fafc !important;
        padding: 8px 16px !important;
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
    }
    .custom-pagination-nav p span {
        color: #0f172a !important;
        font-weight: 800 !important;
    }
    .pagination {
        display: flex !important;
        list-style: none !important;
        padding-left: 0 !important;
        margin: 0 !important;
        gap: 6px !important;
        align-items: center !important;
    }
    .page-link, .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 8px 14px !important;
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        color: #475569 !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        text-decoration: none !important;
        transition: all 0.2s !important;
        min-width: 40px !important;
        height: 40px !important;
    }
    .page-link:hover {
        background: #f8fafc !important;
        color: #2563eb !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px);
    }
    .page-item.active .page-link, .page-item.active span {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 4px 12px rgba(37,99,235,0.25) !important;
    }
    .page-item.disabled .page-link, .page-item.disabled span {
        color: #94a3b8 !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none !important;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px) }
        to { opacity: 1; transform: translateY(0) }
    }
    .animate-in { animation: fadeInUp .35s ease forwards }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 animate-in">
    @include('admin.partials.inventory-nav')

    <div class="hero-card mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="hero-title">
                    <i class="bi bi-clipboard-check"></i>
                    Kiểm Kê Kho Hàng
                </h1>
                <div class="hero-desc">Quản lý các phiếu đối chiếu kiểm kho thực tế, đối soát lệch số lượng thừa/thiếu và thực hiện cân bằng.</div>
            </div>
            <div>
                <a href="{{ route('admin.inventory-audits.create') }}" class="btn-create-audit">
                    <i class="bi bi-plus-circle"></i> Tạo Phiếu Kiểm Kho
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" id="flash-alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="table-card mb-4">
        <div class="table-card-header">
            <h5><i class="bi bi-list-ul me-2 text-primary"></i>Danh Sách Phiếu Kiểm Kho</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Mã Phiếu</th>
                        <th>Vị Trí Kho</th>
                        <th>Số Mặt Hàng</th>
                        <th>Trạng Thái</th>
                        <th>Người Tạo</th>
                        <th>Ghi Chú</th>
                        <th>Ngày Tạo</th>
                        <th>Ngày Duyệt</th>
                        <th class="text-center" style="width: 120px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $index => $audit)
                        <tr>
                            <td><span class="id-badge">{{ $audits->firstItem() + $index }}</span></td>
                            <td><strong class="text-primary">{{ $audit->audit_code }}</strong></td>
                            <td><i class="bi bi-geo-alt-fill text-muted me-1"></i>{{ $audit->warehouse_loc }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $audit->details_count }} mặt hàng</span></td>
                            <td>
                                @if($audit->status === 'Draft')
                                    <span class="badge bg-warning text-dark px-2.5 py-1.5 rounded-pill"><i class="bi bi-clock-fill me-1"></i>Chờ duyệt</span>
                                @else
                                    <span class="badge bg-success px-2.5 py-1.5 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i>Đã cân bằng</span>
                                @endif
                            </td>
                            <td>{{ $audit->creator->full_name ?? ($audit->creator->name ?? '—') }}</td>
                            <td><span class="text-muted text-sm">{{ Str::limit($audit->notes, 30) ?: '—' }}</span></td>
                            <td>{{ $audit->created_at ? $audit->created_at->format('d/m/Y H:i') : '—' }}</td>
                            <td>{{ $audit->completed_at ? $audit->completed_at->format('d/m/Y H:i') : '—' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.inventory-audits.show', $audit->audit_id) }}" class="btn btn-outline-primary btn-sm rounded-3 fw-bold">
                                    <i class="bi bi-eye-fill"></i> Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="bi bi-clipboard-x d-block"></i>
                                    <p class="mb-0">Chưa có phiếu kiểm kê kho nào được tạo.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($audits, 'links') && $audits->lastPage() > 1)
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $audits->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>{{ $audits->currentPage() }}</span> / {{ $audits->lastPage() }}
                </p>
                
                <ul class="pagination">
                    @if ($audits->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $audits->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif

                    @foreach ($audits->getUrlRange(1, $audits->lastPage()) as $page => $url)
                        @if ($page == $audits->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @elseif ($page === 1 || $page === $audits->lastPage() || abs($page - $audits->currentPage()) <= 1)
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @elseif (($page === 2 && $audits->currentPage() > 3) || ($page === $audits->lastPage() - 1 && $audits->currentPage() < $audits->lastPage() - 2))
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                    @endforeach

                    @if ($audits->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $audits->nextPageUrl() }}" rel="next">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>
</div>
@endsection
