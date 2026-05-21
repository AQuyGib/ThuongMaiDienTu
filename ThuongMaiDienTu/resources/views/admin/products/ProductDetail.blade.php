<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chi Tiết: {{ $product->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root{--bg-primary:#0f1117;--bg-secondary:#1a1d27;--bg-card:#1e2231;--bg-hover:#262a3a;--accent:#6c5ce7;--accent-hover:#7f71ed;--accent-glow:rgba(108,92,231,0.25);--text-primary:#e8e8ef;--text-secondary:#9ca3b4;--border:#2d3148;--danger:#e74c5e;--danger-hover:#d4364a;--success:#2ed573;--warning:#ffa502}
        *{box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:var(--bg-primary);color:var(--text-primary);min-height:100vh;margin:0}
        .page-header{background:linear-gradient(135deg,var(--bg-secondary) 0%,var(--bg-card) 100%);border-bottom:1px solid var(--border);padding:28px 0}
        .page-header h1{font-size:1.75rem;font-weight:700;margin:0;display:flex;align-items:center;gap:12px}
        .page-header h1 i{font-size:1.5rem;color:var(--accent)}
        .btn-back{background:var(--bg-secondary);color:var(--text-secondary);border:1px solid var(--border);border-radius:10px;padding:8px 18px;font-weight:500;font-size:.875rem;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
        .btn-back:hover{background:var(--bg-hover);color:var(--text-primary);border-color:var(--accent)}
        .info-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:24px}
        .info-card h5{font-weight:700;font-size:1.1rem;margin-bottom:20px;display:flex;align-items:center;gap:10px}
        .info-card h5 i{color:var(--accent)}
        .info-row{display:flex;padding:12px 0;border-bottom:1px solid var(--border)}
        .info-row:last-child{border-bottom:none}
        .info-label{width:160px;color:var(--text-secondary);font-size:.875rem;font-weight:500;flex-shrink:0}
        .info-value{font-size:.9rem;font-weight:500}
        .badge-category{background:rgba(108,92,231,.15);color:var(--accent);font-weight:500;padding:5px 12px;border-radius:8px;font-size:.8rem}
        .price-tag{color:var(--success);font-weight:700;font-size:1.1rem}
        .table-card{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;overflow:hidden}
        .table-card-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
        .table-card-header h5{margin:0;font-weight:600;font-size:1.05rem}
        .table-custom{margin:0;width:100%}
        .table-custom thead th{background:var(--bg-secondary);color:var(--text-secondary);font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;padding:14px 16px;border:none;white-space:nowrap}
        .table-custom tbody td{padding:14px 16px;border-bottom:1px solid var(--border);vertical-align:middle;font-size:.875rem}
        .table-custom tbody tr{transition:background .2s}
        .table-custom tbody tr:hover{background:var(--bg-hover)}
        .table-custom tbody tr:last-child td{border-bottom:none}
        .btn-accent{background:var(--accent);color:#fff;border:none;border-radius:10px;padding:9px 20px;font-weight:600;font-size:.875rem;transition:all .25s;display:inline-flex;align-items:center;gap:6px}
        .btn-accent:hover{background:var(--accent-hover);color:#fff;transform:translateY(-1px);box-shadow:0 4px 18px var(--accent-glow)}
        .btn-action{width:34px;height:34px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--border);background:var(--bg-secondary);color:var(--text-secondary);transition:all .2s;font-size:.85rem;cursor:pointer}
        .btn-action.edit:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
        .btn-action.delete:hover{background:var(--danger);color:#fff;border-color:var(--danger)}
        .modal-content{background:var(--bg-card);border:1px solid var(--border);border-radius:16px;color:var(--text-primary)}
        .modal-header{border-bottom:1px solid var(--border);padding:20px 24px}
        .modal-header .modal-title{font-weight:700;font-size:1.1rem}
        .modal-header .btn-close{filter:invert(1) grayscale(100%) brightness(200%)}
        .modal-body{padding:24px}
        .modal-footer{border-top:1px solid var(--border);padding:16px 24px}
        .form-label{font-weight:500;font-size:.875rem;color:var(--text-secondary);margin-bottom:6px}
        .form-control,.form-select{background:var(--bg-primary);border:1px solid var(--border);color:var(--text-primary);border-radius:10px;padding:10px 14px;font-size:.9rem;transition:border-color .2s,box-shadow .2s}
        .form-control:focus,.form-select:focus{background:var(--bg-primary);color:var(--text-primary);border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow)}
        .btn-cancel{background:var(--bg-secondary);color:var(--text-secondary);border:1px solid var(--border);border-radius:10px;padding:9px 20px;font-weight:500;transition:all .2s}
        .btn-cancel:hover{background:var(--bg-hover);color:var(--text-primary)}
        .alert-custom{border-radius:12px;padding:14px 20px;font-size:.9rem;border:none;display:flex;align-items:center;gap:10px}
        .alert-success-custom{background:rgba(46,213,115,.1);color:var(--success)}
        .alert-danger-custom{background:rgba(231,76,94,.1);color:var(--danger)}
        .empty-state{text-align:center;padding:50px 20px;color:var(--text-secondary)}
        .empty-state i{font-size:2.5rem;margin-bottom:12px;opacity:.4}
        .color-dot{width:22px;height:22px;border-radius:50%;border:2px solid var(--border);display:inline-block;vertical-align:middle}
        .variant-img{width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid var(--border)}
        .extra-price{color:var(--warning);font-weight:600}
        .total-price{color:var(--success);font-weight:700}
        .img-preview{max-width:100%;max-height:120px;border-radius:8px;margin-top:8px;display:none;border:1px solid var(--border)}
        .upload-tabs{display:flex;gap:0;margin-bottom:14px;border-radius:10px;overflow:hidden;border:1px solid var(--border)}
        .upload-tab{flex:1;padding:9px 14px;text-align:center;font-size:.82rem;font-weight:600;cursor:pointer;background:var(--bg-primary);color:var(--text-secondary);transition:all .2s;border:none;outline:none}
        .upload-tab.active{background:var(--accent);color:#fff}
        .upload-tab:first-child{border-right:1px solid var(--border)}
        .upload-pane{display:none}
        .upload-pane.active{display:block}
        .drop-zone{border:2px dashed var(--border);border-radius:12px;padding:24px;text-align:center;cursor:pointer;transition:all .25s;background:var(--bg-primary);position:relative}
        .drop-zone:hover,.drop-zone.drag-over{border-color:var(--accent);background:rgba(108,92,231,.06)}
        .drop-zone input[type=file]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
        .drop-zone .drop-icon{font-size:2rem;color:var(--accent);margin-bottom:6px}
        .drop-zone .drop-text{font-size:.85rem;color:var(--text-secondary)}
        .drop-zone .drop-text strong{color:var(--accent)}
        .file-info{display:flex;align-items:center;gap:10px;background:var(--bg-primary);border:1px solid var(--border);border-radius:10px;padding:10px 14px;margin-top:10px}
        .file-info .file-name{font-size:.85rem;font-weight:500;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .file-info .file-size{font-size:.78rem;color:var(--text-secondary)}
        .file-info .file-remove{width:28px;height:28px;border-radius:6px;border:none;background:var(--danger);color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:.8rem;transition:background .2s}
        .file-info .file-remove:hover{background:var(--danger-hover)}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
        .animate-in{animation:fadeInUp .4s ease forwards}
    </style>
</head>
<body>
    {{-- ===== HEADER ===== --}}
    <div class="page-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h1>
                    <i class="bi bi-box-seam-fill"></i>
                    {{ $product->name }}
                </h1>
                <a href="{{ route('admin.products.index') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </a>
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
        @if($errors->any())
            <div class="alert alert-custom alert-danger-custom animate-in">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
            </div>
        @endif

        {{-- Thông tin sản phẩm --}}
        <div class="info-card animate-in">
            <h5><i class="bi bi-info-circle-fill"></i> Thông Tin Sản Phẩm</h5>
            <div class="info-row">
                <div class="info-label">Mã SP</div>
                <div class="info-value">#{{ $product->product_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tên sản phẩm</div>
                <div class="info-value">{{ $product->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Danh mục</div>
                <div class="info-value">
                    @if($product->category)
                        <span class="badge-category">{{ $product->category->name }}</span>
                    @else <span style="color:var(--text-secondary)">—</span>
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Giá gốc</div>
                <div class="info-value"><span class="price-tag">{{ number_format($product->base_price, 0, ',', '.') }}₫</span></div>
            </div>
            @if($product->seo_description)
            <div class="info-row">
                <div class="info-label">Mô tả SEO</div>
                <div class="info-value" style="color:var(--text-secondary)">{{ $product->seo_description }}</div>
            </div>
            @endif
        </div>
        @php
            $catName = $product->category->name ?? '';
            $variantMode = match (true) {
                str_contains($catName, 'Laptop') || str_contains($catName, 'MacBook') => 'laptop',
                str_contains($catName, 'Điện thoại') || in_array($catName, ['iPhone', 'Samsung', 'Xiaomi', 'OPPO'], true) => 'phone',
                str_contains($catName, 'Tablet') || in_array($catName, ['iPad', 'Samsung Galaxy Tab'], true) => 'tablet',
                str_contains($catName, 'Tai nghe') || str_contains($catName, 'Loa') => 'audio',
                str_contains($catName, 'Đồng hồ') => 'watch',
                str_contains($catName, 'Tivi') || str_contains($catName, 'Màn hình') => 'tv',
                default => 'default',
            };
            $hasRamRom = in_array($variantMode, ['laptop', 'phone', 'tablet'], true);
            $hasCpuGpu = $variantMode === 'laptop' || $variantMode === 'tablet' || $variantMode === 'phone';
            $colorLabel = match ($variantMode) {
                'watch' => 'Kích Thước',
                'tv' => 'Kích Thước',
                'audio' => 'Phiên Bản / Màu',
                default => 'Màu Sắc',
            };
        @endphp

        {{-- Bảng biến thể --}}
        <div class="table-card animate-in">
            <div class="table-card-header">
                <h5><i class="bi bi-palette-fill me-2" style="color:var(--accent);"></i>Biến Thể <span style="background:var(--accent);color:#fff;font-size:.75rem;padding:3px 10px;border-radius:20px;margin-left:8px;">{{ $product->variants->count() }}</span></h5>
                <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addVariantModal">
                    <i class="bi bi-plus-lg"></i> Thêm Biến Thể
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ $colorLabel }}</th>
                            @if($hasRamRom)
                                <th>RAM</th>
                                <th>ROM / Bộ nhớ</th>
                            @endif
                            @if($hasCpuGpu)
                                <th>CPU</th>
                                <th>GPU</th>
                            @endif
                            <th>Giá +</th>
                            <th>Tổng Giá</th>
                            <th>Tồn An Toàn</th>
                            <th>Ảnh</th>
                            <th style="width:100px;text-align:center;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->variants as $variant)
                            <tr>
                                <td><span style="background:var(--bg-primary);border:1px solid var(--border);border-radius:6px;padding:3px 8px;font-size:.78rem;font-weight:600;color:var(--text-secondary)">{{ $variant->variant_id }}</span></td>
                                <td>
                                    @if($variant->color)
                                        <span class="d-flex align-items-center gap-2">
                                            @if(!str_contains($colorLabel, 'Kích Thước'))
                                                <span class="color-dot" style="background:{{ $variant->color }}" title="{{ $variant->color }}"></span>
                                            @endif
                                            {{ $variant->color }}
                                        </span>
                                    @else <span style="color:var(--text-secondary)">—</span>
                                    @endif
                                </td>
                                    @if($hasRamRom)
                                    <td>{{ $variant->ram ?? '—' }}</td>
                                    <td>{{ $variant->rom_capacity ?? '—' }}</td>
                                @endif
                                @if($hasCpuGpu)
                                    <td>{{ $variant->cpu_chip ?? '—' }}</td>
                                    <td>{{ $variant->gpu_chip ?? '—' }}</td>
                                @endif
                                <td><span class="extra-price">+{{ number_format($variant->extra_price, 0, ',', '.') }}₫</span></td>
                                <td><span class="total-price">{{ number_format($product->base_price + $variant->extra_price, 0, ',', '.') }}₫</span></td>
                                <td><span class="badge bg-secondary">{{ $variant->safe_stock ?? 5 }}</span></td>
                                <td>
                                    @if($variant->image_url)
                                        <img src="{{ $variant->image_url }}" class="variant-img" alt="variant" onerror="this.style.display='none'">
                                    @else <span style="color:var(--text-secondary)">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <button class="btn-action edit" title="Sửa"
                                            onclick="openEditVariant({{ $variant->variant_id }}, '{{ addslashes($variant->color ?? '') }}', '{{ addslashes($variant->ram ?? '') }}', '{{ addslashes($variant->rom_capacity ?? '') }}', '{{ addslashes($variant->cpu_chip ?? '') }}', '{{ addslashes($variant->gpu_chip ?? '') }}', {{ $variant->extra_price }}, '{{ addslashes($variant->image_url ?? '') }}', {{ $variant->safe_stock ?? 5 }})">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        <button class="btn-action delete" title="Xóa"
                                            onclick="confirmDeleteVariant({{ $variant->variant_id }})">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="bi bi-palette d-block"></i>
                                        <p class="mb-0">Chưa có biến thể nào. Hãy thêm biến thể đầu tiên!</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: THÊM BIẾN THỂ ===== --}}
    <div class="modal fade" id="addVariantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('admin.products.variants.store', $product->product_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-plus-circle-fill me-2" style="color:var(--accent);"></i>Thêm Biến Thể</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="{{ $hasRamRom ? 'col-md-6' : 'col-md-12' }}">
                                <label class="form-label">{{ $colorLabel }}</label>
                                <input type="text" name="color" class="form-control" placeholder="VD: Đen, 45mm, Tiêu chuẩn..." maxlength="30" required>
                            </div>
                            @if($hasRamRom)
                            <div class="col-md-6">
                                <label class="form-label">RAM</label>
                                <input type="text" name="ram" class="form-control" placeholder="VD: 8GB, 12GB..." maxlength="20">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ROM / Bộ nhớ</label>
                                <input type="text" name="rom_capacity" class="form-control" placeholder="VD: 128GB, 256GB..." maxlength="20">
                            </div>
                            @endif
                            @if($hasCpuGpu)
                            <div class="col-md-6">
                                <label class="form-label">CPU</label>
                                <input type="text" name="cpu_chip" class="form-control" placeholder="VD: Intel Core i7, Apple M3..." maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GPU</label>
                                <input type="text" name="gpu_chip" class="form-control" placeholder="VD: RTX 4060, Apple GPU 10-core..." maxlength="100">
                            </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Giá Cộng Thêm (₫) <span style="color:var(--danger);">*</span></label>
                                <input type="number" name="extra_price" class="form-control" placeholder="0" required min="0" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tồn Kho An Toàn <span style="color:var(--danger);">*</span></label>
                                <input type="number" name="safe_stock" class="form-control" placeholder="5" required min="0" value="5">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ảnh Biến Thể</label>
                                <div class="upload-tabs">
                                    <button type="button" class="upload-tab active" onclick="switchTab(this, 'add-upload-pane')"><i class="bi bi-cloud-arrow-up me-1"></i>Tải ảnh lên</button>
                                    <button type="button" class="upload-tab" onclick="switchTab(this, 'add-url-pane')"><i class="bi bi-link-45deg me-1"></i>Nhập URL</button>
                                </div>
                                <div id="add-upload-pane" class="upload-pane active">
                                    <div class="drop-zone" id="addDropZone">
                                        <input type="file" name="image_file" id="addFileInput" accept="image/jpeg,image/png,image/webp,image/gif" onchange="handleFileSelect(this, 'addFileInfo', 'addFilePreview')">
                                        <div class="drop-icon"><i class="bi bi-image"></i></div>
                                        <div class="drop-text">Kéo thả ảnh vào đây hoặc <strong>chọn file</strong></div>
                                        <div style="font-size:.75rem;color:var(--text-secondary);margin-top:4px;">JPG, PNG, WEBP, GIF • Tối đa 2MB</div>
                                    </div>
                                    <div id="addFileInfo" class="file-info" style="display:none;">
                                        <i class="bi bi-file-earmark-image" style="font-size:1.2rem;color:var(--accent);"></i>
                                        <span class="file-name"></span>
                                        <span class="file-size"></span>
                                        <button type="button" class="file-remove" onclick="clearFileInput('addFileInput', 'addFileInfo', 'addFilePreview')"><i class="bi bi-x"></i></button>
                                    </div>
                                    <img id="addFilePreview" class="img-preview" alt="Preview">
                                </div>
                                <div id="add-url-pane" class="upload-pane">
                                    <input type="text" name="image_url" class="form-control" placeholder="https://..." maxlength="500" oninput="previewUrl(this,'addVarPreview')">
                                    <img id="addVarPreview" class="img-preview" alt="Preview">
                                </div>
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

    {{-- ===== MODAL: SỬA BIẾN THỂ ===== --}}
    <div class="modal fade" id="editVariantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editVariantForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-pencil-square me-2" style="color:var(--accent);"></i>Sửa Biến Thể</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="{{ $hasRamRom ? 'col-md-6' : 'col-md-12' }}">
                                <label class="form-label">{{ $colorLabel }}</label>
                                <input type="text" name="color" id="evColor" class="form-control" maxlength="30" required>
                            </div>
                            @if($hasRamRom)
                            <div class="col-md-6">
                                <label class="form-label">RAM</label>
                                <input type="text" name="ram" id="evRam" class="form-control" maxlength="20">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ROM / Bộ nhớ</label>
                                <input type="text" name="rom_capacity" id="evRom" class="form-control" maxlength="20">
                            </div>
                            @endif
                            @if($hasCpuGpu)
                            <div class="col-md-6">
                                <label class="form-label">CPU</label>
                                <input type="text" name="cpu_chip" id="evCpu" class="form-control" maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GPU</label>
                                <input type="text" name="gpu_chip" id="evGpu" class="form-control" maxlength="100">
                            </div>
                            @endif
                            <div class="col-md-6">
                                <label class="form-label">Giá Cộng Thêm (₫) <span style="color:var(--danger);">*</span></label>
                                <input type="number" name="extra_price" id="evPrice" class="form-control" required min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tồn Kho An Toàn <span style="color:var(--danger);">*</span></label>
                                <input type="number" name="safe_stock" id="evSafeStock" class="form-control" required min="0">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ảnh Biến Thể</label>
                                {{-- Ảnh hiện tại --}}
                                <div id="editCurrentImage" style="margin-bottom:10px;display:none;">
                                    <div style="font-size:.78rem;color:var(--text-secondary);margin-bottom:4px;">Ảnh hiện tại:</div>
                                    <img id="editCurrentPreview" style="max-height:80px;border-radius:8px;border:1px solid var(--border);" alt="">
                                </div>
                                <div class="upload-tabs">
                                    <button type="button" class="upload-tab active" onclick="switchTab(this, 'edit-upload-pane')"><i class="bi bi-cloud-arrow-up me-1"></i>Tải ảnh mới</button>
                                    <button type="button" class="upload-tab" onclick="switchTab(this, 'edit-url-pane')"><i class="bi bi-link-45deg me-1"></i>Nhập URL</button>
                                </div>
                                <div id="edit-upload-pane" class="upload-pane active">
                                    <div class="drop-zone" id="editDropZone">
                                        <input type="file" name="image_file" id="editFileInput" accept="image/jpeg,image/png,image/webp,image/gif" onchange="handleFileSelect(this, 'editFileInfo', 'editFilePreview')">
                                        <div class="drop-icon"><i class="bi bi-image"></i></div>
                                        <div class="drop-text">Kéo thả ảnh vào đây hoặc <strong>chọn file</strong></div>
                                        <div style="font-size:.75rem;color:var(--text-secondary);margin-top:4px;">JPG, PNG, WEBP, GIF • Tối đa 2MB</div>
                                    </div>
                                    <div id="editFileInfo" class="file-info" style="display:none;">
                                        <i class="bi bi-file-earmark-image" style="font-size:1.2rem;color:var(--accent);"></i>
                                        <span class="file-name"></span>
                                        <span class="file-size"></span>
                                        <button type="button" class="file-remove" onclick="clearFileInput('editFileInput', 'editFileInfo', 'editFilePreview')"><i class="bi bi-x"></i></button>
                                    </div>
                                    <img id="editFilePreview" class="img-preview" alt="Preview">
                                </div>
                                <div id="edit-url-pane" class="upload-pane">
                                    <input type="text" name="image_url" id="evImage" class="form-control" maxlength="500" oninput="previewUrl(this,'editVarPreview')">
                                    <img id="editVarPreview" class="img-preview" alt="Preview">
                                </div>
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

    {{-- Form ẩn xóa --}}
    <form id="deleteVariantForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const productId = {{ $product->product_id }};
        const baseUrl = "{{ url('admin/products') }}";

        // ===== Tab Switching =====
        function switchTab(btn, paneId) {
            const tabs = btn.closest('.upload-tabs');
            const container = btn.closest('.col-12');
            tabs.querySelectorAll('.upload-tab').forEach(t => t.classList.remove('active'));
            btn.classList.add('active');
            container.querySelectorAll('.upload-pane').forEach(p => p.classList.remove('active'));
            document.getElementById(paneId).classList.add('active');
        }

        // ===== File Upload Handling =====
        function handleFileSelect(input, infoId, previewId) {
            const file = input.files[0];
            const info = document.getElementById(infoId);
            const preview = document.getElementById(previewId);
            if (file) {
                info.style.display = 'flex';
                info.querySelector('.file-name').textContent = file.name;
                info.querySelector('.file-size').textContent = formatFileSize(file.size);
                // Preview
                const reader = new FileReader();
                reader.onload = (e) => { preview.src = e.target.result; preview.style.display = 'block'; };
                reader.readAsDataURL(file);
            }
        }

        function clearFileInput(inputId, infoId, previewId) {
            document.getElementById(inputId).value = '';
            document.getElementById(infoId).style.display = 'none';
            const p = document.getElementById(previewId);
            p.src = ''; p.style.display = 'none';
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        // ===== Drag & Drop =====
        document.querySelectorAll('.drop-zone').forEach(zone => {
            zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drag-over'); });
            zone.addEventListener('dragleave', () => { zone.classList.remove('drag-over'); });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('drag-over');
                const input = zone.querySelector('input[type=file]');
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        });

        // ===== Edit Variant Modal =====
        function openEditVariant(id, color, ram, rom, cpu, gpu, price, imageUrl, safeStock) {
            document.getElementById('evColor').value = color;
            const evRam = document.getElementById('evRam');
            const evRom = document.getElementById('evRom');
            const evCpu = document.getElementById('evCpu');
            const evGpu = document.getElementById('evGpu');
            if (evRam) evRam.value = ram;
            if (evRom) evRom.value = rom;
            if (evCpu) evCpu.value = cpu;
            if (evGpu) evGpu.value = gpu;
            document.getElementById('evPrice').value = price;
            document.getElementById('evSafeStock').value = safeStock !== undefined ? safeStock : 5;
            document.getElementById('evImage').value = '';

            // Show current image
            const currentImg = document.getElementById('editCurrentImage');
            const currentPreview = document.getElementById('editCurrentPreview');
            if (imageUrl) {
                currentPreview.src = imageUrl;
                currentImg.style.display = 'block';
            } else {
                currentImg.style.display = 'none';
            }

            // Reset file input & previews
            clearFileInput('editFileInput', 'editFileInfo', 'editFilePreview');
            const urlPreview = document.getElementById('editVarPreview');
            urlPreview.src = ''; urlPreview.style.display = 'none';

            // Reset tabs to upload
            const editTabs = document.querySelector('#editVariantModal .upload-tabs');
            if (editTabs) {
                editTabs.querySelectorAll('.upload-tab').forEach((t, i) => t.classList.toggle('active', i === 0));
            }
            document.getElementById('edit-upload-pane').classList.add('active');
            document.getElementById('edit-url-pane').classList.remove('active');

            document.getElementById('editVariantForm').action = baseUrl + '/' + productId + '/variants/' + id;
            new bootstrap.Modal(document.getElementById('editVariantModal')).show();
        }

        // ===== Delete Variant =====
        function confirmDeleteVariant(id) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                html: 'Bạn có chắc muốn xóa biến thể này?<br><small style="color:#9ca3b4;">Hành động này không thể hoàn tác.</small>',
                icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#e74c5e', cancelButtonColor: '#2d3148',
                confirmButtonText: '<i class="bi bi-trash3-fill"></i> Xóa', cancelButtonText: 'Hủy',
                background: '#1e2231', color: '#e8e8ef',
            }).then((r) => {
                if (r.isConfirmed) {
                    const f = document.getElementById('deleteVariantForm');
                    f.action = baseUrl + '/' + productId + '/variants/' + id;
                    f.submit();
                }
            });
        }

        // ===== URL Preview =====
        function previewUrl(input, previewId) {
            const p = document.getElementById(previewId);
            if (input.value.trim()) { p.src = input.value.trim(); p.style.display = 'block'; p.onerror = () => p.style.display = 'none'; }
            else { p.style.display = 'none'; }
        }

        // ===== Flash Alert Auto-dismiss =====
        const fa = document.getElementById('flash-alert');
        if (fa) { setTimeout(() => { fa.style.transition='opacity .5s'; fa.style.opacity='0'; setTimeout(() => fa.remove(), 500); }, 4000); }
    </script>
</body>
</html>
