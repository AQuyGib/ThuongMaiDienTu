@extends('admin.layouts.master')

@section('title', 'Cảnh Báo Tồn Kho')

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
        background:linear-gradient(135deg,#fff 0%,#fff5f5 100%);
        border:1px solid #fee2e2;
        border-radius:26px;
        padding:22px 24px;
        box-shadow:0 12px 30px rgba(220,38,38,.03);
    }
    .hero-title{
        font-size:1.5rem;
        font-weight:800;
        margin:0;
        color:#991b1b;
        display:flex;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
    }
    .hero-title i{color:#dc2626}
    .hero-desc{
        color:#7f1d1d;
        opacity:0.8;
        margin-top:4px;
    }
    .badge-count{
        background:#dc2626;
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
    .table-custom tbody tr:hover{background:#fff8f8}
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
    .btn-purchase{
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 10px !important;
        padding: 6px 14px !important;
        font-size: .82rem !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: all .2s !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.15) !important;
    }
    .btn-purchase:hover{
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        color: #fff !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 14px rgba(16, 185, 129, 0.25) !important;
    }
    .btn-purchase-top {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 10px 18px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.2) !important;
        transition: all 0.2s ease !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .btn-purchase-top:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 10px 24px rgba(16, 185, 129, 0.28) !important;
        color: #ffffff !important;
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

    <div class="hero-card mb-4 animate-in">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="hero-title">
                    <i class="bi bi-exclamation-octagon-fill"></i> 
                    Cảnh Báo Tồn Kho An Toàn 
                    <span class="badge-count">{{ $lowStockVariants->count() + $lowStockProductsWithoutVariants->count() }} Mặt hàng</span>
                </h1>
                <div class="hero-desc">Danh sách các sản phẩm và biến thể có số lượng tồn kho thực tế bằng hoặc thấp hơn mức tồn kho an toàn đã thiết lập.</div>
            </div>
            <div>
                <a href="{{ route('admin.purchase-orders.create') }}" class="btn-purchase-top">
                    <i class="bi bi-plus-circle"></i> Đi Thu Mua / Nhập Hàng
                </a>
            </div>
        </div>
    </div>

    <div class="table-card animate-in mb-4">
        <div class="table-card-header">
            <h5><i class="bi bi-list-stars me-2 text-danger"></i>Danh Sách Biến Thể Cần Nhập</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th style="width:70px;">#</th>
                        <th>Sản Phẩm</th>
                        <th>Biến Thể</th>
                        <th>Danh Mục</th>
                        <th class="text-center">Tồn Thực Tế</th>
                        <th class="text-center">Hạn Mức An Toàn</th>
                        <th class="text-center">Trạng Thái</th>
                        <th class="text-center" style="width:150px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 1; @endphp
                    @forelse($lowStockVariants as $variant)
                        <tr style="background-color: rgba(239, 68, 68, 0.03);">
                            <td><span class="id-badge">{{ $counter++ }}</span></td>
                            <td>
                                <a href="{{ route('admin.products.show', $variant->product->product_id) }}" class="text-decoration-none" style="font-weight: 700; color: #1e293b;">
                                    {{ $variant->product->name ?? '—' }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $variant->color ?? 'Mặc định' }}</span>
                                @if($variant->rom_capacity)
                                    <span class="badge bg-dark">{{ $variant->rom_capacity }}</span>
                                @endif
                            </td>
                            <td>{{ $variant->product->category->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="text-danger" style="font-weight: 800; font-size: 1.1rem;">
                                    {{ $variant->in_stock_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="id-badge bg-light text-dark">{{ $variant->safe_stock ?? 5 }}</span>
                            </td>
                            <td class="text-center">
                                @if($variant->in_stock_count == 0)
                                    <span class="badge bg-danger">Hết hàng</span>
                                @else
                                    <span class="badge bg-warning text-dark">Sắp hết hàng</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.purchase-orders.create', ['variant_id' => $variant->variant_id]) }}" class="btn-purchase">
                                    <i class="bi bi-cart-plus-fill"></i> Nhập hàng
                                </a>
                            </td>
                        </tr>
                    @empty
                        {{-- Sẽ hiển thị sản phẩm không biến thể ở dưới nếu bảng trống --}}
                    @endforelse

                    @forelse($lowStockProductsWithoutVariants as $product)
                        <tr style="background-color: rgba(239, 68, 68, 0.03);">
                            <td><span class="id-badge">{{ $counter++ }}</span></td>
                            <td>
                                <a href="{{ route('admin.products.show', $product->product_id) }}" class="text-decoration-none" style="font-weight: 700; color: #1e293b;">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td><span class="text-muted">—</span></td>
                            <td>{{ $product->category->name ?? '—' }}</td>
                            <td class="text-center">
                                <span class="text-danger" style="font-weight: 800; font-size: 1.1rem;">0</span>
                            </td>
                            <td class="text-center">
                                <span class="id-badge bg-light text-dark">{{ $product->safe_stock ?? 5 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger">Hết hàng</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.purchase-orders.create', ['product_id' => $product->product_id]) }}" class="btn-purchase">
                                    <i class="bi bi-cart-plus-fill"></i> Nhập hàng
                                </a>
                            </td>
                        </tr>
                    @empty
                        @if($lowStockVariants->isEmpty())
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="bi bi-check-circle text-success" style="opacity: 1; font-size: 3rem;"></i>
                                        <p class="mb-0 mt-2" style="font-weight:600; color:#1e293b;">Kho hàng an toàn!</p>
                                        <small class="text-muted">Không có sản phẩm hay biến thể nào ở dưới mức tồn kho tối thiểu.</small>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
