@extends('admin.layouts.master')

@section('title', 'Chi tiết Khách hàng - ' . $customer->full_name)

@section('content')
@php
    $tier = ucfirst(strtolower(trim($customer->member_tier ?: 'Dong')));
    $tierClasses = [
        'Vang' => [
            'bg' => 'bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-600 text-white shadow-lg shadow-amber-500/20 border border-amber-300/40',
            'name' => 'VÀNG',
            'icon' => 'fa-solid fa-crown text-white',
            'avatar_ring' => 'ring-4 ring-amber-400 ring-offset-4 ring-offset-white shadow-xl shadow-amber-500/10',
            'text_color' => 'text-amber-600',
            'bg_light' => 'bg-amber-50 border border-amber-100',
            'badge' => 'bg-amber-500/10 text-amber-700 border border-amber-200/50',
            'label' => 'Thành viên Vàng',
            'desc' => 'Khách hàng VIP vàng với nhiều ưu đãi đặc quyền.'
        ],
        'Bac' => [
            'bg' => 'bg-gradient-to-r from-slate-300 via-slate-400 to-slate-500 text-white shadow-lg shadow-slate-400/20 border border-slate-200/40',
            'name' => 'BẠC',
            'icon' => 'fa-solid fa-medal text-white',
            'avatar_ring' => 'ring-4 ring-slate-300 ring-offset-4 ring-offset-white shadow-xl shadow-slate-400/10',
            'text_color' => 'text-slate-600',
            'bg_light' => 'bg-slate-50 border border-slate-150',
            'badge' => 'bg-slate-500/10 text-slate-700 border border-slate-200/50',
            'label' => 'Thành viên Bạc',
            'desc' => 'Khách hàng VIP bạc với các ưu đãi sinh nhật.'
        ],
        'Dong' => [
            'bg' => 'bg-gradient-to-r from-orange-400 via-amber-600 to-amber-700 text-white shadow-lg shadow-orange-600/15 border border-orange-500/30',
            'name' => 'ĐỒNG',
            'icon' => 'fa-solid fa-award text-white',
            'avatar_ring' => 'ring-4 ring-orange-300 ring-offset-4 ring-offset-white shadow-xl shadow-orange-500/5',
            'text_color' => 'text-amber-800',
            'bg_light' => 'bg-orange-50/30 border border-orange-100/50',
            'badge' => 'bg-orange-500/10 text-orange-700 border border-orange-250/30',
            'label' => 'Thành viên Đồng',
            'desc' => 'Khách hàng thành viên hạng phổ thông.'
        ],
    ][$tier] ?? [
        'bg' => 'bg-gray-100 text-gray-700',
        'name' => 'CHƯA PHÂN HẠNG',
        'icon' => 'fa-solid fa-user',
        'avatar_ring' => 'ring-4 ring-gray-200 ring-offset-4 ring-offset-white',
        'text_color' => 'text-gray-500',
        'bg_light' => 'bg-gray-50',
        'badge' => 'bg-gray-150 text-gray-700 border border-gray-200',
        'label' => 'Thành viên mới',
        'desc' => 'Khách hàng mới đăng ký tài khoản.'
    ];
@endphp

