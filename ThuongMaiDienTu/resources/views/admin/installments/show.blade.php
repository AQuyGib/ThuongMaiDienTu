@extends('admin.layouts.master')

@section('title', 'Chi tiết hồ sơ trả góp #' . $installment->installment_code)

@section('content')
<div class="container-fluid px-0">
    <!-- Back to list & Header -->
    <div class="mb-4">
        <a href="{{ route('admin.installments.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors mb-2">
            <i class="fa-solid fa-arrow-left-long"></i> Quay lại danh sách hồ sơ
        </a>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h1 class="h3 font-black text-slate-800 mb-1 flex items-center gap-2">
                    Hồ sơ #{{ $installment->installment_code }}
                </h1>
                <p class="text-sm text-slate-500 mb-0">Đăng ký vào {{ $installment->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if($installment->status === 'Pending_Approval')
                    <span class="px-3 py-2 rounded-xl bg-amber-50 text-amber-800 text-xs font-black uppercase tracking-wider border border-amber-200">Chờ phê duyệt</span>
                @elseif($installment->status === 'Approved')
                    <span class="px-3 py-2 rounded-xl bg-emerald-50 text-emerald-800 text-xs font-black uppercase tracking-wider border border-emerald-200">Đã phê duyệt</span>
                @elseif($installment->status === 'Rejected')
                    <span class="px-3 py-2 rounded-xl bg-rose-50 text-rose-800 text-xs font-black uppercase tracking-wider border border-rose-200">Đã từ chối</span>
                @endif
                
                <a href="{{ route('admin.installments.invoice', $installment->id) }}" target="_blank" class="btn btn-outline-info rounded-xl text-sm font-semibold px-3" style="color: #0ea5e9; border-color: #0ea5e9;">
                    <i class="fa-solid fa-print me-1"></i> Xuất hóa đơn
                </a>
                <a href="{{ route('admin.installments.edit', $installment->id) }}" class="btn btn-outline-secondary rounded-xl text-sm font-semibold px-3">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Sửa hợp đồng
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT COLUMN: Customer and Financial details -->
        <div class="col-lg-8">
            <div class="flex flex-col gap-4">
                <!-- Customer Details Panel -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-address-card text-indigo-500"></i> Thông tin khách hàng
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-xs text-slate-400 block">Họ và tên</span>
                            <span class="font-bold text-slate-700">{{ $installment->customer_name }}</span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-xs text-slate-400 block">Số điện thoại</span>
                            <span class="font-bold text-slate-700">{{ $installment->customer_phone }}</span>
                        </div>
                        @if($installment->customer_id_card)
                            <div class="col-md-6">
                                <span class="text-xs text-slate-400 block">Số CMND/CCCD</span>
                                <span class="font-bold text-slate-700">{{ $installment->customer_id_card }}</span>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <span class="text-xs text-slate-400 block">Tài khoản đăng ký</span>
                            <span class="font-bold text-slate-700">{{ $installment->order->user->email ?? 'Không có' }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-xs text-slate-400 block">Địa chỉ nhận hàng</span>
                            <span class="font-bold text-slate-700">{{ $installment->order->shipping_address ?? 'Tại cửa hàng' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Product & Order Details Panel -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-laptop text-indigo-500"></i> Thông tin sản phẩm mua
                    </h3>
                    @if($installment->order && $installment->order->details->count() > 0)
                        @foreach($installment->order->details as $detail)
                            @php 
                                $invItem = $detail->inventoryItem;
                                $product = $invItem?->variant?->product;
                                $variant = $invItem?->variant;
                            @endphp
                            <div class="flex items-center gap-3 p-3 bg-slate-50/50 rounded-2xl border border-slate-100">
                                <img src="{{ $product->thumbnail ?? 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=100' }}" style="width: 50px; height: 50px; object-fit: contain;" class="bg-white rounded-xl p-1 border">
                                <div class="flex-grow">
                                    <h4 class="text-sm font-bold text-slate-700 mb-0.5">{{ $product->name ?? 'Sản phẩm trả góp' }}</h4>
                                    <div class="flex gap-2 items-center">
                                        <span class="text-xs text-slate-400">Biến thể: <strong class="text-slate-600">{{ $variant->color ?? 'Mặc định' }} {{ $variant->rom ?? '' }}</strong></span>
                                        <span class="text-slate-300">|</span>
                                        <span class="text-xs text-slate-400">IMEI/Serial: <strong class="text-slate-600">{{ $invItem->imei_serial ?? 'Tự động cấp' }}</strong></span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="text-sm font-black text-slate-800">{{ number_format($detail->price, 0, ',', '.') }}đ</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-slate-400 text-xs text-center py-3">Không lấy được chi tiết sản phẩm.</div>
                    @endif
                </div>

                <!-- Financial Calculation Breakdown -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-calculator text-indigo-500"></i> Chi tiết khoản vay & chi phí
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <span class="text-xs text-slate-400 block">Giá trị sản phẩm</span>
                            <span class="font-extrabold text-slate-800">{{ number_format($installment->product_price, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-xs text-slate-400 block">Trả trước (Prepay)</span>
                            <span class="font-extrabold text-indigo-600 block">{{ number_format($installment->prepay_amount, 0, ',', '.') }}đ</span>
                            <span class="text-[10px] text-slate-400">(~ {{ round(($installment->prepay_amount / $installment->product_price) * 100) }}%)</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-xs text-slate-400 block">Số tiền vay (Loan)</span>
                            <span class="font-extrabold text-rose-500">{{ number_format($installment->loan_amount, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-xs text-slate-400 block">Góp mỗi tháng</span>
                            <span class="font-extrabold text-rose-500 text-base">{{ number_format($installment->monthly_payment, 0, ',', '.') }}đ</span>
                            <span class="text-[10px] text-slate-400">/tháng × {{ $installment->period }} tháng</span>
                        </div>
                        
                        <div class="col-12">
                            <div class="bg-slate-50 p-3 rounded-2xl mt-2 row g-3 text-xs border border-slate-100">
                                <div class="col-md-3">
                                    <span class="text-slate-400">Lãi suất hàng tháng:</span>
                                    <strong class="text-slate-700 block">{{ $installment->interest_rate * 100 }}% / tháng</strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-slate-400">Phí dịch vụ thu hộ:</span>
                                    <strong class="text-slate-700 block">{{ number_format($installment->service_fee, 0, ',', '.') }}đ / tháng</strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-slate-400">Tổng trả góp:</span>
                                    <strong class="text-slate-700 block">{{ number_format($installment->total_payment, 0, ',', '.') }}đ</strong>
                                </div>
                                <div class="col-md-3">
                                    <span class="text-slate-400">Chênh lệch trả thẳng:</span>
                                    <strong class="text-amber-600 block">+{{ number_format($installment->difference_amount, 0, ',', '.') }}đ</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Repayment Schedule Timeline -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-indigo-500"></i> Lịch thanh toán trả góp
                    </h3>
                    @if($installment->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm align-middle text-xs mb-0">
                                <thead>
                                    <tr class="text-slate-400">
                                        <th>Kỳ hạn</th>
                                        <th>Ngày đến hạn</th>
                                        <th class="text-end">Số tiền đóng</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-center">Ngày thanh toán</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($installment->payments as $pay)
                                        <tr>
                                            <td class="font-bold">Kỳ thứ {{ $pay->payment_no }}</td>
                                            <td>{{ $pay->due_date->format('d/m/Y') }}</td>
                                            <td class="text-end font-bold text-slate-700">{{ number_format($pay->amount, 0, ',', '.') }}đ</td>
                                            <td class="text-center">
                                                @if($pay->status === 'Unpaid')
                                                    <span class="badge bg-slate-100 text-slate-600 rounded">Chưa đóng</span>
                                                    <form action="{{ route('admin.installments.pay-month', $pay->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Xác nhận khách hàng đã nộp tiền kỳ thứ {{ $pay->payment_no }} này?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-success py-0.5 px-1.5 rounded font-bold text-[9px] uppercase tracking-wide text-white" style="background-color: #10b981; border: none;">
                                                            Đóng tiền
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-emerald-100 text-emerald-800 rounded">Đã đóng</span>
                                                @endif
                                            </td>
                                            <td class="text-center text-slate-500 font-semibold">{{ $pay->paid_at ? $pay->paid_at->format('d/m/Y') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- Preview Schedule for Pending items -->
                        <div class="p-3 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <p class="text-xs text-slate-500 font-bold mb-2 flex items-center gap-1.5"><i class="fa-solid fa-circle-info text-indigo-500"></i> Xem trước lịch biểu (khi được duyệt):</p>
                            <div class="row g-2">
                                @for($i = 1; $i <= $installment->period; $i++)
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded-xl border border-slate-100 flex justify-between items-center text-[11px]">
                                            <div>
                                                <span class="text-slate-400 block font-semibold">Tháng {{ $i }}</span>
                                                <span class="font-bold text-slate-700">{{ now()->addMonths($i)->format('d/m/Y') }}</span>
                                            </div>
                                            <span class="font-black text-rose-500">{{ number_format($installment->monthly_payment, 0, ',', '.') }}đ</span>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: AI Analysis & Manual actions -->
        <div class="col-lg-4">
            <div class="flex flex-col gap-4">
                <!-- AI Risk Scoring Analysis Panel -->
                <div class="bg-gradient-to-br from-indigo-950 via-slate-900 to-indigo-900 text-white p-4 rounded-3xl shadow-xl border border-slate-800 relative overflow-hidden">
                    <!-- AI Badge background -->
                    <div class="absolute -right-6 -bottom-6 text-indigo-500/10 text-9xl">
                        <i class="fa-solid fa-robot"></i>
                    </div>

                    <h3 class="text-xs font-black uppercase tracking-widest text-indigo-400 border-b border-indigo-900 pb-3 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-brain animate-pulse"></i> Gemini AI Credit Risk Assessment
                    </h3>

                    @if($installment->ai_risk_level)
                        <div class="flex items-center justify-between mb-4 mt-2">
                            <div>
                                <span class="text-[10px] text-slate-400 block uppercase tracking-wider">Mức độ rủi ro</span>
                                @if($installment->ai_risk_level === 'Low')
                                    <span class="text-xl font-black text-emerald-400 uppercase">Thấp (Low)</span>
                                @elseif($installment->ai_risk_level === 'Medium')
                                    <span class="text-xl font-black text-amber-400 uppercase">Vừa (Medium)</span>
                                @else
                                    <span class="text-xl font-black text-rose-400 uppercase">Cao (High)</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block uppercase tracking-wider">Điểm rủi ro</span>
                                <span class="text-3xl font-black text-white">{{ $installment->ai_risk_score }}%</span>
                            </div>
                        </div>

                        <!-- Findings and recommendations -->
                        <div class="flex flex-col gap-3 text-xs">
                            <div class="p-3 bg-white/5 rounded-2xl border border-white/5">
                                <span class="font-extrabold text-indigo-300 block mb-1">Quyết định đề xuất:</span>
                                @if($installment->ai_recommendation === 'Approve')
                                    <span class="inline-flex px-2 py-0.5 bg-emerald-500/20 text-emerald-300 rounded font-bold">Chấp thuận (Approve)</span>
                                @elseif($installment->ai_recommendation === 'Review')
                                    <span class="inline-flex px-2 py-0.5 bg-amber-500/20 text-amber-300 rounded font-bold">Xem xét thêm (Review)</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 bg-rose-500/20 text-rose-300 rounded font-bold">Từ chối (Reject)</span>
                                @endif
                            </div>

                            <div>
                                <span class="font-extrabold text-indigo-300 block mb-1">Các điểm phát hiện:</span>
                                <ul class="list-disc list-inside text-slate-300 mb-2 font-normal pl-0" style="list-style-type: disc;">
                                    @forelse($installment->ai_findings as $finding)
                                        <li class="mb-1">{{ $finding }}</li>
                                    @empty
                                        <li class="text-slate-400">Không có phát hiện đặc biệt.</li>
                                    @endforelse
                                </ul>
                            </div>

                            <div>
                                <span class="font-extrabold text-indigo-300 block mb-1">Phân tích lập luận:</span>
                                <p class="text-slate-300 leading-relaxed mb-0 font-normal">
                                    {{ $installment->ai_reasoning }}
                                </p>
                            </div>
                        </div>
                    @else
                        <!-- If AI has not analyzed yet -->
                        <div class="text-center py-4">
                            <i class="fa-solid fa-hourglass-half text-slate-400 text-3xl mb-2 block"></i>
                            <p class="text-xs text-slate-300">Chưa có dữ liệu phân tích tín dụng từ AI cho hồ sơ này.</p>
                        </div>
                    @endif
                </div>

                <!-- Admin Action Decision Panel -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <h3 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-gavel text-indigo-500"></i> Quyết định phê duyệt
                    </h3>

                    @if($installment->status === 'Pending_Approval')
                        <div class="flex flex-col gap-3 mt-2">
                            <!-- Approve form -->
                            <form action="{{ route('admin.installments.approve', $installment->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn phê duyệt hồ sơ trả góp này và khởi tạo lịch đóng tiền?')">
                                @csrf
                                <button type="submit" class="w-full btn btn-success py-2.5 font-bold rounded-2xl flex items-center justify-center gap-2 text-sm">
                                    <i class="fa-solid fa-circle-check"></i> PHÊ DUYỆT HỒ SƠ
                                </button>
                            </form>

                            <hr class="border-slate-100 my-1">

                            <!-- Reject Form -->
                            <form action="{{ route('admin.installments.reject', $installment->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="text-xs text-slate-400 font-bold mb-1 block">Lý do từ chối (Gửi tới khách hàng):</label>
                                    <textarea name="reject_reason" placeholder="Nhập lý do từ chối hồ sơ..." required class="form-control text-xs border-slate-200 focus-ring py-2 px-3 rounded-xl" rows="3"></textarea>
                                </div>
                                <button type="submit" class="w-full btn btn-danger py-2.5 font-bold rounded-2xl flex items-center justify-center gap-2 text-sm">
                                    <i class="fa-solid fa-circle-xmark"></i> TỪ CHỐI HỒ SƠ
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Locked state if already Approved or Rejected -->
                        <div class="mt-2 text-xs">
                            @if($installment->status === 'Approved')
                                <div class="p-3 bg-emerald-50 text-emerald-800 rounded-2xl border border-emerald-100 flex items-center gap-3">
                                    <i class="fa-solid fa-circle-check text-2xl text-emerald-500"></i>
                                    <div>
                                        <span class="font-extrabold block">Hồ sơ đã duyệt</span>
                                        <span class="text-slate-500">Đã kích hoạt lúc {{ $installment->approved_at ? $installment->approved_at->format('d/m/Y H:i') : '' }}</span>
                                    </div>
                                </div>
                            @elseif($installment->status === 'Rejected')
                                <div class="p-3 bg-rose-50 text-rose-800 rounded-2xl border border-rose-100">
                                    <div class="flex items-center gap-3 mb-2">
                                        <i class="fa-solid fa-circle-xmark text-2xl text-rose-500"></i>
                                        <strong>Hồ sơ đã từ chối</strong>
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        <span class="block font-bold">Lý do từ chối:</span>
                                        <span>{{ $installment->notes }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
