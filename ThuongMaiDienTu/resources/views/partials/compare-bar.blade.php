{{-- resources/views/partials/compare-bar.blade.php --}}
<!-- KHỐI 1: THANH SO SÁNH SẢN PHẨM FLOATING (COMPARE BAR)
     Cố định ở dưới cùng màn hình (Floating Bar), mặc định ẩn và chỉ hiện khi người dùng thêm sản phẩm so sánh.
-->
<div id="compareBar" class="compare-bar" style="display: none;">
    <div class="compare-bar-inner">
        <!-- Khu vực chứa các khe (Slots) sản phẩm so sánh (Tối đa 3 sản phẩm) -->
        <div class="compare-slots" id="compareSlotsContainer">
            @for($i = 0; $i < 3; $i++)
            <div class="compare-slot" id="compareSlot{{ $i }}">
                <!-- Trạng thái 1: Khe trống (Chưa có sản phẩm, click vào sẽ mở modal tìm kiếm sản phẩm) -->
                <div class="compare-slot-empty" onclick="openCompareSearch({{ $i }})">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm sản phẩm</span>
                </div>
                <!-- Trạng thái 2: Khe đã đầy (Đã chọn sản phẩm, hiển thị ảnh, tên, giá và nút X để xóa) -->
                <div class="compare-slot-filled" style="display: none;" data-product-id="">
                    <img src="" alt="" class="compare-slot-img">
                    <div class="compare-slot-info">
                        <span class="compare-slot-name"></span>
                        <span class="compare-slot-price"></span>
                    </div>
                    <button class="compare-slot-remove" onclick="removeFromCompare(this)">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
            @endfor
        </div>

        <!-- Khối nút hành động so sánh (Xem chi tiết so sánh, Xóa hết hoặc Thu gọn thanh bar) -->
        <div class="compare-actions">
            <!-- Nút điều hướng sang trang chi tiết so sánh các thuộc tính của các sản phẩm đã chọn -->
            <a href="{{ route('compare.index') }}" class="compare-btn-go">
                So sánh ngay <span id="compareCountBadge" class="compare-count-badge">0</span>
            </a>
            <div style="display: flex; gap: 8px;">
                <!-- Nút xóa toàn bộ sản phẩm đang chọn trong danh sách so sánh -->
                <button class="compare-btn-clear" onclick="clearCompare()">Xóa hết</button>
                <!-- Nút thu gọn thanh so sánh để không chiếm không gian hiển thị của người dùng -->
                <button id="compareCollapseBtn" class="compare-btn-clear">Thu gọn</button>
            </div>
        </div>
    </div>
</div>

<!-- KHỐI 2: MODAL TÌM KIẾM SẢN PHẨM NHANH (SEARCH MODAL)
     Hiển thị pop-up cho phép người dùng gõ từ khóa tìm sản phẩm cùng ngành để thêm vào ô so sánh trống.
-->
<div id="compareSearchModal" class="compare-search-modal" style="display: none;">
    <div class="compare-search-content">
        <!-- Header của Modal chứa tiêu đề và nút đóng (x) -->
        <div class="compare-search-header">
            <h3><i class="fa-solid fa-magnifying-glass"></i> Tìm sản phẩm so sánh</h3>
            <button onclick="closeCompareSearch()">&times;</button>
        </div>
        <!-- Thân Modal chứa thanh input tìm kiếm và khung hiển thị danh sách kết quả (injected bằng JS) -->
        <div class="compare-search-body">
            <input type="text" id="compareSearchInput" placeholder="Nhập tên sản phẩm cần tìm...">
            <div id="compareSearchResults" class="compare-search-results">
                <!-- Kết quả tìm kiếm sản phẩm sẽ được Javascript gửi yêu cầu và render động vào đây -->
            </div>
        </div>
    </div>
</div>

