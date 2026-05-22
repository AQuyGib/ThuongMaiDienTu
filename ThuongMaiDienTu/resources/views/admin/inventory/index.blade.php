@extends('admin.layouts.master')

@section('title', 'Quản Lý Kho')

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
        background:linear-gradient(135deg,#fff 0%,#f8fbff 100%);
        border:1px solid #e2e8f0;
        border-radius:26px;
        padding:22px 24px;
        box-shadow:0 12px 30px rgba(15,23,42,.05);
    }
    .hero-title{
        font-size:1.5rem;
        font-weight:800;
        margin:0;
        color:#0f172a;
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }
    .hero-title i{color:#2563eb}
    .hero-desc{
        color:#64748b;
        margin-top:4px;
    }
    .badge-count{
        background:#2563eb;
        color:#fff;
        font-size:.75rem;
        font-weight:800;
        padding:5px 10px;
        border-radius:999px;
    }
    .stat-card{
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius:24px;
        padding:22px 24px;
        transition:all .25s;
        box-shadow:0 10px 30px rgba(15,23,42,.05);
        min-height:116px;
    }
    .stat-card:hover{
        transform:translateY(-2px);
        box-shadow:0 16px 34px rgba(15,23,42,.08);
    }
    .stat-card .stat-icon{
        width:48px;
        height:48px;
        border-radius:16px;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.05rem;
        background:#fff;
        box-shadow:0 8px 18px rgba(15,23,42,.05);
    }
    .stat-card .stat-value{
        font-size:1.7rem;
        font-weight:800;
        line-height:1;
        color:#111827;
    }
    .stat-card .stat-label{
        font-size:.74rem;
        color:#94a3b8;
        margin-top:4px;
        text-transform:uppercase;
        letter-spacing:.12em;
        font-weight:700;
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
    .search-box{
        position:relative;
        max-width:320px;
        min-width:240px;
    }
    .search-box input{
        background:#fff;
        border:1px solid #dbe3ee;
        color:#0f172a;
        border-radius:14px;
        padding:11px 14px 11px 40px;
        width:100%;
        font-size:.9rem;
        box-shadow:0 4px 16px rgba(15,23,42,.03);
    }
    .search-box input:focus{
        outline:none;
        border-color:#2563eb;
        box-shadow:0 0 0 4px rgba(37,99,235,.12);
    }
    .search-box i{
        position:absolute;
        left:14px;
        top:50%;
        transform:translateY(-50%);
        color:#94a3b8;
    }
    .filter-btn{
        padding:8px 16px;
        border-radius:999px;
        font-size:.85rem;
        font-weight:800;
        border:1px solid #dbe3ee;
        background:#fff;
        color:#475569;
        cursor:pointer;
        transition:all .2s;
        text-decoration:none;
    }
    .filter-btn:hover,.filter-btn.active{
        background:#2563eb;
        color:#fff;
        border-color:#2563eb;
        box-shadow:0 8px 18px rgba(37,99,235,.18);
    }
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
    .pagination .page-link{
        background:#fff;
        border:1px solid #dbe3ee;
        color:#475569;
        border-radius:10px!important;
        margin:0 3px;
        font-size:.85rem;
        padding:7px 13px;
    }
    .pagination .page-link:hover,.pagination .page-item.active .page-link{
        background:#2563eb;
        border-color:#2563eb;
        color:#fff;
    }
    .form-select-sm{
        background:#fff;
        border:1px solid #dbe3ee;
        color:#0f172a;
        border-radius:12px;
        padding:7px 10px;
        font-size:.85rem;
    }
    .form-select-sm:focus{
        border-color:#2563eb;
        box-shadow:0 0 0 4px rgba(37,99,235,.12);
    }
    .form-select-sm option{background:#fff;color:#0f172a}
    .section-title{
        font-size:1.05rem;
        font-weight:800;
        color:#0f172a;
        margin:0;
    }
    @keyframes fadeInUp{
        from{opacity:0;transform:translateY(16px)}
        to{opacity:1;transform:translateY(0)}
    }
    .animate-in{animation:fadeInUp .35s ease forwards}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @include('admin.partials.inventory-nav')

    <div class="hero-card mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="hero-title"><i class="bi bi-warehouse"></i> Quản Lý Kho <span class="badge-count">{{ ($totalInStock + $totalSold + $totalDefective) }} IMEI</span></h1>
                <div class="hero-desc">Theo dõi tình trạng hàng trong kho, IMEI/Serial và thống kê theo sản phẩm.</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4" id="flash-alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row g-3 mb-4 justify-content-center mx-auto" style="max-width: 1080px;">
        <div class="col-12 col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(59,130,246,.12);color:#2563eb;"><i class="bi bi-grid-1x2-fill"></i></div>
                <div><div class="stat-value">{{ $totalProducts ?? 0 }}</div><div class="stat-label">Tổng Sản Phẩm</div></div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(34,197,94,.12);color:#16a34a;"><i class="bi bi-box-seam-fill"></i></div>
                <div><div class="stat-value">{{ $totalInStock }}</div><div class="stat-label">Còn Trong Kho</div></div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(37,99,235,.12);color:#2563eb;"><i class="bi bi-bag-check-fill"></i></div>
                <div><div class="stat-value">{{ $totalSold }}</div><div class="stat-label">Đã Bán</div></div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="stat-card d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(239,68,68,.12);color:#dc2626;"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <div><div class="stat-value">{{ $totalDefective }}</div><div class="stat-label">Lỗi / Bảo Hành</div></div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-4 flex-wrap">
        <a href="{{ route('admin.inventory.index') }}" class="filter-btn {{ !request('status') ? 'active' : '' }}">Tất cả</a>
        <a href="{{ route('admin.inventory.index', ['status' => 'In_Stock']) }}" class="filter-btn {{ request('status') == 'In_Stock' ? 'active' : '' }}">Còn hàng</a>
        <a href="{{ route('admin.inventory.index', ['status' => 'Sold']) }}" class="filter-btn {{ request('status') == 'Sold' ? 'active' : '' }}">Đã bán</a>
        <a href="{{ route('admin.inventory.index', ['status' => 'Defective']) }}" class="filter-btn {{ request('status') == 'Defective' ? 'active' : '' }}">Lỗi</a>
    </div>

    <div class="table-card animate-in mb-4">
        <div class="table-card-header">
            <h5><i class="bi bi-list-ul me-2 text-primary"></i>Danh Sách IMEI</h5>
            <form method="GET" action="{{ route('admin.inventory.index') }}" class="search-box mb-0">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <i class="bi bi-search"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo IMEI, sản phẩm...">
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width:90px;">#</th>
                        <th>IMEI/Serial</th>
                        <th>Sản Phẩm</th>
                        <th>Biến Thể</th>
                        <th>NCC (Phiếu Nhập)</th>
                        <th>Vị Trí Kho</th>
                        <th class="text-center">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><span class="id-badge">{{ $items->firstItem() + $loop->index }}</span></td>
                            <td><strong style="font-family:monospace;letter-spacing:1px;">{{ $item->imei_serial }}</strong></td>
                            <td>{{ $item->variant->product->name ?? '—' }}</td>
                            <td>{{ $item->variant ? ($item->variant->color ?? '') . ($item->variant->rom_capacity ? ' - '.$item->variant->rom_capacity : '') : '—' }}</td>
                            <td>
                                @if($item->purchaseOrder && $item->purchaseOrder->supplier)
                                    {{ $item->purchaseOrder->supplier->name }}
                                    <small class="text-muted">(PO-{{ str_pad($item->po_id, 5, '0', STR_PAD_LEFT) }})</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $item->warehouse_loc ?? '—' }}</td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('admin.inventory.updateStatus', $item->item_id) }}" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="form-select-sm" onchange="this.form.submit()">
                                        <option value="In_Stock" {{ $item->status == 'In_Stock' ? 'selected' : '' }}>Còn hàng</option>
                                        <option value="Sold" {{ $item->status == 'Sold' ? 'selected' : '' }}>Đã bán</option>
                                        <option value="Defective" {{ $item->status == 'Defective' ? 'selected' : '' }}>Lỗi</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-inbox d-block"></i>
                                    <p class="mb-0">Không tìm thấy IMEI nào. Hãy tạo phiếu nhập kho trước!</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($items, 'links'))
            <div class="d-flex justify-content-center py-3">{{ $items->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>

    <div class="table-card animate-in">
        <div class="table-card-header">
            <h5><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Thống Kê Theo Sản Phẩm</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width:90px;">#</th>
                        <th>Sản Phẩm</th>
                        <th class="text-center">Biến Thể</th>
                        <th class="text-center">Còn Trong Kho</th>
                        <th class="text-center">Đã Bán</th>
                        <th class="text-center">Lỗi / Bảo Hành</th>
                        <th class="text-center">Tổng IMEI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productStats as $index => $product)
                        <tr>
                            <td><span class="id-badge">{{ $index + 1 }}</span></td>
                            <td><strong>{{ $product->name }}</strong></td>
                            <td class="text-center">{{ $product->variant_count }}</td>
                            <td class="text-center">{{ $product->in_stock_count }}</td>
                            <td class="text-center">{{ $product->sold_count }}</td>
                            <td class="text-center">{{ $product->defective_count }}</td>
                            <td class="text-center"><span class="id-badge">{{ $product->total_items }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-graph-up d-block"></i>
                                    <p class="mb-0">Chưa có dữ liệu thống kê theo sản phẩm.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection