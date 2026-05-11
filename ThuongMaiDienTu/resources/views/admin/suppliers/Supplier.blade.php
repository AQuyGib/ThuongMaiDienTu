<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản Lý Nhà Cung Cấp</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Tailwind CSS (for Sidebar) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --bg-primary: #0f1117;
            --bg-secondary: #1a1d27;
            --bg-card: #1e2231;
            --bg-hover: #262a3a;
            --accent: #6c5ce7;
            --accent-hover: #7f71ed;
            --accent-glow: rgba(108, 92, 231, 0.25);
            --text-primary: #e8e8ef;
            --text-secondary: #9ca3b4;
            --border: #2d3148;
            --danger: #e74c5e;
            --success: #2ed573;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
        }

        .page-header {
            background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-card) 100%);
            border-bottom: 1px solid var(--border);
            padding: 28px 0;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-header h1 i {
            font-size: 1.5rem;
            color: var(--accent);
        }

        .badge-count {
            background: var(--accent);
            color: #fff;
            font-size: .75rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            margin-left: 8px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px 24px;
            transition: all .3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            border-color: var(--accent);
            box-shadow: 0 8px 30px var(--accent-glow);
        }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card .stat-value {
            font-size: 1.6rem;
            font-weight: 700;
        }

        .stat-card .stat-label {
            font-size: .82rem;
            color: var(--text-secondary);
        }

        .table-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
        }

        .table-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.05rem;
        }

        .search-box {
            position: relative;
            max-width: 280px;
        }

        .search-box input {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 9px 14px 9px 38px;
            width: 100%;
            font-size: .875rem;
            transition: border-color .2s;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: .9rem;
        }

        .table-custom {
            margin: 0;
            width: 100%;
        }

        .table-custom thead th {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: .8rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            padding: 14px 20px;
            border: none;
            white-space: nowrap;
        }

        .table-custom tbody td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
            font-size: .9rem;
        }

        .table-custom tbody tr {
            transition: background .2s;
        }

        .table-custom tbody tr:hover {
            background: var(--bg-hover);
        }

        .table-custom tbody tr:last-child td {
            border-bottom: none;
        }

        .btn-accent {
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 9px 20px;
            font-weight: 600;
            font-size: .875rem;
            transition: all .25s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-accent:hover {
            background: var(--accent-hover);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 18px var(--accent-glow);
        }

        .btn-action {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            background: var(--bg-secondary);
            color: var(--text-secondary);
            transition: all .2s;
            font-size: .9rem;
            cursor: pointer;
        }

        .btn-action.edit:hover {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .btn-action.delete:hover {
            background: var(--danger);
            color: #fff;
            border-color: var(--danger);
        }

        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            color: var(--text-primary);
        }

        .modal-header {
            border-bottom: 1px solid var(--border);
            padding: 20px 24px;
        }

        .modal-header .modal-title {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .modal-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            border-top: 1px solid var(--border);
            padding: 16px 24px;
        }

        .form-label {
            font-weight: 500;
            font-size: .875rem;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .form-control {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus {
            background: var(--bg-primary);
            color: var(--text-primary);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .btn-cancel {
            background: var(--bg-secondary);
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 9px 20px;
            font-weight: 500;
            transition: all .2s;
        }

        .btn-cancel:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .alert-custom {
            border-radius: 12px;
            padding: 14px 20px;
            font-size: .9rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success-custom {
            background: rgba(46, 213, 115, .1);
            color: var(--success);
        }

        .alert-danger-custom {
            background: rgba(231, 76, 94, .1);
            color: var(--danger);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: .4;
        }

        .pagination .page-link {
            background: var(--bg-secondary);
            border: 1px solid var(--border);
            color: var(--text-secondary);
            border-radius: 8px !important;
            margin: 0 3px;
            font-size: .85rem;
            padding: 7px 13px;
            transition: all .2s;
        }

        .pagination .page-link:hover {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .pagination .page-item.active .page-link {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp .4s ease forwards;
        }

        .id-badge {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px 10px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .po-count-badge {
            background: rgba(108, 92, 231, .15);
            color: var(--accent);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: .8rem;
            font-weight: 600;
        }

        .contact-cell {
            font-size: .85rem;
            color: var(--text-secondary);
        }

        .contact-cell i {
            width: 16px;
            color: var(--accent);
            margin-right: 4px;
            font-size: .8rem;
        }
    </style>
</head>

<body class="flex h-screen overflow-hidden">

    {{-- ===== SIDEBAR ===== --}}
    @include('admin.partials.sidebar')

    {{-- ===== MAIN WRAPPER ===== --}}
    <div class="flex-1 overflow-y-auto w-full">

        {{-- ===== HEADER ===== --}}
        <div class="page-header">
            <div class="container">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <h1>
                        <i class="bi bi-building"></i>
                        Quản Lý Nhà Cung Cấp
                        <span class="badge-count">{{ $totalSuppliers ?? 0 }} NCC</span>
                    </h1>
                    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="bi bi-plus-lg"></i> Thêm NCC
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="container py-4">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-custom alert-success-custom animate-in" id="flash-alert">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-custom alert-danger-custom animate-in" id="flash-alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-custom alert-danger-custom animate-in">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Stats Row --}}
            <div class="row g-3 mb-4">
                <div class="col-md-12 animate-in">
                    <div class="stat-card d-flex align-items-center gap-3">
                        <div class="stat-icon" style="background:rgba(108,92,231,.15);color:var(--accent);">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <div class="stat-value">{{ $totalSuppliers ?? 0 }}</div>
                            <div class="stat-label">Tổng Nhà Cung Cấp</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="table-card animate-in">
                <div class="table-card-header">
                    <h5><i class="bi bi-list-ul me-2" style="color:var(--accent);"></i>Danh Sách Nhà Cung Cấp</h5>
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="searchInput" placeholder="Tìm kiếm NCC..." onkeyup="filterTable()">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom" id="supplierTable">
                        <thead>
                            <tr>
                                <th style="width:70px;">#</th>
                                <th>Tên NCC</th>
                                <th>SĐT</th>
                                <th>Email</th>
                                <th>Địa Chỉ</th>
                                <th style="text-align:center;">Phiếu Nhập</th>
                                <th style="width:120px;text-align:center;">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplier)
                                <tr>
                                    <td><span class="id-badge">{{ $suppliers->firstItem() + $loop->index }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-building-fill"
                                                style="color:var(--accent);font-size:1.1rem;"></i>
                                            <strong>{{ $supplier->name }}</strong>
                                            </daiv>
                                    </td>
                                    <td class="contact-cell">
                                        @if($supplier->phone)
                                            <i class="bi bi-telephone-fill"></i>{{ $supplier->phone }}
                                        @else
                                            <span style="opacity:.4;">—</span>
                                        @endif
                                    </td>
                                    <td class="contact-cell">
                                        @if($supplier->email)
                                            <i class="bi bi-envelope-fill"></i>{{ $supplier->email }}
                                        @else
                                            <span style="opacity:.4;">—</span>
                                        @endif
                                    </td>
                                    <td class="contact-cell"
                                        style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                        title="{{ $supplier->address }}">
                                        @if($supplier->address)
                                            <i class="bi bi-geo-alt-fill"></i>{{ $supplier->address }}
                                        @else
                                            <span style="opacity:.4;">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="po-count-badge">{{ $supplier->purchase_orders_count ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn-action edit" title="Sửa"
                                                onclick="openEditModal({{ $supplier->supplier_id }}, '{{ addslashes($supplier->name) }}', '{{ addslashes($supplier->phone ?? '') }}', '{{ addslashes($supplier->email ?? '') }}', '{{ addslashes($supplier->address ?? '') }}')">
                                                <i class="bi bi-pencil-fill"></i>
                                            </button>
                                            <button class="btn-action delete" title="Xóa"
                                                onclick="confirmDelete({{ $supplier->supplier_id }}, '{{ addslashes($supplier->name) }}')">
                                                <i class="bi bi-trash3-fill"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="empty-state">
                                            <i class="bi bi-inbox d-block"></i>
                                            <p class="mb-0">Chưa có nhà cung cấp nào. Hãy thêm NCC đầu tiên!</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($suppliers, 'links'))
                    <div class="d-flex justify-content-center py-3">
                        {{ $suppliers->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== MODAL: THÊM NCC ===== --}}
        <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form action="{{ route('admin.suppliers.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-plus-circle-fill me-2"
                                    style="color:var(--accent);"></i>Thêm Nhà Cung Cấp Mới</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tên Nhà Cung Cấp <span
                                            style="color:var(--danger);">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="VD: Samsung Việt Nam" required maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số Điện Thoại</label>
                                    <input type="text" name="phone" class="form-control" placeholder="VD: 0901234567"
                                        maxlength="20">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="VD: info@samsung.vn" maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Địa Chỉ</label>
                                    <input type="text" name="address" class="form-control"
                                        placeholder="VD: 123 Nguyễn Huệ, Q.1, TP.HCM" maxlength="255">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Thêm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== MODAL: SỬA NCC ===== --}}
        <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"
                                    style="color:var(--accent);"></i>Sửa Nhà Cung Cấp</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tên Nhà Cung Cấp <span
                                            style="color:var(--danger);">*</span></label>
                                    <input type="text" name="name" id="editName" class="form-control" required
                                        maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Số Điện Thoại</label>
                                    <input type="text" name="phone" id="editPhone" class="form-control" maxlength="20">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" id="editEmail" class="form-control"
                                        maxlength="100">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Địa Chỉ</label>
                                    <input type="text" name="address" id="editAddress" class="form-control"
                                        maxlength="255">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Cập
                                Nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ===== FORM ẨN: XÓA ===== --}}
        <form id="deleteForm" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>

    </div>{{-- end main wrapper --}}

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ===== Mở modal Sửa =====
        function openEditModal(id, name, phone, email, address) {
            document.getElementById('editName').value = name;
            document.getElementById('editPhone').value = phone || '';
            document.getElementById('editEmail').value = email || '';
            document.getElementById('editAddress').value = address || '';

            const form = document.getElementById('editForm');
            form.action = "{{ url('admin/suppliers') }}/" + id;

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        // ===== Xác nhận Xóa =====
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                html: `Bạn có chắc muốn xóa nhà cung cấp <strong>"${name}"</strong>?<br><small style="color:#9ca3b4;">Hành động này không thể hoàn tác.</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c5e',
                cancelButtonColor: '#2d3148',
                confirmButtonText: '<i class="bi bi-trash3-fill"></i> Xóa',
                cancelButtonText: 'Hủy',
                background: '#1e2231',
                color: '#e8e8ef',
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteForm');
                    form.action = "{{ url('admin/suppliers') }}/" + id;
                    form.submit();
                }
            });
        }

        // ===== Tìm kiếm bảng =====
        function filterTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#supplierTable tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(input) ? '' : 'none';
            });
        }

        // ===== Auto-hide flash alert =====
        const flashAlert = document.getElementById('flash-alert');
        if (flashAlert) {
            setTimeout(() => {
                flashAlert.style.transition = 'opacity .5s ease';
                flashAlert.style.opacity = '0';
                setTimeout(() => flashAlert.remove(), 500);
            }, 4000);
        }

        // ===== Toggle Sidebar (Mobile) =====
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) sidebar.classList.toggle('-translate-x-full');
        }
    </script>

</body>

</html>