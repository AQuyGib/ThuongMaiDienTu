@extends('admin.layouts.master')

@section('title', 'Sửa hợp đồng trả góp #' . $installment->installment_code)

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-black text-slate-800 mb-1">Sửa hợp đồng trả góp</h1>
            <p class="text-sm text-slate-500 mb-0">Chỉnh sửa thông tin hợp đồng trả góp mã số <strong>#{{ $installment->installment_code }}</strong>.</p>
        </div>
        <a href="{{ route('admin.installments.show', $installment->id) }}" class="btn btn-outline-secondary rounded-xl text-sm font-semibold px-3">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại chi tiết
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger rounded-xl mb-4 border-0 shadow-sm text-sm">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-xl mb-4 border-0 shadow-sm text-sm">
            <div class="font-bold mb-1"><i class="fa-solid fa-circle-exclamation me-2"></i> Lỗi nhập liệu:</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.installments.update', $installment->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            <!-- Left Side: Fields -->
            <div class="col-lg-8">
                <!-- Customer Details -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 mb-4">
                    <h4 class="h6 font-bold text-slate-700 mb-3"><i class="fa-solid fa-user-pen me-2 text-indigo-500"></i> Thông tin khách hàng</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Họ và tên khách hàng <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_name" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" value="{{ old('customer_name', $installment->customer_name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số điện thoại <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_phone" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" value="{{ old('customer_phone', $installment->customer_phone) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số CMND/CCCD (12 số)</label>
                            <input type="text" name="customer_id_card" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" value="{{ old('customer_id_card', $installment->customer_id_card) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Địa chỉ giao hàng <span class="text-rose-500">*</span></label>
                            <input type="text" name="shipping_address" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" placeholder="Nhập địa chỉ giao hàng" value="{{ old('shipping_address', $installment->order->shipping_address ?? '') }}" required>
                        </div>
                    </div>
                </div>

                <!-- Financial Details -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 mb-4">
                    <h4 class="h6 font-bold text-slate-700 mb-3"><i class="fa-solid fa-sack-dollar me-2 text-indigo-500"></i> Điều chỉnh khoản trả trước</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số tiền sản phẩm</label>
                            <input type="text" class="form-control bg-slate-50 border-slate-200 py-2 rounded-xl text-sm font-bold" value="{{ number_format($installment->product_price, 0, ',', '.') }}đ" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số tiền trả trước hiện tại (đ) <span class="text-rose-500">*</span></label>
                            <input type="number" name="prepay_amount" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm font-bold" value="{{ old('prepay_amount', $installment->prepay_amount) }}" required>
                        </div>
                    </div>
                    <div class="alert alert-warning rounded-xl border-0 bg-amber-50 text-amber-800 text-xs mt-3 mb-0">
                        <i class="fa-solid fa-circle-info me-2"></i> Lưu ý: Khi sửa số tiền trả trước, hệ thống sẽ tự động tính toán lại khoản vay, số tiền góp hàng tháng và chênh lệch chênh lệch.
                    </div>
                </div>
            </div>

            <!-- Right Side: Status Control -->
            <div class="col-lg-4">
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <h4 class="h6 font-bold text-slate-700 mb-3"><i class="fa-solid fa-shield-halved me-2 text-indigo-500"></i> Kiểm soát & Trạng thái</h4>
                    
                    <div class="mb-3">
                        <label class="form-label text-slate-500 text-xs font-bold uppercase">Trạng thái hợp đồng <span class="text-rose-500">*</span></label>
                        <select name="status" class="form-select border-slate-200 py-2 rounded-xl text-sm" required>
                            <option value="Pending_Approval" {{ $installment->status === 'Pending_Approval' ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="Approved" {{ $installment->status === 'Approved' ? 'selected' : '' }}>Đã duyệt</option>
                            <option value="Rejected" {{ $installment->status === 'Rejected' ? 'selected' : '' }}>Từ chối</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-slate-500 text-xs font-bold uppercase">Ghi chú / Lý do từ chối</label>
                        <textarea name="notes" rows="4" class="form-control border-slate-200 rounded-xl text-sm" placeholder="Nhập ghi chú hoặc lý do nếu từ chối hồ sơ...">{{ old('notes', $installment->notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn w-100 py-2.5 rounded-xl font-bold text-sm shadow-sm" style="background-color: #0046ab; color: white;">
                        <i class="fa-solid fa-floppy-disk me-2"></i> CẬP NHẬT HỢP ĐỒNG
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
