@extends('admin.layouts.master')

@section('title', 'Quản lý sản phẩm')

@push('styles')
<style>
    .page-shell {
        background: linear-gradient(180deg, rgba(17, 24, 39, 0.03) 0%, rgba(17, 24, 39, 0) 220px);
    }

    .page-hero,
    .glass-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .page-hero {
        border-radius: 24px;
    }

    .soft-input {
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        transition: all .2s ease;
    }

    .soft-input:focus {
        background: #fff;
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
    }

    .table-modern th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #64748b;
        border-bottom: 1px solid #e5e7eb !important;
        white-space: nowrap;
    }

    .table-modern td {
        border-bottom: 1px solid #eef2f7 !important;
        vertical-align: middle;
    }

    .badge-soft { background: #eef2ff; color: #4338ca; }
    .badge-category { background: #ecfeff; color: #0f766e; }

    .btn-primary-soft {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: #fff;
        border: none;
        box-shadow: 0 10px 20px rgba(99, 102, 241, .18);
    }

    .btn-primary-soft:hover { color: #fff; transform: translateY(-1px); }

    .icon-btn {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #64748b;
        transition: all .2s ease;
        text-decoration: none;
    }

    .icon-btn:hover {
        transform: translateY(-1px);
        border-color: #c7d2fe;
        color: #4338ca;
        background: #eef2ff;
    }

    .icon-btn.danger:hover {
        border-color: #fecaca;
        color: #b91c1c;
        background: #fef2f2;
    }

    .modal-panel {
        max-height: calc(100vh - 2rem);
        overflow: auto;
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
        <div class="d-flex flex-column gap-4 border-bottom border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900 mb-1">Danh sách sản phẩm</h2>
                <p class="text-sm text-slate-500 mb-0">Tìm kiếm nhanh, xem chi tiết và thao tác ngay trên một màn hình.</p>
            </div>

            <form method="GET" action="{{ route('admin.products.index') }}" class="position-relative" style="width:min(100%,420px)">
                <i class="fa-solid fa-magnifying-glass position-absolute top-50 start-0 translate-middle-y ms-3 text-slate-400"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm kiếm sản phẩm..." class="soft-input w-100 rounded-2xl py-3 ps-5 pe-5 text-sm outline-none" style="padding-left:2.75rem;padding-right:6.5rem;">
                @if(!empty($search))
                    <a href="{{ route('admin.products.index') }}" class="position-absolute top-50 end-0 translate-middle-y me-5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs fw-semibold text-slate-600 text-decoration-none">Xóa</a>
                @endif
                <button type="submit" class="position-absolute top-50 end-0 translate-middle-y me-1 btn btn-primary btn-sm rounded-xl" style="font-size:.8rem;">Tìm</button>
            </form>
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
                            <td class="px-5 py-4"><span class="badge border border-slate-200 bg-slate-50 text-slate-600">#{{ $product->product_id }}</span></td>
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
                                            data-seo="{{ e($product->seo_description ?? '') }}">
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

        <div class="border-top border-slate-200 px-4 py-3">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

{{-- Add Modal --}}
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
                            <input type="number" name="base_price" class="form-control" min="0" required>
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

{{-- Edit Modal --}}
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-panel">
        <div class="modal-content">
            <form id="editProductForm" method="POST">
                @csrf
                @method('PUT')
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
                            <input type="number" name="base_price" id="editProductPrice" class="form-control" min="0" required>
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

    function openEditModal(id, name, categoryId, price, seo) {
        document.getElementById('editProductName').value = name || '';
        document.getElementById('editProductCategory').value = categoryId || '';
        document.getElementById('editProductPrice').value = price || '';
        document.getElementById('editProductSeo').value = seo || '';
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

    document.querySelectorAll('.js-edit-product').forEach(button => {
        button.addEventListener('click', function () {
            openEditModal(
                this.dataset.id,
                this.dataset.name,
                this.dataset.categoryId,
                this.dataset.price,
                this.dataset.seo
            );
        });
    });

    document.querySelectorAll('.js-delete-product').forEach(button => {
        button.addEventListener('click', function () {
            confirmDelete(this.dataset.id, this.dataset.name);
        });
    });
</script>
@endsection
