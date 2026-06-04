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
                            <div class="invalid-feedback text-rose-500 mt-1 text-xs font-bold" id="nameError" style="display: none;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số điện thoại <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_phone" id="custPhoneInput" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" placeholder="Nhập số điện thoại" required value="{{ old('customer_phone') }}">
                            <div class="invalid-feedback text-rose-500 mt-1 text-xs font-bold" id="phoneError" style="display: none;"></div>
                        </div>
                        <div class="col-md-6" id="cccdArea">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Số CMND/CCCD (12 số) <span class="text-rose-500">*</span></label>
                            <input type="text" name="customer_id_card" id="custIdCardInput" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" placeholder="Nhập 12 số CCCD" value="{{ old('customer_id_card') }}">
                            <div class="invalid-feedback text-rose-500 mt-1 text-xs font-bold" id="idCardError" style="display: none;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-slate-500 text-xs font-bold uppercase">Địa chỉ giao hàng <span class="text-rose-500">*</span></label>
                            <input type="text" name="shipping_address" id="custAddressInput" class="form-control border-slate-200 py-2 rounded-xl focus-ring text-sm" placeholder="Nhập địa chỉ giao hàng" required value="{{ old('shipping_address') }}">
                            <div class="invalid-feedback text-rose-500 mt-1 text-xs font-bold" id="addressError" style="display: none;"></div>
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
                                    <option value="{{ $p->product_id }}" data-price="{{ $p->base_price }}" {{ old('product_id') == $p->product_id ? 'selected' : '' }}>
                                        {{ $p->name }} (Giá cơ bản: {{ number_format($p->base_price, 0, ',', '.') }}đ)
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback text-rose-500 mt-1 text-xs font-bold" id="productError" style="display: none;"></div>
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
                                <option value="" {{ old('method') == '' ? 'selected' : '' }}>-- Chọn phương thức trả góp --</option>
                                <option value="financial_company" {{ old('method') == 'financial_company' ? 'selected' : '' }}>Công ty tài chính</option>
                                <option value="credit_card" {{ old('method') == 'credit_card' ? 'selected' : '' }}>Thẻ tín dụng ngân hàng</option>
                                <option value="kredivo" {{ old('method') == 'kredivo' ? 'selected' : '' }}>Cổng trả góp Kredivo</option>
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
                            <div class="invalid-feedback text-rose-500 mt-1 text-xs font-bold" id="prepayError" style="display: none;"></div>
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
                        <i class="fa-solid fa-file-signature me-2"></i> TẠO HỢP ĐỒNG TRẢ GÓP
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Đổ dữ liệu biến thể từ Blade sang Javascript dạng JSON để lọc nhanh -->
<script>
    const allVariants = @json($variants);
    const oldProductId = "{{ old('product_id', '') }}";
    const oldVariantId = "{{ old('variant_id', '') }}";
    const oldMethod = "{{ old('method', '') }}";
    const oldPartner = "{{ old('partner', '') }}";
    const oldPeriod = "{{ old('period', '') }}";
</script>

