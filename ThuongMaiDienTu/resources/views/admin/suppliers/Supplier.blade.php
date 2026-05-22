@extends('admin.layouts.master')

@section('title', 'Quản Lý Nhà Cung Cấp')
@section('page-title', 'Quản Lý Nhà Cung Cấp')

@section('content')
<style>
    :root {
        --accent: #4f46e5;
        --accent-hover: #4338ca;
        --accent-soft: rgba(79, 70, 229, .12);
        --accent-glow: rgba(79, 70, 229, .16);
        --success: #16a34a;
        --danger: #dc2626;
        --border: #e2e8f0;
        --surface: #ffffff;
        --surface-soft: #f8fafc;
        --text-primary: #0f172a;
        --text-secondary: #64748b;
    }
    .page-header,.table-card-header,.app-modal-header,.app-modal-footer{box-sizing:border-box}
    .page-header{background:linear-gradient(135deg,#fff 0%,#f8fafc 100%);border-bottom:1px solid var(--border);padding:24px 0}
    .page-header h1{font-size:1.55rem;font-weight:800;margin:0;display:flex;align-items:center;gap:12px;color:var(--text-primary);flex-wrap:wrap}
    .page-header h1 i{font-size:1.5rem;color:var(--accent)}
    .badge-count{background:var(--accent);color:#fff;font-size:.75rem;font-weight:700;padding:4px 10px;border-radius:20px;white-space:nowrap}
    .page-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .btn-accent{height:42px;padding:0 16px;border-radius:12px;font-weight:700;font-size:.875rem;display:inline-flex;align-items:center;justify-content:center;gap:8px;white-space:nowrap;background:var(--accent);color:#fff;border:none;box-shadow:0 10px 20px rgba(79,70,229,.14)}
    .btn-accent:hover{background:var(--accent-hover);color:#fff;transform:translateY(-1px);box-shadow:0 14px 26px rgba(79,70,229,.18)}
    .btn-danger-soft{background:var(--danger);color:#fff;border:none;box-shadow:0 10px 20px rgba(220,38,38,.14)}
    .btn-danger-soft:hover{background:#b91c1c;color:#fff;transform:translateY(-1px);box-shadow:0 14px 26px rgba(220,38,38,.18)}
    .stat-card{background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:20px 22px;transition:.25s ease;box-shadow:0 10px 30px rgba(15,23,42,.04);height:100%}
    .stat-card:hover{transform:translateY(-3px);border-color:rgba(79,70,229,.25);box-shadow:0 16px 40px rgba(79,70,229,.08)}
    .stat-card .stat-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;background:var(--accent-soft)!important;color:var(--accent)!important;flex-shrink:0}
    .stat-card .stat-value{font-size:1.5rem;font-weight:800;color:var(--text-primary);line-height:1.1}
    .stat-card .stat-label{font-size:.82rem;color:var(--text-secondary);margin-top:2px}
    .table-card{background:var(--surface);border:1px solid var(--border);border-radius:18px;overflow:hidden;box-shadow:0 10px 30px rgba(15,23,42,.05)}
    .table-card-header{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;background:var(--surface-soft)}
    .table-card-header h5{margin:0;font-weight:700;font-size:1.02rem;color:var(--text-primary)}
    .table-toolbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;width:100%}
    .search-box{position:relative;flex:1 1 320px}
    .search-box input{background:#fff;border:1px solid var(--border);color:var(--text-primary);border-radius:12px;padding:11px 14px 11px 38px;width:100%;font-size:.875rem;transition:border-color .2s,box-shadow .2s}
    .search-box input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 4px var(--accent-glow)}
    .search-box i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:.9rem}
    .search-clear-btn{height:42px;padding:0 14px;border-radius:12px;border:1px solid var(--border);background:#fff;color:var(--text-secondary);font-weight:700;display:inline-flex;align-items:center;gap:6px}
    .search-clear-btn:hover{background:#f8fafc;color:var(--text-primary)}
    .table-custom{margin:0;width:100%}
    .table-custom thead th{background:#f8fafc;color:var(--text-secondary);font-weight:700;font-size:.78rem;text-transform:uppercase;letter-spacing:.6px;padding:14px 20px;border:none;white-space:nowrap;vertical-align:middle}
    .table-custom tbody td{padding:16px 20px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:.9rem;color:var(--text-primary)}
    .table-custom tbody tr:hover{background:#f8fafc}
    .table-actions{display:flex;align-items:center;justify-content:center;gap:10px;flex-wrap:nowrap}
    .btn-action{width:44px;height:44px;min-width:44px;min-height:44px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #dbe3f0;background:#f8fafc;color:var(--text-secondary);transition:all .2s;font-size:1rem;cursor:pointer;flex-shrink:0;box-shadow:0 6px 14px rgba(15,23,42,.04);position:relative;z-index:5}
    .btn-action.edit{color:var(--accent)}
    .btn-action.delete{color:var(--danger)}
    .btn-action.edit:hover{background:var(--accent);color:#fff;border-color:var(--accent);transform:translateY(-1px)}
    .btn-action.delete:hover{background:var(--danger);color:#fff;border-color:var(--danger);transform:translateY(-1px)}
    .btn-action:focus{outline:none;box-shadow:0 0 0 4px var(--accent-glow)}
    .app-modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:16px;background:rgba(15,23,42,.45);z-index:9999}
    .app-modal.is-open{display:flex}
    .app-modal-panel{width:100%;max-width:720px;background:#fff;border:1px solid var(--border);border-radius:18px;color:var(--text-primary);box-shadow:0 24px 60px rgba(15,23,42,.14);overflow:hidden}
    .app-modal-panel.small{max-width:520px}
    .app-modal-header{border-bottom:1px solid var(--border);padding:20px 24px;background:var(--surface-soft);display:flex;align-items:center;justify-content:space-between;gap:12px}
    .app-modal-title{font-weight:800;font-size:1.1rem;color:var(--text-primary);margin:0}
    .app-modal-close{border:none;background:transparent;font-size:1.5rem;line-height:1;color:var(--text-secondary);cursor:pointer}
    .app-modal-body{padding:24px}
    .app-modal-footer{border-top:1px solid var(--border);padding:16px 24px;background:var(--surface-soft);display:flex;justify-content:flex-end;gap:10px}
    .supplier-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .supplier-field{display:flex;flex-direction:column;gap:6px}
    .supplier-field.full{grid-column:1 / -1}
    .form-label{font-weight:600;font-size:.875rem;color:var(--text-secondary);margin-bottom:0;display:block;line-height:1.3}
    .form-control{width:100%;background:#fff;border:1px solid var(--border);color:var(--text-primary);border-radius:12px;padding:12px 14px;font-size:.9rem;transition:border-color .2s,box-shadow .2s}
    .form-control:focus{background:#fff;color:var(--text-primary);border-color:var(--accent);box-shadow:0 0 0 4px var(--accent-glow)}
    .btn-cancel{background:#fff;color:var(--text-secondary);border:1px solid var(--border);border-radius:12px;padding:9px 20px;font-weight:600;transition:all .2s}
    .btn-cancel:hover{background:#f8fafc;color:var(--text-primary)}
    .alert-custom{border-radius:14px;padding:14px 20px;font-size:.9rem;border:none;display:flex;align-items:center;gap:10px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05)}
    .alert-success-custom{border-left:4px solid var(--success);color:var(--success)}
    .alert-danger-custom{border-left:4px solid var(--danger);color:var(--danger)}
    .empty-state{text-align:center;padding:60px 20px;color:var(--text-secondary)}
    .empty-state i{font-size:3rem;margin-bottom:16px;opacity:.35;color:var(--text-secondary)}
    /* Custom pagination wrapper to force horizontal layout */
    .custom-pagination-nav {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        width: 100% !important;
        padding: 16px 24px !important;
        border-top: 1px solid var(--border) !important;
        background-color: #ffffff !important;
        flex-wrap: wrap !important;
        gap: 16px !important;
    }
    
    /* Left side: Results info */
    .custom-pagination-nav p {
        margin: 0 !important;
        font-size: 0.82rem !important;
        font-weight: 600 !important;
        color: #475569 !important; /* Màu xám đậm Slate-600 */
        background: #f8fafc !important; /* Nền xám nhạt Slate-50 */
        padding: 8px 16px !important;
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02) !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
    }
    
    .custom-pagination-nav p span {
        color: #0f172a !important; /* Màu đen Slate-900 để làm nổi bật các con số */
        font-weight: 800 !important;
    }
    
    /* Right side: Page selection numbers */
    .pagination {
        display: flex !important;
        list-style: none !important;
        padding-left: 0 !important;
        margin: 0 !important;
        gap: 6px !important;
        align-items: center !important;
    }
    
    .page-item {
        display: inline-block !important;
    }
    
    .page-link, .page-item span {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 8px 14px !important;
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        color: #475569 !important;
        background: #ffffff !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        text-decoration: none !important;
        transition: all 0.2s !important;
        min-width: 40px !important;
        height: 40px !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    
    .page-link:hover {
        background: #f8fafc !important;
        color: #2563eb !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px);
    }
    
    /* Active page item */
    .page-item.active .page-link, .page-item.active span {
        background: #2563eb !important;
        color: #ffffff !important;
        border-color: #2563eb !important;
        box-shadow: 0 4px 12px rgba(37,99,235,0.25) !important;
    }
    
    /* Disabled pagination buttons */
    .page-item.disabled .page-link, .page-item.disabled span {
        color: #94a3b8 !important;
        background: #f8fafc !important;
        border-color: #e2e8f0 !important;
        pointer-events: none !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
    }
    @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
    .animate-in{animation:fadeInUp .4s ease forwards}
    .id-badge{background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:4px 10px;font-size:.8rem;font-weight:700;color:var(--text-secondary)}
    .po-count-badge{background:var(--accent-soft);color:var(--accent);padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:700}
    .contact-cell{font-size:.85rem;color:var(--text-secondary)}
    .contact-cell i{width:16px;color:var(--accent);margin-right:4px;font-size:.8rem}
    @media (max-width:768px){.page-header{padding:20px 0}.page-header h1{font-size:1.25rem}.page-actions{width:100%}.page-actions .btn-accent{flex:1;min-width:0}.table-card-header{padding:16px}.table-toolbar{width:100%}.search-box{width:100%}.table-actions{gap:6px}.btn-action{width:36px;height:36px}.supplier-form-grid{grid-template-columns:1fr}}
</style>

<div class="page-header">
    <div class="container-fluid">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h1><i class="bi bi-building"></i> Quản Lý Nhà Cung Cấp <span class="badge-count">{{ $totalSuppliers ?? 0 }} NCC</span></h1>
            <div class="page-actions">
                <button class="btn btn-accent" type="button" onclick="openAppModal('addModal')"><i class="bi bi-plus-lg"></i> Thêm NCC</button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid py-4">
    @if(session('success'))<div class="alert alert-custom alert-success-custom animate-in" id="flash-alert"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-custom alert-danger-custom animate-in" id="flash-alert"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-custom alert-danger-custom animate-in"><i class="bi bi-exclamation-triangle-fill"></i><div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div></div>@endif

    <div class="row g-3 mb-4 align-items-stretch">
        <div class="col-md-4 animate-in"><div class="stat-card d-flex align-items-center gap-3"><div class="stat-icon"><i class="bi bi-building"></i></div><div><div class="stat-value">{{ $totalSuppliers ?? 0 }}</div><div class="stat-label">Tổng Nhà Cung Cấp</div></div></div></div>
        <div class="col-md-4 animate-in"><div class="stat-card d-flex align-items-center gap-3"><div class="stat-icon"><i class="bi bi-telephone-fill"></i></div><div><div class="stat-value">{{ $suppliers->whereNotNull('phone')->count() }}</div><div class="stat-label">Có Số Điện Thoại</div></div></div></div>
        <div class="col-md-4 animate-in"><div class="stat-card d-flex align-items-center gap-3"><div class="stat-icon"><i class="bi bi-envelope-fill"></i></div><div><div class="stat-value">{{ $suppliers->whereNotNull('email')->count() }}</div><div class="stat-label">Có Email Liên Hệ</div></div></div></div>
    </div>

    <div class="table-card animate-in">
        <div class="table-card-header">
            <h5><i class="bi bi-list-ul me-2" style="color:var(--accent);"></i>Danh Sách Nhà Cung Cấp</h5>
            <div class="table-toolbar">
                <div class="search-box"><i class="bi bi-search"></i><input type="text" id="searchInput" placeholder="Tìm theo tên, SĐT, email, địa chỉ..." oninput="filterTable()"></div>
                <button type="button" class="search-clear-btn" onclick="clearSearch()"><i class="bi bi-x-lg"></i> Xóa lọc</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-custom" id="supplierTable">
                <thead><tr><th style="width:70px;">#</th><th>Tên NCC</th><th>SĐT</th><th>Email</th><th>Địa Chỉ</th><th style="text-align:center;">Phiếu Nhập</th><th style="width:120px;text-align:center;">Thao Tác</th></tr></thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td><span class="id-badge">{{ $suppliers->firstItem() + $loop->index }}</span></td>
                            <td><div class="d-flex align-items-center gap-2"><i class="bi bi-building-fill" style="color:var(--accent);font-size:1.1rem;"></i><strong>{{ $supplier->name }}</strong></div></td>
                            <td class="contact-cell">{{ $supplier->phone ?: '—' }}</td>
                            <td class="contact-cell">{{ $supplier->email ?: '—' }}</td>
                            <td class="contact-cell" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $supplier->address }}">{{ $supplier->address ?: '—' }}</td>
                            <td class="text-center"><span class="po-count-badge">{{ $supplier->purchase_orders_count ?? 0 }}</span></td>
                            <td class="text-center">
                                <div class="table-actions">
                                    <button class="btn-action edit js-edit-supplier" type="button" title="Sửa"
                                            data-id="{{ $supplier->supplier_id }}"
                                            data-name="{{ e($supplier->name) }}"
                                            data-phone="{{ e($supplier->phone ?? '') }}"
                                            data-email="{{ e($supplier->email ?? '') }}"
                                            data-address="{{ e($supplier->address ?? '') }}"
                                            data-version="{{ $supplier->version }}">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn-action delete js-delete-supplier" type="button" title="Xóa"
                                            data-id="{{ $supplier->supplier_id }}"
                                            data-name="{{ e($supplier->name) }}">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty-state"><i class="bi bi-inbox d-block"></i><p class="mb-0">Chưa có nhà cung cấp nào. Hãy thêm NCC đầu tiên!</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($suppliers, 'links') && $suppliers->lastPage() > 1)
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $suppliers->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>{{ $suppliers->currentPage() }}</span> / {{ $suppliers->lastPage() }}
                </p>
                
                <ul class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($suppliers->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $suppliers->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($suppliers->getUrlRange(1, $suppliers->lastPage()) as $page => $url)
                        @if ($page == $suppliers->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @elseif ($page === 1 || $page === $suppliers->lastPage() || abs($page - $suppliers->currentPage()) <= 1)
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @elseif (($page === 2 && $suppliers->currentPage() > 3) || ($page === $suppliers->lastPage() - 1 && $suppliers->currentPage() < $suppliers->lastPage() - 2))
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($suppliers->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $suppliers->nextPageUrl() }}" rel="next">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </nav>
        @elseif(method_exists($suppliers, 'links'))
            <nav class="custom-pagination-nav">
                <p class="text-sm">
                    Kết quả: <span>{{ $suppliers->total() }}</span> 
                    <span class="mx-2" style="opacity: 0.3;">|</span> 
                    Trang <span>1</span> / 1
                </p>
            </nav>
        @endif
    </div>
</div>

<div class="app-modal" id="addModal">
    <div class="app-modal-panel">
        <form action="{{ route('admin.suppliers.store') }}" method="POST">
            @csrf
            <div class="app-modal-header">
                <h5 class="app-modal-title"><i class="bi bi-plus-circle-fill me-2" style="color:var(--accent);"></i>Thêm Nhà Cung Cấp Mới</h5>
                <button type="button" class="app-modal-close" onclick="closeAppModal('addModal')">&times;</button>
            </div>
            <div class="app-modal-body">
                <div class="supplier-form-grid">
                    <div class="supplier-field full">
                        <label class="form-label">Tên Nhà Cung Cấp <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="VD: Samsung Việt Nam" required maxlength="100">
                    </div>
                    <div class="supplier-field">
                        <label class="form-label">Số Điện Thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="VD: 0901234567" maxlength="20">
                    </div>
                    <div class="supplier-field">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="VD: info@samsung.vn" maxlength="100">
                    </div>
                    <div class="supplier-field full">
                        <label class="form-label">Địa Chỉ</label>
                        <input type="text" name="address" class="form-control" placeholder="VD: 123 Nguyễn Huệ, Q.1, TP.HCM" maxlength="255">
                    </div>
                </div>
            </div>
            <div class="app-modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeAppModal('addModal')">Hủy</button>
                <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Thêm</button>
            </div>
        </form>
    </div>
</div>

<div class="app-modal" id="editModal">
    <div class="app-modal-panel">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="version" id="editSupplierVersion">
            <div class="app-modal-header">
                <h5 class="app-modal-title"><i class="bi bi-pencil-square me-2" style="color:var(--accent);"></i>Sửa Nhà Cung Cấp</h5>
                <button type="button" class="app-modal-close" onclick="closeAppModal('editModal')">&times;</button>
            </div>
            <div class="app-modal-body">
                <div class="supplier-form-grid">
                    <div class="supplier-field full">
                        <label class="form-label">Tên Nhà Cung Cấp <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required maxlength="100">
                    </div>
                    <div class="supplier-field">
                        <label class="form-label">Số Điện Thoại</label>
                        <input type="text" name="phone" id="editPhone" class="form-control" maxlength="20">
                    </div>
                    <div class="supplier-field">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="editEmail" class="form-control" maxlength="100">
                    </div>
                    <div class="supplier-field full">
                        <label class="form-label">Địa Chỉ</label>
                        <input type="text" name="address" id="editAddress" class="form-control" maxlength="255">
                    </div>
                </div>
            </div>
            <div class="app-modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeAppModal('editModal')">Hủy</button>
                <button type="submit" class="btn btn-accent"><i class="bi bi-check-lg"></i> Cập Nhật</button>
            </div>
        </form>
    </div>
</div>

<div class="app-modal" id="deleteModal">
    <div class="app-modal-panel small">
        <form id="deleteConfirmForm" method="POST" onsubmit="return false;">
            @csrf
            @method('DELETE')
            <div class="app-modal-header">
                <h5 class="app-modal-title"><i class="bi bi-trash3-fill me-2" style="color:var(--danger);"></i>Xóa Nhà Cung Cấp</h5>
                <button type="button" class="app-modal-close" onclick="closeAppModal('deleteModal')">&times;</button>
            </div>
            <div class="app-modal-body">
                <p class="mb-0 text-center" style="color:var(--text-primary);">Bạn có chắc muốn xóa nhà cung cấp <strong id="deleteSupplierName"></strong>?</p>
                <p class="mb-0 mt-2 text-center" style="color:var(--text-secondary);font-size:.875rem;">Hành động này không thể hoàn tác.</p>
            </div>
            <div class="app-modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeAppModal('deleteModal')">Hủy</button>
                <button type="button" class="btn btn-danger-soft" onclick="submitDelete()"><i class="bi bi-trash3-fill"></i> Xóa</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>
<script>
    var deleteTargetId = null;
    function openAppModal(id){const modal=document.getElementById(id);if(modal) modal.classList.add('is-open');}
    function closeAppModal(id){const modal=document.getElementById(id);if(modal) modal.classList.remove('is-open');if(id==='deleteModal') deleteTargetId=null;}
    
    var supplierTable = document.getElementById('supplierTable');
    if (supplierTable) {
        supplierTable.addEventListener('click', function(e) {
            var editBtn = e.target.closest('.js-edit-supplier');
            if (editBtn) {
                openEditModal(
                    editBtn.dataset.id,
                    editBtn.dataset.name,
                    editBtn.dataset.phone,
                    editBtn.dataset.email,
                    editBtn.dataset.address,
                    editBtn.dataset.version
                );
                return;
            }
            var deleteBtn = e.target.closest('.js-delete-supplier');
            if (deleteBtn) {
                openDeleteModal(
                    deleteBtn.dataset.id,
                    deleteBtn.dataset.name
                );
                return;
            }
        });
    }

    function openEditModal(id,name,phone,email,address,version){document.getElementById('editName').value=name||'';document.getElementById('editPhone').value=phone||'';document.getElementById('editEmail').value=email||'';document.getElementById('editAddress').value=address||'';document.getElementById('editSupplierVersion').value=version||1;document.getElementById('editForm').action="{{ url('admin/suppliers') }}/"+id;openAppModal('editModal');}
    function openDeleteModal(id,name){deleteTargetId=id;document.getElementById('deleteSupplierName').textContent=name||'';openAppModal('deleteModal');}
    function submitDelete(){if(!deleteTargetId)return;const form=document.getElementById('deleteConfirmForm');form.action="{{ url('admin/suppliers') }}/"+deleteTargetId;form.submit();}
    
    if (!window.hasSupplierKeydownRegistered) {
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                closeAppModal('addModal');
                closeAppModal('editModal');
                closeAppModal('deleteModal');
            }
        });
        window.hasSupplierKeydownRegistered = true;
    }

    function filterTable(){const input=document.getElementById('searchInput').value.toLowerCase().trim();document.querySelectorAll('#supplierTable tbody tr').forEach(row=>{row.style.display=row.textContent.toLowerCase().includes(input)?'':'';});}
    function clearSearch(){const input=document.getElementById('searchInput');input.value='';filterTable();input.focus();}
    var flashAlert=document.getElementById('flash-alert');if(flashAlert){setTimeout(()=>{flashAlert.style.transition='opacity .5s ease';flashAlert.style.opacity='0';setTimeout(()=>flashAlert.remove(),500);},4000);}
</script>
@endsection