@extends('admin.layouts.master')

@section('title', 'Quản lý sản phẩm')

@push('styles')
<style>
    .page-shell {
        background: linear-gradient(180deg, rgba(17, 24, 39, 0.03) 0%, rgba(17, 24, 39, 0) 220px);
    }

    .page-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.86);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(229, 231, 235, 0.9);
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
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
    }

    .table-modern td {
        border-bottom: 1px solid #eef2f7 !important;
        vertical-align: middle;
    }

    .badge-soft {
        background: #eef2ff;
        color: #4338ca;
    }

    .badge-category {
        background: #ecfeff;
        color: #0f766e;
    }

    .btn-primary-soft {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        border: none;
        box-shadow: 0 10px 20px rgba(99, 102, 241, .18);
    }

    .btn-primary-soft:hover {
        color: white;
        transform: translateY(-1px);
    }

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

    .modal-backdrop-custom {
        background: rgba(15, 23, 42, .55);
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
                    Danh mục sản phẩm
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900 sm:text-3xl">Quản lý sản phẩm</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">
                    Đồng bộ phong cách với giao diện quản trị hiện đại, rõ ràng và dễ thao tác hơn.
                </p>
            </div>

            <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700" onclick="openModal('addProductModal')">
                <i class="fa-solid fa-plus"></i>
                Thêm sản phẩm
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="glass-card border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="glass-card border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            <i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="glass-card border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
            <div class="font-semibold mb-2"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Vui lòng kiểm tra lại</div>
            <div class="space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Tổng sản phẩm</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalProducts ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
        </div>
        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Tổng danh mục</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalCategories ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-600">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
        </div>
        <div class="glass-card p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Tổng biến thể</p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalVariants ?? 0 }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-sliders"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách sản phẩm</h2>
                <p class="text-sm text-slate-500">Tìm kiếm nhanh, xem chi tiết và thao tác ngay trên một màn hình.</p>
            </div>
            <div class="relative w-full lg:max-w-md">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Tìm kiếm sản phẩm..." class="soft-input w-full rounded-2xl py-3 pl-11 pr-4 text-sm outline-none">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm table-modern" id="productTable">
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
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-xl border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">#{{ $product->product_id }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="space-y-1">
                                    <div class="font-semibold text-slate-900">{{ $product->name }}</div>
                                    @if($product->seo_description)
                                        <div class="max-w-xl text-xs text-slate-500">{{ Str::limit($product->seo_description, 70) }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if($product->category)
                                    <span class="badge-category inline-flex rounded-full px-3 py-1 text-xs font-semibold">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.products.show', $product->product_id) }}" class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 transition hover:bg-amber-100">
                                    {{ $product->variants_count ?? 0 }} biến thể
                                </a>
                            </td>
                            <td class="px-5 py-4 font-semibold text-emerald-600">{{ number_format($product->base_price, 0, ',', '.') }}₫</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.products.show', $product->product_id) }}" class="icon-btn" title="Chi tiết">
                                        <i class="fa-regular fa-eye"></i>
                                    </a>
                                    <button type="button" class="icon-btn" title="Sửa" onclick="openEditModal({{ $product->product_id }}, @js($product->name), {{ $product->category_id ?? 'null' }}, {{ $product->base_price }}, @js($product->seo_description ?? ''), @js($product->brand ?? ''))">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="icon-btn danger" title="Xóa" onclick="confirmDelete({{ $product->product_id }}, @js($product->name))">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center text-slate-500">
                                <i class="fa-regular fa-inbox mb-3 text-4xl text-slate-300"></i>
                                <div class="font-medium">Chưa có sản phẩm nào.</div>
                                <div class="text-sm">Hãy tạo sản phẩm đầu tiên để bắt đầu quản lý.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($products, 'links'))
            <div class="border-t border-slate-200 px-5 py-4">
                <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white px-5 py-4 shadow-sm lg:flex-row lg:items-center lg:justify-between">
                    <div class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm">
                        <span class="font-semibold uppercase tracking-wide text-slate-900">Kết quả: {{ $products->total() }}</span>
                        <span class="h-5 w-px bg-slate-200"></span>
                        <span class="font-semibold uppercase tracking-wide text-slate-900">Trang {{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-2 shadow-sm">
                        @if($products->onFirstPage())
                            <span class="inline-flex h-10 min-w-[92px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold uppercase tracking-wide text-slate-300">Trước</span>
                        @else
                            <a href="{{ $products->previousPageUrl() }}" class="inline-flex h-10 min-w-[92px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold uppercase tracking-wide text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Trước</a>
                        @endif

                        @if($products->hasMorePages())
                            <a href="{{ $products->nextPageUrl() }}" class="inline-flex h-10 min-w-[92px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold uppercase tracking-wide text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Tiếp</a>
                        @else
                            <span class="inline-flex h-10 min-w-[92px] items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold uppercase tracking-wide text-slate-300">Tiếp</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@php
    $categoryOptions = $allCategories->map(fn($cat) => ['id' => $cat->category_id, 'name' => $cat->name])->values();
@endphp

<div id="addProductModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 modal-backdrop-custom" onclick="closeModal('addProductModal')"></div>
    <div class="relative w-full max-w-3xl rounded-3xl bg-white shadow-2xl modal-panel">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Thêm sản phẩm mới</h3>
                    <p class="mt-1 text-sm text-slate-500">Nhập thông tin theo cùng một phong cách với các trang quản trị khác.</p>
                </div>
                <button type="button" class="icon-btn" onclick="closeModal('addProductModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Tên sản phẩm</label>
                    <input type="text" name="name" required maxlength="150" class="soft-input w-full rounded-2xl px-4 py-3 outline-none" placeholder="VD: iPhone 15 Pro Max">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Giá bán (₫)</label>
                    <input type="number" name="base_price" required min="0" class="soft-input w-full rounded-2xl px-4 py-3 outline-none" placeholder="VD: 29990000">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Danh mục</label>
                    <select name="category_id" required class="soft-input w-full rounded-2xl px-4 py-3 outline-none">
                        <option value="">— Chọn danh mục —</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Thương hiệu</label>
                    <input type="text" name="brand" maxlength="100" class="soft-input w-full rounded-2xl px-4 py-3 outline-none" placeholder="VD: Apple, Samsung...">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Mô tả SEO</label>
                    <input type="text" name="seo_description" maxlength="255" class="soft-input w-full rounded-2xl px-4 py-3 outline-none" placeholder="Mô tả ngắn cho SEO...">
                </div>
                <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Ảnh từ máy tính</label>
                        <input type="file" name="image_file" accept="image/*" class="soft-input w-full rounded-2xl px-4 py-3 outline-none" onchange="previewFile(this, 'addFilePreview')">
                        <img id="addFilePreview" class="mt-3 hidden max-h-48 rounded-2xl border border-slate-200 object-cover" alt="Preview">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Hoặc URL ảnh</label>
                        <input type="text" name="image_url" class="soft-input w-full rounded-2xl px-4 py-3 outline-none" placeholder="https://..." oninput="previewUrl(this, 'addUrlPreview')">
                        <img id="addUrlPreview" class="mt-3 hidden max-h-48 rounded-2xl border border-slate-200 object-cover" alt="Preview">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-slate-200 pt-5">
                <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50" onclick="closeModal('addProductModal')">Hủy</button>
                <button type="submit" class="btn-primary-soft rounded-2xl px-5 py-3 text-sm font-semibold">Thêm sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<div id="editProductModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 modal-backdrop-custom" onclick="closeModal('editProductModal')"></div>
    <div class="relative w-full max-w-3xl rounded-3xl bg-white shadow-2xl modal-panel">
        <form id="editForm" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf
            @method('PUT')
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">Cập nhật sản phẩm</h3>
                    <p class="mt-1 text-sm text-slate-500">Giữ phong cách thống nhất với phần quản trị còn lại.</p>
                </div>
                <button type="button" class="icon-btn" onclick="closeModal('editProductModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Tên sản phẩm</label>
                    <input type="text" name="name" id="editName" required maxlength="150" class="soft-input w-full rounded-2xl px-4 py-3 outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Giá bán (₫)</label>
                    <input type="number" name="base_price" id="editPrice" required min="0" class="soft-input w-full rounded-2xl px-4 py-3 outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Danh mục</label>
                    <select name="category_id" id="editCategoryId" required class="soft-input w-full rounded-2xl px-4 py-3 outline-none">
                        <option value="">— Chọn danh mục —</option>
                        @foreach($allCategories as $cat)
                            <option value="{{ $cat->category_id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Thương hiệu</label>
                    <input type="text" name="brand" id="editBrand" maxlength="100" class="soft-input w-full rounded-2xl px-4 py-3 outline-none">
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Mô tả SEO</label>
                    <input type="text" name="seo_description" id="editSeoDesc" maxlength="255" class="soft-input w-full rounded-2xl px-4 py-3 outline-none">
                </div>
                <div class="md:col-span-2 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Ảnh từ máy tính</label>
                        <input type="file" name="image_file" accept="image/*" class="soft-input w-full rounded-2xl px-4 py-3 outline-none" onchange="previewFile(this, 'editFilePreview')">
                        <img id="editFilePreview" class="mt-3 hidden max-h-48 rounded-2xl border border-slate-200 object-cover" alt="Preview">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Hoặc URL ảnh</label>
                        <input type="text" name="image_url" id="editImageUrl" class="soft-input w-full rounded-2xl px-4 py-3 outline-none" placeholder="https://..." oninput="previewUrl(this, 'editUrlPreview')">
                        <img id="editUrlPreview" class="mt-3 hidden max-h-48 rounded-2xl border border-slate-200 object-cover" alt="Preview">
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 border-t border-slate-200 pt-5">
                <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50" onclick="closeModal('editProductModal')">Hủy</button>
                <button type="submit" class="btn-primary-soft rounded-2xl px-5 py-3 text-sm font-semibold">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    function openEditModal(id, name, categoryId, price, seoDesc, brand) {
        document.getElementById('editName').value = name || '';
        document.getElementById('editCategoryId').value = categoryId || '';
        document.getElementById('editPrice').value = price || '';
        document.getElementById('editSeoDesc').value = seoDesc || '';
        document.getElementById('editBrand').value = brand || '';
        document.getElementById('editForm').action = "{{ url('admin/products') }}/" + id;
        openModal('editProductModal');
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            html: `Bạn có chắc muốn xóa sản phẩm <strong>${name}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = "{{ url('admin/products') }}/" + id;
                form.submit();
            }
        });
    }

    function filterTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#productTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(input) ? '' : 'none';
        });
    }

    function previewFile(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewUrl(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.value.trim()) {
            preview.src = input.value.trim();
            preview.classList.remove('hidden');
            preview.onerror = () => preview.classList.add('hidden');
        } else {
            preview.classList.add('hidden');
        }
    }

    document.querySelectorAll('button[type="button"][onclick^="openModal("]')?.forEach((button) => {
        button.addEventListener('click', () => {
            const match = button.getAttribute('onclick')?.match(/openModal\('([^']+)'\)/);
            if (match?.[1]) openModal(match[1]);
        });
    });
</script>
@endpush
@endsection
