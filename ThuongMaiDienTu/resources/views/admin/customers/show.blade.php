@extends('layouts.app')

@section('title', 'Chi tiết Khách hàng - ' . $customer->full_name)

@section('content')
<div class="container py-8">
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.customers.index') }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-white shadow-sm border border-gray-100 hover:bg-gray-50 transition text-gray-500">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Hồ sơ Khách hàng</h1>
                <p class="text-sm text-gray-500">Quản lý và xem lịch sử của khách hàng #{{ $customer->user_id }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.customers.edit', $customer->user_id) }}" class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fa-solid fa-edit"></i> Chỉnh sửa
            </a>
            @if(in_array(Auth::user()->role_id, [1, 2]))
                <form action="{{ route('admin.customers.destroy', $customer->user_id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tạm khách hàng này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-50 text-red-600 border border-red-100 px-5 py-2 rounded-lg hover:bg-red-100 transition flex items-center gap-2">
                        <i class="fa-solid fa-trash"></i> Xóa tạm
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cột trái: Thông tin cá nhân -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 text-center border-b border-gray-50">
                    <div class="w-24 h-24 rounded-full bg-red-100 mx-auto flex items-center justify-center text-primary text-3xl font-bold mb-4">
                        {{ strtoupper(substr($customer->full_name, 0, 1)) }}
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $customer->full_name }}</h2>
                    <p class="text-sm text-gray-400 mb-4">{{ $customer->email }}</p>
                    <div class="flex justify-center gap-2">
                        @if($customer->status == 'Active')
                            <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Đang hoạt động</span>
                        @else
                            <span class="bg-red-100 text-red-700 text-xs font-bold px-3 py-1 rounded-full">Bị khóa</span>
                        @endif
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full">Hạng {{ $customer->member_tier ?? 'Đồng' }}</span>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400"><i class="fa-solid fa-phone mr-2"></i> Điện thoại:</span>
                        <span class="text-gray-800 font-medium">{{ $customer->phone_number ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400"><i class="fa-solid fa-location-dot mr-2"></i> Địa chỉ mặc định:</span>
                        <span class="text-gray-800 font-medium text-right">{{ $customer->address ?? 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400"><i class="fa-solid fa-calendar mr-2"></i> Ngày gia nhập:</span>
                        <span class="text-gray-800 font-medium">{{ $customer->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-6">Thống kê mua hàng</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="text-2xl font-bold text-gray-800">{{ number_format($totalSpent) }}đ</div>
                        <div class="text-xs text-gray-500">Tổng chi tiêu</div>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <div class="text-2xl font-bold text-gray-800">{{ $orderCount }}</div>
                        <div class="text-xs text-gray-500">Đơn hàng</div>
                    </div>
                    <div class="p-4 rounded-xl bg-orange-50 border border-orange-100">
                        <div class="text-2xl font-bold text-orange-600">{{ number_format($pointBalance) }}</div>
                        <div class="text-xs text-orange-500">Điểm thưởng</div>
                    </div>
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-100">
                        <div class="text-2xl font-bold text-blue-600">{{ $customer->articles_count ?? 0 }}</div>
                        <div class="text-xs text-blue-500">Bài viết</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải: Tabs chi tiết -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100">
                    <nav class="flex">
                        <button onclick="switchTab('orders')" id="tab-orders" class="tab-btn px-6 py-4 text-sm font-bold border-b-2 border-primary text-primary">Lịch sử đơn hàng</button>
                        <button onclick="switchTab('addresses')" id="tab-addresses" class="tab-btn px-6 py-4 text-sm font-medium text-gray-400 hover:text-gray-600 border-b-2 border-transparent">Sổ địa chỉ</button>
                        <button onclick="switchTab('points')" id="tab-points" class="tab-btn px-6 py-4 text-sm font-medium text-gray-400 hover:text-gray-600 border-b-2 border-transparent">Điểm thưởng</button>
                    </nav>
                </div>
                
                <div class="p-6">
                    <!-- Tab: Đơn hàng -->
                    <div id="content-orders" class="tab-content overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                    <th class="pb-4">Mã đơn</th>
                                    <th class="pb-4">Ngày mua</th>
                                    <th class="pb-4">Trạng thái</th>
                                    <th class="pb-4 text-right">Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($customer->orders->take(10) as $order)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="py-4 font-medium text-gray-800">#{{ $order->order_id }}</td>
                                        <td class="py-4 text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="py-4">
                                            <span class="text-xs font-bold px-2 py-1 rounded-full 
                                                @if($order->status == 'Completed') bg-green-100 text-green-700
                                                @elseif($order->status == 'Pending') bg-yellow-100 text-yellow-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-sm font-bold text-gray-800 text-right">{{ number_format($order->total_amount) }}đ</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-10 text-center text-gray-400 italic">Khách hàng chưa có đơn hàng nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if($customer->orders->count() > 10)
                            <div class="mt-4 text-center">
                                <a href="#" class="text-sm font-bold text-primary hover:underline">Xem tất cả {{ $customer->orders->count() }} đơn hàng</a>
                            </div>
                        @endif
                    </div>

                    <!-- Tab: Địa chỉ -->
                    <div id="content-addresses" class="tab-content hidden space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($customer->addresses as $address)
                                <div class="p-4 rounded-xl border {{ $address->is_default ? 'border-primary bg-red-50/10' : 'border-gray-100 bg-white' }} relative">
                                    @if($address->is_default)
                                        <span class="absolute top-2 right-2 bg-primary text-white text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Mặc định</span>
                                    @endif
                                    <div class="font-bold text-sm text-gray-800">{{ $address->receiver_name }}</div>
                                    <div class="text-xs text-gray-500 mb-2">{{ $address->receiver_phone }}</div>
                                    <div class="text-sm text-gray-600">{{ $address->full_address }}</div>
                                </div>
                            @empty
                                <div class="col-span-2 text-center text-gray-400 py-6 italic text-sm">Chưa có thông tin sổ địa chỉ.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab: Điểm thưởng -->
                    <div id="content-points" class="tab-content hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                                        <th class="pb-4">Ngày</th>
                                        <th class="pb-4">Nội dung</th>
                                        <th class="pb-4 text-right">Số điểm</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @forelse($customer->rewardPoints->take(10) as $point)
                                        <tr>
                                            <td class="py-4 text-sm text-gray-500">{{ $point->created_at->format('d/m/Y') }}</td>
                                            <td class="py-4 text-sm text-gray-800">{{ $point->description }}</td>
                                            <td class="py-4 text-right font-bold {{ $point->points >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $point->points >= 0 ? '+' : '' }}{{ number_format($point->points) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-10 text-center text-gray-400 italic">Chưa có lịch sử điểm thưởng.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        // Ẩn tất cả content
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        // Hiện content được chọn
        document.getElementById('content-' + tab).classList.remove('hidden');
        
        // Reset style tất cả button
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-primary', 'font-bold', 'border-primary');
            btn.classList.add('text-gray-400', 'font-medium', 'border-transparent');
        });
        
        // Active style cho button được chọn
        const activeBtn = document.getElementById('tab-' + tab);
        activeBtn.classList.remove('text-gray-400', 'font-medium', 'border-transparent');
        activeBtn.classList.add('text-primary', 'font-bold', 'border-primary');
    }
</script>
@endsection

