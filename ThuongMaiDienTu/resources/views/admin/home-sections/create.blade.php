@extends('admin.layouts.master')

@section('title', 'Tạo khung sản phẩm mới')

@section('content')
<div class="container-fluid px-4">
    <!-- Breadcrumb điều hướng quay lại trang danh sách -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.home-sections.index') }}">Quản lý khung</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tạo mới</li>
            </ol>
        </nav>
        <h2 class="fw-bold">Tạo khung sản phẩm mới</h2>
    </div>

    <!-- Form tạo mới khung sản phẩm, có thuộc tính enctype="multipart/form-data" để hỗ trợ tải file ảnh -->
    <form action="{{ route('admin.home-sections.store') }}" method="POST" enctype="multipart/form-data" id="sectionForm">
        @csrf
        <div class="row">
            <!-- Cột bên trái: Cấu hình chung và chức năng gắp sản phẩm -->
            <div class="col-lg-8">
                <!-- Card Cấu hình chung -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-cog me-2 text-primary"></i>Cấu hình khung</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Nhập tiêu đề hiển thị ngoài trang chủ (Ví dụ: LAPTOP KHUYẾN MÃI) -->
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Tiêu đề khung <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="Ví dụ: ĐIỆN THOẠI NỔI BẬT" required>
                            </div>

                            <!-- Chọn loại hiển thị -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Loại hiển thị <span class="text-danger">*</span></label>
                                <select name="type" id="typeSelect" class="form-select" required>
                                    <option value="latest">Sản phẩm mới nhất</option>
                                    <option value="category">Theo danh mục</option>
                                    <option value="manual">Tự chọn (Gắp sản phẩm)</option>
                                </select>
                            </div>

                            <!-- Nhập giới hạn số lượng sản phẩm tối đa được tải ra giao diện -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Số lượng hiển thị <span class="text-danger">*</span></label>
                                <input type="number" name="limit" class="form-control" value="8" min="1" max="20" required>
                            </div>

                            <!-- Chọn danh mục sản phẩm (Chỉ hiển thị khi loại hiển thị = category) -->
                            <div id="categoryWrapper" class="col-md-12 d-none">
                                <label class="form-label fw-semibold">Chọn danh mục <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->category_id }}">{{ $category->name }}</option>
                                        <!-- Duyệt đệ quy lấy các danh mục con cấp 2 -->
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->category_id }}">-- {{ $child->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Khu vực Gắp sản phẩm (Chỉ hiển thị khi loại hiển thị = manual) -->
                <div id="manualWrapper" class="card border-0 shadow-sm rounded-4 mb-4 d-none">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-mouse-pointer me-2 text-primary"></i>Gắp sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <!-- Ô tìm kiếm sản phẩm tích hợp biểu tượng Search kính lúp -->
                        <div class="input-group mb-4">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="productSearch" class="form-control border-start-0" placeholder="Gõ tên sản phẩm để tìm...">
                        </div>
                        
                        <!-- Danh sách hiển thị kết quả tìm kiếm thả xuống nhanh (Autocomplete) -->
                        <div id="searchResults" class="list-group mb-4" style="max-height: 300px; overflow-y: auto;">
                            <!-- Kết quả tìm kiếm AJAX đổ về sẽ được render tại đây -->
                        </div>

                        <!-- Danh sách các sản phẩm đã gắp, cho phép kéo thả để sắp xếp lại thứ tự -->
                        <h6 class="fw-bold mb-3">Sản phẩm đã chọn (Kéo thả các sản phẩm để sắp xếp thứ tự hiển thị)</h6>
                        <ul id="selectedProducts" class="list-group shadow-sm">
                            <!-- Danh sách sản phẩm được chọn sẽ render động tại đây -->
                        </ul>
                        
                        <!-- Lưu trữ danh sách ID các sản phẩm cách nhau bởi dấu phẩy để gửi lên Server xử lý -->
                        <input type="hidden" name="product_ids" id="product_ids_input">
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
                        <!-- Chọn file từ máy tính -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Upload ảnh</label>
                            <input type="file" name="sidebar_banner_file" class="form-control" id="bannerInput" accept="image/*">
                            <div class="form-text">Kích thước gợi ý: 400x800px</div>
                        </div>

                        <!-- Hoặc sử dụng link ảnh có sẵn ở bên ngoài -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Hoặc dùng URL</label>
                            <input type="text" name="sidebar_banner_url" class="form-control" placeholder="https://...">
                        </div>

                        <!-- Khu vực xem trước ảnh trước khi lưu cấu hình -->
                        <div id="bannerPreviewWrapper" class="text-center p-3 bg-light rounded-3 d-none">
                            <img id="bannerPreview" src="" class="img-fluid rounded-3 shadow-sm">
                        </div>

                        <!-- Liên kết khi người mua click vào banner quảng cáo ngoài trang chủ -->
                        <div class="mt-4">
                            <label class="form-label fw-semibold">Link khi click banner</label>
                            <input type="text" name="sidebar_link" class="form-control" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <!-- Cấu hình lưu trữ -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body">
                        <!-- Thứ tự sắp xếp của Khung sản phẩm so với các khung khác ngoài trang chủ -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Thứ tự hiển thị</label>
                            <input type="number" name="order" class="form-control" value="0">
                        </div>
                        
                        <!-- Bật/tắt trạng thái ẩn hiện nhanh -->
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" checked>
                            <label class="form-check-label fw-semibold" for="statusSwitch">Hiển thị khung này</label>
                        </div>
                        
                        <!-- Nút gửi lưu toàn bộ thông tin form -->
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm fw-bold">
                            <i class="fas fa-save me-2"></i>Lưu cấu hình
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- Nhúng SortableJS để xử lý kéo thả sắp xếp các sản phẩm đã gắp -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Ánh xạ các phần tử giao diện
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

        // Mảng chứa các sản phẩm được gắp chọn thủ công
        let selectedItems = [];

        // Lắng nghe sự kiện đổi loại hiển thị để ẩn/hiện danh mục hoặc khu vực gắp sản phẩm
        typeSelect.addEventListener('change', function() {
            categoryWrapper.classList.add('d-none');
            manualWrapper.classList.add('d-none');
            
            if (this.value === 'category') categoryWrapper.classList.remove('d-none');
            if (this.value === 'manual') manualWrapper.classList.remove('d-none');
        });

        // Đọc và xem trước (Preview) file ảnh bằng FileReader khi admin chọn ảnh
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

        // Xử lý debounce tìm kiếm tự động sản phẩm qua AJAX
        let searchTimeout;
        productSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value;
            // Bỏ qua nếu từ khóa quá ngắn (dưới 2 ký tự)
            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            // Đợi 300ms sau khi người dùng dừng gõ phím mới gửi request (giảm tải cho server)
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

        // Hàm xử lý an toàn chuỗi HTML tránh lỗi phá vỡ layout
        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Thêm sản phẩm được chọn vào mảng và cập nhật giao diện
        window.addProduct = function(id, name, thumbnail) {
            // Ngăn chặn trùng lặp sản phẩm trong danh sách đã gắp
            if (selectedItems.find(item => item.id == id)) {
                alert('Sản phẩm này đã được chọn!');
                return;
            }

            selectedItems.push({ id, name, thumbnail });
            updateSelectedUI();
            searchResults.innerHTML = ''; // Xóa kết quả tìm kiếm thả xuống
            productSearch.value = ''; // Làm sạch ô tìm kiếm
        };

        // Loại bỏ sản phẩm khỏi danh sách đã gắp
        window.removeProduct = function(id) {
            selectedItems = selectedItems.filter(item => item.id != id);
            updateSelectedUI();
        };

        // Khởi tạo SortableJS để cho phép kéo thả thay đổi thứ tự sản phẩm đã gắp ngay trên form
        Sortable.create(selectedProducts, {
            handle: '.fa-bars', // Chỉ cho phép bắt đầu kéo khi nhấp chuột vào biểu tượng menu bars 3 gạch
            animation: 150,
            onEnd: function() {
                // Khi kết thúc kéo thả, xây dựng lại mảng selectedItems theo thứ tự trực quan mới trên giao diện
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
                // Cập nhật lại chuỗi ID sản phẩm gửi lên Backend
                productIdsInput.value = selectedItems.map(item => item.id).join(',');
            }
        });

        // Cập nhật giao diện danh sách sản phẩm được chọn
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

            // Đóng gói mảng ID thành chuỗi phân cách bởi dấu phẩy gán vào hidden input
            productIdsInput.value = selectedItems.map(item => item.id).join(',');
        }
    });
</script>
@endpush

<style>
    /* Con trỏ cầm nắm biểu tượng kéo thả */
    .cursor-grab { cursor: grab; }
    .cursor-grab:active { cursor: grabbing; }
    .form-control:focus, .form-select:focus { border-color: #0046ab; box-shadow: 0 0 0 0.25rem rgba(0, 70, 171, 0.1); }
    .list-group-item { border-left: none; border-right: none; }
    .list-group-item:first-child { border-top: none; }
</style>
@endsection
