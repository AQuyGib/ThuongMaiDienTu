@extends('admin.layouts.master')

@section('title', 'Quản lý Tài khoản')
@section('page-title', 'Quản lý Tài khoản')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

        {{-- HEADER: Tổng + Nút thêm --}}
        <div
            class="p-4 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 bg-gray-50">
            <span class="text-gray-600 font-medium">
                Tổng cộng: <b class="text-gray-800">{{ $users->total() }}</b> tài khoản
            </span>
            <button onclick="openCreateUserModal()"
                class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Thêm Tài Khoản
            </button>
        </div>

        {{-- BẢNG DANH SÁCH --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm min-w-[700px]">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <th class="p-4 border-b font-semibold w-16">ID</th>
                        <th class="p-4 border-b font-semibold">Họ tên & Email</th>
                        <th class="p-4 border-b font-semibold">Vai trò</th>
                        <th class="p-4 border-b font-semibold">Hạng TV</th>
                        <th class="p-4 border-b font-semibold">Trạng thái</th>
                        <th class="p-4 border-b font-semibold">Ngày tạo</th>
                        <th class="p-4 border-b font-semibold text-center w-32">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 border-b border-gray-100 transition">
                            <td class="p-4 text-gray-500">#{{ $user->user_id }}</td>
                            <td class="p-4">
                                <div class="font-bold text-gray-800">{{ $user->full_name }}</div>
                                <div class="text-gray-500 text-xs">{{ $user->email }}</div>
                            </td>
                            <td class="p-4">
                                @if($user->role_id == 1)
                                    <span class="bg-red-100 text-red-600 px-2 py-1 rounded text-xs font-bold">Admin</span>
                                @elseif($user->role_id == 2)
                                    <span class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs font-bold">Quản lý</span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-bold">Khách hàng</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($user->member_tier == 'Vang')
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs font-bold"><i
                                            class="fa-solid fa-crown mr-1"></i>Vàng</span>
                                @elseif($user->member_tier == 'Bac')
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-bold"><i
                                            class="fa-solid fa-medal mr-1"></i>Bạc</span>
                                @else
                                    <span class="bg-orange-50 text-orange-600 px-2 py-1 rounded text-xs font-bold">Đồng</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($user->status == 'Active')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Hoạt động</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Bị khóa</span>
                                @endif
                            </td>
                            <td class="p-4 text-gray-500 text-xs">
                                {{ $user->created_at ? \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="p-4">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Nút Sửa --}}
                                    <button onclick='editUser(@json($user))'
                                        class="flex items-center justify-center w-9 h-9 text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md" 
                                        title="Chỉnh sửa thông tin">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    @if(Auth::user()->user_id != $user->user_id)
                                        {{-- Nút Xem thiết bị --}}
                                        <a href="{{ route('admin.users.sessions', $user->user_id) }}" 
                                            class="flex items-center justify-center w-9 h-9 text-amber-600 bg-amber-50 hover:bg-amber-500 hover:text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md"
                                            title="Quản lý thiết bị đang đăng nhập">
                                            <i class="fa-solid fa-display"></i>
                                        </a>

                                        {{-- Nút Xóa --}}
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->user_id) }}"
                                            class="inline-block" onsubmit="return confirmDelete(event)">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="flex items-center justify-center w-9 h-9 text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all duration-300 shadow-sm hover:shadow-md"
                                                title="Xóa tài khoản">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Placeholder cho chính mình --}}
                                        <div class="w-9 h-9 flex items-center justify-center text-gray-300 cursor-not-allowed" title="Bạn không thể thực hiện thao tác này với chính mình">
                                            <i class="fa-solid fa-circle-user text-lg"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">
                                <i class="fa-solid fa-user-slash text-3xl mb-2 block text-gray-300"></i>
                                Không có tài khoản nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PHÂN TRANG --}}
        @if($users->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>

    {{-- =========================================================
    MODAL THÊM / SỬA TÀI KHOẢN
    ========================================================= --}}
    <div id="userModal" class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto relative">
            {{-- Header Modal --}}
            <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 flex justify-between items-center z-10">
                <h3 class="text-lg font-bold text-gray-800" id="userModalTitle">Thêm Tài Khoản Mới</h3>
                <button type="button" onclick="closeModal('userModal')" class="text-gray-400 hover:text-red-500 text-xl"><i
                        class="fa-solid fa-xmark"></i></button>
            </div>

            {{-- Form --}}
            <form id="userForm" method="POST" action="{{ route('admin.users.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="userFormMethod" name="_method" value="POST">
                <input type="hidden" id="userId" name="user_id" value="">
                <input type="hidden" id="inp_version" name="version" value="">

                {{-- Họ tên --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fa-solid fa-user mr-1 text-blue-500"></i> Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="full_name" id="inp_full_name" required maxlength="50"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        placeholder="Nguyễn Văn A">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fa-solid fa-envelope mr-1 text-green-500"></i> Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="inp_email" required maxlength="100"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        placeholder="example@gmail.com">
                </div>

                {{-- Mật khẩu --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fa-solid fa-lock mr-1 text-red-500"></i> Mật khẩu <span id="pwdRequired"
                            class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="inp_password" minlength="6"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        placeholder="Tối thiểu 6 ký tự">
                    <p id="pwdHint" class="text-xs text-gray-400 mt-1 hidden">Để trống nếu không muốn đổi mật khẩu.</p>
                </div>

                {{-- Vai trò + Hạng thành viên --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fa-solid fa-shield-halved mr-1 text-purple-500"></i> Vai trò
                        </label>
                        <select name="role_id" id="inp_role_id"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fa-solid fa-crown mr-1 text-yellow-500"></i> Hạng thành viên
                        </label>
                        <select name="member_tier" id="inp_member_tier"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                            <option value="Dong">Đồng</option>
                            <option value="Bac">Bạc</option>
                            <option value="Vang">Vàng</option>
                        </select>
                    </div>
                </div>

                {{-- Trạng thái --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <i class="fa-solid fa-toggle-on mr-1 text-teal-500"></i> Trạng thái
                    </label>
                    <select name="status" id="inp_status"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <option value="Active">Hoạt động</option>
                        <option value="Banned">Khóa tài khoản</option>
                    </select>
                </div>

                {{-- Nút Submit --}}
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                    <button type="button" onclick="closeModal('userModal')"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition">
                        Hủy
                    </button>
                    <button type="submit" id="userSubmitBtn"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold transition shadow-md flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> <span id="userSubmitText">Tạo Tài Khoản</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /**
         * Mở modal Thêm mới (reset toàn bộ form)
         */
        function openCreateUserModal() {
            document.getElementById('userModalTitle').textContent = 'Thêm Tài Khoản Mới';
            document.getElementById('userSubmitText').textContent = 'Tạo Tài Khoản';
            document.getElementById('userForm').action = '{{ route("admin.users.store") }}';
            document.getElementById('userFormMethod').value = 'POST';
            document.getElementById('userId').value = '';
            document.getElementById('inp_version').value = '';
            document.getElementById('inp_full_name').value = '';
            document.getElementById('inp_email').value = '';
            document.getElementById('inp_password').value = '';
            document.getElementById('inp_role_id').value = '3';
            document.getElementById('inp_member_tier').value = 'Dong';
            document.getElementById('inp_status').value = 'Active';
            document.getElementById('inp_password').required = true;
            document.getElementById('pwdRequired').classList.remove('hidden');
            document.getElementById('pwdHint').classList.add('hidden');
            openModal('userModal');
        }

        /**
         * Mở modal Sửa (điền dữ liệu cũ vào form)
         */
        function editUser(user) {
            document.getElementById('userModalTitle').textContent = 'Chỉnh sửa: ' + user.full_name;
            document.getElementById('userSubmitText').textContent = 'Cập nhật';
            document.getElementById('userForm').action = '/admin/users/' + user.user_id;
            document.getElementById('userFormMethod').value = 'PUT';
            document.getElementById('userId').value = user.user_id;
            document.getElementById('inp_version').value = user.version; // Optimistic Locking
            document.getElementById('inp_full_name').value = user.full_name;
            document.getElementById('inp_email').value = user.email;
            document.getElementById('inp_password').value = '';
            document.getElementById('inp_role_id').value = user.role_id;
            document.getElementById('inp_member_tier').value = user.member_tier;
            document.getElementById('inp_status').value = user.status;
            document.getElementById('inp_password').required = false;
            document.getElementById('pwdRequired').classList.add('hidden');
            document.getElementById('pwdHint').classList.remove('hidden');
            openModal('userModal');
        }
    </script>
@endpush