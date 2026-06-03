@extends('admin.layouts.master')

@section('title', 'Tạo Phiếu Kiểm Kho')

@push('styles')
<style>
    .form-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        padding: 24px;
    }
    .form-control-custom, .form-select-custom {
        width: 100%;
        background: #fff;
        border: 1px solid #dbe3ee;
        color: #0f172a;
        border-radius: 12px;
        padding: 10px 14px;
        font-size: .9rem;
        transition: all .2s;
    }
    .form-control-custom:focus, .form-select-custom:focus {
        outline: none;
        border-color: #0ea5e9;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, .12);
    }
    .btn-action-primary {
        background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 10px 24px !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        box-shadow: 0 6px 14px rgba(14, 165, 233, 0.2) !important;
        transition: all 0.2s ease !important;
    }
    .btn-action-primary:hover {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 8px 18px rgba(14, 165, 233, 0.28) !important;
        color: #ffffff !important;
    }
    .btn-add-row {
        background-color: #f0fdf4 !important;
        color: #16a34a !important;
        border: 1px dashed #bbf7d0 !important;
        border-radius: 12px !important;
        padding: 8px 16px !important;
        font-weight: 700 !important;
        font-size: .85rem !important;
        transition: all .2s !important;
    }
    .btn-add-row:hover {
        background-color: #dcfce7 !important;
        border-color: #86efac !important;
    }
    .table-custom { margin: 0; width: 100% }
    .table-custom thead th {
        background: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: .78rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: 14px 18px;
        border: none;
    }
    .table-custom tbody td {
        padding: 12px 18px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px) }
        to { opacity: 1; transform: translateY(0) }
    }
    .animate-in { animation: fadeInUp .35s ease forwards }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 animate-in">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}" class="text-decoration-none">Kho hàng</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.inventory-audits.index') }}" class="text-decoration-none">Kiểm kê</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tạo phiếu</li>
                </ol>
            </nav>
            <h1 class="h3 font-weight-bold text-slate-800 m-0"><i class="bi bi-file-earmark-plus me-1 text-primary"></i> Tạo Phiếu Kiểm Kho Mới</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <h6 class="font-weight-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i> Có lỗi xảy ra:</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.inventory-audits.store') }}">
        @csrf

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="form-card mb-4">
                    <h5 class="font-weight-bold mb-4 text-slate-800"><i class="bi bi-info-circle me-1 text-primary"></i> Thông tin phiếu kiểm</h5>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Kho cần kiểm kê <span class="text-danger">*</span></label>
                        <select name="warehouse_loc" class="form-select-custom" required>
                            <option value="">-- Chọn vị trí kho --</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse }}" {{ old('warehouse_loc') == $warehouse ? 'selected' : '' }}>
                                    {{ $warehouse }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-muted fw-bold">Ghi chú phiếu</label>
                        <textarea name="notes" rows="4" placeholder="Nhập lý do kiểm kho, mô tả đợt kiểm kê..." class="form-control-custom">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn-action-primary w-100"><i class="bi bi-save me-1"></i> Lưu và Chờ duyệt</button>
                    <a href="{{ route('admin.inventory-audits.index') }}" class="btn btn-light rounded-3 py-2 fw-bold text-muted">Quay lại danh sách</a>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="form-card">
                    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                        <h5 class="font-weight-bold m-0 text-slate-800"><i class="bi bi-list-check me-1 text-primary"></i> Danh sách mặt hàng kiểm kê</h5>
                        <button type="button" class="btn-add-row" id="add-row-btn">
                            <i class="bi bi-plus-lg"></i> Thêm mặt hàng
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-custom" id="audit-items-table">
                            <thead>
                                <tr>
                                    <th>Sản Phẩm</th>
                                    <th>Biến Thể</th>
                                    <th style="width: 140px;">SL Thực Tế</th>
                                    <th>Ghi Chú Mặt Hàng</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="empty-row-msg">
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-plus-circle d-block mb-2" style="font-size: 2rem; opacity: .4;"></i>
                                        Chưa có mặt hàng nào. Nhấn "Thêm mặt hàng" để bắt đầu nhập.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Đối tượng JSON chứa sản phẩm và biến thể để JS xử lý đổi dropdown --}}
<script>
    const productsData = {!! json_encode($products) !!};
</script>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.querySelector('#audit-items-table tbody');
        const addRowBtn = document.getElementById('add-row-btn');
        const emptyRowMsg = document.querySelector('.empty-row-msg');
        let rowIndex = 0;

        function updateEmptyState() {
            const rows = tableBody.querySelectorAll('tr:not(.empty-row-msg)');
            if (rows.length === 0) {
                emptyRowMsg.style.display = 'table-row';
            } else {
                emptyRowMsg.style.display = 'none';
            }
        }

        addRowBtn.addEventListener('click', function() {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <select class="form-select-custom product-select" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        \${productsData.map(p => `<option value="\${p.product_id}">\${p.name}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <select name="items[\${rowIndex}][variant_id]" class="form-select-custom variant-select" required disabled>
                        <option value="">-- Chọn biến thể --</option>
                    </select>
                </td>
                <td>
                    <input type="number" name="items[\${rowIndex}][actual_qty]" class="form-control-custom text-center" min="0" required placeholder="0">
                </td>
                <td>
                    <input type="text" name="items[\${rowIndex}][notes]" class="form-control-custom" placeholder="Ghi chú lẻ...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-3 remove-row-btn" style="border:none">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            `;

            tableBody.appendChild(tr);
            rowIndex++;
            updateEmptyState();

            // Lắng nghe sự kiện thay đổi sản phẩm để load biến thể tương ứng
            const productSelect = tr.querySelector('.product-select');
            const variantSelect = tr.querySelector('.variant-select');

            productSelect.addEventListener('change', function() {
                const productId = parseInt(this.value);
                variantSelect.innerHTML = '<option value="">-- Chọn biến thể --</option>';

                if (productId) {
                    const productObj = productsData.find(p => p.product_id === productId);
                    if (productObj && productObj.variants && productObj.variants.length > 0) {
                        productObj.variants.forEach(v => {
                            const desc = [v.color, v.rom_capacity].filter(Boolean).join(' - ') || 'Mặc định';
                            variantSelect.innerHTML += `<option value="\${v.variant_id}">\${desc}</option>`;
                        });
                        variantSelect.disabled = false;
                    } else {
                        // Trường hợp sản phẩm không có biến thể
                        variantSelect.innerHTML = '<option value="">-- Không có biến thể --</option>';
                        variantSelect.disabled = true;
                    }
                } else {
                    variantSelect.disabled = true;
                }
            });

            // Lắng nghe xóa dòng
            tr.querySelector('.remove-row-btn').addEventListener('click', function() {
                tr.remove();
                updateEmptyState();
            });
        });
    });
</script>
@endpush
