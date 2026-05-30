@extends('admin.layouts.master')

@section('title', 'Tạo Phiếu Nhập Kho')

@push('styles')
<style>
    .page-tabs{display:flex;justify-content:center;gap:28px;margin-bottom:28px;flex-wrap:wrap}
    .page-tab{position:relative;font-size:1rem;font-weight:700;color:#64748b;text-decoration:none;padding-bottom:8px}
    .page-tab.active{color:#111827}
    .page-tab.active::after{content:'';position:absolute;left:0;right:0;bottom:0;height:2px;border-radius:999px;background:#111827}
    .hero-card{background:linear-gradient(135deg,#fff 0%,#f8fbff 100%);border:1px solid #e2e8f0;border-radius:26px;padding:22px 24px;box-shadow:0 12px 30px rgba(15,23,42,.05)}
    .hero-title{font-size:1.5rem;font-weight:800;margin:0;color:#0f172a;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .hero-title i{color:#2563eb}
    .hero-desc{color:#64748b;margin-top:4px}
    .btn-soft{border:1px solid #dbe3ee;background:#fff;color:#475569;border-radius:12px;padding:10px 16px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
    .btn-soft:hover{background:#f8fafc;color:#0f172a}
    .btn-primary-strong{background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);color:#fff;border:none;border-radius:16px;padding:12px 20px;font-weight:900;display:inline-flex;align-items:center;gap:8px;text-decoration:none;box-shadow:0 12px 28px rgba(37,99,235,.22);transition:all .2s ease;white-space:nowrap}
    .btn-primary-strong:hover{background:linear-gradient(135deg,#1d4ed8 0%,#1e40af 100%);color:#fff;transform:translateY(-1px);box-shadow:0 16px 34px rgba(37,99,235,.28)}
    .form-card{background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:22px;box-shadow:0 10px 30px rgba(15,23,42,.05)}
    .form-card h5{font-weight:800;color:#0f172a;margin-bottom:18px;display:flex;align-items:center;gap:8px}
    .form-label{font-weight:700;font-size:.85rem;color:#475569;margin-bottom:6px}
    .form-control,.form-select{background:#fff;border:1px solid #dbe3ee;color:#0f172a;border-radius:14px;padding:11px 14px;font-size:.92rem}
    .form-control:focus,.form-select:focus{border-color:#2563eb;box-shadow:0 0 0 4px rgba(37,99,235,.12)}
    .item-row{background:#f8fafc;border:1px solid #e2e8f0;border-radius:18px;padding:16px;position:relative;transition:all .2s}
    .item-row:hover{border-color:#cbd5e1;box-shadow:0 10px 25px rgba(15,23,42,.05)}
    .remove-btn{position:absolute;top:10px;right:10px}
    .btn-danger-sm{background:#ef4444;color:#fff;border:none;border-radius:10px;padding:7px 12px;font-size:.82rem;font-weight:700}
    .btn-danger-sm:hover{opacity:.9}
    .btn-success-sm{background:#16a34a;color:#fff;border:none;border-radius:12px;padding:9px 16px;font-size:.88rem;font-weight:800}
    .btn-success-sm:hover{opacity:.9}
    .total-box{background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%);border-radius:18px;padding:18px 20px;color:#fff;text-align:right}
    .total-box .total-label{font-size:.9rem;opacity:.85}
    .total-box .total-value{font-size:1.8rem;font-weight:900}
    .step-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:999px;background:#eff6ff;color:#2563eb;font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
    .section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px}
    .section-head h5{margin:0}
    .alert-custom{border-radius:12px;padding:14px 20px;font-size:.9rem;border:none;display:flex;align-items:center;gap:10px}
    .alert-danger-custom{background:rgba(239,68,68,.1);color:#dc2626}
    .animate-in{animation:fadeInUp .35s ease forwards}
    @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @include('admin.partials.inventory-nav')

    <div class="hero-card mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="hero-title"><i class="bi bi-file-earmark-plus"></i> Tạo Phiếu Nhập Kho</h1>
                <div class="hero-desc">Nhập hàng theo nhà cung cấp, gắn biến thể và sinh IMEI/Serial cho từng thiết bị.</div>
            </div>
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn-soft"><i class="bi bi-arrow-left"></i> Quay lại</a>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="poForm">
        @csrf

        <div class="form-card animate-in mb-4">
            <div class="section-head">
                <h5><i class="bi bi-building"></i> Thông Tin Nhà Cung Cấp</h5>
                <span class="step-chip">Bước 1</span>
            </div>
            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label">Nhà Cung Cấp <span class="text-danger">*</span></label>
                    <select name="supplier_id" class="form-select" required>
                        <option value="">-- Chọn nhà cung cấp --</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->supplier_id }}" {{ old('supplier_id') == $supplier->supplier_id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-card animate-in mb-4">
            <div class="section-head">
                <h5><i class="bi bi-box-seam"></i> Danh Sách Thiết Bị Nhập Kho</h5>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="step-chip">Bước 2</span>
                    <button type="button" class="btn-success-sm" onclick="addItemRow()"><i class="bi bi-plus-lg"></i> Thêm dòng</button>
                </div>
            </div>
            <div id="itemsContainer" class="d-grid gap-3"></div>
            <div class="total-box mt-3">
                <div class="total-label">Tổng tiền nhập</div>
                <div class="total-value" id="totalCost">0₫</div>
            </div>
        </div>

        <div class="d-flex gap-3 justify-content-end mb-4 flex-wrap">
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn-soft"><i class="bi bi-x-lg"></i> Hủy</a>
            <button type="submit" class="btn-primary-strong"><i class="bi bi-check-lg"></i> Lưu Phiếu Nhập</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const products = @json($products);
let rowIndex = 0;

function addItemRow() {
    const container = document.getElementById('itemsContainer');
    const idx = rowIndex++;
    let productOptions = '<option value="">-- Chọn sản phẩm --</option>';
    products.forEach(p => { productOptions += `<option value="${p.product_id}">${p.name} (${Number(p.base_price || 0).toLocaleString('vi-VN')}₫)</option>`; });

    const html = `
    <div class="item-row animate-in" id="row-${idx}">
        <button type="button" class="btn-danger-sm remove-btn" onclick="removeRow(${idx})"><i class="bi bi-trash3"></i></button>
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Sản phẩm <span class="text-danger">*</span></label>
                <select class="form-select" onchange="loadVariants(this, ${idx})" required>
                    ${productOptions}
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Biến thể <span class="text-danger">*</span></label>
                <select class="form-select" name="items[${idx}][variant_id]" id="variant-${idx}" required>
                    <option value="">-- Chọn SP trước --</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">IMEI/Serial <span class="text-danger">*</span></label>
                <input type="text" name="items[${idx}][imei_serial]" class="form-control" placeholder="IMEI..." required maxlength="30">
            </div>
            <div class="col-md-2">
                <label class="form-label">Giá nhập <span class="text-danger">*</span></label>
                <input type="number" name="items[${idx}][cost_price]" class="form-control cost-input" placeholder="0" required min="0" oninput="calcTotal()">
            </div>
            <div class="col-md-3">
                <label class="form-label">Vị trí kho</label>
                <input type="text" name="items[${idx}][warehouse_loc]" class="form-control" placeholder="VD: Kệ A1" maxlength="50">
            </div>
        </div>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
    calcTotal();
}

function removeRow(idx) {
    const row = document.getElementById('row-' + idx);
    if (row) row.remove();
    calcTotal();
}

function loadVariants(select, idx) {
    const productId = select.value;
    const variantSelect = document.getElementById('variant-' + idx);
    variantSelect.innerHTML = '<option value="">Đang tải...</option>';

    if (!productId) {
        variantSelect.innerHTML = '<option value="">-- Chọn SP trước --</option>';
        return;
    }

    const product = products.find(p => String(p.product_id) === String(productId));
    if (product && product.variants && product.variants.length) {
        let opts = '<option value="">-- Chọn biến thể --</option>';
        product.variants.forEach(v => {
            const label = [v.color, v.rom_capacity].filter(Boolean).join(' - ') || 'Mặc định';
            opts += `<option value="${v.variant_id}">${label}</option>`;
        });
        variantSelect.innerHTML = opts;
    } else {
        variantSelect.innerHTML = '<option value="">Không có biến thể</option>';
    }
}

function calcTotal() {
    let total = 0;
    document.querySelectorAll('.cost-input').forEach(input => { total += Number(input.value) || 0; });
    document.getElementById('totalCost').textContent = total.toLocaleString('vi-VN') + '₫';
}

addItemRow();
</script>
@endpush