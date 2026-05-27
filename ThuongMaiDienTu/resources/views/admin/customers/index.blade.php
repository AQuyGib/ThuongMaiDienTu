@extends('admin.layouts.master')

@section('title', 'Quản lý Khách hàng')

@section('content')
<div class="space-y-6">
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
            <a href="{{ route('admin.customers.export', request()->query()) }}" download class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                <i class="fa-solid fa-file-export"></i> Xuất Excel
            </a>
            <button type="button" onclick="openAddCustomerModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Thêm khách hàng
            </button>
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
                                    <div class="text-[10px] mt-0.5">
                                        @if(($customer->member_tier ?? 'Dong') == 'Vang')
                                            <span class="text-amber-600 font-extrabold uppercase tracking-wider"><i class="fa-solid fa-crown mr-0.5"></i> Hạng Vàng</span>
                                        @elseif(($customer->member_tier ?? 'Dong') == 'Bac')
                                            <span class="text-slate-500 font-bold uppercase tracking-wider"><i class="fa-solid fa-medal mr-0.5"></i> Hạng Bạc</span>
                                        @else
                                            <span class="text-orange-600 font-semibold uppercase tracking-wider"><i class="fa-solid fa-award mr-0.5"></i> Hạng Đồng</span>
                                        @endif
                                    </div>
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
                                <button type="button" 
                                        onclick="openEditCustomerModal({{ json_encode([
                                            'id' => $customer->user_id,
                                            'full_name' => $customer->full_name,
                                            'email' => $customer->email,
                                            'phone_number' => $customer->phone_number,
                                            'status' => $customer->status,
                                            'address' => $customer->address,
                                            'version' => $customer->version
                                        ]) }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Sửa">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                @if(in_array(Auth::user()->role_id, [1, 2]))
                                    <form id="delete-form-{{ $customer->user_id }}" action="{{ route('admin.customers.destroy', $customer->user_id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="confirmDelete('{{ $customer->user_id }}', '{{ e($customer->full_name) }}')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Xóa">
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

