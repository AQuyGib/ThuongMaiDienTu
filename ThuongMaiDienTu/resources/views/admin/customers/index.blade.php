@extends('layouts.app')

@section('title', 'Quản lý Khách hàng')

@section('content')
<div class="container py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý Khách hàng</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.customers.trash') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition flex items-center gap-2 border border-gray-200">
                <i class="fa-solid fa-trash-can"></i> Thùng rác
            </a>
            @if(Auth::user()->role_id == 1)
                <button onclick="toggleModal('logsModal')" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition flex items-center gap-2 border border-gray-200">
                    <i class="fa-solid fa-clock-rotate-left"></i> Nhật ký hoạt động
                </button>
            @endif
            <a href="{{ route('admin.customers.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <i class="fa-solid fa-file-export"></i> Xuất Excel
            </a>
            <a href="{{ route('admin.customers.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Thêm khách hàng
            </a>
        </div>
    </div>

    <!-- Widgets Thống kê -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Tổng khách hàng</div>
                <div class="text-2xl font-bold text-gray-800">{{ \App\Models\User::where('role_id', 3)->count() }}</div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                <i class="fa-solid fa-user-check text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Đang hoạt động</div>
                <div class="text-2xl font-bold text-gray-800">{{ \App\Models\User::where('role_id', 3)->where('status', 'Active')->count() }}</div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600">
                <i class="fa-solid fa-bolt text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Đang Online</div>
                <div class="text-2xl font-bold text-gray-800">
                    {{ \App\Models\User::where('role_id', 3)->get()->filter(fn($u) => $u->isOnline())->count() }}
                </div>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-red-50 flex items-center justify-center text-primary">
                <i class="fa-solid fa-user-plus text-xl"></i>
            </div>
            <div>
                <div class="text-sm text-gray-500 font-medium">Mới hôm nay</div>
                <div class="text-2xl font-bold text-gray-800">{{ \App\Models\User::where('role_id', 3)->whereDate('created_at', today())->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Thanh tìm kiếm & Bộ lọc -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="relative flex-1 min-w-[300px]">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, email hoặc số điện thoại..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
            </div>

            <div class="w-48">
                <select name="status" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary appearance-none">
                    <option value="">-- Trạng thái --</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="Banned" {{ request('status') == 'Banned' ? 'selected' : '' }}>Bị khóa</option>
                </select>
            </div>

            <div class="w-48">
                <select name="tier" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary appearance-none">
                    <option value="">-- Hạng thành viên --</option>
                    <option value="Dong" {{ request('tier') == 'Dong' ? 'selected' : '' }}>Hạng Đồng</option>
                    <option value="Bac" {{ request('tier') == 'Bac' ? 'selected' : '' }}>Hạng Bạc</option>
                    <option value="Vang" {{ request('tier') == 'Vang' ? 'selected' : '' }}>Hạng Vàng</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-primary">
                <span class="text-gray-400">đến</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" 
                       class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-primary">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-black transition">
                    <i class="fa-solid fa-filter mr-1"></i> Lọc
                </button>
                @if(request()->anyFilled(['q', 'status', 'tier', 'start_date', 'end_date']))
                    <a href="{{ route('admin.customers.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition flex items-center">
                        Xóa lọc
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bulk Actions Bar (Ẩn mặc định) -->
    <div id="bulkActionsBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-6 py-4 rounded-2xl shadow-2xl z-50 flex items-center gap-6 animate-bounce-in">
        <div class="text-sm font-medium">
            Đã chọn <span id="selectedCount" class="font-bold text-primary">0</span> khách hàng
        </div>
        <div class="h-6 w-px bg-gray-700"></div>
        <div class="flex gap-3">
            <button onclick="handleBulkAction('Active')" class="text-sm hover:text-green-400 transition flex items-center gap-2">
                <i class="fa-solid fa-user-check"></i> Kích hoạt
            </button>
            <button onclick="handleBulkAction('Banned')" class="text-sm hover:text-orange-400 transition flex items-center gap-2">
                <i class="fa-solid fa-user-slash"></i> Khóa
            </button>
            @if(in_array(Auth::user()->role_id, [1, 2]))
                <button onclick="handleBulkAction('delete')" class="text-sm hover:text-red-400 transition flex items-center gap-2">
                    <i class="fa-solid fa-trash"></i> Xóa tạm
                </button>
            @endif
        </div>
        <button onclick="clearSelection()" class="ml-4 text-gray-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Bảng danh sách -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 w-10">
                        <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-primary focus:ring-primary">
                    </th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Khách hàng</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Liên hệ</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase">Ngày tạo</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase text-center">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="customer_ids[]" value="{{ $customer->user_id }}" class="customer-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">#{{ $customer->user_id }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-primary font-bold">
                                    {{ strtoupper(substr($customer->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800">{{ $customer->full_name }}</div>
                                    <div class="text-xs text-gray-400">Khách hàng thân thiết</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600">{{ $customer->email }}</div>
                            <div class="text-xs text-gray-400">{{ $customer->phone_number ?? 'Chưa cập nhật SĐT' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($customer->status == 'Active')
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-full">Hoạt động</span>
                            @else
                                <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded-full">Bị khóa</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $customer->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.customers.show', $customer->user_id) }}" 
                                   class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition" title="Xem chi tiết">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.customers.edit', $customer->user_id) }}" 
                                   class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Sửa">
                                    <i class="fa-solid fa-edit"></i>
                                </a>
                                @if(in_array(Auth::user()->role_id, [1, 2]))
                                    <form action="{{ route('admin.customers.destroy', $customer->user_id) }}" method="POST" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa khách hàng này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400">Không tìm thấy khách hàng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $customers->appends(request()->query())->links('vendor.pagination.tailwind') }}
    </div>
</div>

<!-- Modal Nhật ký hoạt động (Dành cho Admin) -->
@if(Auth::user()->role_id == 1)
<div id="logsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-xl font-bold">Nhật ký thay đổi hệ thống</h2>
            <button onclick="toggleModal('logsModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-xmark text-2xl"></i>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1">
            <div class="space-y-4">
                @foreach($logs as $log)
                    <div class="flex gap-4 p-3 rounded-lg hover:bg-gray-50 transition border border-gray-50">
                        <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-gray-800">{{ $log->user->full_name ?? 'Hệ thống' }}</div>
                            <div class="text-sm text-gray-600">{{ $log->action }}</div>
                            <div class="text-xs text-gray-400 mt-1 flex gap-3">
                                <span><i class="fa-solid fa-clock mr-1"></i> {{ $log->created_at->diffForHumans() }}</span>
                                <span><i class="fa-solid fa-network-wired mr-1"></i> {{ $log->ip_address }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="p-4 bg-gray-50 text-center text-xs text-gray-400 uppercase tracking-widest font-bold">
            Chỉ hiển thị 20 hành động gần nhất
        </div>
    </div>
</div>
@endif

<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        modal.classList.toggle('hidden');
    }

    // Xử lý Checkbox hàng loạt
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

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkBar);
    });

    function clearSelection() {
        selectAll.checked = false;
        checkboxes.forEach(cb => cb.checked = false);
        updateBulkBar();
    }

    function handleBulkAction(action) {
        const ids = Array.from(document.querySelectorAll('.customer-checkbox:checked')).map(cb => cb.value);
        if (ids.length === 0) return;

        let confirmMsg = `Bạn có chắc muốn thực hiện thao tác này cho ${ids.length} khách hàng?`;
        if (action === 'delete') confirmMsg = `Bạn có chắc muốn XÓA TẠM ${ids.length} khách hàng đã chọn?`;
        
        if (!confirm(confirmMsg)) return;

        // Gửi request hàng loạt
        fetch("{{ route('admin.customers.bulk-action') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids, action })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        });
    }
</script>
@endsection
