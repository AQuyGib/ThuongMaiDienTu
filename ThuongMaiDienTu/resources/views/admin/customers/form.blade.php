@extends('admin.layouts.master')

@section('title', $title)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('admin.customers.index') }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-gray-500 shadow-sm border border-gray-100 hover:bg-gray-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $title }}</h1>
    </div>

    <form action="{{ $customer->user_id ? route('admin.customers.update', $customer->user_id) : route('admin.customers.store') }}" 
          method="POST" class="space-y-6">
        @csrf
        @if($customer->user_id)
            @method('PUT')
            <input type="hidden" name="version" value="{{ $customer->version }}">
        @else
            <input type="hidden" name="version" value="1">
        @endif

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Họ tên -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Họ và tên <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="full_name" value="{{ old('full_name', $customer->full_name) }}" required
                               maxlength="50"
                               class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                               style="padding-left: 2.75rem;"
                               placeholder="Nhập họ và tên khách hàng">
                    </div>
                    @error('full_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Email <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}" required
                               maxlength="100"
                               class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                               style="padding-left: 2.75rem;"
                               placeholder="example@gmail.com">
                    </div>
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Số điện thoại -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Số điện thoại</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-phone"></i></span>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $customer->phone_number) }}"
                               maxlength="10"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                               style="padding-left: 2.75rem;"
                               placeholder="0987xxxxxx">
                    </div>
                    @error('phone_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Mật khẩu -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                        Mật khẩu {{ $customer->user_id ? '(Để trống nếu không đổi)' : '*' }}
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" {{ $customer->user_id ? '' : 'required' }}
                               maxlength="255"
                               class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-medium text-slate-700"
                               style="padding-left: 2.75rem;"
                               placeholder="••••••••">
                    </div>
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Trạng thái -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Trạng thái <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><i class="fa-solid fa-circle-info"></i></span>
                        <select name="status" required
                                class="w-full pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-primary/10 focus:border-primary transition font-semibold text-slate-700 appearance-none"
                                style="padding-left: 2.75rem;">
                            <option value="Active" {{ old('status', $customer->status) == 'Active' ? 'selected' : '' }}>Đang hoạt động</option>
                            <option value="Banned" {{ old('status', $customer->status) == 'Banned' ? 'selected' : '' }}>Khóa tài khoản</option>
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
                                  placeholder="Nhập địa chỉ khách hàng">{{ old('address', $customer->address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.customers.index') }}" class="px-8 py-3 rounded-xl border border-gray-200 font-bold text-gray-600 hover:bg-gray-50 transition">Hủy</a>
            <button type="submit" class="px-8 py-3 rounded-xl bg-gray-800 text-white font-bold hover:bg-black transition shadow-lg shadow-gray-200">
                {{ $customer->user_id ? 'Cập nhật' : 'Thêm mới' }}
            </a>
        </div>
    </form>
</div>
@endsection
