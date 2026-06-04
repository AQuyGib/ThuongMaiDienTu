<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn trả góp #{{ $installment->installment_code }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1e293b;
            background-color: #f8fafc;
            margin: 0;
            padding: 40px;
        }
        .invoice-card {
            background-color: #ffffff;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px dashed #e2e8f0;
            padding-bottom: 25px;
            margin-bottom: 30px;
        }
        .store-info h2 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }
        .store-info p {
            margin: 3px 0;
            font-size: 13px;
            color: #64748b;
        }
        .invoice-title-area {
            text-align: right;
        }
        .invoice-title-area h1 {
            margin: 0 0 8px 0;
            font-size: 20px;
            font-weight: 800;
            color: #0046ab;
            text-transform: uppercase;
        }
        .invoice-title-area p {
            margin: 3px 0;
            font-size: 13px;
            color: #475569;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 35px;
        }
        .info-block p {
            margin: 6px 0;
            font-size: 14px;
            line-height: 1.5;
        }
        .info-block strong {
            color: #0f172a;
            font-weight: 600;
        }
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }
        .invoice-table th {
            text-align: left;
            padding: 12px 16px;
            background-color: #f8fafc;
            color: #475569;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }
        .invoice-table td {
            padding: 16px;
            font-size: 14px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .product-meta {
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }
        .summary-wrapper {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .summary-table {
            width: 320px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
            color: #475569;
        }
        .summary-row.total {
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }
        .summary-row.highlight {
            background-color: #f0fdf4;
            color: #166534;
            padding: 10px;
            border-radius: 8px;
            font-weight: 700;
            margin-top: 5px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #f1f5f9;
        }
        .signature-block {
            text-align: center;
            width: 250px;
        }
        .signature-block p {
            margin: 5px 0;
            font-size: 13px;
            color: #64748b;
        }
        .signature-space {
            height: 80px;
        }
        .print-btn-area {
            text-align: center;
            margin-top: 30px;
        }
        .print-button {
            background-color: #0046ab;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 70, 171, 0.2);
            transition: all 0.2s ease;
        }
        .print-button:hover {
            background-color: #003685;
            transform: translateY(-1px);
        }

        /* Print Media Style */
        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
                margin: 0;
            }
            .invoice-card {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .print-btn-area {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="invoice-card">
    <!-- Header -->
    <div class="invoice-header">
        <div class="store-info">
            <h2>DIENMAYPRO</h2>
            <p>Showroom: 180 Cao Thắng, Quận 3, TP. Hồ Chí Minh</p>
            <p>Hotline: 1900 8888 | Website: dienmaypro.com.vn</p>
        </div>
        <div class="invoice-title-area">
            <h1>HÓA ĐƠN TRẢ GÓP</h1>
            <p>Mã HS: <strong>#{{ $installment->installment_code }}</strong></p>
            <p>Ngày lập: {{ $installment->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-block">
            <div class="section-title">Thông tin khách hàng</div>
            <p>Họ tên: <strong>{{ $installment->customer_name }}</strong></p>
            <p>Điện thoại: <strong>{{ $installment->customer_phone }}</strong></p>
            <p>CCCD: <strong>{{ $installment->customer_id_card ?? 'N/A' }}</strong></p>
            <p>Địa chỉ nhận: {{ $installment->order->shipping_address ?? 'Nhận tại cửa hàng' }}</p>
        </div>
        <div class="info-block">
            <div class="section-title">Thông tin hợp đồng trả góp</div>
            <p>Phương thức: <strong>
                @if($installment->method === 'financial_company')
                    Qua công ty tài chính
                @elseif($installment->method === 'credit_card')
                    Qua thẻ tín dụng ngân hàng
                @else
                    Qua cổng Kredivo
                @endif
            </strong></p>
            <p>Đối tác liên kết: <strong>{{ $installment->partner }}</strong></p>
            <p>Kỳ hạn góp: <strong>{{ $installment->period }} tháng</strong></p>
            <p>Trạng thái hồ sơ: <strong style="text-transform: uppercase;">
                @if($installment->status === 'Pending_Approval')
                    Chờ duyệt hồ sơ
                @elseif($installment->status === 'Approved')
                    Đã phê duyệt
                @elseif($installment->status === 'Rejected')
                    Bị từ chối
                @endif
            </strong></p>
        </div>
    </div>

    <!-- Items Table -->
    <div class="section-title">Danh sách sản phẩm mua trả góp</div>
    <table class="invoice-table">
        <thead>
            <tr>
                <th>Sản phẩm / Biến thể</th>
                <th style="text-align: right;">Đơn giá</th>
                <th style="text-align: center;">Số lượng</th>
                <th style="text-align: right;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @if($installment->order && $installment->order->details)
                @foreach($installment->order->details as $detail)
                    @php
                        $item = $detail->inventoryItem;
                        $variant = $item ? $item->variant : null;
                        $product = $variant ? $variant->product : null;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $product->name ?? 'Sản phẩm mua sắm' }}</strong>
                            @if($variant && $variant->color)
                                <div class="product-meta">Màu sắc: {{ $variant->color }}</div>
                            @endif
                            @if($item && $item->imei_serial)
                                <div class="product-meta" style="font-family: monospace;">Serial/IMEI: {{ $item->imei_serial }}</div>
                            @endif
                        </td>
                        <td style="text-align: right;">{{ number_format($detail->price, 0, ',', '.') }}đ</td>
                        <td style="text-align: center;">1</td>
                        <td style="text-align: right; font-weight: 600;">{{ number_format($detail->price, 0, ',', '.') }}đ</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center; color: #94a3b8;">Không có chi tiết sản phẩm hợp đồng.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Summary Details -->
    <div class="summary-wrapper">
        <div class="summary-table">
            <div class="summary-row">
                <span>Tổng giá trị sản phẩm:</span>
                <span style="font-weight: 600;">{{ number_format($installment->product_price, 0, ',', '.') }}đ</span>
            </div>
            <div class="summary-row highlight">
                <span>Đã trả trước tại quầy:</span>
                <span>{{ number_format($installment->prepay_amount, 0, ',', '.') }}đ</span>
            </div>
            <div class="summary-row">
                <span>Khoản vay nợ trả góp:</span>
                <span style="font-weight: 600; color: #0046ab;">{{ number_format($installment->loan_amount, 0, ',', '.') }}đ</span>
            </div>
            <div class="summary-row">
                <span>Lãi suất tháng:</span>
                <span>{{ number_format($installment->interest_rate * 100, 1) }}%</span>
            </div>
            <div class="summary-row">
                <span>Phí dịch vụ hàng tháng:</span>
                <span>{{ number_format($installment->service_fee, 0, ',', '.') }}đ</span>
            </div>
            <div class="summary-row total">
                <span>Góp mỗi tháng:</span>
                <span style="color: #d70018;">{{ number_format($installment->monthly_payment, 0, ',', '.') }}đ / tháng</span>
            </div>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="signature-block">
            <strong>KHÁCH HÀNG KÝ TÊN</strong>
            <p>(Ký, ghi rõ họ tên)</p>
            <div class="signature-space"></div>
            <strong>{{ $installment->customer_name }}</strong>
        </div>
        <div class="signature-block">
            <strong>NHÂN VIÊN LẬP PHIẾU</strong>
            <p>(Ký, đóng dấu cửa hàng)</p>
            <div class="signature-space"></div>
            <strong>{{ auth()->user()->full_name ?? 'Nhân viên thu ngân' }}</strong>
        </div>
    </div>
</div>

<div class="print-btn-area">
    <button class="print-button" onclick="window.print()"><i class="fa-solid fa-print"></i> IN HÓA ĐƠN TRẢ GÓP</button>
</div>

</body>
</html>
