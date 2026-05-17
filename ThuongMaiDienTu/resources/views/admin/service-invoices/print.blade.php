<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In hóa đơn {{ $serviceInvoice->invoice_no }}</title>
    <style>
        @page { margin: 28mm 14mm 24mm 14mm; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; font-size: 13px; }
        .page-header { position: fixed; top: -16mm; left: 0; right: 0; height: 16mm; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
        .page-footer { position: fixed; bottom: -16mm; left: 0; right: 0; height: 16mm; border-top: 1px solid #e5e7eb; padding-top: 8px; font-size: 11px; color: #6b7280; }
        .header-table, .footer-table { width: 100%; border-collapse: collapse; }
        .brand { font-size: 16px; font-weight: 700; color: #111827; }
        .brand-sub { font-size: 11px; color: #6b7280; }
        .meta { text-align: right; font-size: 11px; }
        .invoice { margin-top: 8px; }
        .title { font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
        .muted { color: #6b7280; margin: 0; }
        .box { border: 1px solid #e5e7eb; border-radius: 10px; padding: 14px; margin-bottom: 14px; }
        .grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .grid td { width: 50%; vertical-align: top; padding-right: 10px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 12px; }
        .items th, .items td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        .items th { background: #f9fafb; }
        .items td.amount, .summary td.amount { text-align: right; }
        .summary { width: 45%; margin-left: auto; border-collapse: collapse; margin-top: 12px; }
        .summary td { padding: 8px 0; }
        .summary tr:last-child td { font-size: 16px; font-weight: 700; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .actions { margin-bottom: 16px; }
        .btn { display: inline-block; padding: 9px 14px; border-radius: 8px; background: #111827; color: #fff; text-decoration: none; font-size: 12px; }
        .note { margin-top: 16px; font-size: 11px; color: #6b7280; }
        @media print {
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand">THUONG MAI DIEN TU</div>
                    <div class="brand-sub">Hóa đơn dịch vụ - Chứng từ thanh toán</div>
                </td>
                <td class="meta">
                    <div><strong>Mã:</strong> {{ $serviceInvoice->invoice_no }}</div>
                    <div><strong>Ngày in:</strong> {{ now()->format('d/m/Y H:i') }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td>Trang in tự động từ hệ thống quản trị</td>
                <td style="text-align:right;">Cảm ơn quý khách đã sử dụng dịch vụ</td>
            </tr>
        </table>
    </div>

    <div class="invoice">
        @if(!isset($isPdf) || !$isPdf)
        <div class="actions">
            <a href="javascript:window.print()" class="btn">In hóa đơn</a>
        </div>
        @endif

        <div style="text-align:center; margin-bottom: 16px;">
            <h1 class="title">HÓA ĐƠN DỊCH VỤ</h1>
            <p class="muted">Mã hóa đơn: {{ $serviceInvoice->invoice_no }}</p>
        </div>

        <table class="grid">
            <tr>
                <td>
                    <div class="box">
                        <h3>Thông tin khách hàng</h3>
                        <p><strong>Tên:</strong> {{ $serviceInvoice->customer_name }}</p>
                        <p><strong>Điện thoại:</strong> {{ $serviceInvoice->customer_phone ?? '-' }}</p>
                        <p><strong>Email:</strong> {{ $serviceInvoice->customer_email ?? '-' }}</p>
                    </div>
                </td>
                <td>
                    <div class="box">
                        <h3>Thông tin dịch vụ</h3>
                        <p><strong>Tên dịch vụ:</strong> {{ $serviceInvoice->service_name }}</p>
                        <p><strong>Mô tả:</strong> {{ $serviceInvoice->description ?? '-' }}</p>
                        <p><strong>Trạng thái:</strong> {{ $serviceInvoice->status }}</p>
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Nội dung</th>
                    <th class="amount">Số tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tạm tính</td>
                    <td class="amount">{{ number_format($serviceInvoice->subtotal, 0, ',', '.') }} đ</td>
                </tr>
                <tr>
                    <td>Thuế</td>
                    <td class="amount">{{ number_format($serviceInvoice->tax_amount, 0, ',', '.') }} đ</td>
                </tr>
                <tr>
                    <td>Giảm giá</td>
                    <td class="amount">-{{ number_format($serviceInvoice->discount_amount, 0, ',', '.') }} đ</td>
                </tr>
            </tbody>
        </table>

        <table class="summary">
            <tr>
                <td>Tổng cộng</td>
                <td class="amount">{{ number_format($serviceInvoice->total_amount, 0, ',', '.') }} đ</td>
            </tr>
        </table>

        <div class="note">
            Hóa đơn này được tạo tự động từ hệ thống và có giá trị tham khảo nội bộ / thanh toán theo xác nhận của đơn vị.
        </div>
    </div>
</body>
</html>