<script>
function initInstallmentForm() {
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
    const tradeInCheck = document.getElementById('tradeInCheck');

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

    if (!userIdSelect || !productIdSelect || !methodSelect) return;

    // Inline validation error containers
    const nameError = document.getElementById('nameError');
    const phoneError = document.getElementById('phoneError');
    const idCardError = document.getElementById('idCardError');
    const productError = document.getElementById('productError');
    const prepayError = document.getElementById('prepayError');
    const addressError = document.getElementById('addressError');

    // Validation state tracking
    let isSubmitted = false;
    const touchedFields = new Set();

    // Validation helper functions
    function showFieldError(inputEl, errorEl, message) {
        inputEl.classList.add('is-invalid');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
    }

    function clearFieldError(inputEl, errorEl) {
        inputEl.classList.remove('is-invalid');
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.style.display = 'none';
        }
    }

    function validateName() {
        const val = custNameInput.value.trim();
        if (!val) {
            if (isSubmitted || touchedFields.has('name')) {
                showFieldError(custNameInput, nameError, 'Họ và tên khách hàng không được để trống.');
            }
            return false;
        }
        clearFieldError(custNameInput, nameError);
        return true;
    }

    // Validation helper functions
    function validatePhone() {
        const val = custPhoneInput.value.trim();
        const phoneRegex = /^0[0-9]{8,9}$/;
        if (!val) {
            if (isSubmitted || touchedFields.has('phone')) {
                showFieldError(custPhoneInput, phoneError, 'Số điện thoại không được để trống.');
            }
            return false;
        } else if (!phoneRegex.test(val)) {
            if (isSubmitted || touchedFields.has('phone')) {
                showFieldError(custPhoneInput, phoneError, 'Số điện thoại không hợp lệ (Phải bắt đầu bằng số 0, có 9 hoặc 10 chữ số).');
            }
            return false;
        }
        clearFieldError(custPhoneInput, phoneError);
        return true;
    }

    function validateIdCard() {
        const method = methodSelect.value;
        const val = custIdCardInput.value.trim();
        if (method === 'financial_company') {
            const idCardRegex = /^[0-9]{12}$/;
            if (!val) {
                if (isSubmitted || touchedFields.has('id_card')) {
                    showFieldError(custIdCardInput, idCardError, 'Vui lòng nhập số CMND/CCCD cho phương thức trả góp qua công ty tài chính.');
                }
                return false;
            } else if (!idCardRegex.test(val)) {
                if (isSubmitted || touchedFields.has('id_card')) {
                    showFieldError(custIdCardInput, idCardError, 'Số CMND/CCCD không hợp lệ (Phải đúng 12 chữ số).');
                }
                return false;
            }
        }
        clearFieldError(custIdCardInput, idCardError);
        return true;
    }

    function validateProduct() {
        const val = productIdSelect.value;
        if (!val) {
            if (isSubmitted || touchedFields.has('product')) {
                showFieldError(productIdSelect, productError, 'Vui lòng chọn sản phẩm.');
            }
            return false;
        }
        clearFieldError(productIdSelect, productError);
        return true;
    }

    function validatePrepay() {
        const prepayAmount = parseInt(prepayAmountInput.value) || 0;
        const productPrice = getSelectedProductPrice();
        if (prepayAmountInput.value.trim() === '') {
            if (isSubmitted || touchedFields.has('prepay')) {
                showFieldError(prepayAmountInput, prepayError, 'Vui lòng nhập số tiền trả trước.');
            }
            return false;
        } else if (prepayAmount < 0) {
            if (isSubmitted || touchedFields.has('prepay')) {
                showFieldError(prepayAmountInput, prepayError, 'Số tiền trả trước không được âm.');
            }
            return false;
        } else if (productPrice > 0 && prepayAmount > productPrice) {
            if (isSubmitted || touchedFields.has('prepay')) {
                showFieldError(prepayAmountInput, prepayError, `Số tiền trả trước không thể lớn hơn giá trị sản phẩm (${formatVnd(productPrice)}).`);
            }
            return false;
        }
        clearFieldError(prepayAmountInput, prepayError);
        return true;
    }

    function validateAddress() {
        const val = custAddressInput.value.trim();
        if (!val) {
            if (isSubmitted || touchedFields.has('address')) {
                showFieldError(custAddressInput, addressError, 'Địa chỉ giao hàng không được để trống.');
            }
            return false;
        } else if (val.length < 5) {
            if (isSubmitted || touchedFields.has('address')) {
                showFieldError(custAddressInput, addressError, 'Địa chỉ giao hàng phải từ 5 ký tự trở lên.');
            }
            return false;
        }
        clearFieldError(custAddressInput, addressError);
        return true;
    }

    // 1. Tự điền thông tin khi chọn tài khoản người dùng
    userIdSelect.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        if (option && option.value) {
            custNameInput.value = option.getAttribute('data-name') || '';
            custPhoneInput.value = option.getAttribute('data-phone') || '';
            custAddressInput.value = option.getAttribute('data-address') || '';
            touchedFields.add('name');
            touchedFields.add('phone');
            touchedFields.add('address');
        } else {
            custNameInput.value = '';
            custPhoneInput.value = '';
            custAddressInput.value = '';
        }
        validateName();
        validatePhone();
        validateAddress();
    });

    // 2. Tải danh sách biến thể tương ứng khi chọn sản phẩm
    productIdSelect.addEventListener('change', function () {
        touchedFields.add('product');
        const productId = this.value;
        
        // Reset danh sách biến thể
        variantIdSelect.innerHTML = '<option value="">-- Mặc định --</option>';
        
        if (productId && typeof allVariants !== 'undefined') {
            const filtered = allVariants.filter(v => v.product_id == productId);
            filtered.forEach(v => {
                const extra = v.extra_price ? parseInt(v.extra_price) : 0;
                const specLabel = v.color ? `${v.color} (+${extra.toLocaleString('vi-VN')}đ)` : `Biến thể #${v.variant_id}`;
                const option = document.createElement('option');
                option.value = v.variant_id;
                option.setAttribute('data-extra', extra);
                option.innerText = specLabel;
                variantIdSelect.appendChild(option);
            });
        }
        
        updateCalculations();
        validateProduct();
        validatePrepay();
    });

    variantIdSelect.addEventListener('change', function() {
        updateCalculations();
        validatePrepay();
    });

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
        if (partners.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = '-- Chọn đối tác/ngân hàng --';
            partnerSelect.appendChild(option);
            partnerSelect.disabled = true;
        } else {
            partnerSelect.disabled = false;
            partners.forEach(p => {
                const option = document.createElement('option');
                const label = method === 'credit_card' ? `${p} Credit Card` : p;
                option.value = label;
                option.textContent = label;
                partnerSelect.appendChild(option);
            });
        }

        // Render periods
        periodSelect.innerHTML = '';
        const periods = periodsByMethod[method] || [];
        if (periods.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = '-- Chọn kỳ hạn --';
            periodSelect.appendChild(option);
            periodSelect.disabled = true;
        } else {
            periodSelect.disabled = false;
            periods.forEach(p => {
                const option = document.createElement('option');
                option.value = p;
                option.textContent = `${p} tháng`;
                periodSelect.appendChild(option);
            });
        }

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
        validateIdCard();
        validatePrepay();
    });

    partnerSelect.addEventListener('change', updateCalculations);
    partnerSelect.addEventListener('input', updateCalculations);
    periodSelect.addEventListener('change', updateCalculations);
    periodSelect.addEventListener('input', updateCalculations);
    
    // SỰ KIỆN THAY ĐỔI TRẠNG THÁI CỦA CHECKBOX THU CŨ LÊN ĐỜI (TRADE-IN)
    // Khi admin tích chọn hoặc hủy tích chọn ô Thu cũ lên đời:
    // 1. Nếu là phương thức trả góp qua "Công ty tài chính" (yêu cầu trả trước 30% tối thiểu),
    //    tính toán lại số tiền trả trước 30% mặc định dựa trên mức giá mới sau khi trừ trợ giá.
    // 2. Cập nhật lại toàn bộ bảng tính chi phí tạm tính realtime.
    // 3. Chạy lại validate số tiền trả trước để đảm bảo số tiền hợp lệ.
    if (tradeInCheck) {
        tradeInCheck.addEventListener('change', function() {
            const method = methodSelect.value;
            if (method === 'financial_company') {
                const price = getSelectedProductPrice();
                prepayAmountInput.value = Math.round(price * 0.3);
            }
            updateCalculations();
            validatePrepay();
        });
    }
    
    prepayAmountInput.addEventListener('input', function() {
        touchedFields.add('prepay');
        updateCalculations();
        validatePrepay();
    });
    prepayAmountInput.addEventListener('blur', function() {
        touchedFields.add('prepay');
        updateCalculations();
        validatePrepay();
    });

    // Bind real-time input event listeners
    custNameInput.addEventListener('input', function() {
        touchedFields.add('name');
        validateName();
    });
    custNameInput.addEventListener('blur', function() {
        touchedFields.add('name');
        validateName();
    });

    custPhoneInput.addEventListener('input', function() {
        touchedFields.add('phone');
        validatePhone();
    });
    custPhoneInput.addEventListener('blur', function() {
        touchedFields.add('phone');
        validatePhone();
    });

    custIdCardInput.addEventListener('input', function() {
        touchedFields.add('id_card');
        validateIdCard();
    });
    custIdCardInput.addEventListener('blur', function() {
        touchedFields.add('id_card');
        validateIdCard();
    });

    custAddressInput.addEventListener('input', function() {
        touchedFields.add('address');
        validateAddress();
    });
    custAddressInput.addEventListener('blur', function() {
        touchedFields.add('address');
        validateAddress();
    });

    // Trigger thay đổi mặc định ban đầu hoặc khôi phục dữ liệu đã chọn trước đó
    if (typeof oldProductId !== 'undefined' && oldProductId) {
        productIdSelect.value = oldProductId;
        productIdSelect.dispatchEvent(new Event('change'));
        if (typeof oldVariantId !== 'undefined' && oldVariantId) {
            variantIdSelect.value = oldVariantId;
            variantIdSelect.dispatchEvent(new Event('change'));
        }
    }

    if (typeof oldMethod !== 'undefined' && oldMethod) {
        methodSelect.value = oldMethod;
    }
    methodSelect.dispatchEvent(new Event('change'));

    if (typeof oldPartner !== 'undefined' && oldPartner) {
        partnerSelect.value = oldPartner;
    }
    if (typeof oldPeriod !== 'undefined' && oldPeriod) {
        periodSelect.value = oldPeriod;
    }

    updateCalculations();

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

        // ==========================================
        // CHỨC NĂNG: TÍNH TOÁN TRỢ GIÁ "THU CŨ ĐỔI MỚI" (TRADE-IN)
        // [Ý nghĩa cho người dùng]: Hỗ trợ giảm trừ giá trị sản phẩm khi tham gia đổi máy cũ lấy máy mới.
        // - Trợ giá bằng 10% đơn giá hiện tại.
        // - Mức giảm tối đa là 2.000.000đ.
        // - Tránh lỗi đưa giá phụ kiện giá rẻ về 0đ hoặc âm tiền.
        // ==========================================
        
        // Kiểm tra xem phần tử checkbox Thu cũ lên đời có tồn tại và đang được tích chọn hay không
        if (tradeInCheck && tradeInCheck.checked) {
            // Tính số tiền trợ giá thu cũ đổi mới bằng 10% đơn giá hiện tại của sản phẩm
            const tenPercentOfPrice = Math.round(price * 0.1);
            // Giới hạn mức trợ giá thu cũ đổi mới tối đa không vượt quá 2.000.000 VNĐ
            const discount = Math.min(tenPercentOfPrice, 2000000);
            // Khấu trừ số tiền trợ giá vừa tính được trực tiếp vào đơn giá sản phẩm hiện tại
            const discountedPrice = price - discount;
            // Đảm bảo đơn giá sản phẩm sau khi trừ trợ giá không bao giờ bị âm hoặc nhỏ hơn 0đ
            price = Math.max(0, discountedPrice);
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
        const period = parseInt(periodSelect.value) || 0;

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
        } else if (method === 'credit_card') {
            // Thẻ tín dụng
            serviceFee = 20000;
        } else {
            interestRate = 0;
            serviceFee = 0;
        }

        // Thực hiện tính góp mỗi tháng
        const monthlyNoInterest = period > 0 ? (loanAmount / period) : 0;
        const monthlyInterest = loanAmount * interestRate;
        const monthlyPayment = (loanAmount > 0 && period > 0) ? (monthlyNoInterest + monthlyInterest + serviceFee) : 0;

        const totalPayment = prepayAmount + (period > 0 ? (monthlyPayment * period) : 0);
        const diffAmount = Math.max(0, totalPayment - productPrice);

        // Hiển thị lên UI
        calcProductPrice.innerText = formatVnd(productPrice);
        calcPrepayAmount.innerText = formatVnd(prepayAmount);
        calcLoanAmount.innerText = formatVnd(loanAmount);
        calcInterestRate.innerText = method ? `${(interestRate * 100).toFixed(1)}%/tháng` : '--';
        calcServiceFee.innerText = method ? formatVnd(serviceFee) : '--';
        calcPeriod.innerText = period > 0 ? `${period} tháng` : '--';
        calcMonthlyPayment.innerText = period > 0 ? formatVnd(monthlyPayment) : '--';
        calcTotalPayment.innerText = period > 0 ? formatVnd(totalPayment) : '--';
        calcDiffAmount.innerText = period > 0 ? formatVnd(diffAmount) : '--';
    }

    // 5. Client-side form validation before submission
    const form = document.getElementById('createInstallmentForm');
    form.addEventListener('submit', function (e) {
        isSubmitted = true;
        const isNameValid = validateName();
        const isPhoneValid = validatePhone();
        const isIdCardValid = validateIdCard();
        const isProductValid = validateProduct();
        const isPrepayValid = validatePrepay();
        const isAddressValid = validateAddress();

        if (!isNameValid || !isPhoneValid || !isIdCardValid || !isProductValid || !isPrepayValid || !isAddressValid) {
            e.preventDefault();
            
            // Tìm phần tử lỗi đầu tiên để scroll tới
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
            return false;
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initInstallmentForm);
} else {
    initInstallmentForm();
}
</script>
@endsection
