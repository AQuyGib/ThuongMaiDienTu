<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Quản Lý Flash Sale</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Quản Lý Flash Sale</h1>
            <div class="text-muted">Tạo chương trình, gán sản phẩm và theo dõi số lượng đã bán.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-outline-secondary">Làm mới</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">{{ $editingFlashSale ? 'Cập nhật Flash Sale' : 'Tạo Flash Sale' }}</div>
                <div class="card-body">
                    <form action="{{ $editingFlashSale ? route('admin.flash-sales.update', $editingFlashSale->flash_sale_id) : route('admin.flash-sales.store') }}" method="POST" class="vstack gap-3">
                        @csrf
                        @if($editingFlashSale)
                            @method('PUT')
                        @endif
                        <div>
                            <label class="form-label">Tên chương trình</label>
                            <input type="text" name="name" class="form-control" required maxlength="150" value="{{ $editingFlashSale->name ?? '' }}">
                        </div>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Bắt đầu</label>
                                <input type="datetime-local" name="start_at" class="form-control" required value="{{ isset($editingFlashSale) ? \Carbon\Carbon::parse($editingFlashSale->start_at)->format('Y-m-d\\TH:i') : '' }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Kết thúc</label>
                                <input type="datetime-local" name="end_at" class="form-control" required value="{{ isset($editingFlashSale) ? \Carbon\Carbon::parse($editingFlashSale->end_at)->format('Y-m-d\\TH:i') : '' }}">
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3">{{ $editingFlashSale->description ?? '' }}</textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ !isset($editingFlashSale) || $editingFlashSale->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Kích hoạt</label>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary">{{ $editingFlashSale ? 'Lưu thay đổi' : 'Tạo Flash Sale' }}</button>
                            @if($editingFlashSale)
                                <a href="{{ route('admin.flash-sales.index') }}" class="btn btn-outline-secondary">Hủy</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white fw-bold">Danh sách Flash Sale</div>
                <div class="card-body table-responsive">
                    <table class="table align-middle">
                        <thead>
                        <tr>
                            <th>Tên</th><th>Thời gian</th><th>Trạng thái</th><th>Sản phẩm</th><th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($flashSales as $flashSale)
                            <tr>
                                <td class="fw-semibold">{{ $flashSale->name }}</td>
                                <td class="text-muted small">{{ $flashSale->start_at }}<br>{{ $flashSale->end_at }}</td>
                                <td>
                                    <span class="badge {{ $flashSale->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $flashSale->is_active ? 'Đang bật' : 'Tắt' }}
                                    </span>
                                </td>
                                <td>{{ $flashSale->products_count ?? 0 }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.flash-sales.index', ['edit' => $flashSale->flash_sale_id]) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                    <form action="{{ route('admin.flash-sales.destroy', $flashSale->flash_sale_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa Flash Sale này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Chưa có Flash Sale nào.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                    {{ $flashSales->links() }}
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Sản phẩm trong Flash Sale mới nhất</div>
                <div class="card-body">
                    @php($currentFlashSale = $flashSales->first())
                    @if($currentFlashSale)
                        <form action="{{ route('admin.flash-sales.products.store', $currentFlashSale->flash_sale_id) }}" method="POST" class="row g-3 mb-4">
                            @csrf
                            <div class="col-md-6">
                                <label class="form-label">Sản phẩm</label>
                                <select name="product_id" class="form-select" required>
                                    <option value="">-- Chọn --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->product_id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Giá sale</label>
                                <input type="number" name="sale_price" class="form-control" required min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Giới hạn</label>
                                <input type="number" name="stock_limit" class="form-control" required min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Thứ tự</label>
                                <input type="number" name="sort_order" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button class="btn btn-success w-100">Gán sản phẩm</button>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead>
                                <tr>
                                    <th>Sản phẩm</th><th>Giá sale</th><th>Giới hạn</th><th>Đã bán</th><th>Trạng thái</th><th></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($currentFlashSale->products as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($item->sale_price, 0, ',', '.') }} ₫</td>
                                        <td>{{ $item->stock_limit }}</td>
                                        <td>{{ $item->sold_quantity }}</td>
                                        <td>{{ $item->is_active ? 'Bật' : 'Tắt' }}</td>
                                        <td>
                                            <form action="{{ route('admin.flash-sales.products.destroy', [$currentFlashSale->flash_sale_id, $item->flash_sale_product_id]) }}" method="POST" onsubmit="return confirm('Gỡ sản phẩm khỏi Flash Sale?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">Gỡ</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Chưa có sản phẩm nào.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Chưa có Flash Sale nào để gán sản phẩm.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
