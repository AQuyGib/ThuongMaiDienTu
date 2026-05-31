@extends('admin.layouts.master')

@section('title', 'Điều Chuyển Nội Bộ')

@push('styles')
<style>
    .page-tabs{
        display:flex;
        justify-content:center;
        gap:28px;
        margin-bottom:28px;
        flex-wrap:wrap;
    }
    .page-tab{
        position:relative;
        font-size:1rem;
        font-weight:700;
        color:#64748b;
        text-decoration:none;
        padding-bottom:8px;
        transition:color .2s ease;
    }
    .page-tab:hover{color:#111827}
    .page-tab.active{color:#111827}
    .page-tab.active::after{
        content:'';
        position:absolute;
        left:0;
        right:0;
        bottom:0;
        height:2px;
        border-radius:999px;
        background:#111827;
    }
    .hero-card{
        background:linear-gradient(135deg,#fff 0%,#f0fdf4 100%);
        border:1px solid #bbf7d0;
        border-radius:26px;
        padding:22px 24px;
        box-shadow:0 12px 30px rgba(22,163,74,.03);
    }
    .hero-title{
        font-size:1.5rem;
        font-weight:800;
        margin:0;
        color:#166534;
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }
    .hero-title i{color:#16a34a}
    .hero-desc{
        color:#14532d;
        opacity:0.8;
        margin-top:4px;
    }
    .badge-count{
        background:#16a34a;
        color:#fff;
        font-size:.75rem;
        font-weight:800;
        padding:5px 10px;
        border-radius:999px;
    }
    .table-card{
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:24px;
        overflow:hidden;
        box-shadow:0 10px 30px rgba(15,23,42,.05);
    }
    .table-card-header{
        padding:18px 22px;
        border-bottom:1px solid #e2e8f0;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
    }
    .table-card-header h5{
        margin:0;
        font-weight:800;
        color:#0f172a;
    }
    .table-custom{margin:0;width:100%}
    .table-custom thead th{
        background:#f8fafc;
        color:#64748b;
        font-weight:700;
        font-size:.78rem;
        text-transform:uppercase;
        letter-spacing:.04em;
        padding:14px 18px;
        border:none;
        white-space:nowrap;
    }
    .table-custom tbody td{
        padding:16px 18px;
        border-bottom:1px solid #eef2f7;
        vertical-align:middle;
        font-size:.92rem;
        color:#334155;
    }
    .table-custom tbody tr:hover{background:#f8fafc}
    .id-badge{
        background:#f1f5f9;
        border:1px solid #e2e8f0;
        border-radius:10px;
        padding:4px 10px;
        font-size:.8rem;
        font-weight:800;
        color:#475569;
    }
    .empty-state{
        text-align:center;
        padding:70px 20px;
        color:#64748b;
    }
    .empty-state i{
        font-size:3rem;
        margin-bottom:14px;
        opacity:.35;
    }
    .status-badge{
        padding:6px 12px;
        border-radius:999px;
        font-size:.8rem;
        font-weight:700;
        display:inline-flex;
        align-items:center;
        gap:5px;
    }
    .status-pending{
        background:#fef3c7;
        color:#d97706;
    }
    .status-completed{
        background:#dcfce7;
        color:#16a34a;
    }
    .status-cancelled{
        background:#fee2e2;
        color:#dc2626;
    }
    @keyframes fadeInUp{
        from{opacity:0;transform:translateY(16px)}
        to{opacity:1;transform:translateY(0)}
    }
    .animate-in{animation:fadeInUp .35s ease forwards}

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
    .btn-secondary {
        background-color: #64748b;
        color: #ffffff !important;
        border-color: #64748b;
    }
    .btn-secondary:hover {
        background-color: #475569;
        border-color: #475569;
    }

    /* Pagination CSS Fallback matching User Management style */
    /* Custom pagination wrapper to force horizontal layout */
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
    
    /* Left side: Results info */
    .custom-pagination-nav p {
        margin: 0 !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        color: #475569 !important; /* Màu xám đậm Slate-600 */
        background: #f8fafc !important; /* Nền xám nhạt Slate-50 */
        padding: 8px 16px !important;
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
    }
    
    .custom-pagination-nav p span {
        color: #0f172a !important; /* Màu đen Slate-900 để làm nổi bật các con số */
        font-weight: 800 !important;
    }
    
    /* Right side: Page selection numbers */
    .pagination {
        display: flex !important;
        list-style: none !important;
        padding-left: 0 !important;
        margin: 0 !important;
        gap: 6px !important;
        align-items: center !important;
    }
    
    .page-item {
        display: inline-block !important;
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
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    
    .page-link:hover {
        background: #f8fafc !important;
        color: #2563eb !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px);
    }
    
    /* Active page item */
    .page-item.active .page-link, .page-item.active span {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 4px 12px rgba(37,99,235,0.25) !important;
    }
    
    /* Disabled pagination buttons */
    .page-item.disabled .page-link, .page-item.disabled span {
        color: #94a3b8 !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <div class="hero-card mb-4 animate-in">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="hero-title">
                    <i class="bi bi-truck-flatbed"></i> 
                    Điều Chuyển Kho Nội Bộ
                    <span class="badge-count">{{ $transfers->total() }} Phiếu</span>
                </h1>
                <div class="hero-desc">Tạo và quản lý các phiếu luân chuyển hàng hóa (mã IMEI) giữa kho tổng và các kho cửa hàng thành viên.</div>
            </div>
            <div>
                <a href="{{ route('admin.warehouse-transfers.create') }}" class="btn btn-primary d-flex align-items-center gap-2" style="border-radius: 12px; font-weight:700;">
                    <i class="bi bi-plus-circle"></i> Lập Phiếu Điều Chuyển
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" id="flash-alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="table-card animate-in">
        <div class="table-card-header">
            <h5><i class="bi bi-list-ul me-2 text-primary"></i>Danh Sách Phiếu Luân Chuyển</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width:70px;">#</th>
                        <th>Mã Phiếu</th>
                        <th>Kho Đi</th>
                        <th>Kho Đến</th>
                        <th class="text-center">Số Lượng</th>
                        <th>Trạng Thái</th>
                        <th>Người Lập</th>
                        <th>Ngày Lập</th>
                        <th class="text-center" style="width:120px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $transfer)
                        <tr>
                            <td><span class="id-badge">{{ $transfers->firstItem() + $loop->index }}</span></td>
                            <td><strong style="color:#2563eb;">{{ $transfer->transfer_code }}</strong></td>
                            <td><i class="bi bi-geo-alt-fill text-muted"></i> {{ $transfer->from_warehouse }}</td>
                            <td><i class="bi bi-geo-alt text-primary"></i> {{ $transfer->to_warehouse }}</td>
                            <td class="text-center">
                                <span class="badge bg-secondary" style="font-size:0.85rem; font-weight:700;">
                                    {{ $transfer->items_count }} IMEI
                                </span>
                            </td>
                            <td>
                                @if($transfer->status == 'Pending')
                                    <span class="status-badge status-pending"><i class="bi bi-clock-history"></i> Chờ xử lý</span>
                                @elseif($transfer->status == 'Completed')
                                    <span class="status-badge status-completed"><i class="bi bi-check-circle"></i> Đã hoàn thành</span>
                                @else
                                    <span class="status-badge status-cancelled"><i class="bi bi-x-circle"></i> Đã hủy</span>
                                @endif
                            </td>
                            <td>{{ $transfer->creator->name ?? 'Hệ thống' }}</td>
                            <td>{{ $transfer->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                @if($transfer->status == 'Pending')
                                    <a href="{{ route('admin.warehouse-transfers.show', $transfer->transfer_id) }}" class="btn btn-warning text-dark btn-sm" style="border-radius:8px; font-weight:700; box-shadow: 0 4px 10px rgba(217,119,6,0.15);">
                                        <i class="bi bi-pencil-square"></i> Duyệt phiếu
                                    </a>
                                @elseif($transfer->status == 'Completed')
                                    <a href="{{ route('admin.warehouse-transfers.show', $transfer->transfer_id) }}" class="btn btn-success text-white btn-sm" style="border-radius:8px; font-weight:700; box-shadow: 0 4px 10px rgba(22,163,74,0.15);">
                                        <i class="bi bi-check-circle-fill"></i> Xem chi tiết
                                    </a>
                                @else
                                    <a href="{{ route('admin.warehouse-transfers.show', $transfer->transfer_id) }}" class="btn btn-secondary btn-sm" style="border-radius:8px; font-weight:600; opacity: 0.85;">
                                        <i class="bi bi-eye"></i> Xem chi tiết
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="bi bi-truck-flatbed d-block"></i>
                                    <p class="mb-0 mt-2" style="font-weight:600; color:#1e293b;">Chưa có phiếu điều chuyển nào!</p>
                                    <small class="text-muted">Nhấp nút "Lập Phiếu Điều Chuyển" ở trên để tạo phiếu luân chuyển hàng hóa đầu tiên.</small>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($transfers, 'links') && $transfers->lastPage() > 1)
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $transfers->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>{{ $transfers->currentPage() }}</span> / {{ $transfers->lastPage() }}
                </p>
                
                <ul class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($transfers->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $transfers->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($transfers->getUrlRange(1, $transfers->lastPage()) as $page => $url)
                        @if ($page == $transfers->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @elseif ($page === 1 || $page === $transfers->lastPage() || abs($page - $transfers->currentPage()) <= 1)
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @elseif (($page === 2 && $transfers->currentPage() > 3) || ($page === $transfers->lastPage() - 1 && $transfers->currentPage() < $transfers->lastPage() - 2))
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($transfers->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $transfers->nextPageUrl() }}" rel="next">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </nav>
        @elseif(method_exists($transfers, 'links'))
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $transfers->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>1</span> / 1
                </p>
            </nav>
        @endif
    </div>
</div>
@endsection
