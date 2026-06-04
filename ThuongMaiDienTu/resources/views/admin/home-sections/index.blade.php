@extends('admin.layouts.master')

@section('title', 'Quản lý Khung trang chủ')

@section('content')
<div class="container-fluid px-4">
    <!-- Khu vực Tiêu đề và Nút hành động -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Quản lý Khung trang chủ</h2>
            <p class="text-muted">Tùy biến các khối sản phẩm hiển thị ngoài trang chủ</p>
        </div>
        <!-- Nút thêm mới khung sản phẩm trang chủ -->
        <a href="{{ route('admin.home-sections.create') }}" class="btn btn-primary px-4 shadow-sm">
            <i class="fas fa-plus me-2"></i>Thêm khung mới
        </a>
    </div>

    <!-- Hiển thị thông báo thành công (Flash Session) từ controller gửi về -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Bảng danh sách các khung sản phẩm -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="sections-table">
                    <thead class="bg-light">
                        <tr>
                            <!-- Các tiêu đề cột trong bảng quản trị -->
                            <th class="ps-4 py-3" style="width: 50px;">#</th>
                            <th class="py-3">Tiêu đề</th>
                            <th class="py-3">Loại hiển thị</th>
                            <th class="py-3">Số lượng</th>
                            <th class="py-3">Trạng thái</th>
                            <th class="py-3 text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <!-- Gán id="sortable-sections" để kích hoạt thư viện SortableJS kéo thả -->
                    <tbody id="sortable-sections">
                        @foreach($sections as $section)
                        <!-- Gán data-id phục vụ cho việc lấy ID cập nhật thứ tự khi kéo thả -->
                        <tr data-id="{{ $section->id }}">
                            <td class="ps-4">
                                <!-- Nút grip hiển thị trực quan biểu tượng kéo thả -->
                                <i class="fas fa-grip-vertical text-muted cursor-move me-2"></i>
                                {{ $loop->iteration }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <!-- Hiển thị ảnh nhỏ (thumbnail) của Banner quảng cáo đi kèm khung sản phẩm -->
                                    @if($section->sidebar_banner)
                                        <img src="{{ $section->sidebar_banner }}" class="rounded-2 me-3" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <!-- Fallback hiển thị icon mặc định nếu chưa gán banner -->
                                        <div class="bg-light rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ $section->title }}</div>
                                        <small class="text-muted">Thứ tự: {{ $section->order }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <!-- Nhãn Badge hiển thị rõ loại nguồn dữ liệu sản phẩm -->
                                @if($section->type === 'latest')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3">Mới nhất</span>
                                @elseif($section->type === 'manual')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3">Thủ công</span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">Danh mục</span>
                                @endif
                            </td>
                            <td>{{ $section->limit }} SP</td>
                            <td>
                                <!-- Nhãn Badge trạng thái ẩn/hiện ngoài Storefront -->
                                @if($section->status)
                                    <span class="badge bg-success rounded-pill px-3">Đang hiện</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3">Đang ẩn</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                    <!-- Nút Chỉnh sửa -->
                                    <a href="{{ route('admin.home-sections.edit', $section->id) }}" class="btn btn-white btn-sm px-3" title="Chỉnh sửa">
                                        <i class="fas fa-edit text-primary"></i>
                                    </a>
                                    <!-- Nút Xóa kèm xác nhận Javascript để tránh click nhầm -->
                                    <form action="{{ route('admin.home-sections.destroy', $section->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khung này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-white btn-sm px-3" title="Xóa">
                                            <i class="fas fa-trash-alt text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        
                        <!-- Hiển thị thông báo nếu danh sách trống -->
                        @if($sections->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-layer-group fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Chưa có khung sản phẩm nào được tạo.</p>
                                    <a href="{{ route('admin.home-sections.create') }}" class="btn btn-link text-primary p-0">Tạo khung đầu tiên ngay</a>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* CSS tùy biến con trỏ kéo thả và nút trắng */
    .cursor-move { cursor: move; }
    .btn-white { background: #fff; border: 1px solid #eee; }
    .btn-white:hover { background: #f8f9fa; }
    .bg-info-subtle { background-color: #e0f7fa !important; }
    .text-info { color: #00acc1 !important; }
    .bg-warning-subtle { background-color: #fff8e1 !important; }
    .text-warning { color: #ffa000 !important; }
    .bg-primary-subtle { background-color: #e3f2fd !important; }
</style>
@endsection

@push('scripts')
<!-- Nhúng thư viện SortableJS để cho phép kéo thả các dòng trong bảng trực quan -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('sortable-sections');
        if (!el) return;

        // Khởi tạo tính năng kéo thả trên phần tử tbody chứa các dòng dữ liệu
        Sortable.create(el, {
            handle: '.cursor-move', // Chỉ cho phép nhấp vào biểu tượng Grip để bắt đầu kéo
            animation: 150,        // Độ trễ chuyển động mượt mà (miligiây)
            onEnd: function() {
                // Tự động thu thập thứ tự ID mới sau khi kéo thả xong
                const orders = {};
                const rows = el.querySelectorAll('tr');
                
                rows.forEach((row, index) => {
                    const id = row.getAttribute('data-id');
                    if (id) {
                        orders[id] = index + 1; // Gán thứ tự mới bắt đầu từ 1
                    }
                });

                // Gửi AJAX POST lên Backend để lưu lại thứ tự mới vào cơ sở dữ liệu
                fetch('{{ route("admin.home-sections.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Bắt buộc đính kèm token CSRF để tránh lỗi 419
                    },
                    body: JSON.stringify({ orders: orders })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Tải lại nhẹ trang hoặc cập nhật số thứ tự hiển thị bằng văn bản trên màn hình
                        rows.forEach((row, index) => {
                            const orderNumTd = row.querySelector('td.ps-4');
                            if (orderNumTd) {
                                orderNumTd.innerHTML = `<i class="fas fa-grip-vertical text-muted cursor-move me-2"></i>${index + 1}`;
                            }
                            const orderNumSmall = row.querySelector('small.text-muted');
                            if (orderNumSmall) {
                                orderNumSmall.innerText = `Thứ tự: ${index + 1}`;
                            }
                        });
                    } else {
                        alert('Có lỗi xảy ra khi cập nhật thứ tự.');
                    }
                })
                .catch(err => {
                    console.error('Lỗi reorder:', err);
                    alert('Không thể kết nối đến máy chủ.');
                });
            }
        });
    });
</script>
@endpush
