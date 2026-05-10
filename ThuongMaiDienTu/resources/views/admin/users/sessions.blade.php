@extends('admin.layouts.master')

@section('title', 'Quản lý thiết bị - ' . $user->full_name)
@section('page-title', 'Quản lý thiết bị: ' . $user->full_name)

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="flex items-center text-gray-600 hover:text-blue-600 transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Trở về danh sách tài khoản
        </a>
        
        @if(count($sessions) > 0)
            <form action="{{ route('admin.users.revoke', $user->user_id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn đăng xuất TẤT CẢ các thiết bị của tài khoản này?')">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center gap-2 shadow-sm font-bold">
                    <i class="fa-solid fa-power-off"></i> Đăng xuất tất cả thiết bị
                </button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($sessions as $session)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center 
                        @if($session->device == 'Máy tính') bg-blue-50 text-blue-600 @else bg-purple-50 text-purple-600 @endif text-xl">
                        @if($session->device == 'Máy tính')
                            <i class="fa-solid fa-desktop"></i>
                        @else
                            <i class="fa-solid fa-mobile-screen"></i>
                        @endif
                    </div>
                    
                    <form action="{{ route('admin.users.sessions.destroy', $session->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn buộc thoát thiết bị này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-lg hover:bg-red-50" title="Đăng xuất thiết bị này">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </form>
                </div>

                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-500 font-medium">Trình duyệt & Hệ điều hành</div>
                        <div class="font-bold text-gray-800">{{ $session->browser }} on {{ $session->os }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500 font-medium">Địa chỉ IP</div>
                        <div class="font-mono text-xs bg-gray-50 px-2 py-1 rounded inline-block text-gray-600">{{ $session->ip_address }}</div>
                    </div>

                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-400">
                            <i class="fa-regular fa-clock mr-1"></i> Hoạt động {{ $session->last_active }}
                        </span>
                        @if($session->id === session()->getId())
                            <span class="bg-green-100 text-green-700 text-[10px] font-extrabold uppercase px-2 py-1 rounded-full">Hiện tại</span>
                        @endif
                    </div>
                </div>

                {{-- User Agent raw (ẩn đi, hiện khi hover hoặc click xem thêm nếu cần) --}}
                <div class="mt-4 pt-4 border-t border-gray-50 text-[10px] text-gray-400 truncate" title="{{ $session->user_agent }}">
                    {{ $session->user_agent }}
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 flex flex-col items-center justify-center bg-white rounded-2xl border-2 border-dashed border-gray-100">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 text-2xl mb-4">
                    <i class="fa-solid fa-shield-slash"></i>
                </div>
                <h3 class="text-gray-500 font-medium text-lg">Tài khoản này hiện không có thiết bị nào đăng nhập</h3>
                <p class="text-gray-400 text-sm">Các phiên làm việc sẽ xuất hiện ở đây sau khi người dùng đăng nhập.</p>
            </div>
        @endforelse
    </div>
@endsection
