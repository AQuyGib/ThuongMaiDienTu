@extends('admin.layouts.master')

@section('title', 'Tạo khung sản phẩm mới')

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.home-sections.index') }}">Quản lý khung</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tạo mới</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Tạo khung sản phẩm mới</h2>
    </div>

    <form action="{{ route('admin.home-sections.store') }}" method="POST" enctype="multipart/form-data" id="sectionForm">
        @csrf
        <div class="row">
            <!-- Cấu hình chung -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Cấu hình khung</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Tiêu đề khung <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="Ví dụ: ĐIỆN THOẠI NỔI BẬT" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Loại hiển thị <span class="text-danger">*</span></label>
                                <select name="type" id="typeSelect" class="form-select" required>
                                    <option value="latest">Sản phẩm mới nhất</option>
                                    <option value="category">Theo danh mục</option>
                                    <option value="manual">Tự chọn (Gắp sản phẩm)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số lượng hiển thị <span class="text-danger">*</span></label>
                                <input type="number" name="limit" class="form-control" value="8" min="1" max="20" required>
                            </div>

                            <div id="categoryWrapper" class="col-md-12 d-none">
                                <label class="form-label fw-semibold">Chọn danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->category_id }}">-- {{ $child->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gắp sản phẩm (Chỉ hiện khi chọn manual) -->
                <div id="manualWrapper" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-mouse-pointer me-2 text-primary"></i>Gắp sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <div class="input-group mb-4">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="productSearch" class="form-control border-start-0" placeholder="Gõ tên sản phẩm để tìm...">
                        </div>
                        
                        <div id="searchResults" class="list-group mb-4" style="max-height: 300px; overflow-y: auto;">
                            <!-- Kết quả tìm kiếm hiện ở đây -->
                        </div>

                        <h6 class="fw-bold mb-3">Sản phẩm đã chọn (Kéo để đổi thứ tự)</h6>
                        <ul id="selectedProducts" class="list-group shadow-sm">
                            <!-- Sản phẩm đã chọn hiện ở đây -->
                        </ul>
                        <input type="hidden" name="product_ids" id="product_ids_input">
                    </div>
                </div>
            </div>

            <!-- Banner & Media -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-image me-2 text-primary"></i>Banner quảng cáo</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Upload ảnh</label>
                            <input type="file" name="sidebar_banner_file" class="form-control" id="bannerInput" accept="image/*">
                            <div class="form-text">Kích thước gợi ý: 400x800px</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Hoặc dùng URL</label>
                            <input type="text" name="sidebar_banner_url" class="form-control" placeholder="https://...">
                        </div>

                        <div id="bannerPreviewWrapper" class="text-center p-3 bg-light rounded-3 d-none">
                            <img id="bannerPreview" src="" class="img-fluid rounded-3 shadow-sm">
                        </div>

                        <div class="mt-4">
                            <label class="form-label fw-semibold">Link khi click banner</label>
                            <input type="text" name="sidebar_link" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                            <input type="number" name="order" class="form-control" value="0">
                        </div>
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" checked>
                            <label class="form-check-label fw-semibold" for="statusSwitch">Hiển thị khung này</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold">
                            <i class="fas fa-save me-2"></i>Lưu cấu hình
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('typeSelect');
        const categoryWrapper = document.getElementById('categoryWrapper');
        const manualWrapper = document.getElementById('manualWrapper');
        const bannerInput = document.getElementById('bannerInput');
        const bannerPreview = document.getElementById('bannerPreview');
        const bannerPreviewWrapper = document.getElementById('bannerPreviewWrapper');
        const productSearch = document.getElementById('productSearch');
        const searchResults = document.getElementById('searchResults');
        const selectedProducts = document.getElementById('selectedProducts');
        const productIdsInput = document.getElementById('product_ids_input');

        let selectedItems = [];

        // Chuyển đổi giao diện theo loại
        typeSelect.addEventListener('change', function() {
            categoryWrapper.classList.add('d-none');
            manualWrapper.classList.add('d-none');
            
            if (this.value === 'category') categoryWrapper.classList.remove('d-none');
            if (this.value === 'manual') manualWrapper.classList.remove('d-none');
        });

        // Xem trước banner
        bannerInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    bannerPreview.src = e.target.result;
                    bannerPreviewWrapper.classList.remove('d-none');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Tìm kiếm sản phẩm
        let searchTimeout;
        productSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value;
            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('admin.api.products.search') }}?q=${query}`)
                    .then(res => res.json())
                    .then(data => {
                        searchResults.innerHTML = data.map(p => `
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="addProduct('${p.product_id}', '${p.name}', '${p.thumbnail}')">
                                <div class="d-flex align-items-center">
                                    <img src="${p.thumbnail}" class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                                    <span>${p.name}</span>
                                </div>
                                <i class="fas fa-plus text-primary"></i>
                            </button>
                        `).join('');
                    });
            }, 300);
        });

        // Hàm thêm sản phẩm vào danh sách chọn
        window.addProduct = function(id, name, thumbnail) {
            if (selectedItems.find(item => item.id == id)) {
                alert('Sản phẩm này đã được chọn!');
                return;
            }

            selectedItems.push({ id, name, thumbnail });
            updateSelectedUI();
            searchResults.innerHTML = '';
            productSearch.value = '';
        };

        window.removeProduct = function(id) {
            selectedItems = selectedItems.filter(item => item.id != id);
            updateSelectedUI();
        };

        function updateSelectedUI() {
            selectedProducts.innerHTML = selectedItems.map(item => `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bars text-muted me-3"></i>
                        <img src="${item.thumbnail}" class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                        <span>${item.name}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeProduct('${item.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </li>
            `).join('');

            productIdsInput.value = selectedItems.map(item => item.id).join(',');
        }
    });
</script>
@endpush

<style>
    .form-control:focus, .form-select:focus { border-color: #0046ab; box-shadow: 0 0 0 0.25rem rgba(0, 70, 171, 0.1); }
    .list-group-item { border-left: none; border-right: none; }
    .list-group-item:first-child { border-top: none; }
</style>
@endsection
