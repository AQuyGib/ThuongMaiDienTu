<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tạo Phiếu Nhập Kho</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{--bg-primary:#0f1117;--bg-secondary:#1a1d27;--bg-card:#1e2231;--bg-hover:#262a3a;--accent:#6c5ce7;--accent-hover:#7f71ed;--accent-glow:rgba(108,92,231,.25);--text-primary:#e8e8ef;--text-secondary:#9ca3b4;--border:#2d3148;--danger:#e74c5e;--success:#2ed573;}
        *{box-sizing:border-box}body{font-family:'Inter',sans-serif;background:var(--bg-primary);color:var(--text-primary);min-height:100vh;margin:0}
        .page-header{background:linear-gradient(135deg,var(--bg-secondary),var(--bg-card));border-bottom:1px solid var(--border);padding:28px 0}.page-header h1{font-size:1.75rem;font-weight:700;margin:0;display:flex;align-items:center;gap:12px}.page-header h1 i{color:var(--accent)}
        .form-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:24px;margin-bottom:20px}
        .form-card h5{font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:8px}.form-card h5 i{color:var(--accent)}
        .form-label{font-weight:500;font-size:.875rem;color:var(--text-secondary);margin-bottom:6px}
        .form-control,.form-select{background:var(--bg-primary);border:1px solid var(--border);color:var(--text-primary);border-radius:10px;padding:10px 14px;font-size:.9rem}
        .form-control:focus,.form-select:focus{background:var(--bg-primary);color:var(--text-primary);border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow)}
        .form-select option{background:var(--bg-primary);color:var(--text-primary)}
        .btn-accent{background:var(--accent);color:#fff;border:none;border-radius:10px;padding:9px 20px;font-weight:600;font-size:.875rem;transition:all .25s;display:inline-flex;align-items:center;gap:6px;text-decoration:none}.btn-accent:hover{background:var(--accent-hover);color:#fff}
        .btn-cancel{background:var(--bg-secondary);color:var(--text-secondary);border:1px solid var(--border);border-radius:10px;padding:9px 20px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;gap:6px}.btn-cancel:hover{background:var(--bg-hover);color:var(--text-primary)}
        .btn-danger-sm{background:var(--danger);color:#fff;border:none;border-radius:8px;padding:6px 12px;font-size:.8rem;cursor:pointer;transition:all .2s}.btn-danger-sm:hover{opacity:.8}
        .btn-success-sm{background:var(--success);color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:.85rem;cursor:pointer;transition:all .2s}.btn-success-sm:hover{opacity:.8}
        .item-row{background:var(--bg-primary);border:1px solid var(--border);border-radius:12px;padding:16px;margin-bottom:12px;position:relative;transition:border-color .2s}.item-row:hover{border-color:var(--accent)}
        .item-row .remove-btn{position:absolute;top:8px;right:8px}
        .total-box{background:linear-gradient(135deg,var(--accent),#8b7cf7);border-radius:12px;padding:20px;color:#fff;text-align:right}.total-box .total-label{font-size:.9rem;opacity:.8}.total-box .total-value{font-size:1.8rem;font-weight:800}
        .alert-custom{border-radius:12px;padding:14px 20px;font-size:.9rem;border:none;display:flex;align-items:center;gap:10px}.alert-danger-custom{background:rgba(231,76,94,.1);color:var(--danger)}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}.animate-in{animation:fadeInUp .4s ease forwards}
    </style>
</head>
<body class="flex h-screen overflow-hidden">
    @include('admin.partials.sidebar')
    <div class="flex-1 overflow-y-auto w-full">
        <div class="page-header"><div class="container"><div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h1><i class="bi bi-file-earmark-plus"></i> Tạo Phiếu Nhập Kho</h1>
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-cancel"><i class="bi bi-arrow-left"></i> Quay lại</a>
        </div></div></div>

        <div class="container py-4">
            @if(session('error'))<div class="alert alert-custom alert-danger-custom animate-in"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>@endif
            @if($errors->any())<div class="alert alert-custom alert-danger-custom animate-in"><i class="bi bi-exclamation-triangle-fill"></i><div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div></div>@endif

            <form action="{{ route('admin.purchase-orders.store') }}" method="POST" id="poForm">
                @csrf
                {{-- Chọn NCC --}}
                <div class="form-card animate-in">
                    <h5><i class="bi bi-building"></i> Thông Tin Nhà Cung Cấp</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nhà Cung Cấp <span style="color:var(--danger)">*</span></label>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">-- Chọn nhà cung cấp --</option>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->supplier_id }}" {{ old('supplier_id') == $s->supplier_id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Danh sách sản phẩm nhập --}}
                <div class="form-card animate-in">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Sản Phẩm Nhập Kho</h5>
                        <button type="button" class="btn-success-sm" onclick="addItemRow()"><i class="bi bi-plus-lg"></i> Thêm dòng</button>
                    </div>
                    <div id="itemsContainer">
                        {{-- Dòng mẫu sẽ được JS tạo --}}
                    </div>
                    <div class="total-box mt-3">
                        <div class="total-label">Tổng tiền nhập</div>
                        <div class="total-value" id="totalCost">0₫</div>
                    </div>
                </div>

                <div class="d-flex gap-3 justify-content-end mb-4">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-cancel"><i class="bi bi-x-lg"></i> Hủy</a>
                    <button type="submit" class="btn btn-accent" style="padding:12px 30px;font-size:1rem;"><i class="bi bi-check-lg"></i> Lưu Phiếu Nhập</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const products = @json($products);
    let rowIndex = 0;

    function addItemRow() {
        const container = document.getElementById('itemsContainer');
        const idx = rowIndex++;
        let productOptions = '<option value="">-- Chọn sản phẩm --</option>';
        products.forEach(p => { productOptions += `<option value="${p.product_id}">${p.name} (${Number(p.base_price).toLocaleString('vi')}₫)</option>`; });

        const html = `
        <div class="item-row" id="row-${idx}">
            <button type="button" class="btn-danger-sm remove-btn" onclick="removeRow(${idx})"><i class="bi bi-trash3"></i></button>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Sản phẩm <span style="color:#e74c5e">*</span></label>
                    <select class="form-select" onchange="loadVariants(this, ${idx})" required>
                        ${productOptions}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Biến thể</label>
                    <select class="form-select" name="items[${idx}][variant_id]" id="variant-${idx}" required>
                        <option value="">-- Chọn SP trước --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">IMEI/Serial <span style="color:#e74c5e">*</span></label>
                    <input type="text" name="items[${idx}][imei_serial]" class="form-control" placeholder="IMEI..." required maxlength="30">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Giá nhập <span style="color:#e74c5e">*</span></label>
                    <input type="number" name="items[${idx}][cost_price]" class="form-control cost-input" placeholder="0" required min="0" onchange="calcTotal()" oninput="calcTotal()">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Vị trí kho</label>
                    <input type="text" name="items[${idx}][warehouse_loc]" class="form-control" placeholder="VD: Kệ A1" maxlength="50">
                </div>
            </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
    }

    function removeRow(idx) {
        const row = document.getElementById('row-' + idx);
        if (row) { row.remove(); calcTotal(); }
    }

    function loadVariants(select, idx) {
        const productId = select.value;
        const variantSelect = document.getElementById('variant-' + idx);
        variantSelect.innerHTML = '<option value="">Đang tải...</option>';

        if (!productId) { variantSelect.innerHTML = '<option value="">-- Chọn SP trước --</option>'; return; }

        const product = products.find(p => p.product_id == productId);
        if (product && product.variants && product.variants.length > 0) {
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
        document.getElementById('totalCost').textContent = total.toLocaleString('vi') + '₫';
    }

    // Thêm 1 dòng mặc định khi load trang
    addItemRow();

    function toggleSidebar(){const s=document.getElementById('sidebar');if(s)s.classList.toggle('-translate-x-full');}
    </script>
</body>
</html>
