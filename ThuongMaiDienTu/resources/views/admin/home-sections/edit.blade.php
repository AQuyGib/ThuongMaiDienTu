@extends('admin.layouts.master')

@section('title', 'Chỉnh sửa khung sản phẩm')

@section('content')
<div class="container-fluid px-4">
    <!-- Breadcrumb điều hướng quay lại trang danh sách quản lý -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.home-sections.index') }}">Quản lý khung</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chỉnh sửa</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Chỉnh sửa khung: {{ $section->title }}</h2>
    </div>

    <!-- Form cập nhật thông tin khung, hỗ trợ method PUT và tải file enctype -->
    <form action="{{ route('admin.home-sections.update', $section->id) }}" method="POST" enctype="multipart/form-data" id="sectionForm">
        @csrf
        @method('PUT')
        <div class="row">
            <!-- Cột bên trái: Cấu hình chung & khu vực gắp sản phẩm -->
            <div class="col-lg-8">
                <!-- Card Cấu hình chung -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Cấu hình khung</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Sửa tiêu đề khung -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Tiêu đề khung <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ $section->title }}" required>
                            </div>

                            <!-- Sửa loại hiển thị -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Loại hiển thị <span class="text-danger">*</span></label>
                                <select name="type" id="typeSelect" class="form-select" required>
                                    <option value="latest" {{ $section->type === 'latest' ? 'selected' : '' }}>Sản phẩm mới nhất</option>
                                    <option value="category" {{ $section->type === 'category' ? 'selected' : '' }}>Theo danh mục</option>
                                    <option value="manual" {{ $section->type === 'manual' ? 'selected' : '' }}>Tự chọn (Gắp sản phẩm)</option>
                                </select>
                            </div>

                            <!-- Sửa số lượng sản phẩm hiển thị tối đa -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số lượng hiển thị <span class="text-danger">*</span></label>
                                <input type="number" name="limit" class="form-control" value="{{ $section->limit }}" min="1" max="20" required>
                            </div>

                            <!-- Chọn danh mục sản phẩm (Chỉ hiển thị khi loại hiển thị = category) -->
                            <div id="categoryWrapper" class="col-md-12 {{ $section->type !== 'category' ? 'd-none' : '' }}">
                                <label class="form-label fw-semibold">Chọn danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->category_id }}" {{ $section->category_id == $category->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        <!-- Duyệt lấy danh mục con đính kèm -->
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->category_id }}" {{ $section->category_id == $child->category_id ? 'selected' : '' }}>-- {{ $child->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Khu vực Gắp sản phẩm (Chỉ hiển thị khi loại hiển thị = manual) -->
                <div id="manualWrapper" class="card border-0 shadow-sm rounded-4 mb-4 {{ $section->type !== 'manual' ? 'd-none' : '' }}">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-mouse-pointer me-2 text-primary"></i>Gắp sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <!-- Ô tìm kiếm sản phẩm Autocomplete qua AJAX -->
                        <div class="input-group mb-4">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="productSearch" class="form-control border-start-0" placeholder="Gõ tên sản phẩm để tìm...">
                        </div>
                        
                        <!-- Kết quả tìm kiếm hiển thị thả xuống nhanh -->
                        <div id="searchResults" class="list-group mb-4" style="max-height: 300px; overflow-y: auto;">
                            <!-- Đổ kết quả tìm kiếm dynamic -->
                        </div>

                        <!-- Danh sách sản phẩm đã gắp trước đó, cho phép kéo thả để thay đổi thứ tự -->
                        <h6 class="fw-bold mb-3">Sản phẩm đã chọn (Kéo thả các sản phẩm để sắp xếp thứ tự hiển thị)</h6>
                        <ul id="selectedProducts" class="list-group shadow-sm">
                            <!-- Danh sách sản phẩm được chọn hiển thị tại đây -->
                        </ul>
                        
                        <!-- Lưu trữ chuỗi các ID sản phẩm được chọn dạng dấu phẩy để gửi lên server -->
                        <input type="hidden" name="product_ids" id="product_ids_input" value="{{ $section->products->pluck('product_id')->join(',') }}">
                    </div>
                </div>
            </div>

            <!-- Cột bên phải: Upload banner quảng cáo đi kèm khung -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-image me-2 text-primary"></i>Banner quảng cáo</h5>
                    </div>
                    <div class="card-body">
                        <!-- Chọn file ảnh mới tải lên từ máy tính -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Thay đổi ảnh</label>
                            <input type="file" name="sidebar_banner_file" class="form-control" id="bannerInput" accept="image/*">
                        </div>

                        <!-- Hoặc nhập link ảnh khác ngoài hệ thống -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Hoặc dùng URL</label>
                            <input type="text" name="sidebar_banner_url" class="form-control" value="{{ $section->sidebar_banner }}">
                        </div>

                        <!-- Hiển thị xem trước banner hiện tại hoặc mới tải lên -->
                        <div id="bannerPreviewWrapper" class="text-center p-3 bg-light rounded-3 {{ !$section->sidebar_banner ? 'd-none' : '' }}">
                            <img id="bannerPreview" src="{{ $section->sidebar_banner }}" class="img-fluid rounded-3 shadow-sm">
                        </div>

                        <!-- Liên kết đích khi click banner quảng cáo ngoài trang chủ -->
                        <div class="mt-4">
                            <label class="form-label fw-semibold">Link khi click banner</label>
                            <input type="text" name="sidebar_link" class="form-control" value="{{ $section->sidebar_link }}">
                        </div>
                    </div>
                </div>

                <!-- Cấu hình lưu trữ và sắp xếp -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <!-- Số thứ tự hiển thị của khung sản phẩm so với các khung khác -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                            <input type="number" name="order" class="form-control" value="{{ $section->order }}">
                        </div>
                        
                        <!-- Switch bật tắt ẩn hiện nhanh -->
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" {{ $section->status ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="statusSwitch">Hiển thị khung này</label>
                        </div>
                        
                        <!-- Nút Lưu cập nhật cấu hình -->
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold">
                            <i class="fas fa-save me-2"></i>Cập nhật cấu hình
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- Nhúng SortableJS để hỗ trợ tính năng kéo thả thay đổi thứ tự sản phẩm đã chọn -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ánh xạ các phần tử DOM
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

        // BẮT BUỘC: Khởi tạo mảng selectedItems ban đầu bằng dữ liệu sản phẩm đã liên kết trước đó trong DB
        let selectedItems = @json($section->products->map(function($p) {
            return [
                'id' => $p->product_id, 
                'name' => $p->name, 
                'thumbnail' => $p->thumbnail ? asset($p->thumbnail) : 'https://images.unsplash.com/photo-1593640495253-23196b27a87f?w=400'
            ];
        }));

        // Render giao diện danh sách sản phẩm đã gắp ban đầu
        updateSelectedUI();

        // Ẩn/hiện các khối danh mục hoặc gắp sản phẩm theo loại hiển thị
        typeSelect.addEventListener('change', function() {
            categoryWrapper.classList.add('d-none');
            manualWrapper.classList.add('d-none');
            if (this.value === 'category') categoryWrapper.classList.remove('d-none');
            if (this.value === 'manual') manualWrapper.classList.remove('d-none');
        });

        // Xử lý xem trước hình ảnh tải lên thời gian thực
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

        // Xử lý tìm kiếm sản phẩm autocomplete với kỹ thuật debounce 300ms
        let searchTimeout;
        productSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value;
            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('admin.api.products.search') }}?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        searchResults.innerHTML = data.map(p => `
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="addProduct('${p.product_id}', '${escapeHtml(p.name)}', '${p.thumbnail}')">
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

        // Chuyển đổi an toàn chuỗi HTML
        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Thêm sản phẩm được gắp vào mảng và cập nhật giao diện
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

        // Loại bỏ sản phẩm ra khỏi danh sách gắp
        window.removeProduct = function(id) {
            selectedItems = selectedItems.filter(item => item.id != id);
            updateSelectedUI();
        };

        // Khởi tạo kéo thả SortableJS cho danh sách sản phẩm để thay đổi thứ tự sắp xếp hiển thị
        Sortable.create(selectedProducts, {
            handle: '.fa-bars', // Chỉ cho phép kéo thả khi di chuột vào icon bars 3 gạch
            animation: 150,
            onEnd: function() {
                // Sắp xếp lại mảng selectedItems theo đúng thứ tự DOM thực tế sau khi kéo
                const newOrderItems = [];
                const lis = selectedProducts.querySelectorAll('li');
                lis.forEach(li => {
                    const id = li.getAttribute('data-id');
                    const found = selectedItems.find(item => item.id == id);
                    if (found) {
                        newOrderItems.push(found);
                    }
                });
                selectedItems = newOrderItems;
                // Cập nhật lại chuỗi ID gửi lên Server
                productIdsInput.value = selectedItems.map(item => item.id).join(',');
            }
        });

        // Vẽ lại giao diện danh sách sản phẩm đã chọn
        function updateSelectedUI() {
            selectedProducts.innerHTML = selectedItems.map(item => `
                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="${item.id}">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-bars text-muted me-3 cursor-grab" title="Kéo thả để sắp xếp"></i>
                        <img src="${item.thumbnail}" class="rounded me-2" style="width: 30px; height: 30px; object-fit: cover;">
                        <span>${item.name}</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="removeProduct('${item.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </li>
            `).join('');

            // Cập nhật giá trị chuỗi ID sản phẩm cho thẻ input hidden gửi lên Backend
            productIdsInput.value = selectedItems.map(item => item.id).join(',');
        }
    });
</script>
@endpush
@endsection
