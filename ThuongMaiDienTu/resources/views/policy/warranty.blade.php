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
<div class="warranty-page">
    <div class="container">
        {{-- Hero Section --}}
        <div class="warranty-hero">
            <div class="hero-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <h1>Tra cứu & Chính sách bảo hành</h1>
            <p>Nhập mã IMEI/Serial để kiểm tra bảo hành hoặc tham khảo các chính sách hậu mãi bên dưới</p>
        </div>

        {{-- Search Box --}}
        <div class="warranty-search-box" id="warrantySearchBox">
            <form id="warrantyForm" onsubmit="return false;">
                <div class="search-input-group">
                    <input type="text" id="imeiInput" name="imei"
                           placeholder="Nhập mã IMEI hoặc Serial Number..."
                           maxlength="30" autocomplete="off" required>
                    <button type="button" class="btn-lookup" id="btnLookup" onclick="lookupWarranty()">
                        <i class="fa-solid fa-spinner spinner"></i>
                        <span class="btn-text"><i class="fa-solid fa-magnifying-glass"></i> Tra cứu</span>
                    </button>
                </div>
                <div class="search-hint">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Mã IMEI thường nằm trên hộp sản phẩm hoặc quay số *#06# trên điện thoại</span>
                </div>
            </form>
        </div>

        {{-- Result Area --}}
        <div class="warranty-result" id="warrantyResult"></div>

        {{-- How it works --}}
        <div class="how-it-works">
            <h3>Cách tra cứu bảo hành</h3>
            <div class="steps-grid">
                <div class="step-item">
                    <div class="step-icon"><i class="fa-solid fa-barcode"></i></div>
                    <h4>Bước 1</h4>
                    <p>Tìm mã IMEI/Serial trên hộp sản phẩm hoặc quay số *#06#</p>
                </div>
                <div class="step-item">
                    <div class="step-icon"><i class="fa-solid fa-keyboard"></i></div>
                    <h4>Bước 2</h4>
                    <p>Nhập mã IMEI vào ô tra cứu phía trên</p>
                </div>
                <div class="step-item">
                    <div class="step-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <h4>Bước 3</h4>
                    <p>Xem kết quả trạng thái bảo hành ngay lập tức</p>
                </div>
            </div>
        </div>

        {{-- Integrated Policy Contents --}}
        <div class="policy-wrapper">
            <h3 style="text-align: center; font-size: 20px; font-weight: 700; margin-bottom: 24px; color: #1e293b;">
                Chính sách bảo hành & Đổi trả
            </h3>

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

<!-- Claim Request Modal -->
<div id="claimModal" class="fixed inset-0 z-[9999] hidden" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); justify-content: center; align-items: center;">
    <div id="claimModalContent" style="background: #fff; border-radius: 16px; width: 92%; max-width: 500px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); transform: scale(0.95); opacity: 0; transition: transform 0.3s ease, opacity 0.3s ease;">
        <!-- Modal Header -->
        <div style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0;">Gửi yêu cầu dịch vụ</h3>
            <button type="button" onclick="closeClaimModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <!-- Modal Body -->
        <form id="claimForm" style="padding: 24px;" onsubmit="submitClaim(event)">
            @csrf
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px;">Sản phẩm</label>
                <input type="text" id="modalProductName" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; color: #64748b;" readonly>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px;">Mã IMEI/Serial</label>
                <input type="text" id="modalImei" name="imei_serial" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; color: #64748b; font-family: monospace;" readonly>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px;">Loại yêu cầu</label>
                <select id="modalClaimType" name="claim_type" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; font-weight: 500; outline: none; background: #fff;" required>
                    <option value="warranty">Bảo hành sửa chữa</option>
                    <option value="return">Đổi trả hàng hoàn tiền</option>
                    <option value="exchange">Đổi máy mới/khách</option>
                </select>
            </div>
            <div style="margin-bottom: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px;">Họ tên</label>
                    <input type="text" id="modalCustomerName" name="customer_name" value="{{ auth()->user() ? auth()->user()->full_name : '' }}" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;" required>
                </div>
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px;">Số điện thoại</label>
                    <input type="text" id="modalCustomerPhone" name="customer_phone" value="{{ auth()->user() ? auth()->user()->phone_number : '' }}" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;" required>
                </div>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px;">Email liên hệ</label>
                <input type="email" id="modalCustomerEmail" name="customer_email" value="{{ auth()->user() ? auth()->user()->email : '' }}" style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #64748b; margin-bottom: 6px;">Lý do yêu cầu</label>
                <textarea id="modalReason" name="reason" rows="3" placeholder="Mô tả cụ thể lỗi thiết bị hoặc lý do muốn đổi trả..." style="width: 100%; padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; resize: none;" required></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn-lookup" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 10px 20px;" onclick="closeClaimModal()">Hủy</button>
                <button type="submit" class="btn-lookup" id="btnSubmitClaim" style="padding: 10px 20px;">Gửi yêu cầu</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const imeiInput = document.getElementById('imeiInput');
