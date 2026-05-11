{{-- resources/views/partials/compare-bar.blade.php --}}
<div id="compareBar" class="compare-bar" style="display: none;">
    <div class="compare-bar-inner">
        <div class="compare-slots" id="compareSlotsContainer">
            @for($i = 0; $i < 3; $i++)
            <div class="compare-slot" id="compareSlot{{ $i }}">
                <div class="compare-slot-empty" onclick="openCompareSearch({{ $i }})">
                    <i class="fa-solid fa-plus"></i>
                    <span>Thêm sản phẩm</span>
                </div>
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

        <div class="compare-actions">
            <a href="{{ route('compare.index') }}" class="compare-btn-go">
                So sánh ngay <span id="compareCountBadge" class="compare-count-badge">0</span>
            </a>
            <div style="display: flex; gap: 8px;">
                <button class="compare-btn-clear" onclick="clearCompare()">Xóa hết</button>
                <button id="compareCollapseBtn" class="compare-btn-clear">Thu gọn</button>
            </div>
        </div>
    </div>
</div>

<!-- Search Modal for Comparison -->
<div id="compareSearchModal" class="compare-search-modal" style="display: none;">
    <div class="compare-search-content">
        <div class="compare-search-header">
            <h3><i class="fa-solid fa-magnifying-glass"></i> Tìm sản phẩm so sánh</h3>
            <button onclick="closeCompareSearch()">&times;</button>
        </div>
        <div class="compare-search-body">
            <input type="text" id="compareSearchInput" placeholder="Nhập tên sản phẩm cần tìm...">
            <div id="compareSearchResults" class="compare-search-results">
                <!-- Results injected here -->
            </div>
        </div>
    </div>
</div>

<div id="compareToast" class="compare-global-toast">
    <i class="fa-solid fa-circle-check"></i>
    <span></span>
</div>
