@extends('admin.layouts.master')

@section('title', 'Quản lý trả góp')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-black text-slate-800 mb-1">Hợp đồng trả góp</h1>
            <p class="text-sm text-slate-500 mb-0">Quản lý, thẩm định và phê duyệt các hồ sơ đăng ký trả góp của khách hàng.</p>
        </div>
        <a href="{{ route('admin.installments.create') }}" class="btn text-sm font-bold py-2.5 px-4 rounded-xl shadow-sm text-white" style="background-color: #0046ab;">
            <i class="fa-solid fa-plus me-2"></i> Tạo hợp đồng tại quầy
        </a>
    </div>

    <!-- Quick Metrics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Tổng hồ sơ</span>
                    <span class="text-3xl font-extrabold text-slate-700">
                        {{ \App\Models\Installment::count() }}
                    </span>
                </div>
                <div class="w-12 h-12 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Chờ duyệt</span>
                    <span class="text-3xl font-extrabold text-amber-500">
                        {{ \App\Models\Installment::where('status', 'Pending_Approval')->count() }}
                    </span>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center text-xl animate-pulse">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Đã duyệt</span>
                    <span class="text-3xl font-extrabold text-emerald-500">
                        {{ \App\Models\Installment::where('status', 'Approved')->count() }}
                    </span>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest block mb-1">Từ chối</span>
                    <span class="text-3xl font-extrabold text-rose-500">
                        {{ \App\Models\Installment::where('status', 'Rejected')->count() }}
                    </span>
                </div>
                <div class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center text-xl">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 mb-4">
        <form method="GET" action="{{ route('admin.installments.index') }}" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-slate-50 border-slate-200 text-slate-400"><i class="fa-solid fa-search"></i></span>
                    <input type="text" name="search" class="form-control border-slate-200 text-sm py-2 px-3 focus-ring" placeholder="Tìm tên khách hàng, số điện thoại, mã hồ sơ..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select border-slate-200 text-sm py-2 focus-ring">
                    <option value="">-- Trạng thái hợp đồng --</option>
                    <option value="Pending_Approval" {{ request('status') === 'Pending_Approval' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Bị từ chối</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="ai_risk_level" class="form-select border-slate-200 text-sm py-2 focus-ring">
                    <option value="">-- Mức rủi ro AI --</option>
                    <option value="Low" {{ request('ai_risk_level') === 'Low' ? 'selected' : '' }}>Rủi ro Thấp (Low)</option>
                    <option value="Medium" {{ request('ai_risk_level') === 'Medium' ? 'selected' : '' }}>Rủi ro Vừa (Medium)</option>
                    <option value="High" {{ request('ai_risk_level') === 'High' ? 'selected' : '' }}>Rủi ro Cao (High)</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="partner" class="form-control border-slate-200 text-sm py-2" placeholder="Đối tác tài chính..." value="{{ request('partner') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-indigo flex-fill text-sm font-semibold py-2 rounded-xl" style="background-color: #0046ab; color: white;">Lọc</button>
                <a href="{{ route('admin.installments.index') }}" class="btn btn-light border-slate-200 text-sm font-semibold py-2 px-3 rounded-xl"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-sm">
                <thead class="table-light border-bottom border-slate-100 text-slate-400 font-bold uppercase text-[11px] tracking-wider">
                    <tr>
                        <th class="ps-4 py-3">Mã hồ sơ / Ngày tạo</th>
                        <th class="py-3">Khách hàng</th>
                        <th class="py-3">Gói trả góp</th>
                        <th class="py-3 text-end">Giá trị khoản vay</th>
                        <th class="py-3 text-center">Rủi ro AI</th>
                        <th class="py-3 text-center">Trạng thái</th>
                        <th class="py-3 text-center pe-4" style="width: 100px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse($installments as $inst)
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="font-extrabold text-slate-800 block">#{{ $inst->installment_code }}</span>
                                <span class="text-xs text-slate-400">{{ $inst->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="py-3">
                                <span class="font-bold text-slate-700 block">{{ $inst->customer_name }}</span>
                                <span class="text-xs text-slate-400"><i class="fa-solid fa-phone me-1"></i>{{ $inst->customer_phone }}</span>
                            </td>
                            <td class="py-3">
                                <div class="flex items-center gap-1.5 mb-1">
                                    <span class="badge bg-slate-100 text-slate-700 border border-slate-200 rounded-pill text-[10px]">
                                        @if($inst->method === 'financial_company')
                                            Công ty tài chính
                                        @elseif($inst->method === 'credit_card')
                                            Thẻ tín dụng
                                        @else
                                            Kredivo
                                        @endif
                                    </span>
                                    <span class="text-xs font-semibold text-slate-500">{{ $inst->partner }}</span>
                                </div>
                                <span class="text-xs text-slate-400">Kỳ hạn: <strong class="text-slate-600">{{ $inst->period }} tháng</strong> | Góp: <strong class="text-rose-500">{{ number_format($inst->monthly_payment, 0, ',', '.') }}đ/tháng</strong></span>
                            </td>
                            <td class="py-3 text-end font-bold text-slate-700">
                                <div>{{ number_format($inst->loan_amount, 0, ',', '.') }}đ</div>
                                <div class="text-[10px] text-slate-400 font-normal">Sản phẩm: {{ number_format($inst->product_price, 0, ',', '.') }}đ</div>
                            </td>
                            <td class="py-3 text-center">
                                @if($inst->ai_risk_level === 'Low')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full font-bold text-xs border border-emerald-100">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                        {{ $inst->ai_risk_score }}% Thấp
                                    </span>
                                @elseif($inst->ai_risk_level === 'Medium')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full font-bold text-xs border border-amber-100">
                                        <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                        {{ $inst->ai_risk_score }}% Vừa
                                    </span>
                                @elseif($inst->ai_risk_level === 'High')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-rose-50 text-rose-700 rounded-full font-bold text-xs border border-rose-100">
                                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span>
                                        {{ $inst->ai_risk_score }}% Cao
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 text-center">
                                @if($inst->status === 'Pending_Approval')
                                    <span class="badge bg-amber-100 text-amber-800 px-3 py-1.5 rounded-pill text-[10px] font-extrabold uppercase tracking-wide">Chờ duyệt</span>
                                @elseif($inst->status === 'Approved')
                                    <span class="badge bg-emerald-100 text-emerald-800 px-3 py-1.5 rounded-pill text-[10px] font-extrabold uppercase tracking-wide">Đã duyệt</span>
                                @elseif($inst->status === 'Rejected')
                                    <span class="badge bg-rose-100 text-rose-800 px-3 py-1.5 rounded-pill text-[10px] font-extrabold uppercase tracking-wide">Từ chối</span>
                                @endif
                            </td>
                            <td class="py-3 text-center pe-4">
                                <div class="d-flex justify-content-center gap-1.5">
                                    <a href="{{ route('admin.installments.show', $inst->id) }}" class="btn btn-sm btn-outline-primary px-2.5 rounded-xl text-xs font-bold" title="Thẩm định hồ sơ">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.installments.edit', $inst->id) }}" class="btn btn-sm btn-outline-secondary px-2.5 rounded-xl text-xs font-bold" title="Sửa hợp đồng">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="{{ route('admin.installments.invoice', $inst->id) }}" target="_blank" class="btn btn-sm btn-outline-info px-2.5 rounded-xl text-xs font-bold" title="Xuất hóa đơn / Phiếu thu" style="color: #0ea5e9; border-color: #0ea5e9;">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <form action="{{ route('admin.installments.destroy', $inst->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hợp đồng trả góp này và khôi phục kho hàng không?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 rounded-xl text-xs font-bold" title="Xóa hợp đồng">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-slate-400">
                                <div class="mb-2"><i class="fa-solid fa-folder-open text-4xl"></i></div>
                                <span>Không tìm thấy hồ sơ trả góp nào khớp với bộ lọc.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($installments->hasPages())
            <div class="px-4 py-3 border-top border-slate-100 bg-slate-50/50">
                {!! $installments->withQueryString()->links() !!}
            </div>
        @endif
    </div>
</div>
@endsection