<!-- Modal Thêm khách hàng -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl overflow-hidden bg-white">
            <div class="modal-header px-6 py-4 border-0" style="background-color: #0f172a !important; color: #ffffff !important;">
                <h5 class="modal-title font-bold text-lg flex items-center gap-2" style="color: #ffffff !important;">
                    <i class="fa-solid fa-user-plus text-rose-500"></i> Thêm Khách Hàng Mới
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.customers.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <input type="hidden" name="version" value="1">
                
                @if($errors->any() && !old('user_id'))
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                        <div class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation mr-2"></i>Vui lòng kiểm tra lại thông tin:</div>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Họ tên -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Họ và tên <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="full_name" value="{{ old('user_id') ? '' : old('full_name') }}" required
                                   maxlength="50"
                                   class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                   style="padding-left: 2.75rem;"
                                   placeholder="Nhập họ và tên khách hàng">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" value="{{ old('user_id') ? '' : old('email') }}" required
                                   maxlength="100"
                                   class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                   style="padding-left: 2.75rem;"
                                   placeholder="example@gmail.com">
                        </div>
                    </div>

                    <!-- Số điện thoại -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Số điện thoại</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" name="phone_number" value="{{ old('user_id') ? '' : old('phone_number') }}"
                                   maxlength="10"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                   style="padding-left: 2.75rem;"
                                   placeholder="0987xxxxxx">
                        </div>
                    </div>

                    <!-- Mật khẩu -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Mật khẩu <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password" required
                                   maxlength="255"
                                   class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                   style="padding-left: 2.75rem;"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Trạng thái -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Trạng thái <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-circle-info"></i></span>
                            <select name="status" required
                                    class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-semibold text-slate-700 appearance-none"
                                    style="padding-left: 2.75rem;">
                                <option value="Active" {{ (old('user_id') ? '' : old('status')) == 'Active' ? 'selected' : '' }}>Đang hoạt động</option>
                                <option value="Banned" {{ (old('user_id') ? '' : old('status')) == 'Banned' ? 'selected' : '' }}>Khóa tài khoản</option>
                            </select>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Địa chỉ</label>
                        <div class="relative">
                            <span class="absolute left-4 top-4 text-slate-400"><i class="fa-solid fa-location-dot"></i></span>
                            <textarea name="address" rows="3" 
                                      maxlength="255"
                                      class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                      style="padding-left: 2.75rem;"
                                      placeholder="Nhập địa chỉ khách hàng">{{ old('user_id') ? '' : old('address') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" class="px-5 py-2.5 rounded-xl border border-slate-200 font-bold text-slate-500 hover:bg-slate-50 transition" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-950 text-white font-bold hover:bg-black transition shadow-lg shadow-slate-200 flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Lưu thông tin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa khách hàng -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-2xl border-0 shadow-xl overflow-hidden bg-white">
            <div class="modal-header px-6 py-4 border-0" style="background-color: #1e3a8a !important; color: #ffffff !important;">
                <h5 class="modal-title font-bold text-lg flex items-center gap-2" style="color: #ffffff !important;">
                    <i class="fa-solid fa-user-pen text-blue-200"></i> Chỉnh Sửa Khách Hàng
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCustomerForm" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="user_id" id="edit_user_id" value="{{ old('user_id') }}">
                <input type="hidden" name="version" id="edit_version" value="{{ old('version') }}">
                
                @if($errors->any() && old('user_id'))
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
                        <div class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation mr-2"></i>Vui lòng kiểm tra lại thông tin:</div>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Họ tên -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Họ và tên <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-user"></i></span>
                            <input type="text" name="full_name" id="edit_full_name" value="{{ old('user_id') ? old('full_name') : '' }}" required
                                   maxlength="50"
                                   class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                   style="padding-left: 2.75rem;"
                                   placeholder="Nhập họ và tên khách hàng">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                            <input type="email" name="email" id="edit_email" value="{{ old('user_id') ? old('email') : '' }}" required
                                   maxlength="100"
                                   class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                   style="padding-left: 2.75rem;"
                                   placeholder="example@gmail.com">
                        </div>
                    </div>

                    <!-- Số điện thoại -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Số điện thoại</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-phone"></i></span>
                            <input type="text" name="phone_number" id="edit_phone_number" value="{{ old('user_id') ? old('phone_number') : '' }}"
                                   maxlength="10"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                   class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                   style="padding-left: 2.75rem;"
                                   placeholder="0987xxxxxx">
                        </div>
                    </div>

                    <!-- Mật khẩu -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Mật khẩu (Để trống nếu không đổi)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-lock"></i></span>
                            <input type="password" name="password"
                                   maxlength="255"
                                   class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                   style="padding-left: 2.75rem;"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <!-- Trạng thái -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Trạng thái <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-circle-info"></i></span>
                            <select name="status" id="edit_status" required
                                    class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-semibold text-slate-700 appearance-none"
                                    style="padding-left: 2.75rem;">
                                <option value="Active" {{ (old('user_id') ? old('status') : '') == 'Active' ? 'selected' : '' }}>Đang hoạt động</option>
                                <option value="Banned" {{ (old('user_id') ? old('status') : '') == 'Banned' ? 'selected' : '' }}>Khóa tài khoản</option>
                            </select>
                        </div>
                    </div>

                    <!-- Địa chỉ -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Địa chỉ</label>
                        <div class="relative">
                            <span class="absolute left-4 top-4 text-slate-400"><i class="fa-solid fa-location-dot"></i></span>
                            <textarea name="address" id="edit_address" rows="3" 
                                      maxlength="255"
                                      class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                                      style="padding-left: 2.75rem;"
                                      placeholder="Nhập địa chỉ khách hàng">{{ old('user_id') ? old('address') : '' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" class="px-5 py-2.5 rounded-xl border border-slate-200 font-bold text-slate-500 hover:bg-slate-50 transition" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition shadow-lg shadow-blue-200 flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Xác nhận xóa',
            html: `Bạn có chắc muốn xóa khách hàng <strong>${name}</strong> không?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
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
                document.getElementById(`delete-form-${id}`).submit();
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

        if (action === 'delete') {
            title = 'Xác nhận xóa';
            htmlMsg = `Bạn có chắc muốn <strong>XÓA TẠM</strong> <strong>${ids.length}</strong> khách hàng đã chọn?`;
            confirmBtnText = 'Xóa tạm';
            confirmBtnColor = '#dc2626';
        } else if (action === 'Active') {
            title = 'Kích hoạt tài khoản';
            htmlMsg = `Kích hoạt trạng thái hoạt động cho <strong>${ids.length}</strong> khách hàng đã chọn?`;
            confirmBtnText = 'Kích hoạt';
            confirmBtnColor = '#16a34a';
        } else if (action === 'Banned') {
            title = 'Khóa tài khoản';
            htmlMsg = `Khóa <strong>${ids.length}</strong> tài khoản khách hàng đã chọn?`;
            confirmBtnText = 'Khóa';
            confirmBtnColor = '#ea580c';
        }

        Swal.fire({
            title: title,
            html: htmlMsg,
            icon: 'warning',
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
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: data.message || 'Có lỗi xảy ra!'
                    });
                }
            });
        });
    }

    let addModalInstance = null;
    let editModalInstance = null;

    function openAddCustomerModal() {
        if (!addModalInstance) {
            addModalInstance = new bootstrap.Modal(document.getElementById('addCustomerModal'));
        }
        addModalInstance.show();
    }

    function openEditCustomerModal(customer) {
        const id = customer.user_id || customer.id;
        document.getElementById('editCustomerForm').action = `/admin/customers/${id}`;
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_full_name').value = customer.full_name || '';
        document.getElementById('edit_email').value = customer.email || '';
        document.getElementById('edit_phone_number').value = customer.phone_number || '';
        document.getElementById('edit_status').value = customer.status || 'Active';
        document.getElementById('edit_address').value = customer.address || '';
        document.getElementById('edit_version').value = customer.version || '1';

        if (!editModalInstance) {
            editModalInstance = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        }
        editModalInstance.show();
    }

    // Tự động mở modal từ Session hoặc Validation Error Redirects
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('show_create_modal'))
            openAddCustomerModal();
        @elseif(session('edit_customer'))
            openEditCustomerModal({!! json_encode(session('edit_customer')) !!});
        @endif

        @if($errors->any())
            @if(old('user_id'))
                // Khi có lỗi từ trang Edit, mở lại modal Edit
                if (!editModalInstance) {
                    editModalInstance = new bootstrap.Modal(document.getElementById('editCustomerModal'));
                }
                editModalInstance.show();
            @else
                // Khi có lỗi từ trang Add, mở lại modal Add
                if (!addModalInstance) {
                    addModalInstance = new bootstrap.Modal(document.getElementById('addCustomerModal'));
                }
                addModalInstance.show();
            @endif
        @endif
    });
</script>
@endsection
