@extends('admin.layouts.master')

@section('title', 'Lập Phiếu Điều Chuyển')

@push('styles')
<style>
    .form-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(15,23,42,.05);
    }
    .form-label {
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }
    .form-control, .form-select {
        border-radius: 12px;
        border: 1px solid #dbe3ee;
        padding: 10px 14px;
        font-size: .95rem;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37,99,235,.12);
    }
    .imei-selector-card {
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        margin-top: 14px;
    }
    .imei-selector-header {
        background: #f8fafc;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .imei-list-scroll {
        max-height: 350px;
        overflow-y: auto;
        padding: 8px;
    }
    .imei-item-row {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        border-bottom: 1px solid #f1f5f9;
        transition: background .15s;
        border-radius: 8px;
    }
    .imei-item-row:hover {
        background: #f8fafc;
    }
    .imei-item-row:last-child {
        border-bottom: none;
    }
    .imei-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .imei-info {
        flex: 1;
        margin-left: 12px;
    }
    .imei-info-title {
        font-weight: 700;
        color: #1e293b;
        font-size: .92rem;
    }
    .imei-info-sub {
        font-size: .82rem;
        color: #64748b;
    }
    .imei-badge {
        font-family: monospace;
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: .85rem;
        font-weight: 700;
        color: #334155;
    }
    .btn-submit {
        border-radius: 12px;
        padding: 10px 24px;
        font-weight: 700;
        font-size: .95rem;
    }
    .empty-selector-state {
        text-align: center;
        padding: 40px 20px;
        color: #94a3b8;
    }
    .empty-selector-state i {
        font-size: 2.5rem;
        margin-bottom: 8px;
        opacity: .5;
    }

    /* Button styling fallback */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 12px;
        border: 1px solid transparent;
        transition: all 0.2s ease-in-out;
        cursor: pointer;
        text-decoration: none;
    }
    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
        border-radius: 8px;
    }
    .btn-primary {
        background-color: #2563eb;
        color: #ffffff !important;
        border-color: #2563eb;
    }
    .btn-primary:hover {
        background-color: #1d4ed8;
        border-color: #1d4ed8;
    }
    .btn-success {
        background-color: #16a34a;
        color: #ffffff !important;
        border-color: #16a34a;
    }
    .btn-success:hover {
        background-color: #15803d;
        border-color: #15803d;
    }
    .btn-warning {
        background-color: #eab308;
        color: #1e293b !important;
        border-color: #eab308;
    }
    .btn-warning:hover {
        background-color: #ca8a04;
        border-color: #ca8a04;
    }
    .btn-danger {
        background-color: #dc2626;
        color: #ffffff !important;
        border-color: #dc2626;
    }
    .btn-danger:hover {
        background-color: #b91c1c;
        border-color: #b91c1c;
    }
    .btn-secondary {
        background-color: #64748b;
        color: #ffffff !important;
        border-color: #64748b;
    }
    .btn-secondary:hover {
        background-color: #475569;
        border-color: #475569;
    }
    .btn-outline-secondary {
        background-color: transparent;
        color: #475569 !important;
        border-color: #cbd5e1;
    }
    .btn-outline-secondary:hover {
        background-color: #f8fafc;
        color: #1e293b !important;
        border-color: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" style="max-width: 960px;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="mb-0" style="font-weight: 800; color: #0f172a;">
            <i class="bi bi-file-earmark-plus-fill text-primary me-2"></i> Lập Phiếu Điều Chuyển Kho
        </h4>
        <a href="{{ route('admin.warehouse-transfers.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px; font-weight:600;">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <h6 style="font-weight:700;"><i class="bi bi-exclamation-triangle-fill me-2"></i>Có lỗi xảy ra:</h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-card">
        <form action="{{ route('admin.warehouse-transfers.store') }}" method="POST" id="transferForm">
            @csrf
            
            <div class="row g-3">
                {{-- Kho đi --}}
                <div class="col-md-6">
                    <label for="from_warehouse" class="form-label">Kho Nguồn (Kho đi) <span class="text-danger">*</span></label>
                    <select name="from_warehouse" id="from_warehouse" class="form-select" required>
                        <option value="">— Chọn kho nguồn —</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh }}" {{ old('from_warehouse') == $wh ? 'selected' : '' }}>{{ $wh }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Kho đến --}}
                <div class="col-md-6">
                    <label for="to_warehouse" class="form-label">Kho Đích (Kho đến) <span class="text-danger">*</span></label>
                    <select name="to_warehouse" id="to_warehouse" class="form-select" required>
                        <option value="">— Chọn kho đích —</option>
                        @foreach($warehouses as $wh)
                            <option value="{{ $wh }}" {{ old('to_warehouse') == $wh ? 'selected' : '' }}>{{ $wh }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Ghi chú --}}
                <div class="col-12">
                    <label for="notes" class="form-label">Ghi Chú Phiếu</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Nhập lý do điều chuyển hoặc ghi chú thêm..." maxlength="255">{{ old('notes') }}</textarea>
                </div>

                {{-- Chọn IMEI --}}
                <div class="col-12">
                    <label class="form-label">Chọn IMEI Điều Chuyển <span class="text-danger">*</span></label>
                    <div class="imei-selector-card">
                        <div class="imei-selector-header">
                            <div class="d-flex align-items-center gap-2">
                                <input type="checkbox" id="check_all_imei" class="imei-checkbox" disabled>
                                <label for="check_all_imei" class="mb-0" style="font-weight:700; cursor:pointer;">Chọn Tất Cả (<span id="selected_count">0</span> đã chọn)</label>
                            </div>
                            <div style="max-width: 250px; width: 100%;">
                                <input type="text" id="search_imei" class="form-control form-control-sm" placeholder="Lọc theo IMEI, tên sản phẩm..." disabled>
                            </div>
                        </div>

                        <div class="imei-list-scroll" id="imei_list_container">
                            <div class="empty-selector-state">
                                <i class="bi bi-building-fill-exclamation d-block"></i>
                                <span>Vui lòng chọn Kho Nguồn để tải danh sách IMEI khả dụng.</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Loại hành động lưu --}}
                <input type="hidden" name="action_type" id="action_type" value="Pending">

                {{-- Các nút bấm --}}
                <div class="col-12 d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-warning text-dark btn-submit" onclick="submitForm('Pending')">
                        <i class="bi bi-clock-history"></i> Lưu Chờ Xử Lý
                    </button>
                    <button type="button" class="btn btn-success btn-submit" onclick="submitForm('Completed')">
                        <i class="bi bi-check-circle-fill"></i> Hoàn Thành Điều Chuyển
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var fromWhSelect = document.getElementById('from_warehouse');
    var checkAllCheckbox = document.getElementById('check_all_imei');
    var searchInput = document.getElementById('search_imei');
    var imeiContainer = document.getElementById('imei_list_container');
    var selectedCountLabel = document.getElementById('selected_count');
    var form = document.getElementById('transferForm');
    var actionTypeInput = document.getElementById('action_type');

    var allItems = []; // Lưu trữ tất cả IMEI tải về từ server
    var selectedIds = new Set(); // Lưu các ID đã tích chọn

    // Theo dõi sự kiện thay đổi kho đi
    fromWhSelect.addEventListener('change', function() {
        const selectedWh = this.value;
        selectedIds.clear();
        updateSelectedCount();

        if (!selectedWh) {
            checkAllCheckbox.disabled = true;
            checkAllCheckbox.checked = false;
            searchInput.disabled = true;
            searchInput.value = '';
            imeiContainer.innerHTML = `
                <div class="empty-selector-state">
                    <i class="bi bi-building-fill-exclamation d-block"></i>
                    <span>Vui lòng chọn Kho Nguồn để tải danh sách IMEI khả dụng.</span>
                </div>
            `;
            allItems = [];
            return;
        }

        // Hiện trạng thái đang tải
        imeiContainer.innerHTML = `
            <div class="empty-selector-state">
                <div class="spinner-border text-primary mb-2" role="status"></div>
                <div>Đang tải danh sách IMEI khả dụng...</div>
            </div>
        `;

        fetch(`{{ route('admin.api.inventory-by-warehouse') }}?warehouse=${encodeURIComponent(selectedWh)}`)
            .then(res => res.json())
            .then(data => {
                allItems = data;
                renderImeiList(allItems);

                if (allItems.length > 0) {
                    checkAllCheckbox.disabled = false;
                    searchInput.disabled = false;
                } else {
                    checkAllCheckbox.disabled = true;
                    checkAllCheckbox.checked = false;
                    searchInput.disabled = true;
                }
            })
            .catch(err => {
                console.error(err);
                imeiContainer.innerHTML = `
                    <div class="empty-selector-state text-danger">
                        <i class="bi bi-x-circle d-block"></i>
                        <span>Có lỗi xảy ra khi tải dữ liệu từ máy chủ.</span>
                    </div>
                `;
            });
    });

    // Hàm render danh sách IMEI
    function renderImeiList(items) {
        if (items.length === 0) {
            imeiContainer.innerHTML = `
                <div class="empty-selector-state">
                    <i class="bi bi-inbox d-block"></i>
                    <span>Không có sản phẩm nào trong kho nguồn có trạng thái "Còn trong kho".</span>
                </div>
            `;
            return;
        }

        let html = '';
        items.forEach(item => {
            const isChecked = selectedIds.has(item.item_id) ? 'checked' : '';
            html += `
                <div class="imei-item-row" data-search="${(item.product_name + ' ' + item.variant_name + ' ' + item.imei_serial).toLowerCase()}">
                    <input type="checkbox" name="item_ids[]" value="${item.item_id}" class="imei-checkbox item-select-checkbox" ${isChecked} onchange="toggleItem(${item.item_id}, this.checked)">
                    <div class="imei-info">
                        <div class="imei-info-title">${escapeHtml(item.product_name)}</div>
                        <div class="imei-info-sub">Biến thể: <span class="badge bg-light text-dark border">${escapeHtml(item.variant_name)}</span></div>
                    </div>
                    <div>
                        <span class="imei-badge">${escapeHtml(item.imei_serial)}</span>
                    </div>
                </div>
            `;
        });
        imeiContainer.innerHTML = html;
        updateCheckAllState();
    }

    // Escape HTML to prevent XSS
    function escapeHtml(str) {
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Toggle lựa chọn từng phần tử
    function toggleItem(id, isChecked) {
        if (isChecked) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        updateSelectedCount();
        updateCheckAllState();
    }

    // Cập nhật số lượng item đã chọn hiển thị trên UI
    function updateSelectedCount() {
        selectedCountLabel.textContent = selectedIds.size;
    }

    // Cập nhật trạng thái checkbox check_all
    function updateCheckAllState() {
        const visibleCheckboxes = document.querySelectorAll('.item-select-checkbox');
        if (visibleCheckboxes.length === 0) {
            checkAllCheckbox.checked = false;
            return;
        }

        let allChecked = true;
        visibleCheckboxes.forEach(cb => {
            if (!cb.checked) {
                allChecked = false;
            }
        });
        checkAllCheckbox.checked = allChecked;
    }

    // Sự kiện Click checkbox Chọn tất cả
    checkAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        const filteredRows = Array.from(document.querySelectorAll('.imei-item-row')).filter(row => row.style.display !== 'none');
        
        filteredRows.forEach(row => {
            const cb = row.querySelector('.item-select-checkbox');
            cb.checked = isChecked;
            const itemId = parseInt(cb.value);
            if (isChecked) {
                selectedIds.add(itemId);
            } else {
                selectedIds.delete(itemId);
            }
        });

        updateSelectedCount();
    });

    // Sự kiện tìm kiếm lọc danh sách
    searchInput.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll('.imei-item-row');

        rows.forEach(row => {
            const text = row.getAttribute('data-search');
            if (text.includes(query)) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });

        updateCheckAllState();
    });

    // Submit form theo hành động mong muốn
    function submitForm(statusType) {
        if (selectedIds.size === 0) {
            alert('Vui lòng chọn ít nhất một IMEI để điều chuyển hàng hóa.');
            return;
        }

        const toWh = document.getElementById('to_warehouse').value;
        const fromWh = document.getElementById('from_warehouse').value;

        if (toWh === fromWh) {
            alert('Kho đích và kho nguồn phải khác nhau.');
            return;
        }

        actionTypeInput.value = statusType;
        form.submit();
    }
</script>
@endsection
