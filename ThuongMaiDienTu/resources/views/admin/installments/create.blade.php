@extends('admin.layouts.master')

@section('title', 'Tạo hợp đồng trả góp tại quầy')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-black text-slate-800 mb-1">Tạo hợp đồng trả góp tại quầy</h1>
            <p class="text-sm text-slate-500 mb-0">Thiết lập hợp đồng trả góp trực tiếp cho khách hàng mua sắm tại cửa hàng (POS).</p>
        </div>
        <a href="{{ route('admin.installments.index') }}" class="btn btn-outline-secondary rounded-xl text-sm font-semibold px-3">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger rounded-xl mb-4 border-0 shadow-sm text-sm">
            <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.installments.store') }}" method="POST" id="createInstallmentForm">
        @csrf
        <div class="row g-4">
            <!-- Left Panel: Form Input -->
            <div class="col-lg-8">
                <!-- 1. Customer Selection & Info -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 mb-4">
                    <h4 class="h6 font-bold text-slate-700 mb-3"><i class="fa-solid fa-user me-2 text-indigo-500"></i> 1. Thông tin khách hàng</h4>
                    
                    <div class="mb-3">
                        <label class="form-label text-slate-500 text-xs font-bold uppercase">Liên kết tài khoản khách hàng (Không bắt buộc)</label>
                        <select name="user_id" id="userIdSelect" class="form-select border-slate-200 py-2 rounded-xl focus-ring text-sm">
                            <option value="">-- Khách vãng lai / Không liên kết tài khoản --</option>
                            @foreach($users as $u)
                                <option value="{{ $u->user_id }}" data-name="{{ $u->full_name }}" data-phone="{{ $u->phone_number }}" data-address="{{ $u->address }}">
                                    {{ $u->full_name }} ({{ $u->phone_number ?? 'Không có SĐT' }} - {{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Họ và tên khách hàng <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_name" id="custNameInput" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" placeholder="Nhập họ và tên" required value="{{ old('customer_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số điện thoại <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_phone" id="custPhoneInput" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" placeholder="Nhập số điện thoại" required value="{{ old('customer_phone') }}">
                        </div>
                        <div class="col-md-6" id="cccdArea">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số CMND/CCCD (12 số) <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_id_card" id="custIdCardInput" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" placeholder="Nhập 12 số CCCD" value="{{ old('customer_id_card') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Địa chỉ giao hàng</label>
                            <input type="text" name="shipping_address" id="custAddressInput" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" placeholder="Nhập địa chỉ nhà hoặc bỏ trống nếu nhận tại cửa hàng" value="{{ old('shipping_address') }}">
                        </div>
                    </div>
                </div>

                <!-- 2. Product Selection -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 mb-4">
                    <h4 class="h6 font-bold text-slate-700 mb-3"><i class="fa-solid fa-laptop me-2 text-indigo-500"></i> 2. Chọn sản phẩm & biến thể</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Chọn sản phẩm <span class="text-rose-500">*</span></label>
                            <select name="product_id" id="productIdSelect" class="form-select border-slate-200 py-2 rounded-xl focus-ring text-sm" required>
                                <option value="">-- Chọn sản phẩm --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->product_id }}" data-price="{{ $p->base_price }}">
                                        {{ $p->name }} (Giá cơ bản: {{ number_format($p->base_price, 0, ',', '.') }}đ)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Chọn cấu hình/màu sắc (Biến thể)</label>
                            <select name="variant_id" id="variantIdSelect" class="form-select border-slate-200 py-2 rounded-xl focus-ring text-sm">
                                <option value="">-- Mặc định --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Installment Configuration -->
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100">
                    <h4 class="h6 font-bold text-slate-700 mb-3"><i class="fa-solid fa-gears me-2 text-indigo-500"></i> 3. Thiết lập gói trả góp</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Phương thức trả góp <span class="text-rose-500">*</span></label>
                            <select name="method" id="methodSelect" class="form-select border-slate-200 py-2 rounded-xl focus-ring text-sm" required>
                                <option value="financial_company">Công ty tài chính</option>
                                <option value="credit_card">Thẻ tín dụng ngân hàng</option>
                                <option value="kredivo">Cổng trả góp Kredivo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Đối tác/Ngân hàng <span class="text-rose-500">*</span></label>
                            <select name="partner" id="partnerSelect" class="form-select border-slate-200 py-2 rounded-xl focus-ring text-sm" required>
                                <!-- Sẽ được điền bằng JS dựa trên phương thức trả góp -->
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Kỳ hạn trả góp <span class="text-rose-500">*</span></label>
                            <select name="period" id="periodSelect" class="form-select border-slate-200 py-2 rounded-xl focus-ring text-sm" required>
                                <!-- Sẽ được điền bằng JS dựa trên phương thức trả góp -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số tiền trả trước (đ) <span class="text-rose-500">*</span></label>
                            <div class="input-group">
                                <input type="number" name="prepay_amount" id="prepayAmountInput" class="form-control border-slate-200 py-2 rounded-l-xl focus-ring text-sm font-bold text-slate-700" placeholder="0" min="0" required value="{{ old('prepay_amount', 0) }}">
                                <span class="input-group-text border-slate-200 text-slate-400 text-xs font-bold bg-slate-50 px-3 rounded-r-xl">VNĐ</span>
                            </div>
                            <span class="text-xs text-slate-400 mt-1 block" id="prepayPercentageText">Tương đương: 0% giá trị sản phẩm</span>
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input type="checkbox" name="trade_in" id="tradeInCheck" class="form-check-input border-slate-300 w-5 h-5 rounded-md" value="1">
                                <label for="tradeInCheck" class="form-check-label text-sm text-slate-600 font-semibold ms-2" style="user-select:none; cursor:pointer;">Đăng ký Thu cũ lên đời (Trợ giá lên đến 2 triệu)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Realtime Calculation Card -->
            <div class="col-lg-4">
                <div class="bg-white p-4 rounded-3xl shadow-sm border border-slate-100 sticky-top" style="top: 20px; z-index: 10;">
                    <h4 class="h6 font-bold text-slate-700 mb-3"><i class="fa-solid fa-calculator me-2 text-indigo-500"></i> Bảng tính chi phí (Tạm tính)</h4>
                    
                    <div class="divide-y divide-slate-100">
                        <div class="py-2.5 d-flex justify-content-between text-sm text-slate-500">
                            <span>Giá bán sản phẩm:</span>
                            <span class="font-bold text-slate-800" id="calcProductPrice">0đ</span>
                        </div>
                        <div class="py-2.5 d-flex justify-content-between text-sm text-slate-500">
                            <span>Đã thanh toán (Trả trước):</span>
                            <span class="font-bold text-slate-800" id="calcPrepayAmount">0đ</span>
                        </div>
                        <div class="py-2.5 d-flex justify-content-between text-sm text-slate-500">
                            <span>Cần vay (Còn lại):</span>
                            <span class="font-bold text-indigo-600" id="calcLoanAmount">0đ</span>
                        </div>
                        <div class="py-2.5 d-flex justify-content-between text-sm text-slate-500">
                            <span>Lãi suất tháng:</span>
                            <span class="font-bold text-slate-800" id="calcInterestRate">0%</span>
                        </div>
                        <div class="py-2.5 d-flex justify-content-between text-sm text-slate-500">
                            <span>Phí dịch vụ hàng tháng:</span>
                            <span class="font-bold text-slate-800" id="calcServiceFee">0đ</span>
                        </div>
                        <div class="py-2.5 d-flex justify-content-between text-sm text-slate-500">
                            <span>Thời hạn vay:</span>
                            <span class="font-bold text-slate-800" id="calcPeriod">6 tháng</span>
                        </div>
                        <div class="py-3 d-flex justify-content-between align-items-center">
                            <span class="text-sm font-bold text-slate-700">Góp mỗi tháng:</span>
                            <span class="text-xl font-extrabold text-rose-500" id="calcMonthlyPayment">0đ</span>
                        </div>
                        <div class="py-2.5 d-flex justify-content-between text-sm text-slate-500">
                            <span>Tổng tiền trả góp:</span>
                            <span class="font-bold text-slate-800" id="calcTotalPayment">0đ</span>
                        </div>
                        <div class="py-2.5 d-flex justify-content-between text-sm text-slate-500">
                            <span>Chênh lệch mua thẳng:</span>
                            <span class="font-bold text-amber-600" id="calcDiffAmount">0đ</span>
                        </div>
                    </div>

                    <button type="submit" class="btn w-100 py-3 rounded-2xl font-bold mt-4 shadow-sm text-sm" style="background-color: #0046ab; color: white;">
                        <i class="fa-solid fa-file-signature me-2"></i> KÝ HỢP ĐỒNG TRẢ GÓP
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Đổ dữ liệu biến thể từ Blade sang Javascript dạng JSON để lọc nhanh -->
<script>
    const allVariants = @json($variants);
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const userIdSelect = document.getElementById('userIdSelect');
    const custNameInput = document.getElementById('custNameInput');
    const custPhoneInput = document.getElementById('custPhoneInput');
    const custAddressInput = document.getElementById('custAddressInput');
    const custIdCardInput = document.getElementById('custIdCardInput');
    const cccdArea = document.getElementById('cccdArea');

    const productIdSelect = document.getElementById('productIdSelect');
    const variantIdSelect = document.getElementById('variantIdSelect');
    
    const methodSelect = document.getElementById('methodSelect');
    const partnerSelect = document.getElementById('partnerSelect');
    const periodSelect = document.getElementById('periodSelect');
    const prepayAmountInput = document.getElementById('prepayAmountInput');
    const prepayPercentageText = document.getElementById('prepayPercentageText');

    // Element hiển thị realtime bảng tính
    const calcProductPrice = document.getElementById('calcProductPrice');
    const calcPrepayAmount = document.getElementById('calcPrepayAmount');
    const calcLoanAmount = document.getElementById('calcLoanAmount');
    const calcInterestRate = document.getElementById('calcInterestRate');
    const calcServiceFee = document.getElementById('calcServiceFee');
    const calcPeriod = document.getElementById('calcPeriod');
    const calcMonthlyPayment = document.getElementById('calcMonthlyPayment');
    const calcTotalPayment = document.getElementById('calcTotalPayment');
    const calcDiffAmount = document.getElementById('calcDiffAmount');

    // 1. Tự điền thông tin khi chọn tài khoản người dùng
    userIdSelect.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            custNameInput.value = option.getAttribute('data-name') || '';
            custPhoneInput.value = option.getAttribute('data-phone') || '';
            custAddressInput.value = option.getAttribute('data-address') || '';
        } else {
            custNameInput.value = '';
            custPhoneInput.value = '';
            custAddressInput.value = '';
        }
    });

    // 2. Tải danh sách biến thể tương ứng khi chọn sản phẩm
    productIdSelect.addEventListener('change', function () {
        const productId = this.value;
        
        // Reset danh sách biến thể
        variantIdSelect.innerHTML = '<option value="">-- Mặc định --</option>';
        
        if (productId) {
            const filtered = allVariants.filter(v => v.product_id == productId);
            filtered.forEach(v => {
                const specLabel = v.color ? `${v.color} (+${v.extra_price ? v.extra_price.toLocaleString() : 0}đ)` : `Biến thể #${v.variant_id}`;
                const option = document.createElement('option');
                option.value = v.variant_id;
                option.setAttribute('data-extra', v.extra_price || 0);
                option.innerText = specLabel;
                variantIdSelect.appendChild(option);
            });
        }
        
        updateCalculations();
    });

    variantIdSelect.addEventListener('change', updateCalculations);

    // 3. Thay đổi phương thức trả góp -> Cập nhật các đối tác & kỳ hạn tương thích
    const partnersByMethod = {
        financial_company: ['Shinhan Finance', 'Home Credit', 'HD Saison', 'Mirae Asset'],
        credit_card: ['Vietcombank', 'Techcombank', 'MB Bank', 'Sacombank', 'VPBank'],
        kredivo: ['Kredivo']
    };

    const periodsByMethod = {
        financial_company: [3, 4, 6, 9, 12],
        credit_card: [3, 6, 9, 12],
        kredivo: [3, 6, 12]
    };

    methodSelect.addEventListener('change', function () {
        const method = this.value;
        
        // Render partners
        partnerSelect.innerHTML = '';
        const partners = partnersByMethod[method] || [];
        partners.forEach(p => {
            const label = method === 'credit_card' ? `${p} Credit Card` : p;
            const value = label;
            partnerSelect.innerHTML += `<option value="${value}">${label}</option>`;
        });

        // Render periods
        periodSelect.innerHTML = '';
        const periods = periodsByMethod[method] || [];
        periods.forEach(p => {
            periodSelect.innerHTML += `<option value="${p}">${p} tháng</option>`;
        });

        // Kiểm soát CCCD bắt buộc đối với công ty tài chính
        if (method === 'financial_company') {
            cccdArea.style.display = 'block';
            custIdCardInput.setAttribute('required', 'required');
        } else {
            cccdArea.style.display = 'none';
            custIdCardInput.removeAttribute('required');
        }

        // Tự thiết lập giá trị trả trước tối thiểu/mặc định
        if (method === 'financial_company') {
            const price = getSelectedProductPrice();
            prepayAmountInput.value = Math.round(price * 0.3); // 30% mặc định
        } else {
            prepayAmountInput.value = 0; // 0% cho thẻ hoặc Kredivo
        }

        updateCalculations();
    });

    partnerSelect.addEventListener('change', updateCalculations);
    periodSelect.addEventListener('change', updateCalculations);
    prepayAmountInput.addEventListener('input', updateCalculations);

    // Trigger thay đổi mặc định ban đầu
    methodSelect.dispatchEvent(new Event('change'));

    // Hàm lấy giá sản phẩm đang chọn
    function getSelectedProductPrice() {
        const prodOpt = productIdSelect.options[productIdSelect.selectedIndex];
        if (!prodOpt || !prodOpt.value) return 0;
        
        let price = parseInt(prodOpt.getAttribute('data-price')) || 0;
        
        const varOpt = variantIdSelect.options[variantIdSelect.selectedIndex];
        if (varOpt && varOpt.value) {
            const extra = parseInt(varOpt.getAttribute('data-extra')) || 0;
            price += extra;
        }
        return price;
    }

    // Hàm định dạng tiền tệ VNĐ
    function formatVnd(val) {
        return Math.round(val).toLocaleString('vi-VN') + 'đ';
    }

    // 4. Tính toán toàn bộ chi phí trả góp và hiển thị lên bảng tính
    function updateCalculations() {
        const productPrice = getSelectedProductPrice();
        const prepayAmount = parseInt(prepayAmountInput.value) || 0;
        const method = methodSelect.value;
        const partner = partnerSelect.value;
        const period = parseInt(periodSelect.value) || 6;

        // Tính tỷ lệ % trả trước
        const prepayPercent = productPrice > 0 ? Math.round((prepayAmount / productPrice) * 100) : 0;
        prepayPercentageText.innerText = `Tương đương: ${prepayPercent}% giá trị sản phẩm`;

        // Khoản vay
        const loanAmount = Math.max(0, productPrice - prepayAmount);

        // Quy đổi các cấu hình lãi suất và phí
        let interestRate = 0;
        let serviceFee = 0;

        if (method === 'financial_company') {
            if (partner === 'Home Credit') {
                interestRate = 0.01;
                serviceFee = 50000;
            } else if (partner === 'HD Saison') {
                interestRate = 0.015;
                serviceFee = 60000;
            } else if (partner === 'Mirae Asset') {
                interestRate = 0.02;
                serviceFee = 70000;
            }
        } else if (method === 'kredivo') {
            if (period !== 3) {
                interestRate = 0.025;
                serviceFee = 30000;
            }
        } else {
            // Thẻ tín dụng
            serviceFee = 20000;
        }

        // Thực hiện tính góp mỗi tháng
        const monthlyNoInterest = loanAmount / period;
        const monthlyInterest = loanAmount * interestRate;
        const monthlyPayment = loanAmount > 0 ? (monthlyNoInterest + monthlyInterest + serviceFee) : 0;

        const totalPayment = prepayAmount + (monthlyPayment * period);
        const diffAmount = Math.max(0, totalPayment - productPrice);

        // Hiển thị lên UI
        calcProductPrice.innerText = formatVnd(productPrice);
        calcPrepayAmount.innerText = formatVnd(prepayAmount);
        calcLoanAmount.innerText = formatVnd(loanAmount);
        calcInterestRate.innerText = `${(interestRate * 100).toFixed(1)}%/tháng`;
        calcServiceFee.innerText = formatVnd(serviceFee);
        calcPeriod.innerText = `${period} tháng`;
        calcMonthlyPayment.innerText = formatVnd(monthlyPayment);
        calcTotalPayment.innerText = formatVnd(totalPayment);
        calcDiffAmount.innerText = formatVnd(diffAmount);
    }
});
</script>
@endsection
