<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo danh sách nhân sự - {{ now()->format('d/m/Y') }}</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        /* Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }
        .header-logo {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.5px;
        }
        .header-sub {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 2px;
        }
        .header-meta {
            text-align: right;
            font-size: 10px;
            color: #475569;
        }
        .header-meta td {
            padding: 2px 0;
        }

        /* Title */
        .report-title-container {
            text-align: center;
            margin-bottom: 25px;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-subtitle {
            font-size: 11px;
            color: #475569;
            margin-top: 5px;
            font-style: italic;
        }

        /* KPI Cards Grid using Tables */
        .stats-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin-bottom: 25px;
        }
        .stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            vertical-align: middle;
            text-align: left;
        }
        .stat-card-total {
            background-color: #f0fdfa;
            border-color: #ccfbf1;
        }
        .stat-card-active {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
        }
        .stat-card-banned {
            background-color: #fef2f2;
            border-color: #fee2e2;
        }
        .stat-card-role {
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }
        .stat-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            line-height: 1;
        }
        .stat-desc {
            font-size: 8px;
            color: #94a3b8;
            margin-top: 3px;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 10px 8px;
            border: 1px solid #1e3a8a;
            letter-spacing: 0.3px;
        }
        .data-table td {
            padding: 9px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-semibold {
            font-weight: bold;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: center;
        }
        .badge-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-banned {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge-role-admin {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-role-manager {
            background-color: #f3e8ff;
            color: #6b21a8;
        }
        .badge-role-staff {
            background-color: #e2e8f0;
            color: #334155;
        }

        /* Footer & Signatures */
        .signature-container {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-column {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .signature-title {
            font-weight: bold;
            font-size: 11px;
            color: #0f172a;
            margin-bottom: 60px;
            text-transform: uppercase;
        }
        .signature-name {
            font-weight: bold;
            font-size: 11px;
            color: #0f172a;
        }
        .signature-sub {
            font-size: 9px;
            color: #64748b;
            font-style: italic;
        }

        .pdf-footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="header-logo">THƯƠNG MẠI ĐIỆN TỬ</div>
                <div class="header-sub">Hệ thống quản lý nhân sự & POS liên kết</div>
            </td>
            <td class="header-meta" style="vertical-align: middle;">
                <table align="right">
                    <tr>
                        <td class="font-semibold" style="padding-right: 8px;">Mã báo cáo:</td>
                        <td>HR-RPT-{{ now()->format('Ymd') }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold" style="padding-right: 8px;">Ngày xuất:</td>
                        <td>{{ now()->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold" style="padding-right: 8px;">Người xuất:</td>
                        <td>{{ auth()->user()->full_name }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Report Title -->
    <div class="report-title-container">
        <h1 class="report-title">BÁO CÁO THỐNG KÊ DANH SÁCH NHÂN SỰ HỆ THỐNG</h1>
        <div class="report-subtitle">Áp dụng bộ lọc dữ liệu thực tế tại thời điểm xuất</div>
    </div>

    <!-- Quick Statistics KPI Cards -->
    <table class="stats-table">
        <tr>
            <!-- Cột 1: Tổng nhân sự -->
            <td class="stat-card stat-card-total" style="width: 25%;">
                <div class="stat-label">Tổng nhân sự</div>
                <div class="stat-value">{{ $pdfStats['total'] }}</div>
                <div class="stat-desc">Nhân sự thuộc bộ lọc</div>
            </td>
            <!-- Cột 2: Đang hoạt động -->
            <td class="stat-card stat-card-active" style="width: 25%;">
                <div class="stat-label">Đang làm việc</div>
                <div class="stat-value">{{ $pdfStats['active'] }}</div>
                <div class="stat-desc">{{ $pdfStats['total'] > 0 ? round(($pdfStats['active'] / $pdfStats['total']) * 100, 1) : 0 }}% tỷ lệ hoạt động</div>
            </td>
            <!-- Cột 3: Tạm dừng -->
            <td class="stat-card stat-card-banned" style="width: 25%;">
                <div class="stat-label">Tạm dừng</div>
                <div class="stat-value">{{ $pdfStats['banned'] }}</div>
                <div class="stat-desc">Tài khoản bị khóa</div>
            </td>
            <!-- Cột 4: Cơ cấu vai trò -->
            <td class="stat-card stat-card-role" style="width: 25%;">
                <div class="stat-label">Cơ cấu vai trò</div>
                <div style="font-size: 9px; font-weight: bold; color: #334155; margin-top: 2px;">
                    Admin: {{ $pdfStats['admin'] }} | Manager: {{ $pdfStats['manager'] }} | Staff: {{ $pdfStats['staff'] }}
                </div>
                <div class="stat-desc">Phân bố quyền hạn</div>
            </td>
        </tr>
    </table>

    <!-- Main Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">Mã NV</th>
                <th style="width: 20%;">Họ và tên</th>
                <th style="width: 25%;">Email</th>
                <th style="width: 15%;" class="text-center">Số điện thoại</th>
                <th style="width: 10%;" class="text-center">Vai trò</th>
                <th style="width: 10%;" class="text-center">Trạng thái</th>
                <th style="width: 10%;" class="text-center">Ngày tham gia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td class="text-center font-semibold">EMP-{{ $employee->user_id }}</td>
                    <td class="font-semibold">{{ $employee->full_name }}</td>
                    <td>{{ $employee->email }}</td>
                    <td class="text-center">{{ $employee->phone_number ?? '-' }}</td>
                    <td class="text-center">
                        @if($employee->role_id == 1)
                            <span class="badge badge-role-admin">Admin</span>
                        @elseif($employee->role_id == 2)
                            <span class="badge badge-role-manager">Manager</span>
                        @else
                            <span class="badge badge-role-staff">Staff</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($employee->status === 'Active')
                            <span class="badge badge-active">Hoạt động</span>
                        @else
                            <span class="badge badge-banned">Đã khóa</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $employee->created_at ? $employee->created_at->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #64748b; font-style: italic;">
                        Không có nhân viên nào thỏa mãn bộ lọc hiện tại.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signatures -->
    <table class="signature-container">
        <tr>
            <td class="signature-column">
                <div class="signature-title">Giám đốc Nhân sự</div>
                <div style="height: 60px;"></div>
                <div class="signature-name">...................................................</div>
                <div class="signature-sub">(Ký, ghi rõ họ tên & đóng dấu)</div>
            </td>
            <td class="signature-column">
                <div class="signature-title">Người lập biểu</div>
                <div style="height: 60px;"></div>
                <div class="signature-name">{{ auth()->user()->full_name }}</div>
                <div class="signature-sub">(Ký và ghi rõ họ tên)</div>
            </td>
        </tr>
    </table>

    <!-- Footer static -->
    <div class="pdf-footer">
        Trang in được trích xuất tự động từ hệ thống TMĐT lúc {{ now()->format('d/m/Y H:i:s') }}. Bản quyền thuộc về doanh nghiệp.
    </div>

</body>
</html>
