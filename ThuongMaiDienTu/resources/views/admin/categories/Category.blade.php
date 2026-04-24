<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản Lý Danh Mục</title>

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
            --danger-hover: #d4364a;
            --success: #2ed573;
            --warning: #ffa502;
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

        /* ===== HEADER ===== */
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

        /* ===== STATS CARDS ===== */
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

        /* ===== TABLE CARD ===== */
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

        /* ===== SEARCH ===== */
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

        /* ===== TABLE ===== */
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

        .img-upload-area{border:2px dashed var(--border);border-radius:12px;padding:20px;text-align:center;transition:all .3s;cursor:pointer;position:relative}
        .img-upload-area:hover{border-color:var(--accent);background:rgba(108,92,231,.05)}
        .img-upload-area input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer}
        .img-upload-area i{font-size:2rem;color:var(--text-secondary);margin-bottom:8px}
        .img-upload-area p{margin:0;font-size:.82rem;color:var(--text-secondary)}
        .img-preview{max-width:100%;max-height:150px;border-radius:10px;margin-top:10px;display:none;border:1px solid var(--border)}
        .img-thumb{width:45px;height:45px;border-radius:8px;object-fit:cover;border:1px solid var(--border)}
        .or-divider{display:flex;align-items:center;gap:10px;margin:12px 0;color:var(--text-secondary);font-size:.8rem}
        .or-divider::before,.or-divider::after{content:'';flex:1;height:1px;background:var(--border)}

        /* ===== BUTTONS ===== */
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

        /* ===== MODAL ===== */
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

        .form-control,
        .form-select {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus,
        .form-select:focus {
            background: var(--bg-primary);
            color: var(--text-primary);
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-select option {
            background: var(--bg-primary);
            color: var(--text-primary);
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

        /* ===== ALERTS ===== */
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

        /* ===== EMPTY STATE ===== */
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

        /* ===== PAGINATION ===== */
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

        /* ===== ANIMATION ===== */
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

        .animate-in:nth-child(2) {
            animation-delay: .05s;
        }

        .animate-in:nth-child(3) {
            animation-delay: .1s;
        }

        /* Category ID badge */
        .id-badge {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px 10px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--text-secondary);
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
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                    Quản Lý Danh Mục
                    <span class="badge-count">{{ isset($categories) ? $categories->count() : 0 }} mục mục</span>
                </h1>
                <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-lg"></i> Thêm Danh Mục
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
                        <i class="bi bi-grid-3x3-gap-fill"></i>
                    </div>
                    <div>
                        <div class="stat-value">{{ $totalCategories ?? 0 }}</div>
                        <div class="stat-label">Tổng Danh Mục</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table Card --}}
        <div class="table-card animate-in">
            <div class="table-card-header">
                <h5><i class="bi bi-list-ul me-2" style="color:var(--accent);"></i>Danh Sách Danh Mục</h5>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Tìm kiếm danh mục..." onkeyup="filterTable()">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-custom" id="categoryTable">
                    <thead>
                        <tr>
                            <th style="width:80px;">#</th>
                            <th>Tên Danh Mục</th>
                            <th>Số SP</th>
                            <th style="width:130px;text-align:center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $index => $category)
                            <tr>
                                <td><span class="id-badge">{{ $category->category_id }}</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($category->icon)
                                            <i class="fa-solid fa-{{ $category->icon }}" style="color:var(--accent);font-size:1.1rem;width:20px;text-align:center;"></i>
                                        @else
                                            <i class="bi bi-folder-fill" style="color:var(--accent);font-size:1.1rem;"></i>
                                        @endif
                                        <strong>{{ $category->name }}</strong>
                                    </div>
                                </td>

                                <td>
                                    <span
                                        style="color:var(--text-secondary);">{{ $category->products_count ?? $category->products->count() }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button class="btn-action edit" title="Sửa"
                                            onclick="openEditModal({{ $category->category_id }}, '{{ addslashes($category->name) }}', '{{ addslashes($category->icon ?? '') }}')">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn-action delete" title="Xóa"
                                            onclick="confirmDelete({{ $category->category_id }}, '{{ addslashes($category->name) }}')">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="bi bi-inbox d-block"></i>
                                        <p class="mb-0">Chưa có danh mục nào. Hãy thêm danh mục đầu tiên!</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(method_exists($categories, 'links'))
                <div class="d-flex justify-content-center py-3">
                    {{ $categories->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    {{-- ===== MODAL: THÊM DANH MỤC ===== --}}
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle-fill me-2"
                                style="color:var(--accent);"></i>Thêm Danh Mục Mới</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên Danh Mục <span style="color:var(--danger);">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="VD: Lò vi sóng"
                                    required maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Icon FontAwesome <span style="color:var(--text-secondary);font-weight:400;">(Tùy chọn)</span></label>
                                <input type="text" name="icon" class="form-control" placeholder="fa-tv, fa-box..."
                                    maxlength="50">
                                <div class="form-text" style="color:var(--text-secondary);font-size:.8rem;margin-top:6px;">Xem icon tại <a href="https://fontawesome.com/icons" target="_blank" style="color:var(--accent);">fontawesome.com</a></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tải ảnh từ máy tính</label>
                                <div class="img-upload-area" id="addUploadArea">
                                    <input type="file" name="image_file" accept="image/*" onchange="previewFile(this, 'addFilePreview')">
                                    <i class="bi bi-cloud-arrow-up-fill d-block"></i>
                                    <p>Nhấn hoặc kéo thả ảnh vào đây</p>
                                    <img id="addFilePreview" class="img-preview" alt="Preview">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hoặc dùng URL Ảnh</label>
                                <input type="text" name="image_url" class="form-control" placeholder="https://..." oninput="previewUrl(this, 'addUrlPreview')">
                                <img id="addUrlPreview" class="img-preview" alt="Preview">
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

    {{-- ===== MODAL: SỬA DANH MỤC ===== --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2" style="color:var(--accent);"></i>Sửa
                            Danh Mục</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên Danh Mục <span style="color:var(--danger);">*</span></label>
                                <input type="text" name="name" id="editName" class="form-control"
                                    placeholder="VD: Lò vi sóng" required maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Icon FontAwesome <span style="color:var(--text-secondary);font-weight:400;">(Tùy chọn)</span></label>
                                <input type="text" name="icon" id="editIcon" class="form-control" placeholder="fa-tv, fa-box..."
                                    maxlength="50">
                                <div class="form-text" style="color:var(--text-secondary);font-size:.8rem;margin-top:6px;">Xem icon tại <a href="https://fontawesome.com/icons" target="_blank" style="color:var(--accent);">fontawesome.com</a></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tải ảnh từ máy tính</label>
                                <div class="img-upload-area">
                                    <input type="file" name="image_file" accept="image/*" onchange="previewFile(this, 'editFilePreview')">
                                    <i class="bi bi-cloud-arrow-up-fill d-block"></i>
                                    <p>Nhấn hoặc kéo thả ảnh vào đây</p>
                                    <img id="editFilePreview" class="img-preview" alt="Preview">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hoặc dùng URL Ảnh</label>
                                <input type="text" name="image_url" id="editImageUrl" class="form-control" placeholder="https://..." oninput="previewUrl(this, 'editUrlPreview')">
                                <img id="editUrlPreview" class="img-preview" alt="Preview">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Cập Nhật</button>
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

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ===== Mở modal Sửa =====
        function openEditModal(id, name, icon) {
            document.getElementById('editName').value = name;
            document.getElementById('editIcon').value = icon || '';

            // Cập nhật action URL
            const form = document.getElementById('editForm');
            form.action = "{{ url('admin/categories') }}/" + id;

            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        // ===== Xác nhận Xóa =====
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                html: `Bạn có chắc muốn xóa danh mục <strong>"${name}"</strong>?<br><small style="color:#9ca3b4;">Hành động này không thể hoàn tác.</small>`,
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
                    form.action = "{{ url('admin/categories') }}/" + id;
                    form.submit();
                }
            });
        }

        // ===== Tìm kiếm bảng =====
        function filterTable() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#categoryTable tbody tr');
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
            if(sidebar) {
                sidebar.classList.toggle('-translate-x-full');
            }
        }

        // ===== Preview ảnh từ file =====
        function previewFile(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // ===== Preview ảnh từ URL =====
        function previewUrl(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.value.trim()) {
                preview.src = input.value.trim();
                preview.style.display = 'block';
                preview.onerror = function() { preview.style.display = 'none'; };
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
    </div>
</body>

</html>