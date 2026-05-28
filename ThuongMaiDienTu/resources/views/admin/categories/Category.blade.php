<<<<<<< HEAD
@extends('admin.layouts.master')

@section('title', 'Quản lý danh mục')

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
                    <i class="fa-solid fa-layer-group"></i>
                    Danh mục sản phẩm
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900 sm:text-3xl">Quản lý danh mục</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-500">
                    Giao diện được đồng bộ với trang sản phẩm để thao tác nhất quán và dễ theo dõi hơn.
                </p>
            </div>

            <button type="button" class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-200 transition hover:bg-indigo-700" onclick="openCategoryModal('addCategoryModal')">
                <i class="fa-solid fa-plus"></i>
                Thêm danh mục
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

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="relative overflow-hidden rounded-[26px] border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.05)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(79,70,229,0.12)]">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-indigo-100/50 blur-3xl"></div>
            <div class="absolute -bottom-8 -left-8 h-24 w-24 rounded-full bg-violet-100/40 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-700">Tổng</span>
                <p class="mt-4 text-sm font-medium text-slate-500">Tổng danh mục</p>
                <div class="mt-2 flex items-end gap-2">
                    <span class="text-4xl leading-none font-black tracking-tight text-slate-900">{{ $totalCategories ?? $categories->total() }}</span>
                    <span class="pb-1 text-xs font-bold uppercase tracking-[0.22em] text-slate-400">mục</span>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-500">Toàn bộ danh mục đang được quản lý trong hệ thống.</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[26px] border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.05)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(14,165,233,0.12)]">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-sky-100/50 blur-3xl"></div>
            <div class="absolute -bottom-8 -left-8 h-24 w-24 rounded-full bg-cyan-100/40 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-700">Root</span>
                <p class="mt-4 text-sm font-medium text-slate-500">Danh mục gốc</p>
                <div class="mt-2 flex items-end gap-2">
                    <span class="text-4xl leading-none font-black tracking-tight text-slate-900">{{ $rootCategories ?? 0 }}</span>
                    <span class="pb-1 text-xs font-bold uppercase tracking-[0.22em] text-slate-400">mục</span>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-500">Những nhóm cấp cao nhất trong cấu trúc danh mục.</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[26px] border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.05)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(245,158,11,0.12)]">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-amber-100/50 blur-3xl"></div>
            <div class="absolute -bottom-8 -left-8 h-24 w-24 rounded-full bg-orange-100/40 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">Sub</span>
                <p class="mt-4 text-sm font-medium text-slate-500">Danh mục con</p>
                <div class="mt-2 flex items-end gap-2">
                    <span class="text-4xl leading-none font-black tracking-tight text-slate-900">{{ $childCategories ?? 0 }}</span>
                    <span class="pb-1 text-xs font-bold uppercase tracking-[0.22em] text-slate-400">mục</span>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-500">Các danh mục nằm bên trong danh mục cha.</p>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-[26px] border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(15,23,42,0.05)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(16,185,129,0.12)]">
            <div class="absolute -right-10 -top-10 h-32 w-32 rounded-full bg-emerald-100/50 blur-3xl"></div>
            <div class="absolute -bottom-8 -left-8 h-24 w-24 rounded-full bg-lime-100/40 blur-3xl"></div>
            <div class="relative">
                <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-emerald-700">Items</span>
                <p class="mt-4 text-sm font-medium text-slate-500">Tổng sản phẩm</p>
                <div class="mt-2 flex items-end gap-2">
                    <span class="text-4xl leading-none font-black tracking-tight text-slate-900">{{ $categories->sum('products_count') ?? 0 }}</span>
                    <span class="pb-1 text-xs font-bold uppercase tracking-[0.22em] text-slate-400">sp</span>
                </div>
                <p class="mt-3 text-sm leading-6 text-slate-500">Số lượng sản phẩm được gán trong toàn bộ danh mục.</p>
            </div>
        </div>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="flex flex-col gap-4 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Danh sách danh mục</h2>
                <p class="text-sm text-slate-500">Quản lý danh mục cha, danh mục con và số lượng sản phẩm.</p>
            </div>
            <form method="GET" action="{{ route('admin.categories.index') }}" class="w-full lg:max-w-md">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input name="search" type="text" value="{{ $search ?? '' }}" placeholder="Tìm kiếm danh mục..." class="soft-input w-full rounded-2xl py-3 pl-11 pr-4 text-sm outline-none">
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm table-modern" id="categoryTable">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4">#</th>
                        <th class="px-5 py-4">Tên danh mục</th>
                        <th class="px-5 py-4">Danh mục cha</th>
                        <th class="px-5 py-4">Số SP</th>
                        <th class="px-5 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-xl border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">#{{ $category->category_id }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-slate-900">{{ $category->name }}</div>
                            </td>
                            <td class="px-5 py-4">
                                @if($category->parent)
                                    <span class="badge-category inline-flex rounded-full px-3 py-1 text-xs font-semibold">{{ $category->parent->name }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-semibold text-emerald-600">{{ $category->products_count ?? 0 }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
=======
                                    <a href="{{ route('admin.categories.translation.edit', $category->category_id) }}"
                                       class="icon-btn"
                                       title="Edit EN Translation">
                                        <i class="fa-solid fa-language"></i>
                                    </a>
>>>>>>> master
                                    <button type="button" class="icon-btn js-edit-category"
                                            data-id="{{ $category->category_id }}"
                                            data-name="{{ e($category->name) }}"
                                            data-parent-id="{{ $category->parent_id ?? '' }}"
                                            data-version="{{ $category->version }}"
                                            title="Sửa">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="icon-btn danger js-delete-category"
                                            data-id="{{ $category->category_id }}"
                                            data-name="{{ e($category->name) }}"
                                            title="Xóa">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
<<<<<<< HEAD
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center text-slate-500">
                                <i class="fa-regular fa-folder-open mb-3 text-4xl text-slate-300"></i>
                                <div class="font-medium">Chưa có danh mục nào.</div>
                                <div class="text-sm">Hãy thêm danh mục đầu tiên để bắt đầu quản lý.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($categories, 'links'))
            <div class="border-t border-slate-200 px-5 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="inline-flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm">
                        <span class="font-semibold text-slate-900">Kết quả:</span>
                        <span>{{ $categories->total() }} danh mục</span>
                        <span class="h-5 w-px bg-slate-200"></span>
                        <span class="font-semibold text-slate-900">Trang {{ $categories->currentPage() }} / {{ $categories->lastPage() }}</span>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm">
                        @if($categories->onFirstPage())
                            <span class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-semibold text-slate-400 cursor-not-allowed">Trước</span>
                        @else
                            <a href="{{ $categories->previousPageUrl() }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Trước</a>
                        @endif

                        @if($categories->hasMorePages())
                            <a href="{{ $categories->nextPageUrl() }}" class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">Tiếp</a>
                        @else
                            <span class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-semibold text-slate-400 cursor-not-allowed">Tiếp</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<div id="addCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 modal-backdrop-custom" onclick="closeCategoryModal('addCategoryModal')"></div>
    <div class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl modal-panel">
        <form action="{{ route('admin.categories.store') }}" method="POST" class="p-6 sm:p-8">
            @csrf
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <i class="fa-solid fa-folder-plus"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Thêm danh mục mới</h3>
                        <p class="mt-1 text-sm text-slate-500">Nhập thông tin danh mục cần tạo.</p>
                    </div>
                </div>
                <button type="button" class="icon-btn" onclick="closeCategoryModal('addCategoryModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mt-6 grid gap-5 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-tag text-indigo-500"></i>
                        Tên danh mục
                    </label>
                    <input type="text" name="name" required maxlength="50" class="soft-input w-full rounded-2xl px-4 py-3.5 outline-none" placeholder="VD: Điện thoại">
                </div>
                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-sitemap text-indigo-500"></i>
                        Danh mục cha
                    </label>
                    <select name="parent_id" class="soft-input w-full rounded-2xl px-4 py-3.5 outline-none">
                        <option value="">Không có</option>
                        @foreach($allCategories as $parent)
                            <option value="{{ $parent->category_id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-slate-400">Để trống nếu đây là danh mục gốc.</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50" onclick="closeCategoryModal('addCategoryModal')">Hủy</button>
                <button type="submit" class="btn-primary-soft rounded-2xl px-5 py-3 text-sm font-semibold">Thêm danh mục</button>
            </div>
        </form>
    </div>
