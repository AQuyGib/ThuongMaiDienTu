@extends('admin.layouts.master')

@section('title', 'Biến Động Kho')

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
        background: linear-gradient(135deg, #fff 0%, #f0fdf4 100%);
        border: 1px solid #dcfce7;
        border-radius: 26px;
        padding: 22px 24px;
        box-shadow: 0 12px 30px rgba(22, 163, 74, 0.03);
    }
    .hero-title {
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0;
        color: #14532d;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .hero-title i { color: #16a34a }
    .hero-desc {
        color: #166534;
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
        padding: 20px 24px;
        border-bottom: 1px solid #eef2f7;
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
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .form-control-custom, .form-select-custom {
        width: 100%;
        background: #fff;
        border: 1px solid #dbe3ee;
        color: #0f172a;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: .9rem;
        transition: all .2s;
    }
    .form-control-custom:focus, .form-select-custom:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
    }
    .qty-change-badge {
        font-weight: 800;
        font-size: 1rem;
        font-family: monospace;
    }
    .text-success-custom { color: #16a34a }
    .text-danger-custom { color: #dc2626 }
    
    /* Pagination style matching main layout */
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
                    <i class="bi bi-clock-history"></i>
                    Lịch Sử Biến Động Kho
                </h1>
                <div class="hero-desc">Theo dõi chi tiết mọi thay đổi xuất, nhập, hoàn trả và cân chỉnh số lượng tồn kho.</div>
            </div>
        </div>
    </div>

    <div class="table-card mb-4">
        <div class="table-card-header">
            <h5 class="mb-3"><i class="bi bi-funnel-fill me-2 text-primary"></i>Bộ Lọc Biến Động</h5>
            
            <form method="GET" action="{{ route('admin.inventory.movements') }}">
                <div class="filter-grid">
                    <div>
                        <label class="form-label small text-muted fw-bold">Sản phẩm</label>
                        <select name="product_id" class="form-select-custom">
                            <option value="">-- Tất cả sản phẩm --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->product_id }}" {{ request('product_id') == $product->product_id ? 'selected' : '' }}>
                                    {{ $product->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Loại biến động</label>
                        <select name="type" class="form-select-custom">
                            <option value="">-- Tất cả loại --</option>
                            @foreach($types as $key => $typeData)
                                <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                    {{ $typeData['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Từ ngày</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control-custom">
                    </div>

                    <div>
                        <label class="form-label small text-muted fw-bold">Đến ngày</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control-custom">
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pt-2 border-top">
                    <div style="flex-grow: 1; max-width: 400px;">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo ghi chú, mã đơn hàng..." class="form-control-custom">
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.inventory.movements') }}" class="btn btn-light rounded-3 px-4 fw-bold">Xóa lọc</a>
                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold"><i class="bi bi-search me-1"></i> Lọc kết quả</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width: 80px;">#</th>
                        <th>Sản Phẩm</th>
                        <th>Biến Thể</th>
                        <th>Loại Biến Động</th>
                        <th class="text-center">Số Lượng Thay Đổi</th>
                        <th class="text-center">Trước Biến Động</th>
                        <th class="text-center">Sau Biến Động</th>
                        <th>Người Thực Hiện</th>
                        <th>Ghi Chú</th>
                        <th>Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td><span class="id-badge">{{ $movements->firstItem() + $loop->index }}</span></td>
                            <td>
                                <strong>{{ $movement->product->name ?? '—' }}</strong>
                            </td>
                            <td>
                                @if($movement->variant)
                                    <span class="badge bg-secondary">{{ $movement->variant->color ?? 'Mặc định' }}</span>
                                    @if($movement->variant->rom_capacity)
                                        <span class="badge bg-dark">{{ $movement->variant->rom_capacity }}</span>
                                    @endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeInfo = $types[$movement->type] ?? ['label' => $movement->type, 'bg' => 'bg-secondary text-white'];
                                @endphp
                                <span class="badge {{ $typeInfo['bg'] }} px-2 py-1.5 rounded-pill">{{ $typeInfo['label'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($movement->quantity_change > 0)
                                    <span class="qty-change-badge text-success-custom">+{{ $movement->quantity_change }}</span>
                                @else
                                    <span class="qty-change-badge text-danger-custom">{{ $movement->quantity_change }}</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $movement->before_stock }}</td>
                            <td class="text-center fw-bold">{{ $movement->after_stock }}</td>
                            <td>{{ $movement->creator->full_name ?? ($movement->creator->name ?? 'Hệ thống') }}</td>
                            <td>
                                <span class="text-muted">{{ $movement->note ?? '—' }}</span>
                                @if($movement->order_id)
                                    @php
                                        $orderExists = \App\Models\Order::where('order_id', $movement->order_id)->exists();
                                    @endphp
                                    @if($orderExists)
                                        <a href="{{ route('admin.orders.show', $movement->order_id) }}" class="badge bg-light text-primary border border-primary-subtle text-decoration-none ms-1">
                                            Đơn #{{ $movement->order_id }}
                                        </a>
                                    @else
                                        <span class="badge bg-light text-muted border border-slate-200 ms-1 cursor-not-allowed" title="Đơn hàng mẫu (Không tồn tại thực tế)">
                                            Đơn #{{ $movement->order_id }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                {{ $movement->created_at ? $movement->created_at->format('d/m/Y H:i') : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="bi bi-clock-history d-block"></i>
                                    <p class="mb-0">Chưa ghi nhận biến động kho nào phù hợp với bộ lọc.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($movements, 'links') && $movements->lastPage() > 1)
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $movements->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>{{ $movements->currentPage() }}</span> / {{ $movements->lastPage() }}
                </p>
                
                <ul class="pagination">
                    @if ($movements->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $movements->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif

                    @foreach ($movements->getUrlRange(1, $movements->lastPage()) as $page => $url)
                        @if ($page == $movements->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @elseif ($page === 1 || $page === $movements->lastPage() || abs($page - $movements->currentPage()) <= 1)
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @elseif (($page === 2 && $movements->currentPage() > 3) || ($page === $movements->lastPage() - 1 && $movements->currentPage() < $movements->lastPage() - 2))
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                    @endforeach

                    @if ($movements->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $movements->nextPageUrl() }}" rel="next">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </nav>
        @elseif(method_exists($movements, 'links'))
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $movements->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>1</span> / 1
                </p>
            </nav>
        @endif
    </div>
</div>
@endsection
