@extends('layouts.app')
@section('title', 'warranty')

@push('styles')
<style>
.warranty-page { padding: 40px 0 80px; min-height: 70vh; }
.warranty-hero {
    text-align: center; padding: 50px 20px 40px;
    background: linear-gradient(135deg, #0046ab 0%, #003380 50%, #001a4d 100%);
    border-radius: 20px; margin-bottom: 40px; position: relative; overflow: hidden;
}
.warranty-hero::before {
    content: ''; position: absolute; top: -50%; right: -20%; width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(0,210,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.warranty-hero::after {
    content: ''; position: absolute; bottom: -40%; left: -10%; width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(215,0,24,0.1) 0%, transparent 70%);
    border-radius: 50%;
}
.warranty-hero h1 {
    color: #fff; font-size: 32px; font-weight: 800; margin-bottom: 12px;
    position: relative; z-index: 1;
}
.warranty-hero p {
    color: rgba(255,255,255,0.8); font-size: 16px; position: relative; z-index: 1;
}
.warranty-hero .hero-icon {
    font-size: 48px; color: #00d2ff; margin-bottom: 16px;
    position: relative; z-index: 1;
}

/* Search Box */
.warranty-search-box {
    max-width: 600px; margin: -30px auto 0; position: relative; z-index: 2;
    background: #fff; border-radius: 16px; padding: 30px;
    box-shadow: 0 15px 50px rgba(0,70,171,0.15);
}
.search-input-group {
    display: flex; gap: 12px; margin-bottom: 16px;
}
.search-input-group input {
    flex: 1; padding: 14px 18px; border: 2px solid #e2e8f0; border-radius: 12px;
    font-size: 16px; font-weight: 500; outline: none; transition: 0.3s;
    font-family: 'Inter', monospace; letter-spacing: 1px;
}
.search-input-group input:focus { border-color: #0046ab; box-shadow: 0 0 0 4px rgba(0,70,171,0.1); }
.search-input-group input::placeholder { letter-spacing: 0; color: #94a3b8; font-weight: 400; }
.btn-lookup {
    padding: 14px 28px; background: linear-gradient(135deg, #0046ab, #0061f2);
    color: #fff; border: none; border-radius: 12px; font-size: 15px; font-weight: 700;
    cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px;
    white-space: nowrap;
}
.btn-lookup:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,70,171,0.3); }
.btn-lookup:active { transform: translateY(0); }
.btn-lookup:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
.btn-lookup .spinner { display: none; animation: spin 1s linear infinite; }
.btn-lookup.loading .spinner { display: inline-block; }
.btn-lookup.loading .btn-text { display: none; }

/* CTA Buttons in result card */
.btn-create-ticket {
    padding: 10px 20px; background: linear-gradient(135deg, #0046ab, #0061f2);
    color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: 0.25s; display: inline-flex; align-items: center; gap: 8px;
    white-space: nowrap;
}
.btn-create-ticket:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,70,171,0.3); }
.btn-create-ticket:active { transform: translateY(0); }

.search-hint {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #64748b;
}
.search-hint i { color: #0046ab; }

/* Result Card */
.warranty-result { max-width: 600px; margin: 30px auto 0; display: none; }
.warranty-result.show { display: block; animation: slideUp 0.4s ease; }
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Error State */
.result-error {
    background: #fff; border-radius: 16px; padding: 40px; text-align: center;
    border: 2px solid #fee2e2; box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}
.result-error .error-icon { font-size: 48px; color: #ef4444; margin-bottom: 16px; }
.result-error h3 { color: #dc2626; font-size: 18px; margin-bottom: 8px; }
.result-error p { color: #64748b; font-size: 14px; line-height: 1.6; }

/* Success Card */
.result-card {
    background: #fff; border-radius: 16px; overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
}
.result-header {
    padding: 24px; display: flex; align-items: center; gap: 20px;
    border-bottom: 1px solid #f1f5f9;
}
.result-product-img {
    width: 80px; height: 80px; object-fit: contain; border-radius: 12px;
    background: #f8fafc; padding: 8px; border: 1px solid #e2e8f0;
}
.result-product-info { flex: 1; }
.result-product-info h3 { font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 4px; }
.result-product-info .variant-label { font-size: 13px; color: #64748b; }
.result-product-info .imei-label {
    font-size: 12px; color: #94a3b8; font-family: monospace; margin-top: 4px;
}

/* Status Badge */
.warranty-status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 50px; font-size: 13px; font-weight: 700;
}
.warranty-status-badge.active {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #15803d;
}
.warranty-status-badge.expired {
    background: linear-gradient(135deg, #fee2e2, #fecaca); color: #dc2626;
}
.warranty-status-badge.paused {
    background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309;
}
.warranty-status-badge.rejected {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #64748b;
}
.warranty-status-badge.none {
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0); color: #64748b;
}

/* Info Grid */
.result-body { padding: 24px; }
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 16px; }
.info-item {
    padding: 16px; background: #f8fafc; border-radius: 12px;
    border: 1px solid #f1f5f9; transition: 0.2s;
}
.info-item:hover { border-color: #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
.info-item .info-label {
    font-size: 11px; font-weight: 600; color: #94a3b8;
    text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
}
.info-item .info-value { font-size: 15px; font-weight: 700; color: #1e293b; }
.info-item .info-value.text-green { color: #15803d; }
.info-item .info-value.text-red { color: #dc2626; }
.info-item .info-value.text-orange { color: #b45309; }

/* Progress Bar */
.warranty-progress { margin-top: 20px; padding: 20px; background: #f8fafc; border-radius: 12px; }
.warranty-progress .progress-label {
    display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-bottom: 8px;
}
.progress-bar-track {
    height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;
}
.progress-bar-fill {
    height: 100%; border-radius: 4px; transition: width 1s ease;
    background: linear-gradient(90deg, #22c55e, #16a34a);
}
.progress-bar-fill.low { background: linear-gradient(90deg, #f59e0b, #d97706); }
.progress-bar-fill.critical { background: linear-gradient(90deg, #ef4444, #dc2626); }
.progress-bar-fill.expired { background: #94a3b8; width: 100% !important; }

/* Note */
.warranty-note {
    margin-top: 16px; padding: 14px 18px; background: #eff6ff; border-radius: 10px;
    border-left: 4px solid #0046ab; font-size: 13px; color: #1e40af; line-height: 1.5;
}
.warranty-note i { margin-right: 6px; }

/* Repair History */
.repair-history { margin-top: 24px; }
.repair-history h4 {
    font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.repair-history h4 i { color: #0046ab; }
.repair-item {
    display: flex; align-items: center; gap: 12px; padding: 12px 16px;
    background: #f8fafc; border-radius: 10px; margin-bottom: 8px;
    border: 1px solid #f1f5f9; transition: 0.2s;
}
.repair-item:hover { border-color: #e2e8f0; }
.repair-item .repair-id {
    font-size: 12px; font-weight: 700; color: #0046ab;
    background: #eff6ff; padding: 4px 10px; border-radius: 6px; white-space: nowrap;
}
.repair-item .repair-issue { flex: 1; font-size: 13px; color: #475569; }
.repair-item .repair-status {
    font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px;
}
.repair-item .repair-status.Received { background: #dbeafe; color: #1d4ed8; }
.repair-item .repair-status.Waiting_Parts { background: #fef3c7; color: #b45309; }
.repair-item .repair-status.Done { background: #dcfce7; color: #15803d; }

.claim-status-badge {
    font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 6px; display: inline-block;
}
.claim-status-badge.pending { background: #fef3c7; color: #b45309; }
.claim-status-badge.approved { background: #dcfce7; color: #15803d; }
.claim-status-badge.rejected { background: #fee2e2; color: #b91c1c; }

/* CTA Button */
.warranty-cta {
    margin-top: 20px; padding-top: 20px; border-top: 1px solid #f1f5f9;
    text-align: center;
}
.btn-create-ticket {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 24px; background: linear-gradient(135deg, #0046ab, #0061f2);
    color: #fff; border: none; border-radius: 12px; font-size: 14px; font-weight: 700;
    cursor: pointer; transition: 0.3s; text-decoration: none;
}
.btn-create-ticket:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,70,171,0.3); }

/* How it works */
.how-it-works {
    max-width: 600px; margin: 50px auto 0; text-align: center;
}
.how-it-works h3 { font-size: 20px; font-weight: 700; margin-bottom: 24px; color: #1e293b; }
.steps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.step-item { padding: 24px 16px; }
.step-icon {
    width: 56px; height: 56px; border-radius: 16px; display: flex;
    align-items: center; justify-content: center; margin: 0 auto 14px;
    font-size: 22px; color: #fff;
    background: linear-gradient(135deg, #0046ab, #0061f2);
}
.step-item h4 { font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 6px; }
.step-item p { font-size: 12px; color: #64748b; line-height: 1.5; }

/* Integrated Policy Styles */
.policy-wrapper { max-width: 900px; margin: 60px auto 0; }
.policy-nav { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-bottom: 30px; }
.policy-nav a { padding: 10px 20px; background: #fff; border: 2px solid #e2e8f0; border-radius: 10px; font-size: 13px; font-weight: 700; color: #475569; transition: .2s; text-decoration: none; }
.policy-nav a:hover, .policy-nav a.active { border-color: #0046ab; color: #0046ab; background: #f0f7ff; }
.policy-content { background: #fff; border-radius: 16px; padding: 40px; box-shadow: 0 5px 30px rgba(0,0,0,.06); }
.policy-section { margin-bottom: 36px; display: none; }
.policy-section.active { display: block; animation: slideUp 0.3s ease; }
.policy-section h2 { font-size: 20px; font-weight: 800; color: #0046ab; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #eff6ff; display: flex; align-items: center; gap: 10px; }
.policy-section h2 i { font-size: 18px; }
.policy-section h3 { font-size: 16px; font-weight: 700; color: #1e293b; margin: 18px 0 10px; }
.policy-table { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 13px; border-radius: 10px; overflow: hidden; }
.policy-table thead th { background: #0046ab; color: #fff; padding: 12px 14px; text-align: left; font-weight: 700; font-size: 12px; text-transform: uppercase; }
.policy-table tbody td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #475569; vertical-align: top; line-height: 1.6; }
.policy-table tbody tr:nth-child(even) { background: #f8fafc; }
.policy-table tbody tr:hover { background: #eff6ff; }
.policy-note { padding: 14px 18px; background: #eff6ff; border-radius: 10px; border-left: 4px solid #0046ab; font-size: 13px; color: #1e40af; line-height: 1.6; margin: 14px 0; }
.policy-note.warning { background: #fef3c7; border-color: #f59e0b; color: #92400e; }
.policy-note i { margin-right: 6px; }
.policy-list { margin: 10px 0; padding-left: 20px; }
.policy-list li { margin-bottom: 8px; font-size: 14px; color: #475569; line-height: 1.6; }
.policy-list li::marker { color: #0046ab; }
.policy-section p { font-size: 14px; line-height: 1.7; color: #475569; margin-bottom: 10px; }

@media (max-width: 768px) {
    .policy-content { padding: 20px; }
    .policy-table { font-size: 11px; }
    .policy-nav { gap: 6px; }
    .policy-nav a { padding: 8px 14px; font-size: 12px; }
}
</style>
@endpush

@section('content')
{{-- Vùng bao ngoài cùng của trang Tra cứu & Chính sách bảo hành --}}
<div class="warranty-page">
    <div class="container">
        
        {{-- =========================================================================
             PHẦN HERO BANNER GIỚI THIỆU CHUNG (HERO SECTION)
             ========================================================================= --}}
        <div class="warranty-hero">
            {{-- Biểu tượng chiếc khiên bảo mật --}}
            <div class="hero-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <h1>Tra cứu & Chính sách bảo hành</h1>
            <p>Nhập mã IMEI/Serial để kiểm tra bảo hành hoặc tham khảo các chính sách hậu mãi bên dưới</p>
        </div>

        {{-- =========================================================================
             HỘP TÌM KIẾM TRA CỨU IMEI (SEARCH BOX)
             Khách nhập mã IMEI/Số điện thoại/Mã đơn hàng vào đây để tra cứu
             ========================================================================= --}}
        <div class="warranty-search-box" id="warrantySearchBox">
            <form id="warrantyForm" onsubmit="return false;">
                <div class="search-input-group">
                    {{-- Ô nhập thông tin IMEI/Serial --}}
                    <input type="text" id="imeiInput" name="imei"
                           placeholder="Nhập mã IMEI hoặc Serial Number..."
                           maxlength="30" autocomplete="off" required>
                    {{-- Nút bấm kích hoạt tra cứu. Khi bấm sẽ gọi JS để gọi API --}}
                    <button type="button" class="btn-lookup" id="btnLookup" onclick="lookupWarranty()">
                        <i class="fa-solid fa-spinner spinner"></i>
                        <span class="btn-text"><i class="fa-solid fa-magnifying-glass"></i> Tra cứu</span>
                    </button>
                </div>
                {{-- Gợi ý nhỏ bên dưới giúp khách hàng biết cách lấy IMEI --}}
                <div class="search-hint">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Mã IMEI thường nằm trên hộp sản phẩm hoặc quay số *#06# trên điện thoại</span>
                </div>
            </form>
        </div>

        {{-- =========================================================================
             KHU VỰC HIỂN THỊ KẾT QUẢ TRA CỨU (RESULT AREA)
             JS sẽ chèn động HTML kết quả bảo hành, lịch sử, form vào đây khi có kết quả
             ========================================================================= --}}
        <div class="warranty-result" id="warrantyResult"></div>

        {{-- =========================================================================
             HƯỚNG DẪN CÁC BƯỚC THỰC HIỆN (HOW IT WORKS)
             ========================================================================= --}}
        <div class="how-it-works">
            <h3>Cách tra cứu bảo hành</h3>
            <div class="steps-grid">
                {{-- Bước 1 --}}
                <div class="step-item">
                    <div class="step-icon"><i class="fa-solid fa-barcode"></i></div>
                    <h4>Bước 1</h4>
                    <p>Tìm mã IMEI/Serial trên hộp sản phẩm hoặc quay số *#06#</p>
                </div>
                {{-- Bước 2 --}}
                <div class="step-item">
                    <div class="step-icon"><i class="fa-solid fa-keyboard"></i></div>
                    <h4>Bước 2</h4>
                    <p>Nhập mã IMEI vào ô tra cứu phía trên</p>
                </div>
                {{-- Bước 3 --}}
                <div class="step-item">
                    <div class="step-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <h4>Bước 3</h4>
                    <p>Xem kết quả trạng thái bảo hành ngay lập tức</p>
                </div>
            </div>
        </div>

        {{-- =========================================================================
             KHỐI NỘI DUNG CHÍNH SÁCH BẢO HÀNH & ĐỔI TRẢ (TABBED POLICIES)
             ========================================================================= --}}
        <div class="policy-wrapper">
            <h3 style="text-align: center; font-size: 20px; font-weight: 700; margin-bottom: 24px; color: #1e293b;">
                Chính sách bảo hành & Đổi trả
            </h3>

            {{-- Các tab điều hướng chính sách --}}
            <div class="policy-nav">
                <a href="javascript:void(0)" class="active" onclick="switchPolicyTab('doi-moi', this)"><i class="fa-solid fa-rotate"></i> Đổi mới 30 ngày</a>
                <a href="javascript:void(0)" onclick="switchPolicyTab('bh-tieu-chuan', this)"><i class="fa-solid fa-shield-halved"></i> Bảo hành tiêu chuẩn</a>
                <a href="javascript:void(0)" onclick="switchPolicyTab('bh-mo-rong', this)"><i class="fa-solid fa-star"></i> Bảo hành mở rộng</a>
            </div>

            <div class="policy-content">
                {{-- I. ĐỔI MỚI 30 NGÀY --}}
                <div class="policy-section active" id="doi-moi">
                    <h2><i class="fa-solid fa-rotate"></i> I. Đổi mới miễn phí</h2>
                    <p>Khi mua sản phẩm tại DIENMAYPRO, khách hàng được hưởng chính sách đổi mới miễn phí lên tới 30 ngày nếu sản phẩm gặp lỗi phần cứng từ phía nhà sản xuất.</p>

                    <table class="policy-table">
                        <thead><tr><th>Loại sản phẩm</th><th>Đổi mới miễn phí (*)</th><th>Quy định nhập lại, trả lại</th></tr></thead>
                        <tbody>
                            <tr><td>Điện thoại / Máy tính bảng / Macbook / Apple Watch</td><td>30 ngày</td><td>Trong 30 ngày đầu trừ phí 20% giá hiện tại. Sau 30 ngày theo thoả thuận.</td></tr>
                            <tr><td>Samsung Watch</td><td>30 ngày</td><td>Trong 30 ngày đầu trừ phí 30% giá hiện tại. Sau 30 ngày theo thoả thuận.</td></tr>
                            <tr><td>Laptop</td><td>30 ngày</td><td>Trong 30 ngày đầu trừ phí 20% giá hiện tại. Sau 30 ngày theo thoả thuận.</td></tr>
                            <tr><td>Màn hình máy tính</td><td>15 ngày</td><td>Trong 15 ngày đầu trừ phí 20% giá hiện tại. Sau 15 ngày theo thoả thuận.</td></tr>
                            <tr><td>Máy tính All-In-One / PC / Máy in</td><td>15 ngày</td><td>Không áp dụng nhập lại</td></tr>
                            <tr><td>Loa - Tai nghe cao cấp</td><td>15 ngày</td><td>Trong 30 ngày trừ 40%. Từ 31-60 ngày trừ 50%. Quá 60 ngày không áp dụng.</td></tr>
                            <tr><td>Phụ kiện &lt; 1 triệu</td><td>1 năm (mới) / 1 tháng (cũ)</td><td>Không áp dụng nhập lại</td></tr>
                            <tr><td>Phụ kiện &gt; 1 triệu</td><td>15 ngày</td><td>Không áp dụng (riêng Airpod: 30 ngày trừ 20%)</td></tr>
                            <tr><td>Đồ gia dụng / Tivi</td><td>15 - 30 ngày</td><td>Không áp dụng nhập lại</td></tr>
                            <tr><td>Hàng cũ</td><td>30 ngày</td><td>Trong 30 ngày trừ 15%. Sau 30 ngày theo thoả thuận.</td></tr>
                        </tbody>
                    </table>

                    <p><small>(*) Lỗi phần cứng từ phía nhà sản xuất: nguồn, mainboard, ổ cứng, màn hình, linh kiện bên trong.</small></p>

                    <h3>Điều kiện đổi trả</h3>
                    <ul class="policy-list">
                        <li><strong>Máy:</strong> Như mới, không xước xát, không dán decal, hình trang trí</li>
                        <li><strong>Hộp:</strong> Như mới, không móp méo, rách; Serial/IMEI trên hộp trùng với thân máy</li>
                        <li><strong>Phụ kiện và quà tặng:</strong> Còn đầy đủ, nguyên vẹn</li>
                        <li><strong>Tài khoản:</strong> Đã đăng xuất khỏi tất cả tài khoản (iCloud, Google, Mi Account…)</li>
                    </ul>

                    <div class="policy-note">
                        <i class="fa-solid fa-circle-info"></i>
                        Lỗi điểm chết màn hình: từ 3 điểm chết trở lên hoặc 1 điểm chết &gt; 1mm (điện thoại); từ 5 điểm chết trở lên (laptop, màn hình rời).
                    </div>
                </div>

                {{-- II. BẢO HÀNH TIÊU CHUẨN --}}
                <div class="policy-section" id="bh-tieu-chuan">
                    <h2><i class="fa-solid fa-shield-halved"></i> II. Bảo hành tiêu chuẩn</h2>

                    <h3>1. Điện thoại, Laptop</h3>
                    <table class="policy-table">
                        <thead><tr><th>Sản phẩm</th><th>Thời gian BH</th><th>Quyền lợi</th><th>Địa chỉ BH</th></tr></thead>
                        <tbody>
                            <tr><td>Hàng mới</td><td>12 tháng (hoặc dài hơn theo hãng)</td><td>Quyền lợi bảo hành chính hãng</td><td>TTBH chính hãng</td></tr>
                            <tr><td>Hàng đã kích hoạt BH</td><td>12 tháng = BH hãng còn lại + BH tại cửa hàng</td><td>Sửa chữa, thay thế linh kiện</td><td>TTBH chính hãng & Cửa hàng</td></tr>
                            <tr><td>Hàng cũ</td><td>6 tháng</td><td>Sửa chữa, thay thế linh kiện (bao gồm nguồn và màn hình)</td><td>Cửa hàng</td></tr>
                        </tbody>
                    </table>

                    <div class="policy-note">
                        <i class="fa-solid fa-circle-info"></i>
                        Trong thời gian đợi bảo hành, khách hàng được hỗ trợ miễn phí một điện thoại khác để sử dụng.
                    </div>

                    <h3>2. Phụ kiện</h3>
                    <table class="policy-table">
                        <thead><tr><th>Sản phẩm</th><th>Tình trạng</th><th>Thời gian</th><th>Quyền lợi</th></tr></thead>
                        <tbody>
                            <tr><td>Phụ kiện &gt; 1 triệu</td><td>Mới</td><td>12 tháng</td><td>Sửa chữa, thay thế linh kiện</td></tr>
                            <tr><td>Phụ kiện &lt; 1 triệu</td><td>Mới</td><td>12 tháng</td><td>1 đổi 1 tất cả các lỗi</td></tr>
                            <tr><td>Phụ kiện &lt; 1 triệu</td><td>Cũ</td><td>30 ngày</td><td>1 đổi 1 tất cả các lỗi</td></tr>
                            <tr><td>Dán cường lực / chống va đập</td><td>—</td><td>30 ngày</td><td>1 đổi 1. Dán lần 2 giảm 30%</td></tr>
                            <tr><td>Âm thanh / Đồ gia dụng</td><td>Mới</td><td>12 tháng</td><td>Sửa chữa, thay thế linh kiện</td></tr>
                            <tr><td>Bao da, ốp lưng</td><td>Mới</td><td>12 tháng</td><td>BH lỗi NSX (không BH ố vàng ốp trong)</td></tr>
                        </tbody>
                    </table>

                    <h3>Linh kiện máy tính</h3>
                    <table class="policy-table">
                        <thead><tr><th>Linh kiện</th><th>Thời gian BH</th><th>Quyền lợi</th></tr></thead>
                        <tbody>
                            <tr><td>CPU / Mainboard / PSU / RAM / SSD-HDD / VGA</td><td>36 tháng</td><td>Sửa chữa hoặc đổi tương đương</td></tr>
                            <tr><td>Vỏ Case / Tản nhiệt</td><td>12 tháng</td><td>Sửa chữa hoặc đổi tương đương</td></tr>
                        </tbody>
                    </table>
                </div>

                {{-- III. BẢO HÀNH MỞ RỘNG --}}
                <div class="policy-section" id="bh-mo-rong">
                    <h2><i class="fa-solid fa-star"></i> III. Bảo hành mở rộng</h2>

                    <h3>1. Bảo hành 1 đổi 1 VIP</h3>
                    <p><strong>Áp dụng:</strong> Điện thoại, máy tính bảng mới/cũ; Tai nghe cao cấp mới; Đồng hồ thông minh Apple/Samsung mới.</p>
                    <p><strong>Thời gian:</strong> 06 hoặc 12 tháng.</p>

                    <table class="policy-table">
                        <thead><tr><th>Phạm vi bảo hành</th><th>BH tiêu chuẩn</th><th>BH 1 đổi 1 VIP</th></tr></thead>
                        <tbody>
                            <tr><td>Mainboard (lỗi nguồn), ổ cứng</td><td>Sửa chữa</td><td>1 đổi 1</td></tr>
                            <tr><td>Màn hình (≥3 điểm chết hoặc ≥1mm)</td><td>Sửa chữa</td><td>1 đổi 1</td></tr>
                            <tr><td>Camera, loa, chip wifi, mic, đèn flash…</td><td>Sửa chữa</td><td>1 đổi 1</td></tr>
                            <tr><td>Chân sim, chân thẻ nhớ, chân sạc</td><td>Không BH</td><td>Sửa chữa</td></tr>
                            <tr><td>Pin và phím bấm vật lý</td><td>Sửa chữa 3 tháng</td><td>1 đổi 1 trong 12 tháng</td></tr>
                        </tbody>
                    </table>

                    <div class="policy-note">
                        <i class="fa-solid fa-clock"></i>
                        Thời gian xử lý: 07 - 14 ngày làm việc tùy theo sản phẩm (không tính lễ, T7, CN).
                    </div>

                    <h3>2. Bảo hành rơi vỡ, ngấm nước</h3>
                    <p><strong>Áp dụng:</strong> Điện thoại, máy tính bảng mới/cũ.</p>
                    <p><strong>Thời gian:</strong> 12 tháng.</p>
                    <ul class="policy-list">
                        <li>Sản phẩm bị rơi vỡ hoặc ngấm nước sẽ được sửa chữa/thay thế linh kiện</li>
                        <li>Khách hàng chịu phí dịch vụ 10% chi phí sửa chữa</li>
                        <li>Tổng chi phí sửa chữa không vượt quá giá niêm yết sản phẩm</li>
                        <li>Nếu không sửa được, đổi sản phẩm cũ tương đương (phí dịch vụ 10%)</li>
                    </ul>

                    <h3>3. Bảo hành mở rộng S24+</h3>
                    <p><strong>Áp dụng:</strong> Macbook, Điện thoại.</p>
                    <p><strong>Thời gian:</strong> 24 - 36 tháng (bao gồm 12 tháng BH nhà sản xuất).</p>
                    <ul class="policy-list">
                        <li>Sau khi hết BH nhà sản xuất, tiếp tục BH lỗi NSX thêm 12-24 tháng</li>
                        <li>Miễn phí sửa chữa và thay thế linh kiện do lỗi NSX</li>
                        <li>Không áp dụng: hư hỏng do rơi vỡ, ngấm nước, lỗi pin, phím vật lý</li>
                        <li>Đặc quyền +3% giá trị thu cũ khi lên đời trong thời gian BH</li>
                    </ul>

                    <div class="policy-note warning">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <strong>Lưu ý về dữ liệu:</strong> Quý khách vui lòng chủ động sao lưu dữ liệu trước khi gửi bảo hành. Cửa hàng không chịu trách nhiệm về việc mất dữ liệu trong mọi trường hợp.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =========================================================================
     CỬA SỔ POPUP NHẬP FORM YÊU CẦU DỊCH VỤ (CLAIM MODAL)
     Được mở lên khi người dùng nhấn nút Bảo Hành hoặc Đổi Trả sau khi tra cứu thành công
     ========================================================================= --}}
<div id="claimModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);justify-content:center;align-items:center;padding:12px;z-index:9999;">
    <div id="claimModalContent" style="background:#fff;border-radius:16px;width:100%;max-width:480px;max-height:90vh;overflow:hidden;box-shadow:0 20px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04);transform:scale(0.95);opacity:0;transition:transform .3s ease,opacity .3s ease;display:flex;flex-direction:column;">
        {{-- Tiêu đề Modal và nút đóng màu trắng nền xanh hoặc cam --}}
        <div id="claimModalHeader" style="padding:12px 18px;background:#0046ab;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
            <h3 id="claimModalTitle" style="font-size:16px;font-weight:700;color:#fff;margin:0;">Gửi yêu cầu bảo hành</h3>
            <button type="button" onclick="closeClaimModal()" style="background:none;border:none;font-size:18px;color:#fff;cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        {{-- Form điền thông tin và đính kèm tệp tin gửi lên backend xử lý --}}
        <form id="claimForm" style="padding:16px;overflow-y:auto;display:flex;flex-direction:column;gap:12px;" onsubmit="submitClaim(event)" enctype="multipart/form-data">
            @csrf
            {{-- Khối thông tin thiết bị đang thao tác (chỉ đọc) --}}
            <div style="background:#f8fafc;padding:10px 12px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;">
                <div style="display:flex;flex-direction:column;gap:2px;">
                    <span style="font-weight:700;color:#475569;">Sản phẩm:</span>
                    <span style="color:#1e293b;" id="modalProductNameDisplay"></span>
                </div>
                <div style="display:flex;gap:6px;align-items:center;margin-top:6px;padding-top:6px;border-top:1px dashed #e2e8f0;">
                    <span style="font-weight:700;color:#475569;">IMEI:</span>
                    <span style="color:#0f172a;font-family:monospace;font-weight:600;" id="modalImeiDisplay"></span>
                </div>
            </div>
            
            {{-- Trường ẩn chứa IMEI thực sự sẽ gửi đi --}}
            <input type="hidden" id="modalImei" name="imei_serial">
            
            {{-- Chọn Loại yêu cầu --}}
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Loại yêu cầu</label>
                <select id="modalClaimType" name="claim_type" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;background:#fff;" required>
                    <option value="warranty">Bảo hành sửa chữa (Miễn phí)</option>
                    <option value="return">Đổi trả hàng hoàn tiền</option>
                    <option value="exchange">Đổi máy mới/khách</option>
                </select>
            </div>
            
            {{-- Nhập thông tin liên hệ của khách hàng --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Họ tên</label>
                    <input type="text" id="modalCustomerName" name="customer_name" value="{{ auth()->user() ? auth()->user()->full_name : '' }}" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;" required>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Số điện thoại</label>
                    <input type="text" id="modalCustomerPhone" name="customer_phone" value="{{ auth()->user() ? auth()->user()->phone_number : '' }}" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;" required>
                </div>
            </div>
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Email liên hệ</label>
                <input type="email" id="modalCustomerEmail" name="customer_email" value="{{ auth()->user() ? auth()->user()->email : '' }}" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
            </div>
            
            {{-- Lý do chi tiết yêu cầu --}}
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Lý do yêu cầu</label>
                <textarea id="modalReason" name="reason" rows="2" placeholder="Mô tả cụ thể lỗi thiết bị hoặc lý do..." style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;resize:none;" required></textarea>
            </div>
            
            {{-- Đính kèm ảnh/video minh họa lỗi máy --}}
            <div>
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Hình ảnh/Video <span style="font-weight:normal;color:#94a3b8;">(Tối đa 20MB)</span></label>
                <input type="file" id="modalMediaFile" name="media_file" accept="image/*,video/*" style="width:100%;padding:6px 10px;border:1px dashed #cbd5e1;border-radius:8px;font-size:12px;background:#fafafa;cursor:pointer;">
            </div>
            
            {{-- Lựa chọn phương thức nhận lại tiền hoàn trả (Chỉ hiện khi khách chọn Đổi Trả) --}}
            <div id="refundMethodSection" style="display: none; flex-direction: column; gap: 4px;">
                <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Phương thức nhận tiền hoàn</label>
                <select id="modalRefundMethod" name="refund_method" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;outline:none;background:#fff;">
                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                    <option value="cash">Tiền mặt tại cửa hàng</option>
                </select>
            </div>
            
            {{-- Điền thông tin số tài khoản và ngân hàng (Chỉ hiển thị khi phương thức hoàn tiền là Chuyển khoản) --}}
            <div id="bankDetailsSection" style="display: none; border-top: 1px dashed #e2e8f0; padding-top: 12px; margin-top: 4px; flex-direction: column; gap: 10px;">
                <h4 style="font-size: 13px; font-weight: 700; color: #d97706; margin: 0; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-building-columns"></i> Thông tin nhận tiền hoàn
                </h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Ngân hàng</label>
                        <input type="text" id="modalBankName" name="bank_name" placeholder="VD: Vietcombank" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Số tài khoản</label>
                        <input type="text" id="modalBankAccountNumber" name="bank_account_number" placeholder="VD: 1023456789" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                    </div>
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:4px;">Tên chủ tài khoản</label>
                    <input type="text" id="modalBankAccountName" name="bank_account_name" placeholder="VD: NGUYEN VAN A" style="width:100%;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;text-transform: uppercase;">
                </div>
            </div>
            
            {{-- Các nút gửi đi hoặc tắt bỏ --}}
            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:12px;border-top:1px solid #f1f5f9;flex-shrink:0;">
                <button type="button" class="btn-lookup" style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:8px 16px;font-size:13px;border-radius:8px;" onclick="closeClaimModal()">Hủy</button>
                <button type="submit" class="btn-lookup" id="btnSubmitClaim" style="padding:8px 20px;background:#0046ab;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:13px;">Gửi yêu cầu</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// =========================================================================
// KHỞI TẠO CÁC BIẾN DOM VÀ BẮT SỰ KIỆN PHÍM ENTER (INITIALIZATION)
// Lấy các thẻ nhập liệu, nút bấm và vùng hiển thị kết quả từ giao diện
// =========================================================================
const imeiInput = document.getElementById('imeiInput'); // Ô nhập mã IMEI/Serial của khách hàng.
const btnLookup = document.getElementById('btnLookup'); // Nút bấm thực hiện hành động "Tra cứu".
const resultArea = document.getElementById('warrantyResult'); // Vùng trống dùng để hiển thị kết quả bảo hành khi tra cứu xong.

// Sự kiện: Khi khách đang trỏ vào ô nhập IMEI và nhấn phím "Enter", tự động gọi hàm tìm kiếm.
imeiInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); lookupWarranty(); }
});

// =========================================================================
// HÀM CHÍNH 1: TRA CỨU THÔNG TIN BẢO HÀNH QUA AJAX (lookupWarranty)
// Hàm này lấy IMEI khách nhập, gửi lên server để kiểm tra, và hiển thị thông tin trả về.
// =========================================================================
function lookupWarranty() {
    const imei = imeiInput.value.trim(); // Lấy giá trị IMEI khách nhập và xóa khoảng trắng dư thừa ở đầu/cuối.
    
    // Kiểm tra nhanh dưới giao diện xem khách đã nhập IMEI hợp lệ hay chưa (tối thiểu 8 ký tự).
    if (!imei || imei.length < 8) {
        Swal.fire({ icon: 'warning', title: 'Lưu ý', text: 'Vui lòng nhập mã IMEI/Serial hợp lệ (tối thiểu 8 ký tự).', confirmButtonColor: '#0046ab' });
        return;
    }

    // Hiệu ứng chờ: Thêm biểu tượng đang tải (spinner) và tạm thời vô hiệu hóa nút Tra cứu để chống bấm nhiều lần.
    btnLookup.classList.add('loading');
    btnLookup.disabled = true;
    resultArea.classList.remove('show'); // Ẩn kết quả cũ đi.

    // Gửi yêu cầu kiểm tra IMEI lên máy chủ thông qua đường dẫn AJAX (route "warranty.lookup").
    fetch('{{ route("warranty.lookup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, // Mã bảo mật CSRF tránh giả mạo yêu cầu.
            'Accept': 'application/json',
        },
        body: JSON.stringify({ imei: imei }) // Truyền mã IMEI dạng chuỗi JSON lên server.
    })
    .then(r => r.json().then(data => ({ status: r.status, body: data })))
    .then(res => {
        // Tải xong: Khôi phục lại trạng thái bình thường của nút Tra cứu.
        btnLookup.classList.remove('loading');
        btnLookup.disabled = false;

        // Nếu phiên làm việc hết hạn (do lâu không thao tác), yêu cầu người dùng tải lại trang.
        if (res.status === 419 || (res.body && res.body.message === 'CSRF token mismatch.')) {
            Swal.fire({
                icon: 'warning',
                title: 'Phiên làm việc hết hạn',
                text: 'Phiên làm việc của bạn đã hết hạn. Vui lòng tải lại trang để tiếp tục.',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Tải lại trang'
            }).then(() => {
                window.location.reload();
            });
            return;
        }

        const data = res.body;
        if (!data.success) {
            // Nếu không tìm thấy hoặc có lỗi nghiệp vụ từ máy chủ: hiển thị giao diện báo lỗi tương ứng.
            resultArea.innerHTML = renderError(data.message);
        } else {
            // Nếu tìm thấy: Gọi hàm dựng HTML để hiển thị thông tin chi tiết của sản phẩm và bảo hành.
            resultArea.innerHTML = renderResult(data);
        }
        
        // Hiện vùng kết quả và tự động cuộn màn hình mượt mà xuống khu vực thông tin này.
        resultArea.classList.add('show');
        resultArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    })
    .catch(err => {
        // Xử lý lỗi nếu mất mạng hoặc lỗi máy chủ không kết nối được.
        btnLookup.classList.remove('loading');
        btnLookup.disabled = false;
        resultArea.innerHTML = renderError('Đã xảy ra lỗi khi tra cứu. Vui lòng thử lại.');
        resultArea.classList.add('show');
    });
}

// =========================================================================
// CÁC HÀM PHỤ TRỢ: CHUYỂN ĐỔI TRẠNG THÁI VÀ ĐỊNH DẠNG CHỮ HIỂN THỊ
// Giúp hiển thị nội dung thân thiện cho người dùng cuối
// =========================================================================
function getStatusLabel(status) {
    const map = {
        active: 'Còn bảo hành', expired: 'Hết hạn bảo hành',
        paused: 'Tạm dừng bảo hành', rejected: 'Từ chối bảo hành', none: 'Chưa kích hoạt'
    };
    return map[status] || status;
}

function getStatusIcon(status) {
    const map = {
        active: 'fa-circle-check', expired: 'fa-circle-xmark',
        paused: 'fa-circle-pause', rejected: 'fa-ban', none: 'fa-circle-question'
    };
    return map[status] || 'fa-circle-question';
}

function getWarrantyTypeLabel(type) {
    const map = { manufacturer: 'Chính hãng', extended: 'Mở rộng', replacement: 'Đổi mới' };
    return map[type] || type;
}

function getRepairStatusLabel(s) {
    const map = { Received: 'Đã tiếp nhận', Waiting_Parts: 'Chờ linh kiện', Done: 'Hoàn tất' };
    return map[s] || s;
}

// =========================================================================
// HÀM DỰNG GIAO DIỆN BÁO LỖI KHI KHÔNG TÌM THẤY SẢN PHẨM (renderError)
// =========================================================================
function renderError(msg) {
    return `<div class="result-error">
        <div class="error-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <h3>Không tìm thấy</h3>
        <p>${msg}</p>
    </div>`;
}

// =========================================================================
// HÀM CHÍNH 3: DỰNG GIAO DIỆN KẾT QUẢ BẢO HÀNH (renderResult)
// Xử lý và chuyển đổi dữ liệu thô từ máy chủ thành giao diện đẹp mắt cho người dùng.
// =========================================================================
function renderResult(d) {
    const imgSrc = d.product_image ? `/storage/${d.product_image}` : 'https://via.placeholder.com/80x80?text=N/A';
    const statusClass = d.warranty_status;

    // A. Vẽ thanh tiến trình thời gian bảo hành (nếu có gói bảo hành hoạt động).
    let progressHTML = '';
    if (d.has_warranty) {
        const start = new Date(d.start_date.split('/').reverse().join('-'));
        const end = new Date(d.end_date.split('/').reverse().join('-'));
        const now = new Date();
        const total = end - start;
        const elapsed = now - start;
        let pct = total > 0 ? Math.max(0, Math.min(100, 100 - (elapsed / total * 100))) : 0;
        let barClass = pct > 50 ? '' : pct > 20 ? 'low' : 'critical';
        if (d.warranty_status === 'expired') { pct = 0; barClass = 'expired'; }

        progressHTML = `<div class="warranty-progress">
            <div class="progress-label">
                <span>${d.start_date}</span>
                <span>${d.days_left > 0 ? 'Còn ' + d.days_left + ' ngày' : 'Đã hết hạn'}</span>
                <span>${d.end_date}</span>
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill ${barClass}" style="width: ${pct}%"></div>
            </div>
        </div>`;
    }

    // B. Vẽ lưới thông tin ngày bắt đầu, kết thúc, loại gói bảo hành.
    let infoHTML = '';
    if (d.has_warranty) {
        const daysClass = d.days_left > 90 ? 'text-green' : d.days_left > 0 ? 'text-orange' : 'text-red';
        infoHTML = `<div class="info-grid">
            <div class="info-item">
                <div class="info-label"><i class="fa-regular fa-calendar"></i> Ngày bắt đầu</div>
                <div class="info-value">${d.start_date}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fa-regular fa-calendar-check"></i> Ngày kết thúc</div>
                <div class="info-value">${d.end_date}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fa-solid fa-clock"></i> Số ngày còn lại</div>
                <div class="info-value ${daysClass}">${d.days_left > 0 ? d.days_left + ' ngày' : 'Đã hết hạn'}</div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fa-solid fa-tag"></i> Loại bảo hành</div>
                <div class="info-value">${getWarrantyTypeLabel(d.warranty_type)}</div>
            </div>
        </div>`;
    }

    let noteHTML = d.note ? `<div class="warranty-note"><i class="fa-solid fa-circle-info"></i> ${d.note}</div>` : '';

    // C. Vẽ danh sách lịch sử sửa chữa phần cứng của máy tại cửa hàng.
    let repairHTML = '';
    if (d.repair_history && d.repair_history.length > 0) {
        let items = d.repair_history.map(r => `
            <div class="repair-item">
                <span class="repair-id">#${r.ticket_id}</span>
                <span class="repair-issue">${r.issue}</span>
                <span class="repair-status ${r.status}">${getRepairStatusLabel(r.status)}</span>
            </div>`).join('');
        repairHTML = `<div class="repair-history">
            <h4><i class="fa-solid fa-wrench"></i> Lịch sử sửa chữa</h4>${items}</div>`;
    }

    // D. Vẽ danh sách các yêu cầu bảo hành/đổi trả trực tuyến đã gửi trước đó kèm phản hồi admin.
    let claimsHTML = '';
    if (d.claims_history && d.claims_history.length > 0) {
        let items = d.claims_history.map(c => {
            let typeLabel = '';
            let typeColor = '';
            if (c.claim_type === 'warranty') {
                typeLabel = 'Bảo hành';
                typeColor = '#0046ab';
            } else if (c.claim_type === 'return') {
                typeLabel = 'Đổi trả';
                typeColor = '#f59e0b';
            } else {
                typeLabel = 'Đổi máy';
                typeColor = '#a855f7';
            }

            let statusLabel = '';
            let statusClass = '';
            if (c.status === 'pending') {
                statusLabel = 'Chờ duyệt';
                statusClass = 'pending';
            } else if (c.status === 'approved') {
                statusLabel = 'Đã duyệt';
                statusClass = 'approved';
            } else {
                statusLabel = 'Từ chối';
                statusClass = 'rejected';
            }

            let adminNoteHTML = c.admin_note ? `<div style="margin-top: 6px; padding: 8px 12px; background: #f8fafc; border-left: 3px solid #cbd5e1; font-size: 12px; color: #475569; border-radius: 0 4px 4px 0;"><i class="fa-solid fa-reply"></i> <strong>Phản hồi:</strong> ${c.admin_note}</div>` : '';
            let mediaHTML = c.media_path ? `<div style="margin-top: 6px;"><a href="${c.media_path}" target="_blank" style="font-size: 11px; color: #2563eb; display: inline-flex; align-items: center; gap: 4px; text-decoration: underline;"><i class="fa-solid fa-paperclip"></i> Xem tệp minh họa</a></div>` : '';

            return `
                <div style="padding: 14px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; background: #fff; text-align: left;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-weight: 700; font-size: 13px; color: ${typeColor};"><i class="fa-solid fa-circle-dot"></i> Yêu cầu ${typeLabel}</span>
                        <span class="claim-status-badge ${statusClass}">${statusLabel}</span>
                    </div>
                    <div style="font-size: 11px; color: #94a3b8; margin-bottom: 4px;">Ngày gửi: ${c.created_at || '—'}</div>
                    <div style="font-size: 13px; color: #475569; line-height: 1.4;"><strong>Lý do:</strong> ${c.reason}</div>
                    ${mediaHTML}
                    ${adminNoteHTML}
                </div>
            `;
        }).join('');

        claimsHTML = `
            <div class="repair-history" style="margin-top: 24px;">
                <h4 style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                    <i class="fa-solid fa-list-check" style="color: #0046ab;"></i> Lịch sử yêu cầu dịch vụ
                </h4>
                ${items}
            </div>
        `;
    }

    // E. Các nút bấm hành động (CTA)
    let ctaHTML = '';
    let warrantyBtnDisabled = !d.can_claim_warranty ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';
    let returnBtnDisabled = !d.can_claim_return ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : '';
    
    let warrantyTooltip = !d.can_claim_warranty ? ' (Không đủ điều kiện hoặc hết hạn)' : '';
    let returnTooltip = !d.can_claim_return ? ' (Hết hạn hoặc không hỗ trợ)' : '';

    ctaHTML = `
        <div class="warranty-cta" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <button type="button" class="btn-create-ticket" ${warrantyBtnDisabled} onclick="openClaimModal('${d.imei}', '${d.product_name}', 'warranty')">
                <i class="fa-solid fa-screwdriver-wrench"></i> Yêu cầu bảo hành${warrantyTooltip}
            </button>
            <button type="button" class="btn-create-ticket" style="background: linear-gradient(135deg, #f59e0b, #d97706);" ${returnBtnDisabled} onclick="openClaimModal('${d.imei}', '${d.product_name}', 'return')">
                <i class="fa-solid fa-rotate-left"></i> Yêu cầu đổi trả${returnTooltip}
            </button>
        </div>
    `;

    return `<div class="result-card">
        <div class="result-header">
            <img src="${imgSrc}" alt="" class="result-product-img">
            <div class="result-product-info">
                <h3>${d.product_name}</h3>
                ${d.variant_label ? `<div class="variant-label">${d.variant_label}</div>` : ''}
                <div class="imei-label">IMEI: ${d.imei}</div>
            </div>
            <div class="warranty-status-badge ${statusClass}">
                <i class="fa-solid ${getStatusIcon(d.warranty_status)}"></i>
                ${getStatusLabel(d.warranty_status)}
            </div>
        </div>
        <div class="result-body">
            ${infoHTML}
            ${progressHTML}
            ${noteHTML}
            ${repairHTML}
            ${ctaHTML}
            ${claimsHTML}
        </div>
    </div>`;
}
// HÀM CHÍNH 4: MỞ CỬA SỔ POPUP NHẬP YÊU CẦU DỊCH VỤ (openClaimModal)
// Cấu hình giao diện form nhập tùy theo khách bấm nút "Bảo hành" hay "Đổi trả".
// =========================================================================
function openClaimModal(imei, productName, defaultType) {
    // 1. Lưu trữ thông tin ẩn và hiển thị thông tin sản phẩm/IMEI lên giao diện
    document.getElementById('modalImei').value = imei; // Gán ngầm IMEI vào input hidden để gửi lên server.
    document.getElementById('modalProductNameDisplay').textContent = productName; // Hiển thị tên sản phẩm lên form.
    document.getElementById('modalImeiDisplay').textContent = imei; // Hiển thị mã IMEI lên form.

    const sel    = document.getElementById('modalClaimType');
    const header = document.getElementById('claimModalHeader');
    const title  = document.getElementById('claimModalTitle');
    const btn    = document.getElementById('btnSubmitClaim');

    // 2. Tự động chuyển màu sắc chủ đạo của modal để khách hàng không bị nhầm lẫn
    if (defaultType === 'warranty') {
        // Giao diện màu xanh dương chuẩn cho Bảo hành sửa chữa
        sel.innerHTML = '<option value="warranty">Bảo hành sửa chữa (Miễn phí)</option>';
        header.style.background = '#0046ab';
        title.textContent = 'Gửi yêu cầu bảo hành chính hãng';
        btn.style.background = '#0046ab';
        btn.textContent = 'Gửi yêu cầu bảo hành';
    } else {
        // Giao diện màu cam nổi bật cho Đổi trả hàng / Hoàn tiền
        sel.innerHTML = '<option value="return">Đổi trả hàng hoàn tiền</option><option value="exchange">Đổi máy mới/khách</option>';
        header.style.background = '#f59e0b';
        title.textContent = 'Gửi yêu cầu đổi trả sản phẩm';
        btn.style.background = '#f59e0b';
        btn.textContent = 'Gửi yêu cầu đổi trả';
    }
    sel.value = defaultType;
    
    // 3. Khởi tạo lại trạng thái ban đầu của các trường nhập liệu
    document.getElementById('modalReason').value = ''; // Reset trống ô lý do nhập.
    const media = document.getElementById('modalMediaFile');
    if (media) media.value = ''; // Reset trống tệp đính kèm đã chọn.
    const refMethod = document.getElementById('modalRefundMethod');
    if (refMethod) refMethod.value = 'bank_transfer'; // Mặc định phương thức nhận lại tiền là chuyển khoản.

    // 4. Gọi hàm kiểm tra để ẩn/hiện các ô nhập tài khoản ngân hàng dựa trên phương thức hoàn tiền
    toggleBankFields();

    // 5. Mở popup kèm hiệu ứng zoom-in mượt mà tránh giật lag màn hình
    const modal   = document.getElementById('claimModal');
    const content = document.getElementById('claimModalContent');
    modal.style.display = 'flex';
    setTimeout(() => { 
        content.style.transform = 'scale(1)'; 
        content.style.opacity = '1'; 
    }, 10);
}

// =========================================================================
// HÀM ĐÓNG CỬA SỔ POPUP MODAL (closeClaimModal)
// Thu nhỏ và làm mờ dần cửa sổ trước khi ẩn hoàn toàn khỏi màn hình
// =========================================================================
function closeClaimModal() {
    const content = document.getElementById('claimModalContent');
    content.style.transform = 'scale(0.95)';
    content.style.opacity   = '0';
    setTimeout(() => { 
        document.getElementById('claimModal').style.display = 'none'; 
    }, 300);
}

// Bắt sự kiện bàn phím: Cho phép nhấn phím Esc để tắt nhanh cửa sổ popup đang mở
document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') closeClaimModal(); 
});

// =========================================================================
// HÀM CHÍNH 5: GỬI THÔNG TIN FORM LÊN SERVER QUA AJAX (submitClaim)
// Đóng gói toàn bộ dữ liệu, kiểm tra dung lượng tệp tin và thực hiện POST yêu cầu
// =========================================================================
function submitClaim(e) {
    e.preventDefault(); // Ngăn chặn trình duyệt tải lại trang theo cơ chế submit form mặc định.
    const btn = document.getElementById('btnSubmitClaim');
    const oldText = btn.innerHTML;
    
    // Khóa nút gửi và hiển thị trạng thái xoay vòng để chống khách nhấn liên tiếp nhiều lần
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...';

    // Kiểm tra nhanh kích thước file hình ảnh/video đính kèm dưới client (Tối đa 20MB)
    const media = document.getElementById('modalMediaFile');
    if (media && media.files.length > 0 && media.files[0].size > 20 * 1024 * 1024) {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Tệp quá lớn', 
            text: 'Dung lượng tệp không được vượt quá 20MB.', 
            confirmButtonColor: '#ef4444' 
        });
        btn.disabled = false; 
        btn.innerHTML = oldText; 
        return;
    }

    // Gửi yêu cầu qua cơ chế API Fetch POST đến backend xử lý lưu trữ database
    fetch('/warranty/claim', {
        method: 'POST',
        headers: { 
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
            'Accept': 'application/json' 
        },
        body: new FormData(document.getElementById('claimForm')) // Đóng gói tự động toàn bộ input và file đính kèm
    })
    .then(r => r.json().then(data => ({ status: r.status, body: data })))
    .then(res => {
        btn.disabled = false; 
        btn.innerHTML = oldText; // Khôi phục lại nút bấm ban đầu sau khi có phản hồi
        
        if (res.status !== 200) {
            let msg = res.body.message || 'Đã có lỗi xảy ra.';
            
            // Xử lý trường hợp phiên làm việc CSRF token hết hạn
            if (res.status === 419 || msg === 'CSRF token mismatch.') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Phiên làm việc hết hạn',
                    text: 'Phiên làm việc của bạn đã hết hạn. Vui lòng tải lại trang để tiếp tục.',
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Tải lại trang'
                }).then(() => {
                    window.location.reload();
                });
                return;
            }
            
            // Nếu backend trả về danh sách lỗi nhập liệu cụ thể
            if (res.body.errors) msg = Object.values(res.body.errors).flat().join('<br>');
            Swal.fire({ icon: 'error', title: 'Lỗi gửi yêu cầu', html: msg, confirmButtonColor: '#ef4444' });
        } else {
            // Gửi thành công: Đóng modal, hiển thị thông báo chúc mừng và tự động chạy lại tìm kiếm để cập nhật lịch sử
            closeClaimModal();
            Swal.fire({ icon: 'success', title: 'Thành công', text: res.body.message, confirmButtonColor: '#0046ab' })
                .then(() => lookupWarranty());
        }
    })
    .catch(() => {
        btn.disabled = false; 
        btn.innerHTML = oldText;
        Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Không thể kết nối đến máy chủ.', confirmButtonColor: '#ef4444' });
    });
}

// =========================================================================
// HÀM CHÍNH 6: ẨN/HIỆN PHẦN NHẬP TÀI KHOẢN NGÂN HÀNG (toggleBankFields)
// Tự động kiểm tra loại yêu cầu và phương thức hoàn tiền để ẩn hiện, bắt buộc nhập
// =========================================================================
function toggleBankFields() {
    const sel = document.getElementById('modalClaimType'); // Lựa chọn loại yêu cầu.
    const refundMethodSection = document.getElementById('refundMethodSection'); // Vùng lựa chọn phương thức hoàn tiền.
    const bankSection = document.getElementById('bankDetailsSection'); // Vùng thông tin tài khoản ngân hàng.
    if (!sel) return;

    const refundMethodSelect = document.getElementById('modalRefundMethod');
    const isReturn = (sel.value === 'return'); // Kiểm tra có phải là Đổi trả hoàn tiền hay không.

    // 1. Ẩn/Hiện phần chọn phương thức nhận lại tiền (chỉ hiện khi chọn Đổi trả hoàn tiền)
    if (refundMethodSection) {
        refundMethodSection.style.display = isReturn ? 'flex' : 'none';
    }

    // 2. Ẩn/Hiện và gán thuộc tính bắt buộc nhập (required) cho các trường ngân hàng
    if (bankSection) {
        const inputs = bankSection.querySelectorAll('input');
        // Chỉ hiển thị ngân hàng khi chọn Đổi trả VÀ chọn phương thức nhận tiền là Chuyển khoản ngân hàng
        const isBankTransfer = isReturn && (refundMethodSelect ? refundMethodSelect.value === 'bank_transfer' : true);

        if (isBankTransfer) {
            bankSection.style.display = 'flex'; // Hiển thị form ngân hàng lên màn hình
            inputs.forEach(input => input.setAttribute('required', 'true')); // Bắt buộc khách phải nhập đầy đủ
        } else {
            bankSection.style.display = 'none'; // Ẩn form ngân hàng
            inputs.forEach(input => {
                input.removeAttribute('required'); // Bỏ bắt buộc nhập để không bị chặn submit
                input.value = ''; // Xóa sạch dữ liệu cũ khách đã nhập để tránh gửi nhầm thông tin
            });
        }
    }
}

// Theo dõi các sự kiện thay đổi lựa chọn (change) để tự động kích hoạt ẩn hiện giao diện tương ứng
document.getElementById('modalClaimType').addEventListener('change', toggleBankFields);
const refMethodEl = document.getElementById('modalRefundMethod');
if (refMethodEl) {
    refMethodEl.addEventListener('change', toggleBankFields);
}

</script>
@endpush