</div>

<div id="editCategoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 modal-backdrop-custom" onclick="closeCategoryModal('editCategoryModal')"></div>
    <div class="relative w-full max-w-2xl rounded-3xl bg-white shadow-2xl modal-panel">
        <form id="editForm" method="POST" class="p-6 sm:p-8">
            @csrf
            @method('PUT')
            <input type="hidden" name="version" id="editVersion">
            <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Sửa danh mục</h3>
                        <p class="mt-1 text-sm text-slate-500">Cập nhật thông tin danh mục.</p>
                    </div>
                </div>
                <button type="button" class="icon-btn" onclick="closeCategoryModal('editCategoryModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mt-6 grid gap-5 rounded-3xl border border-slate-200 bg-slate-50/70 p-5">
                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-tag text-indigo-500"></i>
                        Tên danh mục
                    </label>
                    <input type="text" name="name" id="editName" required maxlength="50" class="soft-input w-full rounded-2xl px-4 py-3.5 outline-none">
                </div>
                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <i class="fa-solid fa-sitemap text-indigo-500"></i>
                        Danh mục cha
                    </label>
                    <select name="parent_id" id="editParentId" class="soft-input w-full rounded-2xl px-4 py-3.5 outline-none">
                        <option value="">Không có</option>
                        @foreach($allCategories as $parent)
                            <option value="{{ $parent->category_id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-slate-400">Để trống nếu đây là danh mục gốc.</p>
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50" onclick="closeCategoryModal('editCategoryModal')">Hủy</button>
                <button type="submit" class="btn-primary-soft rounded-2xl px-5 py-3 text-sm font-semibold">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    function openCategoryModal(id) {
        const el = document.getElementById(id);
        if (el) {
            if (el.parentNode !== document.body) {
                document.body.appendChild(el);
            }
            el.classList.remove('hidden');
            el.classList.add('flex');
        }
    }

    function closeCategoryModal(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('hidden');
            el.classList.remove('flex');
        }
    }

    function openEditCategoryModal(id, name, icon, parentId, version) {
        document.getElementById('editName').value = name || '';
        document.getElementById('editParentId').value = parentId || '';
        document.getElementById('editVersion').value = version || '1';
        document.getElementById('editForm').action = "{{ url('admin/categories') }}/" + id;
        openCategoryModal('editCategoryModal');
    }

    var categoryTable = document.getElementById('categoryTable');
    if (categoryTable) {
        categoryTable.addEventListener('click', (event) => {
            const editButton = event.target.closest('.js-edit-category');
            if (editButton) {
                openEditCategoryModal(
                    editButton.dataset.id,
                    editButton.dataset.name,
                    '',
                    editButton.dataset.parentId || '',
                    editButton.dataset.version || '1'
                );
                return;
            }

            const deleteButton = event.target.closest('.js-delete-category');
            if (deleteButton) {
                confirmDeleteCategory(deleteButton.dataset.id, deleteButton.dataset.name);
            }
        });
    }

    function confirmDeleteCategory(id, name) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            html: `Bạn có chắc muốn xóa danh mục <strong>${name}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = "{{ url('admin/categories') }}/" + id;
                form.submit();
            }
        });
    }
</script>
@endsection
=======
>>>>>>> master