<div class="space-y-8">
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.customers.index') }}" class="w-11 h-11 flex items-center justify-center rounded-xl bg-white shadow-sm border border-slate-100 hover:bg-slate-50 hover:text-slate-800 transition text-slate-500">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Hồ sơ khách hàng</h1>
                <p class="text-sm text-slate-500">Xem thông tin chi tiết và lịch sử hoạt động của thành viên</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.customers.edit', $customer->user_id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/15 hover:shadow-indigo-600/25 transition flex items-center gap-2 text-sm">
                <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa
            </a>
            @if(in_array(Auth::user()->role_id, [1, 2]))
                <form id="delete-form-{{ $customer->user_id }}" action="{{ route('admin.customers.destroy', $customer->user_id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="confirmDelete('{{ $customer->user_id }}', '{{ e($customer->full_name) }}')" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 px-5 py-2.5 rounded-xl transition flex items-center gap-2 text-sm font-semibold">
                        <i class="fa-solid fa-trash"></i> Xóa tạm
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Cột trái: Thẻ hồ sơ thành viên --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative">
                {{-- Decorative banner background --}}
                <div class="h-28 bg-gradient-to-r from-slate-100 to-slate-200/60 relative">
                    <span class="absolute top-4 right-4 bg-white/80 backdrop-blur px-2.5 py-1 rounded-lg text-[10px] font-black text-slate-500 tracking-wider">
                        ID: #{{ $customer->user_id }}
                    </span>
                </div>
                
                {{-- Profile Main Info --}}
                <div class="px-8 pb-8 pt-0 text-center -mt-14 relative z-10">
                    <div class="relative w-28 h-28 mx-auto mb-4">
                        <div class="w-28 h-28 rounded-full bg-slate-100 flex items-center justify-center text-slate-800 text-4xl font-extrabold {{ $tierClasses['avatar_ring'] }}">
                            {{ strtoupper(substr($customer->full_name, 0, 1)) }}
                        </div>
                        <div class="absolute bottom-0 right-0 w-9 h-9 rounded-full {{ $tierClasses['bg'] }} flex items-center justify-center border-4 border-white text-xs shadow-md">
                            <i class="{{ $tierClasses['icon'] }}"></i>
                        </div>
                    </div>
                    
                    <h2 class="text-xl font-bold text-slate-800 leading-snug mb-1">{{ $customer->full_name }}</h2>
                    <p class="text-sm text-slate-400 mb-4">{{ $customer->email }}</p>
                    
                    <div class="flex justify-center gap-2 mb-6">
                        @if($customer->status == 'Active')
                            <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1 rounded-full border border-emerald-100 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Hoạt động
                            </span>
                        @else
                            <span class="bg-rose-50 text-rose-700 text-xs font-bold px-3 py-1 rounded-full border border-rose-100 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                Khóa
                            </span>
                        @endif
                        
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold tracking-wider {{ $tierClasses['bg'] }}">
                            <i class="{{ $tierClasses['icon'] }} text-[9px] mr-0.5"></i> Hạng {{ $tierClasses['name'] }}
                        </span>
                    </div>

                    {{-- Premium Member Status Card --}}
                    <div class="p-4 rounded-2xl text-left {{ $tierClasses['bg_light'] }} mb-6">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="w-6 h-6 rounded-lg {{ $tierClasses['bg'] }} flex items-center justify-center text-xs">
                                <i class="{{ $tierClasses['icon'] }}"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-700">{{ $tierClasses['label'] }}</span>
                        </div>
                        <p class="text-xs text-slate-500">{{ $tierClasses['desc'] }}</p>
                    </div>

                    {{-- Detail List --}}
                    <div class="space-y-4 border-t border-slate-100 pt-6 text-left">
                        <div class="flex items-start justify-between text-sm">
                            <span class="text-slate-400 flex items-center"><i class="fa-solid fa-phone text-slate-400 mr-3 mt-0.5 w-4"></i> Điện thoại</span>
                            <span class="text-slate-700 font-semibold text-right">{{ $customer->phone_number ?? 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="flex items-start justify-between text-sm">
                            <span class="text-slate-400 flex items-center"><i class="fa-solid fa-location-dot text-slate-400 mr-3 mt-0.5 w-4"></i> Địa chỉ mặc định</span>
                            <span class="text-slate-700 font-semibold text-right max-w-[180px] break-words">{{ $customer->address ?? 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="flex items-start justify-between text-sm">
                            <span class="text-slate-400 flex items-center"><i class="fa-solid fa-calendar-days text-slate-400 mr-3 mt-0.5 w-4"></i> Ngày tham gia</span>
                            <span class="text-slate-700 font-semibold text-right">{{ $customer->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Thống kê mua hàng --}}
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider mb-6">Thống kê mua hàng</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-emerald-50/30 border border-emerald-100/50 flex flex-col justify-between min-h-[90px] hover:shadow-md hover:shadow-emerald-500/5 transition duration-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-emerald-600 font-extrabold uppercase tracking-wider">Chi tiêu</span>
                            <i class="fa-solid fa-wallet text-emerald-500 text-xs"></i>
                        </div>
                        <div class="text-lg font-black text-emerald-950 mt-2">{{ number_format($totalSpent) }}đ</div>
                    </div>
                    
                    <div class="p-4 rounded-2xl bg-indigo-50/30 border border-indigo-100/50 flex flex-col justify-between min-h-[90px] hover:shadow-md hover:shadow-indigo-500/5 transition duration-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-indigo-600 font-extrabold uppercase tracking-wider">Đơn hàng</span>
                            <i class="fa-solid fa-box-open text-indigo-500 text-xs"></i>
                        </div>
                        <div class="text-lg font-black text-indigo-950 mt-2">{{ $orderCount }} đơn</div>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-50/30 border border-amber-100/50 flex flex-col justify-between min-h-[90px] hover:shadow-md hover:shadow-amber-500/5 transition duration-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-amber-600 font-extrabold uppercase tracking-wider">Điểm thưởng</span>
                            <i class="fa-solid fa-coins text-amber-500 text-xs"></i>
                        </div>
                        <div class="text-lg font-black text-amber-950 mt-2">{{ number_format($pointBalance) }}</div>
                    </div>

                    <div class="p-4 rounded-2xl bg-sky-50/30 border border-sky-100/50 flex flex-col justify-between min-h-[90px] hover:shadow-md hover:shadow-sky-500/5 transition duration-300">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-sky-600 font-extrabold uppercase tracking-wider">Bài viết</span>
                            <i class="fa-solid fa-file-lines text-sky-500 text-xs"></i>
                        </div>
                        <div class="text-lg font-black text-sky-950 mt-2">{{ $customer->articles_count ?? 0 }} bài</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột phải: Lịch sử và hoạt động --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="border-b border-slate-100 bg-slate-50/50 px-2">
                    <nav class="flex gap-2">
                        <button onclick="switchTab('orders')" id="tab-orders" class="tab-btn px-6 py-4 text-xs font-black uppercase tracking-wider border-b-2 border-indigo-600 text-indigo-600 transition-all duration-300">
                            <i class="fa-solid fa-clock-rotate-left mr-1.5"></i> Lịch sử đơn hàng
                        </button>
                        <button onclick="switchTab('addresses')" id="tab-addresses" class="tab-btn px-6 py-4 text-xs font-bold uppercase tracking-wider border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all duration-300">
                            <i class="fa-solid fa-map-location-dot mr-1.5"></i> Sổ địa chỉ
                        </button>
                        <button onclick="switchTab('points')" id="tab-points" class="tab-btn px-6 py-4 text-xs font-bold uppercase tracking-wider border-b-2 border-transparent text-slate-400 hover:text-slate-600 transition-all duration-300">
                            <i class="fa-solid fa-award mr-1.5"></i> Điểm thưởng
                        </button>
                    </nav>
                </div>
                
                <div class="p-6 sm:p-8">
                    {{-- Tab: Đơn hàng --}}
                    <div id="content-orders" class="tab-content transition-all duration-300">
                        <div class="overflow-x-auto -mx-6 sm:-mx-8">
                            <div class="inline-block min-w-full align-middle px-6 sm:px-8">
                                <table class="min-w-full divide-y divide-slate-100">
                                    <thead>
                                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-left">
                                            <th scope="col" class="pb-4">Mã đơn</th>
                                            <th scope="col" class="pb-4">Ngày mua</th>
                                            <th scope="col" class="pb-4">Trạng thái</th>
                                            <th scope="col" class="pb-4 text-right">Tổng tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse($customer->orders->take(10) as $order)
                                            <tr class="hover:bg-slate-50/50 transition">
                                                <td class="py-4 font-bold text-slate-800 text-sm">#{{ $order->order_id }}</td>
                                                <td class="py-4 text-sm text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                                <td class="py-4">
                                                    @if($order->status == 'Completed')
                                                        <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-emerald-100/50">
                                                            Hoàn tất
                                                        </span>
                                                    @elseif($order->status == 'Pending')
                                                        <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-lg border border-amber-100/50">
                                                            Chờ xử lý
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 bg-slate-50 text-slate-600 text-xs font-bold px-2.5 py-1 rounded-lg border border-slate-150">
                                                            {{ $order->status }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="py-4 text-sm font-black text-slate-800 text-right">{{ number_format($order->total_amount) }}đ</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-12 text-center text-slate-400 italic text-sm">
                                                    <i class="fa-solid fa-box-open text-2xl mb-3 block text-slate-300"></i>
                                                    Khách hàng chưa có đơn hàng nào.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($customer->orders->count() > 10)
                            <div class="mt-6 text-center border-t border-slate-50 pt-4">
                                <a href="#" class="inline-flex items-center gap-1.5 text-xs font-black text-indigo-600 hover:text-indigo-700 uppercase tracking-wider">
                                    Xem tất cả {{ $customer->orders->count() }} đơn hàng <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Tab: Địa chỉ --}}
                    <div id="content-addresses" class="tab-content hidden transition-all duration-300">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($customer->addresses as $address)
                                <div class="p-5 rounded-2xl border transition duration-300 flex flex-col justify-between min-h-[140px] {{ $address->is_default ? 'border-indigo-200 bg-indigo-50/10' : 'border-slate-100 bg-white hover:border-slate-200 shadow-sm' }} relative">
                                    <div>
                                        <div class="flex items-center justify-between gap-2 mb-2">
                                            <span class="font-bold text-slate-800 text-sm flex items-center gap-1.5">
                                                <i class="fa-regular fa-user text-slate-400 text-xs"></i> {{ $address->receiver_name }}
                                            </span>
                                            @if($address->is_default)
                                                <span class="bg-indigo-600 text-white text-[8px] font-black px-2 py-0.5 rounded-md uppercase tracking-wider shadow shadow-indigo-600/10">Mặc định</span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 mb-3 flex items-center gap-1.5">
                                            <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i> {{ $address->receiver_phone }}
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-650 leading-relaxed border-t border-slate-100/50 pt-3">
                                        <i class="fa-solid fa-location-dot text-slate-400 mr-1 text-[10px]"></i> {{ $address->full_address }}
                                    </p>
                                </div>
                            @empty
                                <div class="col-span-2 text-center text-slate-400 py-12 italic text-sm">
                                    <i class="fa-solid fa-map-location-dot text-2xl mb-3 block text-slate-300"></i>
                                    Chưa có thông tin sổ địa chỉ của khách hàng này.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Tab: Điểm thưởng --}}
                    <div id="content-points" class="tab-content hidden transition-all duration-300">
                        <div class="overflow-x-auto -mx-6 sm:-mx-8">
                            <div class="inline-block min-w-full align-middle px-6 sm:px-8">
                                <table class="min-w-full divide-y divide-slate-100">
                                    <thead>
                                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest text-left">
                                            <th scope="col" class="pb-4">Ngày giao dịch</th>
                                            <th scope="col" class="pb-4">Nội dung / Mô tả</th>
                                            <th scope="col" class="pb-4 text-right">Biến động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse($customer->rewardPoints->take(10) as $point)
                                            <tr class="hover:bg-slate-50/50 transition">
                                                <td class="py-4 text-sm text-slate-500">{{ $point->created_at->format('d/m/Y') }}</td>
                                                <td class="py-4 text-sm text-slate-800 font-medium">{{ $point->description }}</td>
                                                <td class="py-4 text-right font-black text-sm {{ $point->points >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                    {{ $point->points >= 0 ? '+' : '' }}{{ number_format($point->points) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="py-12 text-center text-slate-400 italic text-sm">
                                                    <i class="fa-solid fa-coins text-2xl mb-3 block text-slate-300"></i>
                                                    Chưa có lịch sử tích điểm hoặc đổi thưởng.
                                                </td>
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
</div>

<script>
    function switchTab(tab) {
        // Ẩn tất cả content với animation
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.add('hidden');
        });
        // Hiện content được chọn
        const activeContent = document.getElementById('content-' + tab);
        activeContent.classList.remove('hidden');
        
        // Reset style tất cả button
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-indigo-600', 'font-black', 'border-indigo-600');
            btn.classList.add('text-slate-400', 'font-bold', 'border-transparent');
        });
        
        // Active style cho button được chọn
        const activeBtn = document.getElementById('tab-' + tab);
        activeBtn.classList.remove('text-slate-400', 'font-bold', 'border-transparent');
        activeBtn.classList.add('text-indigo-600', 'font-black', 'border-indigo-600');
    }

    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Xác nhận xóa tạm?',
            html: `Hành động này sẽ đưa khách hàng <strong class="text-rose-600">${name}</strong> vào danh sách thùng rác và tạm ngưng truy cập.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy',
            reverseButtons: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            background: '#fff',
            customClass: {
                popup: 'rounded-2xl shadow-lg border border-slate-100',
                confirmButton: 'px-5 py-2.5 text-sm font-semibold rounded-xl',
                cancelButton: 'px-5 py-2.5 text-sm font-semibold rounded-xl border border-slate-200'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(`delete-form-${id}`).submit();
            }
        });
    }
</script>
@endsection
