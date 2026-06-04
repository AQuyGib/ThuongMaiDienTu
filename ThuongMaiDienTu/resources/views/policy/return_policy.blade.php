@extends('layouts.app')
@section('title', 'Chính sách đổi trả & Hoàn tiền - DIENMAYPRO')

@push('styles')
<style>
.return-page { padding: 40px 0 80px; min-height: 70vh; background-color: #f8fafc; }
.return-hero {
    text-align: center; padding: 50px 20px 40px;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
    border-radius: 20px; margin-bottom: 40px; position: relative; overflow: hidden;
}
.return-hero::before {
    content: ''; position: absolute; top: -50%; right: -20%; width: 500px; height: 500px;
    background: radial-gradient(circle, rgba(0,210,255,0.1) 0%, transparent 70%);
    border-radius: 50%;
}
.return-hero h1 {
    color: #fff; font-size: 30px; font-weight: 800; margin-bottom: 12px;
    position: relative; z-index: 1;
}
.return-hero p {
    color: rgba(255,255,255,0.75); font-size: 15px; position: relative; z-index: 1;
}
.return-hero .hero-icon {
    font-size: 46px; color: #38bdf8; margin-bottom: 16px;
    position: relative; z-index: 1;
}

.return-nav {
    display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-bottom: 30px;
}
.return-nav a {
    padding: 10px 20px; background: #fff; border: 2px solid #e2e8f0; border-radius: 10px;
    font-size: 13px; font-weight: 700; color: #475569; transition: .2s; text-decoration: none;
    display: flex; align-items: center; gap: 8px;
}
.return-nav a:hover, .return-nav a.active {
    border-color: #0046ab; color: #0046ab; background: #f0f7ff;
}

.return-content {
    max-width: 960px; margin: 0 auto; background: #fff; border-radius: 16px;
    padding: 40px; box-shadow: 0 5px 30px rgba(0,0,0,.06);
}
.return-section {
    margin-bottom: 40px; border-bottom: 1px solid #f1f5f9; padding-bottom: 30px;
}
.return-section:last-child {
    border-bottom: none; padding-bottom: 0; margin-bottom: 0;
}
.return-section h2 {
    font-size: 20px; font-weight: 800; color: #0f172a; margin-bottom: 20px;
    padding-bottom: 10px; border-bottom: 2px solid #eff6ff;
    display: flex; align-items: center; gap: 10px;
}
.return-section h2 i {
    color: #0046ab; font-size: 18px;
}
.return-section h3 {
    font-size: 16px; font-weight: 700; color: #1e293b; margin: 20px 0 12px;
}

.return-table-wrapper {
    overflow-x: auto; margin: 16px 0; border-radius: 12px; border: 1px solid #e2e8f0;
}
.return-table {
    width: 100%; border-collapse: collapse; font-size: 13px; min-width: 600px;
}
.return-table thead th {
    background: #0f172a; color: #fff; padding: 12px 16px; text-align: left;
    font-weight: 700; font-size: 12px; text-transform: uppercase;
}
.return-table tbody td {
    padding: 12px 16px; border-bottom: 1px solid #f1f5f9; color: #475569;
    vertical-align: middle; line-height: 1.6;
}
.return-table tbody tr:last-child td {
    border-bottom: none;
}
.return-table tbody tr:nth-child(even) {
    background: #f8fafc;
}
.return-table tbody tr:hover {
    background: #eff6ff;
}

.return-note {
    padding: 16px 20px; background: #eff6ff; border-radius: 10px;
    border-left: 4px solid #0046ab; font-size: 13px; color: #1e40af;
    line-height: 1.6; margin: 16px 0;
}
.return-note.warning {
    background: #fffbeb; border-color: #d97706; color: #b45309;
}
.return-note i {
    margin-right: 6px;
}

.return-list {
    margin: 12px 0; padding-left: 22px;
}
.return-list li {
    margin-bottom: 10px; font-size: 14px; color: #475569; line-height: 1.6;
}
.return-list li strong {
    color: #0f172a;
}

.return-section p {
    font-size: 14px; line-height: 1.7; color: #475569; margin-bottom: 14px;
}

.grid-2 {
    display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 16px;
}

.refund-timeline-card {
    background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0;
}
.refund-timeline-card h4 {
    font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 12px;
    display: flex; align-items: center; gap: 8px;
}
.refund-timeline-card h4 i {
    color: #0046ab;
}
.refund-timeline-card ul {
    list-style: none; padding: 0; margin: 0;
}
.refund-timeline-card li {
    padding: 10px 0; border-bottom: 1px solid #e2e8f0;
    font-size: 13px; color: #475569; display: flex; justify-content: space-between;
}
.refund-timeline-card li:last-child {
    border-bottom: none; padding-bottom: 0;
}
.refund-timeline-card li span.method {
    font-weight: 600; color: #0f172a;
}
.refund-timeline-card li span.time {
    font-weight: 700; color: #16a34a;
}

@media(max-width:768px){
    .return-content { padding: 20px; }
    .grid-2 { grid-template-columns: 1fr; gap: 12px; }
    .return-nav { gap: 6px; }
    .return-nav a { padding: 8px 14px; font-size: 12px; }
}
</style>
@endpush

@section('content')
<div class="return-page">
    <div class="container">
        {{-- Hero --}}
        <div class="return-hero">
            <div class="hero-icon"><i class="fa-solid fa-right-left"></i></div>
            <h1>Chính sách hủy giao dịch & Đổi trả hàng</h1>
            <p>Chi tiết quy định đổi mới, hoàn trả sản phẩm và thời hạn hoàn tiền tại DIENMAYPRO</p>
        </div>

        {{-- Nav --}}
        <div class="return-nav">
            <a href="#huy-giao-dich" class="active"><i class="fa-solid fa-rectangle-xmark"></i> 1. Hủy giao dịch</a>
            <a href="#doi-tra"><i class="fa-solid fa-arrows-rotate"></i> 2. Chính sách đổi trả</a>
            <a href="#dieu-kien-tra"><i class="fa-solid fa-circle-check"></i> 3. Điều kiện & Hướng dẫn</a>
            <a href="#hoan-tien"><i class="fa-solid fa-wallet"></i> 4. Thời gian hoàn tiền</a>
            <a href="#nhom-special"><i class="fa-solid fa-tags"></i> 5. Quy định theo nhóm sản phẩm</a>
        </div>

        {{-- Content --}}
        <div class="return-content">
            
            {{-- 1. CHÍNH SÁCH HỦY GIAO DỊCH --}}
            <div class="return-section" id="huy-giao-dich">
                <h2><i class="fa-solid fa-rectangle-xmark"></i> 1. Chính sách hủy giao dịch</h2>
                <h3>1.1. Điều kiện hủy giao dịch:</h3>
                <p>Khách hàng có thể hủy giao dịch kể từ lúc bấm nút <strong>"Đặt hàng"</strong> đến trước thời điểm nhận hàng thành công.</p>
                
                <h3>1.2. Phương thức hủy giao dịch:</h3>
                <p>Sau khi đặt hàng thành công, khi muốn hủy giao dịch, quý khách hàng vui lòng thực hiện một trong hai cách:</p>
                <ul class="return-list">
                    <li><strong>Liên hệ hỗ trợ:</strong> Gọi điện thoại lên tổng đài <strong>1800.2097</strong> (Miền Nam) hoặc <strong>1800.2044</strong> (Miền Bắc), gửi email về <strong>cskh@cellphones.com.vn</strong> hoặc nhắn tin trực tiếp trên Fanpage chính thức để báo hủy.</li>
                    <li><strong>Từ chối nhận hàng:</strong> Từ chối nhận hàng và xác nhận hủy mua sản phẩm trực tiếp khi đơn vị vận chuyển giao hàng tới.</li>
                </ul>
            </div>

            {{-- 2. CHÍNH SÁCH ĐỔI, TRẢ HÀNG --}}
            <div class="return-section" id="doi-tra">
                <h2><i class="fa-solid fa-arrows-rotate"></i> 2. Chính sách đổi, trả hàng</h2>
                <h3>2.1. Thời gian đổi trả và phí nhập lại:</h3>
                <p>Quy định thời gian đổi mới tiêu chuẩn và mức phí áp dụng khi đổi trả sản phẩm lỗi hoặc đổi trả theo nhu cầu:</p>
                
                <div class="return-table-wrapper">
                    <table class="return-table">
                        <thead>
                            <tr>
                                <th>Loại sản phẩm</th>
                                <th>Thời gian đổi mới</th>
                                <th>Phí trong hạn (Mới)</th>
                                <th>Phí trong hạn (Cũ)</th>
                                <th>Ngoài hạn (Mới)</th>
                                <th>Ngoài hạn (Cũ)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Điện thoại / Máy tính bảng / Macbook</strong></td>
                                <td>30 ngày</td>
                                <td>20%</td>
                                <td>15%</td>
                                <td>Thoả thuận</td>
                                <td>Thoả thuận</td>
                            </tr>
                            <tr>
                                <td><strong>Apple watch</strong></td>
                                <td>30 ngày</td>
                                <td>20%</td>
                                <td>20%</td>
                                <td>Thoả thuận</td>
                                <td>Thoả thuận</td>
                            </tr>
                            <tr>
                                <td><strong>Samsung watch</strong></td>
                                <td>30 ngày</td>
                                <td>30%</td>
                                <td>30%</td>
                                <td>Thoả thuận</td>
                                <td>Thoả thuận</td>
                            </tr>
                            <tr>
                                <td><strong>Laptop</strong></td>
                                <td>30 ngày</td>
                                <td>20%</td>
                                <td>Không áp dụng</td>
                                <td>Không áp dụng</td>
                                <td>Không áp dụng</td>
                            </tr>
                            <tr>
                                <td><strong>Phụ kiện &lt; 1 triệu</strong></td>
                                <td>1 năm (Mới) / 30 ngày (Cũ)</td>
                                <td>Không áp dụng</td>
                                <td>Không áp dụng</td>
                                <td>Không áp dụng</td>
                                <td>Không áp dụng</td>
                            </tr>
                            <tr>
                                <td><strong>Phụ kiện &gt; 1 triệu</strong></td>
                                <td>15 ngày</td>
                                <td>Không áp dụng (*)</td>
                                <td>Không áp dụng (*)</td>
                                <td>Không áp dụng (**)</td>
                                <td>Không áp dụng (**)</td>
                            </tr>
                            <tr>
                                <td><strong>Bảo hành mở rộng</strong></td>
                                <td>Không áp dụng</td>
                                <td>Không áp dụng (***)</td>
                                <td>Không áp dụng (***)</td>
                                <td>Không áp dụng</td>
                                <td>Không áp dụng</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="return-note">
                    <strong>Ghi chú chi tiết:</strong><br>
                    (*) Riêng <strong>Airpod</strong> nhập trả trong thời gian tiêu chuẩn sẽ trừ phí 20%.<br>
                    (**) Ngoài thời gian tiêu chuẩn, <strong>Airpod</strong> sẽ được nhập lại theo giá thỏa thuận.<br>
                    (***) Gói <strong>Bảo hành mở rộng (BHMR)</strong> hỗ trợ nhập trả lại trong vòng 7 ngày đầu kể từ lúc mua và chịu phí thu hồi 50% giá trị gói.
                </div>

                <p><i class="fa-solid fa-truck-ramp-box"></i> Đối với các đơn hàng mua và giao online, thời gian đổi trả được tính từ ngày khách hàng nhận hàng thành công (ký nhận với đơn vị vận chuyển), tuy nhiên không quá <strong>T+5 ngày</strong> so với ngày in trên hoá đơn mua hàng.</p>
                <p>Ngoài thời gian đổi trả tiêu chuẩn nói trên, sản phẩm phát sinh lỗi sẽ được tiếp nhận và xử lý theo đúng chính sách bảo hành quy định.</p>
            </div>

            {{-- 3. ĐIỀU KIỆN & HƯỚNG DẪN GỬI TRẢ --}}
            <div class="return-section" id="dieu-kien-tra">
                <h2><i class="fa-solid fa-circle-check"></i> 3. Điều kiện & Hướng dẫn đổi trả</h2>
                <h3>2.2. Điều kiện áp dụng đổi trả hàng:</h3>
                <p>Sản phẩm chỉ được chấp nhận đổi trả khi thỏa mãn đầy đủ các điều kiện khắt khe sau:</p>
                <ul class="return-list">
                    <li><strong>Trạng thái máy mới:</strong> Máy như mới, không bị trầy xước, nứt vỡ, không dán decal hoặc vẽ trang trí lên sản phẩm.</li>
                    <li><strong>Trạng thái máy cũ:</strong> Có tình trạng sản phẩm nguyên vẹn đúng như lúc khách hàng nhận mua.</li>
                    <li><strong>Vỏ hộp:</strong> Hộp nguyên vẹn như mới, không móp méo, rách nát, viết vẽ bậy hoặc quấn keo băng dính; có số Serial/IMEI in trên hộp trùng khớp tuyệt đối với Serial/IMEI trên thân máy.</li>
                    <li><strong>Phụ kiện và quà tặng:</strong> Còn đầy đủ phụ kiện kèm theo, nguyên vẹn quà tặng khuyến mãi; còn nguyên tem bảo hành (không rách rời). Sản phẩm không bị đứt gãy, móp méo hay biến dạng ngoại hình.</li>
                    <li><strong>Tài khoản bảo mật:</strong> Thiết bị đã được đăng xuất hoàn toàn khỏi tất cả các tài khoản cá nhân như iCloud, Google Account, Mi Account, Samsung Account...</li>
                </ul>

                <h3>2.3. Hướng dẫn các bước thực hiện gửi trả lại sản phẩm:</h3>
                <p><strong>a. Kiểm tra điều kiện đổi trả:</strong> Vui lòng đảm bảo sản phẩm của bạn đáp ứng đầy đủ điều kiện đổi trả ở mục 2.2 phía trên.</p>
                <p><strong>b. Phương thức thực hiện:</strong></p>
                <ul class="return-list">
                    <li><strong>Cách 1: Đổi trả trực tiếp tại cửa hàng</strong><br>Quý khách mang sản phẩm cùng hóa đơn mua hàng tới chi nhánh cửa hàng DIENMAYPRO gần nhất để được kiểm tra và xử lý trực tiếp nhanh chóng.</li>
                    <li><strong>Cách 2: Đổi trả thông qua đơn vị vận chuyển</strong><br>
                        - Khách hàng tự gửi hàng thông qua các đơn vị chuyển phát uy tín như VNPost, Viettel Post... về địa chỉ trung tâm.<br>
                        - Đối với khách hàng nội thành HN/HCM, chúng tôi hỗ trợ tạo gói cước thu hồi để đơn vị vận chuyển đến tận nhà thu lại sản phẩm.<br>
                        <small style="color: #e11d48;">* Lưu ý: Cửa hàng không chịu trách nhiệm đối với các hư hỏng vật lý phát sinh do lỗi từ phía đơn vị vận chuyển trong quá trình gửi trả hàng. Phí vận chuyển chỉ được miễn phí hoàn toàn đối với sản phẩm lỗi do nhà sản xuất.</small>
                    </li>
                </ul>

                <h3>Chính sách đổi trả đối với công ty (Khách hàng Doanh nghiệp):</h3>
                <p>Trường hợp đổi trả sản phẩm đã được xuất hoá đơn giá trị gia tăng (GTGT) cho Công ty, Quý khách cần cung cấp đầy đủ <strong>Biên bản trả hàng và thu hồi hoá đơn</strong> hoặc <strong>Biên bản điều chỉnh giảm hoá đơn GTGT</strong> có dấu mộc tròn công ty cùng chữ ký của đại diện pháp luật. Nếu không cung cấp đủ chứng từ, cửa hàng sẽ thu phí dịch vụ tương đương <strong>8% hoặc 10%</strong> giá trị thuế suất sản phẩm đổi trả.</p>
            </div>

            {{-- 4. THỜI GIAN HOÀN TIỀN --}}
            <div class="return-section" id="hoan-tien">
                <h2><i class="fa-solid fa-wallet"></i> 4. Thời gian hoàn trả tiền cho khách hàng</h2>
                <p>Đối với các trường hợp đơn hàng hủy hoặc đổi trả đủ điều kiện hoàn lại tiền, thời hạn xử lý hoàn tiền được quy định cụ thể theo hình thức thanh toán ban đầu:</p>

                <div class="grid-2">
                    <div class="refund-timeline-card">
                        <h4><i class="fa-solid fa-money-bill-wave"></i> Thanh toán trực tiếp</h4>
                        <ul>
                            <li><span class="method">Tiền mặt</span> <span class="time">Hoàn trả ngay tại quầy</span></li>
                            <li><span class="method">Chuyển khoản ngân hàng</span> <span class="time">Trong vòng 2 ngày làm việc</span></li>
                        </ul>
                    </div>

                    <div class="refund-timeline-card">
                        <h4><i class="fa-solid fa-credit-card"></i> Thanh toán thẻ & Ví điện tử</h4>
                        <ul>
                            <li><span class="method">Thẻ ATM nội địa</span> <span class="time">Từ 7 - 10 ngày làm việc</span></li>
                            <li><span class="method">Visa / Master / JCB</span> <span class="time">Từ 7 - 15 ngày làm việc</span></li>
                            <li><span class="method">Cổng MPOS / ALEPAY</span> <span class="time">Từ 7 - 14 ngày làm việc</span></li>
                            <li><span class="method">Cổng VNPAY</span> <span class="time">Từ 3 - 8 ngày làm việc</span></li>
                            <li><span class="method">Ví điện tử MOCA</span> <span class="time">Từ 3 - 5 ngày làm việc</span></li>
                        </ul>
                    </div>
                </div>

                <div class="return-note warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <strong>Lưu ý quan trọng về hoàn tiền:</strong><br>
                    - Ngày làm việc được tính từ Thứ 2 đến Thứ 6 (không bao gồm Thứ 7, Chủ nhật, các ngày nghỉ lễ, Tết theo luật định).<br>
                    - Cửa hàng hoàn lại đúng giá trị sản phẩm thực tế khách hàng đã thanh toán. Các khoản chi phí phát sinh như phí vận chuyển, phụ phí thanh toán, phí chuyển đổi trả góp trả thẳng và các giá trị khuyến mãi cộng thêm sẽ không được hoàn trả lại.
                </div>
            </div>

            {{-- 5. QUY ĐỊNH CHI TIẾT THEO NHÓM SẢN PHẨM --}}
            <div class="return-section" id="nhom-special">
                <h2><i class="fa-solid fa-tags"></i> 5. Chính sách bảo hành & đổi trả theo nhóm sản phẩm</h2>
                
                <h3>5.1. Nhóm sản phẩm chính hãng mới (Trừ phụ kiện không điện & hàng trưng bày):</h3>
                <p>Khách hàng mua sản phẩm chính hãng được áp dụng các điều khoản bảo hành cam kết vượt trội:</p>
                <ul class="return-list">
                    <li><strong>Bảo hành có cam kết trong 12 tháng:</strong> Tiếp nhận và xử lý bảo hành lỗi phần cứng tối đa trong vòng 15 ngày (tính từ lúc tiếp nhận máy lỗi đến khi báo khách hàng nhận lại). Nếu vi phạm cam kết thời gian này, khách hàng được áp dụng đổi lỗi ngay lập tức hoặc hoàn tiền với mức phí giảm 50%.</li>
                    <li><strong>Hư gì đổi nấy ngay và luôn:</strong> 
                        - Tháng đầu tiên kể từ ngày mua: Miễn phí đổi sản phẩm chính hoặc phụ kiện đi kèm bị lỗi.<br>
                        - Từ tháng thứ 2 đến tháng thứ 12: Đổi sản phẩm lỗi có thu phí khấu hao 10% giá trị hóa đơn trên mỗi tháng (ví dụ: tháng thứ 2 phí 10%, tháng thứ 3 phí 20%...).
                    </li>
                    <li><strong>Quy định đổi nguyên hộp (Fullbox):</strong> Ngoài phí khấu hao ở trên, nếu khách hàng muốn đổi lấy sản phẩm nguyên hộp mới (fullbox) sẽ thanh toán thêm phí lấy hộp tương đương 20% giá trị trên hóa đơn ban đầu.</li>
                    <li><strong>Chính sách hoàn tiền sản phẩm lỗi & không lỗi:</strong><br>
                        - Trong tháng đầu tiên: Phí thu hồi 20% giá trị hóa đơn.<br>
                        - Từ tháng thứ 2 đến tháng thứ 12: Phí khấu hao 10% giá trị hóa đơn/tháng.<br>
                        - Lưu ý: Sản phẩm hoàn trả phải giữ nguyên 100% hình dạng ban đầu, màn hình và thân máy không trầy xước. Mất hộp thu phí 2%, mất phụ kiện thu phí theo giá tối thiểu chính hãng công bố (tối đa 5% hóa đơn). Hoàn trả kèm theo toàn bộ quà khuyến mãi (nếu mất sẽ thu phí theo giá khuyến mãi công bố).
                    </li>
                </ul>

                <h3>5.2. Nhóm đồng hồ thời trang (Áp dụng từ 15/12/2024):</h3>
                <ul class="return-list">
                    <li><strong>Các Hãng ELIO, EYKI, SKMEI, SMILE KID:</strong> 1 đổi 1 trong vòng 6 tháng đầu nếu phát sinh lỗi kỹ thuật từ nhà sản xuất.</li>
                    <li><strong>Các Hãng còn lại:</strong> Bảo hành 12 tháng bộ máy bên trong (không bảo hành dây và vỏ ngoài). Tháng đầu tiên hỗ trợ đổi lỗi miễn phí, từ tháng thứ 2 đến thứ 12 áp dụng phí đổi 10% giá trị hóa đơn/tháng.</li>
                    <li><strong>Đặc quyền:</strong> Thay pin miễn phí trọn đời đối với tất cả đồng hồ sử dụng bộ máy Quartz.</li>
                    <li style="color: #dc2626;">* Lưu ý: Nhóm hàng đồng hồ thời trang không áp dụng chính sách hoàn trả lại tiền mặt.</li>
                </ul>

                <h3>5.3. Nhóm phụ kiện không điện & Sản phẩm khuyến mãi:</h3>
                <p>Không áp dụng bảo hành và đổi trả tại hệ thống DIENMAYPRO. Nếu sản phẩm có chính sách bảo hành riêng từ hãng, quý khách vui lòng liên hệ trực tiếp TTBH hãng để được hỗ trợ.</p>
                <p><strong>Chính sách đối với miếng dán màn hình:</strong><br>
                    - Miếng dán trước - sau: Lần đầu mua nguyên giá, từ lần thứ 2 dán lại mua với giá ưu đãi giảm 50%.<br>
                    - Miếng dán kính cường lực: Dán lại hoàn toàn miễn phí trong vòng 30 ngày nếu có lỗi bong tróc keo hoặc lỗi cảm ứng.
                </p>

                <h3>5.4. Nhóm sản phẩm trưng bày & Đã qua sử dụng:</h3>
                <div class="return-table-wrapper">
                    <table class="return-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Chính sách áp dụng</th>
                                <th>Điều kiện đi kèm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Tháng đầu tiên</strong></td>
                                <td>Áp dụng bảo hành hãng hoặc hoàn tiền mặt chịu phí thu hồi 10% giá trị hoá đơn.</td>
                                <td>Sản phẩm lỗi kỹ thuật có giấy xác nhận từ hãng; sản phẩm giữ nguyên hình dạng ban đầu, đủ điều kiện bảo hành của hãng.</td>
                            </tr>
                            <tr>
                                <td><strong>Từ tháng thứ 2 trở đi</strong></td>
                                <td>Không áp dụng chính sách đổi trả. Chỉ hỗ trợ bảo hành chính hãng nếu còn hạn bảo hành và đủ điều kiện của hãng.</td>
                                <td>Chỉ áp dụng bảo hành đối với sản phẩm chính, không áp dụng đối với phụ kiện đi kèm theo máy.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>5.5. Nhóm sản phẩm máy in & Bộ lưu điện (UPS):</h3>
                <ul class="return-list">
                    <li><strong>Tháng đầu tiên:</strong> Hỗ trợ 1 đổi 1 sản phẩm tương đương nếu sản phẩm bị lỗi kỹ thuật. Trường hợp hết hàng đổi lỗi, áp dụng bảo hành hãng hoặc hoàn lại tiền chịu phí 10% giá trị hóa đơn.</li>
                    <li><strong>Từ tháng thứ 2 trở đi:</strong> Không áp dụng đổi trả. Sản phẩm lỗi chuyển hãng để sửa chữa bảo hành theo chính sách.</li>
                </ul>
            </div>
            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// =========================================================================
// SCRIPT CUỘN MƯỢT MÀ & ĐỔI TRẠNG THÁI HIỂN THỊ CỦA THANH MENU ĐIỀU HƯỚNG
// Tự động cuộn đến phần nội dung tương ứng khi click vào link chính sách
// =========================================================================
document.querySelectorAll('.return-nav a').forEach(link => {
    link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        // Chỉ xử lý các liên kết dạng ID trỏ tới các phần trong trang (bắt đầu bằng dấu #)
        if (href.startsWith('#')) {
            e.preventDefault(); // Ngăn hành động chuyển trang mặc định của trình duyệt
            
            // Xóa class 'active' (đang được chọn) khỏi toàn bộ các nút điều hướng khác
            document.querySelectorAll('.return-nav a').forEach(a => a.classList.remove('active'));
            // Thêm class 'active' vào nút vừa bấm để làm nổi bật lên
            this.classList.add('active');
            
            // Tìm phần tử HTML đích dựa vào ID
            const target = document.querySelector(href);
            if (target) {
                // Thực hiện hiệu ứng cuộn mượt mà (smooth scroll) đến phần tử đích
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });
});
</script>
@endpush
