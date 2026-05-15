@extends('admin.layouts.master')

@section('title', 'Quản Lý Danh Mục')

@section('content')
<div class="container mx-auto py-6 space-y-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Quản Lý Danh Mục</h1>
            <p class="text-sm text-slate-500 mt-1">{{ $totalCategories ?? $categories->total() }} danh mục</p>
        </div>
            <button type="button"
                    style="background: linear-gradient(90deg, #0284c7 0%, #2563eb 100%); color: #fff; box-shadow: 0 10px 25px rgba(37,99,235,.25);"
                    class="rounded-2xl px-5 py-3 text-sm font-semibold transition hover:brightness-110 hover:scale-[1.01]"
                    data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus"></i>
            Thêm Danh Mục
        </button>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-700">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-700 space-y-1">
            <div class="font-semibold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Vui lòng kiểm tra lại dữ liệu</div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="rounded-3xl bg-white px-5 py-4 shadow-sm border border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center text-lg"><i class="fa-solid fa-layer-group"></i></div>
                <div>
                    <div class="text-xl font-extrabold text-slate-900 leading-none">{{ $totalCategories ?? $categories->total() }}</div>
                    <div class="text-xs text-slate-500 mt-1">Tổng danh mục</div>
                </div>
            </div>
        </div>
        <div class="rounded-3xl bg-white px-5 py-4 shadow-sm border border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg"><i class="fa-solid fa-folder"></i></div>
                <div>
                    <div class="text-xl font-extrabold text-slate-900 leading-none">{{ $rootCategories ?? 0 }}</div>
                    <div class="text-xs text-slate-500 mt-1">Danh mục gốc</div>
                </div>
            </div>
        </div>
        <div class="rounded-3xl bg-white px-5 py-4 shadow-sm border border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg"><i class="fa-solid fa-layer-group"></i></div>
                <div>
                    <div class="text-xl font-extrabold text-slate-900 leading-none">{{ $childCategories ?? 0 }}</div>
                    <div class="text-xs text-slate-500 mt-1">Danh mục con</div>
                </div>
            </div>
        </div>
        <div class="rounded-3xl bg-white px-5 py-4 shadow-sm border border-slate-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div>
                    <div class="text-xl font-extrabold text-slate-900 leading-none">{{ $categories->sum('products_count') ?? 0 }}</div>
                    <div class="text-xs text-slate-500 mt-1">Tổng sản phẩm</div>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-[28px] bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between gap-4 flex-wrap px-6 py-5 border-b border-slate-200">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Danh Sách Danh Mục</h2>
                <p class="text-sm text-slate-500 mt-1">Quản lý thông tin danh mục sản phẩm</p>
            </div>
            <form method="GET" action="{{ route('admin.categories.index') }}" class="w-full sm:w-[440px]">
                <div class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 p-2 shadow-sm transition focus-within:border-sky-500 focus-within:bg-white focus-within:shadow-md">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl text-slate-400 shrink-0">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input name="search" type="text" value="{{ $search ?? '' }}" placeholder="Tìm kiếm danh mục cha, tên danh mục..."
                           class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-slate-400"
                           onkeydown="if(event.key==='Enter'){event.preventDefault(); this.form.submit();}">
                    @if(!empty($search))
                        <button type="button"
                                onclick="window.location='{{ route('admin.categories.index') }}'"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                            Xóa
                        </button>
                    @endif
                    <button type="submit" class="rounded-xl bg-gradient-to-r from-sky-600 to-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:brightness-110">Tìm</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left" id="categoryTable">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">Tên danh mục</th>
                        <th class="px-6 py-4">Danh mục cha</th>
                        <th class="px-6 py-4">Số SP</th>
                        <th class="px-6 py-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <span class="inline-flex min-w-9 h-7 items-center justify-center rounded-xl bg-slate-900 px-2 text-xs font-bold text-white">{{ $category->category_id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">{{ $category->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-500">{{ $category->parent ? $category->parent->name : '—' }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $category->products_count ?? 0 }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                            class="w-10 h-10 rounded-2xl bg-slate-900 text-white transition hover:bg-violet-600"
                                            title="Sửa"
                                            onclick='openEditModal({{ $category->category_id }}, @json($category->name), @json($category->icon ?? ""), @json($category->parent_id ?? ""))'>
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button"
                                            class="w-10 h-10 rounded-2xl bg-slate-900 text-white transition hover:bg-rose-600"
                                            title="Xóa"
                                            onclick='confirmDelete({{ $category->category_id }}, @json($category->name))'>
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-500">
                                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-violet-50 text-violet-600 text-2xl">
                                    <i class="fa-solid fa-box-open"></i>
                                </div>
                                <div class="font-semibold text-slate-900">Chưa có danh mục nào</div>
                                <div class="mt-1 text-sm">Hãy thêm danh mục đầu tiên để bắt đầu quản lý.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($categories, 'links'))
            <div class="px-6 py-4 border-t border-slate-200 flex items-center justify-between gap-4 flex-wrap">
                <div class="text-sm text-slate-500">
                    Hiển thị {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} trên {{ $categories->total() }} danh mục
                    @if(!empty($search))
                        <span class="ml-2 inline-flex items-center rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                            Kết quả cho: "{{ $search }}"
                        </span>
                    @endif
                </div>
                {{ $categories->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-[28px] overflow-hidden max-h-[90vh] flex flex-col">
            <form action="{{ route('admin.categories.store') }}" method="POST" class="flex flex-col max-h-[90vh]">
                @csrf
                <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between shrink-0 bg-white">
                    <div>
                        <h5 class="text-lg font-bold text-slate-900 mb-1">Thêm Danh Mục Mới</h5>
                        <p class="text-sm text-slate-500">Nhập thông tin danh mục cần tạo</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="p-6 bg-white overflow-y-auto custom-scrollbar">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Tên danh mục <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-violet-500 focus:bg-white" placeholder="VD: Điện thoại" required maxlength="50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Danh mục cha</label>
                            <div class="relative" data-combobox="addParent">
                                <input type="hidden" name="parent_id" id="addParentId">
                                <button type="button" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-left text-sm outline-none focus:border-sky-500 focus:bg-white" onclick="toggleCombobox('addParent')">
                                    <span id="addParentLabel" class="text-slate-400">Không có</span>
                                </button>
                                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <div id="addParentDropdown" class="absolute left-0 right-0 top-full z-50 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                                    <div class="border-b border-slate-100 p-2">
                                        <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-sky-500" placeholder="Tìm danh mục cha..." oninput="filterCombobox('addParent', this.value)">
                                    </div>
                                    <div class="max-h-56 overflow-y-auto py-1">
                                        <button type="button" class="combobox-option w-full px-4 py-2 text-left text-sm hover:bg-slate-50" data-label="Không có" onclick="selectCombobox('addParent', '', 'Không có')">Không có</button>
                                        @foreach($allCategories as $parent)
                                            <button type="button" class="combobox-option w-full px-4 py-2 text-left text-sm hover:bg-slate-50" data-value="{{ $parent->category_id }}" data-label="{{ $parent->name }}" onclick="selectCombobox('addParent', '{{ $parent->category_id }}', @js($parent->name))">{{ $parent->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-400">Chọn nếu muốn tạo danh mục con, hoặc để trống nếu đây là danh mục gốc.</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 border-t border-slate-200 flex justify-end gap-3 bg-slate-50 shrink-0">
                    <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" style="background: linear-gradient(90deg, #0284c7 0%, #2563eb 100%); color: #fff; box-shadow: 0 10px 25px rgba(37,99,235,.25);" class="rounded-2xl px-5 py-3 text-sm font-semibold transition hover:brightness-110 hover:scale-[1.01]">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-[28px] overflow-hidden">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h5 class="text-lg font-bold text-slate-900 mb-1">Sửa Danh Mục</h5>
                        <p class="text-sm text-slate-500">Cập nhật thông tin danh mục</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="p-6 bg-white">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Tên danh mục <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="editName" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-violet-500 focus:bg-white" placeholder="VD: Điện thoại" required maxlength="50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Danh mục cha</label>
                            <div class="relative" data-combobox="editParent">
                                <input type="hidden" name="parent_id" id="editParentId">
                                <button type="button" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-left text-sm outline-none focus:border-sky-500 focus:bg-white" onclick="toggleCombobox('editParent')">
                                    <span id="editParentLabel" class="text-slate-400">Không có</span>
                                </button>
                                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                <div id="editParentDropdown" class="absolute left-0 right-0 top-full z-50 mt-2 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                                    <div class="border-b border-slate-100 p-2">
                                        <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-sky-500" placeholder="Tìm danh mục cha..." oninput="filterCombobox('editParent', this.value)">
                                    </div>
                                    <div class="max-h-56 overflow-y-auto py-1">
                                        <button type="button" class="combobox-option w-full px-4 py-2 text-left text-sm hover:bg-slate-50" data-label="Không có" onclick="selectCombobox('editParent', '', 'Không có')">Không có</button>
                                        @foreach($allCategories as $parent)
                                            <button type="button" class="combobox-option w-full px-4 py-2 text-left text-sm hover:bg-slate-50" data-value="{{ $parent->category_id }}" data-label="{{ $parent->name }}" onclick="selectCombobox('editParent', '{{ $parent->category_id }}', @js($parent->name))">{{ $parent->name }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-slate-400">Chọn nếu muốn thay đổi nhóm cha, hoặc để trống nếu đây là danh mục gốc.</p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 border-t border-slate-200 flex justify-end gap-3 bg-slate-50">
                    <button type="button" class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" style="background: linear-gradient(90deg, #0284c7 0%, #2563eb 100%); color: #fff; box-shadow: 0 10px 25px rgba(37,99,235,.25);" class="rounded-2xl px-5 py-3 text-sm font-semibold transition hover:brightness-110 hover:scale-[1.01]">Cập Nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    function toggleCombobox(prefix) {
        const dropdown = document.getElementById(prefix + 'Dropdown');
        const isHidden = dropdown.classList.contains('hidden');
        document.querySelectorAll('[id$="Dropdown"]').forEach(el => el.classList.add('hidden'));
        if (isHidden) dropdown.classList.remove('hidden');
    }

    function filterCombobox(prefix, keyword) {
        const term = (keyword || '').toLowerCase();
        document.querySelectorAll(`#${prefix}Dropdown .combobox-option`).forEach(item => {
            const label = (item.dataset.label || item.textContent || '').toLowerCase();
            item.classList.toggle('hidden', !label.includes(term));
        });
    }

    function selectCombobox(prefix, value, label) {
        const display = document.getElementById(prefix + 'Label');
        display.textContent = label || 'Không có';
        display.classList.toggle('text-slate-400', !value);
        display.classList.toggle('text-slate-900', !!value);
        document.getElementById(prefix + 'Id').value = value || '';
        document.getElementById(prefix + 'Dropdown').classList.add('hidden');
    }

    document.addEventListener('click', function (event) {
        document.querySelectorAll('[id$="Dropdown"]').forEach(dropdown => {
            const wrapper = dropdown.closest('[data-combobox]');
            if (wrapper && !wrapper.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    });

    function openEditModal(id, name, icon, parentId) {
        document.getElementById('editName').value = name || '';
        document.getElementById('editParentId').value = parentId || '';
        document.getElementById('editParentLabel').textContent = 'Không có';
        document.getElementById('editParentLabel').classList.add('text-slate-400');
        document.getElementById('editParentLabel').classList.remove('text-slate-900');
        document.getElementById('editForm').action = "{{ url('admin/categories') }}/" + id;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Xác nhận xóa?',
            html: `Bạn có chắc muốn xóa danh mục <strong>"${name}"</strong>?<br><small style="color:#64748b;">Hành động này không thể hoàn tác.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#e2e8f0',
            confirmButtonText: '<i class="fa-solid fa-trash-can"></i> Xóa',
            cancelButtonText: 'Hủy',
            background: '#ffffff',
            color: '#0f172a',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteForm');
                form.action = "{{ url('admin/categories') }}/" + id;
                form.submit();
            }
        });
    }
</script>
@endpush
