@extends('admin.layouts.master')

@section('title', 'Quản lý sản phẩm')

@push('styles')
<style>
    :root {
        --accent: #4f46e5;
        --accent-hover: #4338ca;
        --accent-soft: rgba(79, 70, 229, 0.08);
        --accent-glow: rgba(79, 70, 229, 0.16);
        --border: #e2e8f0;
        --surface: #ffffff;
        --surface-soft: #f8fafc;
        --text-primary: #0f172a;
        --text-secondary: #64748b;
    }

    .page-shell {
        background: linear-gradient(180deg, rgba(79, 70, 229, 0.02) 0%, rgba(79, 70, 229, 0) 260px);
        padding: 24px 0;
    }

    .page-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid var(--border);
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        padding: 28px;
        margin-bottom: 24px;
        transition: all 0.3s ease;
    }

    .glass-card {
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        transition: all 0.25s ease;
    }

    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.07);
        border-color: rgba(79, 70, 229, 0.2);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
        transition: all 0.25s ease;
    }

    .glass-card:hover .stat-icon {
        transform: scale(1.05);
    }

    .soft-input {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 50px !important;
        height: 52px;
        font-size: 0.92rem;
        color: var(--text-primary);
        transition: all 0.2s ease;
        outline: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.01);
    }

    .soft-input:focus {
        background: #ffffff;
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .table-modern {
        margin: 0;
        width: 100%;
    }

    .table-modern th {
        background: #f8fafc;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border) !important;
        padding: 16px 20px;
        white-space: nowrap;
        vertical-align: middle;
    }

    .table-modern td {
        padding: 16px 20px;
        border-bottom: 1px solid #eef2f7 !important;
        vertical-align: middle;
        font-size: .92rem;
        color: var(--text-primary);
        transition: background-color 0.2s ease;
    }

    .table-modern tbody tr:hover td {
        background-color: #f8fafc;
    }

    .id-badge {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 4px 10px;
        font-size: 0.8rem;
        font-weight: 800;
        color: #475569;
    }

    .badge-soft {
        background: rgba(79, 70, 229, 0.08);
        color: #4f46e5;
        border: 1px solid rgba(79, 70, 229, 0.12);
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .badge-category {
        background: rgba(14, 116, 144, 0.08);
        color: #0e7490;
        border: 1px solid rgba(14, 116, 144, 0.12);
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .btn-primary-soft {
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        color: #ffffff;
        border: none;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25);
        font-weight: 700;
        font-size: 0.875rem;
        border-radius: 12px;
        padding: 10px 20px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-primary-soft:hover {
        background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .btn-outline-secondary {
        border-radius: 12px;
        padding: 10px 18px;
        font-weight: 600;
        font-size: 0.875rem;
        border: 1px solid #cbd5e1;
        color: #475569;
        background: #ffffff;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
    }

    .btn-outline-secondary:hover {
        background: #f8fafc;
        color: #0f172a;
        border-color: #94a3b8;
    }

    .icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        color: #64748b;
        transition: all 0.2s ease;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .icon-btn:hover {
        transform: translateY(-1px);
        border-color: rgba(79, 70, 229, 0.25);
        color: #4f46e5;
        background: rgba(79, 70, 229, 0.04);
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.08);
    }

    .icon-btn.danger:hover {
        border-color: rgba(220, 38, 38, 0.25);
        color: #dc2626;
        background: rgba(220, 38, 38, 0.04);
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.08);
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15);
        overflow: hidden;
    }

    .modal-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 28px;
    }

    .modal-title {
        font-weight: 800;
        color: #0f172a;
        font-size: 1.15rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-body {
        padding: 28px;
    }

    .modal-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 16px 28px;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #475569;
        margin-bottom: 6px;
    }

    .form-control, .form-select {
        border-radius: 12px;
        padding: 10px 14px;
        font-size: 0.9rem;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        color: var(--text-primary);
        transition: all 0.2s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
        outline: none;
    }

    /* Custom pagination wrapper to force horizontal layout */
    .custom-pagination-nav {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        padding: 16px 24px !important;
        border-top: 1px solid var(--border) !important;
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
<div class="page-shell space-y-6">
    <div class="page-hero p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700">
                    <i class="fa-solid fa-box-open"></i>
                    Sản phẩm
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900 sm:text-3xl">Quản lý sản phẩm</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">
                    Đồng bộ phong cách với giao diện quản trị hiện đại, rõ ràng và dễ thao tác hơn.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.products.import.form') }}" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-upload me-1"></i> Import Excel
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('admin.products.export') }}'">
                    <i class="fa-solid fa-download me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-primary-soft" onclick="openModal('addProductModal')">
                    <i class="fa-solid fa-plus me-1"></i> Thêm sản phẩm
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="glass-card border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="glass-card border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            <div class="fw-semibold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Vui lòng kiểm tra lại</div>
            <div class="small">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-4">
            <div class="glass-card p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Tổng sản phẩm</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 mb-0">{{ $totalProducts ?? 0 }}</p>
                    </div>
                    <div class="d-flex h-12 w-12 align-items-center justify-content-center rounded-2xl bg-indigo-50 text-indigo-600" style="width:48px;height:48px;">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Tổng danh mục</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 mb-0">{{ $totalCategories ?? 0 }}</p>
                    </div>
                    <div class="d-flex h-12 w-12 align-items-center justify-content-center rounded-2xl bg-cyan-50 text-cyan-600" style="width:48px;height:48px;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card p-5">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Tổng biến thể</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900 mb-0">{{ $totalVariants ?? 0 }}</p>
                    </div>
                    <div class="d-flex h-12 w-12 align-items-center justify-content-center rounded-2xl bg-amber-50 text-amber-600" style="width:48px;height:48px;">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="d-flex flex-column align-items-center text-center gap-4 border-bottom border-slate-200 p-5 py-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900 mb-1">Danh sách sản phẩm</h2>
                <p class="text-sm text-slate-500 mb-0">Tìm kiếm nhanh, xem chi tiết và thao tác ngay trên một màn hình.</p>
            </div>

            <div class="d-flex justify-content-center w-100">
                <form method="GET" action="{{ route('admin.products.index') }}" class="position-relative" style="width: 100%; max-width: 500px;">
                    <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-4 text-slate-400"></i>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm kiếm sản phẩm..." class="soft-input w-100 rounded-full py-3 ps-5 pe-5 text-sm outline-none" style="padding-left: 3rem !important; padding-right: 7.5rem !important; border-radius: 50px !important; height: 52px; border: 1px solid #cbd5e1; background: #f8fafc; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);">
                    
                    @if(!empty($search))
                        <a href="{{ route('admin.products.index') }}" class="position-absolute top-50 translate-middle-y text-slate-400 text-decoration-none hover:text-slate-600" style="right: 90px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Xóa</a>
                    @endif
                    
                    <button type="submit" class="position-absolute top-50 end-0 translate-middle-y me-1.5 btn btn-primary rounded-full px-4" style="height: 42px; border-radius: 50px !important; font-size: 0.9rem; font-weight: 700; background: #2563eb; border-color: #2563eb; display: inline-flex; align-items: center; justify-content: center; min-width: 76px; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.15);">Tìm</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-modern mb-0" id="productTable">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4">#</th>
                        <th class="px-5 py-4">Tên sản phẩm</th>
                        <th class="px-5 py-4">Danh mục</th>
                        <th class="px-5 py-4">Biến thể</th>
                        <th class="px-5 py-4">Giá bán</th>
                        <th class="px-5 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($products as $product)
                        <tr>
                            <td class="px-5 py-4"><span class="id-badge">#{{ $product->product_id }}</span></td>
                            <td class="px-5 py-4">
                                <div class="space-y-1">
                                    <div class="fw-semibold text-slate-900">{{ $product->name }}</div>
                                    @if($product->seo_description)
                                        <div class="small text-slate-500">{{ \Illuminate\Support\Str::limit($product->seo_description, 70) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4"><span class="badge badge-category">{{ $product->category->name ?? '—' }}</span></td>
                            <td class="px-5 py-4"><span class="badge badge-soft">{{ $product->variants_count ?? 0 }}</span></td>
                            <td class="px-5 py-4 text-slate-700">{{ number_format($product->base_price, 0, ',', '.') }} đ</td>
                            <td class="px-5 py-4">
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="{{ route('admin.products.show', $product->product_id) }}" class="icon-btn" title="Chi tiết">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <button type="button"
                                            class="icon-btn js-edit-product"
                                            title="Sửa"
                                            data-id="{{ $product->product_id }}"
                                            data-name="{{ e($product->name) }}"
                                            data-category-id="{{ $product->category_id }}"
                                            data-price="{{ $product->base_price }}"
                                            data-seo="{{ e($product->seo_description ?? '') }}"
                                            data-version="{{ $product->version }}">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button"
                                            class="icon-btn danger js-delete-product"
                                            title="Xóa"
                                            data-id="{{ $product->product_id }}"
                                            data-name="{{ e($product->name) }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-slate-500">Chưa có sản phẩm nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($products, 'links') && $products->lastPage() > 1)
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $products->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>{{ $products->currentPage() }}</span> / {{ $products->lastPage() }}
                </p>
                
                <ul class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($products->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $products->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                        @if ($page == $products->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @elseif ($page === 1 || $page === $products->lastPage() || abs($page - $products->currentPage()) <= 1)
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @elseif (($page === 2 && $products->currentPage() > 3) || ($page === $products->lastPage() - 1 && $products->currentPage() < $products->lastPage() - 2))
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($products->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </nav>
        @elseif(method_exists($products, 'links'))
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $products->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>1</span> / 1
                </p>
            </nav>
        @endif
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-panel">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.products.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Thêm sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" name="name" class="form-control" required maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Danh mục</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Chọn danh mục --</option>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá bán</label>
                            <input type="number" name="base_price" class="form-control" min="0" max="999999999" oninput="if(this.value < 0) this.value = 0; if(this.value > 999999999) this.value = 999999999;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả SEO</label>
                            <input type="text" name="seo_description" class="form-control" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-soft">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-panel">
        <div class="modal-content">
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="version" id="editProductVersion">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa sản phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tên sản phẩm</label>
                            <input type="text" name="name" id="editProductName" class="form-control" required maxlength="150">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Danh mục</label>
                            <select name="category_id" id="editProductCategory" class="form-select" required>
                                @foreach($allCategories as $category)
                                    <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Giá bán</label>
                            <input type="number" name="base_price" id="editProductPrice" class="form-control" min="0" max="999999999" oninput="if(this.value < 0) this.value = 0; if(this.value > 999999999) this.value = 999999999;" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mô tả SEO</label>
                            <input type="text" name="seo_description" id="editProductSeo" class="form-control" maxlength="255">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary-soft">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteProductForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>

<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        bootstrap.Modal.getOrCreateInstance(el).show();
    }

    function openEditModal(id, name, categoryId, price, seo, version) {
        document.getElementById('editProductName').value = name || '';
        document.getElementById('editProductCategory').value = categoryId || '';
        document.getElementById('editProductPrice').value = price || '';
        document.getElementById('editProductSeo').value = seo || '';
        document.getElementById('editProductVersion').value = version || 1;
        document.getElementById('editProductForm').action = "{{ url('admin/products') }}/" + id;
        openModal('editProductModal');
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Xác nhận xóa sản phẩm',
            html: 'Bạn có chắc muốn xóa sản phẩm <strong>' + name + '</strong> không?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy',
            reverseButtons: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            background: '#fff',
            customClass: {
                popup: 'rounded-4 shadow-lg',
                confirmButton: 'px-4 py-2',
                cancelButton: 'px-4 py-2'
            }
        }).then((result) => {
            if (!result.isConfirmed) return;
            const form = document.getElementById('deleteProductForm');
            form.action = "{{ url('admin/products') }}/" + id;
            form.submit();
        });
    }

    var productTable = document.getElementById('productTable');
    if (productTable) {
        productTable.addEventListener('click', function (e) {
            var editBtn = e.target.closest('.js-edit-product');
            if (editBtn) {
                openEditModal(
                    editBtn.dataset.id,
                    editBtn.dataset.name,
                    editBtn.dataset.categoryId,
                    editBtn.dataset.price,
                    editBtn.dataset.seo,
                    editBtn.dataset.version
                );
                return;
            }

            var deleteBtn = e.target.closest('.js-delete-product');
            if (deleteBtn) {
                confirmDelete(deleteBtn.dataset.id, deleteBtn.dataset.name);
                return;
            }
        });
    }
</script>
@endsection