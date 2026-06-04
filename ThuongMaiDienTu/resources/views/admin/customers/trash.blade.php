@extends('admin.layouts.master')

@section('title', 'Thùng rác Khách hàng')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.customers.index') }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-500 shadow-sm border border-gray-100 hover:bg-gray-50 transition">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Thùng rác Khách hàng</h1>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulkActionsBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl z-50 flex items-center gap-6">
        <div class="text-sm font-medium">Đã chọn <span id="selectedCount" class="font-bold text-primary">0</span> khách hàng</div>
        <div class="h-6 w-px bg-gray-700"></div>
        <div class="flex gap-3">
            <button onclick="handleBulkAction('restore')" class="text-sm hover:text-green-400 transition flex items-center gap-2">
                <i class="fa-solid fa-rotate-left"></i> Khôi phục
            </button>
            @if(Auth::user()->role_id == 1)
                <button onclick="handleBulkAction('force-delete')" class="text-sm hover:text-red-400 transition flex items-center gap-2">
                    <i class="fa-solid fa-skull-crossbones"></i> Xóa vĩnh viễn
                </button>
            @endif
        </div>
        <button onclick="clearSelection()" class="ml-4 text-gray-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 w-10">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Khách hàng</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Ngày xóa</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <input type="checkbox" value="{{ $customer->user_id }}" class="customer-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold">
                                    {{ strtoupper(substr($customer->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">{{ $customer->full_name }}</div>
                                    <div class="text-xs text-gray-400">{{ $customer->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $customer->deleted_at->format('d/m/Y H:i') }}
                            <div class="text-xs text-gray-400">{{ $customer->deleted_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                <form action="{{ route('admin.customers.restore', $customer->user_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Khôi phục">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                                @if(Auth::user()->role_id == 1)
                                    <form id="force-delete-form-{{ $customer->user_id }}" action="{{ route('admin.customers.force-delete', $customer->user_id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmForceDelete('{{ $customer->user_id }}', '{{ e($customer->full_name) }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Xóa vĩnh viễn">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400">Thùng rác trống.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $customers->links('vendor.pagination.tailwind') }}
    </div>
</div>

<script>
    // Tái sử dụng logic Bulk Action
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.customer-checkbox');
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');

    function updateBulkBar() {
        const checked = document.querySelectorAll('.customer-checkbox:checked');
        if (checked.length > 0) {
            bulkBar.classList.remove('hidden');
            selectedCount.innerText = checked.length;
        } else {
            bulkBar.classList.add('hidden');
        }
    }

    selectAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkBar();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));

    function clearSelection() {
        selectAll.checked = false;
        checkboxes.forEach(cb => cb.checked = false);
        updateBulkBar();
    }

    function confirmForceDelete(id, name) {
        Swal.fire({
            title: 'Xóa vĩnh viễn',
            html: `Hành động này <strong>KHÔNG THỂ KHÔI PHỤC</strong>!<br>Bạn có chắc muốn xóa vĩnh viễn khách hàng <strong>${name}</strong> không?`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Xóa vĩnh viễn',
            cancelButtonText: 'Hủy',
            reverseButtons: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl shadow-lg',
                confirmButton: 'px-4 py-2 text-sm font-semibold rounded-lg',
                cancelButton: 'px-4 py-2 text-sm font-semibold rounded-lg'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`force-delete-form-${id}`).submit();
            }
        });
    }

    function handleBulkAction(action) {
        const ids = Array.from(document.querySelectorAll('.customer-checkbox:checked')).map(cb => cb.value);
        if (ids.length === 0) return;

        let title = 'Xác nhận';
        let htmlMsg = `Bạn có chắc muốn thực hiện thao tác này cho <strong>${ids.length}</strong> khách hàng?`;
        let confirmBtnText = 'Xác nhận';
        let confirmBtnColor = '#1e293b';
        let icon = 'warning';

        if (action === 'force-delete') {
            title = 'Xóa vĩnh viễn hàng loạt';
            htmlMsg = `CẢNH BÁO: Bạn có chắc muốn <strong>XÓA VĨNH VIỄN</strong> <strong>${ids.length}</strong> khách hàng đã chọn?<br>Thao tác này không thể hoàn tác!`;
            confirmBtnText = 'Xóa vĩnh viễn';
            confirmBtnColor = '#dc2626';
            icon = 'error';
        } else if (action === 'restore') {
            title = 'Khôi phục tài khoản';
            htmlMsg = `Khôi phục hoạt động cho <strong>${ids.length}</strong> khách hàng đã chọn?`;
            confirmBtnText = 'Khôi phục';
            confirmBtnColor = '#16a34a';
        }

        Swal.fire({
            title: title,
            html: htmlMsg,
            icon: icon,
            showCancelButton: true,
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Hủy',
            reverseButtons: true,
            confirmButtonColor: confirmBtnColor,
            cancelButtonColor: '#64748b',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl shadow-lg',
                confirmButton: 'px-4 py-2 text-sm font-semibold rounded-lg',
                cancelButton: 'px-4 py-2 text-sm font-semibold rounded-lg'
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch("{{ route('admin.customers.bulk-action') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ ids, action })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message || 'Có lỗi xảy ra!'
                    });
                }
            });
        });
    }
</script>
@endsection
