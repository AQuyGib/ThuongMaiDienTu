<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Tiêu đề động tùy thuộc vào loại yêu cầu --}}
    <title>
        @if($claim->claim_type === 'warranty')
            Phiếu bảo hành #WC-{{ $claim->id }}
        @elseif($claim->claim_type === 'return')
            Hóa đơn hoàn tiền #WC-{{ $claim->id }}
        @else
            Phiếu đổi trả #WC-{{ $claim->id }}
        @endif
    </title>
    <style>
        /* Cấu hình lề trang in ấn */
        @page { 
            margin: 20mm 15mm 20mm 15mm; 
        }
        
        /* Reset font chữ sang Unicode chuẩn để hỗ trợ in ấn PDF/HTML tiếng Việt */
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            color: #1e293b; 
            font-size: 13px; 
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Định dạng phần đầu (Header) của tài liệu in */
        .page-header { 
            border-bottom: 2px solid #3b82f6; 
            padding-bottom: 12px; 
            margin-bottom: 20px;
        }

        .header-table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        .brand-title { 
            font-size: 18px; 
            font-weight: 800; 
            color: #1e3a8a; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .brand-sub { 
            font-size: 11px; 
            color: #64748b; 
            margin-top: 2px;
        }

        .meta-info { 
            text-align: right; 
            font-size: 11px; 
            color: #475569;
        }

        .meta-info strong {
            color: #0f172a;
        }

        /* Phần thân tài liệu */
        .invoice-body { 
            margin-top: 10px; 
        }

        .main-title { 
            font-size: 22px; 
            font-weight: 800; 
            text-align: center;
            color: #0f172a;
            margin: 0 0 6px 0;
            text-transform: uppercase;
        }

        .sub-title { 
            text-align: center; 
            color: #64748b; 
            margin: 0 0 20px 0;
            font-size: 12px;
        }

        /* Khối thông tin dạng thẻ (Card/Box) */
        .info-box { 
            border: 1px solid #e2e8f0; 
            border-radius: 8px; 
            padding: 14px; 
            background-color: #f8fafc;
        }

        .info-box h3 { 
            margin-top: 0; 
            margin-bottom: 10px; 
            font-size: 13px; 
            font-weight: 700; 
            color: #1e3a8a;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
            text-transform: uppercase;
        }

        .info-box p {
            margin: 6px 0;
            font-size: 12px;
        }

        .info-box strong {
            color: #334155;
        }

        /* Bảng căn lề Grid hai cột */
        .info-grid { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }

        .info-grid td { 
            width: 50%; 
            vertical-align: top; 
        }

        .info-grid td:first-child {
            padding-right: 10px;
        }

        .info-grid td:last-child {
            padding-left: 10px;
        }

        /* Bảng thống kê chi tiết sản phẩm / chi phí */
        .detail-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            margin-bottom: 20px;
        }

        .detail-table th { 
            background-color: #f1f5f9; 
            border: 1px solid #cbd5e1; 
            padding: 10px; 
            text-align: left; 
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #475569;
        }

        .detail-table td { 
            border: 1px solid #cbd5e1; 
            padding: 10px; 
            font-size: 12px;
            color: #334155;
        }

        .detail-table td.amount { 
            text-align: right; 
            font-weight: 700;
        }

        /* Khu vực tổng kết dòng tiền */
        .summary-wrapper {
            width: 100%;
            margin-bottom: 30px;
        }

        .summary-table { 
            width: 40%; 
            margin-left: auto; 
            border-collapse: collapse; 
        }

        .summary-table td { 
            padding: 6px 0; 
            font-size: 12px;
        }

        .summary-table td.amount { 
            text-align: right; 
            font-weight: 700;
            color: #0f172a;
        }

        .summary-table tr.total-row td { 
            font-size: 15px; 
            font-weight: 800; 
            border-top: 2px solid #3b82f6; 
            padding-top: 8px; 
            color: #1e3a8a;
        }

        /* Khu vực ký tên xác nhận */
        .signature-section { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 40px; 
        }

        .signature-section td { 
            width: 33.33%; 
            text-align: center; 
            vertical-align: top;
            font-size: 12px;
        }

        .signature-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 60px;
        }

        .signature-name {
            font-style: italic;
            color: #64748b;
        }

        /* Nhãn trạng thái (Badge) */
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-approved {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .status-pending {
            background-color: #fef9c3;
            color: #a16207;
            border: 1px solid #fef08a;
        }

        /* Ghi chú chân trang */
        .footer-note { 
            margin-top: 30px; 
            font-size: 11px; 
            color: #64748b; 
            border-top: 1px dashed #cbd5e1;
            padding-top: 10px;
            font-style: italic;
        }

        /* Thanh hành động chứa nút in */
        .action-bar { 
            margin-bottom: 20px; 
            background-color: #f1f5f9;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .btn-print { 
            display: inline-flex; 
            align-items: center;
            padding: 8px 16px; 
            border-radius: 6px; 
            background-color: #2563eb; 
            color: #fff; 
            text-decoration: none; 
            font-size: 12px; 
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-print:hover {
            background-color: #1d4ed8;
        }

        /* Tự động ẩn thanh hành động khi gọi lệnh Print của trình duyệt */
        @media print {
            .action-bar { 
                display: none; 
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    {{-- Thanh công cụ xuất hóa đơn (Ẩn đi khi in thật) --}}
    <div class="action-bar">
        <button onclick="window.print()" class="btn-print">
            <svg style="width:14px; height:14px; margin-right:6px; fill:currentColor;" viewBox="0 0 20 20"><path d="M5 4v3h10V4H5zM3 8h14v7h-2v-3H5v3H3V8zm2 5h6v2H5v-2z"></path></svg>
            In hóa đơn
        </button>
    </div>

    {{-- Header của hóa đơn --}}
    <div class="page-header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="brand-title">THƯƠNG MẠI ĐIỆN TỬ TGP</div>
                    <div class="brand-sub">Chính sách Bảo hành & Đổi trả chuyên nghiệp</div>
                </td>
                <td class="meta-info">
                    <div>Mã số chứng từ: <strong>WC-{{ str_pad($claim->id, 6, '0', STR_PAD_LEFT) }}</strong></div>
                    <div>Ngày lập: <strong>{{ $claim->created_at ? $claim->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="invoice-body">
        {{-- Tiêu đề chính dựa vào loại yêu cầu dịch vụ --}}
        <div>
            @if($claim->claim_type === 'warranty')
                <h1 class="main-title">BIÊN NHẬN BẢO HÀNH & SỬA CHỮA</h1>
                <p class="sub-title">Chứng từ tiếp nhận bảo hành thiết bị chính hãng tại quầy</p>
            @elseif($claim->claim_type === 'return')
                <h1 class="main-title">HÓA ĐƠN TRẢ HÀNG & HOÀN TIỀN</h1>
                <p class="sub-title">Chứng từ xác nhận trả hàng và quyết định hoàn tiền cho khách hàng</p>
            @else
                <h1 class="main-title">PHIẾU ĐỔI TRẢ SẢN PHẨM</h1>
                <p class="sub-title">Chứng từ xác nhận đổi trả thiết bị sang biến thể mới</p>
            @endif
        </div>

        {{-- Bảng chứa thông tin khách hàng và thông tin dịch vụ --}}
        <table class="info-grid">
            <tr>
                <td>
                    <div class="info-box">
                        <h3>Thông tin khách hàng</h3>
                        <p><strong>Họ và tên:</strong> {{ $claim->customer_name }}</p>
                        <p><strong>Số điện thoại:</strong> {{ $claim->customer_phone }}</p>
                        <p><strong>Email liên lạc:</strong> {{ $claim->customer_email ?? 'Chưa cung cấp' }}</p>
                        
                        {{-- Hiển thị thông tin hoàn tiền nếu là đổi trả hoàn tiền --}}
                        @if($claim->claim_type === 'return')
                            <p style="margin-top: 10px; padding-top: 8px; border-top: 1px dashed #cbd5e1;">
                                <strong style="color: #b45309;">Phương thức hoàn tiền:</strong> 
                                @if($claim->refund_method === 'cash')
                                    <strong>Tiền mặt (Cash)</strong>
                                @else
                                    <strong>Chuyển khoản ngân hàng (Bank Transfer)</strong>
                                    @if($claim->bank_name)
                                        <br>Ngân hàng: <strong>{{ $claim->bank_name }}</strong>
                                        <br>Số tài khoản: <strong>{{ $claim->bank_account_number }}</strong>
                                        <br>Chủ tài khoản: <strong>{{ strtoupper($claim->bank_account_name) }}</strong>
                                    @endif
                                @endif
                            </p>
                        @endif
                    </div>
                </td>
                <td>
                    <div class="info-box">
                        <h3>Thông tin dịch vụ</h3>
                        <p>
                            <strong>Loại nghiệp vụ:</strong> 
                            @if($claim->claim_type === 'warranty')
                                <span style="font-weight: 700; color: #2563eb;">Bảo hành sửa chữa</span>
                            @elseif($claim->claim_type === 'return')
                                <span style="font-weight: 700; color: #d97706;">Đổi trả hoàn tiền</span>
                            @else
                                <span style="font-weight: 700; color: #7c3aed;">Đổi máy mới/khác</span>
                            @endif
                        </p>
                        <p>
                            <strong>Trạng thái duyệt:</strong> 
                            @if($claim->status === 'approved')
                                <span class="status-badge status-approved">Đã duyệt</span>
                            @elseif($claim->status === 'rejected')
                                <span class="status-badge status-rejected">Từ chối</span>
                            @else
                                <span class="status-badge status-pending">Chờ duyệt</span>
                            @endif
                        </p>
                        <p><strong>Thời gian cập nhật:</strong> {{ $claim->updated_at ? $claim->updated_at->format('d/m/Y H:i') : '—' }}</p>
                        <p><strong>Nhân viên lập phiếu:</strong> {{ auth()->user()->full_name ?? 'Hệ thống' }}</p>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Bảng chi tiết sản phẩm --}}
        <h3 style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px; text-transform: uppercase;">
            Chi tiết thiết bị áp dụng
        </h3>
        <table class="detail-table">
            <thead>
                <tr>
                    <th style="width: 8%;">STT</th>
                    <th style="width: 45%;">Tên sản phẩm thiết bị</th>
                    <th style="width: 25%;">Số IMEI / Serial</th>
                    <th style="width: 22%;">Thời gian yêu cầu</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>
                        {{-- Hiển thị tên sản phẩm phân giải từ IMEI hoặc fallback --}}
                        <strong>{{ $item->variant->product->name ?? ($claim->reason ? 'Thiết bị di động của khách hàng' : 'Sản phẩm chưa xác định') }}</strong>
                        @if(isset($item->variant) && $item->variant->label)
                            <div style="font-size: 10px; color: #64748b; margin-top: 2px;">
                                Phân loại: {{ $item->variant->label }}
                            </div>
                        @endif
                    </td>
                    <td style="font-family: monospace; font-size: 12px; font-weight: 700;">
                        {{ $claim->imei_serial }}
                    </td>
                    <td>
                        {{ $claim->created_at ? $claim->created_at->format('d/m/Y H:i') : '—' }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Phần mô tả lỗi và quyết định xử lý --}}
        <div style="margin-bottom: 20px;">
            <div style="border-left: 3px solid #cbd5e1; padding-left: 10px; margin-bottom: 12px;">
                <strong style="font-size: 12px; color: #475569; display: block; margin-bottom: 4px;">Lý do của khách hàng:</strong>
                <p style="margin: 0; font-size: 12px; font-style: italic; color: #334155;">
                    "{{ $claim->reason }}"
                </p>
            </div>

            @if($claim->admin_note)
                <div style="border-left: 3px solid #3b82f6; padding-left: 10px; background-color: #eff6ff; padding: 10px; border-radius: 0 6px 6px 0;">
                    <strong style="font-size: 12px; color: #1e3a8a; display: block; margin-bottom: 4px;">Phương án & Ghi chú của hệ thống:</strong>
                    <p style="margin: 0; font-size: 12px; color: #1e40af;">
                        {{ $claim->admin_note }}
                    </p>
                </div>
            @endif
        </div>

        {{-- Bảng tổng cộng dòng tiền (Chỉ áp dụng với loại đổi trả hoàn tiền) --}}
        @if($claim->claim_type === 'return')
            <div class="summary-wrapper">
                <table class="summary-table">
                    <tr>
                        <td>Giá trị tạm tính:</td>
                        <td class="amount">{{ number_format($claim->refund_amount ?? 0, 0, ',', '.') }} đ</td>
                    </tr>
                    <tr class="total-row">
                        <td>Thực hoàn trả:</td>
                        <td class="amount">{{ number_format($claim->refund_amount ?? 0, 0, ',', '.') }} đ</td>
                    </tr>
                </table>
            </div>
        @endif

        {{-- Các chữ ký xác nhận --}}
        <table class="signature-section">
            <tr>
                <td>
                    <div class="signature-title">Khách hàng nhận máy</div>
                    <div class="signature-name">(Ký và ghi rõ họ tên)</div>
                </td>
                <td>
                    <div class="signature-title">Kỹ thuật viên kiểm tra</div>
                    <div class="signature-name">(Ký và ghi rõ họ tên)</div>
                </td>
                <td>
                    <div class="signature-title">Người lập hóa đơn</div>
                    <div class="signature-name">
                        {{ auth()->user()->full_name ?? 'Nhân viên cửa hàng' }}
                    </div>
                </td>
            </tr>
        </table>

        {{-- Ghi chú bổ sung tùy loại --}}
        <div class="footer-note">
            @if($claim->claim_type === 'warranty')
                * Lưu ý: Quý khách vui lòng mang theo biên nhận này khi đến nhận lại thiết bị. Thời hạn sửa chữa trung bình từ 3 - 7 ngày làm việc tùy thuộc vào linh kiện thay thế.
            @elseif($claim->claim_type === 'return')
                * Lưu ý: Số tiền hoàn lại đã được hạch toán chi phí chi tiêu từ sổ quỹ kinh doanh. Thời gian nhận tiền chuyển khoản từ 24h - 48h làm việc của ngân hàng liên kết.
            @else
                * Lưu ý: Thiết bị được đổi sang sản phẩm mới có cùng model hoặc model tương đương. Thời gian áp dụng chế độ bảo hành mới tính từ ngày ký nhận biên bản này.
            @endif
        </div>
    </div>
</body>
</html>
