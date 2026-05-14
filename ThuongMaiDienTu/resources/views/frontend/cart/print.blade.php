<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn bán hàng - DIENMAYPRO</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 72mm; /* Thường dùng cho máy in 80mm */
            margin: 0 auto;
            padding: 5mm 0;
            color: #000;
            font-size: 13px;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .header { margin-bottom: 5mm; }
        .logo { font-size: 24px; font-weight: 900; letter-spacing: 2px; }
        .divider { border-top: 1px dashed #000; margin: 3mm 0; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 1mm; align-items: flex-start; }
        .item-info { flex: 1; }
        .item-total { width: 25mm; text-align: right; font-weight: bold; }
        .total-section { margin-top: 4mm; }
        .total-row { display: flex; justify-content: space-between; font-size: 16px; margin-top: 1mm; }
        .footer { margin-top: 8mm; font-size: 11px; }
        .no-print {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            display: block;
            margin: 10px auto 20px;
            font-weight: bold;
            font-family: sans-serif;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .barcode {
            width: 100%;
            height: 40px;
            background: repeating-linear-gradient(90deg, #000, #000 2px, #fff 2px, #fff 4px);
            margin: 5mm 0 2mm;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()">
        <i class="fa-solid fa-print"></i> XÁC NHẬN IN HÓA ĐƠN
    </button>

    <div class="header text-center">
        <div class="logo">DIENMAYPRO</div>
        <div style="font-size: 11px;">Hệ thống bán lẻ điện máy toàn quốc</div>
        <div>Đ/C: 123 Lê Lợi, P. Bến Thành, Quận 1, HCM</div>
        <div>SĐT: 1900 1234 - 0559.763.134</div>
    </div>

    <div class="text-center bold" style="font-size: 18px; margin-bottom: 4mm;">HÓA ĐƠN BÁN LẺ</div>

    <div id="bill-meta">
        <div class="item-row">
            <span>Ngày: <span id="bill-date"></span></span>
            <span class="text-right" id="bill-time"></span>
        </div>
        <div>Số HD: <span id="bill-id" class="bold"></span></div>
        <div>NV: Quản Trị Viên</div>
        <div>KH: Khách vãng lai</div>
    </div>

    <div class="divider"></div>

    <div style="display: flex; font-weight: bold; margin-bottom: 2mm; font-size: 12px;">
        <span style="flex: 1;">Tên sản phẩm / SL</span>
        <span style="width: 25mm; text-align: right;">Thành tiền</span>
    </div>

    <div id="items-list">
        <!-- Injected via JS -->
    </div>

    <div class="divider"></div>

    <div class="total-section">
        <div class="item-row">
            <span>Tạm tính:</span>
            <span id="subtotal" class="text-right">0đ</span>
        </div>
        <div class="item-row">
            <span>Giảm giá:</span>
            <span class="text-right">0đ</span>
        </div>
        <div class="total-row bold">
            <span>TỔNG CỘNG:</span>
            <span id="grand-total">0đ</span>
        </div>
    </div>

    <div style="margin-top: 5mm; border: 1px solid #000; padding: 2mm; font-size: 11px;">
        <div>Hình thức: <span id="pay-method" class="bold">CHUYỂN KHOẢN QR</span></div>
        <div>Trạng thái: <span class="bold">ĐÃ THANH TOÁN</span></div>
    </div>

    <div class="text-center">
        <div class="barcode"></div>
        <div id="barcode-text" style="font-size: 10px; letter-spacing: 3px;"></div>
    </div>

    <div class="footer text-center">
        <div class="divider"></div>
        <div class="bold" style="font-size: 14px;">CẢM ƠN QUÝ KHÁCH!</div>
        <div style="margin: 2mm 0;">Quý khách vui lòng kiểm tra lại hàng hóa và hóa đơn trước khi rời khỏi cửa hàng.</div>
        <div>Chính sách đổi trả trong vòng 7 ngày.</div>
        <div style="font-style: italic; margin-top: 2mm;">Hotline bảo hành: 1800 6789</div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const now = new Date();
            document.getElementById('bill-date').textContent = now.toLocaleDateString('vi-VN');
            document.getElementById('bill-time').textContent = now.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });

            const rawItems = sessionStorage.getItem('checkoutItems');
            const totalVal = sessionStorage.getItem('paymentTotal');
            
            if (rawItems) {
                const items = JSON.parse(rawItems);
                const list = document.getElementById('items-list');
                let calculatedSubtotal = 0;

                items.forEach(item => {
                    const lineTotal = item.price * item.quantity;
                    calculatedSubtotal += lineTotal;

                    const row = document.createElement('div');
                    row.className = 'item-row';
                    row.style.marginBottom = '2mm';
                    row.innerHTML = `
                        <div class="item-info">
                            <div>${item.name}</div>
                            <div style="font-size: 11px;">${new Intl.NumberFormat('vi-VN').format(item.price)} x ${item.quantity}</div>
                        </div>
                        <div class="item-total">${new Intl.NumberFormat('vi-VN').format(lineTotal)}</div>
                    `;
                    list.appendChild(row);
                });

                const finalTotal = totalVal || calculatedSubtotal;
                document.getElementById('subtotal').textContent = new Intl.NumberFormat('vi-VN').format(calculatedSubtotal) + 'đ';
                document.getElementById('grand-total').textContent = new Intl.NumberFormat('vi-VN').format(finalTotal) + 'đ';
            }

            const billId = 'DMP' + Math.random().toString(36).substring(2, 10).toUpperCase();
            document.getElementById('bill-id').textContent = billId;
            document.getElementById('barcode-text').textContent = billId;
            
            // Tự động kích hoạt lệnh in sau 1 giây
            setTimeout(() => {
                // window.print();
            }, 1000);
        });
    </script>
</body>
</html>
