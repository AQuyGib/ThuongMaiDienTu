@extends('layouts.app')

@section('title', 'Quản lý Khách hàng')

@section('content')
<div class="container py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Quản lý Khách hàng</h1>
        <div class="flex gap-2">
            @if(Auth::user()->role_id == 1)
                <button onclick="toggleModal('logsModal')" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition flex items-center gap-2 border border-gray-200">
                    <i class="fa-solid fa-clock-rotate-left"></i> Nhật ký hoạt động
                </button>
            @endif
            <a href="{{ route('admin.customers.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Thêm khách hàng
            </a>
        </div>
    </div>

    <!-- Thanh tìm kiếm -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="flex gap-3">
            <div class="relative flex-1">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, email hoặc số điện thoại..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:outline-none focus:border-primary">
            </div>
            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-black transition">Tìm kiếm</button>
        </form>
    </div>

    <!-- Bảng danh sách -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
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
</script>
@endsection