const btnLookup = document.getElementById('btnLookup');
const resultArea = document.getElementById('warrantyResult');

// Enter key
imeiInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); lookupWarranty(); }
});

function lookupWarranty() {
    const imei = imeiInput.value.trim();
    if (!imei || imei.length < 8) {
        Swal.fire({ icon: 'warning', title: 'Lưu ý', text: 'Vui lòng nhập mã IMEI/Serial hợp lệ (tối thiểu 8 ký tự).', confirmButtonColor: '#0046ab' });
        return;
    }

    btnLookup.classList.add('loading');
    btnLookup.disabled = true;
    resultArea.classList.remove('show');

    fetch('{{ route("warranty.lookup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ imei: imei })
    })
    .then(r => r.json())
    .then(data => {
        btnLookup.classList.remove('loading');
        btnLookup.disabled = false;

        if (!data.success) {
            resultArea.innerHTML = renderError(data.message);
        } else {
            resultArea.innerHTML = renderResult(data);
        }
        resultArea.classList.add('show');
        resultArea.scrollIntoView({ behavior: 'smooth', block: 'center' });
    })
    .catch(err => {
        btnLookup.classList.remove('loading');
        btnLookup.disabled = false;
        resultArea.innerHTML = renderError('Đã xảy ra lỗi khi tra cứu. Vui lòng thử lại.');
        resultArea.classList.add('show');
    });
}

function renderError(msg) {
    return `<div class="result-error">
        <div class="error-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <h3>Không tìm thấy</h3>
        <p>${msg}</p>
    </div>`;
}

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

function renderResult(d) {
    const imgSrc = d.product_image ? `/storage/${d.product_image}` : 'https://via.placeholder.com/80x80?text=N/A';
    const statusClass = d.warranty_status;

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
            
            <div class="warranty-cta" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <button type="button" class="btn-create-ticket" onclick="openClaimModal('${d.imei}', '${d.product_name}', 'warranty')">
                    <i class="fa-solid fa-screwdriver-wrench"></i> Yêu cầu bảo hành
                </button>
                <button type="button" class="btn-create-ticket" style="background: linear-gradient(135deg, #f59e0b, #d97706);" onclick="openClaimModal('${d.imei}', '${d.product_name}', 'return')">
                    <i class="fa-solid fa-rotate-left"></i> Yêu cầu đổi trả
                </button>
            </div>
        </div>
    </div>`;
}

function switchPolicyTab(tabId, el) {
    document.querySelectorAll('.policy-nav a').forEach(a => a.classList.remove('active'));
    el.classList.add('active');
    
    document.querySelectorAll('.policy-section').forEach(s => s.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
}

function openClaimModal(imei, productName, defaultType) {
    document.getElementById('modalImei').value = imei;
    document.getElementById('modalProductName').value = productName;
    document.getElementById('modalClaimType').value = defaultType;
    document.getElementById('modalReason').value = '';
    
    const modal = document.getElementById('claimModal');
    const content = document.getElementById('claimModalContent');
    
    modal.style.display = 'flex';
    modal.classList.remove('hidden');
    
    setTimeout(() => {
        content.style.transform = 'scale(1)';
        content.style.opacity = '1';
    }, 10);
}

function closeClaimModal() {
    const modal = document.getElementById('claimModal');
    const content = document.getElementById('claimModalContent');
    
    content.style.transform = 'scale(0.95)';
    content.style.opacity = '0';
    
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }, 300);
}

function submitClaim(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitClaim');
    const oldText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Đang gửi...';
    
    const formData = {
        imei_serial: document.getElementById('modalImei').value,
        customer_name: document.getElementById('modalCustomerName').value,
        customer_phone: document.getElementById('modalCustomerPhone').value,
        customer_email: document.getElementById('modalCustomerEmail').value,
        claim_type: document.getElementById('modalClaimType').value,
        reason: document.getElementById('modalReason').value,
    };
    
    fetch('{{ route("warranty.claim.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(r => r.json().then(data => ({ status: r.status, body: data })))
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = oldText;
        
        if (res.status !== 200) {
            let errorMsg = res.body.message || 'Đã có lỗi xảy ra.';
            if (res.body.errors) {
                errorMsg = Object.values(res.body.errors).flat().join('<br>');
            }
            Swal.fire({
                icon: 'error',
                title: 'Lỗi gửi yêu cầu',
                html: errorMsg,
                confirmButtonColor: '#ef4444'
            });
        } else {
            closeClaimModal();
            Swal.fire({
                icon: 'success',
                title: 'Thành công',
                text: res.body.message,
                confirmButtonColor: '#0046ab'
            });
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = oldText;
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại.',
            confirmButtonColor: '#ef4444'
        });
    });
}
</script>
@endpush
